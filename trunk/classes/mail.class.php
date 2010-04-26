<?php

class Mail extends w2p_Utilities_Mail
{
    function __construct($datetime = null, $tz = '')
    {
        parent::__construct();
        //trigger_error("Mail has been deprecated in v2.0 and will be removed in v3.0. Please use w2p_Utilities_Mail instead.", E_USER_NOTICE );
    }
}