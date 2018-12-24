<?php
/**
 * Session Handling Functions
 *
 * Please note that these functions assume that the database is accessible and that a table called 'sessions' (with a
 *   prefix if necessary) exists.  It also assumes MySQL date and time functions, which may make it less than easy to
 *   port to other databases.  You may need to use less efficient techniques to make it more generic.
 *
 * NOTE: index.php and fileviewer.php MUST call w2PsessionStart instead of trying to set their own sessions.
 */

class w2p_System_Session
{
    protected $q = null;

    public function open()
    {
        return true;
    }

    public function close()
    {
        return true;
    }

    public function read($id)
    {
        $q = new w2p_Database_Query;
        $q->addTable('sessions');
        $q->addQuery('session_data');
        $q->addQuery('UNIX_TIMESTAMP() - UNIX_TIMESTAMP(session_created) as session_lifespan');
        $q->addQuery('UNIX_TIMESTAMP() - UNIX_TIMESTAMP(session_updated) as session_idle');
        $q->addWhere('session_id = \'' . $id . '\'');
        $qid = &$q->exec();
        if (!$qid || $qid->EOF) {
            dprint(__file__, __line__, 11, 'Failed to retrieve session ' . $id);
            $data = '';
        } else {
            $max = $this->convertTime('max_lifetime');
            $idle = $this->convertTime('idle_time');
            // If the idle time or the max lifetime is exceeded, trash the
            // session.
            if ($max < $qid->fields['session_lifespan'] || $idle < $qid->fields['session_idle']) {
                dprint(__file__, __line__, 11, "session $id expired");
                $this->destroy($id);
                $data = '';
            } else {
                $data = $qid->fields['session_data'];
            }
        }
        $q->clear();

        return $data;
    }

    public function write($id, $data)
    {
        global $AppUI;

        $q = new w2p_Database_Query;
        $q->addQuery('count(session_id) as row_count');
        $q->addTable('sessions');
        $q->addWhere('session_id = \'' . $id . '\'');
        $row_count = (int) $q->loadResult();
        $q->clear();

        if ($row_count) {
            $q->addTable('sessions');
            $q->addWhere('session_id = \'' . $id . '\'');
            $q->addUpdate('session_data', $data);
            if (isset($AppUI)) {
                $q->addUpdate('session_user', (int) $AppUI->last_insert_id);
            }
        } else {
            $q->addTable('sessions');
            $q->addInsert('session_id', $id);
            $q->addInsert('session_data', $data);
            $q->addInsert('session_created', $q->dbfnNowWithTZ());
        }
        $q->exec();
        $q->clear();

        return true;
    }

    public function destroy($id)
    {
        $q = new w2p_Database_Query;
        $q2 = new w2p_Database_Query;

        $q->addTable('user_access_log');
        $q->addUpdate('date_time_out', $q->dbfnNowWithTZ());

        $q2->addTable('sessions');
        $q2->addQuery('session_user');
        $q2->addWhere("session_id = '$id'");

        $q->addWhere('user_access_log_id = ( ' . $q2->prepare() . ' )');
        $q->exec();
        $q->clear();
        $q2->clear();

        $q->setDelete('sessions');
        $q->addWhere('session_id = \'' . $id . '\'');
        $q->exec();
        $q->clear();

        return true;
    }

    public function gc()
    {
        global $AppUI;

        $max = $this->convertTime('max_lifetime');
        $idle = $this->convertTime('idle_time');
        // First pass is to kill any users that are logged in at the time of the session.
        $where = 'UNIX_TIMESTAMP() - UNIX_TIMESTAMP(session_updated) > ' . $idle . ' OR UNIX_TIMESTAMP() - UNIX_TIMESTAMP(session_created) > ' . $max;
        $q = new w2p_Database_Query;
        $q->addTable('user_access_log');
        $q->addUpdate('date_time_out', $q->dbfnNowWithTZ());
        $q2 = new w2p_Database_Query;
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
                $AppUI = new w2p_Core_CAppUI();
                $queue = new w2p_System_EventQueue();
                $queue->scan();
            }
        }

        return true;
    }

    public function convertTime($key)
    {
        $key = 'session_' . $key;

        // If the value isn't set, then default to 1 day.
        if (!w2PgetConfig($key, 0)) {
            return 86400;
        }

        $numpart = (int) w2PgetConfig($key);
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

    public function start()
    {
        session_name('web2project');
        if (ini_get('session.auto_start') > 0) {
            session_write_close();
        }
        if (w2PgetConfig('session_handling') == 'app') {
            register_shutdown_function('session_write_close');
            session_set_save_handler(
                array($this, 'open'),     array($this, 'close'),
                array($this, 'read'),     array($this, 'write'),
                array($this, 'destroy'),  array($this, 'gc'));
            $max_time = $this->convertTime('max_lifetime');
        } else {
            $max_time = 0; // Browser session only.
        }

        $url_parts = array();
        $cookie_dir = '';

        // Try and get the correct path to the base URL.
        preg_match('_^(https?://)([^/]+)(:0-9]+)?(/.*)?$_i', W2P_BASE_URL, $url_parts);

        if (isset($url_parts[4])) {
            $cookie_dir = $url_parts[4];
        }

        if (substr($cookie_dir, 0, 1) != '/') {
            $cookie_dir = '/' . $cookie_dir;
        }
        if (substr($cookie_dir, -1) != '/') {
            $cookie_dir .= '/';
        }

        session_set_cookie_params($max_time, $cookie_dir);
        session_start();
    }
}