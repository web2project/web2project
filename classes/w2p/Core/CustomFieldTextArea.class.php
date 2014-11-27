<?php

/**
 * Produces a TEXTAREA Element in edit mode
 *
 * @package     w2p\Core
 */

class w2p_Core_CustomFieldTextArea extends w2p_Core_CustomField
{
    public $field_htmltype = 'textarea';

    public function getHTML($mode)
    {
        $field = new Web2project\Fields\TextArea();

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