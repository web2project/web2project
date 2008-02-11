<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

require_once ($AppUI->getSystemClass('w2p'));
require_once ($AppUI->getModuleClass('tasks'));
require_once ($AppUI->getModuleClass('projects'));
/**
 * Link Class
 */
class CLink extends CW2pObject {

	var $link_id = null;
	var $link_project = null;
	var $link_url = null;
	var $link_task = null;
	var $link_name = null;
	var $link_parent = null;
	var $link_description = null;
	var $link_owner = null;
	var $link_date = null;
	var $link_category = null;

	function CLink() {
		$this->CW2pObject('links', 'link_id');
	}

	function check() {
		// ensure the integrity of some variables
		$this->link_id = intval($this->link_id);
		$this->link_parent = intval($this->link_parent);
		$this->link_category = intval($this->link_category);
		$this->link_task = intval($this->link_task);
		$this->link_project = intval($this->link_project);

		return null; // object is ok
	}

	function delete() {
		global $w2Pconfig;
		$this->_message = "deleted";

		// delete the main table reference
		$q = new DBQuery();
		$q->setDelete('links');
		$q->addWhere('link_id = ' . $this->link_id);
		if (!$q->exec()) {
			return db_error();
		}
		return null;
	}
}
?>