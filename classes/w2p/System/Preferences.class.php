<?php
/**
 * This is the core preferences object that runs both the default and User-based preferences.
 *
 * @package     web2project\system
 *
 * @todo    couldn't this just extend the BaseObject?
 */
class w2p_System_Preferences {
    /** This is the user_id */
    public $pref_user = null;
    /** There is no defined list of preferences, so we can add as needed */
    public $pref_name = null;
    /** This is the value itself */
    public $pref_value = null;

    protected $_query = null;

    /**
     * @todo refactor
     */
    public function __construct()
    {
        // empty constructor
        $this->_query = new w2p_Database_Query;
    }

    /**
     * @todo refactor
     */
    public function bind($hash)
    {
        if (!is_array($hash)) {
            return 'w2p_System_Preferences::bind failed';
        } else {
            $q = new w2p_Database_Query;
            $q->bindHashToObject($hash, $this);
            return null;
        }
    }

    /**
     * @todo refactor
     */
    public function isValid()
    {
        return true;
    }

    /**
     * @todo refactor
     */
    public function check()
    {
        return array();
    }

    /**
     * @todo refactor
     */
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

    /**
     * @todo refactor
     */
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
     * @todo refactor
     *
     * @return w2p_Database_Query Clean query object
     */
    protected function _getQuery()
    {
        $this->_query->clear();
        return $this->_query;
    }
}