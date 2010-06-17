<?php /* $Id$ $URL$ */

/**
 *	@package web2project
 *	@subpackage core
 *	@version $Revision$
 *
 *	CustomFieldLabel Class.
 *
 *	Produces just a non editable label
 *
 */

class w2p_Core_CustomFieldLabel extends w2p_Core_CustomField {

    public function __construct($field_id, $field_name, $field_order, $field_description, $field_extratags, $field_published) {
		parent::__construct($field_id, $field_name, $field_order, $field_description, $field_extratags, $field_published);
		$this->field_htmltype = 'label';
	}

	public function getHTML($mode) {
		// We don't really care about its mode
		return '<span ' . $this->field_extratags . '>' . $this->field_description . '</span>';
	}
}