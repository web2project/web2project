<?php /* $Id$ $URL$ */

/**
 *	@package web2Project
 *	@subpackage modules
 *	@version $Revision$
 */

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
	public $contact_address1 = null;
	public $contact_address2 = null;
	public $contact_city = null;
	public $contact_state = null;
	public $contact_zip = null;
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

	public function __construct() {
        parent::__construct('contacts', 'contact_id');
	}

	public function loadFull(CAppUI $AppUI = null, $contactId) {
		global $AppUI;

        $q = new DBQuery;
        $q->addTable('contacts');
        $q->addJoin('companies', 'cp', 'cp.company_id = contact_company');
        $q->addWhere('contact_id = ' . (int) $contactId);
        $q->loadObject($this, true, false);
	}

	public function store(CAppUI $AppUI = null) {
        global $AppUI;
        $errorMsgArray = $this->check();
        $this->contact_company = (int) $this->contact_company;
        $this->contact_department = (int) $this->contact_department;

        if (count($errorMsgArray) > 0) {
            return $errorMsgArray;
        }
        /*
        *  This  validates that any Contact saved will have a Display Name as
        * required by various dropdowns, etc throughout the system.  This is
        * mostly required when Contacts are generated via programatic methods and
        * not through the add/edit UI.
        */
        if(mb_strlen($this->contact_order_by) <= 1 || $this->contact_order_by == null) {
            //TODO: this should use the USERFORMAT to determine how display names are generated
            if ($this->contact_first_name == null && $this->contact_last_name == null) {
               $this->contact_order_by = $this->contact_company;
            } else {
                $this->contact_order_by = mb_trim($this->contact_first_name.' '.$this->contact_last_name);
            }
        }
        if($this->contact_first_name == null) {
            $this->contact_first_name = '';
        }
        if($this->contact_last_name == null) {
            $this->contact_last_name = '';
        }
        if($this->contact_birthday == '') {
            $this->contact_birthday = null;
        }
        $q = new DBQuery;
        $this->contact_lastupdate = $q->dbfnNow();
        addHistory('contacts', $this->contact_id, 'store', $this->contact_first_name.' '.$this->contact_last_name, $this->contact_id);

        parent::store();
	}

	public function setContactMethods(array $methods) {
		$q = new DBQuery;
		$q->setDelete('contacts_methods');
		$q->addWhere('contact_id=' . (int)$this->contact_id);
		$q->exec();
		$q->clear();

		if (!empty($methods)) {
			$q = new DBQuery;
			$q->addTable('contacts_methods');
			$q->addInsert('contact_id', (int)$this->contact_id);
			foreach ($methods as $name => $value) {
				if (!empty($value)) {
					$q->addInsert('method_name', $name);
					$q->addInsert('method_value', $value);
					$q->exec();
				}
			}
			$q->clear();
		}
	}

	public function getContactMethods() {
		$q = new DBQuery;
		$q->addTable('contacts_methods');
		$q->addQuery('method_name, method_value');
		$q->addWhere('contact_id = ' . (int)$this->contact_id);
		$q->addOrder('method_name');
		$result = $q->loadList();
		return $result ? $result : array();
	}

	public function delete(CAppUI $AppUI = null) {
        global $AppUI;

        if ($msg = parent::delete()) {
            return $msg;
        }
        addHistory('contacts', 0, 'delete', 'Deleted', 0);
        return true;
	}

	public function check() {
        $errorArray = array();
        $baseErrorMsg = get_class($this) . '::store-check failed - ';

        if ('' != $this->contact_url && !w2p_check_url($this->contact_url)) {
            $errorArray['contact_url'] = $baseErrorMsg . 'contact url is not formatted properly';
        }
        if ('' != $this->contact_email && !w2p_check_email($this->contact_email)) {
            $errorArray['contact_email'] = $baseErrorMsg . 'contact email is not formatted properly';
        }
        if ('' != $this->contact_email2 && !w2p_check_email($this->contact_email2)) {
            $errorArray['contact_email2'] = $baseErrorMsg . 'contact email2 is not formatted properly';
        }

	    return $errorArray;
	}

	public function canDelete(&$msg, $oid = null, $joins = null) {
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

	public function isUser($oid = null) {
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

	public function is_alpha($val) {
		// If the field consists solely of numerics, then we return it as an integer
		// otherwise we return it as an alpha

		$numval = strtr($val, '012345678', '999999999');
		if (count_chars($numval, 3) == '9') {
			return false;
		}
		return true;
	}

	public function getCompanyID() {
		$q = new DBQuery;
		$q->addTable('companies');
		$q->addQuery('company_id');
		$q->addWhere('company_name = ' . (int)$this->contact_company);

		return $q->loadResult();
	}

	public function getCompanyName() {
		$q = new DBQuery;
		$q->addTable('companies');
		$q->addQuery('company_name');
		$q->addWhere('company_id = ' . (int)$this->contact_company);

		return $q->loadResult();
	}

	public function getCompanyDetails() {
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

	public function getDepartmentDetails() {
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
		global $AppUI;

    $rnow = new CDate();
		$this->contact_updatekey = '';
		$this->contact_lastupdate = $rnow->format(FMT_DATETIME_MYSQL);
		$this->store($AppUI);
	}

	public function updateNotify() {
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

	public function getAllowedRecords($uid, $fields = '*', $orderby = '', $index = null, $extra = null) {
		global $AppUI;
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

	public static function searchContacts(CAppUI $AppUI = null, $where = '', $searchString = '') {
		global $AppUI;

        $showfields = array('contact_address1' => 'contact_address1',
			'contact_address2' => 'contact_address2', 'contact_city' => 'contact_city',
			'contact_state' => 'contact_state', 'contact_zip' => 'contact_zip',
			'contact_country' => 'contact_country', 'contact_company' => 'contact_company',
			'company_name' => 'company_name', 'dept_name' => 'dept_name');
		$additional_filter = '';

		if ($searchString != '') {
			$additional_filter = "OR contact_first_name like '%$searchString%'
                                  OR contact_last_name  like '%$searchString%'
			                      OR CONCAT(contact_first_name, ' ', contact_last_name)  like '%$searchString%'
                                  OR company_name like '%$searchString%'
                                  OR contact_notes like '%$searchString%'";
		}
		// assemble the sql statement
		$q = new DBQuery;
		$q->addQuery('contact_id, contact_order_by');
		$q->addQuery($showfields);
		$q->addQuery('contact_first_name, contact_last_name, contact_title');
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
		global $AppUI;

    $q = new DBQuery;
    $q->addTable('contacts');
		$q->addQuery('contact_id');
		$q->addWhere('contact_first_name IS NULL');
		$contactIdList = $q->loadList();

		foreach($contactIdList as $contactId) {
			$myContact = new CContact();
			$myContact = $myContact->load($contactId['contact_id']);
			$myContact->store($AppUI);
		}

		//To Bruce: Clean updatekeys based on datediff to warn about long waiting.
		//TODO: This should be converted to a system configuration value
		$days_for_update = 5;
		$this->clearOldUpdatekeys($days_for_update);
	}

  public function hook_search() {
    $search['table'] = 'contacts';
    $search['table_alias'] = 'c';
    $search['table_module'] = 'contacts';
    $search['table_key'] = 'c.contact_id'; // primary key in searched table
    $search['table_link'] = 'index.php?m=contacts&a=view&contact_id='; // first part of link
    $search['table_title'] = 'Contacts';
    $search['table_orderby'] = 'contact_last_name,contact_first_name';
    $search['search_fields'] = array('contact_first_name', 'contact_last_name', 'contact_title', 'contact_company', 'contact_type', 'contact_email', 'contact_email2', 'contact_address1', 'contact_address2', 'contact_city', 'contact_state', 'contact_zip', 'contact_country', 'contact_notes');
    $search['display_fields'] = array('contact_first_name', 'contact_last_name', 'contact_title', 'contact_company', 'contact_type', 'contact_email', 'contact_email2', 'contact_address1', 'contact_address2', 'contact_city', 'contact_state', 'contact_zip', 'contact_country', 'contact_notes');

    return $search;
  }

  public function hook_calendar($userId) {
//    return $this->getUpcomingBirthdays($userId);
	return null;
  }
}
