<?php
/**
 * @package web2project\deprecated
 * @deprecated
 */
class w2p_Core_Event extends w2p_System_Event
{
    public function __construct($resourceName, $eventName, $data=null)
    {
        parent::__construct($resourceName, $eventName, $data);
        trigger_error(get_class($this) . " has been deprecated in v3.1 and will be removed by v4.0. Please use " . get_parent_class($this) . " instead.", E_USER_NOTICE);
    }
}