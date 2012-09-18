<?php /* $Id$ $URL$ */

/**
 * @package web2project
 * @subpackage authenticators
 */

class w2p_Authenticators_LDAP extends w2p_Authenticators_Base {
	public $ldap_host;
	public $ldap_port;
	public $ldap_version;
	public $base_dn;
	public $ldap_search_user;
	public $ldap_search_pass;
	public $filter;

	public $user_id;
	public $username;

	public function __construct() {
		global $w2Pconfig;

		$this->fallback = isset($w2Pconfig['ldap_allow_login']) ? $w2Pconfig['ldap_allow_login'] : false;

		$this->ldap_host = $w2Pconfig['ldap_host'];
		$this->ldap_port = $w2Pconfig['ldap_port'];
		$this->ldap_version = $w2Pconfig['ldap_version'];
		$this->base_dn = $w2Pconfig['ldap_base_dn'];
		$this->ldap_search_user = $w2Pconfig['ldap_search_user'];
		$this->ldap_search_pass = $w2Pconfig['ldap_search_pass'];
		$this->filter = $w2Pconfig['ldap_user_filter'];

        $this->ldap_complete_string = $w2Pconfig['ldap_complete_string'];
	}

    public function authenticate($username, $password) {
		global $w2Pconfig;
		$this->username = $username;

		if (strlen($password) == 0) {
			return false; // LDAP will succeed binding with no password on AD (defaults to anon bind)
		}

		if ($rs = ldap_connect($this->ldap_host, $this->ldap_port)) {
		    ldap_set_option($rs, LDAP_OPT_PROTOCOL_VERSION, $this->ldap_version);
    		ldap_set_option($rs, LDAP_OPT_REFERRALS, 0);

            if ('' == $this->ldap_complete_string) {
                /*
                 * This should be compliant with the old/previous LDAP settings
                 *   that we've used all along.
                 */
                if (strpos($this->ldap_search_user, 'CN=') === false) {
                    $ldap_bind_dn = 'CN='.$this->ldap_search_user.',OU=Users,'.$this->base_dn;
                } else {
                    $ldap_bind_dn = $this->ldap_search_user.','.$this->base_dn;
                }
            } else {
                /*
                 * In case the LDAP configuration is different than expected,
                 *   we can configure a completely custom one.
                 */
                $ldap_bind_dn = $this->ldap_complete_string;
            }
	    	$ldap_bind_pw = empty($this->ldap_search_pass) ? null : $this->ldap_search_pass;

		    if ($bindok = ldap_bind($rs, $ldap_bind_dn, $ldap_bind_pw)) {
			    $filter_r = html_entity_decode(str_replace('%USERNAME%', $username, $this->filter), ENT_COMPAT, 'UTF-8');
			    $result = ldap_search($rs, $this->base_dn, $filter_r);

				if ($result) {
					$result_user = ldap_get_entries($rs, $result);

					if ($result_user['count'] != 0) {
						$first_user = $result_user[0];
						$ldap_user_dn = $first_user['dn'];

						// Bind with the dn of the user that matched our filter (only one user should match sAMAccountName or uid etc..)
						if ($bind_user = ldap_bind($rs, $ldap_user_dn, $password)) {
							if ($this->userExists($username)) {
								// Update password if different
								$tmpUser = new CUser();
								$tmpUser->load($this->userId($username));
								$hash_pass = $this->hashPassword($password);
								if($hash_pass != $tmpUser->user_password) {
									$tmpUser->user_password = $hash_pass;
									$tmpUser->store();
								}
								return true;
							} else {
								$this->createsqluser($username, $password, $first_user);
							}
							return true;
						}
					}
				}
		    }
		}

	    if ($this->fallback == true) {
		    $sqlAuth = new w2p_Authenticators_SQL();
            return $sqlAuth->authenticate($username, $password);
	    }

		return false;
	}

	public function userExists($username) {
		global $db;
		$q = new w2p_Database_Query;
		$result = false;
		$q->addTable('users');
		$q->addWhere('user_username = \'' . $username . '\'');
		$rs = $q->exec();
		if ($rs->RecordCount() > 0) {
			$result = true;
		}

		return $result;
	}

	public function userId($username) {
		global $db;
		$q = new w2p_Database_Query;
		$q->addTable('users');
		$q->addWhere('user_username = \'' . $username . '\'');
		$rs = $q->exec();
		$row = $rs->FetchRow();
		$q->clear();
		return $row['user_id'];
	}

	public function createsqluser($username, $password, $ldap_attribs = array()) {
		global $AppUI;
		$hash_pass = $this->hashPassword($password);

		if (!count($ldap_attribs) == 0) {
			// Contact information based on the inetOrgPerson class schema
			$c = new CContact();
			$c->contact_first_name = $ldap_attribs['givenname'][0];
			$c->contact_last_name = $ldap_attribs['sn'][0];
			$c->contact_city = $ldap_attribs['l'][0];
			$c->contact_country = $ldap_attribs['country'][0];
			$c->contact_state = $ldap_attribs['st'][0];
			$c->contact_zip = $ldap_attribs['postalcode'][0];
			$c->contact_job = $ldap_attribs['title'][0];
            $c->contact_email = $ldap_attribs['mail'][0];
            $c->contact_phone = $ldap_attribs['telephonenumber'][0];
            $c->contact_owner = $AppUI->user_id;
            $c->store();
            $contactArray = array('phone_mobile' => $ldap_attribs['mobile'][0]);
            $c->setContactMethods($contactArray);
		}
		$contact_id = ($c->contact_id == null) ? 'NULL' : $c->contact_id;

        $u = new CUser();
        $u->user_username = $username;
        $u->user_password = $hash_pass;
        $u->user_type = 0;              // Changed from 1 (administrator) to 0 (Default user)
        $u->user_contact = (int) $contact_id;
        $u->store();
        $user_id = $u->user_id;
		$this->user_id = $user_id;

		$acl = &$AppUI->acl();
		$acl->insertUserRole($acl->get_group_id('anon'), $this->user_id);
	}
}