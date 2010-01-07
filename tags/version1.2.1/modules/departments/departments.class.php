<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

##
## CDepartment Class
##

class CDepartment extends CW2pObject {
	public $dept_id = null;
	public $dept_parent = null;
	public $dept_company = null;
	public $dept_name = null;
	public $dept_phone = null;
	public $dept_fax = null;
	public $dept_address1 = null;
	public $dept_address2 = null;
	public $dept_city = null;
	public $dept_state = null;
	public $dept_zip = null;
	public $dept_country = null;
	public $dept_url = null;
	public $dept_desc = null;
	public $dept_owner = null;
	public $dept_email = null;
	public $dept_type = null;

	public function __construct() {
	  parent::__construct('departments', 'dept_id');
	}

	public function load($deptId) {
		$q = new DBQuery;
		$q->addTable('departments', 'dep');
		$q->addQuery('dep.*, company_name');
		$q->addJoin('companies', 'com', 'com.company_id = dep.dept_company', 'inner');
		$q->addWhere('dep.dept_id = ' . (int) $deptId);
		$this->company_name = '';

		return $q->loadObject($this);
	}
	public function loadFull(CAppUI $AppUI = null, $deptId) {
    global $AppUI;

		$q = new DBQuery;
		$q->addTable('companies', 'com');
		$q->addTable('departments', 'dep');
		$q->addQuery('dep.*, company_name');
		$q->addQuery('con.contact_first_name');
		$q->addQuery('con.contact_last_name');
		$q->addJoin('users', 'u', 'u.user_id = dep.dept_owner');
		$q->addJoin('contacts', 'con', 'u.user_contact = con.contact_id');
		$q->addWhere('dep.dept_id = ' . (int) $deptId);
		$q->addWhere('dep.dept_company = company_id');

		$this->company_name = '';
		$this->contact_first_name = '';
		$this->contact_last_name = '';

		$q->loadObject($this);
	}
	public function loadOtherDepts(CAppUI $AppUI = null, $company_id, $removeDeptId = 0) {
		global $AppUI;

    $results = array();
    $q = new DBQuery;
		$q->addTable('departments', 'dep');
		$q->addQuery('dept_id, dept_name, dept_parent');
		$q->addWhere('dep.dept_company = ' . (int) $company_id);
		if ($removeDeptId > 0) {
			$q->addWhere('dep.dept_id <> ' . $removeDeptId);
		}
		$this->setAllowedSQL($AppUI->user_id, $q);
    $q->addOrder('dept_name');
    $deptList = $q->loadList();

    foreach ($deptList as $dept) {
    	$results[$dept['dept_id']] = $dept['dept_name'];
    }
    return $results;
	}

	public function getFilteredDepartmentList(CAppUI $AppUI = null, $deptType = -1, $searchString = '', $ownerId = 0, $orderby = 'dept_name', $orderdir = 'ASC') {
    global $AppUI;

	  $orderby = (in_array($orderby, array('dept_name', 'dept_type', 'countp', 'inactive'))) ? $orderby : 'dept_name';
	  $q = new DBQuery;
	  $q->addTable('departments');
      $q->addQuery('departments.*, COUNT(ct.contact_department) dept_users, count(distinct p.project_id) as countp, count(distinct p2.project_id) as inactive, con.contact_first_name, con.contact_last_name');
      $q->addJoin('project_departments', 'pd', 'pd.department_id = dept_id');
      $q->addJoin('projects', 'p', 'pd.project_id = p.project_id AND p.project_active = 1');
      $q->leftJoin('users', 'u', 'dept_owner = u.user_id');
      $q->leftJoin('contacts', 'con', 'u.user_contact = con.contact_id');
      $q->addJoin('projects', 'p2', 'pd.project_id = p2.project_id AND p2.project_active = 0');
      $q->addJoin('contacts', 'ct', 'ct.contact_department = dept_id');
      $q->addGroup('dept_id');
      $q->addOrder('dept_parent, dept_name');
      
      $oCpy = new CCompany();
      $where = $this->getAllowedSQL($AppUI->user_id, 'c.company_id');
      $q->addWhere($where);
      
      if ($deptType > -1) {
      	$q->addWhere('dept_type = ' . (int) $deptType);
      }
      if ($searchString != '') {
      	$q->addWhere("dept_name LIKE '%$searchString%'");
      }
      if ($ownerId > 0) {
      	$q->addWhere('dept_owner = '.$ownerId);
      }
      $q->addGroup('dept_id');
      $q->addOrder($orderby . ' ' . $orderdir);
      
      return $q->loadList();
	}

	public function bind($hash) {
		if (!is_array($hash)) {
			return get_class($this) . "::bind failed";
		} else {
			$q = new DBQuery;
			$q->bindHashToObject($hash, $this);

			return null;
		}
	}

	public function check() {
    $errorArray = array();
    $baseErrorMsg = get_class($this) . '::store-check failed - ';

    if (0 == (int) $this->dept_company) {
      $errorArray['dept_company'] = $baseErrorMsg . 'department company is not set';
    }
    if ('' == trim($this->dept_name)) {
      $errorArray['dept_name'] = $baseErrorMsg . 'department name is not set';
    }
    if (0 != $this->dept_id && $this->dept_id == $this->dept_parent) {
      $errorArray['parentError'] = $baseErrorMsg . 'a department cannot be its own parent';
    }

		return $errorArray;
	}

	public function store(CAppUI $AppUI = null) {
    global $AppUI;

    $perms = $AppUI->acl();
    $stored = false;

    $errorMsgArray = $this->check();

    if (count($errorMsgArray) > 0) {
      return $errorMsgArray;
    }

		if ($this->dept_id) {
			$q = new DBQuery;
			$ret = $q->updateObject('departments', $this, 'dept_id', false);
      addHistory('departments', $this->dept_id, 'update', $this->dept_name, $this->dept_id);
      $stored = true;
		} else {
			$q = new DBQuery;
			$ret = $q->insertObject('departments', $this, 'dept_id');
      addHistory('departments', $this->dept_id, 'add', $this->dept_name, $this->dept_id);
      $stored = true;
		}
    return $stored;
	}

	public function delete(CAppUI $AppUI = null) {
		global $AppUI;

    $q = new DBQuery;
		$q->addTable('departments', 'dep');
		$q->addQuery('dep.dept_id');
		$q->addWhere('dep.dept_parent = ' . (int)$this->dept_id);
		$rows = $q->loadList();
		$q->clear();

		if (count($rows)) {
			return 'deptWithSub';
		}

		$q->addTable('project_departments', 'pd');
		$q->addQuery('pd.project_id');
		$q->addWhere('pd.department_id = ' . (int)$this->dept_id);
		$rows = $q->loadList();
		$q->clear();

		if (count($rows)) {
			return 'deptWithProject';
		}

		$q->addQuery('*');
		$q->setDelete('departments');
		$q->addWhere('dept_id = ' . (int)$this->dept_id);
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
	public function getAllowedRecords($uid, $fields = '*', $orderby = '', $index = null, $extra = null) {
		global $AppUI;

    $perms = $AppUI->acl();
		$uid = intval($uid);
		$uid || exit('FATAL ERROR<br />' . get_class($this) . '::getAllowedRecords failed');
		$deny = &$perms->getDeniedItems($this->_tbl, $uid);
		$allow = &$perms->getAllowedItems($this->_tbl, $uid);

		$this->_query->clear();
		$this->_query->addQuery($fields);
		$this->_query->addTable($this->_tbl);

		if ($extra['from']) {
			$this->_query->addTable($extra['from']);
		}

		if ($extra['join'] && $extra['on']) {
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

	public function getAllowedSQL($uid, $index = null) {
		global $AppUI;

    $perms = $AppUI->acl();
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

	public function setAllowedSQL($uid, &$query, $index = null, $key = null) {
		global $AppUI;

    $perms = $AppUI->acl();
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
	public static function getDepartmentList(CAppUI $AppUI = null, $companyId, $departmentId = 0) {
		global $AppUI;

    $q = new DBQuery;
		$q->addTable('departments');
		$q->addQuery('dept_id, dept_name');
		if (is_int($departmentId)) {
			$q->addWhere('dept_parent = ' . (int) $departmentId);
		}
		$q->addWhere('dept_company = ' . (int) $companyId);
		$q->addOrder('dept_name');
		$department = new CDepartment;
		$department->setAllowedSQL($AppUI->user_id, $q);

		return $q->loadHashList('dept_id');
	}
	public static function getContactList($AppUI = null, $deptId) {
		global $AppUI;

    $q = new DBQuery;
		$q->addTable('contacts', 'con');
		$q->addQuery('contact_id, con.contact_first_name');
		$q->addQuery('con.contact_last_name, contact_email, contact_phone');
		$q->addWhere('contact_department = ' . (int) $deptId);
		$q->addWhere('(contact_owner = ' . (int) $AppUI->user_id . ' OR contact_private = 0)');
		$q->addOrder('contact_first_name');

		return $q->loadHashList('contact_id');
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

	$s .= '&nbsp;&nbsp;' . $a['dept_name'] . '</option>';
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