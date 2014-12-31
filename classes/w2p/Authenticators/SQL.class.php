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

        $q = $this->query;
        $q->addTable('users');
        $q->addQuery('user_id, user_password');
        $q->addWhere("user_username = '$username'");
        $result = $q->loadHash();

        // This detects and updates legacy passwords automatically
        if (32 == strlen($result['user_password'])) {
            if (md5($password) == $result['user_password']) {
                $q->clear();
                $q->addTable('users');
                $q->addUpdate('user_password', $this->hashPassword($password));
                $q->addWhere("user_username = '$username'");
                $q->exec();
                $this->user_id = $result['user_id'];
            }
        } else {
            if (password_verify($password, $result['user_password'])) {
                $this->user_id = $result['user_id'];
            }
        }

        return ($this->user_id) ? true : false;
    }
}