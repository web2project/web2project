<?php
/**
 * Parent class to all database table derived objects
 *
 * @package     web2project\core
 * @author      Andrew Eddie <eddieajau@users.sourceforge.net>
 *
 * @abstract
 */

abstract class w2p_Core_BaseObject extends w2p_System_Event implements w2p_Interfaces_Listener
{

    /**
     *     @var string Name of the table prefix in the db schema
     */
    protected $_tbl_prefix = '';

    /**
     *     @var string Name of the table in the db schema relating to child class
     */
    protected $_tbl = '';

    /**
     *     @var string Name of the primary key field in the table
     */
    protected $_tbl_key = '';

    /**
     *     @var string Error message
     */
    protected $_error = '';

    /**
     * @var object Query Handler
     */
    protected $_query;

    /**
     * @var object permissions/preference/translation object
     */
    protected $_AppUI;
    protected $_perms;
    protected $_w2Pconfig;

    /**
     * @var string Internal name of the module as stored in the 'mod_directory' of the 'modules' table, and the 'value' field of the 'gacl_axo' table
     */
    protected $_tbl_module;
    protected $_dispatcher;
    protected $_locale_char_set;

    /**
     *     Object constructor to set table and key field. This should be overridden by all submodules
     *
     * @param string $table     name of the table in the db schema relating to child class
     * @param string $key       name of the primary key field in the table
     * @param string $module    name as stored in the 'mod_directory' of the 'modules' table, and the 'value' field of the 'gacl_axo' table. It is used for permission checking in situations where the table name is different from the module folder name. For compatibility sake this variable is set equal to the $table if not set as failsafe.
     */
    public function __construct($table, $key, $module = '')
    {
        $this->_error = array();
        $this->_tbl = $table;
        $this->_tbl_key = $key;
        $this->_tbl_module = ('' == $module) ? $table : $module;

        $this->_tbl_prefix = w2PgetConfig('dbprefix', '');
        $this->_query = new w2p_Database_Query;

        /**
         * I hate this global but this will allow us to get rid of all the
         *   others, so I think it's the best approach for now.
         *                                           ~ caseydk 27 Dec 2011
         */
        global $AppUI;
        $this->_AppUI = is_null($AppUI) ? new w2p_Core_CAppUI() : $AppUI;
        $this->_perms = $this->_AppUI->acl();
        global $locale_char_set;
        $this->_locale_char_set = $locale_char_set;

        /**
         * This block does a lot and may need to be simplified.. but the point
         *   is that it sets up all of our base Events for later notifications,
         *   logging, etc. We also need a way to enable Core Modules (CProject,
         *   CTask, etc) and Add On Modules to add their own hooks.
         */
        $this->_dispatcher = new w2p_System_Dispatcher();
        $this->_dispatcher->subscribe($this, get_class($this), 'preStoreEvent');
        $this->_dispatcher->subscribe($this, get_class($this), 'postStoreEvent');
        $this->_dispatcher->subscribe($this, get_class($this), 'preCreateEvent');
        $this->_dispatcher->subscribe($this, get_class($this), 'postCreateEvent');
        $this->_dispatcher->subscribe($this, get_class($this), 'preUpdateEvent');
        $this->_dispatcher->subscribe($this, get_class($this), 'postUpdateEvent');
        $this->_dispatcher->subscribe($this, get_class($this), 'preDeleteEvent');
        $this->_dispatcher->subscribe($this, get_class($this), 'postDeleteEvent');
        $this->_dispatcher->subscribe($this, get_class($this), 'preLoadEvent');
        $this->_dispatcher->subscribe($this, get_class($this), 'postLoadEvent');
        parent::__construct($this->_tbl_module, get_class($this), array());
    }

    /**
     * Since Dependency injection isn't feasible due to the sheer number of
     *   calls to the above constructor, this is a way to hijack the current
     *   $this->_query and manipulate it however we want.
     *
     *   @param Object A database connection (real or mocked)
     */
    public function overrideDatabase($override)
    {
        if (!is_null($override)) {
            $this->_query = $override;
        }
    }

    /**
     * Since Dependency injection isn't feasible due to the sheer number of
     *   calls to the above constructor, this is a way to hijack the current
     *   $this->_AppUI and manipulate it however we want.
     *
     *   @param Object A permissions/preferences object (real or mocked)
     */
    public function overrideAppUI($override)
    {
        $this->_AppUI = $override;
        $this->_perms = $this->_AppUI->acl();
    }

    /**
     *     @return string or array Returns the error message
     */
    public function getError()
    {
        return $this->_error;
    }

    public function clearErrors()
    {
        $this->_error = array();
    }

    /**
     *     Binds a named array/hash to this object
     *
     * @param      $hash
     * @param null $prefix          Defaults to null, prefix to use with hash keys
     * @param bool $checkSlashes    Defaults to true, strip any slashes from the hash values
     * @param bool $bindAll
     *
     * @return bool
     */
    public function bind($hash, $prefix = null, $checkSlashes = true, $bindAll = false)
    {
        if (!is_array($hash)) {
            $this->_error[] = get_class($this) . '::bind failed.';
            return false;
        } else {
            /**
             * We need to filter out any object values from the array/hash so the bindHashToObject()
             * doesn't die. We also avoid issues such as passing objects to non-object functions
             * and copying object references instead of cloning objects. Object cloning (if needed)
             * should be handled seperatly anyway.
             */
            $filtered_hash = array();
            
            foreach ($hash as $k => $v) {
                if (!(is_object($hash[$k]))) {
                    $filtered_hash[$k] = (is_string($v)) ? htmlentities($v) : $v;
                }
            }
            $q = $this->_getQuery();
            $q->bindHashToObject($filtered_hash, $this, $prefix, $checkSlashes, $bindAll);

            return true;
        }
    }

    /**
     *     Binds an array/hash to this object
     *
     * @param null $oid     optional argument, if not specified then the value of current key is used
     * @param bool $strip
     *
     * @return bool
     */
    public function load($oid = null, $strip = true)
    {
        $this->_dispatcher->publish(new w2p_System_Event(get_class($this), 'preLoadEvent'));

        $k = $this->_tbl_key;
        if ($oid) {
            $this->$k = (int) $oid;
        }
        $oid = $this->$k;
        if ($oid === null) {
            return false;
        }
        if (!$this->canView()) {
            return false;
        }
        $q = $this->_getQuery();
        $q->addTable($this->_tbl);
        $q->addWhere($this->_tbl_key . ' = ' . $oid);
        $hash = $q->loadHash();
        //If no record was found send false because there is no data
        if (!$hash) {
            $this->$k = null;
            return false;
        }
        $q->bindHashToObject($hash, $this, null, $strip);
        $this->_dispatcher->publish(new w2p_System_Event(get_class($this), 'postLoadEvent'));

        return true;
    }

    /**
     * Although this is a new method in this class, it is deprecated because it is replacing all the subclass
     *   implementations.
     * @deprecated
     */
    public function loadFull($notUsed = null, $object_id) {     $this->load($object_id);        }

    /**
     *     Returns an array, keyed by the key field, of all elements that meet
     *     the where clause provided. Ordered by $order key.
     *
     * @param null $order
     * @param null $where
     *
     * @return Associative
     */
    public function loadAll($order = null, $where = null)
    {
        $q = $this->_getQuery();
        $q->addTable($this->_tbl);
        if ($order && property_exists($this, $order)) {
            $q->addOrder($order);
        }
        if ($where) {
            $q->addWhere($where);
        }
        $result = $q->loadHashList($this->_tbl_key);

        return $result;
    }

    /**
     *     Return a w2p_Database_Query object seeded with the table name.
     *     @param string $alias optional alias for table queries.
     *     @return w2p_Database_Query object
     */
    public function &getQuery($alias = null)
    {
        $q = $this->_getQuery();
        $q->addTable($this->_tbl, $alias);
        return $q;
    }

    /**
     *     Generic check method, overload as needed
     *
     *     @return array() of size zero if the object is ok
     */
    public function check()
    {
        $this->isValid();

        return $this->getError();
    }

    /**
     * This function does just what you think it does. The nice thing is that
     *    since it always returns a boolean (storing the errors in the
     *    $this->_errors, we can make decisions based on it even if we don't
     *    care about the errors.
     * 
     * @return boolean
     */
    public function isValid()
    {
        return true;
    }
    /**
     *     Clone the current record
     *
     *     @author    handco <handco@users.sourceforge.net>
     *     @return    object    The new record object or null if error
     * */
    public function duplicate()
    {
        /**
         *  PHP4 is no longer supported or allowed. The
         *    installer/upgrader/converter simply stops executing.
         *  This method also appears (modified) in the w2p_Utilities_Date and w2p_Database_Query class.
         */

        $_key = $this->_tbl_key;

        $newObj = clone ($this);
        $newObj->$_key = '';

        return $newObj;
    }

    /**
     *     Default trimming method for class variables of type string
     *
     *     @param object Object to trim class variables for
     *
     *     @return none
     */
    public function w2PTrimAll()
    {
        $trim_arr = get_object_vars($this);
        foreach ($trim_arr as $trim_key => $trim_val) {
            if (!(strcasecmp(gettype($trim_val), 'string'))) {
                $this->{$trim_key} = trim($trim_val, " \t\r\n\0\x0B");
            }
        }
    }

    /**
     *     Inserts a new row if id is zero or updates an existing row in the database table
     *
     * @param bool $updateNulls
     *
     * @return bool
     */
    public function store($updateNulls = false)
    {
        $result = false;
        $this->clearErrors();

        $this->_dispatcher->publish(new w2p_System_Event(get_class($this), 'preStoreEvent'));

        $this->w2PTrimAll();

        if (!$this->isValid()) {
            return false;
        }

        $k = $this->_tbl_key;
        // NOTE: I don't particularly like this but it wires things properly.
        $this->_event = ($this->$k) ? 'Update' : 'Create';
        $this->_dispatcher->publish(new w2p_System_Event(get_class($this), 'pre' . $this->_event . 'Event'));

        $q = $this->_getQuery();

        /**
         * Note that we have to check and perform the edit *first* because the
         *    create/add fills in the id that we're checking. Therefore, if we
         *    did the create/add first, we'd have a valid id and then we'd
         *    *always* immediately do an update on the object we just created.
         */
        if ($this->$k && $this->canEdit()) {
            $store_type = 'update';
            $result = $q->updateObject($this->_tbl, $this, $this->_tbl_key, $updateNulls);
        }
        if (0 == $this->$k && $this->canCreate()) {
            $store_type = 'add';
            $result = $q->insertObject($this->_tbl, $this, $this->_tbl_key);
        }

        if ($result) {
            // NOTE: I don't particularly like how the name is generated but it wires things properly.
            $this->_dispatcher->publish(new w2p_System_Event(get_class($this), 'post' . $this->_event . 'Event'));
            $this->_dispatcher->publish(new w2p_System_Event(get_class($this), 'postStoreEvent'));
        } else {
            $this->_error['store'] = db_error();
        }

        return $result;
    }

    /**
     * @return boolean
     */
    public function canAddEdit()
    {
        if ($this->{$this->_tbl_key}) {
            return $this->canEdit();
        } else {
            return $this->canCreate();
        }
    }

    /**
     * @return boolean
     */
    public function canAccess() {
        return $this->_perms->checkModuleItem($this->_tbl_module, 'access');
    }
    /**
     * @return boolean
     */
    public function canCreate() {
        return $this->_perms->checkModuleItem($this->_tbl_module, 'add');
    }
    /**
     * @return boolean
     */
    public function canEdit() {
        return $this->_perms->checkModuleItem($this->_tbl_module, 'edit', $this->{$this->_tbl_key});
    }
    /**
     * @return boolean
     */
    public function canView() {
        return $this->_perms->checkModuleItem($this->_tbl_module, 'view', $this->{$this->_tbl_key});
    }

    /**
     *     Generic check for whether dependencies exist for this object in the db schema, overload as needed
     *
     * @param string $notUsed
     * @param null   $oid
     * @param null   $joins
     *
     * @return bool
     */
    public function canDelete($notUsed = '', $oid = null, $joins = null)
    {
        $k = $this->_tbl_key;
        if ($oid) {
            $this->$k = (int) $oid;
        }

        // First things first.  Are we allowed to delete?
        if (!$this->_perms->checkModuleItem($this->_tbl_module, 'delete', $this->$k)) {
            $this->_error['noDeletePermission'] = $this->_AppUI->_('noDeletePermission');
            return false;
        }

        /**
        * @todo This should confirm that the module is actually active and available before executing the query below
        */
        if (is_array($joins)) {
            foreach ($joins as $table) {
                $q = $this->_getQuery();
                $q->addQuery('COUNT(*)');
                $q->addTable($table['name']);
                $q->addWhere($table['joinfield'] . ' = \'' . $this->$k . '\'');
                $records = (int) $q->loadResult();
                if ($records) {
                    $this->_error['noDeleteRecord-' . $table['label']] = 
                            $this->_AppUI->_('You cannot delete this item. It is currently considered a ' . $table['label']);
                }
            }
        }

        return (count($this->_error)) ? false : true;
    }

    /**
     *     Default delete method
     *
     * @param null $oid
     *
     * @return bool|Handle
     */
    public function delete($oid = null)
    {
        $result = false;
        $this->clearErrors();

        $k = $this->_tbl_key;
        if ($oid) {
            $this->$k = (int) $oid;
        }

        $this->_dispatcher->publish(new w2p_System_Event(get_class($this), 'preDeleteEvent'));

        if (!$this->canDelete()) {
            //TODO: no clue why this is required..
            unset($this->_error['store']);
            $this->_error['delete-check'] = get_class($this) . '::delete-check failed';
            return false;
        }

        $q = $this->_getQuery();
        $q->setDelete($this->_tbl);
        $q->addWhere($this->_tbl_key . ' = \'' . $this->$k . '\'');
        $result = $q->exec();

        if ($result) {
            $this->_dispatcher->publish(new w2p_System_Event(get_class($this), 'postDeleteEvent'));
        } else {
            $this->_error['delete'] = db_error();
        }

        return $result;
    }

    /**
     *     Get specifically denied records from a table/module based on a user
     *     @param int User id number
     *     @return array
     */
    public function getDeniedRecords($uid)
    {
        $uid = intval($uid);
        $uid || exit('FATAL ERROR ' . get_class($this) . '::getDeniedRecords failed, user id = 0');

        return $this->_perms->getDeniedItems($this->_tbl_module, $uid);
    }

    /**
     *     Returns a list of records visible to the user
     *
     * @param        $uid
     * @param string $fields        fields to be returned by the query, default is all
     * @param string $orderby       sort order for the query
     * @param null   $index         name of field to index the returned array
     * @param null   $extra         array of additional sql parameters (from and where supported)
     * @param string $table_alias
     *
     * @return Associative
     */
    public function getAllowedRecords($uid, $fields = '*', $orderby = '', $index = null, $extra = null, $table_alias = '')
    {
        $uid = (int) $uid;
        if (!$uid) {
            return array();
        }
        $deny = $this->_perms->getDeniedItems($this->_tbl_module, $uid);
        $allow = $this->_perms->getAllowedItems($this->_tbl_module, $uid);

        $q = $this->_getQuery();
        $q->addQuery($fields);
        $q->addTable($this->_tbl);

        if (isset($extra['from'])) {
            $q->addTable($extra['from']);
        }

        if (isset($extra['join']) && isset($extra['on'])) {
            $q->addJoin($extra['join'], $extra['join'], $extra['on']);
        }

        if (count($allow)) {
            if ((array_search('0', $allow)) === false) {
                //If 0 (All Items of a module) are not permited then just add the allowed items only
                $q->addWhere(($table_alias ? $table_alias . '.' : '') . $this->_tbl_key . ' IN (' . implode(',', $allow) . ')');
            }
            //Denials are only required if we were able to see anything in the first place so now we handle the denials
            if (count($deny)) {
                if ((array_search('0', $deny)) === false) {
                    //If 0 (All Items of a module) are not on the denial array then just deny the denied items
                    $q->addWhere(($table_alias ? $table_alias . '.' : '') . $this->_tbl_key . ' NOT IN (' . implode(',', $deny) . ')');
                } elseif ((array_search('0', $allow)) === false) {
                    //If 0 (All Items of a module) are denied and we have granted some then implicit denial to everything else is already in place
                } else {
                    //if we allow everything and deny everything then denials have higher priority... Deny Everything!
                    $q->addWhere('0=1');
                }
            }
        } else {
            //if there are no allowances, deny!
            $q->addWhere('0=1');
        }

        if (isset($extra['where'])) {
            $q->addWhere($extra['where']);
        }

        if ($orderby) {
            $q->addOrder($orderby);
        }
        return $q->loadHashList($index);
    }

    public function getAllowedSQL($uid, $index = null)
    {
        $uid = (int) $uid;
        if (!$uid) {
            return array();
        }
        $deny = $this->_perms->getDeniedItems($this->_tbl_module, $uid);
        $allow = $this->_perms->getAllowedItems($this->_tbl_module, $uid);

        if (!isset($index)) {
            $index = $this->_tbl_key;
        }
        $where = array();
        if (count($allow)) {
            if ((array_search('0', $allow)) === false) {
                //If 0 (All Items of a module) are not permited then just add the allowed items only
                $where[] = $index . ' IN (' . implode(',', $allow) . ')';
            }
            //Denials are only required if we were able to see anything in the first place so now we handle the denials
            if (count($deny)) {
                if ((array_search('0', $deny)) === false) {
                    //If 0 (All Items of a module) are not on the denial array then just deny the denied items
                    // TODO: The following line needs to be reworked/removed at some point.
                    $index = ('project_id' == $index) ? 'pr.project_id' : $index;
                    $where[] = $index . ' NOT IN (' . implode(',', $deny) . ')';
                } elseif ((array_search('0', $allow)) === false) {
                    //If 0 (All Items of a module) are denied and we have granted some then implicit denial to everything else is already in place
                } else {
                    //if we allow everything and deny everything then denials have higher priority... Deny Everything!
                    $where[] = '0=1';
                }
            }
        } else {
            //if there are no allowances, deny!
            $where[] = '0=1';
        }
        return $where;
    }

    /**
     * This method applies permissions to the SQL statement on the fly
     *   respecting the object hierarchy.
     *
     * Currently this handles the $query with a pass by reference and so you
     *   didn't need to pass the object back. I've added a return so we can
     *   get rid of the pbr at some point.
     *
     * @param int                   $uid
     * @param w2p_Database_Query    $query
     * @param string                $index
     *
     * @return  w2p_Database_Query
     */
    public function setAllowedSQL($uid, $query, $index = null)
    {
        $where = $this->getAllowedSQL($uid, $index);
        foreach($where as $criteria) {
            $query->addWhere($criteria);
        }

        return $query;
    }

    /*
     * Decode HTML entities in object vars
     */
    public function htmlDecode()
    {
        foreach (get_object_vars($this) as $k => $v) {
            if (is_array($v) or is_object($v) or $v == null) {
                continue;
            }
            if ($k[0] == '_') { // internal field
                continue;
            }
            $this->$k = htmlspecialchars_decode($v);
        }
    }

    /**
     *  This pre/post functions are only here for completeness. They are meant to
     *    be overridden by subclasses as needed.
     *
     *  It is important to remember the hook_pre* methods are called:
     *    -  before the object validation occurs. If you need to do something to
     *       make the object valid, this is where you do it.
     *    -  before the permissions check. So don't do anything dependent on specific
     *       access rights unless you check them yourself.
     *
     *  It is important to remember the hook_post* methods are called:
     *    -  if and only if the corresponding method executed successfully.
     *    -  For example, hook_postDelete() will execute only after the object is
     *       deleted as expected. In this case, don't count on using object properties.
     *
     */

    /**
     * This method is called within $this->store() but before we attempt to
     *   store it. It is called regardless of whether or not the object is
     *   valid or the user has permissions. You might use this to make an
     *   object valid.
     *
     * @return \w2p_Core_BaseObject
     */
    protected function hook_preStore()      {   return $this;   }
    /**
     * This method is called after hook_preStore() but before we attempt to
     *   store it. It is called regardless of whether or not the object is
     *   valid or the user has permissions to create it. You might use this to
     *   make an object valid or set a create date.
     *
     * @return \w2p_Core_BaseObject
     */
    protected function hook_preCreate()     {   return $this;   }
    /**
     * This method is called after hook_preStore() but before we attempt to
     *   store it. It is called regardless of whether or not the object is
     *   valid or the user has permissions to update it. You might use this to
     *   make an object valid when it is being updated.
     *
     * @return \w2p_Core_BaseObject
     */
    protected function hook_preUpdate()     {   return $this;   }
    /**
     * This method is called within $this->delete() but before we attempt to
     *   delete it. It is called regardless of whether or not the object can be
     *   deleted.. which may be determined by dependencies or permissions.
     *
     * We're grabbing the id here before we delete the object because we might
     *   need it later.
     *
     * @return \w2p_Core_BaseObject
     */
    protected function hook_preDelete()
    {
        $this->_old_key = (int) $this->{$this->_tbl_key};

        return $this;

    }
    protected function hook_preLoad()       {   return $this;   }

    /**
     * This method is called within $this->store() but only after the object
     *   was stored properly. You might use this to send notifications or
     *   update other objects.
     * @todo TODO: I REALLY hate this $_POST here.. it's just asking for trouble.
     *
     * @return \w2p_Core_BaseObject
     */
    protected function hook_postStore()
    {
        $custom_fields = new w2p_Core_CustomFields($this->_tbl_module, 'addedit', $this->{$this->_tbl_key}, 'edit');
        $custom_fields->bind($_POST);
        $custom_fields->store($this->{$this->_tbl_key});

        $prefix = $this->_getColumnPrefixFromTableName($this->_tbl);
        $name = ('' != $this->{$prefix . '_name'}) ? $this->{$prefix . '_name'} : '';
        addHistory($this->_tbl, $this->{$this->_tbl_key}, $this->_event, $name . ' - ' .
            $this->_AppUI->_('ACTION') . ': ' . $this->_event . ' ' . $this->_AppUI->_('TABLE') . ': ' .
            $this->_tbl . ' ' . $this->_AppUI->_('ID') . ': ' . $this->{$this->_tbl_key});

        return $this;
    }
    /**
     * This method is called within $this->store() but only after the object
     *   was created properly. You might use this to send notifications or
     *   update other objects.
     *
     * @return \w2p_Core_BaseObject
     */
    protected function hook_postCreate()    {   return $this;   }
    /**
     * This method is called within $this->store() but only after the object
     *   was updated properly. You might use this to send notifications or
     *   update other objects.
     *
     * @return \w2p_Core_BaseObject
     */
    protected function hook_postUpdate()    {   return $this;   }
    /**
     * This method is called only if $this->delete() was successful. It is
     *   often used for cleaning up other things like custom fields or related
     *   objects.
     *
     * @return \w2p_Core_BaseObject
     */
    protected function hook_postDelete()
    {
        /**
         * @todo I don't like that we have to initialize this null but it's
         *   needed to delete the custom field values from the object we just
         *   deleted.
         */
        $custom_field = new w2p_Core_CustomField('notUsed', 'notUsed2',
            'notUsed3', 'notUsed4', 'notUsed5', 'notUsed6');
        $custom_field->deleteByObject($this->_old_key);

        addHistory($this->_tbl, $this->{$this->_tbl_key}, 'delete');
        return $this;
    }
    /**
     * This method is called within $this->load() but only after the object
     *   was loaded properly.
     *
     * @return \w2p_Core_BaseObject
     */
    protected function hook_postLoad()      {   return $this;   }


    public function publish(w2p_System_Event $event)
    {
        $hook = substr($event->getEventName(), 0, -5);

        switch ($hook) {
            case 'preStore':
            case 'postStore':
            case 'preCreate':
            case 'postCreate':
            case 'preUpdate':
            case 'postCreate':
            case 'preUpdate':
            case 'postUpdate':
            case 'preLoad':
            case 'postLoad':
            case 'preDelete':
            case 'postDelete':
                $this->{'hook_' . $hook}();
                break;
            default:
            //do nothing
        }
        //error_log("{$event->resourceName} published {$event->eventName} with id: " . $this->{$this->_tbl_key});
    }

    /**
     * Returns a clean query object
     *
     * Clears out the query and then returns it for use
     *
     * @access protected
     *
     * @return w2p_Database_Query Clean query object
     */
    protected function _getQuery()
    {
        $this->_query->clear();
        $this->_query->direct_query = false;
        return $this->_query;
    }

    // TODO: create a proper "unpluralize" from this?
    protected function _getColumnPrefixFromTableName($tableName)
    {
        $prefix = substr($tableName, 0, -1);
        // companies -> company
        if (substr($prefix, -2) === 'ie') {
            $prefix = substr($prefix, 0, -2) . 'y';
        }
        
        return $prefix;
    }

    public function setId($key)
    {
        $this->{$this->_tbl_key} = $key;
    }

    public function getId()
    {
        return $this->{$this->_tbl_key};
    }
}
