<?php

/**
 * Produces a TEXTAREA Element in edit mode
 *
 * @package     web2project\core
 */

class w2p_Core_CustomFieldTextArea extends w2p_Core_CustomField
{
    public function getHTML($mode) {
        switch ($mode) {
            case 'edit':
                $html = $this->field_description . ': </td><td><textarea name="' . $this->fieldName() . '" ' . $this->fieldExtraTags() . ' class="customfield">' . $this->charValue() . '</textarea>';
                break;
            case 'view':
                $html = $this->field_description . ': </td><td class="hilite" width="100%">' . nl2br($this->charValue());
                break;
        }
        return $html;
    }
}