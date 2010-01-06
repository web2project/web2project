<?php /* $Id$ $URL$ */

/**
 *	@package web2project
 *	@subpackage modules
 *	@version $Revision$
 */

if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

/**
 *	CW2pObject Abstract Class.
 *
 *	Parent class to all database table derived objects
 *	@author Andrew Eddie <eddieajau@users.sourceforge.net>
 *	@abstract
 */
class CW2pObject {
	/**
	 *	@var string Name of the table prefix in the db schema
	 */
	public $_tbl_prefix = '';
	/**
	 *	@var string Name of the table in the db schema relating to child class
	 */
	public $_tbl = '';
	/**
	 *	@var string Name of the primary key field in the table
	 */
	public $_tbl_key = '';
	/**
	 *	@var string Error message
	 */
	public $_error = '';

	/**
	 * @var object Query Handler
	 */
	public $_query;

	/**
	 *	Object constructor to set table and key field
	 *
	 *	Can be overloaded/supplemented by the child class
	 *	@param string $table name of the table in the db schema relating to child class
	 *	@param string $key name of the primary key field in the table
	 */
	public function CW2pObject($table, $key) {
		$this->_tbl = $table;
		$this->_tbl_key = $key;
		$this->_tbl_prefix = w2PgetConfig('dbprefix', '');
		$this->_query = new DBQuery;
	}
	/**
	 *	@return string Returns the error message
	 */
	public function getError() {
		return $this->_error;
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
	public function bind($hash, $prefix = null, $checkSlashes = true, $bindAll = false) {
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
			$this->_query->bindHashToObject($filtered_hash, $this, $prefix, $checkSlashes, $bindAll);
			$this->_query->clear();
			return true;
		}
	}

	/**
	 *	Binds an array/hash to this object
	 *	@param int $oid optional argument, if not specifed then the value of current key is used
	 *	@return any result from the database operation
	 */
	public function load($oid = null, $strip = true) {
		$k = $this->_tbl_key;
		if ($oid) {
			$this->$k = intval($oid);
		}
		$oid = $this->$k;
		if ($oid === null) {
			return false;
		}
		$this->_query->clear();
		$this->_query->addTable($this->_tbl);
		$this->_query->addWhere($this->_tbl_key . ' = ' . $oid);
		$hash = $this->_query->loadHash();
		//If no record was found send false because there is no data
		if (!$hash) {
			return false;
		}
		$this->_query->bindHashToObject($hash, $this, null, $strip);
		$this->_query->clear();
		return $this;
	}

	/**
	 *	Returns an array, keyed by the key field, of all elements that meet
	 *	the where clause provided. Ordered by $order key.
	 */
	public function loadAll($order = null, $where = null) {
		$this->_query->clear();
		$this->_query->addTable($this->_tbl);
		if ($order) {
			$this->_query->addOrder($order);
		}
		if ($where) {
			$this->_query->addWhere($where);
		}
		$result = $this->_query->loadHashList($this->_tbl_key);
		$this->_query->clear();
		return $result;
	}

	/**
	 *	Return a DBQuery object seeded with the table name.
	 *	@param string $alias optional alias for table queries.
	 *	@return DBQuery object
	 */
	public function &getQuery($alias = null) {
		$this->_query->clear();
		$this->_query->addTable($this->_tbl, $alias);
		return $this->_query;
	}

	/**
	 *	Generic check method
	 *
	 *	Can be overloaded/supplemented by the child class
	 *	@return null if the object is ok
	 */
	public function check() {
		return null;
	}

	/**
	 *	Clone the current record
	 *
	 *	@author	handco <handco@users.sourceforge.net>
	 *	@return	object	The new record object or null if error
	 **/
	public function duplicate() {
		$_key = $this->_tbl_key;

		// In php4 assignment does a shallow copy
		// in php5 clone is required
		if (version_compare(phpversion(), '5') >= 0) {
			$newObj = clone($this);
		} else {
			$newObj = $this;
		}
		// blanking the primary key to ensure that's a new record
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
	public function w2PTrimAll() {
		$trim_arr = get_object_vars($this);
		foreach ($trim_arr as $trim_key => $trim_val) {
			if (!(strcasecmp(gettype($trim_val), 'string'))) {
				$this->{$trim_key} = trim($trim_val);
			}
		}
	}

	/**
	 *	Inserts a new row if id is zero or updates an existing row in the database table
	 *
	 *	Can be overloaded/supplemented by the child class
	 *	@return null|string null if successful otherwise returns and error message
	 */
	public function store($updateNulls = false) {
		global $AppUI;

		$this->w2PTrimAll();

		$msg = $this->check();
		if ($msg) {
			return get_class($this) . '::store-check failed ' . $msg;
		}
		$k = $this->_tbl_key;
		if ($this->$k) {
			$store_type = 'update';
			$q = new DBQuery;
			$ret = $q->updateObject($this->_tbl, $this, $this->_tbl_key, $updateNulls);
			$q->clear();
		} else {
			$store_type = 'add';
			$q = new DBQuery;
			$ret = $q->insertObject($this->_tbl, $this, $this->_tbl_key);
			$q->clear();
		}

		if ($ret) {
			// only record history if an update or insert actually occurs.
			addHistory($this->_tbl, $this->$k, $store_type, $AppUI->_('ACTION') . ': ' . $store_type . ' ' . $AppUI->_('TABLE') . ': ' . $this->_tbl . ' ' . $AppUI->_('ID') . ': ' . $this->$k);
		}
		return ((!$ret) ? (get_class($this) . '::store failed ' . db_error()) : null);
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
	public function canDelete(&$msg, $oid = null, $joins = null) {
		global $AppUI;

		// First things first.  Are we allowed to delete?
		$acl = &$AppUI->acl();
		if (!$acl->checkModuleItem($this->_tbl, 'delete', $oid)) {
			$msg = $AppUI->_('noDeletePermission');
			return false;
		}

		$k = $this->_tbl_key;
		if ($oid) {
			$this->$k = intval($oid);
		}
		if (is_array($joins)) {
			$select = $k;
			$join = '';

			$q = new DBQuery;
			$q->addTable($this->_tbl);
			$q->addWhere($k . ' = \'' . $this->$k . '\'');
			$q->addGroup($k);
			foreach ($joins as $table) {
				$q->addQuery('COUNT(DISTINCT ' . $table['idfield'] . ') AS ' . $table['idfield']);
				$q->addJoin($table['name'], $table['name'], $table['joinfield'] . ' = ' . $k);
			}
			$obj = null;
			$q->loadObject($obj);
			$q->clear();

			if (!$obj) {
				$msg = db_error();
				return false;
			}
			$msg = array();
			foreach ($joins as $table) {
				$k = $table['idfield'];
				if ($obj->$k) {
					$msg[] = $AppUI->_($table['label']);
				}
			}

			if (count($msg)) {
				$msg = $AppUI->_('noDeleteRecord') . ': ' . implode(', ', $msg);
				return false;
			} else {
				return true;
			}
		}

		return true;
	}

	/**
	 *	Default delete method
	 *
	 *	Can be overloaded/supplemented by the child class
	 *	@return null|string null if successful otherwise returns and error message
	 */
	public function delete($oid = null) {
		$k = $this->_tbl_key;
		if ($oid) {
			$this->$k = intval($oid);
		}
		if (!$this->canDelete($msg)) {
			return $msg;
		}

		$q = new DBQuery;
		$q->setDelete($this->_tbl);
		$q->addWhere($this->_tbl_key . ' = \'' . $this->$k . '\'');
		$result = ((!$q->exec()) ? db_error() : null);
		if (!$result) {
			// only record history if deletion actually occurred
			addHistory($this->_tbl, $this->$k, 'delete');
		}
		$q->clear();
		return $result;
	}

	/**
	 *	Get specifically denied records from a table/module based on a user
	 *	@param int User id number
	 *	@return array
	 */
	public function getDeniedRecords($uid) {
		$uid = intval($uid);
		$uid || exit('FATAL ERROR ' . get_class($this) . '::getDeniedRecords failed, user id = 0');

		$perms = &$GLOBALS['AppUI']->acl();
		return $perms->getDeniedItems($this->_tbl, $uid);
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
	public function getAllowedRecords($uid, $fields = '*', $orderby = '', $index = null, $extra = null, $table_alias = '') {
		$perms = &$GLOBALS['AppUI']->acl();
		$uid = intval($uid);
		$uid || exit('FATAL ERROR ' . get_class($this) . '::getAllowedRecords failed');
		$deny = &$perms->getDeniedItems($this->_tbl, $uid);
		$allow = &$perms->getAllowedItems($this->_tbl, $uid);
		/*print_r('Deny:');
		print_r($deny);
		print_r('Allow:');
		print_r($allow);*/
		//if (! $perms->checkModule($this->_tbl, 'view', $uid )) {
		//  if (! count($allow))
		//    return array();	// No access, and no allow overrides, so nothing to show.
		//} else {
		//  $allow = array();	// Full access, allow overrides don't mean anything.
		//}
		$this->_query->clear();
		$this->_query->addQuery($fields);
		$this->_query->addTable($this->_tbl);

		if (isset($extra['from'])) {
			$this->_query->addTable($extra['from']);
		}

		if (isset($extra['join']) && isset($extra['on'])) {
			$this->_query->addJoin($extra['join'], $extra['join'], $extra['on']);
		}

		if (count($allow)) {
			if ((array_search('0', $allow)) === false) {
				//If 0 (All Items of a module) are not permited then just add the allowed items only
				$this->_query->addWhere(($table_alias ? $table_alias . '.' : '') . $this->_tbl_key . ' IN (' . implode(',', $allow) . ')');
			} else {
				//If 0 (All Items of a module) are permited then don't add a where clause so the user is permitted to see all
			}
			//Denials are only required if we were able to see anything in the first place so now we handle the denials
			if (count($deny)) {
				if ((array_search('0', $deny)) === false) {
					//If 0 (All Items of a module) are not on the denial array then just deny the denied items
					$this->_query->addWhere(($table_alias ? $table_alias . '.' : '') . $this->_tbl_key . ' NOT IN (' . implode(',', $deny) . ')');
				} elseif ((array_search('0', $allow)) === false) {
					//If 0 (All Items of a module) are denied and we have granted some then implicit denial to everything else is already in place
				} else {
					//if we allow everything and deny everything then denials have higher priority... Deny Everything!
					$this->_query->addWhere('0=1');
				}
			}
		} else {
			//if there are no allowances, deny!
			$this->_query->addWhere('0=1');
		}

		if (isset($extra['where'])) {
			$this->_query->addWhere($extra['where']);
		}

		if ($orderby) {
			$this->_query->addOrder($orderby);
		}
		//print_r($this->_query->prepare());
		return $this->_query->loadHashList($index);
	}

	public function getAllowedSQL($uid, $index = null) {
		$perms = &$GLOBALS['AppUI']->acl();
		$uid = intval($uid);
		$uid || exit('FATAL ERROR ' . get_class($this) . '::getAllowedSQL failed');
		$deny = &$perms->getDeniedItems($this->_tbl, $uid);
		$allow = &$perms->getAllowedItems($this->_tbl, $uid);
		/*		print_r('allow:');
		print_r($allow);
		print_r('deny:');
		print_r($deny);
		print_r('deny:');
		print_r($deny);
		if (! $perms->checkModule($this->_tbl, 'view', $uid )) {
		if (! count($allow))
		return array('1=0');*/ // No access, and no allow overrides, so nothing to show.
		//} else {
		//  $allow = array();	// Full access, allow overrides don't mean anything.
		//}

		if (!isset($index)) {
			$index = $this->_tbl_key;
		}
		$where = array();
		if (count($allow)) {
			if ((array_search('0', $allow)) === false) {
				//If 0 (All Items of a module) are not permited then just add the allowed items only
				$where[] = $index  . ' IN (' . implode(',', $allow) . ')';
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

	public function setAllowedSQL($uid, &$query, $index = null, $key = null) {
		$perms = &$GLOBALS['AppUI']->acl();
		$uid = intval($uid);
		$uid || exit('FATAL ERROR ' . get_class($this) . '::getAllowedSQL failed');
		$deny = &$perms->getDeniedItems($this->_tbl, $uid);
		$allow = &$perms->getAllowedItems($this->_tbl, $uid);
		// Make sure that we add the table otherwise dependencies break
		if (isset($index)) {
			if (!$key) {
				$key = substr($this->_tbl, 0, 2);
			}
			$query->leftJoin($this->_tbl, $key, $key . '.' . $this->_tbl_key . ' = ' . $index);
		}
		//		if (! $perms->checkModule($this->_tbl, 'view', $uid )) {
		//		  if (! count($allow)) {
		// We need to ensure that we don't just break complex SQLs, but
		// instead limit to a nonsensical value.  This assumes that the
		// key is auto-incremented.
		//		    $query->addWhere($this->_tbl_key . ' = 0');
		//		    return;
		//			}
		//		}

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
	public function htmlDecode() {
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
}