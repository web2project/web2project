<?php

/**
 * Produces a TEXTAREA Element in edit mode
 *
 * @package     web2project\core
 */

class w2p_Core_CustomFieldTextArea extends w2p_Core_CustomField {

    public function __construct($field_id, $field_name, $field_order, $field_description, $field_extratags, $field_published) {
		parent::__construct($field_id, $field_name, $field_order, $field_description, $field_extratags, $field_published);
		$this->field_htmltype = 'textarea';
	}

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