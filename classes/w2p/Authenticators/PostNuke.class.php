<?php
/**
 * PostNuke authentication has encoded information passed in on the login
 * request. This needs to be extracted and verified.
 *
 * @package     web2project\authenticators
 * @todo    remove
 *
 * @deprecated
 */

class w2p_Authenticators_PostNuke extends w2p_Authenticators_Base
{
    public function __construct()
    {
        parent::__construct();

        $this->fallback = isset($w2Pconfig['postnuke_allow_login']) ?
            $w2Pconfig['postnuke_allow_login'] : false;

        trigger_error("w2p_Authenticators_PostNuke has been deprecated in v3.0 and will be removed by v4.0. There is no replacement as PostNuke is a dead project.", E_USER_NOTICE );
    }

    public function authenticate($username, $password)
    {
        global $db;
        if (!isset($_REQUEST['userdata'])) {
            // fallback to SQL Authentication if PostNuke fails.
            if ($this->fallback) {
                $sqlAuth = new w2p_Authenticators_SQL();
                return $sqlAuth->authenticate($username, $password);
            } else {
                die($this->AppUI->_('You have not configured your PostNuke site
                              correctly'));
            }
        }

        if (!$compressed_data = base64_decode(urldecode($_REQUEST['userdata']))) {
            die($this->AppUI->_('The credentials supplied were missing or corrupted') . ' (1)');
        }
        if (!$userdata = gzuncompress($compressed_data)) {
            die($this->AppUI->_('The credentials supplied were missing or corrupted') . ' (2)');
        }
        if (!$_REQUEST['check'] = $this->hashPassword($userdata)) {
            die($this->AppUI->_('The credentials supplied were issing or corrupted') . ' (3)');
        }
        $user_data = unserialize($userdata);

        // Now we need to check if the user already exists, if so we just
        // update.  If not we need to create a new user and add a default
        // role.
        $username = trim($user_data['login']);
        $this->username = $username;
        $names = explode(' ', trim($user_data['name']));
        $last_name = array_pop($names);
        $first_name = implode(' ', $names);
        $passwd = trim($user_data['passwd']);
        $email = trim($user_data['email']);

        $q = $this->query;
        $q->addTable('users');
        $q->addQuery('user_id, user_password, user_contact');
        $q->addWhere('user_username = \'' . $username . '\'');
        if (!$rs = $q->exec()) {
            die($this->AppUI->_('Failed to get user details') . ' - error was ' . $db->ErrorMsg());
        }
        if ($rs->RecordCount() < 1) {
            $q->clear();
            $this->createsqluser($username, $passwd, $email, $first_name, $last_name);
        } else {
            if (!$row = $rs->FetchRow()) {
                    die($this->AppUI->_('Failed to retrieve user detail'));
            }
            // User exists, update the user details.
            $this->user_id = $row['user_id'];
            $q->clear();
            $q->addTable('users');
            $q->addUpdate('user_password', $passwd);
            $q->addWhere('user_id = ' . $this->user_id);
            if (!$q->exec()) {
                die($this->AppUI->_('Could not update user credentials'));
            }
            $q->clear();
            $q->addTable('contacts');
            $q->addUpdate('contact_first_name', $first_name);
            $q->addUpdate('contact_last_name', $last_name);
            $q->addUpdate('contact_email', $email);
            $q->addWhere('contact_id = ' . $row['user_contact']);
            if (!$q->exec()) {
                die($this->AppUI->_('Could not update user details'));
            }
        }
        return true;
    }

    public function createsqluser($username, $password, $email, $first, $last)
    {
        $hash_pass = $this->hashPassword($password);

        $c = new CContact();
        $c->contact_first_name = $first;
        $c->contact_last_name = $last;
        $c->contact_email = $email;
        $c->store();
    
        $u = new CUser();
        $u->user_username = $username;
        $u->user_password = $hash_pass;
        $u->user_type = 0; // Changed from 1 (administrator) to 0 (Default user)
        $u->user_contact = (int) $c->contact_id;
        $u->store(null, true);
    
        $user_id = $u->user_id;
        $this->user_id = $user_id;

        $acl = &$this->AppUI->acl();
        $acl->insertUserRole($acl->get_group_id('anon'), $this->user_id);
    }
}