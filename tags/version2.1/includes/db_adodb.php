<?php /* $Id$ $URL$ */
/*
Based on Leo West's (west_leo@yahooREMOVEME.com):
lib.DB
Database abstract layer
-----------------------
ADODB VERSION
-----------------------
A generic database layer providing a set of low to middle level functions
originally written for WEBO project, see webo source for "real life" usages
*/
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

require_once (W2P_BASE_DIR . '/lib/adodb/adodb.inc.php');

$db = NewADOConnection(w2PgetConfig('dbtype'));

function db_connect($host = 'localhost', $dbname, $user = 'root', $passwd = '', $persist = false) {
	global $db, $ADODB_FETCH_MODE;

	switch (strtolower(trim(w2PgetConfig('dbtype')))) {
		case 'oci8':
		case 'oracle':
			if ($persist) {
				$db->PConnect($host, $user, $passwd, $dbname) or die('FATAL ERROR: Connection to database server failed');
			} else {
				$db->Connect($host, $user, $passwd, $dbname) or die('FATAL ERROR: Connection to database server failed');
			}
			if (!defined('ADODB_ASSOC_CASE')) define('ADODB_ASSOC_CASE', 0);
			break;
		default:
		//mySQL
			if ($persist) {
				$db->PConnect($host, $user, $passwd, $dbname) or die('FATAL ERROR: Connection to database server failed');
			} else {
				$db->Connect($host, $user, $passwd, $dbname) or die('FATAL ERROR: Connection to database server failed');
			}
	}

	$ADODB_FETCH_MODE = ADODB_FETCH_BOTH;
}

function db_error() {
	global $db;
	if (!is_object($db)) {
		dprint(__file__, __line__, 0, 'Database object does not exist.');
	}
	return $db->ErrorMsg();
}

function db_errno() {
	global $db;
	if (!is_object($db)) {
		dprint(__file__, __line__, 0, 'Database object does not exist.');
	}
	return $db->ErrorNo();
}

function db_insert_id() {
	global $db;
	if (!is_object($db)) {
		dprint(__file__, __line__, 0, 'Database object does not exist.');
	}
	return $db->Insert_ID();
}

function db_exec($sql) {
	global $db, $w2p_performance_dbtime, $w2p_performance_old_dbqueries;

	if (W2P_PERFORMANCE_DEBUG) {
		$startTime = array_sum(explode(' ', microtime()));
	}

	if (!is_object($db)) {
		dprint(__file__, __line__, 0, 'Database object does not exist.');
	}
	$qid = $db->Execute($sql);
	dprint(__file__, __line__, 10, $sql);
	if ($msg = db_error()) {
		global $AppUI;
		dprint(__file__, __line__, 0, "Error executing: <pre>$sql</pre>");
		// Useless statement, but it is being executed only on error,
		// and it stops infinite loop.
		$db->Execute($sql);
		if (!db_error()) {
			echo '<script language="JavaScript"> location.reload(); </script>';
		}
	}
	if (!$qid && preg_match('/^\<select\>/i', $sql)) {
		dprint(__file__, __line__, 0, $sql);
	}

	if (W2P_PERFORMANCE_DEBUG) {
		++$w2p_performance_old_dbqueries;
		$w2p_performance_dbtime += array_sum(explode(' ', microtime())) - $startTime;
	}

	return $qid;
}

function db_free_result($cur) {
	// TODO
	//	mysql_free_result( $cur );
	// Maybe it's done my Adodb
	if (!is_object($cur)) {
		dprint(__file__, __line__, 0, 'Invalid object passed to db_free_result.');
	}
	$cur->Close();
}

function db_num_rows($qid) {
	if (!is_object($qid)) {
		dprint(__file__, __line__, 0, 'Invalid object passed to db_num_rows.');
	}
	return $qid->RecordCount();
}

function db_fetch_row(&$qid) {
	if (!is_object($qid)) {
		dprint(__file__, __line__, 0, 'Invalid object passed to db_fetch_row.');
	}
	return $qid->FetchRow();
}

function db_fetch_assoc(&$qid) {
	if (!is_object($qid)) {
		dprint(__file__, __line__, 0, 'Invalid object passed to db_fetch_assoc.');
	}
	return $qid->FetchRow();
}

function db_fetch_array(&$qid) {
	if (!is_object($qid)) {
		dprint(__file__, __line__, 0, 'Invalid object passed to db_fetch_array.');
	}
	$result = $qid->FetchRow();
	// Ensure there are numerics in the result.
	if ($result && !isset($result[0])) {
		$ak = array_keys($result);
		foreach ($ak as $k => $v) {
			$result[$k] = $result[$v];
		}
	}
	return $result;
}

function db_fetch_object($qid) {
	if (!is_object($qid)) {
		dprint(__file__, __line__, 0, 'Invalid object passed to db_fetch_object.');
	}
	return $qid->FetchNextObject(false);
}

function db_escape($str) {
	global $db;
	return substr($db->qstr($str), 1, -1);
}

function db_version() {
	return 'ADODB';
}

function db_unix2dateTime($time) {
	global $db;
	return $db->DBDate($time);
}

function db_dateTime2unix($time) {
	global $db;

	return $db->UnixDate($time);

	// TODO - check if it's used anywhere...
	//	if ($time == '0000-00-00 00:00:00') {
	//		return -1;
	//	}
}

// make the connection to the db
db_connect(w2PgetConfig('dbhost'), w2PgetConfig('dbname'), w2PgetConfig('dbuser'), w2PgetConfig('dbpass'), w2PgetConfig('dbpersist'));

/*
* Having successfully established the database connection now,
* we will hurry up to load the system configuration details from the database.
*/

$sql = 'SELECT config_name, config_value, config_type FROM ' . w2PgetConfig('dbprefix') . 'config';
$rs = $db->Execute($sql);

if ($rs) { // Won't work in install mode.
	$rsArr = $rs->GetArray();

	switch (strtolower(trim(w2PgetConfig('dbtype')))) {
		case 'oci8':
		case 'oracle':
			foreach ($rsArr as $c) {
				if ($c['CONFIG_TYPE'] == 'checkbox') {
					$c['CONFIG_VALUE'] = ($c['CONFIG_VALUES'] == 'true') ? true : false;
				}
				$w2Pconfig[$c['CONFIG_NAME']] = $c['CONFIG_VALUE'];
			}
			break;
		default:
		//mySQL
			foreach ($rsArr as $c) {
				if ($c['config_type'] == 'checkbox') {
					$c['config_value'] = ($c['config_value'] == 'true') ? true : false;
				}
				$w2Pconfig[$c['config_name']] = $c['config_value'];
			}
	}
}