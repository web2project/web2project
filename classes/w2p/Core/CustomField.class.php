<?php /* $Id$ $URL$ */

/**
 *	@package web2project
 *	@subpackage core
 *	@version $Revision$
 */

/**
 *	CustomField Abstract Class.
 *
 */

class w2p_Core_CustomField {
	public $field_id;
	public $field_order;
	public $field_name;
	public $field_description;
	public $field_htmltype;
	public $field_published;
	// TODO - data type, meant for validation if you just want numeric data in a text input
	// but not yet implemented
	public $field_datatype;

	public $field_extratags;

	public $object_id = null;

	public $value_id = 0;

	public $value_charvalue;
	public $value_intvalue;

	public function __construct($field_id, $field_name, $field_order, $field_description, $field_extratags, $field_published) {
		$this->field_id = $field_id;
		$this->field_name = $field_name;
		$this->field_order = $field_order;
		$this->field_description = $field_description;
		$this->field_extratags = $field_extratags;
		$this->field_published = $field_published;
	}

	public function load($object_id) {
		// Override Load Method for List type Classes
		global $db;
		$q = new w2p_Database_Query;
		$q->addTable('custom_fields_values');
		$q->addWhere('value_field_id = ' . $this->field_id);
		$q->addWhere('value_object_id = ' . (int) $object_id);
		$rs = $q->exec();
		$row = $q->fetchRow();
		$q->clear();

		$value_id = $row['value_id'];
		$value_charvalue = $row['value_charvalue'];
		$value_intvalue = $row['value_intvalue'];

		if ($value_id != null) {
			$this->value_id = $value_id;
			$this->value_charvalue = $value_charvalue;
			$this->value_intvalue = $value_intvalue;
		}
	}

	public function store($object_id) {
		global $db;

        $object_id = (int) $object_id;

		if ($object_id) {
			$this->value_intvalue = (int) $this->value_intvalue;
			$ins_charvalue = $this->value_charvalue == null ? '' : stripslashes($this->value_charvalue);

            $q = new w2p_Database_Query;
            $q->addTable('custom_fields_values');

			if ($this->value_id) {
				$q->addUpdate('value_charvalue', $ins_charvalue);
				$q->addUpdate('value_intvalue', $this->value_intvalue);
				$q->addWhere('value_id = ' . $this->value_id);
			} else {
				$q->addInsert('value_module', '');
				$q->addInsert('value_field_id', $this->field_id);
				$q->addInsert('value_object_id', $object_id);
				$q->addInsert('value_charvalue', $ins_charvalue);
				$q->addInsert('value_intvalue', $this->value_intvalue);
			}
			$rs = $q->exec();

			$q->clear();
			if (!$rs) {
				return $db->ErrorMsg() . ' | SQL: ';
			}
		} else {
            return 'Error: Cannot store field (' . $this->field_name . '), associated id not supplied.';
		}
	}

	public function setIntValue($v) {
		$this->value_intvalue = $v;
	}

	public function intValue() {
		return $this->value_intvalue;
	}

	public function setValue($v) {
		$this->value_charvalue = $v;
	}

	public function value() {
		return $this->value_charvalue;
	}

	public function charValue() {
		return $this->value_charvalue;
	}

	public function setValueId($v) {
		$this->value_id = $v;
	}

	public function valueId() {
		return $this->value_id;
	}

	public function fieldName() {
		return $this->field_name;
	}

	public function fieldDescription() {
		return $this->field_description;
	}

	public function fieldId() {
		return $this->field_id;
	}

	public function fieldHtmlType() {
		return $this->field_htmltype;
	}

	public function fieldExtraTags() {
		return $this->field_extratags;
	}

	public function fieldOrder() {
		return $this->field_order;
	}

	public function fieldPublished() {
		return $this->field_published;
	}

}