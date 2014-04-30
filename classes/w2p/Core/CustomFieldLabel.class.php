<?php

/**
 * Produces just a non editable label
 *
 * @package     web2project\core
 */

class w2p_Core_CustomFieldLabel extends w2p_Core_CustomField
{
    public $field_htmltype = 'label';

    public function getHTML($mode)
    {
        // We don't really care about its mode
        return '<span ' . $this->fieldExtraTags() . '>' . $this->field_description . '</span>';
    }
}