<?php

/**
 * Produces an INPUT Element of the TEXT type in edit mode and a <a href> </a>
 * weblink in display mode
 *
 * @package     web2project\core
 */

class w2p_Core_CustomFieldWeblink extends w2p_Core_CustomField
{
    public $field_htmltype = 'href';

    public function getHTML($mode)
    {
        $field = new Web2project\Fields\Url();

        $html = '<label>' . $this->field_description . ':</label>';
        switch ($mode) {
            case 'edit':
                $html .= '<input type="text" class="text" name="' . $this->fieldName() . '" value="' . $this->charValue() . '" ' . $this->fieldExtraTags() . ' />';
                break;
            case 'view':
                $html .= $field->view($this->charValue());
                break;
        }
        return $html;
    }
}