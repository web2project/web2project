<?php /* $Id$ $URL$ */

/**
 *	@package web2project
 *	@subpackage core
 *	@version $Revision$
 *
 *	CustomFieldSelect Class.
 *
 *	Produces a SELECT list, extends the load method so that the option list
 *    can be loaded from a seperate table
 *
 */

class w2p_Core_CustomFieldSelect extends w2p_Core_CustomField {
	public $options;

	public function __construct($field_id, $field_name, $field_order, $field_description, $field_extratags, $field_published) {
		parent::__construct($field_id, $field_name, $field_order, $field_description, $field_extratags, $field_published);
		$this->field_htmltype = 'select';
		$this->options = new CustomOptionList($field_id);
		$this->options->load();
	}

	public function getHTML($mode) {
		switch ($mode) {
			case 'edit':
				$html = $this->field_description . ': </td><td>';
				$html .= $this->options->getHTML($this->field_name, $this->intValue());
				break;
			case 'view':
				$html = $this->field_description . ': </td><td class="hilite" width="100%">' . $this->options->itemAtIndex($this->intValue());
				break;
		}
		return $html;
	}

	public function setValue($v) {
		$this->value_intvalue = $v;
	}

	public function value() {
		return $this->value_intvalue;
	}
}