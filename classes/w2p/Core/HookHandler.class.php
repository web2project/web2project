<?php
/**
 * @package web2project\deprecated
 * @deprecated
 */
class w2p_Core_HookHandler extends w2p_System_HookHandler
{
    public function __construct(w2p_Core_CAppUI $AppUI)
    {
        parent::__construct($AppUI);
        trigger_error(get_class($this) . " has been deprecated in v3.1 and will be removed by v4.0. Please use " . get_parent_class($this) . " instead.", E_USER_NOTICE);
    }
}