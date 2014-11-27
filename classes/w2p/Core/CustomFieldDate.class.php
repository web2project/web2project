<?php

/**
 * Produces a Date Element in edit mode
 *
 * @package     w2p\Core
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
                $pieces = explode('_', $this->fieldName());
                $field->setDateInformation($pieces);
                $html .= $field->edit($this->fieldName(), $this->charValue(), $this->fieldExtraTags());
                break;
            case 'view':
                $html .= $field->view($this->charValue());
                break;
        }
        return $html;
    }
}