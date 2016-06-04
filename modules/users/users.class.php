<?php
/**
 * @package     web2project\modules\core
 *
 * @todo    refactor modules/admin to modules/users
 * @todo    new query object
 * @todo    refactor static methods
 */

class CUser extends w2p_Core_BaseObject
{

    public $user_id = null;
    public $user_username = null;
    public $user_password = null;
    public $user_parent = null;
    public $user_type = null;
    public $user_contact = null;
    public $user_signature = null;

    protected $externally_created_user = false;
    protected $authenticator = null;

    private $perm_func = null;    

    public function __construct()
    {
        parent::__construct('users', 'user_id');
        
        $this->authenticator = new w2p_Authenticators_SQL();
    }

    public function isValid()
    {
        $baseErrorMsg = get_class($this) . '::store-check failed - ';

        if ('' == trim($this->user_username)) {
            $this->_error['user_username'] = $baseErrorMsg . 'username is not set';
        }
        if (!$this->user_id && '' == trim($this->user_password)) {
            $this->_error['user_password'] = $baseErrorMsg . 'user password is not set';
        }
        if (!$this->user_id && $this->user_exists($this->user_username)) {
            $this->_error['user_exists'] = $baseErrorMsg . 'this user already exists';
        }

        return (count($this->_error)) ? false : true;
    }

    protected function  hook_preCreate() {
        $this->perm_func = 'addLogin';
        $this->user_password = $this->authenticator->hashPassword($this->user_password);

        parent::hook_preCreate();
    }

    protected function  hook_preUpdate() {
        $this->perm_func = 'updateLogin';
        $tmpUser = new CUser();
        $tmpUser->overrideDatabase($this->_query);
        $tmpUser->load($this->user_id);

        if ('' == trim($this->user_password)) {
            $this->user_password = $tmpUser->user_password;
        } elseif ($tmpUser->user_password != $this->authenticator->hashPassword($this->user_password)) {
            $this->user_password = $this->authenticator->hashPassword($this->user_password);
        } else {
            $this->user_password = $tmpUser->user_password;
        }

        parent::hook_preUpdate();
    }

    public function store($notUsed = null, $externally_created_user = false)
    {
        $this->externally_created_user = $externally_created_user;

        if (!$this->isValid()) {
            return false;
        }

        return parent::store();
    }

    protected function hook_postStore()
    {
        $this->_perms->{$this->perm_func}($this->user_id, $this->user_username);

        $q = $this->_getQuery();
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

            foreach ($w2prefs as $notUsed => $w2prefsvalue) {
                $q->addTable('user_preferences', 'up');
                $q->addInsert('pref_user', $this->user_id);
                $q->addInsert('pref_name', $w2prefsvalue['pref_name']);
                $q->addInsert('pref_value', $w2prefsvalue['pref_value']);
                $q->exec();
                $q->clear();
            }
        }

        parent::hook_postStore();
    }

    /**
     * A user can always view their own information.
     *
     * @return bool
     */
    public function canView()
    {
        return (parent::canView() || $this->user_id == $this->_AppUI->user_id);
    }

    /**
     * A user can always edit their own information.
     *
     * @return bool
     */
    public function canEdit()
    {
        $result = false;
        if (parent::canEdit() || $this->user_id == $this->_AppUI->user_id) {
            $result = true;
        }
        return $result;
    }

    public function canCreate()
    {
        if ($this->user_exists($this->user_username)) {
            $this->_error['canCreate'] = 'A user with this username already exists';
            return false;
         }

        if (parent::canCreate() || ($this->externally_created_user && 'true' == w2PgetConfig('activate_external_user_creation', 'false'))) {
            return true;
        }

        return false;
    }

    public function canDelete($notUsed = null, $notUsed2 = null, $notUsed3 = null)
    {
        $tables[] = array('label' => 'Company Owner', 'name' => 'companies', 'idfield' => 'company_id', 'joinfield' => 'company_owner');
        $tables[] = array('label' => 'Department Owner', 'name' => 'departments', 'idfield' => 'dept_id', 'joinfield' => 'dept_owner');
        $tables[] = array('label' => 'Project Owner', 'name' => 'projects', 'idfield' => 'project_id', 'joinfield' => 'project_owner');
        $tables[] = array('label' => 'Project Creator', 'name' => 'projects', 'idfield' => 'project_id', 'joinfield' => 'project_creator');
        $tables[] = array('label' => 'Project Updator', 'name' => 'projects', 'idfield' => 'project_id', 'joinfield' => 'project_updator');
        $tables[] = array('label' => 'Task Owner', 'name' => 'tasks', 'idfield' => 'task_id', 'joinfield' => 'task_owner');
        $tables[] = array('label' => 'Task Creator', 'name' => 'tasks', 'idfield' => 'task_id', 'joinfield' => 'task_creator');
        $tables[] = array('label' => 'Task Updator', 'name' => 'tasks', 'idfield' => 'task_id', 'joinfield' => 'task_updator');
        $tables[] = array('label' => 'Task Assignee', 'name' => 'user_tasks', 'idfield' => 'task_id', 'joinfield' => 'user_id');
        $tables[] = array('label' => 'Event Owner', 'name' => 'events', 'idfield' => 'event_id', 'joinfield' => 'event_owner');
        $tables[] = array('label' => 'Event Attendee', 'name' => 'user_events', 'idfield' => 'event_id', 'joinfield' => 'user_id');
        $tables[] = array('label' => 'File Owner', 'name' => 'files', 'idfield' => 'file_id', 'joinfield' => 'file_owner');
        $tables[] = array('label' => 'Forum Owner', 'name' => 'forums', 'idfield' => 'forum_id', 'joinfield' => 'forum_owner');
        $tables[] = array('label' => 'Forum Moderator', 'name' => 'forums', 'idfield' => 'forum_id', 'joinfield' => 'forum_moderated');
        $tables[] = array('label' => 'Forum Message Author', 'name' => 'forum_messages', 'idfield' => 'message_id', 'joinfield' => 'message_author');
        $tables[] = array('label' => 'Forum Message Editor', 'name' => 'forum_messages', 'idfield' => 'message_id', 'joinfield' => 'message_editor');
        $tables[] = array('label' => 'Link Owner', 'name' => 'links', 'idfield' => 'link_id', 'joinfield' => 'link_owner');

        return parent::canDelete('', null, $tables);
    }

    protected function hook_preDelete()
    {
        $this->_perms->deleteLogin($this->user_id);

        $q = $this->_getQuery();
        $q->setDelete('user_preferences');
        $q->addWhere('pref_user = ' . $this->user_id);
        $q->exec();
    }

    public function hook_search()
    {
        $search['table'] = 'users';
        $search['table_module'] = 'users';
        $search['table_key'] = 'user_id'; // primary key in searched table
        $search['table_link'] = 'index.php?m=users&a=view&user_id='; // first part of link
        $search['table_title'] = 'Users';
        $search['table_orderby'] = 'user_username';
        $search['search_fields'] = array('user_username', 'user_signature');
        $search['display_fields'] = $search['search_fields'];

        return $search;
    }

    public function getAllowedRecords($uid, $fields = '*', $orderby = '', $index = null, $extra = null, $unused = null)
    {
        return w2PgetUsers();
    }

    public function loadFull($userId)
    {
        $q = $this->_getQuery();
        $q->addTable('users', 'u');
        $q->addQuery('u.*');
        $q->addQuery('uf.feed_token');
        $q->addQuery('con.*, company_id, company_name, dept_name, dept_id');
        $q->addJoin('contacts', 'con', 'user_contact = contact_id', 'inner');
        $q->addJoin('companies', 'com', 'contact_company = company_id');
        $q->addJoin('departments', 'dep', 'dept_id = contact_department');
        $q->addJoin('user_feeds', 'uf', 'feed_user = u.user_id');
        $q->addWhere('u.user_id = ' . (int) $userId);

        $q->loadObject($this, true, false);
    }

    public function hook_cron()
    {
        $q = $this->_getQuery();
        $q->setDelete('sessions');
        $q->addWhere("session_user ='' OR session_user IS NULL");
        $q->exec();
        $q->clear();

        return true;
    }

    public function validatePassword($userId, $password)
    {
        $hash = $this->authenticator->hashPassword($password);
        
        $users = $this->loadAll('user_id', 'user_password = \'' . $hash . '\' AND user_id = ' . (int) $userId);

        return isset($users[$userId]);
    }

    public function getIdByToken($token)
    {
        $q = $this->_getQuery();
        $q->addQuery('feed_user');
        $q->addTable('user_feeds');
        $q->addWhere("feed_token = '$token'");
        $userId = $q->loadResult();

        return $userId;
    }

    public function getIdByContactId($contactId)
    {
        $users = $this->loadAll('user_id', 'user_contact = ' . (int) $contactId);

        return (string) $users[$contactId]['user_id'];
    }

    public function generateToken($userId, $token = '')
    {
        if (!$userId) {
            return false;
        }
        $q = $this->_getQuery();
        $q->setDelete('user_feeds');
        $q->addWhere('feed_user = ' . $userId);
        $q->addWhere("feed_token = '$token'");
        $q->exec();

        $token = md5(time() . $userId . $token . time());
        $q = $this->_getQuery();
        $q->addTable('user_feeds');
        $q->addInsert('feed_user', $userId);
        $q->addInsert('feed_token', $token);
        $q->exec();

        return true;
    }

    public function getLetters()
    {
        $q = $this->_getQuery();
        $q->addTable('users', 'u');
        $q->addQuery('DISTINCT SUBSTRING(user_username, 1, 1) as L');
        $arr = $q->loadList();

        $letters = implode('', $arr);

        return strtoupper($letters);
    }

    public function user_exists($username)
    {
        $users = $this->loadAll('user_id', "user_username = '$username'");

        return (count($users) > 0) ? true : false;
    }

    public function getDeptId($userId)
    {
        $this->loadFull($userId);

        return $this->contact_department;
    }

    public function getLogList($userId, $startDate, $endDate)
    {
        $q = $this->_getQuery();
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

    public function getFullUserName()
    {
        $q = $this->_getQuery();
        $q->addTable('contacts', 'c');
        $q->addQuery('c.*');
        $q->addWhere('contact_id = ' . (int) $this->user_contact);
        $res = $q->loadList();

        if (count($res) == 1) {
            return $res[0]['contact_display_name'];
        }

        return $this->user_username;
    }

    /**
     * * Function that checks if a user is active or not (i.e. are they able to login to the system)
     * @param $userId
     * @return mixed
     */
    public function isActive($userId)
    {
        return $this->_perms->isUserPermitted($userId);
    }

    public function getList()
    {
        $q = $this->_getQuery();
        $q->addQuery('users.user_contact,users.user_id,co.contact_first_name,co.contact_last_name,co.contact_id');
        $q->addTable('users');
        $q->addJoin('contacts', 'co', 'co.contact_id = users.user_contact', 'inner');
        $q->addWhere('users.user_contact = ' . $this->_AppUI->user_id);
        $q->addOrder('contact_first_name, contact_last_name');
        $result = $q->loadList();
        $retres = array();

        foreach ($result as $user) {
            if ($this->isActive($user["user_id"])) {
                $retres[] = $user;
            }
        }
        return $retres;
    }

    /**
     * @deprecated
     */
    public static function getUserList()
    {
        trigger_error("The CUser::getUserList static method has been deprecated in 3.1 and will be removed in v4.0. Please use CUser->getList instead.", E_USER_NOTICE );
        $user = new CUser();
        return $user->getList();
    }

    /**
     * @deprecated
     */
    public function fullLoad($userId)
    {
        trigger_error("The fullLoad method has been deprecated and will be removed by v4.0.", E_USER_NOTICE);

        $this->loadFull($userId);
    }

    /**
     * @deprecated
     */
    public static function getUserIdByToken($token)
    {
        trigger_error("CUser::getUserIdByToken has been deprecated in v3.0 and will be removed by v4.0. Please use CUser->getIdByToken() instead.", E_USER_NOTICE);
        $user = new CUser();

        return $user->getIdByToken($token);
    }

    /**
     * @deprecated
     */
    public static function getUserIdByContactID($contactId)
    {
        trigger_error("CUser::getUserIdByContactID has been deprecated in v3.0 and will be removed by v4.0. Please use CUser->getIdByContactId() instead.", E_USER_NOTICE);
        $user = new CUser();

        return $user->getIdByContactId($contactId);
    }

    /**
     * @deprecated
     */
    public static function getUserDeptId($user_id)
    {
        trigger_error("CUser::getUserDeptId has been deprecated in v3.0 and will be removed by v4.0. Please use CUser->getDeptId() instead.", E_USER_NOTICE);
        $user = new CUser();

        return $user->getDeptId($user_id);
    }
    public static function exists($username)
    {
        trigger_error("CUser::exists has been deprecated in v3.0 and will be removed by v4.0. Please use CUser->user_exists() instead.", E_USER_NOTICE);

        $user = new CUser();
        return $user->user_exists($username);
    }
    /**
     * @deprecated
     */
    public static function getFirstLetters()
    {
        trigger_error("CUser::getFirstLetters has been deprecated in v3.1 and will be removed by v4.0. Please use CUser->getLetters() instead.", E_USER_NOTICE);
        $user = new CUser();
        return $user->getLetters();
    }

    /**
     * @deprecated
     */
    public static function generateUserToken($userId, $token = '')
    {
        trigger_error("CUser::generateUserToken has been deprecated in v3.0 and will be removed by v4.0. Please use CUser->generateToken() instead.", E_USER_NOTICE);
        $user = new CUser();

        return $user->generateToken($userId, $token);
    }

    /**
     * @deprecated
     */
    public static function getLogs($userId, $startDate, $endDate)
    {
        trigger_error("CUser::getLogs has been deprecated in v3.0 and will be removed by v4.0. Please use CUser->getLogList() instead.", E_USER_NOTICE);
        $user = new CUser();

        return $user->getLogList($userId, $startDate, $endDate);
    }

    /**
     * @deprecated
     */
    public static function isUserActive($user_id)
    {
        trigger_error("CUser::isUserActive has been deprecated in v3.0 and will be removed by v4.0. Please use CUser->isActive() instead.", E_USER_NOTICE);
        $user = new CUser();

        return $user->isActive($user_id);
    }
}