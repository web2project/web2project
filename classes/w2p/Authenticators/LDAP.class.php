<?php
/**
 * In addition to regular LDAP, this handles Active Directory.
 *
 * @package     web2project\authenticators
 */

class w2p_Authenticators_LDAP extends w2p_Authenticators_Base
{
    public $ldap_host;
    public $ldap_port;
    public $ldap_version;
    public $base_dn;
    public $ldap_search_user;
    public $ldap_search_pass;
    public $filter;

    public $user_id;
    public $username;

    public function __construct()
    {
        parent::__construct();

        $this->fallback = isset($this->w2Pconfig['ldap_allow_login']) ?
            $this->w2Pconfig['ldap_allow_login'] : false;

        $this->ldap_host = $this->w2Pconfig['ldap_host'];
        $this->ldap_port = $this->w2Pconfig['ldap_port'];
        $this->ldap_version = $this->w2Pconfig['ldap_version'];
        $this->base_dn = $this->w2Pconfig['ldap_base_dn'];
        $this->ldap_search_user = $this->w2Pconfig['ldap_search_user'];
        $this->ldap_search_pass = $this->w2Pconfig['ldap_search_pass'];
        $this->filter = $this->w2Pconfig['ldap_user_filter'];

        $this->ldap_complete_string = $this->w2Pconfig['ldap_complete_string'];
    }

    public function authenticate($username, $password)
    {
        $this->username = $username;

        if (strlen($password) == 0) {
            // LDAP will succeed binding with no password on AD
            // (defaults to anon bind)
            return false;
        }

        $rs = ldap_connect($this->ldap_host, $this->ldap_port);
        if ($rs) {
            ldap_set_option($rs, LDAP_OPT_PROTOCOL_VERSION, $this->ldap_version);
            ldap_set_option($rs, LDAP_OPT_REFERRALS, 0);

            $ldap_bind_pw = empty($this->ldap_search_pass) ? null :
                $this->ldap_search_pass;
            $ldap_bind_dn = $this->ldap_search_user;

            if (ldap_bind($rs, $ldap_bind_dn, $ldap_bind_pw)) {
                $filter_r = html_entity_decode(str_replace('%USERNAME%', $username,
                                                $this->filter), ENT_COMPAT, 'UTF-8');
                $result = ldap_search($rs, $this->base_dn, $filter_r);

                if ($result) {
                    $result_user = ldap_get_entries($rs, $result);

                    if ($result_user['count'] != 0) {
                        $first_user = $result_user[0];
                        $ldap_user_dn = $first_user['dn'];

                        // Bind with the dn of the user that matched our filter
                        // (only one user should match sAMAccountName or uid etc..)
                        if (ldap_bind($rs, $ldap_user_dn, $password)) {
                            if ($this->userExists($username)) {
                                // Update password if different
                                $tmpUser = new CUser();
                                $tmpUser->load($this->userId($username));
                                $hash_pass = $this->hashPassword($password);
                                if ($hash_pass != $tmpUser->user_password) {
                                    $tmpUser->user_password = $hash_pass;
                                    $tmpUser->store();
                                }
                                return true;
                            } else {
                                $this->createsqluser($username, $password,
                                                     $first_user);
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

    public function userExists($username)
    {
        $user = new CUser();
        return $user->user_exists($username);
    }

    public function userId($username)
    {
        $q = $this->query;
        $q->addTable('users');
        $q->addWhere('user_username = \'' . $username . '\'');
        $rs = $q->exec();
        $row = $rs->FetchRow();

        return $row['user_id'];
    }

    public function contactId($user_id)
    {
        $q = $this->query;
        $q->addTable('contacts');
        $q->addWhere('contact_owner = \'' . $user_id . '\'');
        $rs = $q->exec();
        $row = $rs->FetchRow();

        return $row['contact_id'];
    }

     public function createsqluser($username, $password, $ldap_attribs = array())
    {
        $hash_pass = $this->hashPassword($password);

        $c = new CContact();
            if (count($ldap_attribs)) {
                // Contact information based on the inetOrgPerson class schema
                $c->contact_first_name = $ldap_attribs['givenname'][0];
                $c->contact_last_name = $ldap_attribs['sn'][0];
                $c->contact_city = $ldap_attribs['l'][0];
                $c->contact_country = $ldap_attribs['country'][0];
                $c->contact_state = $ldap_attribs['st'][0];
                $c->contact_zip = $ldap_attribs['postalcode'][0];
                $c->contact_job = $ldap_attribs['title'][0];
                $c->contact_email = $ldap_attribs['mail'][0];
                $c->contact_phone = $ldap_attribs['telephonenumber'][0];
                $result = $c->store();
                $contactArray = array('phone_mobile' => $ldap_attribs['mobile'][0]);
                $c->setContactMethods($contactArray);
            }

        $u = new CUser();
        $u->user_username = $username;
        $u->user_password = $hash_pass;
        $u->user_type = 0;   // Changed from 1 (administrator) to 0 (Default user)
        $u->user_contact = (int) $c->contact_id;
        $result = $u->store(null, true);

        $user_id = $u->user_id;
        $this->user_id = $user_id;

        // No role, so user will be created as 'Inactive'
        //$acl = &$this->AppUI->acl();
        //$acl->insertUserRole($acl->get_group_id('empty'), $this->user_id);
    
    }
}
