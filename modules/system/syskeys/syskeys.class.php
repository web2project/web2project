<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

##
## CSysKey Class
##

class CSysKey extends w2p_Core_BaseObject {
	public $syskey_id = null;
	public $syskey_name = null;
	public $syskey_label = null;
	public $syskey_type = null;
	public $syskey_sep1 = null;
	public $syskey_sep2 = null;

	public function __construct($name = null, $label = null, $type = '0', $sep1 = "\n", $sep2 = '|') {
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

class CSysVal extends w2p_Core_BaseObject {
	public $sysval_id = null;
	public $sysval_key_id = null;
	public $sysval_title = null;
	public $sysval_value_id = null;
	public $sysval_value = null;

	public function check() {
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
		$q = $this->_query;
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

        $q = $this->_query;
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