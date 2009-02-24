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
require_once ($AppUI->getSystemClass('libmail'));

/**
 * Contacts class
 */
class CContact extends CW2pObject {
	/**
 	@var int */
	var $contact_id = null;
	/**
 	@var string */
	var $contact_first_name = '';
	/**
 	@var string */
	var $contact_last_name = '';
	var $contact_order_by = '';
	var $contact_title = null;
	var $contact_job = null;
	var $contact_birthday = null;
	var $contact_company = null;
	var $contact_department = null;
	var $contact_type = null;
	var $contact_email = null;
	var $contact_email2 = null;
	var $contact_phone = null;
	var $contact_phone2 = null;
	var $contact_fax = null;
	var $contact_mobile = null;
	var $contact_address1 = null;
	var $contact_address2 = null;
	var $contact_city = null;
	var $contact_state = null;
	var $contact_zip = null;
	var $contact_url = null;
	var $contact_icq = null;
	var $contact_aol = null;
	var $contact_yahoo = null;
	var $contact_msn = null;
	var $contact_jabber = null;
	var $contact_skype = null;
	var $contact_google = null;
	var $contact_notes = null;
	var $contact_project = null;
	var $contact_country = null;
	var $contact_icon = null;
	var $contact_owner = null;
	var $contact_private = null;
	var $contact_updatekey = null;
	var $contact_lastupdate = null;
	var $contact_updateasked = null;

	function CContact() {
		$this->CW2pObject('contacts', 'contact_id');
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
		$company_id = $q->loadResult();
		$q->clear();
		return $company_id;
	}

	function getCompanyName() {
		$q = new DBQuery;
		$q->addTable('companies');
		$q->addQuery('company_name');
		$q->addWhere('company_id = ' . (int)$this->contact_company);
		$company_name = $q->loadResult();
		$q->clear();
		return $company_name;
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
		$result = $q->loadHash();
		$q->clear();
		return $result;
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
		$result = $q->loadHash();
		$q->clear();
		return $result;
	}

	function getUpdateKey() {
		$q = new DBQuery;
		$q->addTable('contacts');
		$q->addQuery('contact_updatekey');
		$q->addWhere('contact_id = ' . (int)$this->contact_id);
		$updatekey = $q->loadResult();
		$q->clear();
		return $updatekey;
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
	public static function searchContacts($AppUI, $where, $searchString = '') {
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
	/*
	 *  I  don't like this function, it seems to be used for either the username
	 * or the userId, I'd rather it be one or the other...
	 */
	public static function getContactByUsername($username) {
		$q = new DBQuery;
		$q->addTable('users');
		$q->addQuery('contact_first_name, contact_last_name');
		$q->addJoin('contacts', 'con', 'contact_id = user_contact', 'inner');
		$q->addWhere("user_username like '$username'");
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
	public static function getContactByUpdatekey($updateKey) {
		$q = new DBQuery;
		$q->addTable('contacts');
		$q->addQuery('contact_id');
		$q->addWhere("contact_updatekey= '$updateKey'");
		
		return $q->loadResult();
	}

	public function cron_hook() {
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
	}
}
?>