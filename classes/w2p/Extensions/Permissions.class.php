<?php
/**
 * Permissions system extends the phpgacl class.  Very few changes have
 * been made, however the main one is to provide the database details from
 * the main w2P environment.
 *
 * Extend the gacl_api class.  There is an argument to separate this
 * into a gacl and gacl_api class on the premise that normal activity
 * only needs the functions in gacl, but it would appear that this is
 * not so for w2P, which tends to require reverse lookups rather than
 * just forward ones (i.e. looking up who is allowed to do x, rather
 * than is x allowed to do y).
 *
 * @package     web2project\extensions
 */

class w2p_Extensions_Permissions extends gacl_api
{

    public $config_file = '';
    public $_db_acl_prefix = 'gacl_';
    protected $_query;

    public function __construct($opts = null)
    {
        global $db;

        if (!is_array($opts)) {
            $opts = array();
        }
        $opts['db_type'] = w2PgetConfig('dbtype');
        $opts['db_host'] = w2PgetConfig('dbhost');
        $opts['db_user'] = w2PgetConfig('dbuser');
        $opts['db_password'] = w2PgetConfig('dbpass');
        $opts['db_name'] = w2PgetConfig('dbname');
        $opts['db_table_prefix'] = w2PgetConfig('dbprefix') . $this->_db_acl_prefix;
        $opts['db'] = $db;
        // We can add an ADODB instance instead of the database
        // connection details.  This might be worth looking at in
        // the future.
        if (w2PgetConfig('debug', 0) > 10) {
            $this->_debug = true;
        }
        parent::gacl_api($opts);
        $this->_query = new w2p_Database_Query();
    }

    /**
     * Since Dependency injection isn't feasible due to the sheer number of
     *   calls to the above constructor, this is a way to hijack the current
     *   $this->_query and manipulate it however we want.
     *
     *   @param Object A database connection (real or mocked)
     */
    protected function _overrideDatabase($override)
    {
        $this->_query = $override;
    }

    public function checkLogin($login)
    {
        // Simple ARO<->ACO check, no AXO's required.
        $result = $this->acl_check('system', 'login', 'user', $login);
        //recalc the users permissions at login time:
        $recalc = $this->recalcPermissions($login);
        if (!$recalc) {
            dprint(__file__, __line__, 0, 'Failed to recalc Permissions');
        }
        return $result;
    }

    public function checkModule($module, $op, $userid = null)
    {
        if (!$userid) {
            $userid = $GLOBALS['AppUI']->user_id;
        }

        $result = $this->w2Pacl_check('application', $op, 'user', $userid, 'app', $module);
        return $result;
    }

    public function checkModuleItem($module, $op, $item = null, $userid = null)
    {
        if (!$userid) {
            $userid = $GLOBALS['AppUI']->user_id;
        }
        if (!$item) {
            return $this->checkModule($module, $op, $userid);
        }

        $result = $this->w2Pacl_query('application', $op, 'user', $userid, $module, $item);
        // If there is no acl_id then we default back to the parent lookup
        if (!$result || !$result['acl_id']) {
            dprint(__file__, __line__, 2, "checkModuleItem($module, $op, $userid) did not return a record");
            return false;
        }
        return $result['access'];
    }

    /**
     * This gets tricky and is there mainly for the compatibility layer
     * for getDeny functions.
     * If we get an ACL ID, and we get access = false, then the item is
     * actively denied.  Any other combination is a soft-deny (i.e. not
     * strictly allowed, but not actively denied.
     */
    public function checkModuleItemDenied($module, $op, $item, $user_id = null)
    {
        if (!$user_id) {
            $user_id = $GLOBALS['AppUI']->user_id;
        }
        $result = $this->w2Pacl_query('application', $op, 'user', $user_id, $module, $item);
        if (!$result || ($result['acl_id'] && !$result['access'])) {
            return true;
        } else {
            return false;
        }
    }

    public function addLogin($login, $username)
    {
        $res = $this->add_object('user', $username, $login, 1, 0, 'aro');

        if ((int) $res == 0) {
            dprint(__file__, __line__, 0, 'Failed to add user permission object');
        }
        $recalc = $this->recalcPermissions($login);
        if (!$recalc) {
            dprint(__file__, __line__, 0, 'Failed to recalc Permissions');
        }
        return $res;
    }

    public function updateLogin($login, $username)
    {
        $id = $this->get_object_id('user', $login, 'aro');
        if (!$id) {
            return $this->addLogin($login, $username);
        }
        // Check if the details have changed.
        list($notUsed, $notUsed2, $notUsed3, $oname, $notUsed4) = $this->get_object_data($id, 'aro');
        if ($oname != $username) {
            $res = $this->edit_object($id, 'user', $username, $login, 1, 0, 'aro');
            if (!$res) {
                dprint(__file__, __line__, 0, 'Failed to change user permission object');
            }
        }
        return $res;
    }

    public function deleteLogin($login)
    {
        $id = $this->get_object_id('user', $login, 'aro');
        if ($id) {
            $id = $this->del_object($id, 'aro', true);
        } else {
            //TODO: There was no permissions object to delete.. is this actuall an error?
            //dprint(__file__, __line__, 0, 'Failed to remove user permission object');
        }
        $recalc = $this->removePermissions($login);
        if (!$recalc) {
            dprint(__file__, __line__, 0, 'Failed to remove Permissions');
        }
        return $id;
    }

    public function addModule($mod, $modname)
    {
        $res = $this->add_object('app', $modname, $mod, 1, 0, 'axo');
        if ($res) {
            $res = $this->addGroupItem($mod);
        } else {
            dprint(__file__, __line__, 0, 'Failed to add module permission object');
        }
        $recalc = $this->recalcPermissions(null, null, null, $mod);
        if (!$recalc) {
            dprint(__file__, __line__, 0, 'Failed to recalc module Permissions');
        }
        return $res;
    }

    public function addModuleSection($mod)
    {
        $res = $this->add_object_section(ucfirst($mod) . ' Record', $mod, 0, 0, 'axo');
        if (!$res) {
            dprint(__file__, __line__, 0, 'Failed to add module permission section');
        }
        $recalc = $this->recalcPermissions(null, null, null, $mod);
        if (!$recalc) {
            dprint(__file__, __line__, 0, 'Failed to recalc module Permissions');
        }
        return $res;
    }

    public function addModuleItem($mod, $itemid, $itemdesc)
    {
        $res = $this->add_object($mod, $itemdesc, $itemid, 0, 0, 'axo');
        $recalc = $this->recalcPermissions(null, null, null, $mod);
        if (!$recalc) {
            dprint(__file__, __line__, 0, 'Failed to recalc module Permissions');
        }
        return $res;
    }

    public function addGroupItem($item, $group = 'all', $section = 'app', $type = 'axo')
    {
        if ($gid = $this->get_group_id($group, null, $type)) {
            $res = $this->add_group_object($gid, $section, $item, $type);
        }
        return $res;
    }

    public function deleteModule($mod)
    {
        $id = $this->get_object_id('app', $mod, 'axo');
        if ($id) {
            $this->deleteGroupItem($mod);
            $id = $this->del_object($id, 'axo', true);
        }
        if (!$id) {
            dprint(__file__, __line__, 0, 'Failed to remove module permission object');
        }
        $recalc = $this->removeModulePermissions($mod);
        if (!$recalc) {
            dprint(__file__, __line__, 0, 'Failed to recalc Permissions');
        }
        return $id;
    }

    public function deleteModuleSection($mod)
    {
        $id = $this->get_object_section_section_id(null, $mod, 'axo');
        if ($id) {
            $id = $this->del_object_section($id, 'axo', true);
        }
        if (!$id) {
            dprint(__file__, __line__, 0, 'Failed to remove module permission section');
        }
        $recalc = $this->recalcPermissions(null, null, null, $mod);
        if (!$recalc) {
            dprint(__file__, __line__, 0, 'Failed to recalc module Permissions');
        }
        return $id;
    }

    /*
     * * Deleting all module-associated entries from the phpgacl tables
     * * such as gacl_aco_maps, gacl_acl and gacl_aro_map
     * *
     * * @author 	gregorerhardt
     * * @date		20070927
     * * @cause		#2140
     * *
     * * @access 	public
     * * @param	string	module (directory) name
     * * @return
     */

    public function deleteModuleItems($mod)
    {
        // Declaring the return string
        $res = null;

        // Fetching module-associated ACL ID's
        $q = new w2p_Database_Query;
        $q->addTable('gacl_axo_map');
        $q->addQuery('acl_id');
        $q->addWhere('value = \'' . $mod . '\'');
        $acls = $q->loadHashList('acl_id');
        $q->clear();

        foreach ($acls as $acl => $notUsed) {
            // Deleting gacl_aco_map entries
            $q->setDelete('gacl_aco_map');
            $q->addWhere('acl_id = ' . $acl);
            if (!$q->exec()) {
                $res .= is_null($res) ? db_error() : "\n\t" . db_error();
            }
            $q->clear();

            // Deleting gacl_aro_map entries
            $q->setDelete('gacl_aro_map');
            $q->addWhere('acl_id = ' . $acl);
            if (!$q->exec()) {
                $res .= "\n\t" . db_error();
            }
            $q->clear();

            // Deleting gacl_aco_map entries
            $q->setDelete('gacl_acl');
            $q->addWhere('id = ' . $acl);
            if (!$q->exec()) {
                $res .= "\n\t" . db_error();
            }
            $q->clear();
        }

        $recalc = $this->recalcPermissions(null, null, null, $mod);
        if (!$recalc) {
            dprint(__file__, __line__, 0, 'Failed to recalc module Permissions');
        }
        // Returning null (no error) or database error message (error)
        return $res;
    }

    public function deleteGroupItem($item, $group = 'all', $section = 'app', $type = 'axo')
    {
        if ($gid = $this->get_group_id($group, null, $type)) {
            $res = $this->del_group_object($gid, $section, $item, $type);
        }
        return $res;
    }

    public function isUserPermitted($userid, $module = null)
    {
        if ($module) {
            return $this->checkModule($module, 'view', $userid);
        } else {
            //this checks if the user is able to login
            //return $this->checkLogin($userid);
            return $this->acl_check('system', 'login', 'user', $userid);
        }
    }

    public function getPermittedUsers($module = null)
    {
        // Not as pretty as I'd like, but we can do it reasonably well.
        // Check to see if we are allowed to see other users.
        // If not we can only see ourselves.
        global $AppUI;
        $userlist = array();

        $rows = w2PgetUsersList();
        foreach ($rows as $row) {
            if (($this->isUserPermitted($row['user_id'], $module)) || $row['user_id'] == $AppUI->user_id) {
                $userlist[$row['user_id']] = $row['contact_name'];
            }
        }
        //  Now format the userlist as an assoc array.
        return $userlist;
    }

    public function getItemACLs($module, $uid = null)
    {
        if (!$uid) {
            $uid = $GLOBALS['AppUI']->user_id;
        }
        // Grab a list of all acls that match the user/module, for which Deny permission is set.
        //Pedro A.: "user" is not the only thing in place for item ACLs anymore, need to search the Role Item ACLs too
        return $this->w2Psearch_acl('application', 'view', 'user', $uid, $module);
    }

    public function getUserACLs($uid = null)
    {
        if (!$uid) {
            $uid = $GLOBALS['AppUI']->user_id;
        }
        return $this->search_acl('application', false, 'user', $uid, null, false, false, false, false);
    }

    public function getRoleACLs($role_id)
    {
        $role = $this->getRole($role_id);
        return $this->search_acl('application', false, false, false, $role['name'], false, false, false, false);
    }

    public function getRole($role_id)
    {
        $data = $this->get_group_data($role_id);
        if ($data) {
            return array('id' => $data[0], 'parent_id' => $data[1], 'value' => $data[2], 'name' => $data[3], 'lft' => $data[4], 'rgt' => $data[5]);
        } else {
            return false;
        }
    }

    public function &getDeniedItems($module, $uid = null)
    {
        $items = array();
        if (!$uid) {
            $uid = $GLOBALS['AppUI']->user_id;
        }

        $acls = $this->getItemACLs($module, $uid);
        // If we get here we should have an array.
        if (is_array($acls)) {
            // Grab the item values
            foreach ($acls as $acl) {
                if ($acl['access'] == false) {
                    $items[] = $acl['item_id'];
                }
            }
        } else {
            dprint(__file__, __line__, 2, "getDeniedItems($module, $uid) - no ACL's match");
        }
        return $items;
    }

    // This is probably redundant.
    public function &getAllowedItems($module, $uid = null)
    {
        $items = array();
        if (!$uid) {
            $uid = $GLOBALS['AppUI']->user_id;
        }
        $acls = $this->getItemACLs($module, $uid);
        if (is_array($acls)) {
            foreach ($acls as $acl) {
                if ($acl['access'] == true) {
                    $items[] = $acl['item_id'];
                }
            }
        } else {
            dprint(__file__, __line__, 2, "getAllowedItems($module, $uid) - no ACL's match");
        }
        return $items;
    }

    // Copied from get_group_children in the parent class, this version returns
    // all of the fields, rather than just the group ids.  This makes it a bit
    // more efficient as it doesn't need the get_group_data call for each row.
    public function getChildren($group_id, $group_type = 'ARO', $recurse = 'NO_RECURSE')
    {

        switch (strtolower(trim($group_type))) {
            case 'axo':
                $group_type = 'axo';
                $table = $this->_db_acl_prefix . 'axo_groups';
                break;
            default:
                $group_type = 'aro';
                $table = $this->_db_acl_prefix . 'aro_groups';
        }

        if (empty($group_id)) {
            $this->debug_text("get_group_children(): ID ($group_id) is empty, this is required");
            return false;
        }

        $q = new w2p_Database_Query;
        $q->addTable($table, 'g1');
        $q->addQuery('g1.id, g1.name, g1.value, g1.parent_id');
        $q->addOrder('g1.value');

        switch (strtoupper($recurse)) {
            case 'RECURSE':
                $q->addJoin($table, 'g2', 'g2.lft<g1.lft AND g2.rgt>g1.rgt');
                $q->addWhere('g2.id=' . $group_id);
                break;
            default:
                $q->addWhere('g1.parent_id=' . $group_id);
        }

        $result = array();
        $q->exec();
        while ($row = $q->fetchRow()) {
            $result[] = array('id' => $row[0], 'name' => $row[1], 'value' => $row[2], 'parent_id' => $row[3]);
        }
        $q->clear();
        return $result;
    }

    public function insertRole($value, $name)
    {
        $role_parent = $this->get_group_id('role');
        $value = str_replace(' ', '_', $value);
        return $this->add_group($value, $name, $role_parent);
    }

    public function updateRole($id, $value, $name)
    {
        $res = $this->edit_group($id, $value, $name);
        $recalc = $this->recalcPermissions(null, null, $id);
        if (!$recalc) {
            dprint(__file__, __line__, 0, 'Failed to recalc Permissions');
        }
        return $res;
    }

    public function deleteRole($id)
    {
        $res = false;

        $data = $this->getRole($id);
        if (strpos($data['name'], 'admin') === false) {
            // Delete all of the group assignments before deleting group.
            $objs = $this->get_group_objects($id);
            foreach ($objs as $section => $value) {
                $this->del_group_object($id, $section, $value);
            }
            $res = $this->del_group($id, false);
            $recalc = $this->recalcPermissions(null, null, $id);
            if (!$recalc) {
                dprint(__file__, __line__, 0, 'Failed to recalc Permissions');
            }
        }
        return $res;
    }

    public function insertUserRole($role, $user)
    {
        // Check to see if the user ACL exists first.
        $id = $this->get_object_id('user', $user, 'aro');
        if (!$id) {
            $q = new w2p_Database_Query;
            $q->addTable('users');
            $q->addQuery('user_username');
            $q->addWhere('user_id = ' . $user);
            $rq = $q->exec();
            if (!$rq) {
                dprint(__file__, __line__, 0, "Cannot add role, user $user does not exist!<br>" . db_error());
                $q->clear();
                return false;
            }
            $row = $q->fetchRow();
            if ($row) {
                $this->addLogin($user, $row['user_username']);
            }
            $q->clear();
        }
        $res = $this->add_group_object($role, 'user', $user);
        $recalc = $this->recalcPermissions($user);
        if (!$recalc) {
            dprint(__file__, __line__, 0, 'Failed to recalc Permissions');
        }
        return $res;
    }

    public function deleteUserRole($role, $user)
    {
        $res = $this->del_group_object($role, 'user', $user);
        $recalc = $this->recalcPermissions($user);
        if (!$recalc) {
            dprint(__file__, __line__, 0, 'Failed to recalc Permissions');
        }
        return $res;
    }

    // Returns the group ids of all groups this user is mapped to.
    // Not provided in original phpGacl, but useful.
    public function getUserRoles($user)
    {
        $id = $this->get_object_id('user', $user, 'aro');
        $result = $this->get_group_map($id);
        if (!is_array($result)) {
            $result = array();
        }
        return $result;
    }

    // Returns the group of users under a role
    // Not provided in original phpGacl, but useful.
    public function getRoleUsers($role = null)
    {
        if (!$role) {
            return false;
        }

        $q = new w2p_Database_Query;
        $q->addTable($this->_db_acl_prefix . 'aro', 'a');
        $q->addTable($this->_db_acl_prefix . 'aro_groups', 'g1');
        $q->addTable($this->_db_acl_prefix . 'groups_aro_map', 'g2');
        $q->addQuery('a.value');
        $q->addWhere('g1.id = g2.group_id');
        $q->addWhere('a.id = g2.aro_id');
        $q->addWhere('g1.id = ' . $role);
        $q->addOrder('g1.value');

        $result = array();
        $result = $q->loadHashList();
        $q->clear();
        if (count($result)) {
            return $result;
        } else {
            return false;
        }
    }

    public function getActiveUsers()
    {
        return $this->getUsersWithRole();
    }

    // Returns the group of users that have a role (and therefore can login)
    // Not provided in original phpGacl, but useful.
    public function getUsersWithRole()
    {
        $q = new w2p_Database_Query;
        $q->addTable($this->_db_acl_prefix . 'groups_aro_map', 'g');
        $q->addQuery('DISTINCT(g.aro_id)');

        $result = $q->loadHashList();
        $q->clear();
        if (count($result)) {
            return $result;
        } else {
            return false;
        }
    }

    // Return a list of module groups and modules that a user can
    // be permitted access to.
    public function getModuleList()
    {
        $result = array();
        // First grab all the module groups.
        $parent_id = $this->get_group_id('mod', null, 'axo');
        if (!$parent_id) {
            dprint(__file__, __line__, 0, 'failed to get parent for module groups');
        }
        $groups = $this->getChildren($parent_id, 'axo');
        if (is_array($groups)) {
            foreach ($groups as $group) {
                $result[] = array('id' => $group['id'], 'type' => 'grp', 'name' => $group['name'], 'value' => $group['value']);
            }
        } else {
            dprint(__file__, __line__, 1, "No groups available for $parent_id");
        }
        // Now the individual modules.
        $modlist = $this->get_objects_full('app', 0, 'axo');
        if (is_array($modlist)) {
            foreach ($modlist as $mod) {
                $result[] = array('id' => $mod['id'], 'type' => 'mod', 'name' => $mod['name'], 'value' => $mod['value']);
            }
        }
        return $result;
    }

    // An assignable module is one where there is a module sub-group
    // Effectivly we just list those module in the section "modname"
    public function getAssignableModules()
    {
        return $this->get_object_sections(null, 0, 'axo', 'value not in ("sys", "app")');
    }

    public function getPermissionList()
    {
        $list = $this->get_objects_full('application', 0, 'aco');
        // We only need the id and the name
        $result = array();
        if (!is_array($list)) {
            return $result;
        }
        foreach ($list as $perm) {
            $result[$perm['id']] = $perm['name'];
        }
        return $result;
    }

    public function get_group_map($id, $group_type = 'ARO')
    {

        switch (strtolower(trim($group_type))) {
            case 'axo':
                $group_type = 'axo';
                $table = $this->_db_acl_prefix . 'axo_groups';
                $map_table = $this->_db_acl_prefix . 'groups_axo_map';
                $map_field = 'axo_id';
                break;
            default:
                $group_type = 'aro';
                $table = $this->_db_acl_prefix . 'aro_groups';
                $map_table = $this->_db_acl_prefix . 'groups_aro_map';
                $map_field = 'aro_id';
        }

        if (empty($id)) {
            $this->debug_text("get_group_map(): ID ($id) is empty, this is required");
            return false;
        }

        $q = new w2p_Database_Query;
        $q->addTable($table, 'g1');
        $q->addTable($map_table, 'g2');
        $q->addQuery('g1.id, g1.name, g1.value, g1.parent_id');
        $q->addWhere('g1.id = g2.group_id AND g2.' . $map_field . ' = ' . $id);
        $q->addOrder('g1.value');

        $result = array();
        $q->exec();
        while ($row = $q->fetchRow()) {
            $result[] = array('id' => $row[0], 'name' => $row[1], 'value' => $row[2], 'parent_id' => $row[3]);
        }
        $q->clear();
        return $result;
    }

    /* ======================================================================*\
      Function:	get_object()
      \*====================================================================== */

    public function get_object_full($value = null, $section_value = null, $return_hidden = 1, $object_type = null)
    {

        switch (strtolower(trim($object_type))) {
            case 'aco':
                $object_type = 'aco';
                $table = $this->_db_acl_prefix . 'aco';
                break;
            case 'aro':
                $object_type = 'aro';
                $table = $this->_db_acl_prefix . 'aro';
                break;
            case 'axo':
                $object_type = 'axo';
                $table = $this->_db_acl_prefix . 'axo';
                break;
            case 'acl':
                $object_type = 'acl';
                $table = $this->_db_acl_prefix . 'acl';
                break;
            default:
                $this->debug_text('get_object(): Invalid Object Type: ' . $object_type);
                return false;
        }

        $this->debug_text("get_object(): Section Value: $section_value Object Type: $object_type");

        $q = new w2p_Database_Query;
        $q->addTable($table);
        $q->addQuery('id, section_value, name, value, order_value, hidden');

        if (!empty($value)) {
            $q->addWhere('value=' . $this->db->quote($value));
        }

        if (!empty($section_value)) {
            $q->addWhere('section_value=' . $this->db->quote($section_value));
        }

        if ($return_hidden == 0 and $object_type != 'acl') {
            $q->addWhere('hidden=0');
        }

        $q->exec();
        $row = $q->fetchRow();
        $q->clear();

        if (!is_array($row)) {
            $this->debug_db('get_object');
            return false;
        }

        // Return Object info.
        return array('id' => $row[0], 'section_value' => $row[1], 'name' => $row[2], 'value' => $row[3], 'order_value' => $row[4], 'hidden' => $row[5]);
    }

    /* ======================================================================*\
      Function:	get_objects ()
      Purpose:	Grabs all Objects in the database, or specific to a section_value
      returns format suitable for add_acl and is_conflicting_acl
      \*====================================================================== */

    public function get_objects_full($section_value = null, $return_hidden = 1, $object_type = null, $limit_clause = null)
    {
        switch (strtolower(trim($object_type))) {
            case 'aco':
                $object_type = 'aco';
                $table = $this->_db_acl_prefix . 'aco';
                break;
            case 'aro':
                $object_type = 'aro';
                $table = $this->_db_acl_prefix . 'aro';
                break;
            case 'axo':
                $object_type = 'axo';
                $table = $this->_db_acl_prefix . 'axo';
                break;
            default:
                $this->debug_text('get_objects(): Invalid Object Type: ' . $object_type);
                return false;
        }

        $this->debug_text("get_objects(): Section Value: $section_value Object Type: $object_type");

        $q = new w2p_Database_Query;
        $q->addTable($table);
        $q->addQuery('id, section_value, name, value, order_value, hidden');

        if (!empty($section_value)) {
            $q->addWhere('section_value=' . $this->db->quote($section_value));
        }

        if ($return_hidden == 0) {
            $q->addWhere('hidden=0');
        }

        if (!empty($limit_clause)) {
            $q->addWhere($limit_clause);
        }

        $q->addOrder('order_value');

        $retarr = array();

        $q->exec();
        while ($row = $q->fetchRow()) {
            $retarr[] = array('id' => $row[0], 'section_value' => $row[1], 'name' => $row[2], 'value' => $row[3], 'order_value' => $row[4], 'hidden' => $row[5]);
        }
        $q->clear();

        // Return objects
        return $retarr;
    }

    public function get_object_sections($section_value = null, $return_hidden = 1, $object_type = null, $limit_clause = null)
    {
        switch (strtolower(trim($object_type))) {
            case 'aco':
                $object_type = 'aco';
                $table = $this->_db_acl_prefix . 'aco_sections';
                break;
            case 'aro':
                $object_type = 'aro';
                $table = $this->_db_acl_prefix . 'aro_sections';
                break;
            case 'axo':
                $object_type = 'axo';
                $table = $this->_db_acl_prefix . 'axo_sections';
                break;
            default:
                $this->debug_text('get_object_sections(): Invalid Object Type: ' . $object_type);
                return false;
        }

        $this->debug_text("get_objects(): Section Value: $section_value Object Type: $object_type");

        // $query = 'SELECT id, value, name, order_value, hidden FROM '. $table;
        $q = new w2p_Database_Query;
        $q->addTable($table);
        $q->addQuery('id, value, name, order_value, hidden');

        if (!empty($section_value)) {
            $q->addWhere('value=' . $this->db->quote($section_value));
        }

        if ($return_hidden == 0) {
            $q->addWhere('hidden=0');
        }

        if (!empty($limit_clause)) {
            $q->addWhere($limit_clause);
        }

        $q->addOrder('order_value');

        $q->exec();

        $retarr = array();

        while ($row = $q->fetchRow()) {
            $retarr[] = array('id' => $row[0], 'value' => $row[1], 'name' => $row[2], 'order_value' => $row[3], 'hidden' => $row[4]);
        }
        $q->clear();

        // Return objects
        return $retarr;
    }

    /** Called from do_perms_aed, allows us to add a new ACL */
    public function addUserPermission()
    {
        // Need to have a user id,
        // parse the permissions array
        if (!is_array($_POST['permission_type'])) {
            $this->debug_text('you must select at least one permission');
            return false;
        }

        $mod_type = substr($_POST['permission_module'], 0, 4);
        $mod_id = substr($_POST['permission_module'], 4);
        $item_id = (int) $_POST['permission_item'];
        $access = (int) $_POST['permission_access'];
        $table  = (string) $_POST['permission_table'];

        $mod_group = null;
        $mod_mod = null;
        if ($mod_type == 'grp,') {
            $mod_group = array($mod_id);
        } else {
            if ($item_id) {
                $mod_mod = array();
                $mod_mod[$table][] = $item_id;
                // check if the item already exists, if not create it.
                // First need to check if the section exists.
                if (!$this->get_object_section_section_id(null, $table, 'axo')) {
                    $this->addModuleSection($table);
                }
                if (!$this->get_object_id($table, $item_id, 'axo')) {
                    $this->addModuleItem($table, $item_id, $item_id);
                }
            } else {
                // Get the module information
                $mod_info = $this->get_object_data($mod_id, 'axo');
                $mod_mod = array();
                $mod_mod[$mod_info[0][0]][] = $mod_info[0][1];
            }
        }
        $aro_info = $this->get_object_data($_POST['permission_user'], 'aro');
        $aro_map = array();
        $aro_map[$aro_info[0][0]][] = $aro_info[0][1];
        // Build the permissions info
        $type_map = array();
        foreach ($_POST['permission_type'] as $tid) {
            $type = $this->get_object_data($tid, 'aco');
            foreach ($type as $t) {
                $type_map[$t[0]][] = $t[1];
            }
        }
        $res = $this->add_acl($type_map, $aro_map, null, $mod_mod, $mod_group, $access, 1, null, null, 'user');

        $recalc = $this->recalcPermissions(null, $_POST['permission_user']);
        if (!$recalc) {
            dprint(__file__, __line__, 0, 'Failed to recalc Permissions');
        }

        return $res;
    }

    public function addRolePermission()
    {
        if (!is_array($_POST['permission_type'])) {
            $this->debug_text('you must select at least one permission');
            return false;
        }

        $mod_type = substr($_POST['permission_module'], 0, 4);
        $mod_id = substr($_POST['permission_module'], 4);
        $item_id = (int) $_POST['permission_item'];
        $access = (int) $_POST['permission_access'];
        $table  = (string) $_POST['permission_table'];

        $mod_group = null;
        $mod_mod = null;
        if ($mod_type == 'grp,') {
            $mod_group = array($mod_id);
        } else {
            if ($item_id) {
                $mod_mod = array();
                $mod_mod[$table][] = $item_id;
                // check if the item already exists, if not create it.
                // First need to check if the section exists.
                if (!$this->get_object_section_section_id(null, $table, 'axo')) {
                    $this->addModuleSection($table);
                }
                if (!$this->get_object_id($table, $item_id, 'axo')) {
                    $this->addModuleItem($table, $item_id, $item_id);
                }
            } else {
                // Get the module information
                $mod_info = $this->get_object_data($mod_id, 'axo');
                $mod_mod = array();
                $mod_mod[$mod_info[0][0]][] = $mod_info[0][1];
            }
        }
        $aro_map = array($_POST['role_id']);
        // Build the permissions info
        $type_map = array();
        foreach ($_POST['permission_type'] as $tid) {
            $type = $this->get_object_data($tid, 'aco');
            foreach ($type as $t) {
                $type_map[$t[0]][] = $t[1];
            }
        }
        $res = $this->add_acl($type_map, null, $aro_map, $mod_mod, $mod_group, $access, 1, null, null, 'user');

        $recalc = $this->recalcPermissions(null, null, $_POST['role_id']);
        if (!$recalc) {
            dprint(__file__, __line__, 0, 'Failed to recalc Permissions');
        }

        return $res;
    }

    // Some function overrides.
    public function debug_text($text)
    {
        $this->_debug_msg = $text;
        dprint(__file__, __line__, 9, $text);
    }

    public function msg()
    {
        return $this->_debug_msg;
    }

    /**
     * w2Pacl::removeACLPermissions() Removes the permissions for a given ACL ID
     *
     * @param mixed $module
     * @return
     */
    public function removeACLPermissions($acl_id = null)
    {
        if (!$acl_id) {
            return 'Can not remove acl permissions: no acl id given.';
        }
        $q = new w2p_Database_Query;
        $q->setDelete($this->_db_acl_prefix . 'permissions');
        $q->addWhere('acl_id = \'' . $acl_id . '\'');
        $result = $q->exec();
        $q->clear();
        return $result;
    }

    /**
     * w2Pacl::removeModulePermissions() Removes the permissions from the results table for a module
     *
     * @param mixed $module
     * @return
     */
    public function removeModulePermissions($module = null)
    {
        if (!$module) {
            return 'Can not remove modules permissions: no module name given.';
        }
        $q = new w2p_Database_Query;
        $q->setDelete($this->_db_acl_prefix . 'permissions');
        $q->addWhere('module = \'' . $module . '\'');
        $result = $q->exec();
        $q->clear();
        return $result;
    }

    /**
     * w2Pacl::removePermissions() Removes the permissions from the results table for a given user (example: when you delete a user)
     *
     * @param mixed $user_id
     * @return
     */
    public function removePermissions($user_id = null)
    {
        if (!$user_id) {
            return 'Can not remove users permissions: no user given.';
        }
        $q = new w2p_Database_Query;
        $q->setDelete($this->_db_acl_prefix . 'permissions');
        $q->addWhere('user_id = \'' . $user_id . '\'');
        $result = $q->exec();
        $q->clear();
        return $result;
    }

    /**
     * w2Pacl::recalcPermissions()
     *
     * @param mixed $user_id
     * @param mixed $user_aro_id
     * @param mixed $role_id
     * @param mixed $module
     * @return
     */
    public function recalcPermissions($user_id = null, $user_aro_id = null, $role_id = null, $module = '', $notUsed = null)
    {

        $q = new w2p_Database_Query;
        $q->addTable($this->_db_acl_prefix . 'aco_sections', 'a');
        $q->addQuery('a.value AS a_value, a.name AS a_name, ' .
					'b.value AS b_value, b.name AS b_name, ' .
					'c.value AS c_value, c.name AS c_name, ' .
					'd.value AS d_value, d.name AS d_name, ' .
					'e.value AS e_value, e.name AS e_name, ' .
					'f.value AS f_value, f.name AS f_name');
        $q->leftJoin($this->_db_acl_prefix . 'aco', 'b', 'a.value=b.section_value,' . w2PgetConfig('dbprefix') . $this->_db_acl_prefix . 'aro_sections c');
        $q->leftJoin($this->_db_acl_prefix . 'aro', 'd', 'c.value=d.section_value,' . w2PgetConfig('dbprefix') . $this->_db_acl_prefix . 'axo_sections e');
        $q->leftJoin($this->_db_acl_prefix . 'axo', 'f', 'e.value=f.section_value');
        if ($user_id) {
            $q->addWhere("d.value = " . (int) $user_id);
        } elseif ($user_aro_id) {
            $q->addWhere("d.id = " . (int)  $user_aro_id);
        } else {
            //only recalculate permissions for users able to login (that have at least one role)
            $active_users = $this->getUsersWithRole();
            $q->addWhere('d.id IN (' . implode(',', array_keys($active_users)) . ')');
        }
        if ($role_id) {
            $role_users = $this->getRoleUsers($role_id);
            if ($role_users) {
                $q->addWhere('d.value IN (' . implode(',', array_keys($role_users)) . ')');
            } else {
                //If there are no users affected then make it so nothing is recalculated
                $q->addWhere('d.value = 0');
            }
        }
        if ($module) {
            $q->addWhere('f.value = \'' . $module . '\'');
        }
        //Make sure things without axos are not ported, this would make addon modules to carry wrong soft denials affecting visible addon modules
        $q->addWhere('f.value IS NOT NULL');
        $rows = $q->loadList();
        $q->clear();

        $acls = array();

        while (list(, $row) = @each($rows)) {
            $aco_section_value = $row['a_value'];
            $aco_value = $row['b_value'];

            $aro_section_value = $row['c_value'];
            $aro_value = $row['d_value'];
            $aro_name = $row['d_name'];

            $axo_section_value = $row['e_value'];
            $axo_value = $row['f_value'];

            $acl_result = $this->acl_query($aco_section_value, $aco_value, $aro_section_value, $aro_value, $axo_section_value, $axo_value);

            $acl_id = &$acl_result['acl_id'];
            $access = &$acl_result['allow'];

            $acls[] = array('aco_section_value' => $aco_section_value, 'aco_value' => $aco_value, 'aro_section_value' => $aro_section_value, 'aro_value' => $aro_value, 'aro_name' => $aro_name, 'axo_section_value' => $axo_section_value, 'axo_value' => $axo_value, 'acl_id' => $acl_id, 'access' => $access,);
        }

        $user_permissions = array();
        foreach ($acls as $key => $acl) {
            $user_permissions[$acl['aro_value']][$key]['user_id'] = $acl['aro_value'];
            $user_permissions[$acl['aro_value']][$key]['user_name'] = $acl['aro_name'];
            $user_permissions[$acl['aro_value']][$key]['module'] = ($acl['axo_section_value'] == 'app' || $acl['axo_section_value'] == 'sys') ? $acl['axo_value'] : $acl['axo_section_value'];
            $user_permissions[$acl['aro_value']][$key]['item_id'] = ($acl['axo_section_value'] == 'app' || $acl['axo_section_value'] == 'sys') ? 0 : $acl['axo_value'];
            $user_permissions[$acl['aro_value']][$key]['action'] = $acl['aco_value'];
            $user_permissions[$acl['aro_value']][$key]['access'] = $acl['access'] ? 1 : 0;
            $user_permissions[$acl['aro_value']][$key]['acl_id'] = $acl['acl_id'];
        }

        // Now that we have the users permissions lets delete the existing ones and insert the new ones
        $q = new w2p_Database_Query;
        $q->setDelete($this->_db_acl_prefix . 'permissions');
        if ($user_id) {
            $q->addWhere('user_id = \'' . $user_id . '\'');
        }
        if ($user_aro_id) {
            $qui = new w2p_Database_Query;
            $qui->addTable($this->_db_acl_prefix . 'aro');
            $qui->addQuery('value');
            $qui->addWhere('id = \'' . $user_aro_id . '\'');
            $id = $qui->loadResult();
            if ($id) {
                $q->addWhere('user_id = \'' . $id . '\'');
            }
        }
        if ($role_id) {
            $role_users = $this->getRoleUsers($role_id);
            if ($role_users) {
                $q->addWhere('user_id IN (' . implode(',', array_keys($role_users)) . ')');
            } else {
                //If there are no users affected then don not delete anything
                $q->addWhere('user_id = 0');
            }
        }
        if ($module) {
            $q->addWhere('module = \'' . $module . '\'');
        }
        $q->exec();
        $q->clear();

        $q = new w2p_Database_Query;
        foreach ($user_permissions as $notUsed => $permissions) {
            foreach ($permissions as $permission) {
                //Only show permissions with acl_id and item_id when item permissions are to show
                //Don't show login ACOs
                if (!($permission['item_id'] && !$permission['acl_id']) && ($permission['action'] != 'login')) {
                    $q->addTable($this->_db_acl_prefix . 'permissions');
                    $q->addInsert('user_id', $permission['user_id']);
                    $q->addInsert('user_name', $permission['user_name']);
                    $q->addInsert('module', $permission['module']);
                    $q->addInsert('item_id', ($permission['item_id'] ? $permission['item_id'] : 0));
                    $q->addInsert('action', $permission['action']);
                    $q->addInsert('access', $permission['access']);
                    $q->addInsert('acl_id', ($permission['acl_id'] ? $permission['acl_id'] : 0));
                    $q->exec();
                    $q->clear();
                }
            }
        }

        return true;
    }

    //Our own acl_check
    /**
     * w2Pacl::w2Pacl_check()
     * //w2Pacl_check is used for modules only
     *
     * @param mixed $application it passes 'application' string by default and is not used
     * @param mixed $op one of the acos 'access','view','add','delete','edit'
     * @param mixed $user it passes 'user' string by default and is not used
     * @param mixed $userid it passes the user_id
     * @param mixed $app it passes 'app' string by default and is not used
     * @param mixed $module it passes the modules name
     * @return
     */
    public function w2Pacl_check($notUsed = null, $op, $notUsed2 = null, $userid, $notUsed3 = null, $module)
    {
        global $w2p_performance_acltime, $w2p_performance_aclchecks;
        $q = new w2p_Database_Query;
        $q->addTable($this->_db_acl_prefix . 'permissions');
        $q->addQuery('access');
        $q->addWhere('module = \'' . $module . '\'');
        $q->addWhere('action = \'' . $op . '\'');
        $q->addWhere('item_id = 0');
        $q->addWhere('user_id = ' . (int) $userid);
        $q->addOrder('acl_id DESC');

        if (W2P_PERFORMANCE_DEBUG) {
            $startTime = array_sum(explode(' ', microtime()));
        }
        $res = $q->loadResult();
        if (W2P_PERFORMANCE_DEBUG) {
            ++$w2p_performance_aclchecks;
            $w2p_performance_acltime += array_sum(explode(' ', microtime())) - $startTime;
        }
        return $res;
    }

    public function w2Pacl_nuclear($userid, $module, $item, $mod_class = array())
    {
        global $AppUI;
        //This is a sensitive function so if the minimum permission request arguments are not provided don't permit anything to this item
        if (!$userid || !$module || !$item) {
            return array();
        }

        if (!count($mod_class)) {
            $q = new w2p_Database_Query;
            $q->addTable('modules');
            $q->addQuery('mod_main_class, permissions_item_table, permissions_item_field, permissions_item_label, mod_directory');
            $q->addWhere('mod_directory = \'' . $module . '\'');
            $q->addWhere('mod_active = 1');
            $mod_class = $q->loadHash();
        }

        //If we don't know what is the module we are dealing with lets deny
        if (!$mod_class['mod_directory']) {
            dprint(__file__, __line__, 2, 'user:' . $userid . 'module:' . $module . 'Item:' . $item . $AppUI->getModuleClass($mod_class['mod_directory']));
            return array();
        }
        $obj = new $mod_class['mod_main_class'];
        $allowedRecords = array();
        if ($module == 'projects') {
            $allowedRecords = $obj->getAllowedRecords($userid, $mod_class['permissions_item_table'] . '.' . $mod_class['permissions_item_field'] . ',' . $mod_class['permissions_item_label'], '', null, null, 'projects');
        } else {
            $allowedRecords = $obj->getAllowedRecords($userid, $mod_class['permissions_item_table'] . '.' . $mod_class['permissions_item_field'] . ',' . $mod_class['permissions_item_label']);
        }

        if (count($allowedRecords)) {
            if (isset($allowedRecords[(int) $item])) {
                return array('access' => 1, 'acl_id' => 'checked');
            } else {
                return array();
            }
        } else {
            return array();
        }
    }

    /**
     * w2p_Extensions_Permissions::w2Pacl_query()
     * //w2Pacl_query is used for items only
     *
     * @param string $application
     * @param mixed $op
     * @param string $user
     * @param mixed $userid
     * @param mixed $module
     * @param mixed $item
     * @return
     */
    public function w2Pacl_query($notUsed = null, $op, $notUsed2 = null, $userid, $module, $item)
    {
        global $w2p_performance_acltime, $w2p_performance_aclchecks;
        //Basically the view action is nuclear when it comes to cascading, therefore all the others are straight forward
        //So if there is no specific permissions regarding the item, then it is the module that determines the permission.
        //Exception: Task log is not a module so just check if we have module permission for the action
        //This is a sensitive function so if the minimum permission request arguments are not provided don't permit anything to this item
        if (!$op || !$userid || !$module || !$item) {
            return array();
        }

        $mod_class = array();
        if ($module == 'task_log') {
            $mod_class = array('mod_main_class' => 'CTask_Log', 'permissions_item_table' => 'task_log', 'permissions_item_field' => 'task_log_id', 'permissions_item_label' => 'task_log_name', 'mod_directory' => 'tasks');
        } elseif ($module == 'admin') {
            $mod_class = array('mod_main_class' => 'CUser', 'permissions_item_table' => 'users', 'permissions_item_field' => 'user_id', 'permissions_item_label' => 'user_username', 'mod_directory' => 'admin');
        } elseif ($module == 'users') {
            $mod_class = array('mod_main_class' => 'CUser', 'permissions_item_table' => 'users', 'permissions_item_field' => 'user_id', 'permissions_item_label' => 'user_username', 'mod_directory' => 'admin');
        } elseif ($module == 'events') {
            $mod_class = array('mod_main_class' => 'CCalendar', 'permissions_item_table' => 'events', 'permissions_item_field' => 'event_id', 'permissions_item_label' => 'event_title', 'mod_directory' => 'calendar');
        }
        if ($op == 'view') {
            //Because view is nuclear we can't just check the permission against the results table, so we need to check the allowed records on each class, so it handles the
            //Cascading of permissions.
            if (W2P_PERFORMANCE_DEBUG) {
                $startTime = array_sum(explode(' ', microtime()));
            }
            $res = $this->w2Pacl_nuclear($userid, $module, $item, $mod_class);
            if (W2P_PERFORMANCE_DEBUG) {
                ++$w2p_performance_aclchecks;
                $w2p_performance_acltime += array_sum(explode(' ', microtime())) - $startTime;
            }
            return $res;
        } else {
            if (W2P_PERFORMANCE_DEBUG) {
                $startTime = array_sum(explode(' ', microtime()));
            }
            $nuclear = $this->w2Pacl_nuclear($userid, $module, $item, $mod_class);
            if (!$nuclear || !$nuclear['acl_id']) {
                //if we don't have nuclear (view) permission then don't waste our time checking the rest and ... deny.
                if (W2P_PERFORMANCE_DEBUG) {
                    ++$w2p_performance_aclchecks;
                    $w2p_performance_acltime += array_sum(explode(' ', microtime())) - $startTime;
                }
                return array();
            } else {
                $q = new w2p_Database_Query;
                $q->addTable($this->_db_acl_prefix . 'permissions');
                $q->addQuery('access, acl_id');
                $q->addWhere('module = \'' . $module . '\'');
                $q->addWhere('action = \'' . $op . '\'');
                $q->addWhere('user_id = ' . (int) $userid);
                $q->addWhere('(item_id = ' . (int) $item . ' OR item_id = 0)');
                $q->addOrder('item_id DESC, acl_id DESC');
                $result = $q->loadList();
                if (W2P_PERFORMANCE_DEBUG) {
                    ++$w2p_performance_aclchecks;
                    $w2p_performance_acltime += array_sum(explode(' ', microtime())) - $startTime;
                }
                return $result[0];
            }
        }
    }

    public function w2Psearch_acl($notUsed = null, $op, $notUsed2 = null, $userid, $module)
    {
        global $w2p_performance_acltime, $w2p_performance_aclchecks;
        $q = new w2p_Database_Query;
        $q->addTable($this->_db_acl_prefix . 'permissions');
        $q->addQuery('acl_id, access, item_id');
        $q->addWhere('module = \'' . $module . '\'');
        $q->addWhere('action = \'' . $op . '\'');
        $q->addWhere('user_id = ' . (int) $userid);
        $q->addOrder('acl_id DESC');
        if (W2P_PERFORMANCE_DEBUG) {
            $startTime = array_sum(explode(' ', microtime()));
        }
        $res = $q->loadList();
        if (W2P_PERFORMANCE_DEBUG) {
            ++$w2p_performance_aclchecks;
            $w2p_performance_acltime += array_sum(explode(' ', microtime())) - $startTime;
        }
        return $res;
    }

    /*
     * This method is primarily for modules that don't have a set of permissions
     * on their own.  For example, the SmartSearch module in core
     * web2project respects the permissions of the individual items it is
     * searching but it does not apply any permissions of its own.
     */

    public function registerModule($module_name, $module_value, $section_value = 'app')
    {
        $q = new w2p_Database_Query;
        $q->addTable('gacl_axo');
        $q->addInsert('name', $module_name);
        $q->addInsert('value', $module_value);
        $q->addInsert('section_value', $section_value);
        $q->exec();

        return true;
    }

    public function unregisterModule($module_value)
    {
        if ($module_value != '') {
            $q = new w2p_Database_Query;
            $q->setDelete('gacl_axo');
            $q->addWhere("value = '$module_value'");
            $q->exec();
            return true;
        }
        return false;
    }

}