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
        $field = new Web2project\Fields\Text();

        $html = '<label>' . $this->field_description . ':</label>';
        switch ($mode) {
            case 'edit':
                $html .= $field->edit($this->fieldName(), $this->charValue(), $this->fieldExtraTags());
                break;
            case 'view':
                $html .= $field->view($this->charValue());
                break;
        }
        return $html;
    }
}