<?php /* $Id$ $URL$ */

class CDate extends w2p_Utilities_Date
{
    public function __construct($datetime = null, $tz = '')
    {
        parent::__construct($datetime, $tz);
        trigger_error("CDate has been deprecated in v2.0 and will be removed by v4.0. Please use w2p_Utilities_Date instead.", E_USER_NOTICE );
    }
}