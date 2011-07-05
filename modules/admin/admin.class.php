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
        $errorArray = array();
        $baseErrorMsg = get_class($this) . '::store-check failed - ';

		if (!$this->user_id && '' == trim($this->user_password)) {
            $errorArray['user_password'] = $baseErrorMsg . 'user password is not set';
		}
        if (!$this->user_id && CUser::exists($this->user_username)) {
            $errorArray['user_exists'] = $baseErrorMsg . 'this user already exists';
        }

		return $errorArray;
	}

	public function store(CAppUI $AppUI = null) {
		global $AppUI;
        $perms = $AppUI->acl();
        $stored = false;

        $this->_error = $this->check();
        if (count($this->_error)) {
            return false;
        }

        if ($this->user_id && $perms->checkModuleItem('users', 'edit', $this->user_id)) {
            $perm_func = 'updateLogin';
            $tmpUser = new CUser();
            $tmpUser->load($this->user_id);

            if ('' == trim($this->user_password)) {
                $this->user_password = $tmpUser->user_password;
            } elseif ($tmpUser->user_password != md5($this->user_password)) {
                $this->user_password = md5($this->user_password);
            } else {
                $this->user_password = $tmpUser->user_password;
            }

            if (($msg = parent::store())) {
                $this->_error = $msg;
                return false;
            }
            $stored = true;
        }

		# santosdiez
		# There's no way of checking permissions if we are adding a user! Just need to allow it
        // if (0 == $this->user_id && $perms->checkModuleItem('users', 'add')) {
		if (0 == $this->user_id) {
		# /santosdiez
            $perm_func = 'addLogin';
            $this->user_password = md5($this->user_password);

            if (($msg = parent::store())) {
                $this->_error = $msg;
                return false;
            }

            $stored = true;
        }

        if ($stored) {
            $perms->$perm_func($this->user_id, $this->user_username);

            $q = new w2p_Database_Query;
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

            return $stored;
        }

        return $stored;
	}

    public function canDelete() {
        $tables[] = array('label' => 'Companies', 'name' => 'companies', 'idfield' => 'company_id', 'joinfield' => 'company_owner');
        $tables[] = array('label' => 'Departments', 'name' => 'departments', 'idfield' => 'dept_id', 'joinfield' => 'dept_owner');
        $tables[] = array('label' => 'Project Owner', 'name' => 'projects', 'idfield' => 'project_id', 'joinfield' => 'project_owner');
        //$tables[] = array('label' => 'Project Creator', 'name' => 'projects', 'idfield' => 'project_id', 'joinfield' => 'project_creator');
        //$tables[] = array('label' => 'Project Updator', 'name' => 'projects', 'idfield' => 'project_id', 'joinfield' => 'project_updator');
        $tables[] = array('label' => 'Task Owner', 'name' => 'tasks', 'idfield' => 'task_id', 'joinfield' => 'task_owner');
        //$tables[] = array('label' => 'Task Creator', 'name' => 'tasks', 'idfield' => 'task_id', 'joinfield' => 'task_creator');
        //$tables[] = array('label' => 'Task Updator', 'name' => 'tasks', 'idfield' => 'task_id', 'joinfield' => 'task_updator');
        //$tables[] = array('label' => 'Task Assignee', 'name' => 'user_tasks', 'idfield' => 'task_id', 'joinfield' => 'user_id');
        $tables[] = array('label' => 'Events', 'name' => 'events', 'idfield' => 'event_id', 'joinfield' => 'event_owner');
        //$tables[] = array('label' => 'Event Attendees', 'name' => 'user_events', 'idfield' => 'event_id', 'joinfield' => 'user_id');
        $tables[] = array('label' => 'Files', 'name' => 'files', 'idfield' => 'file_id', 'joinfield' => 'file_owner');
        $tables[] = array('label' => 'Forum Owner', 'name' => 'forums', 'idfield' => 'forum_id', 'joinfield' => 'forum_owner');
        //$tables[] = array('label' => 'Forum Moderator', 'name' => 'forums', 'idfield' => 'forum_id', 'joinfield' => 'forum_moderated');
        $tables[] = array('label' => 'Forum Messages', 'name' => 'forum_messages', 'idfield' => 'message_id', 'joinfield' => 'message_author');
        //$tables[] = array('label' => 'Forum Message Editor', 'name' => 'forum_messages', 'idfield' => 'message_id', 'joinfield' => 'message_editor');
        $tables[] = array('label' => 'Links', 'name' => 'links', 'idfield' => 'link_id', 'joinfield' => 'link_owner');

		return parent::canDelete($msg, $this->user_id, $tables);
    }

	public function delete(CAppUI $AppUI = null) {
		global $AppUI;
        $perms = $AppUI->acl();
        $canDelete = (int) $this->canDelete();
        $this->_error = array();

        if ($perms->checkModuleItem('users', 'delete', $this->user_id) && $canDelete) {

            $perms->deleteLogin($this->user_id);

			$q = new w2p_Database_Query;
			$q->setDelete('user_preferences');
			$q->addWhere('pref_user = ' . $this->user_id);
			$q->exec();

            if ($msg = parent::delete()) {
                return $msg;
            }
            return true;
        }

        return false;
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