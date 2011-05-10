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
	public $options = array();
    public $list_option_id;

	public function __construct($field_id) {
		$this->field_id = $field_id;
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

		while ($opt_row = $q->fetchRow()) {
			$this->options[$opt_row['list_option_id']] = $opt_row['list_value'];
		}
	}

	public function store() {
		if (!is_array($this->options)) {
			$this->options = array();
		}

		$newoptions = $this->options;

		//insert the new option
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
			$q->addInsert('list_value', $opt);
            $q->exec();
		}
	}

	public function delete() {
		$q = new w2p_Database_Query;
		$q->setDelete('custom_fields_lists');
		$q->addWhere('field_id = ' . (int) $this->field_id);
        $q->addWhere('list_option_id = ' . (int) $this->list_option_id);

		return $q->exec();
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
		$html = '<select name="' . $field_name . '" class="text">';
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