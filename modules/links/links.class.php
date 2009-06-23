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

	public $link_id = null;
	public $link_project = null;
	public $link_url = null;
	public $link_task = null;
	public $link_name = null;
	public $link_parent = null;
	public $link_description = null;
	public $link_owner = null;
	public $link_date = null;
	public $link_category = null;

	public function CLink() {
		$this->CW2pObject('links', 'link_id');
	}

	public function check() {
		// ensure the integrity of some variables
		$this->link_id = intval($this->link_id);
		$this->link_parent = intval($this->link_parent);
		$this->link_category = intval($this->link_category);
		$this->link_task = intval($this->link_task);
		$this->link_project = intval($this->link_project);

		return null; // object is ok
	}

	public function delete() {
		global $w2Pconfig;
		$this->_message = "deleted";

		// delete the main table reference
		$q = new DBQuery();
		$q->setDelete('links');
		$q->addWhere('link_id = ' . (int)$this->link_id);
		if (!$q->exec()) {
			return db_error();
		}
		return null;
	}
}