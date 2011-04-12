<?php /* $Id$ $URL$ */

class Mail extends w2p_Utilities_Mail
{
    public function __construct()
    {
        parent::__construct();
        trigger_error("Mail has been deprecated in v2.0 and will be removed by v4.0. Please use w2p_Utilities_Mail instead.", E_USER_NOTICE );
    }
}