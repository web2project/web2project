<?php /* $Id$ $URL$ */

/**
 * @package web2project
 * @subpackage authenticators
 */

class w2p_Authenticators_SQL extends w2p_Authenticators_Base {
	public $user_id;
	public $username;

	public function authenticate($username, $password) {
		global $db, $AppUI;

		$this->username = $username;

		$q = new w2p_Database_Query;
		$q->addTable('users');
		$q->addQuery('user_id, user_password');
		$q->addWhere("user_username = '$username'");
        $q->addWhere("user_password = '".MD5($password)."'");
        $q->exec();

		if ($row = $q->fetchRow()) {
            $this->user_id = $row['user_id'];
			return true;
		}
		return false;
	}

	public function userId() {
		return $this->user_id;
	}
}