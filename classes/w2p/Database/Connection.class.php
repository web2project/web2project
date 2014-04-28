<?php

class w2p_Database_Connection
{
    protected $db = null;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function connect($host = 'localhost', $dbname, $user = 'root', $passwd = '', $persist = false)
    {
        global $ADODB_FETCH_MODE;

        switch (strtolower(trim(w2PgetConfig('dbtype')))) {
            default:
                //mySQL
                if ($persist) {
                    $this->db->PConnect($host, $user, $passwd, $dbname) or die('FATAL ERROR: Connection to database server failed');
                } else {
                    $this->db->Connect($host, $user, $passwd, $dbname) or die('FATAL ERROR: Connection to database server failed');
                }
        }

        $ADODB_FETCH_MODE = ADODB_FETCH_BOTH;
    }

    public function error()
    {
        if (!is_object($this->db)) {
            dprint(__file__, __line__, 0, 'Database object does not exist.');
        }

        return $this->db->ErrorMsg();
    }

    public function errno()
    {
        if (!is_object($this->db)) {
            dprint(__file__, __line__, 0, 'Database object does not exist.');
        }

        return $this->db->ErrorNo();
    }

    public function insert_id()
    {
        if (!is_object($this->db)) {
            dprint(__file__, __line__, 0, 'Database object does not exist.');
        }

        return $this->db->Insert_ID();
    }

    public function exec($sql, $w2p_performance_dbtime, $w2p_performance_old_dbqueries)
    {
        if (W2P_PERFORMANCE_DEBUG) {
            $startTime = array_sum(explode(' ', microtime()));
        }

        if (!is_object($this->db)) {
            dprint(__file__, __line__, 0, 'Database object does not exist.');
        }
        $qid = $this->db->Execute($sql);
        dprint(__file__, __line__, 10, $sql);
        if (db_error()) {
            dprint(__file__, __line__, 0, "Error executing: <pre>$sql</pre>");
            // Useless statement, but it is being executed only on error,
            // and it stops infinite loop.
            $this->db->Execute($sql);
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

    public function free_result($cur)
    {
        if (!is_object($cur)) {
            dprint(__file__, __line__, 0, 'Invalid object passed to db_free_result.');
        }
        $cur->Close();
    }

    public function num_rows($qid)
    {
        if (!is_object($qid)) {
            dprint(__file__, __line__, 0, 'Invalid object passed to db_num_rows.');
        }

        return $qid->RecordCount();
    }

    public function fetch_row(&$qid)
    {
        if (!is_object($qid)) {
            dprint(__file__, __line__, 0, 'Invalid object passed to db_fetch_row.');
        }

        return $qid->FetchRow();
    }

    public function fetch_assoc(&$qid)
    {
        if (!is_object($qid)) {
            dprint(__file__, __line__, 0, 'Invalid object passed to db_fetch_assoc.');
        }

        return $qid->FetchRow();
    }

    public function fetch_array(&$qid)
    {
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

    public function fetch_object($qid)
    {
        if (!is_object($qid)) {
            dprint(__file__, __line__, 0, 'Invalid object passed to db_fetch_object.');
        }

        return $qid->FetchNextObject(false);
    }

    public function escape($str)
    {
        return substr($this->db->qstr($str), 1, -1);
    }

    public function version()
    {
        return 'ADODB';
    }

    public function unix2dateTime($time)
    {
        return $this->db->DBDate($time);
    }

    public function dateTime2unix($time)
    {
        return $this->db->UnixDate($time);
    }
}