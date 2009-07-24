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
	
	public function loadFull($link_id) {
		$q = new DBQuery();
		$q->addQuery('links.*');
		$q->addQuery('user_username');
		$q->addQuery('contact_first_name,	contact_last_name');
		$q->addQuery('project_id');
		$q->addQuery('task_id, task_name');
		$q->addTable('links');
		$q->leftJoin('users', 'u', 'link_owner = user_id');
		$q->leftJoin('contacts', 'c', 'user_contact = contact_id');
		$q->leftJoin('projects', 'p', 'project_id = link_project');
		$q->leftJoin('tasks', 't', 'task_id = link_task');
		$q->addWhere('link_id = ' . (int)$link_id);
		$q->loadObject($this, true, false);
	}

	public function getProjectTaskLinksByCategory($AppUI, $project_id = 0, $task_id = 0, $category_id = 0, $search = '') {
		// load the following classes to retrieved denied records
		
		$project = new CProject();
		$task = new CTask();

		// SETUP FOR LINK LIST
		$q = new DBQuery();
		$q->addQuery('links.*');
		$q->addQuery('contact_first_name, contact_last_name');
		$q->addQuery('project_name, project_color_identifier, project_status');
		$q->addQuery('task_name, task_id');
		
		$q->addTable('links');
		
		$q->leftJoin('users', 'u', 'user_id = link_owner');
		$q->leftJoin('contacts', 'c', 'user_contact = contact_id');
		
		if ($search != '') {
			$q->addWhere('(link_name LIKE \'%' . $search . '%\' OR link_description LIKE \'%' . $search . '%\')');
		}
		if ($project_id > 0) { // Project
			$q->addWhere('link_project = ' . (int)$project_id);
		}
		if ($task_id > 0) { // Task
			$q->addWhere('link_task = ' . (int)$task_id);
		}
		if ($category_id >= 0) { // Category
			$q->addWhere('link_category = '.$category_id);
		}
		// Permissions
		$project->setAllowedSQL($AppUI->user_id, $q, 'link_project');
		$task->setAllowedSQL($AppUI->user_id, $q, 'link_task and task_project = link_project');
		$q->addOrder('project_name, link_name');

		return $q->loadList();
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