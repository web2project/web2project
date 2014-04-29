<?php

/**
 * @package     web2project\modules\core
 *
 * @todo    Move the 'address' fields to a generic table
 * @todo    refactor static methods
 */

class CCompany extends w2p_Core_BaseObject {
	/**
 	@var int Primary Key */
	public $company_id = null;
	/**
 	@var string */
	public $company_name = null;

	// these next fields should be ported to a generic address book
	public $company_phone1 = null;
	public $company_phone2 = null;
	public $company_fax = null;
	public $company_address1 = null;
	public $company_address2 = null;
	public $company_city = null;
	public $company_state = null;
	public $company_zip = null;
	public $company_country = null;
	public $company_email = null;
	/**
 	@var string */
	public $company_primary_url = null;
	/**
 	@var int */
	public $company_owner = null;
	/**
 	@var string */
	public $company_description = null;
	/**
 	@var int */
	public $company_type = null;

	public function __construct() {
	  parent::__construct('companies', 'company_id');
	}

    public function isValid()
    {
        $baseErrorMsg = get_class($this) . '::store-check failed - ';

        if ('' == trim($this->company_name)) {
            $this->_error['company_name'] = $baseErrorMsg . 'company name is not set';
        }

        return (count($this->_error)) ? false : true;
    }

    public function canDelete($notUsed = null, $notUsed2 = null, $notUsed3 = null)
    {
		$tables[] = array('label' => 'Projects', 'name' => 'projects', 'idfield' => 'project_id', 'joinfield' => 'project_company');
		$tables[] = array('label' => 'Departments', 'name' => 'departments', 'idfield' => 'dept_id', 'joinfield' => 'dept_company');
		$tables[] = array('label' => 'Users', 'name' => 'users', 'idfield' => 'user_id', 'joinfield' => 'user_company');
        $tables[] = array('label' => 'Contacts', 'name' => 'contacts', 'idfield' => 'contact_id', 'joinfield' => 'contact_company');
		// call the parent class method to assign the oid
		return parent::canDelete('', null, $tables);
	}

    protected function hook_preStore() {
        $this->company_id = (int) $this->company_id;
        $this->company_owner = (int) $this->company_owner ? $this->company_owner : $this->_AppUI->user_id;
        $this->company_primary_url = str_replace(array('"', '"', '<', '>'), '', $this->company_primary_url);

        parent::hook_preStore();
    }

    public function hook_search() {
        $search['table']            = $this->_tbl;
        $search['table_module']     = $this->_tbl;
        $search['table_key']        = $this->_tbl_key;
        $search['table_link']       = 'index.php?m='.$search['table_module'].'&a=view&'.$search['table_key'].'=';
        $search['table_title']      = ucwords($this->_tbl);
        $search['table_orderby']    = 'company_name';
        $search['search_fields']    = array('company_name', 'company_address1', 'company_address2', 'company_city',
            'company_state', 'company_zip', 'company_primary_url', 'company_description', 'company_email');
        $search['display_fields']   = $search['search_fields'];

        return $search;
    }

    public function getCompanyList($notUsed = null, $companyType = -1, $searchString = '', $ownerId = 0, $orderby = 'company_name', $orderdir = 'ASC') {

        $q = $this->_getQuery();
        $q->addTable('companies', 'c');
        $q->addQuery('c.*, count(distinct p.project_id) as countp, count(distinct p2.project_id) as inactive');
        $q->addJoin('projects', 'p', 'c.company_id = p.project_company AND p.project_active = 1');
        $q->addJoin('projects', 'p2', 'c.company_id = p2.project_company AND p2.project_active = 0');

        $where = $this->getAllowedSQL($this->_AppUI->user_id, 'c.company_id');
        $q->addWhere($where);

        if ($companyType > -1) {
            $q->addWhere('c.company_type = ' . (int) $companyType);
        }
        if ($searchString != '') {
            $q->addWhere('c.company_name LIKE "%'.$searchString.'%"');
        }
        if ($ownerId > 0) {
            $q->addWhere('c.company_owner = '.$ownerId);
        }
        $q->addGroup('c.company_id');
        $orderby = (property_exists($this, $orderby) || in_array($orderby, array('countp', 'inactive')))
            ? $orderby : 'company_name';
        $q->addOrder($orderby . ' ' . $orderdir);

        return $q->loadList();
    }

    public function loadAll($order = 'company_name', $where = null)
    {
        $filter = $this->getAllowedSQL($this->_AppUI->user_id, 'company_id');
        $filter = implode(' AND ', $filter);
        $filter .= ($where) ? ' AND ' . $where : '';

        return parent::loadAll($order, $filter);
    }

    /**
     * @param w2p_Core_CAppUI $AppUI
     * @param                 $companyId
     * @param int             $active
     * @param string          $sort
     *
     * @return Array
     */
    public function projects(w2p_Core_CAppUI $AppUI, $companyId, $active = 1, $sort = 'project_name')
    {
        $fields = 'DISTINCT pr.project_id, pr.*, contact_first_name, ' .
            'contact_last_name, contact_display_name as contact_name, ' .
            'contact_display_name as project_owner, contact_display_name as user_username, user_id';

        $q = $this->_getQuery();
        $q->addTable('projects', 'pr');
        $q->addQuery($fields);
        $q->leftJoin('users', 'u', 'u.user_id = pr.project_owner');
        $q->leftJoin('contacts', 'con', 'u.user_contact = con.contact_id');
        if ((int) $companyId > 0) {
            $q->addWhere('pr.project_company = ' . (int) $companyId);
        }

        $projObj = new CProject();
//TODO: We need to convert this from static to use ->overrideDatabase() for testing.
        $q = $projObj->setAllowedSQL($AppUI->user_id, $q, null, 'pr');

        $q->addWhere('pr.project_active = '. (int) $active);

        if(property_exists('CProject', $sort) || strpos($fields, $sort) !== false) {
            $q->addOrder($sort);
        } else {
            $q->addOrder('project_name');
        }

        return $q->loadList();
    }

    public function contacts($companyId)
    {
        $results = array();

        if ($this->_AppUI->isActiveModule('contacts') && canView('contacts') && (int) $companyId > 0) {
            $q = $this->_getQuery();
            $q->addQuery('c.*');
            $q->addQuery('c.contact_display_name as contact_name');
            $q->addQuery('dept_name, dept_id');
            $q->addTable('contacts', 'c');
            $q->leftJoin('companies', 'b', 'c.contact_company = b.company_id');
            $q->leftJoin('departments', '', 'contact_department = dept_id');
            $q->addWhere('contact_company = ' . (int) $companyId);
            $q->addWhere('
				(contact_private=0
					OR (contact_private=1 AND contact_owner=' . $this->_AppUI->user_id . ')
					OR contact_owner IS NULL OR contact_owner = 0
				)');
            $department = new CDepartment;
//TODO: We need to convert this from static to use ->overrideDatabase() for testing.
            $q = $department->setAllowedSQL($this->_AppUI->user_id, $q);

            $q->addOrder('contact_first_name');
            $q->addOrder('contact_last_name');

            $results = $q->loadHashList('contact_id');
        }

        return $results;
    }

    public function users($companyId)
    {
        $q = $this->_getQuery();
        $q->addTable('users');
        $q->addQuery('users.*, c.*');
        $q->addQuery('contact_display_name as contact_name');
        $q->addJoin('contacts', 'c', 'users.user_contact = contact_id', 'inner');
        $q->addJoin('departments', 'd', 'd.dept_id = contact_department');
        $q->addWhere('contact_company = ' . (int) $companyId);
        $q->addOrder('contact_last_name, contact_first_name');

        $department = new CDepartment;
//TODO: We need to convert this from static to use ->overrideDatabase() for testing.
        $q = $department->setAllowedSQL($this->_AppUI->user_id, $q);

        return $q->loadHashList('user_id');
    }

    public function departments($companyId)
    {
        if ($this->_AppUI->isActiveModule('departments') && canView('departments')) {
            $q = $this->_getQuery();
            $q->addTable('departments');
            $q->addQuery('departments.*, COUNT(contact_department) dept_users');
            $q->addJoin('contacts', 'c', 'c.contact_department = dept_id');
            $q->addWhere('dept_company = ' . (int) $companyId);
            $q->addGroup('dept_id');
            $q->addOrder('dept_parent, dept_name');

            $department = new CDepartment();
            $q = $department->setAllowedSQL($this->_AppUI->user_id, $q);

            return $q->loadList();
        }
    }

    /** @deprecated */
    public function getCompanies() {
        trigger_error("The CCompany->getCompanies method has been deprecated in 3.2 and will be removed in v5.0. Please use CCompany->loadAll() instead.", E_USER_NOTICE );

        return $this->loadAll();
    }
    /**
     * @deprecated
     */
    public static function getProjects(w2p_Core_CAppUI $AppUI, $companyId, $active = 1, $sort = 'project_name')
    {
        trigger_error("The CCompany::getProjects static method has been deprecated in 3.1 and will be removed in v4.0. Please use CCompany->projects() instead.", E_USER_NOTICE );

        $company = new CCompany();
        return $company->projects($AppUI, $companyId, $active, $sort);
    }
    /**
     * @deprecated
     */
    public static function getContacts($notUsed, $companyId)
    {
        trigger_error("The CCompany::getContacts static method has been deprecated in 3.1 and will be removed in v4.0. Please use CCompany->contacts() instead.", E_USER_NOTICE );

        $company = new CCompany();
        return $company->contacts($companyId);
    }
    /**
     * @deprecated
     */
    public static function getUsers($notUsed, $companyId) {
        trigger_error("The CCompany::getUsers static method has been deprecated in 3.1 and will be removed in v4.0. Please use CCompany->users() instead.", E_USER_NOTICE );

        $company = new CCompany();
        return $company->users($companyId);
    }
    /**
     * @deprecated
     */
	public static function getDepartments($notUsed, $companyId)
    {
        trigger_error("The CCompany::getDepartments static method has been deprecated in 3.1 and will be removed in v4.0. Please use CCompany->departments() instead.", E_USER_NOTICE );

        $company = new CCompany();
        return $company->departments($companyId);
	}
}