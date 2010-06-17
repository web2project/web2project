<?php

class DBQuery extends w2p_Database_Query
{
    public function __construct($prefix = null, $query_db = null)
    {
        parent::__construct($prefix, $query_db);
        //trigger_error("DBQuery has been deprecated in v2.0 and will be removed in v3.0. Please use w2p_Database_DBQuery instead.", E_USER_NOTICE );
    }
}