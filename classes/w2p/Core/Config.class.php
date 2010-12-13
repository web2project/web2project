<?php
/**
 * Configuration class
 */
class w2p_Core_Config extends CW2pObject {

	public function __construct() {
        parent::__construct('config', 'config_id');
	}

	public function getChildren($id) {
		$this->_query->clear();
		$this->_query->addTable('config_list');
		$this->_query->addOrder('config_list_id');
		$this->_query->addWhere('config_id = ' . (int)$id);
		$result = $this->_query->loadHashList('config_list_id');
		$this->_query->clear();
		return $result;
	}
}