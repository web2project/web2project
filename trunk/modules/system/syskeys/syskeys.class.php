<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

##
## CSysKey Class
##

class CSysKey extends CW2pObject {
	public $syskey_id = null;
	public $syskey_name = null;
	public $syskey_label = null;
	public $syskey_type = null;
	public $syskey_sep1 = null;
	public $syskey_sep2 = null;

	public function CSysKey($name = null, $label = null, $type = '0', $sep1 = "\n", $sep2 = '|') {
    parent::__construct('syskeys', 'syskey_id');
		$this->syskey_name = $name;
		$this->syskey_label = $label;
		$this->syskey_type = $type;
		$this->syskey_sep1 = $sep1;
		$this->syskey_sep2 = $sep2;
	}
}

##
## CSysVal Class
##

class CSysVal extends CW2pObject {
	public $sysval_id = null;
	public $sysval_key_id = null;
	public $sysval_title = null;
	public $sysval_value_id = null;
	public $sysval_value = null;

	public function check() {
		//print_r($this);die;
		if ($this->sysval_key_id == 0) {
			return 'Key Type cannot be empty';
		}
		if (!$this->sysval_title) {
			return 'Key Title cannot be empty';
		}
		return null;
	}

	public function __construct($key = null, $title = null, $value = null) {
    parent::__construct('sysvals', 'sysval_id');
		$this->sysval_key_id = $key;
		$this->sysval_title = $title;
		$this->sysval_value = $value;
	}

	public function store() {
		$this->w2PTrimAll();

		$msg = $this->check();
		if ($msg) {
			return get_class($this) . '::store-check failed - ' . $msg;
		}
		$values = parseFormatSysval($this->sysval_value, $this->sysval_key_id);
		//lets delete the old values
		$q = new DBQuery;
		if ($this->sysval_key_id && $this->sysval_title) {
			$q->setDelete('sysvals');
			$q->addWhere('sysval_key_id = ' . (int)$this->sysval_key_id);
			$q->addWhere('sysval_title = \'' . $this->sysval_title . '\'');
			if (!$q->exec()) {
				$q->clear();
				return get_class($this) . '::store failed: ' . db_error();
			}
		}
		foreach ($values as $key => $value) {
			$q->addTable('sysvals');
			$q->addInsert('sysval_key_id', $this->sysval_key_id);
			$q->addInsert('sysval_title', $this->sysval_title);
			$q->addInsert('sysval_value_id', $key);
			$q->addInsert('sysval_value', $value);
			if (!$q->exec()) {
				$q->clear();
				return get_class($this) . '::store failed: ' . db_error();
			}
			$q->clear();
		}
		return null;
	}

	public function delete() {
		$q = new DBQuery;
		if ($this->sysval_title) {
			$q->setDelete('sysvals');
			$q->addWhere('sysval_title = \'' . $this->sysval_title . '\'');
			if (!$q->exec()) {
				$q->clear();
				return get_class($this) . '::delete failed <br />' . db_error();
			}
		}
		return null;
	}

}

function parseFormatSysval($text, $syskey) {
	$q = new DBQuery;
	$q->addTable('syskeys');
	$q->addQuery('syskey_type, syskey_sep1, syskey_sep2');
	$q->addWhere('syskey_id = ' . (int)$syskey);
	$q->exec();
	$row = $q->fetchRow();
	$q->clear();
	// type 0 = list
	$sep1 = $row['syskey_sep1']; // item separator
	$sep2 = $row['syskey_sep2']; // alias separator

	// A bit of magic to handle newlines and returns as separators
	// Missing sep1 is treated as a newline.
	if (!isset($sep1) || empty($sep1)) {
		$sep1 = "\n";
	}
	if ($sep1 == "\\n") {
		$sep1 = "\n";
	}
	if ($sep1 == "\\r") {
		$sep1 = "\r";
	}

	$temp = explode($sep1, $text);
	$arr = array();
	// We use trim() to make sure a numeric that has spaces
	// is properly treated as a numeric
	foreach ($temp as $item) {
		if ($item) {
			$sep2 = empty($sep2) ? "\n" : $sep2;
			$temp2 = explode($sep2, $item);
			if (isset($temp2[1])) {
				$arr[mb_trim($temp2[0])] = mb_trim($temp2[1]);
			} else {
				$arr[mb_trim($temp2[0])] = mb_trim($temp2[0]);
			}
		}
	}
	return $arr;
}