<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

/**
 *	@package web2Project
 *	@subpackage modules
 *	@version $Revision$
 */

/**
 *	Companies Class
 *	@todo Move the 'address' fields to a generic table
 */
class CCompany extends CW2pObject {
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
	public $company_custom = null;

	public function __construct() {
		parent::__construct('companies', 'company_id');
	}

	// overload check
	public function check() {
		$this->company_id = intval($this->company_id);

		if ('' == mb_trim($this->company_name)) {
			return 'company name is NULL';
		}

		return null; // object is ok
	}

	// overload canDelete
	public function canDelete(&$msg, $oid = null) {
		$tables[] = array('label' => 'Projects', 'name' => 'projects', 'idfield' => 'project_id', 'joinfield' => 'project_company');
		$tables[] = array('label' => 'Departments', 'name' => 'departments', 'idfield' => 'dept_id', 'joinfield' => 'dept_company');
		$tables[] = array('label' => 'Users', 'name' => 'users', 'idfield' => 'user_id', 'joinfield' => 'user_company');
		// call the parent class method to assign the oid
		return parent::canDelete($msg, $oid, $tables);
	}
	
	public function loadFull($companyId) {
		$q = new DBQuery;
		$q->addTable('companies');
		$q->addQuery('companies.*');
		$q->addQuery('con.contact_first_name');
		$q->addQuery('con.contact_last_name');
		$q->leftJoin('users', 'u', 'u.user_id = companies.company_owner');
		$q->leftJoin('contacts', 'con', 'u.user_contact = con.contact_id');
		$q->addWhere('companies.company_id = ' . (int) $companyId);

		$q->loadObject($this, true, false);
	}
	public function getCompanyList($AppUI, $companyType = -1, $searchString = '', $ownerId = 0, $orderby = 'company_name', $orderdir = 'ASC') {
		$q = new DBQuery;
		$q->addTable('companies', 'c');
		$q->addQuery('c.company_id, c.company_name, c.company_type, c.company_description, count(distinct p.project_id) as countp, count(distinct p2.project_id) as inactive, con.contact_first_name, con.contact_last_name');
		$q->addJoin('projects', 'p', 'c.company_id = p.project_company AND p.project_active = 1');
		$q->addJoin('users', 'u', 'c.company_owner = u.user_id');
		$q->addJoin('contacts', 'con', 'u.user_contact = con.contact_id');
		$q->addJoin('projects', 'p2', 'c.company_id = p2.project_company AND p2.project_active = 0');

		$oCpy = new CCompany();
		$where = $this->getAllowedSQL($AppUI->user_id, 'c.company_id');
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

	public static function getProjects($AppUI, $companyId, $active = 1, $sort = 'project_name') {
		$fields = 'pr.project_id, project_name, project_start_date, ' .
				'project_status, project_target_budget, project_start_date, ' .
				'project_priority, contact_first_name, contact_last_name';

		$q = new DBQuery;
		$q->addTable('projects', 'pr');
		$q->addQuery($fields);
		$q->leftJoin('users', 'u', 'u.user_id = pr.project_owner');
		$q->leftJoin('contacts', 'con', 'u.user_contact = con.contact_id');
		if ((int) $companyId > 0) {
			$q->addWhere('pr.project_company = ' . (int) $companyId);
		}
		
		$projObj = new CProject();
		$projObj->setAllowedSQL($AppUI->user_id, $q, null, 'pr');
		
		$q->addWhere('pr.project_active = '. (int) $active);
		
		if (strpos($fields, $sort) !== false) {
			$q->addOrder($sort);
		}
		
		return $q->loadList();
	}
	public static function getContacts($AppUI, $companyId) {
		$results = array();
		$perms = $AppUI->acl();

		if ($AppUI->isActiveModule('contacts') && $perms->checkModule('contacts', 'view') && (int) $companyId > 0) {
			$q = new DBQuery;
			$q->addQuery('a.*');
			$q->addQuery('dept_name');
			$q->addTable('contacts', 'a');
			$q->leftJoin('companies', 'b', 'a.contact_company = b.company_id');
			$q->leftJoin('departments', '', 'contact_department = dept_id');
			$q->addWhere('contact_company = ' . (int) $companyId);
			$q->addWhere('
				(contact_private=0
					OR (contact_private=1 AND contact_owner=' . $AppUI->user_id . ')
					OR contact_owner IS NULL OR contact_owner = 0
				)');

			$department = new CDepartment;
			$department->setAllowedSQL($AppUI->user_id, $q);

			$q->addOrder('contact_first_name');
			$q->addOrder('contact_last_name');

			$results = $q->loadHashList('contact_id');
		}

		return $results;
	}

	public static function getUsers($AppUI, $companyId) {
		$q = new DBQuery;
		$q->addTable('users');
		$q->addQuery('user_id, user_username, contact_first_name, contact_last_name');
		$q->addJoin('contacts', 'c', 'users.user_contact = contact_id', 'inner');
		$q->addJoin('departments', 'd', 'd.dept_id = contact_department');
		$q->addWhere('contact_company = ' . (int) $companyId);
		$q->addOrder('contact_last_name, contact_first_name');

		$department = new CDepartment;
		$department->setAllowedSQL($AppUI->user_id, $q);
		
		return $q->loadHashList('user_id');
	}
	public static function getDepartments($AppUI, $companyId) {
		$perms = $AppUI->acl();

		if ($AppUI->isActiveModule('departments') && $perms->checkModule('departments', 'view')) {
			$q = new DBQuery;
			$q->addTable('departments');
			$q->addQuery('departments.*, COUNT(contact_department) dept_users');
			$q->addJoin('contacts', 'c', 'c.contact_department = dept_id');
			$q->addWhere('dept_company = ' . (int) $companyId);
			$q->addGroup('dept_id');
			$q->addOrder('dept_parent, dept_name');

			$department = new CDepartment;
			$department->setAllowedSQL($AppUI->user_id, $q);

			return $q->loadList();
		}
	}
}