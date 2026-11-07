<?php
/**
 * In addition to regular LDAP, this handles Active Directory.
 *
 * @package web2project\authenticators
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
                                $this->updateSqlPassword($username, $password);
                                return true;
                            }

                            // A first-time LDAP user needs a local account;
                            // without one the login can't continue.
                            return $this->createsqluser($username, $password,
                                                        $first_user);
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

    public function userId($username = null)
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

    /**
     * Keeps the local copy of an LDAP user's password current, so the SQL
     * fallback (ldap_allow_login) accepts the same password. Stored hashes are
     * salted, so the password is checked with password_verify() and the row is
     * only written when it no longer matches. The update goes straight to the
     * table because nobody is logged in yet, so CUser::store() would be refused.
     *
     * @param string $username
     * @param string $password
     */
    protected function updateSqlPassword($username, $password)
    {
        $q = new w2p_Database_Query();
        $q->addTable('users');
        $q->addQuery('user_password');
        $q->addWhere('user_username = ' . $q->quote($username));
        $hash = (string) $q->loadResult();

        if (!password_verify($password, $hash)) {
            $q->clear();
            $q->addTable('users');
            $q->addUpdate('user_password', $this->hashPassword($password));
            $q->addWhere('user_username = ' . $q->quote($username));
            $q->exec();
        }
    }

    /**
     * Returns the first value of an LDAP attribute, or '' when the directory
     * does not provide it (not every schema has every inetOrgPerson field).
     *
     * @param array<string, mixed> $ldap_attribs
     * @param string               $name
     *
     * @return string
     */
    protected function ldapAttribute(array $ldap_attribs, $name)
    {
        return isset($ldap_attribs[$name][0]) ? (string) $ldap_attribs[$name][0] : '';
    }

    /**
     * Creates the local user (and its contact) for a first-time LDAP user.
     *
     * @param string               $username
     * @param string               $password
     * @param array<string, mixed> $ldap_attribs
     *
     * @return bool false if the user could not be stored; the contact made
     *              for it is removed again, so failed attempts leave nothing
     *              behind
     */
    public function createsqluser($username, $password, $ldap_attribs = array())
    {
        // Every user needs a contact: the user lists and the per-user
        // permission checks join on it, so a user without one cannot be
        // found or given a role.
        $c = new CContact();
        if (count($ldap_attribs)) {
            // Contact information based on the inetOrgPerson class schema
            $c->contact_first_name = $this->ldapAttribute($ldap_attribs, 'givenname');
            $c->contact_last_name = $this->ldapAttribute($ldap_attribs, 'sn');
            $c->contact_city = $this->ldapAttribute($ldap_attribs, 'l');
            $c->contact_country = $this->ldapAttribute($ldap_attribs, 'country');
            $c->contact_state = $this->ldapAttribute($ldap_attribs, 'st');
            $c->contact_zip = $this->ldapAttribute($ldap_attribs, 'postalcode');
            $c->contact_job = $this->ldapAttribute($ldap_attribs, 'title');
            $c->contact_email = $this->ldapAttribute($ldap_attribs, 'mail');
            $c->contact_phone = $this->ldapAttribute($ldap_attribs, 'telephonenumber');
        }
        if (mb_strlen((string) $c->contact_first_name) <= 1) {
            // CContact requires a first name; use the login name when the
            // directory has none.
            $c->contact_first_name = $username;
        }
        $c->store();
        if (!$c->contact_id && '' != (string) $c->contact_email) {
            // Most likely another contact already uses this e-mail address.
            $c->contact_email = '';
            $c->clearErrors();
            $c->store();
        }
        if ($c->contact_id && count($ldap_attribs)) {
            $c->setContactMethods(array('phone_mobile' => $this->ldapAttribute($ldap_attribs, 'mobile')));
        }

        $u = new CUser();
        $u->user_username = $username;
        // Plain text: CUser hashes it when the user is created.
        $u->user_password = $password;
        $u->user_type = 0;   // Changed from 1 (administrator) to 0 (Default user)
        $u->user_contact = (int) $c->contact_id;
        $result = $u->store(null, true);

        if (!$result || !$u->user_id) {
            if ($c->contact_id) {
                // Nobody is logged in yet, so CContact::delete() would be
                // refused; remove the contact directly.
                $q = new w2p_Database_Query();
                $q->setDelete('contacts_methods');
                $q->addWhere('contact_id = ' . (int) $c->contact_id);
                $q->exec();
                $q->clear();
                $q->setDelete('contacts');
                $q->addWhere('contact_id = ' . (int) $c->contact_id);
                $q->exec();
            }
            return false;
        }

        $this->user_id = $u->user_id;

        // No role, so user will be created as 'Inactive'
        return true;
    }
}
