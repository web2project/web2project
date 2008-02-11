<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

/**
 * User Class
 */
class CUser extends CW2pObject {
	var $user_id = null;
	var $user_username = null;
	var $user_password = null;
	var $user_parent = null;
	var $user_type = null;
	var $user_contact = null;
	var $user_signature = null;
	/*	var $user_first_name = NULL;
	var $user_last_name = NULL;
	var $user_company = NULL;
	var $user_department = NULL;
	var $user_email = NULL;
	var $user_phone = NULL;
	var $user_home_phone = NULL;
	var $user_mobile = NULL;
	var $user_address1 = NULL;
	var $user_address2 = NULL;
	var $user_city = NULL;
	var $user_state = NULL;
	var $user_zip = NULL;
	var $user_country = NULL;
	var $user_icq = NULL;
	var $user_aol = NULL;
	var $user_birthday = NULL;
	var $user_pic = NULL;
	var $user_owner = NULL; */

	function CUser() {
		$this->CW2pObject('users', 'user_id');
	}

	function check() {
		if ($this->user_id === null) {
			return 'user id is NULL';
		}
		if ($this->user_password !== null) {
			$this->user_password = db_escape(trim($this->user_password));
		}
		// TODO MORE
		return null; // object is ok
	}

	function store() {
		$msg = $this->check();
		if ($msg) {
			return get_class($this) . '::store-check failed';
		}
		$q = new DBQuery;
		if ($this->user_id) {
			// save the old password
			$perm_func = 'updateLogin';
			$q->addTable('users');
			$q->addQuery('user_password');
			$q->addWhere("user_id = $this->user_id");
			$pwd = $q->loadResult();
			if (!$this->user_password) {
				//if the user didn't provide a password keep the old one
				$this->user_password = $pwd;
			} elseif ($pwd != $this->user_password) {
				$this->user_password = md5($this->user_password);
			} else {
				//if something is not right keep the old one
				$this->user_password = $pwd;
			}
			$q->clear();

			$ret = $q->updateObject('users', $this, 'user_id', false);
			$q->clear();
		} else {
			$perm_func = 'addLogin';
			$this->user_password = md5($this->user_password);
			$ret = $q->insertObject('users', $this, 'user_id');
			$q->clear();
		}
		if (!$ret) {
			return get_class($this) . '::store failed' . db_error();
		} else {
			$acl = &$GLOBALS['AppUI']->acl();
			$acl->$perm_func($this->user_id, $this->user_username);
			//Insert Default Preferences
			//Lets check if the user has allready default users preferences set, if not insert the default ones
			$q->addTable('user_preferences', 'upr');
			$q->addWhere("upr.pref_user = $this->user_id");
			$uprefs = $q->loadList();
			$q->clear();
			if (!count($uprefs) && $this->user_id > 0) {
				//Lets get the default users preferences
				$q->addTable('user_preferences', 'dup');
				$q->addWhere('dup.pref_user = 0');
				$w2prefs = $q->loadList();
				$q->clear();

				foreach ($w2prefs as $w2prefskey => $w2prefsvalue) {
					$q->addTable('user_preferences', 'up');
					$q->addInsert('pref_user', $this->user_id);
					$q->addInsert('pref_name', $w2prefsvalue['pref_name']);
					$q->addInsert('pref_value', $w2prefsvalue['pref_value']);
					$q->exec();
					$q->clear();
				}
			}
			return null;
		}
	}

	function delete($oid = null) {
		global $AppUI;
		$id = $this->user_id;
		//check if the user is related to anything and disallow deletion if he is.
		//companies: is he a owner of any company?
		$q = new DBQuery;
		$q->addQuery('count(company_id)');
		$q->addTable('companies');
		$q->addWhere('company_owner = ' . $this->user_id);
		$result = $q->loadResult();
		$q->clear();
		if ($result) {
			return $AppUI->_('Can not Delete Because This User has') . ' ' . $result . ' ' . $AppUI->_('Companies') . ' ' . $AppUI->_('where he is') . ' ' .$AppUI->_('Owner') . '. ' . $AppUI->_('If you just want this user not to log in consider removing all his Roles. That would make the user Inactive.');
		}
		//departments: is he a owner of any department?
		$q = new DBQuery;
		$q->addQuery('count(dept_id)');
		$q->addTable('departments');
		$q->addWhere('dept_owner = ' . $this->user_id);
		$result = $q->loadResult();
		$q->clear();
		if ($result) {
			return $AppUI->_('Can not Delete Because This User has') . ' ' . $result . ' ' . $AppUI->_('Departments') . ' ' . $AppUI->_('where he is') . ' ' .$AppUI->_('Owner') . '. ' . $AppUI->_('If you just want this user not to log in consider removing all his Roles. That would make the user Inactive.');
		}
		//events: is he a owner of any event?
		$q = new DBQuery;
		$q->addQuery('count(event_id)');
		$q->addTable('events');
		$q->addWhere('event_owner = ' . $this->user_id);
		$result = $q->loadResult();
		$q->clear();
		if ($result) {
			return $AppUI->_('Can not Delete Because This User has') . ' ' . $result . ' ' . $AppUI->_('Events') . ' ' . $AppUI->_('where he is') . ' ' .$AppUI->_('Owner') . '. ' . $AppUI->_('If you just want this user not to log in consider removing all his Roles. That would make the user Inactive.');
		}
		//files: is he a owner of any file?
		$q = new DBQuery;
		$q->addQuery('count(file_id)');
		$q->addTable('files');
		$q->addWhere('file_owner = ' . $this->user_id);
		$result = $q->loadResult();
		$q->clear();
		if ($result) {
			return $AppUI->_('Can not Delete Because This User has') . ' ' . $result . ' ' . $AppUI->_('Files') . ' ' . $AppUI->_('where he is') . ' ' .$AppUI->_('Owner') . '. ' . $AppUI->_('If you just want this user not to log in consider removing all his Roles. That would make the user Inactive.');
		}
		//forums: is he a owner of any forum?
		$q = new DBQuery;
		$q->addQuery('count(forum_id)');
		$q->addTable('forums');
		$q->addWhere('forum_owner = ' . $this->user_id);
		$result = $q->loadResult();
		$q->clear();
		if ($result) {
			return $AppUI->_('Can not Delete Because This User has') . ' ' . $result . ' ' . $AppUI->_('Forums') . ' ' . $AppUI->_('where he is') . ' ' .$AppUI->_('Owner') . '. ' . $AppUI->_('If you just want this user not to log in consider removing all his Roles. That would make the user Inactive.');
		}
		//forums: is he a moderator of any forum?
		$q = new DBQuery;
		$q->addQuery('count(forum_id)');
		$q->addTable('forums');
		$q->addWhere('forum_moderated = ' . $this->user_id);
		$result = $q->loadResult();
		$q->clear();
		if ($result) {
			return $AppUI->_('Can not Delete Because This User has') . ' ' . $result . ' ' . $AppUI->_('Forums') . ' ' . $AppUI->_('where he is') . ' ' .$AppUI->_('Forum Moderator') . '. ' . $AppUI->_('If you just want this user not to log in consider removing all his Roles. That would make the user Inactive.');
		}
		//forums: is he a message creator on any forum?
		$q = new DBQuery;
		$q->addQuery('count(message_id)');
		$q->addTable('forum_messages');
		$q->addWhere('message_author = ' . $this->user_id);
		$result = $q->loadResult();
		$q->clear();
		if ($result) {
			return $AppUI->_('Can not Delete Because This User has') . ' ' . $result . ' ' . $AppUI->_('Forum Messages') . ' ' . $AppUI->_('where he is') . ' ' .$AppUI->_('Author') . '. ' . $AppUI->_('If you just want this user not to log in consider removing all his Roles. That would make the user Inactive.');
		}
		//forums: is he a message creator on any forum?
		$q = new DBQuery;
		$q->addQuery('count(message_id)');
		$q->addTable('forum_messages');
		$q->addWhere('message_editor = ' . $this->user_id);
		$result = $q->loadResult();
		$q->clear();
		if ($result) {
			return $AppUI->_('Can not Delete Because This User has') . ' ' . $result . ' ' . $AppUI->_('Forum Messages') . ' ' . $AppUI->_('where he is') . ' ' .$AppUI->_('Editor') . '. ' . $AppUI->_('If you just want this user not to log in consider removing all his Roles. That would make the user Inactive.');
		}
		//links: is he a owner of any link?
		$q = new DBQuery;
		$q->addQuery('count(link_id)');
		$q->addTable('links');
		$q->addWhere('link_owner = ' . $this->user_id);
		$result = $q->loadResult();
		$q->clear();
		if ($result) {
			return $AppUI->_('Can not Delete Because This User has') . ' ' . $result . ' ' . $AppUI->_('Links') . ' ' . $AppUI->_('where he is') . ' ' .$AppUI->_('Owner') . '. ' . $AppUI->_('If you just want this user not to log in consider removing all his Roles. That would make the user Inactive.');
		}
		//projects: is he related to any project?
		$q = new DBQuery;
		$q->addQuery('count(project_id)');
		$q->addTable('projects');
		$q->addWhere('(project_owner = ' . $this->user_id . ' OR project_creator = ' . $this->user_id . ' OR project_updator = ' . $this->user_id . ')');
		$result = $q->loadResult();
		$q->clear();
		if ($result) {
			return $AppUI->_('Can not Delete Because This User has') . ' ' . $result . ' ' . $AppUI->_('Projects') . ' ' . $AppUI->_('where he is') . ' ' .$AppUI->_('Owner, Creator or Updator') . '. ' . $AppUI->_('If you just want this user not to log in consider removing all his Roles. That would make the user Inactive.');
		}
		//tasks: is he related to any task?
		$q = new DBQuery;
		$q->addQuery('count(task_id)');
		$q->addTable('tasks');
		$q->addWhere('(task_owner = ' . $this->user_id . ' OR task_creator = ' . $this->user_id . ' OR task_updator = ' . $this->user_id . ')');
		$result = $q->loadResult();
		$q->clear();
		if ($result) {
			return $AppUI->_('Can not Delete Because This User has') . ' ' . $result . ' ' . $AppUI->_('Tasks') . ' ' . $AppUI->_('where he is') . ' ' .$AppUI->_('Owner, Creator or Updator') . '. ' . $AppUI->_('If you just want this user not to log in consider removing all his Roles. That would make the user Inactive.');
		}
		//events: is he related to any event?
		$q = new DBQuery;
		$q->addQuery('count(event_id)');
		$q->addTable('user_events');
		$q->addWhere('user_id = ' . $this->user_id);
		$result = $q->loadResult();
		$q->clear();
		if ($result) {
			return $AppUI->_('Can not Delete Because This User has') . ' ' . $result . ' ' . $AppUI->_('Events') . ' ' . $AppUI->_('where he is') . ' ' .$AppUI->_('Attendee') . '. ' . $AppUI->_('If you just want this user not to log in consider removing all his Roles. That would make the user Inactive.');
		}
		//tasks: is he related to any event?
		$q = new DBQuery;
		$q->addQuery('count(task_id)');
		$q->addTable('user_tasks');
		$q->addWhere('user_id = ' . $this->user_id);
		$result = $q->loadResult();
		$q->clear();
		if ($result) {
			return $AppUI->_('Can not Delete Because This User has') . ' ' . $result . ' ' . $AppUI->_('Tasks') . ' ' . $AppUI->_('where he is') . ' ' .$AppUI->_('Assignee') . '. ' . $AppUI->_('If you just want this user not to log in consider removing all his Roles. That would make the user Inactive.');
		}
		//tasks: is he related to any pins?
		$q = new DBQuery;
		$q->addQuery('count(task_id)');
		$q->addTable('user_task_pin');
		$q->addWhere('user_id = ' . $this->user_id);
		$result = $q->loadResult();
		$q->clear();
		if ($result) {
			return $AppUI->_('Can not Delete Because This User has') . ' ' . $result . ' ' . $AppUI->_('Tasks') . ' ' . $AppUI->_('pinned') . '. ' . $AppUI->_('If you just want this user not to log in consider removing all his Roles. That would make the user Inactive.');
		}
		
		$result = parent::delete($oid);
		if (!$result) {
			$acl = &$GLOBALS['AppUI']->acl();
			$acl->deleteLogin($id);
			$q = new DBQuery;
			$q->setDelete('user_preferences');
			$q->addWhere('pref_user = ' . $this->user_id);
			$q->exec();
			$q->clear();
		}
		return $result;
	}

	function getAllowedRecords($uid, $fields = '*', $orderby = '', $index = null, $extra = null) {
		return w2PgetUsers();
	}
}

?>