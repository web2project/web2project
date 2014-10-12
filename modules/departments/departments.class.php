<?php
/**
 * @package     web2project\modules\misc
 *
 * @todo    refactor static methods
 */

class CDepartment extends w2p_Core_BaseObject
{
    /**
     * @todo    these parameters should use department_ not dept_ as the prefix
     */
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

    /**
     * This is a nasty hack because our property names don't follow our naming conventions. This is legacy
     *   code that will be killed eventually.
     */
    public function __get($name)
    {
        $field = str_replace('department', 'dept', $name);
        return $this->$field;
    }

    /**
     * I already don't like this one..
     *
     * @deprecated
     */
    public function getProjects($department_id)
    {
        $q = $this->_getQuery();
		$q->addTable('projects', 'p');
        $q->addQuery('p.project_id, company_id');
		$q->addQuery('project_color_identifier, project_percent_complete, project_priority, project_name,
            company_name, project_start_date, project_scheduled_hours, project_owner,
            project_end_date, project_actual_end_date, project_task_count, project_status, project_scheduled_hours');
        $q->addJoin('companies', 'c', 'company_id = project_company');
        $q->addJoin('project_departments', 'd', 'd.project_id = p.project_id');
        $q->addWhere('department_id = ' . (int) $department_id);

        return $q->loadList();
    }

	public function loadOtherDepts($notUsed = null, $company_id, $removeDeptId = 0) {
        $results = array();
        $q = $this->_getQuery();
		$q->addTable('departments', 'dep');
		$q->addQuery('dept_id, dept_name, dept_parent');
		$q->addWhere('dep.dept_company = ' . (int) $company_id);
		if ($removeDeptId > 0) {
			$q->addWhere('dep.dept_id <> ' . $removeDeptId);
		}
		$q = $this->setAllowedSQL($this->_AppUI->user_id, $q);
        $q->addOrder('dept_name');
        $deptList = $q->loadList();

        foreach ($deptList as $dept) {
            $results[$dept['dept_id']] = $dept['dept_name'];
        }
        return $results;
	}

	public function getFilteredDepartmentList($notUsed = null, $deptType = -1, $searchString = '', $ownerId = 0, $orderby = 'dept_name', $orderdir = 'ASC') {

        $q = $this->_getQuery();
        $q->addTable('departments');
        $q->addQuery('departments.*, count(distinct p.project_id) as countp, count(distinct p2.project_id) as inactive');
        $q->addJoin('project_departments', 'pd', 'pd.department_id = dept_id');
        $q->addJoin('projects', 'p', 'pd.project_id = p.project_id AND p.project_active = 1');
        $q->addJoin('projects', 'p2', 'pd.project_id = p2.project_id AND p2.project_active = 0');
        $q->addGroup('dept_id');
        $q->addOrder('dept_name');

        $oCpy = new CCompany();
        $oCpy->overrideDatabase($this->_query);
        $where = $oCpy->getAllowedSQL($this->_AppUI->user_id, 'dept_company');
        $q->addWhere($where);
        $q = $this->setAllowedSQL($this->_AppUI->user_id, $q);

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
        $orderby = (property_exists($this, $orderby) || in_array($orderby, array('countp', 'inactive')))
            ? $orderby : 'dept_name';
        $q->addOrder($orderby . ' ' . $orderdir);

        return $q->loadList();
	}

    public function isValid()
    {
        $baseErrorMsg = get_class($this) . '::store-check failed - ';

        if (0 == (int) $this->dept_company) {
            $this->_error['dept_company'] = $baseErrorMsg . 'department company is not set';
        }
        if ('' == trim($this->dept_name)) {
            $this->_error['dept_name'] = $baseErrorMsg . 'department name is not set';
        }
        if (0 != $this->dept_id && $this->dept_id == $this->dept_parent) {
            $this->_error['parentError'] = $baseErrorMsg . 'a department cannot be its own parent';
        }

        return (count($this->_error)) ? false : true;
    }

    public function canDelete($notUsed = null, $notUsed2 = null, $notUsed3 = null)
    {
        $rows = $this->loadAll('dept_id', 'dept_parent = '. (int)$this->dept_id);
        if (count($rows)) {
            $this->_error['deptWithSub'] = 'deptWithSub';
            return false;
        }

        $q = $this->_getQuery();
        $q->addTable('project_departments', 'pd');
        $q->addQuery('pd.project_id');
        $q->addWhere('pd.department_id = ' . (int)$this->dept_id);
        $rows = $q->loadList();

        if (count($rows)) {
            $this->_error['deptWithProject'] = 'deptWithProject';
            return false;
        }

        return true;
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
//TODO: this modifies the core $_query property
	public function getAllowedRecords($uid, $fields = '*', $orderby = '', $index = null, $extra = null, $unused = '')
    {
        $uid = (int) $uid;
        if (!$uid) {
            return array();
        }
		$deny = $this->_perms->getDeniedItems($this->_tbl, $uid);
		$allow = $this->_perms->getAllowedItems($this->_tbl, $uid);

		$q = $this->_getQuery();
		$q->addQuery($fields);
		$q->addTable($this->_tbl);

		if ($extra['from']) {
			$q->addTable($extra['from']);
		}

		if ($extra['join'] && $extra['on']) {
			$q->addJoin($extra['join'], $extra['join'], $extra['on']);
		}

		if (count($allow)) {
			if ((array_search('0', $allow)) === false) {
				//If 0 (All Items of a module) are not permited then just add the allowed items only
				$q->addWhere('(' . $this->_tbl_key . ' IN (' . implode(',', $allow) . ') OR ' . $this->_tbl_key . ' IS NULL)');
			}
			//Denials are only required if we were able to see anything in the first place so now we handle the denials
			if (count($deny)) {
				if ((array_search('0', $deny)) === false) {
					//If 0 (All Items of a module) are not on the denial array then just deny the denied items
					$q->addWhere('(' . $this->_tbl_key . ' NOT IN (' . implode(',', $deny) . ') OR ' . $this->_tbl_key . ' IS NULL)');
				} elseif ((array_search('0', $allow)) === false) {
					//If 0 (All Items of a module) are denied and we have granted some then implicit denial to everything else is already in place
				} else {
					//If 0 (All Items of a module) are denied then add a false where clause
					$q->addWhere('(' . $this->_tbl_key . ' IS NULL)');
				}
			}
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
        $uid = (int) $uid;
        if (!$uid) {
            return array();
        }
		$deny = $this->_perms->getDeniedItems($this->_tbl, $uid);
		$allow = $this->_perms->getAllowedItems($this->_tbl, $uid);

		if (!isset($index)) {
			$index = $this->_tbl_key;
        }
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

	public function setAllowedSQL($uid, $query, $index = null, $key = null)
    {
        $uid = (int) $uid;
        if (!$uid) {
            return $query;
        }
		$deny = $this->_perms->getDeniedItems($this->_tbl, $uid);
		$allow = $this->_perms->getAllowedItems($this->_tbl, $uid);
		// Make sure that we add the table otherwise dependencies break
		if (isset($index)) {
			if (!$key) {
				$key = substr($this->_tbl, 0, 3);
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

        return $query;
	}

    /**
     * @param     $companyId
     * @param int $departmentId
     *
     * @return Associative
     */
    public function departments($companyId, $departmentId = 0)
    {
        $q = $this->_getQuery();
        $q->addTable('departments');
        $q->addQuery('dept_id, dept_name');
        if (is_int($departmentId)) {
            $q->addWhere('dept_parent = ' . (int) $departmentId);
        }
        $q->addWhere('dept_company = ' . (int) $companyId);
        $q->addOrder('dept_name');

        $q = $this->setAllowedSQL($this->_AppUI->user_id, $q);

        return $q->loadHashList('dept_id');
    }

    public function contacts($departmentId)
    {
        $results = array();

        if ($this->_AppUI->isActiveModule('contacts') && canView('contacts') && (int) $departmentId > 0) {
            $q = $this->_getQuery();
            $q->addTable('contacts', 'con');
            $q->addQuery('con.*, con.contact_display_name as contact_name');
            $q->addWhere('contact_department = ' . (int) $departmentId);
            $q->addWhere('(contact_owner = ' . (int) $this->_AppUI->user_id . ' OR contact_private = 0)');
            $q->addOrder('contact_first_name');

            $results = $q->loadHashList('contact_id');
        }

        return $results;
    }

    protected function hook_preStore()
    {
        $this->dept_owner = (int) $this->dept_owner ? $this->dept_owner : $this->_AppUI->user_id;
        $this->dept_url = str_replace(array('"', '"', '<', '>'), '', $this->dept_url);
    }

    public function hook_search() {
        $search['table'] = 'departments';
        $search['table_module'] = 'departments';
        $search['table_key'] = 'dept_id'; // primary key in searched table
        $search['table_link'] = 'index.php?m=departments&a=view&dept_id='; // first part of link
        $search['table_title'] = 'Departments';
        $search['table_orderby'] = 'dept_name';
        $search['search_fields'] = array('dept_name', 'dept_address1',
            'dept_address2', 'dept_city', 'dept_state', 'dept_zip', 'dept_url', 'dept_desc');
        $search['display_fields'] = $search['search_fields'];

        return $search;
    }

    /**
     * @deprecated
     */
    public static function getContactList($AppUI = null, $deptId)
    {
        trigger_error("The CDepartment::getContactList static method has been deprecated in 3.1 and will be removed in v4.0. Please use CDepartment->contacts() instead.", E_USER_NOTICE );

        $department = new CDepartment();
        return $department->contacts($deptId);
    }

    /**
     * @deprecated
     */
    public static function getDepartmentList($AppUI = null, $companyId, $departmentId = 0)
    {
        trigger_error("The CDepartment::getDepartmentList static method has been deprecated in 3.1 and will be removed in v4.0. Please use CDepartment->departments() instead.", E_USER_NOTICE );

        $department = new CDepartment();
        return $department->departments($companyId, $departmentId);
    }
}