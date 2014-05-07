<?php

/**
 * Produces a TEXTAREA Element in edit mode
 *
 * @package     web2project\core
 */

class w2p_Core_CustomFieldTextArea extends w2p_Core_CustomField
{
    public $field_htmltype = 'textarea';

    public function getHTML($mode)
    {
        $html = '<label>' . $this->field_description . ':</label>';
        switch ($mode) {
            case 'edit':
                $html .= '<textarea name="' . $this->fieldName() . '" ' . $this->fieldExtraTags() . ' class="customfield">' . $this->charValue() . '</textarea>';
                break;
            case 'view':
                $html .= nl2br($this->charValue());
                break;
        }
        return $html;
    }
}