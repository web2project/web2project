<?php
/**
 * Configuration class
 */
class w2p_Core_Config extends w2p_Core_BaseObject {

	public function __construct() {
        parent::__construct('config', 'config_id');
	}

	public function getChildren($id) {
		$q = $this->_getQuery();
		$q->addTable('config_list');
		$q->addOrder('config_list_id');
		$q->addWhere('config_id = ' . (int)$id);
		$result = $q->loadHashList('config_list_id');

		return $result;
	}
}