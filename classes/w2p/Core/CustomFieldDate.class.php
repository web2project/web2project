<?php

/**
 * Produces a Date Element in edit mode
 *
 * @package     web2project\core
 */

class w2p_Core_CustomFieldDate extends w2p_Core_CustomField
{
    public $field_htmltype = 'dateinput';

    public function getHTML($mode)
    {
        global $AppUI;

        $field = new Web2project\Fields\Date($AppUI);

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