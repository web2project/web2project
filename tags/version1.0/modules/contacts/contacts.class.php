<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

/**
 *	@package web2Project
 *	@subpackage modules
 *	@version $Revision$
 */

require_once $AppUI->getSystemClass('w2p');
require_once $AppUI->getSystemClass('libmail');
require_once $AppUI->getModuleClass('companies');
require_once $AppUI->getModuleClass('departments');

/**
 * Contacts class
 */
class CContact extends CW2pObject {
	/**
 	@public int */
	public $contact_id = null;
	/**
 	@public string */
	public $contact_first_name = '';
	/**
 	@public string */
	public $contact_last_name = '';
	public $contact_order_by = '';
	public $contact_title = null;
	public $contact_job = null;
	public $contact_birthday = null;
	public $contact_company = null;
	public $contact_department = null;
	public $contact_type = null;
	public $contact_email = null;
	public $contact_email2 = null;
	public $contact_phone = null;
	public $contact_phone2 = null;
	public $contact_fax = null;
	public $contact_mobile = null;
	public $contact_address1 = null;
	public $contact_address2 = null;
	public $contact_city = null;
	public $contact_state = null;
	public $contact_zip = null;
	public $contact_url = null;
	public $contact_icq = null;
	public $contact_aol = null;
	public $contact_yahoo = null;
	public $contact_msn = null;
	public $contact_jabber = null;
	public $contact_skype = null;
	public $contact_google = null;
	public $contact_notes = null;
	public $contact_project = null;
	public $contact_country = null;
	public $contact_icon = null;
	public $contact_owner = null;
	public $contact_private = null;
	public $contact_updatekey = null;
	public $contact_lastupdate = null;
	public $contact_updateasked = null;
	
	public $contact_methods = array();

	function CContact() {
		$this->CW2pObject('contacts', 'contact_id');
	}

	public function fullLoad($AppUI, $contactId) {
		$perms = $AppUI->acl();
		$canRead = !$perms->checkModule('contacts', 'view', $contactId);

		if ($canRead) {
			$q = new DBQuery;
			$q->addTable('contacts');
			$q->addJoin('companies', 'cp', 'cp.company_id = contact_company');
			$q->addWhere('contact_id = ' . (int) $contactId);

			$q->loadObject($this);
		}
	}
	
	function store() {
		/*
		 *  This  validates that any Contact saved will have a Display Name as
		 * required by various dropdowns, etc throughout the system.  This is
		 * mostly required when Contacts are generated via programatic methods and
		 * not through the add/edit UI.
		 */
		if(strlen($this->contact_order_by) <= 1 || $this->contact_order_by == null) {
			//TODO: this should use the USERFORMAT to determine how display names are generated
			if ($this->contact_first_name == null && $this->contact_last_name == null) {
				$this->contact_order_by = $this->contact_email;
			} else {
				$this->contact_order_by = trim($this->contact_first_name.' '.$this->contact_last_name);
			}
		}
		if($this->contact_first_name == null) {
			$this->contact_first_name = '';
		}
		if($this->contact_last_name == null) {
			$this->contact_last_name = '';
		}

		parent::store();
	}

	function check() {
		if ($this->contact_id === null) {
			return 'contact id is NULL';
		}
		// ensure changes of state in checkboxes is captured
		$this->contact_private = intval($this->contact_private);
		$this->contact_owner = intval($this->contact_owner);
		return null; // object is ok
	}

	function canDelete(&$msg, $oid = null, $joins = null) {
		global $AppUI;
		if ($oid) {
			// Check to see if there is a user
			$q = new DBQuery;
			$q->addTable('users');
			$q->addQuery('count(user_id) as user_count');
			$q->addWhere('user_contact = ' . (int)$oid);
			$user_count = $q->loadResult();
			if ($user_count > 0) {
				$msg = $AppUI->_('contactsDeleteUserError');
				return false;
			}
		}
		return parent::canDelete($msg, $oid, $joins);
	}

	function isUser($oid = null) {
		global $AppUI;

		if (!$oid) {
			$oid = $this->contact_id;
		}

		if ($oid) {
			// Check to see if there is a user
			$q = new DBQuery;
			$q->addTable('users');
			$q->addQuery('count(user_id) as user_count');
			$q->addWhere('user_contact = ' . (int)$oid);
			$user_count = $q->loadResult();
			if ($user_count > 0) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	function is_alpha($val) {
		// If the field consists solely of numerics, then we return it as an integer
		// otherwise we return it as an alpha

		$numval = strtr($val, '012345678', '999999999');
		if (count_chars($numval, 3) == '9') {
			return false;
		}
		return true;
	}

	function getCompanyID() {
		$q = new DBQuery;
		$q->addTable('companies');
		$q->addQuery('company_id');
		$q->addWhere('company_name = ' . (int)$this->contact_company);

		return $q->loadResult();
	}

	function getCompanyName() {
		$q = new DBQuery;
		$q->addTable('companies');
		$q->addQuery('company_name');
		$q->addWhere('company_id = ' . (int)$this->contact_company);

		return $q->loadResult();
	}

	function getCompanyDetails() {
		$result = array('company_id' => 0, 'company_name' => '');
		if (!$this->contact_company) {
			return $result;
		}

		$q = new DBQuery;
		$q->addTable('companies');
		$q->addQuery('company_id, company_name');
		if ($this->is_alpha($this->contact_company)) {
			$q->addWhere('company_name = ' . $q->quote($this->contact_company));
		} else {
			$q->addWhere('company_id = ' . (int)$this->contact_company);
		}

		return $q->loadHash();
	}

	function getDepartmentDetails() {
		$result = array('dept_id' => 0, 'dept_name' => '');
		if (!$this->contact_department) {
			return $result;
		}
		$q = new DBQuery;
		$q->addTable('departments');
		$q->addQuery('dept_id, dept_name');
		if ($this->is_alpha($this->contact_department)) {
			$q->addWhere('dept_name = ' . $q->quote($this->contact_department));
		} else {
			$q->addWhere('dept_id = ' . (int)$this->contact_department);
		}

		return $q->loadHash();
	}

	public function getUpdateKey() {
		$q = new DBQuery;
		$q->addTable('contacts');
		$q->addQuery('contact_updatekey');
		$q->addWhere('contact_id = ' . (int)$this->contact_id);

		return $q->loadResult();
	}
	public function clearUpdateKey() {
		$rnow = new CDate();
		$this->contact_updatekey = '';
		$this->contact_lastupdate = $rnow->format(FMT_DATETIME_MYSQL);
		$this->store();
	}

	function updateNotify() {
		global $AppUI, $w2Pconfig, $locale_char_set;
		$df = $AppUI->getPref('SHDATEFORMAT');
		$df .= ' ' . $AppUI->getPref('TIMEFORMAT');

		$mail = new Mail;

		$mail->Subject('Hello', $locale_char_set);

		if ($this->contact_email) {
			$q = new DBQuery;
			$q->addTable('companies');
			$q->addQuery('company_id, company_name');
			$q->addWhere('company_id = ' . (int)$this->contact_company);
			$contact_company = $q->loadHashList();
			$q->clear();

			$body = "Dear: $this->contact_title $this->contact_first_name $this->contact_last_name,";
			$body .= "\n\nIt was very nice to visit you and " . $contact_company[$this->contact_company] . ". Thank you for all the time that you spent with me.";
			$body .= "\n\nI have entered the data from your business card into my contact data base so that we may keep in touch.";
			$body .= "\n\nWe have implemented a system which allows you to view the information that I've recorded and give you the opportunity to correct it or add information as you see fit. Please click on this link to view what I've recorded...";
			$body .= "\n\n" . $AppUI->_('URL') . ":     " . W2P_BASE_URL . "/updatecontact.php?updatekey=$this->contact_updatekey";
			$body .= "\n\nI assure you that the information will be held in strict confidence and will not be available to anyone other than me. I realize that you may not feel comfortable filling out the entire form so please supply only what you're comfortable with.";
			$body .= "\n\nThank you. I look forward to seeing you again, soon.";
			$body .= "\n\nBest Regards,";
			$body .= "\n\n$AppUI->user_first_name $AppUI->user_last_name";
			$mail->Body($body, isset($GLOBALS['locale_char_set']) ? $GLOBALS['locale_char_set'] : '');
		}

		if ($mail->ValidEmail($this->contact_email)) {
			$mail->To($this->contact_email, true);
			$mail->Send();
		}
		return '';
	}

	/**
	 **	Overload of the w2PObject::getAllowedRecords 
	 **	to ensure that the allowed projects are owned by allowed companies.
	 **
	 **	@author	handco <handco@sourceforge.net>
	 **	@see	w2PObject::getAllowedRecords
	 **/

	function getAllowedRecords($uid, $fields = '*', $orderby = '', $index = null, $extra = null) {
		global $AppUI;
		require_once ($AppUI->getModuleClass('companies'));
		require_once ($AppUI->getModuleClass('departments'));
		$oCpy = new CCompany();

		$aCpies = $oCpy->getAllowedRecords($uid, 'company_id, company_name');
		if (count($aCpies)) {
			$buffer = '(contact_company IN (' . implode(',', array_keys($aCpies)) . ') OR contact_company IS NULL OR contact_company = \'\' OR contact_company = 0)';

			//Department permissions
			$oDpt = new CDepartment();
			$aDpts = $oDpt->getAllowedRecords($uid, 'dept_id, dept_name');
			if (count($aDpts)) {
				$dpt_buffer = '(contact_department IN (' . implode(',', array_keys($aDpts)) . ') OR contact_department = 0)';
			} else {
				// There are no allowed departments, so allow projects with no department.
				$dpt_buffer = '(contact_department = 0)';
			}

			if ($extra['where'] != '') {
				$extra['where'] = $extra['where'] . ' AND ' . $buffer . ' AND ' . $dpt_buffer;
			} else {
				$extra['where'] = $buffer . ' AND ' . $dpt_buffer;
			}
		} else {
			// There are no allowed companies, so don't allow projects.
			if ($extra['where'] != '') {
				$extra['where'] = $extra['where'] . ' AND (contact_company IS NULL OR contact_company = \'\' OR contact_company = 0) ';
			} else {
				$extra['where'] = 'contact_company IS NULL OR contact_company = \'\' OR contact_company = 0';
			}
		}
		return parent::getAllowedRecords($uid, $fields, $orderby, $index, $extra);
	}

	public static function searchContacts($AppUI, $where = '', $searchString = '') {
		$showfields = array('contact_address1' => 'contact_address1', 'contact_address2' => 'contact_address2', 'contact_city' => 'contact_city', 'contact_state' => 'contact_state', 'contact_zip' => 'contact_zip', 'contact_country' => 'contact_country', 'contact_company' => 'contact_company', 'company_name' => 'company_name', 'dept_name' => 'dept_name', 'contact_phone' => 'contact_phone', 'contact_phone2' => 'contact_phone2', 'contact_mobile' => 'contact_mobile', 'contact_fax' => 'contact_fax', 'contact_email' => 'contact_email');

		if ($searchString != '') {
			$additional_filter = "OR contact_first_name like '%$searchString%' OR contact_last_name  like '%$searchString%'
			                      OR CONCAT(contact_first_name, ' ', contact_last_name)  like '%$searchString%'
								  					OR company_name like '%$searchString%' OR contact_notes like '%$searchString%'
								  					OR contact_email like '%$searchString%'";
		}
		// assemble the sql statement
		$q = new DBQuery;
		$q->addQuery('contact_id, contact_order_by');
		$q->addQuery($showfields);
		$q->addQuery('contact_first_name, contact_last_name, contact_phone, contact_title');
		$q->addQuery('contact_updatekey, contact_updateasked, contact_lastupdate');
		$q->addQuery('user_id');
		$q->addTable('contacts', 'a');
		$q->leftJoin('companies', 'b', 'a.contact_company = b.company_id');
		$q->leftJoin('departments', '', 'contact_department = dept_id');
		$q->leftJoin('users', '', 'contact_id = user_contact');
		$q->addWhere("(contact_first_name LIKE '$where%' OR contact_last_name LIKE '$where%' " . $additional_filter . ")");
		$q->addWhere('
			(contact_private=0
				OR (contact_private=1 AND contact_owner=' . $AppUI->user_id . ')
				OR contact_owner IS NULL OR contact_owner = 0
			)');
		$company = new CCompany;
		$company->setAllowedSQL($AppUI->user_id, $q);

		$department = new CDepartment;
		$department->setAllowedSQL($AppUI->user_id, $q);

		$q->addOrder('contact_first_name');
		$q->addOrder('contact_last_name');

		return $q->loadList();
	}
	public static function getFirstLetters($userId, $onlyUsers = false) {
		$letters = '';

		$search_map = array('contact_first_name', 'contact_last_name');
		foreach ($search_map as $search_name) {
			$q = new DBQuery;
			$q->addTable('contacts');
			$q->addQuery('DISTINCT SUBSTRING(' . $search_name . ', 1, 1) as L');
			if ($onlyUsers) {
				//$q->addTable('users', 'u');
				$q->addJoin('users', 'u', 'user_contact = contact_id', 'inner');
			}
			$q->addWhere('contact_private=0 OR (contact_private=1 AND contact_owner=' .(int) $userId. ') OR contact_owner IS NULL OR contact_owner = 0');
			$arr = $q->loadList();

			foreach ($arr as $L) {
				$letters .= $L['L'];
			}
		}
		return strtoupper($letters);
	}

	public static function getContactByUsername($username) {
		$q = new DBQuery;
		$q->addTable('users');
		$q->addQuery('contact_first_name, contact_last_name');
		$q->addJoin('contacts', 'con', 'contact_id = user_contact', 'inner');
		$q->addWhere("user_username like '%$username%'");
		$q->setLimit(1);
		$r = $q->loadResult();
		$result = (is_array($r)) ? $r[0]['contact_first_name'] . ' ' . $r[0]['contact_last_name'] : 'User Not Found';

		return $result;
	}
	public static function getContactByUserid($userId) {
		$q = new DBQuery;
		$q->addTable('users');
		$q->addQuery('contact_first_name, contact_last_name');
		$q->addJoin('contacts', 'con', 'contact_id = user_contact', 'inner');
		$q->addWhere('user_id = ' . (int) $userId);
		$q->setLimit(1);
		$r = $q->loadList();
		$result = (is_array($r)) ? $r[0]['contact_first_name'] . ' ' . $r[0]['contact_last_name'] : 'User Not Found';

		return $result;
	}
	public static function getContactByEmail($email) {
		$q = new DBQuery;
		$q->addTable('users');
		$q->addQuery('contact_first_name, contact_last_name');
		$q->addJoin('contacts', 'con', 'contact_id = user_contact', 'inner');
		$q->addWhere("contact_email = '$email'");
		$q->setLimit(1);
		$r = $q->loadResult();
		$result = (is_array($r)) ? $r[0]['contact_first_name'] . ' ' . $r[0]['contact_last_name'] : 'User Not Found';

		return $result;
	}
	public static function getContactByUpdatekey($updateKey) {
		$q = new DBQuery;
		$q->addTable('contacts');
		$q->addQuery('contact_id');
		$q->addWhere("contact_updatekey= '$updateKey'");
		
		return $q->loadResult();
	}
	public static function getProjects($contactId) {
		$q = new DBQuery;
		$q->addQuery('p.project_id, p.project_name');
		$q->addTable('project_contacts', 'pc');
		$q->addJoin('projects', 'p', 'p.project_id = pc.project_id', 'inner');
		$q->addWhere("contact_id =  $contactId");
									
		return $q->loadList();
	}

	public function clearOldUpdatekeys($days_for_update) {
		$q = new DBQuery;
		$q->addTable('contacts');
		$q->addUpdate('contact_updatekey', '');
		$q->addWhere("(TO_DAYS(NOW()) - TO_DAYS(contact_updateasked) >= $days_for_update)");
		$q->exec();
	}
	public function hook_cron() {
		$q = new DBQuery;
		$q->addTable('contacts');
		$q->addQuery('contact_id');
		$q->addWhere('contact_first_name IS NULL');
		$contactIdList = $q->loadList();

		foreach($contactIdList as $contactId) {
			$myContact = new CContact();
			$myContact = $myContact->load($contactId['contact_id']);
			$myContact->store();
		}

		//To Bruce: Clean updatekeys based on datediff to warn about long waiting.
		//TODO: This should be converted to a system configuration value
		$days_for_update = 5;
		$this->clearOldUpdatekeys($days_for_update);
	}
}
?>