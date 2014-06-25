<?php

/**
 * Produces an INPUT Element of the TEXT type in edit mode
 *
 * @package web2project\core
 */

class w2p_Core_CustomFieldText extends w2p_Core_CustomField
{
    public $field_htmltype = 'textinput';

    public function getHTML($mode)
    {
        $html = '<label>' . $this->field_description . ':</label>';
        switch ($mode) {
            case 'edit':
                $html .= '<input type="text" class="text" name="' . $this->fieldName() . '" value="' . $this->charValue() . '" ' . $this->fieldExtraTags() . ' />';
                break;
            case 'view':
                $html .= '&nbsp;' . $this->charValue();
                break;
        }
        return $html;
    }
}