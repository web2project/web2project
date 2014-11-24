<?php
namespace Web2project\Authenticators;

/**
 * Authentication against the database is the default
 *
 * @package     web2project\authenticators
 */

class SQL extends \Web2project\Authenticators\Base
{
    public $user_id;
    public $username;

    public function authenticate($username, $password)
    {
        $this->username = $username;

        $q = $this->query;
        $q->addTable('users');
        $q->addQuery('user_id');
        $q->addWhere("user_username = '$username'");
        $q->addWhere("user_password = '".$this->hashPassword($password)."'");
        $this->user_id = (int) $q->loadResult();

        return ($this->user_id) ? true : false;
    }
}