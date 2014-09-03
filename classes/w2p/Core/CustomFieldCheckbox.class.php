<?php

/**
 * Produces an INPUT Element of the CheckBox type in edit mode, view mode
 *   indicates 'Yes' or 'No'
 *
 * @package     web2project\core
 */

class  w2p_Core_CustomFieldCheckBox extends w2p_Core_CustomField
{
    public $field_htmltype = 'checkbox';

    public function getHTML($mode)
    {
        $field = new Web2project\Fields\Checkbox();

        $html = '<label>' . $this->field_description . ':</label>';
        switch ($mode) {
            case 'edit':
                $bool_tag = ($this->intValue()) ? 'checked="checked"': '';
                $html .= $field->edit($this->fieldName(), $bool_tag, $this->fieldExtraTags());
                break;
            case 'view':
                $bool_text = ($this->intValue()) ? 'Yes': 'No';
                $html .= $field->view($bool_text);
                break;
        }
        return $html;
    }

    public function setValue($v) {
        $this->value_intvalue = $v;
    }
}