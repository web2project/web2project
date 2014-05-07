<?php

/**
 * Produces an INPUT Element of the TEXT type in edit mode. In view mode, it becomes a clickable email address.
 *
 * @package web2project\core
 */

class w2p_Core_CustomFieldEmail extends w2p_Core_CustomFieldText
{
    public $field_htmltype = 'email';

    public function getHTML($mode)
    {
        $html = '<label>' . $this->field_description . ':</label>';
        switch ($mode) {
            case 'edit':
                $html .= '<input type="text" class="text" name="' . $this->fieldName() . '" value="' . $this->charValue() . '" ' . $this->fieldExtraTags() . ' />';
                break;
            case 'view':
                $html .= w2p_email($this->charValue());
                break;
        }
        return $html;
    }
}