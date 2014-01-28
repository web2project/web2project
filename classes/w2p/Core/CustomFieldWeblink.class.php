<?php

/**
 * Produces an INPUT Element of the TEXT type in edit mode and a <a href> </a>
 * weblink in display mode
 *
 * @package     web2project\core
 */

class w2p_Core_CustomFieldWeblink extends w2p_Core_CustomField
{
    public function getHTML($mode) {
        switch ($mode) {
            case 'edit':
                $html = $this->field_description . ': </td><td><input type="text" class="text" name="' . $this->fieldName() . '" value="' . $this->charValue() . '" ' . $this->fieldExtraTags() . ' />';
                break;
            case 'view':
                $html = $this->field_description . ': </td><td class="hilite" width="100%"><a href="' . $this->charValue() . '">' . $this->charValue() . '</a>';
                break;
        }
        return $html;
    }
}