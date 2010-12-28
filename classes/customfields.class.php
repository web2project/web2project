<?php

class CustomFields extends w2p_Core_CustomFields
{

    public function __construct($m, $a, $obj_id = null, $mode = 'edit', $published = 0)
    {
        parent::__construct($m, $a, $obj_id, $mode, $published);
        trigger_error("CustomFields has been deprecated in v2.0 and will be removed in v4.0. Please use w2p_core_CustomFields instead.", E_USER_NOTICE );
    }
}