<?php /* DEPARTMENTS $Id$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

##
## CDepartment Class
##

class CDepartment extends CW2pObject {
	var $dept_id = null;
	var $dept_parent = null;
	var $dept_company = null;
	var $dept_name = null;
	var $dept_phone = null;
	var $dept_fax = null;
	var $dept_address1 = null;
	var $dept_address2 = null;
	var $dept_city = null;
	var $dept_state = null;
	var $dept_zip = null;
	var $dept_country = null;
	var $dept_url = null;
	var $dept_desc = null;
	var $dept_owner = null;

	function CDepartment() {
		$this->CW2pObject('departments', 'dept_id');
	}

	function load($oid) {
		$q = new DBQuery;
		$q->addTable('departments', 'dep');
		$q->addQuery('dep.*');
		$q->addWhere('dep.dept_id = ' . $oid);
		$result = $q->loadObject($this);
		$q->clear();
		return $result;
	}

	function bind($hash) {
		if (!is_array($hash)) {
			return get_class($this) . "::bind failed";
		} else {
			bindHashToObject($hash, $this);
			return null;
		}
	}

	function check() {
		if ($this->dept_id === null) {
			return 'department id is NULL';
		}
		// TODO MORE
		if ($this->dept_id && $this->dept_id == $this->dept_parent) {
			return 'cannot make myself my own parent (' . $this->dept_id . '=' . $this->dept_parent . ')';
		}
		return null; // object is ok
	}

	function store() {
		$msg = $this->check();
		if ($msg) {
			return get_class($this) . '::store-check failed - ' . $msg;
		}
		if ($this->dept_id) {
			$q = new DBQuery;
			$ret = $q->updateObject('departments', $this, 'dept_id', false);
			$q->clear();
		} else {
			$q = new DBQuery;
			$ret = $q->insertObject('departments', $this, 'dept_id');
			$q->clear();
		}
		if (!$ret) {
			return get_class($this) . '::store failed ' . db_error();
		} else {
			return null;
		}
	}

	function delete() {
		$q = new DBQuery;
		$q->addTable('departments', 'dep');
		$q->addQuery('dep.dept_id');
		$q->addWhere('dep.dept_parent = ' . $this->dept_id);
		$rows = $q->loadList();
		$q->clear();

		if (count($rows)) {
			return 'deptWithSub';
		}
		
		$q->addTable('project_departments', 'pd');
		$q->addQuery('pd.project_id');
		$q->addWhere('pd.department_id = ' . $this->dept_id);
		$rows = $q->loadList();
		$q->clear();

		if (count($rows)) {
			return 'deptWithProject';
		}

		$q->addQuery('*');
		$q->setDelete('departments');
		$q->addWhere('dept_id = ' . $this->dept_id);
		if (!$q->exec()) {
			$result = db_error();
		} else {
			$result = null;
		}
		$q->clear();
		return $result;
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
	function getAllowedRecords($uid, $fields = '*', $orderby = '', $index = null, $extra = null) {
		$perms = &$GLOBALS['AppUI']->acl();
		$uid = intval($uid);
		$uid || exit('FATAL ERROR<br />' . get_class($this) . '::getAllowedRecords failed');
		$deny = &$perms->getDeniedItems($this->_tbl, $uid);
		$allow = &$perms->getAllowedItems($this->_tbl, $uid);

		$this->_query->clear();
		$this->_query->addQuery($fields);
		$this->_query->addTable($this->_tbl);

		if (@$extra['from']) {
			$this->_query->addTable($extra['from']);
		}

		if (@$extra['join'] && @$extra['on']) {
			$this->_query->addJoin($extra['join'], $extra['join'], $extra['on']);
		}

		if (count($allow)) {
			if ((array_search('0', $allow)) === false) {
				//If 0 (All Items of a module) are not permited then just add the allowed items only
				$this->_query->addWhere('(' . $this->_tbl_key . ' IN (' . implode(',', $allow) . ') OR ' . $this->_tbl_key . ' IS NULL)');
			} else {
				//If 0 (All Items of a module) are permited then don't add a where clause so the user is permitted to see all
			}
			//Denials are only required if we were able to see anything in the first place so now we handle the denials
			if (count($deny)) {
				if ((array_search('0', $deny)) === false) {
					//If 0 (All Items of a module) are not on the denial array then just deny the denied items
					$this->_query->addWhere('(' . $this->_tbl_key . ' NOT IN (' . implode(',', $deny) . ') OR ' . $this->_tbl_key . ' IS NULL)');
				} elseif ((array_search('0', $allow)) === false) {
					//If 0 (All Items of a module) are denied and we have granted some then implicit denial to everything else is already in place
				} else {
					//If 0 (All Items of a module) are denied then add a false where clause
					$this->_query->addWhere('(' . $this->_tbl_key . ' IS NULL)');
				}
			}
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

	function getAllowedSQL($uid, $index = null) {
		$perms = &$GLOBALS['AppUI']->acl();
		$uid = intval($uid);
		$uid || exit('FATAL ERROR<br />' . get_class($this) . '::getAllowedSQL failed');
		$deny = &$perms->getDeniedItems($this->_tbl, $uid);
		$allow = &$perms->getAllowedItems($this->_tbl, $uid);

		if (!isset($index))
			$index = $this->_tbl_key;
		$where = array();
		if (count($allow)) {
			if ((array_search('0', $allow)) === false) {
				//If 0 (All Items of a module) are not permited then just add the allowed items only
				$where[] = '(' . $index . ' IN (' . implode(',', $allow) . ') OR ' . $index . ' IS NULL)';
			} else {
				//If 0 (All Items of a module) are permited then don't add a where clause so the user is permitted to see all
			}
			//Denials are only required if we were able to see anything in the first place so now we handle the denials
			if (count($deny)) {
				if ((array_search('0', $deny)) === false) {
					//If 0 (All Items of a module) are not on the denial array then just deny the denied items
					$where[] = '(' . $index . ' NOT IN (' . implode(',', $deny) . ') OR ' . $index . ' IS NULL)';
				} elseif ((array_search('0', $allow)) === false) {
					//If 0 (All Items of a module) are denied and we have granted some then implicit denial to everything else is already in place
				} else {
					//If 0 (All Items of a module) are denied then add a false where clause
					$where[] = '(' . $index . ' IS NULL)';
				}
			}
		} else {
			//if there are no allowances, only show NULL joins!
			$where[] = '(' . $index . ' IS NULL)';
		}
		return $where;
	}

	function setAllowedSQL($uid, &$query, $index = null, $key = null) {
		$perms = &$GLOBALS['AppUI']->acl();
		$uid = intval($uid);
		$uid || exit('FATAL ERROR<br />' . get_class($this) . '::getAllowedSQL failed');
		$deny = &$perms->getDeniedItems($this->_tbl, $uid);
		$allow = &$perms->getAllowedItems($this->_tbl, $uid);
		// Make sure that we add the table otherwise dependencies break
		if (isset($index)) {
			if (!$key) {
				$key = substr($this->_tbl, 0, 3);
				//$key = $this->_tbl;
			}
			$query->leftJoin($this->_tbl, $key, $key . '.' . $this->_tbl_key . '=' . $index);
		}

		if (count($allow)) {
			if ((array_search('0', $allow)) === false) {
				//If 0 (All Items of a module) is not permited then just add the allowed items only
				$query->addWhere('(' . ((!$key) ? '' : $key . '.') . $this->_tbl_key . ' IN (' . implode(',', $allow) . ') OR ' . ((!$key) ? '' : $key . '.') . $this->_tbl_key . ' IS NULL)');
			} else {
				//If 0 (All Items of a module) is permited then don't add a where clause so the user is permitted to see all
			}
			//Denials are only required if we were able to see anything in the first place so now we handle the denials
			if (count($deny)) {
				if ((array_search('0', $deny)) === false) {
					//If 0 (All Items of a module) are not on the denial array then just deny the denied items
					$query->addWhere('(' . ((!$key) ? '' : $key . '.') . $this->_tbl_key . ' NOT IN (' . implode(',', $deny) . ') OR ' . ((!$key) ? '' : $key . '.') . $this->_tbl_key . ' IS NULL)');
				} elseif ((array_search('0', $allow)) === false) {
					//If 0 (All Items of a module) are denied and we have granted some then implicit denial to everything else is already in place
				} else {
					//If 0 (All Items of a module) are denied then add a false where clause
					$query->addWhere('((0=1) OR ' . ((!$key) ? '' : $key . '.') . $this->_tbl_key . ' IS NULL)');
				}

			}
		} else {
			//if there are no allowances, only show NULL joins!
			$query->addWhere('((0=1) OR ' . ((!$key) ? '' : $key . '.') . $this->_tbl_key . ' IS NULL)');
		}
	}
}

//writes out a single <option> element for display of departments
function showchilddept(&$a, $level = 1) {
	global $buffer, $department;
	$s = '<option value="' . $a['dept_id'] . '"' . (isset($department) && $department == $a['dept_id'] ? 'selected="selected"' : '') . '>';

	for ($y = 0; $y < $level; $y++) {
		if ($y + 1 == $level) {
			$s .= '';
		} else {
			$s .= '&nbsp;&nbsp;';
		}
	}

	$s .= '&nbsp;&nbsp;' . $a['dept_name'] . '</option>' . "\n";
	$buffer .= $s;

	//	echo $s;
}

//recursive function to display children departments.
function findchilddept(&$tarr, $parent, $level = 1) {
	$level = $level + 1;
	$n = count($tarr);
	for ($x = 0; $x < $n; $x++) {
		if ($tarr[$x]['dept_parent'] == $parent && $tarr[$x]['dept_parent'] != $tarr[$x]['dept_id']) {
			showchilddept($tarr[$x], $level);
			findchilddept($tarr, $tarr[$x]['dept_id'], $level);
		}
	}
}

function addDeptId($dataset, $parent) {
	global $dept_ids;
	foreach ($dataset as $data) {
		if ($data['dept_parent'] == $parent) {
			$dept_ids[] = $data['dept_id'];
			addDeptId($dataset, $data['dept_id']);
		}
	}
}
?>