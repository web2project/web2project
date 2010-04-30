<?php /* $Id$ $URL$ */

/**
 * @package web2project
 * @subpackage authenticators
 */

class w2p_Authenticators_SQL {
	public $user_id;
	public $username;

	public function authenticate($username, $password) {
		global $db, $AppUI;

		$this->username = $username;

		$q = new DBQuery;
		$q->addTable('users');
		$q->addQuery('user_id, user_password');
		$q->addWhere('user_username = \'' . $username . '\'');
		if (!$rs = $q->exec()) {
			$q->clear();
			return false;
		}
		if (!$row = $q->fetchRow()) {
			$q->clear();
			return false;
		}

		$this->user_id = $row['user_id'];
		$q->clear();
		if (MD5($password) == $row['user_password']) {
			return true;
		}
		return false;
	}

	public function userId() {
		return $this->user_id;
	}
}