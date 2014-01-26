<?php

/**
 * Produces an INPUT Element of the TEXT type in edit mode
 *
 * @package web2project\core
 */

class w2p_Core_CustomFieldEmail extends w2p_Core_CustomFieldText {

    public function __construct($field_id, $field_name, $field_order, $field_description, $field_extratags, $field_published) {
        parent::__construct($field_id, $field_name, $field_order, $field_description, $field_extratags, $field_published);
        $this->field_htmltype = 'email';
    }

    public function getHTML($mode) {
        switch ($mode) {
            case 'edit':
                $html = $this->field_description . ': </td><td><input type="text" class="text" name="' . $this->fieldName() . '" value="' . $this->charValue() . '" ' . $this->fieldExtraTags() . ' />';
                break;
            case 'view':
                $html = $this->field_description . ': </td><td class="hilite" width="100%">' . w2p_email($this->charValue());
                break;
        }
        return $html;
    }
}