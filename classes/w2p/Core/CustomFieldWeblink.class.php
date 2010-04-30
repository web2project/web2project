<?php /* $Id$ $URL$ */

/**
 *	@package web2project
 *	@subpackage core
 *	@version $Revision$
 *
 *	CustomFieldWeblink Class.
 *
 *	Produces an INPUT Element of the TEXT type in edit mode and a <a href> </a>
 *    weblink in display mode
 */

class w2p_Core_CustomFieldWeblink extends w2p_Core_CustomField {

    public function __construct($field_id, $field_name, $field_order, $field_description, $field_extratags, $field_published) {
		parent::__construct($field_id, $field_name, $field_order, $field_description, $field_extratags, $field_published);
		$this->field_htmltype = 'href';
	}

	public function getHTML($mode) {
		switch ($mode) {
			case 'edit':
				$html = $this->field_description . ': </td><td><input type="text" class="text" name="' . $this->field_name . '" value="' . $this->charValue() . '" ' . $this->field_extratags . ' />';
				break;
			case 'view':
				$html = $this->field_description . ': </td><td class="hilite" width="100%"><a href="' . $this->charValue() . '">' . $this->charValue() . '</a>';
				break;
		}
		return $html;
	}
}