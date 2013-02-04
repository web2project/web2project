<?php

/**
 * Produces just an horizontal line
 *
 * @package     web2project\core
 */

class w2p_Core_CustomFieldSeparator extends w2p_Core_CustomField {

    public function __construct($field_id, $field_name, $field_order, $field_description, $field_extratags, $field_published) {
		parent::__construct($field_id, $field_name, $field_order, $field_description, $field_extratags, $field_published);
		$this->field_htmltype = 'separator';
	}

	public function getHTML($mode) {
		// We don't really care about its mode
		return '<hr ' . $this->fieldExtraTags() . ' />';
	}
}