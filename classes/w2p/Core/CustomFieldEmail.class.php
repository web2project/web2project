<?php

/**
 * Produces an INPUT Element of the TEXT type in edit mode. In view mode, it becomes a clickable email address.
 *
 * @package     w2p\Core
 */

class w2p_Core_CustomFieldEmail extends w2p_Core_CustomField
{
    public $field_htmltype = 'email';

    public function getHTML($mode)
    {
        $field = new Web2project\Fields\Email();

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