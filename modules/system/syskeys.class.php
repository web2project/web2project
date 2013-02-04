<?php

/**
 * @package     web2project\modules\core
 */

class CSystem_SysKey extends w2p_Core_BaseObject {
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