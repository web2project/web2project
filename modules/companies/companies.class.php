<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

/**
 *	@package web2Project
 *	@subpackage modules
 *	@version $Revision$
 */

require_once ($AppUI->getSystemClass('w2p'));
require_once ($AppUI->getModuleClass('departments'));

/**
 *	Companies Class
 *	@todo Move the 'address' fields to a generic table
 */
class CCompany extends CW2pObject {
	/**
 	@var int Primary Key */
	var $company_id = null;
	/**
 	@var string */
	var $company_name = null;

	// these next fields should be ported to a generic address book
	var $company_phone1 = null;
	var $company_phone2 = null;
	var $company_fax = null;
	var $company_address1 = null;
	var $company_address2 = null;
	var $company_city = null;
	var $company_state = null;
	var $company_zip = null;
	var $company_country = null;
	var $company_email = null;
	/**
 	@var string */
	var $company_primary_url = null;
	/**
 	@var int */
	var $company_owner = null;
	/**
 	@var string */
	var $company_description = null;
	/**
 	@var int */
	var $company_type = null;
	var $company_custom = null;

	function CCompany() {
		$this->CW2pObject('companies', 'company_id');
	}

	// overload check
	function check() {
		if ($this->company_id === null) {
			return 'company id is NULL';
		}
		$this->company_id = intval($this->company_id);

		return null; // object is ok
	}

	// overload canDelete
	function canDelete(&$msg, $oid = null) {
		$tables[] = array('label' => 'Projects', 'name' => 'projects', 'idfield' => 'project_id', 'joinfield' => 'project_company');
		$tables[] = array('label' => 'Departments', 'name' => 'departments', 'idfield' => 'dept_id', 'joinfield' => 'dept_company');
		$tables[] = array('label' => 'Users', 'name' => 'users', 'idfield' => 'user_id', 'joinfield' => 'user_company');
		// call the parent class method to assign the oid
		return CW2pObject::canDelete($msg, $oid, $tables);
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
}
?>