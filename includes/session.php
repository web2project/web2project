<?php /* $Id$ $URL$ */
##
## Session Handling Functions
##
/*
* Please note that these functions assume that the database
* is accessible and that a table called 'sessions' (with a prefix
* if necessary) exists.  It also assumes MySQL date and time
* functions, which may make it less than easy to port to
* other databases.  You may need to use less efficient techniques
* to make it more generic.
*
* NOTE: index.php and fileviewer.php MUST call w2PsessionStart
* instead of trying to set their own sessions.
*/

if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

require_once W2P_BASE_DIR . '/includes/main_functions.php';
require_once W2P_BASE_DIR . '/includes/db_adodb.php';
require_once W2P_BASE_DIR . '/classes/query.class.php';
require_once W2P_BASE_DIR . '/classes/ui.class.php';
require_once W2P_BASE_DIR . '/classes/event_queue.class.php';

function w2PsessionOpen($save_path, $session_name) {
	return true;
}

function w2PsessionClose() {
	return true;
}

function w2PsessionRead($id) {
	$q = new DBQuery;
	$q->addTable('sessions');
	$q->addQuery('session_data');
	$q->addQuery('UNIX_TIMESTAMP() - UNIX_TIMESTAMP(session_created) as session_lifespan');
	$q->addQuery('UNIX_TIMESTAMP() - UNIX_TIMESTAMP(session_updated) as session_idle');
	$q->addWhere('session_id = \''.$id.'\'');
	$qid = &$q->exec();
	if (!$qid || $qid->EOF) {
		dprint(__file__, __line__, 11, 'Failed to retrieve session ' . $id);
		$data = '';
	} else {
		$max = w2PsessionConvertTime('max_lifetime');
		$idle = w2PsessionConvertTime('idle_time');
		// dprint(__file__, __line__, 11, "Found session $id, max=$max/" . $qid->fields['session_lifespan'] . ", idle=$idle/" . $qid->fields['session_idle']);
		// If the idle time or the max lifetime is exceeded, trash the
		// session.
		if ($max < $qid->fields['session_lifespan'] || $idle < $qid->fields['session_idle']) {
			dprint(__file__, __line__, 11, "session $id expired");
			w2PsessionDestroy($id);
			$data = '';
		} else {
			$data = $qid->fields['session_data'];
		}
	}
	$q->clear();
	return $data;
}

function w2PsessionWrite($id, $data) {
	global $AppUI;

	$q = new DBQuery;
	$q->addQuery('count(session_id) as row_count');
	$q->addTable('sessions');
	$q->addWhere('session_id = \''.$id.'\'');

	if ($qid = &$q->exec() && ($qid->fields['row_count'] > 0 || $qid->fields[0] > 0)) {
		//dprint(__file__, __line__, 11, "Updating session $id");
		$q->query = null;
		$q->addUpdate('session_data', $data);
		if (isset($AppUI)) {
			$q->addUpdate('session_user', (int)$AppUI->last_insert_id);
		}
	} else {
		//dprint(__file__, __line__, 11, "Creating new session $id");
		$q->query = null;
		$q->where = null;
		$q->addInsert('session_id', $id);
		$q->addInsert('session_data', $data);
		$q->addInsert('session_created', date('Y-m-d H:i:s'));
	}
	$q->exec();
	$q->clear();
	return true;
}

function w2PsessionDestroy($id, $user_access_log_id = 0) {
	global $AppUI;

	$q = new DBQuery;

	//dprint(__file__, __line__, 11, "Killing session $id");
	$q->addTable('user_access_log');
	$q->addUpdate('date_time_out', date('Y-m-d H:i:s'));
	$q2 = new DBQuery;
	$q2->addTable('sessions');
	$q2->addQuery('session_user');
	$q2->addWhere('session_id = \'' . $id . '\'');
	$q->addWhere('user_access_log_id = ( ' . $q2->prepare() . ' )');
	$q->exec();
	$q->clear();
	$q2->clear();

	$q->setDelete('sessions');
	$q->addWhere('session_id = \''.$id.'\'');
	$q->exec();
	$q->clear();

	return true;
}

function w2PsessionGC($maxlifetime) {
	global $AppUI;

	//dprint(__file__, __line__, 11, 'Session Garbage collection running');
	$now = time();
	$max = w2PsessionConvertTime('max_lifetime');
	$idle = w2PsessionConvertTime('idle_time');
	// First pass is to kill any users that are logged in at the time of the session.
	$where = 'UNIX_TIMESTAMP() - UNIX_TIMESTAMP(session_updated) > ' . $idle . ' OR UNIX_TIMESTAMP() - UNIX_TIMESTAMP(session_created) > ' . $max;
	$q = new DBQuery;
	$q->addTable('user_access_log');
	$q->addUpdate('date_time_out', date('Y-m-d H:i:s'));
	$q2 = new DBQuery;
	$q2->addTable('sessions');
	$q2->addQuery('session_user');
	$q2->addWhere($where);
	$q->addWhere('user_access_log_id IN ( ' . $q2->prepare() . ' )');
	$q->exec();
	$q->clear();
	$q2->clear();

	// Now we simply delete the expired sessions.
	$q->setDelete('sessions');
	$q->addWhere($where);
	$q->exec();
	$q->clear();
	if (w2PgetConfig('session_gc_scan_queue')) {
		// We need to scan the event queue.  If $AppUI isn't created yet
		// And it isn't likely that it will be, we create it and run the
		// queue scanner.
		if (!isset($AppUI)) {
			$AppUI = new CAppUI;
			$queue = new EventQueue;
			$queue->scan();
		}
	}
	return true;
}

function w2PsessionConvertTime($key) {
	$key = 'session_' . $key;

	// If the value isn't set, then default to 1 day.
	if (w2PgetConfig($key) == null || w2PgetConfig($key) == null) {
		return 86400;
	}

	$numpart = (int)w2PgetConfig($key);
	$modifier = substr(w2PgetConfig($key), -1);
	if (!is_numeric($modifier)) {
		switch ($modifier) {
			case 'h':
				$numpart *= 3600;
				break;
			case 'd':
				$numpart *= 86400;
				break;
			case 'm':
				$numpart *= (86400 * 30);
				break;
			case 'y':
				$numpart *= (86400 * 365);
				break;
		}
	}
	return $numpart;
}

function w2PsessionStart($start_vars = 'AppUI') {
	session_name('web2project');
	if (ini_get('session.auto_start') > 0) {
		session_write_close();
	}
	if (w2PgetConfig('session_handling') == 'app') {
		ini_set('session.save_handler', 'user');
        register_shutdown_function('session_write_close');
		session_set_save_handler('w2PsessionOpen', 'w2PsessionClose', 'w2PsessionRead', 'w2PsessionWrite', 'w2PsessionDestroy', 'w2PsessionGC');
		$max_time = w2PsessionConvertTime('max_lifetime');
	} else {
		$max_time = 0; // Browser session only.
	}
	// Try and get the correct path to the base URL.
	preg_match('_^(https?://)([^/]+)(:0-9]+)?(/.*)?$_i', w2PgetConfig('base_url'), $url_parts);
	$cookie_dir = $url_parts[4];
	if (substr($cookie_dir, 0, 1) != '/') {
		$cookie_dir = '/' . $cookie_dir;
	}
	if (substr($cookie_dir, -1) != '/') {
		$cookie_dir .= '/';
	}
	session_set_cookie_params($max_time, $cookie_dir);
	session_start();
}