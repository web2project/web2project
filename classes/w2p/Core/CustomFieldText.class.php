<?php /* $Id$ $URL$ */

/**
 *	@package web2project
 *	@subpackage core
 *	@version $Revision$
 *
 *	CustomFieldText Class.
 *
 *	Produces an INPUT Element of the TEXT type in edit mode
 *	
 */

class w2p_Core_CustomFieldText extends w2p_Core_CustomField {

    public function __construct($field_id, $field_name, $field_order, $field_description, $field_extratags, $field_published) {
		parent::__construct($field_id, $field_name, $field_order, $field_description, $field_extratags, $field_published);
		$this->field_htmltype = 'textinput';
	}

	public function getHTML($mode) {
		switch ($mode) {
			case 'edit':
				$html = $this->field_description . ': </td><td><input type="text" class="text" name="' . $this->fieldName() . '" value="' . $this->charValue() . '" ' . $this->fieldExtraTags() . ' />';
				break;
			case 'view':
				$html = $this->field_description . ': </td><td class="hilite" width="100%">' . $this->charValue();
				break;
		}
		return $html;
	}
}