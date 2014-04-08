<?php
/**
 * Authentication against the database is the default
 *
 * @package     web2project\authenticators
 */

class w2p_Authenticators_SQL extends w2p_Authenticators_Base
{
    public $user_id;
    public $username;

    public function authenticate($username, $password)
    {
        $this->username = $username;

        $q = $this->_query;
        $q->addTable('users');
        $q->addQuery('user_id, user_password');
        $q->addWhere("user_username = '$username'");
        $result = $q->loadHash();

        if ($this->verify($username, $password, $result['user_password'])) {
            $this->user_id = (int) $result['user_id'];
        }

        return ($this->user_id) ? true : false;
    }
}