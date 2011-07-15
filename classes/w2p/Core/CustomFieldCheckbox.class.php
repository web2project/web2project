<?php /* $Id$ $URL$ */

/**
 *	@package web2project
 *	@subpackage core
 *	@version $Revision$
 *
 *	CustomFieldCheckBox Class.
 *
 *	Produces an INPUT Element of the CheckBox type in edit mode, view mode indicates 'Yes' or 'No'
 *
 */

class  w2p_Core_CustomFieldCheckBox extends w2p_Core_CustomField {

    public function __construct($field_id, $field_name, $field_order, $field_description, $field_extratags, $field_published) {
		parent::__construct($field_id, $field_name, $field_order, $field_description, $field_extratags, $field_published);
		$this->field_htmltype = 'checkbox';
	}

	public function getHTML($mode) {
		switch ($mode) {
			case 'edit':
				$bool_tag = ($this->intValue()) ? 'checked="checked"': '';
				$html = $this->field_description . ': </td><td><input type="checkbox" name="' . $this->fieldName() . '" value="1" ' . $bool_tag . $this->fieldExtraTags() . '/>';
				break;
			case 'view':
				$bool_text = ($this->intValue()) ? 'Yes': 'No';
				$html = $this->field_description . ': </td><td class="hilite" width="100%">' . $bool_text;
				break;
		}
		return $html;
	}

	public function setValue($v) {
		$this->value_intvalue = $v;
	}
}