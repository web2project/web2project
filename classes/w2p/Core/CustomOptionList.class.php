<?php /* $Id$ $URL$ */

/**
 *	@package web2project
 *	@subpackage core
 *	@version $Revision$
 *
 *	CustomOptionList Class.
 *
 */

class w2p_Core_CustomOptionList {
	public $field_id;
	public $options;

	public function __construct($field_id) {
		$this->field_id = $field_id;
		$this->options = array();
	}

	public function load() {
		global $db;

		$q = new w2p_Database_Query;
		$q->addTable('custom_fields_lists');
		$q->addWhere('field_id = ' . $this->field_id);
		$q->addOrder('list_value');
		if (!$rs = $q->exec()) {
			$q->clear();
			return $db->ErrorMsg();
		}

		$this->options = array();

		while ($opt_row = $q->fetchRow()) {
			$this->options[$opt_row['list_option_id']] = $opt_row['list_value'];
		}
		$q->clear();
	}

	public function store() {
		global $db;

		if (!is_array($this->options)) {
			$this->options = array();
		}

		//load the dbs options and compare them with the options
		$q = new w2p_Database_Query;
		$q->addTable('custom_fields_lists');
		$q->addWhere('field_id = ' . $this->field_id);
		$q->addOrder('list_value');
		if (!$rs = $q->exec()) {
			$q->clear();
			return $db->ErrorMsg();
		}

		$dboptions = array();

		while ($opt_row = $q->fetchRow()) {
			$dboptions[$opt_row['list_option_id']] = $opt_row['list_value'];
		}
		$q->clear();

		$newoptions = array();
		$newoptions = array_diff($this->options, $dboptions);
		$deleteoptions = array_diff($dboptions, $this->options);
		//insert the new options
		foreach ($newoptions as $opt) {
			$q = new w2p_Database_Query;
			$q->addTable('custom_fields_lists');
			$q->addQuery('MAX(list_option_id)');
			$max_id = $q->loadResult();
			$optid = $max_id ? $max_id + 1 : 1;

			$q = new w2p_Database_Query;
			$q->addTable('custom_fields_lists');
			$q->addInsert('field_id', $this->field_id);
			$q->addInsert('list_option_id', $optid);
			$q->addInsert('list_value', db_escape(strip_tags($opt)));

			if (!$q->exec()) {
				$insert_error = $db->ErrorMsg();
			}
			$q->clear();
		}
		//delete the deleted options
		foreach ($deleteoptions as $opt => $value) {
			$q = new w2p_Database_Query;
			$q->setDelete('custom_fields_lists');
			$q->addWhere('list_option_id =' . $opt);

			if (!$q->exec()) {
				$delete_error = $db->ErrorMsg();
			}
			$q->clear();
		}

		return $insert_error . ' ' . $delete_error;
	}

	public function delete() {
		$q = new w2p_Database_Query;
		$q->setDelete('custom_fields_lists');
		$q->addWhere('field_id = ' . $this->field_id);
		$q->exec();
		$q->clear();
	}

	public function setOptions($option_array) {
		$this->options = $option_array;
	}

	public function getOptions() {
		return $this->options;
	}

	public function itemAtIndex($i) {
		return $this->options[$i];
	}

	public function getHTML($field_name, $selected) {
		$html = '<select name="' . $field_name . '">';
		foreach ($this->options as $i => $opt) {
			$html .= "\t" . '<option value="' . $i . '"';
			if ($i == $selected) {
				$html .= ' selected="selected" ';
			}
			$html .= '>' . $opt . '</option>';
		}
		$html .= '</select>';
		return $html;
	}
}