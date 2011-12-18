<?php /* $Id$ $URL$ */

/**
 *	@package web2project
 *	@subpackage core
 *	@version $Revision$
 */

/**
 *	w2p_Core_BaseObject Abstract Class.
 *
 *	Parent class to all database table derived objects
 *	@author Andrew Eddie <eddieajau@users.sourceforge.net>
 *	@abstract
 */
abstract class w2p_Core_BaseObject extends w2p_Core_Event
    implements w2p_Core_ListenerInterface
{
	/**
	 *	@var string Name of the table prefix in the db schema
	 */
	protected $_tbl_prefix = '';
	/**
	 *	@var string Name of the table in the db schema relating to child class
	 */
	protected $_tbl = '';
	/**
	 *	@var string Name of the primary key field in the table
	 */
	protected $_tbl_key = '';
	/**
	 *	@var string Error message
	 */
	protected $_error = '';

	/**
	 * @var object Query Handler
	 */
	protected $_query;

	/**
	 * @var string Internal name of the module as stored in the 'mod_directory' of the 'modules' table, and the 'value' field of the 'gacl_axo' table
	 */
	protected $_tbl_module;

    protected $_dispatcher;

	/**
	 *	Object constructor to set table and key field
	 *
	 *	Can be overloaded/supplemented by the child class
	 *	@param string $table name of the table in the db schema relating to child class
	 *	@param string $key name of the primary key field in the table
	 *	@param (OPTIONAL) string $module name as stored in the 'mod_directory' of the 'modules' table, and the 'value' field of the 'gacl_axo' table.
	 *          It is used for permission checking in situations where the table name is different from the module folder name.
	 *          For compatibility sake this variable is set equal to the $table if not set as failsafe.
	 */
	public function __construct($table, $key, $module = '')
	{
		$this->_error = array();
        $this->_tbl = $table;
		$this->_tbl_key = $key;
		if ($module) {
			$this->_tbl_module = $module;
		} else {
			$this->_tbl_module = $table;
		}
		$this->_tbl_prefix = w2PgetConfig('dbprefix', '');
		$this->_query = new w2p_Database_Query;

        /*
         * This block does a lot and may need to be simplified.. but the point
         *   is that it sets up all of our base Events for later notifications,
         *   logging, etc. We also need a way to enable Core Modules (CProject,
         *   CTask, etc) and Add On Modules to add their own hooks.
         */
        $this->_dispatcher = new w2p_Core_Dispatcher();
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
    public function overrideDatabase($override) {
        $this->_query = $override;
    }

	/**
	 *	@return string or array Returns the error message
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
	 *	Binds a named array/hash to this object
	 *
	 *	can be overloaded/supplemented by the child class
	 *	@param array $hash named array
	 *  @param $prefix Defaults to null, prefix to use with hash keys
	 *  @param $checkSlashes Defaults to true, strip any slashes from the hash values
	 *  @param $bindAll Bind all values regardless of their existance as defined instance variables
	 *	@return null|string	null is operation was satisfactory, otherwise returns an error
	 */
	public function bind($hash, $prefix = null, $checkSlashes = true, $bindAll = false)
	{
		if (!is_array($hash)) {
			$this->_error = get_class($this) . '::bind failed.';
			return false;
		} else {
			/*
			* We need to filter out any object values from the array/hash so the bindHashToObject()
			* doesn't die. We also avoid issues such as passing objects to non-object functions
			* and copying object references instead of cloning objects. Object cloning (if needed)
			* should be handled seperatly anyway.
			*/
			foreach ($hash as $k => $v) {
				if (!(is_object($hash[$k]))) {
					$filtered_hash[$k] = (is_string($v)) ? strip_tags($v) : $v;
				}
			}
            $q = $this->_getQuery();
			$q->bindHashToObject($filtered_hash, $this, $prefix, $checkSlashes, $bindAll);

			return true;
		}
	}

	/**
	 *	Binds an array/hash to this object
	 *	@param int $oid optional argument, if not specifed then the value of current key is used
	 *	@return any result from the database operation
	 */
	public function load($oid = null, $strip = true)
	{
        $this->_dispatcher->publish(new w2p_Core_Event(get_class($this), 'preLoadEvent'));

        $k = $this->_tbl_key;
		if ($oid) {
			$this->$k = intval($oid);
		}
		$oid = $this->$k;
		if ($oid === null) {
			return false;
		}
		$q = $this->_getQuery();
		$q->addTable($this->_tbl);
		$q->addWhere($this->_tbl_key . ' = ' . $oid);
		$hash = $q->loadHash();
		//If no record was found send false because there is no data
		if (!$hash) {
			return false;
		}
		$q->bindHashToObject($hash, $this, null, $strip);
        $this->_dispatcher->publish(new w2p_Core_Event(get_class($this), 'postLoadEvent'));

		return $this;
	}

	/**
	 *	Returns an array, keyed by the key field, of all elements that meet
	 *	the where clause provided. Ordered by $order key.
	 */
	public function loadAll($order = null, $where = null)
	{
        $q = $this->_getQuery();
		$q->addTable($this->_tbl);
		if ($order) {
			$q->addOrder($order);
		}
		if ($where) {
			$q->addWhere($where);
		}
		$result = $q->loadHashList($this->_tbl_key);

		return $result;
	}

	/**
	 *	Return a w2p_Database_Query object seeded with the table name.
	 *	@param string $alias optional alias for table queries.
	 *	@return w2p_Database_Query object
	 */
	public function &getQuery($alias = null)
	{
		$q = $this->_getQuery();
        $q->addTable($this->_tbl, $alias);
		return $q;
	}

	/**
	 *	Generic check method
	 *
	 *	Can be overloaded/supplemented by the child class
	 *	@return array() of size zero if the object is ok
	 */
	public function check()
	{
        return $this->_error;
	}

	/**
	 *	Clone the current record
	 *
	 *	@author	handco <handco@users.sourceforge.net>
	 *	@return	object	The new record object or null if error
	 **/
	public function duplicate()
	{
		/*
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
	 *	Default trimming method for class variables of type string
	 *
	 *	@param object Object to trim class variables for
	 *	Can be overloaded/supplemented by the child class
	 *	@return none
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
	 *	Inserts a new row if id is zero or updates an existing row in the database table
	 *
	 *	Can be overloaded/supplemented by the child class
	 *	@return null|string null if successful otherwise returns and error message
	 */
	public function store($updateNulls = false)
	{
        $k = $this->_tbl_key;

        // NOTE: I don't particularly like this but it wires things properly.
        $this->_dispatcher->publish(new w2p_Core_Event(get_class($this), 'preStoreEvent'));
        $event = ($this->$k) ? 'Update' : 'Create';
        $this->_dispatcher->publish(new w2p_Core_Event(get_class($this), 'pre'.$event.'Event'));

		$this->w2PTrimAll();

        // NOTE: This is *very* similar to the store() flow within delete()..
        $this->_error = $this->check();
        if (count($this->_error)) {
			$msg = get_class($this) . '::store-check failed';
            $this->_error['store-check'] = $msg;
            return $msg;
		}

		$k = $this->_tbl_key;
        $q = $this->_getQuery();
		if ($this->$k) {
			$store_type = 'update';
			$ret = $q->updateObject($this->_tbl, $this, $this->_tbl_key, $updateNulls);
		} else {
			$store_type = 'add';
			$ret = $q->insertObject($this->_tbl, $this, $this->_tbl_key);
		}

		if ($ret) {
            $result = null;
            // NOTE: I don't particularly like how the name is generated but it wires things properly.
            $this->_dispatcher->publish(new w2p_Core_Event(get_class($this), 'post'.$event.'Event'));
            $this->_dispatcher->publish(new w2p_Core_Event(get_class($this), 'postStoreEvent'));
		} else {
            $result = db_error();
            $this->_error['store'] = $result;
        }

        return $result;
	}

	/**
	 *	Generic check for whether dependencies exist for this object in the db schema
	 *
	 *	Can be overloaded/supplemented by the child class
	 *	@param string $msg Error message returned
	 *	@param int Optional key index
	 *	@param array Optional array to compiles standard joins: format [label=>'Label',name=>'table name',idfield=>'field',joinfield=>'field']
	 *	@return true|false
	 */
	public function canDelete(&$msg = '', $oid = null, $joins = null)
	{
		global $AppUI;
        $result = true;

		// First things first.  Are we allowed to delete?
		$acl = &$AppUI->acl();
		if (!$acl->checkModuleItem($this->_tbl_module, 'delete', $oid)) {
			$msg = $AppUI->_('noDeletePermission');
            $this->_error['noDeletePermission'] = $msg;
			return false;
		}

		$k = $this->_tbl_key;
		if ($oid) {
			$this->$k = intval($oid);
		}
		if (is_array($joins)) {
			$select = $k;
			$join = '';

			$q = $this->_getQuery();
			$q->addTable($this->_tbl);
			$q->addWhere($k . ' = \'' . $this->$k . '\'');
			$q->addGroup($k);
			foreach ($joins as $table) {
				$q->addQuery('COUNT(DISTINCT ' . $table['idfield'] . ') AS ' . $table['idfield']);
				$q->addJoin($table['name'], $table['name'], $table['joinfield'] . ' = ' . $k);
			}
			$obj = null;
			$q->loadObject($obj);

			if (!$obj && '' != db_error()) {
				$msg = db_error();
                $this->_error['db_error'] = $msg;
				return false;
			}
			$msg = array();
			foreach ($joins as $table) {
				$k = $table['idfield'];
				if ($obj->$k) {
					$msg[$table['label']] = $AppUI->_($table['label']);
                    $this->_error['noDeleteRecord-'.$table['label']] = $table['label'];
				}
			}

			if (count($msg)) {
				$msg = $AppUI->_('noDeleteRecord') . ': ' . implode(', ', $msg);
				return false;
			} else {
                $msg = array();
                foreach ($joins as $table) {
                    $k = $table['idfield'];
                    if ($obj->$k) {
                        $this->_error['canDelete-error-'.$table['name']] = db_error();
                    }
                }

                if (0 == count($this->_errors)) {
                    $result = true;
                }
            }
		}

		return $result;
	}

	/**
	 *	Default delete method
	 *
	 *	Can be overloaded/supplemented by the child class
	 *	@return null|string null if successful otherwise returns and error message
	 */
	public function delete($oid = null)
	{
        $this->_dispatcher->publish(new w2p_Core_Event(get_class($this), 'preDeleteEvent'));

        $k = $this->_tbl_key;
		if ($oid) {
			$this->$k = intval($oid);
		}

        // NOTE: This is *very* similar to the check() flow within store()..
        $this->canDelete();
		if (count($this->_error)) {
			$msg = get_class($this) . '::delete-check failed';
//TODO: no clue why this is required..
unset($this->_error['store']);
            $this->_error['delete-check'] = $msg;
            return $msg;
		}

		$q = $this->_getQuery();
		$q->setDelete($this->_tbl);
		$q->addWhere($this->_tbl_key . ' = \'' . $this->$k . '\'');
        if ($q->exec()) {
            $result = null;
            $this->_dispatcher->publish(new w2p_Core_Event(get_class($this), 'postDeleteEvent'));
        } else {
            $result = db_error();
            $this->_error['delete'] = $result;
        }

		return $result;
	}

	/**
	 *	Get specifically denied records from a table/module based on a user
	 *	@param int User id number
	 *	@return array
	 */
	public function getDeniedRecords($uid)
	{
		$uid = intval($uid);
		$uid || exit('FATAL ERROR ' . get_class($this) . '::getDeniedRecords failed, user id = 0');

		$perms = &$GLOBALS['AppUI']->acl();
		return $perms->getDeniedItems($this->_tbl_module, $uid);
	}

	/**
	 *	Returns a list of records exposed to the user
	 *	@param int User id number
	 *	@param string Optional fields to be returned by the query, default is all
	 *	@param string Optional sort order for the query
	 *	@param string Optional name of field to index the returned array
	 *	@param array Optional array of additional sql parameters (from and where supported)
	 *	@return array
	 */
	// returns a list of records exposed to the user
	public function getAllowedRecords($uid, $fields = '*', $orderby = '', $index = null, $extra = null, $table_alias = '')
	{
		$perms = &$GLOBALS['AppUI']->acl();
		$uid = intval($uid);
		$uid || exit('FATAL ERROR ' . get_class($this) . '::getAllowedRecords failed');
		$deny = &$perms->getDeniedItems($this->_tbl_module, $uid);
		$allow = &$perms->getAllowedItems($this->_tbl_module, $uid);

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
			} else {
				//If 0 (All Items of a module) are permited then don't add a where clause so the user is permitted to see all
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

	public function getAllowedSQL($uid, $index = null) {
		global $AppUI;

        $perms = $AppUI->acl();
        $uid = (int) $uid;
		$uid || exit('FATAL ERROR ' . get_class($this) . '::getAllowedSQL failed');
		$deny = &$perms->getDeniedItems($this->_tbl_module, $uid);
		$allow = &$perms->getAllowedItems($this->_tbl_module, $uid);

		if (!isset($index)) {
			$index = $this->_tbl_key;
		}
		$where = array();
		if (count($allow)) {
			if ((array_search('0', $allow)) === false) {
				//If 0 (All Items of a module) are not permited then just add the allowed items only
				$where[] = $index . ' IN (' . implode(',', $allow) . ')';
			} else {
				//If 0 (All Items of a module) are permited then don't add a where clause so the user is permitted to see all
			}
			//Denials are only required if we were able to see anything in the first place so now we handle the denials
			if (count($deny)) {
				if ((array_search('0', $deny)) === false) {
					//If 0 (All Items of a module) are not on the denial array then just deny the denied items
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

	public function setAllowedSQL($uid, $query, $index = null, $key = null) {
		global $AppUI;

        $perms = $AppUI->acl();
		$uid = (int) $uid;
		$uid || exit('FATAL ERROR ' . get_class($this) . '::getAllowedSQL failed');
		$deny = &$perms->getDeniedItems($this->_tbl_module, $uid);
		$allow = &$perms->getAllowedItems($this->_tbl_module, $uid);
		// Make sure that we add the table otherwise dependencies break
		if (isset($index)) {
			if (!$key) {
				$key = substr($this->_tbl, 0, 2);
			}
			$query->leftJoin($this->_tbl, $key, $key . '.' . $this->_tbl_key . ' = ' . $index);
		}

		if (count($allow)) {
			if ((array_search('0', $allow)) === false) {
				//If 0 (All Items of a module) are not permited then just add the allowed items only
				$query->addWhere(((!$key) ? '' : $key . '.') . $this->_tbl_key . ' IN (' . implode(',', $allow) . ')');
			} else {
				//If 0 (All Items of a module) are permited then don't add a where clause so the user is permitted to see all
			}
			//Denials are only required if we were able to see anything in the first place so now we handle the denials
			if (count($deny)) {
				if ((array_search('0', $deny)) === false) {
					//If 0 (All Items of a module) are not on the denial array then just deny the denied items
					$query->addWhere(((!$key) ? '' : $key . '.') . $this->_tbl_key . ' NOT IN (' . implode(',', $deny) . ')');
				} elseif ((array_search('0', $allow)) === false) {
					//If 0 (All Items of a module) are denied and we have granted some then implicit denial to everything else is already in place
				} else {
					//if we allow everything and deny everything then denials have higher priority... Deny Everything!
					$query->addWhere('0=1');
				}
			}
		} else {
			//if there are no allowances, deny!
			$query->addWhere('0=1');
		}
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

    /*
     *  This pre/post functions are only here for completeness.
     *    NOTE: Each of these actions gets called after the permissions check.
     *    NOTE: The pre* actions happen whether the desired action - create,
     *      update, load, and delete - actually occur.
     *    NOTE: The post* actions only happen if the desired action - create,
     *      update, load, and delete - is successful.
     */

    protected function hook_preStore() {
        return $this;
    }

    protected function hook_postStore() {
        //NOTE: This only happens if the create was successful.
		global $AppUI;

        $name = $this->{substr($this->_tbl, 0, -1).'_name'};
        $name = (isset($name)) ? $name : '';
        addHistory($this->_tbl, $this->{$this->_tbl_key}, 'add', $name . ' - ' .
            $AppUI->_('ACTION') . ': ' .  $store_type . ' ' . $AppUI->_('TABLE') . ': ' .
            $this->_tbl . ' ' . $AppUI->_('ID') . ': ' . $this->{$this->_tbl_key});

        return $this;
    }

    protected function hook_preCreate() {
        return $this;
    }
    protected function hook_postCreate() {
        //NOTE: This only happens if the create was successful.
		global $AppUI;

        $name = $this->{substr($this->_tbl, 0, -1).'_name'};
        $name = (isset($name)) ? $name : '';
        addHistory($this->_tbl, $this->{$this->_tbl_key}, 'add', $name . ' - ' .
            $AppUI->_('ACTION') . ': ' .  $store_type . ' ' . $AppUI->_('TABLE') . ': ' .
            $this->_tbl . ' ' . $AppUI->_('ID') . ': ' . $this->{$this->_tbl_key});

        return $this;
    }
    protected function hook_preUpdate() {
        return $this;
    }
    protected function hook_postUpdate() {
        //NOTE: This only happens if the update was successful.
		global $AppUI;

        $name = $this->{substr($this->_tbl, 0, -1).'_name'};
        $name = (isset($name)) ? $name : '';
        addHistory($this->_tbl, $this->{$this->_tbl_key}, 'update', $name . ' - ' .
            $AppUI->_('ACTION') . ': ' .  $store_type . ' ' . $AppUI->_('TABLE') . ': ' .
            $this->_tbl . ' ' . $AppUI->_('ID') . ': ' . $this->{$this->_tbl_key});
        return $this;
    }
    protected function hook_preLoad() {
        return $this;
    }
    protected function hook_postLoad() {
        //NOTE: This only happens if the load was successful.
        return $this;
    }
    protected function hook_preDelete() {
        return $this;
    }
    protected function hook_postDelete() {
        //NOTE: This only happens if the delete was successful.
		global $AppUI;

        addHistory($this->_tbl, $this->{$this->_tbl_key}, 'delete');
        return $this;
    }

	public function publish(w2p_Core_Event $event)
	{
        $hook = substr($event->getEventName(), 0, -5);

        switch($hook) {
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
                $this->{'hook_'.$hook}();
                break;
            default:
                //do nothing
        }
        //error_log("{$event->resourceName} published {$event->eventName} to call hook_$hook");
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
        return $this->_query;
    }
}
