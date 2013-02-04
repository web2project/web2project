<?php

/**
 * @package     web2project\core
 *
 * @todo        TODO: couldn't this just extend the BaseObject?
 */

class w2p_Core_Preferences {
	public $pref_user = null;
	public $pref_name = null;
	public $pref_value = null;

    protected $_query = null;

	public function __construct()
    {
		// empty constructor
        $this->_query = new w2p_Database_Query;
	}

	public function bind($hash)
    {
		if (!is_array($hash)) {
			return 'w2p_Core_Preferences::bind failed';
		} else {
			$q = new w2p_Database_Query;
			$q->bindHashToObject($hash, $this);
			return null;
		}
	}

    public function isValid()
    {
        return true;
    }

	public function check()
    {
		return array();
	}

	public function store()
    {
		$q = $this->_getQuery();

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

	public function delete()
    {
		$q = $this->_getQuery();

		$q->setDelete('user_preferences');
		$q->addWhere('pref_user = ' . (int)$this->pref_user);
		$q->addWhere('pref_name = \'' . $this->pref_name . '\'');
		if (!$q->exec()) {
			return db_error();
		} else {
			return null;
		}
	}

    /**
     * Returns a clean query object
     *
     * Clears out the query and then returns it for use
     *
     * @access protected
     *
     * @return w2p_Database_Query Clean query object
     */
    protected function _getQuery()
    {
        $this->_query->clear();
        return $this->_query;
    }
}