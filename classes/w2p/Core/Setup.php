<?php
/**
 * @package web2project\deprecated
 * @deprecated
 */
class w2p_Core_Setup extends w2p_System_Setup
{
    public function __construct(w2p_Core_CAppUI $AppUI = null,
                                array $config = null, w2p_Database_Query $query = null)
    {
        parent::__construct($AppUI, $config, $query);
        trigger_error(get_class($this) . " has been deprecated in v3.1 and will be removed by v4.0. Please use " . get_parent_class($this) . " instead.", E_USER_NOTICE);
    }
}