<?php

/**
 * @package     web2project\modules\core
 *
 * @todo Move the 'address' fields to a generic table
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
    /*
     * @deprecated
     */
	public $company_custom = null;

	public function __construct() {
	  parent::__construct('companies', 'company_id');
	}

    public function isValid()
    {
        $baseErrorMsg = get_class($this) . '::store-check failed - ';

        if ('' == trim($this->company_name)) {
            $this->_error['company_name'] = $baseErrorMsg . 'company name is not set';
        }
        if ((int) $this->company_owner == 0) {
            $this->_error['company_owner'] = $baseErrorMsg . 'company owner is not set';
        }

        return (count($this->_error)) ? false : true;
    }
    
	// overload canDelete
	public function canDelete($msg = '', $oid = null, $joins = null) {
		$tables[] = array('label' => 'Projects', 'name' => 'projects', 'idfield' => 'project_id', 'joinfield' => 'project_company');
		$tables[] = array('label' => 'Departments', 'name' => 'departments', 'idfield' => 'dept_id', 'joinfield' => 'dept_company');
		$tables[] = array('label' => 'Users', 'name' => 'users', 'idfield' => 'user_id', 'joinfield' => 'user_company');
        $tables[] = array('label' => 'Contacts', 'name' => 'contacts', 'idfield' => 'contact_id', 'joinfield' => 'contact_company');
		// call the parent class method to assign the oid
		return parent::canDelete($msg, $oid, $tables);
	}

    protected function  hook_preStore() {
        $this->company_id = (int) $this->company_id;

        parent::hook_preStore();
    }

    public function hook_search() {
        $search['table'] = 'companies';
        $search['table_module'] = $search['table'];
        $search['table_key'] = 'company_id';
        $search['table_link'] = 'index.php?m=companies&a=view&company_id=';
        $search['table_title'] = 'Companies';
        $search['table_orderby'] = 'company_name';
        $search['search_fields'] = array('company_name', 'company_address1',
            'company_address2', 'company_city', 'company_state', 'company_zip',
            'company_primary_url', 'company_description', 'company_email');
        $search['display_fields'] = $search['search_fields'];

        return $search;
    }

    public function loadFull($notUsed = null, $companyId) {
        $q = $this->_getQuery();
        $q->addTable('companies');
        $q->addQuery('companies.*');
        $q->addQuery('con.contact_first_name');
        $q->addQuery('con.contact_last_name');
        $q->addQuery('con.contact_display_name as contact_name');
        $q->leftJoin('users', 'u', 'u.user_id = companies.company_owner');
        $q->leftJoin('contacts', 'con', 'u.user_contact = con.contact_id');
        $q->addWhere('companies.company_id = ' . (int) $companyId);

        $q->loadObject($this, true, false);
    }

    public function getCompanyList($notUsed = null, $companyType = -1, $searchString = '', $ownerId = 0, $orderby = 'company_name', $orderdir = 'ASC') {

        $q = $this->_getQuery();
        $q->addTable('companies', 'c');
        $q->addQuery('c.*, count(distinct p.project_id) as countp, ' .
            'count(distinct p2.project_id) as inactive, con.contact_first_name, ' .
            'con.contact_last_name, con.contact_display_name');
        $q->addJoin('projects', 'p', 'c.company_id = p.project_company AND p.project_active = 1');
        $q->addJoin('users', 'u', 'c.company_owner = u.user_id');
        $q->addJoin('contacts', 'con', 'u.user_contact = con.contact_id');
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
        $q->addOrder($orderby . ' ' . $orderdir);

        return $q->loadList();
    }

    public function getCompanies() {
        $where = $this->getAllowedSQL($this->_AppUI->user_id, 'company_id');

        return $this->loadAll('company_id', $where);
    }

	public static function getProjects(w2p_Core_CAppUI $AppUI, $companyId, $active = 1, $sort = 'project_name') {
		$fields = 'DISTINCT pr.project_id, pr.*, contact_first_name, ' .
                'contact_last_name, contact_display_name as contact_name, ' .
                'contact_display_name as project_owner, contact_display_name as user_username, user_id';

		$q = new w2p_Database_Query();
		$q->addTable('projects', 'pr');
		$q->addQuery($fields);
		$q->leftJoin('users', 'u', 'u.user_id = pr.project_owner');
		$q->leftJoin('contacts', 'con', 'u.user_contact = con.contact_id');
		if ((int) $companyId > 0) {
			$q->addWhere('pr.project_company = ' . (int) $companyId);
		}

		$projObj = new CProject();
//TODO: We need to convert this from static to use ->overrideDatabase() for testing.
		$projObj->setAllowedSQL($AppUI->user_id, $q, null, 'pr');

		$q->addWhere('pr.project_active = '. (int) $active);

        if(property_exists('CProject', $sort) || strpos($fields, $sort) !== false) {
			$q->addOrder($sort);
		} else {
            $q->addOrder('project_name');
        }

		return $q->loadList();
	}

	public static function getContacts(w2p_Core_CAppUI $AppUI, $companyId) {
		$results = array();

        if ($AppUI->isActiveModule('contacts') && canView('contacts') && (int) $companyId > 0) {
			$q = new w2p_Database_Query();
			$q->addQuery('c.*');
            $q->addQuery('c.contact_display_name as contact_name');
			$q->addQuery('dept_name, dept_id');
			$q->addTable('contacts', 'c');
			$q->leftJoin('companies', 'b', 'c.contact_company = b.company_id');
			$q->leftJoin('departments', '', 'contact_department = dept_id');
			$q->addWhere('contact_company = ' . (int) $companyId);
			$q->addWhere('
				(contact_private=0
					OR (contact_private=1 AND contact_owner=' . $AppUI->user_id . ')
					OR contact_owner IS NULL OR contact_owner = 0
				)');
			$department = new CDepartment;
//TODO: We need to convert this from static to use ->overrideDatabase() for testing.
			$department->setAllowedSQL($AppUI->user_id, $q);

			$q->addOrder('contact_first_name');
			$q->addOrder('contact_last_name');

			$results = $q->loadHashList('contact_id');
		}

		return $results;
	}

	public static function getUsers(w2p_Core_CAppUI $AppUI, $companyId) {

        $q = new w2p_Database_Query();
		$q->addTable('users');
		$q->addQuery('users.*, c.*');
        $q->addQuery('contact_display_name as contact_name');
		$q->addJoin('contacts', 'c', 'users.user_contact = contact_id', 'inner');
		$q->addJoin('departments', 'd', 'd.dept_id = contact_department');
		$q->addWhere('contact_company = ' . (int) $companyId);
		$q->addOrder('contact_last_name, contact_first_name');

		$department = new CDepartment;
//TODO: We need to convert this from static to use ->overrideDatabase() for testing.
		$department->setAllowedSQL($AppUI->user_id, $q);

		return $q->loadHashList('user_id');
	}

	public static function getDepartments(w2p_Core_CAppUI $AppUI, $companyId) {
		if ($AppUI->isActiveModule('departments') && canView('departments')) {
			$q = new w2p_Database_Query();
			$q->addTable('departments');
			$q->addQuery('departments.*, COUNT(contact_department) dept_users');
			$q->addJoin('contacts', 'c', 'c.contact_department = dept_id');
			$q->addWhere('dept_company = ' . (int) $companyId);
			$q->addGroup('dept_id');
			$q->addOrder('dept_parent, dept_name');

			$department = new CDepartment;
//TODO: We need to convert this from static to use ->overrideDatabase() for testing.
			$department->setAllowedSQL($AppUI->user_id, $q);

			return $q->loadList();
		}
	}
}