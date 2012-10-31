<?php
/**
 *	@package web2project
 *	@subpackage core
 *	@version $Revision$
 */

/**
 *	w2p_Core_Preferences Class.

 */

//TODO: couldn't this just extend the BaseObject?
class w2p_Core_Preferences {
	public $pref_user = null;
	public $pref_name = null;
	public $pref_value = null;

	public function __construct() {
		// empty constructor
	}

	public function bind($hash) {
		if (!is_array($hash)) {
			return 'w2p_Core_Preferences::bind failed';
		} else {
			$q = new w2p_Database_Query;
			$q->bindHashToObject($hash, $this);
			return null;
		}
	}

	public function check() {
		// TODO MORE
		return null; // object is ok
	}

	public function store() {
		$msg = $this->check();
		if ($msg) {
			return 'CPreference::store-check failed ' . $msg;
		}
		if (($msg = $this->delete())) {
			return 'CPreference::store-delete failed ' . $msg;
		}
		$q = new w2p_Database_Query;
		if (!$q->insertObject('user_preferences', $this)) {
			return 'CPreference::store failed ' . db_error();
		} else {
			return null;
		}
	}

	public function delete() {
		$q = new w2p_Database_Query;
		$q->setDelete('user_preferences');
		$q->addWhere('pref_user = ' . (int)$this->pref_user);
		$q->addWhere('pref_name = \'' . $this->pref_name . '\'');
		if (!$q->exec()) {
			return db_error();
		} else {
			return null;
		}
	}
}