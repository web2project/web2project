<?php /* $Id$ $URL$ */

/**
 * @deprecated
 */
class CTaskLog extends CTask_Log
{
    public function __construct()
    {
        parent::__construct();
        trigger_error("CTaskLog has been deprecated in v3.0 and will be removed by v4.0. Please use CTask_Log instead.", E_USER_NOTICE );
    }
}