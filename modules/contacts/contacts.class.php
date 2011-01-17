<?php /* $Id$ $URL$ */

/**
 *	@package web2Project
 *	@subpackage modules
 *	@version $Revision$
 */

/**
 * Contacts class
 */
class CContact extends w2p_Core_BaseObject {
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
    public $contact_phone = null;
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

        $q = new w2p_Database_Query;
        $q->addTable('contacts');
        $q->addJoin('companies', 'cp', 'cp.company_id = contact_company');
        $q->addWhere('contact_id = ' . (int) $contactId);
        $q->loadObject($this, true, false);
	}

	public function store(CAppUI $AppUI = null) {
        global $AppUI;
        $perms = $AppUI->acl();
        
        $this->contact_company = (int) $this->contact_company;
        $this->contact_department = (int) $this->contact_department;

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

        $errorMsgArray = $this->check();
        if (count($errorMsgArray) > 0) {
            return $errorMsgArray;
        }

        $q = new w2p_Database_Query;
        $this->contact_lastupdate = $q->dbfnNowWithTZ();
        /*
         * TODO: I don't like the duplication on each of these two branches, but I
         *   don't have a good idea on how to fix it at the moment...
         */
        if ($this->contact_id) {// && $perms->checkModuleItem('contacts', 'edit', $this->contact_id)) {
            if (($msg = parent::store())) {
                return $msg;
            }
            $stored = true;
        }
        if (0 == $this->contact_id) {// && $perms->checkModuleItem('contacts', 'add')) {
            if (($msg = parent::store())) {
                return $msg;
            }
            $stored = true;
        }
        if ($stored) {
            $custom_fields = new w2p_Core_CustomFields('contacts', 'addedit', $this->contact_id, 'edit');
            $custom_fields->bind($_POST);
            $sql = $custom_fields->store($this->contact_id); // Store Custom Fields
        }

        /*
         *  TODO: I don't like using the $_POST in here..
         */
        if ($stored) {
            $methods = array();
            if (!empty($_POST['contact_methods'])) {
                foreach ($_POST['contact_methods']['field'] as $key => $field) {
                    $methods[$field] = $_POST['contact_methods']['value'][$key];
                }
            }
            $this->setContactMethods($methods);
        }
        return $stored;
	}

	public function setContactMethods(array $methods) {
		$q = new w2p_Database_Query;
		$q->setDelete('contacts_methods');
		$q->addWhere('contact_id=' . (int)$this->contact_id);
		$q->exec();
		$q->clear();

		if (!empty($methods)) {
			$q = new w2p_Database_Query;
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

	public function getContactMethods($methodsArray = null) {
		$results = array();

        $q = new w2p_Database_Query;
		$q->addTable('contacts_methods');
		$q->addQuery('method_name, method_value');
		$q->addWhere('contact_id = ' . (int)$this->contact_id);
        if (is_array($methodsArray)) {
            $q->addWhere("method_name IN ('".implode("','", $methodsArray)."')");
        }
		$q->addOrder('method_name');
		$contacts = $q->loadList();

        foreach($contacts as $row => $data) {
            $results[$data['method_name']] = $data['method_value'];
        }

		return $results;
	}

	public function delete(CAppUI $AppUI = null) {
        global $AppUI;

        if ($msg = parent::delete()) {
            return $msg;
        }
        return true;
	}

	public function check() {
        $errorArray = array();
        $baseErrorMsg = get_class($this) . '::store-check failed - ';

	    return $errorArray;
	}

	public function canDelete(&$msg, $oid = null, $joins = null) {
		global $AppUI;
		if ($oid) {
			// Check to see if there is a user
			$q = new w2p_Database_Query;
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
			$q = new w2p_Database_Query;
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
		$q = new w2p_Database_Query;
		$q->addTable('companies');
		$q->addQuery('company_id');
		$q->addWhere('company_name = ' . (int)$this->contact_company);

		return $q->loadResult();
	}

	public function getCompanyName() {
		$q = new w2p_Database_Query;
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

		$q = new w2p_Database_Query;
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
		$q = new w2p_Database_Query;
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
		$q = new w2p_Database_Query;
		$q->addTable('contacts');
		$q->addQuery('contact_updatekey');
		$q->addWhere('contact_id = ' . (int)$this->contact_id);

		return $q->loadResult();
	}
	
	public function clearUpdateKey() {
		global $AppUI;

        $q = new w2p_Database_Query();
		$this->contact_updatekey = '';
		$this->contact_lastupdate = $q->dbfnNowWithTZ();
		$this->store($AppUI);
	}

	public function updateNotify() {
		global $AppUI, $w2Pconfig, $locale_char_set;
		$df = $AppUI->getPref('SHDATEFORMAT');
		$df .= ' ' . $AppUI->getPref('TIMEFORMAT');

		$mail = new Mail;
		$mail->Subject('Hello', $locale_char_set);

		if ($this->contact_email) {
            $emailManager = new w2p_Output_EmailManager();
            $body = $emailManager->getContactUpdateNotify($AppUI, $this);

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
		$q = new w2p_Database_Query;
		$q->addQuery('contact_id, contact_order_by');
		$q->addQuery($showfields);
		$q->addQuery('contact_first_name, contact_last_name, contact_title');
		$q->addQuery('contact_updatekey, contact_updateasked, contact_lastupdate');
        $q->addQuery('contact_email, contact_phone');
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
			$q = new w2p_Database_Query;
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
		$q = new w2p_Database_Query;
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
		$q = new w2p_Database_Query;
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
		$q = new w2p_Database_Query;
		$q->addTable('users');
		$q->addQuery('contact_first_name, contact_last_name');
		$q->addJoin('contacts', 'con', 'contact_id = user_contact', 'inner');

        $q->leftJoin('contacts_methods', 'cm', 'cm.contact_id = user_contact');
        $q->addWhere("cm.method_value = '$email'");
//TODO: add primary email
		$q->setLimit(1);
		$r = $q->loadResult();
		$result = (is_array($r)) ? $r[0]['contact_first_name'] . ' ' . $r[0]['contact_last_name'] : 'User Not Found';

		return $result;
	}
	
	public static function getContactByUpdatekey($updateKey) {
		$q = new w2p_Database_Query;
		$q->addTable('contacts');
		$q->addQuery('contact_id');
		$q->addWhere("contact_updatekey= '$updateKey'");

		return $q->loadResult();
	}
	
	public static function getProjects($contactId) {
		$q = new w2p_Database_Query;
		$q->addQuery('p.project_id, p.project_name');
		$q->addTable('project_contacts', 'pc');
		$q->addJoin('projects', 'p', 'p.project_id = pc.project_id', 'inner');
		$q->addWhere("contact_id =  $contactId");

		return $q->loadList();
	}

	public function clearOldUpdatekeys($days_for_update) {
		$q = new w2p_Database_Query;
		$q->addTable('contacts');
		$q->addUpdate('contact_updatekey', '');
		$q->addWhere("(TO_DAYS(NOW()) - TO_DAYS(contact_updateasked) >= $days_for_update)");
		$q->exec();
	}
	
	public function hook_cron() {
		global $AppUI;

        $q = new w2p_Database_Query;
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
        $search['table_key'] = 'c.contact_id';
        $search['table_link'] = 'index.php?m=contacts&a=view&contact_id='; // first part of link
        $search['table_title'] = 'Contacts';
        $search['table_orderby'] = 'contact_last_name,contact_first_name';
        $search['table_groupby'] = 'c.contact_id';
        $search['search_fields'] = array('contact_first_name', 'contact_last_name', 'contact_title', 'contact_company', 'contact_type', 'contact_address1', 'contact_address2', 'contact_city', 'contact_state', 'contact_zip', 'contact_country', 'contact_notes', 'cm.method_value');
        $search['display_fields'] = array('contact_first_name', 'contact_last_name', 'contact_title', 'contact_company', 'contact_type', 'contact_address1', 'contact_address2', 'contact_city', 'contact_state', 'contact_zip', 'contact_country', 'contact_notes', 'cm.method_value');
        $search['table_joins'] = array(array('table' => 'contacts_methods', 'alias' => 'cm', 'join' => 'c.contact_id = cm.contact_id'));
//TODO: add primary email
        return $search;
    }

    public function hook_calendar($userId) {
//    return $this->getUpcomingBirthdays($userId);
        return null;
    }
}