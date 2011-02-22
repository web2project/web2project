<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

// user types
$utypes = w2PgetSysVal('UserType');

/**
 * User Class
 */
class CUser extends w2p_Core_BaseObject {
	public $user_id = null;
	public $user_username = null;
	public $user_password = null;
	public $user_parent = null;
	public $user_type = null;
	public $user_contact = null;
	public $user_signature = null;

	public function __construct() {
        parent::__construct('users', 'user_id');
	}

	public function check() {
        if ($this->user_id === null) {
			return 'user id is NULL';
		}
		if ($this->user_password !== null) {
			$this->user_password = db_escape(trim($this->user_password));
		}
		// TODO MORE
		return null; // object is ok
	}

	public function store(CAppUI $AppUI = null) {
		global $AppUI;

        $msg = $this->check();
		if ($msg) {
			return get_class($this) . '::store-check failed';
		}
		$q = new w2p_Database_Query;
        
		if ($this->user_id) {
			// save the old password
			$perm_func = 'updateLogin';
			$q->addTable('users');
			$q->addQuery('user_password');
			$q->addWhere('user_id = ' . $this->user_id);
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
			$q->addWhere('upr.pref_user = ' . $this->user_id);
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

	public function delete($oid = null) {
		global $AppUI;

		$id = (int)$this->user_id;
		//check if the user is related to anything and disallow deletion if he is.
		//companies: is he a owner of any company?
		$q = new w2p_Database_Query;
		$q->addQuery('count(company_id)');
		$q->addTable('companies');
		$q->addWhere('company_owner = ' . $id);
		$result = $q->loadResult();
		$q->clear();
		if ($result) {
			return $AppUI->_('Can not Delete Because This User has') . ' ' . $result . ' ' . $AppUI->_('Companies') . ' ' . $AppUI->_('where he is') . ' ' .$AppUI->_('Owner') . '. ' . $AppUI->_('If you just want this user not to log in consider removing all his Roles. That would make the user Inactive.');
		}
		//departments: is he a owner of any department?
		$q = new w2p_Database_Query;
		$q->addQuery('count(dept_id)');
		$q->addTable('departments');
		$q->addWhere('dept_owner = ' . $id);
		$result = $q->loadResult();
		$q->clear();
		if ($result) {
			return $AppUI->_('Can not Delete Because This User has') . ' ' . $result . ' ' . $AppUI->_('Departments') . ' ' . $AppUI->_('where he is') . ' ' .$AppUI->_('Owner') . '. ' . $AppUI->_('If you just want this user not to log in consider removing all his Roles. That would make the user Inactive.');
		}
		//events: is he a owner of any event?
		$q = new w2p_Database_Query;
		$q->addQuery('count(event_id)');
		$q->addTable('events');
		$q->addWhere('event_owner = ' . $id);
		$result = $q->loadResult();
		$q->clear();
		if ($result) {
			return $AppUI->_('Can not Delete Because This User has') . ' ' . $result . ' ' . $AppUI->_('Events') . ' ' . $AppUI->_('where he is') . ' ' .$AppUI->_('Owner') . '. ' . $AppUI->_('If you just want this user not to log in consider removing all his Roles. That would make the user Inactive.');
		}
		//files: is he a owner of any file?
		$q = new w2p_Database_Query;
		$q->addQuery('count(file_id)');
		$q->addTable('files');
		$q->addWhere('file_owner = ' . $id);
		$result = $q->loadResult();
		$q->clear();
		if ($result) {
			return $AppUI->_('Can not Delete Because This User has') . ' ' . $result . ' ' . $AppUI->_('Files') . ' ' . $AppUI->_('where he is') . ' ' .$AppUI->_('Owner') . '. ' . $AppUI->_('If you just want this user not to log in consider removing all his Roles. That would make the user Inactive.');
		}
		//forums: is he a owner of any forum?
		$q = new w2p_Database_Query;
		$q->addQuery('count(forum_id)');
		$q->addTable('forums');
		$q->addWhere('forum_owner = ' . $id);
		$result = $q->loadResult();
		$q->clear();
		if ($result) {
			return $AppUI->_('Can not Delete Because This User has') . ' ' . $result . ' ' . $AppUI->_('Forums') . ' ' . $AppUI->_('where he is') . ' ' .$AppUI->_('Owner') . '. ' . $AppUI->_('If you just want this user not to log in consider removing all his Roles. That would make the user Inactive.');
		}
		//forums: is he a moderator of any forum?
		$q = new w2p_Database_Query;
		$q->addQuery('count(forum_id)');
		$q->addTable('forums');
		$q->addWhere('forum_moderated = ' . $id);
		$result = $q->loadResult();
		$q->clear();
		if ($result) {
			return $AppUI->_('Can not Delete Because This User has') . ' ' . $result . ' ' . $AppUI->_('Forums') . ' ' . $AppUI->_('where he is') . ' ' .$AppUI->_('Forum Moderator') . '. ' . $AppUI->_('If you just want this user not to log in consider removing all his Roles. That would make the user Inactive.');
		}
		//forums: is he a message creator on any forum?
		$q = new w2p_Database_Query;
		$q->addQuery('count(message_id)');
		$q->addTable('forum_messages');
		$q->addWhere('message_author = ' . $id);
		$result = $q->loadResult();
		$q->clear();
		if ($result) {
			return $AppUI->_('Can not Delete Because This User has') . ' ' . $result . ' ' . $AppUI->_('Forum Messages') . ' ' . $AppUI->_('where he is') . ' ' .$AppUI->_('Author') . '. ' . $AppUI->_('If you just want this user not to log in consider removing all his Roles. That would make the user Inactive.');
		}
		//forums: is he a message creator on any forum?
		$q = new w2p_Database_Query;
		$q->addQuery('count(message_id)');
		$q->addTable('forum_messages');
		$q->addWhere('message_editor = ' . $id);
		$result = $q->loadResult();
		$q->clear();
		if ($result) {
			return $AppUI->_('Can not Delete Because This User has') . ' ' . $result . ' ' . $AppUI->_('Forum Messages') . ' ' . $AppUI->_('where he is') . ' ' .$AppUI->_('Editor') . '. ' . $AppUI->_('If you just want this user not to log in consider removing all his Roles. That would make the user Inactive.');
		}
		//links: is he a owner of any link?
		$q = new w2p_Database_Query;
		$q->addQuery('count(link_id)');
		$q->addTable('links');
		$q->addWhere('link_owner = ' . $id);
		$result = $q->loadResult();
		$q->clear();
		if ($result) {
			return $AppUI->_('Can not Delete Because This User has') . ' ' . $result . ' ' . $AppUI->_('Links') . ' ' . $AppUI->_('where he is') . ' ' .$AppUI->_('Owner') . '. ' . $AppUI->_('If you just want this user not to log in consider removing all his Roles. That would make the user Inactive.');
		}
		//projects: is he related to any project?
		$q = new w2p_Database_Query;
		$q->addQuery('count(project_id)');
		$q->addTable('projects');
		$q->addWhere('(project_owner = ' . $id . ' OR project_creator = ' . $id . ' OR project_updator = ' . $id . ')');
		$result = $q->loadResult();
		$q->clear();
		if ($result) {
			return $AppUI->_('Can not Delete Because This User has') . ' ' . $result . ' ' . $AppUI->_('Projects') . ' ' . $AppUI->_('where he is') . ' ' .$AppUI->_('Owner, Creator or Updator') . '. ' . $AppUI->_('If you just want this user not to log in consider removing all his Roles. That would make the user Inactive.');
		}
		//tasks: is he related to any task?
		$q = new w2p_Database_Query;
		$q->addQuery('count(task_id)');
		$q->addTable('tasks');
		$q->addWhere('(task_owner = ' . $id . ' OR task_creator = ' . $id . ' OR task_updator = ' . $id . ')');
		$result = $q->loadResult();
		$q->clear();
		if ($result) {
			return $AppUI->_('Can not Delete Because This User has') . ' ' . $result . ' ' . $AppUI->_('Tasks') . ' ' . $AppUI->_('where he is') . ' ' .$AppUI->_('Owner, Creator or Updator') . '. ' . $AppUI->_('If you just want this user not to log in consider removing all his Roles. That would make the user Inactive.');
		}
		//events: is he related to any event?
		$q = new w2p_Database_Query;
		$q->addQuery('count(event_id)');
		$q->addTable('user_events');
		$q->addWhere('user_id = ' . $id);
		$result = $q->loadResult();
		$q->clear();
		if ($result) {
			return $AppUI->_('Can not Delete Because This User has') . ' ' . $result . ' ' . $AppUI->_('Events') . ' ' . $AppUI->_('where he is') . ' ' .$AppUI->_('Attendee') . '. ' . $AppUI->_('If you just want this user not to log in consider removing all his Roles. That would make the user Inactive.');
		}
		//tasks: is he related to any event?
		$q = new w2p_Database_Query;
		$q->addQuery('count(task_id)');
		$q->addTable('user_tasks');
		$q->addWhere('user_id = ' . $id);
		$result = $q->loadResult();
		$q->clear();
		if ($result) {
			return $AppUI->_('Can not Delete Because This User has') . ' ' . $result . ' ' . $AppUI->_('Tasks') . ' ' . $AppUI->_('where he is') . ' ' .$AppUI->_('Assignee') . '. ' . $AppUI->_('If you just want this user not to log in consider removing all his Roles. That would make the user Inactive.');
		}
		//tasks: is he related to any pins?
		$q = new w2p_Database_Query;
		$q->addQuery('count(task_id)');
		$q->addTable('user_task_pin');
		$q->addWhere('user_id = ' . $id);
		$result = $q->loadResult();
		$q->clear();
		if ($result) {
			return $AppUI->_('Can not Delete Because This User has') . ' ' . $result . ' ' . $AppUI->_('Tasks') . ' ' . $AppUI->_('pinned') . '. ' . $AppUI->_('If you just want this user not to log in consider removing all his Roles. That would make the user Inactive.');
		}

		$result = parent::delete($oid);
		if (!$result) {
			$acl = &$GLOBALS['AppUI']->acl();
			$acl->deleteLogin($id);
			$q = new w2p_Database_Query;
			$q->setDelete('user_preferences');
			$q->addWhere('pref_user = ' . $id);
			$q->exec();
			$q->clear();
		}
		return $result;
	}

    public function hook_search()
    {
        $search['table'] = 'users';
        $search['table_module'] = 'admin';
        $search['table_key'] = 'user_id'; // primary key in searched table
        $search['table_link'] = 'index.php?m=admin&a=viewuser&user_id='; // first part of link
        $search['table_title'] = 'Users';
        $search['table_orderby'] = 'user_username';
        $search['search_fields'] = array('user_username', 'user_signature');
        $search['display_fields'] = $search['search_fields'];

        return $search;
    }

	public function getAllowedRecords($uid, $fields = '*', $orderby = '', $index = null, $extra = null) {
		return w2PgetUsers();
	}

	/*
	 * DEPRECATED
	 */
	public function fullLoad($userId) {
		$this->loadFull($userId);
	}

	public function loadFull($userId) {
		$q = new w2p_Database_Query();
		$q->addTable('users', 'u');
		$q->addQuery('u.*');
		$q->addQuery('con.contact_email AS user_email');
		$q->addQuery('uf.feed_token');
		$q->addQuery('con.*, company_id, company_name, dept_name, dept_id');
		$q->addJoin('contacts', 'con', 'user_contact = contact_id', 'inner');
		$q->addJoin('companies', 'com', 'contact_company = company_id');
		$q->addJoin('departments', 'dep', 'dept_id = contact_department');
		$q->addJoin('user_feeds', 'uf', 'feed_user = u.user_id');
		$q->addWhere('u.user_id = ' . (int) $userId);

		$q->loadObject($this, true, false);
	}
	
    public function hook_cron() {
		$q = new w2p_Database_Query();
        $q->setDelete('sessions');
        $q->addWhere("session_user ='' OR session_user IS NULL");
        $q->exec();
        $q->clear();

        return true;
    }

	public function validatePassword($userId, $password) {
		$q = new w2p_Database_Query();
		$q->addTable('users');
		$q->addQuery('user_id');
		$q->addWhere('user_password = \'' . md5($password) . '\'');
		$q->addWhere('user_id = ' . (int) $userId);

		return ($q->loadResult() == $userId);
	}

	public static function getUserIdByToken($token) {
		$q = new w2p_Database_Query();
		$q->addQuery('feed_user');
		$q->addTable('user_feeds');
		$q->addWhere("feed_token = '$token'");
		$userId = $q->loadResult();

		return $userId;
	}
	
	public static function generateUserToken($userId, $token = '') {
		$q = new w2p_Database_Query();
		$q->setDelete('user_feeds');
		$q->addWhere('feed_user = ' . $userId);
		$q->addWhere("feed_token = '$token'");
		$q->exec();
		$q->clear();

		$token = md5(time().$userId.$token.time());
		$q->addTable('user_feeds');
		$q->addInsert('feed_user', $userId);
		$q->addInsert('feed_token', $token);
		$q->exec();

		return true;
	}
	
	public static function getFirstLetters() {
		$letters = '';

		$q = new w2p_Database_Query();
		$q->addTable('users', 'u');
		$q->addQuery('DISTINCT SUBSTRING(user_username, 1, 1) as L');
		$arr = $q->loadList();

		foreach ($arr as $L) {
			$letters .= $L['L'];
		}
		return strtoupper($letters);
	}
	
	public static function exists($username) {
		$q = new w2p_Database_Query();
		$q->addTable('users', 'u');
		$q->addQuery('user_username');
		$q->addWhere("user_username = '$username'");
		$users = $q->loadList();

		return (count($users) > 0) ? true : false;
	}

	public static function getUserDeptId($user_id) {
		$q = new w2p_Database_Query;
		$q->addQuery('con.contact_department');
		$q->addTable('users', 'u');
		$q->addJoin('contacts', 'con', 'user_contact = contact_id', 'inner');
		$q->addWhere('u.user_id = ' . (int)$user_id);
		$user_dept = $q->loadColumn();
		$q->clear();		

		return $user_dept;
	}

	public static function getLogs($userId, $startDate, $endDate) {
		$q = new w2p_Database_Query();
		$q->addTable('user_access_log', 'ual');
		$q->addTable('users', 'u');
		$q->addTable('contacts', 'c');
		$q->addQuery('ual.*, u.*, c.*');
		$q->addWhere('ual.user_id = u.user_id');
		$q->addWhere('user_contact = contact_id ');
		if ($userId > 0) {
			$q->addWhere('ual.user_id = ' . (int) $userId);
		}
		$q->addWhere("ual.date_time_in  >= '$startDate'");
		$q->addWhere("ual.date_time_out <= '$endDate'");
		$q->addGroup('ual.date_time_last_action DESC');

		return $q->loadList();
	}
	
	public function getFullUserName() {
		$q = new w2p_Database_Query;
		$q->addTable('contacts', 'c');
		$q->addQuery('c.*');
		$q->addWhere('contact_id = ' . (int)$this->user_contact);
		$res = $q->loadList();
		
		if (count($res) == 1) {
			return $res[0]['contact_first_name'] . ' ' . $res[0]['contact_last_name']; 	

		}
		
		return $this->user_username;
	}

	/**Function that checks if a user is active or not (i.e. are they able to login to the system)
	 * @param int $user_id id of the use to check
	 * @return boolean	true if active, false o/w
	 */
	public static function isUserActive($user_id) {
		global $AppUI;
		$perms = &$AppUI->acl();
		
		return $perms->isUserPermitted($user_id);
	}
	
	public static function getUserList() {
		global $AppUI;
		
		$q = new w2p_Database_Query;  		
        $q->addQuery('users.user_contact,users.user_id,co.contact_first_name,co.contact_last_name,co.contact_id');
        $q->addTable('users');
        $q->addJoin('contacts','co','co.contact_id = users.user_contact','inner');
        $q->addWhere('users.user_contact = ' . $AppUI->user_id . ' or (' . getPermsWhereClause('companies', 'user_company') . ')' );
        $q->addOrder('contact_first_name, contact_last_name');
        $result = $q->loadList();
        $retres = array();

        foreach ($result as $user) {
            if (self::isUserActive($user["user_id"])) {
                $retres[] = $user;
            }
        }
		return $retres;  	
  }
}