<?php

abstract class w2p_Core_Setup {
    
    protected $_errors;
    protected $_AppUI;
    protected $_perms;

    public function __construct(w2p_Core_CAppUI $AppUI = null)
    {
        $this->_AppUI = $AppUI;
        $this->_perms = $this->_AppUI->acl();
    }

    /**
     * 	@return string or array Returns the error message
     */
    public function getErrors()
    {
        return $this->_errors;
    }

    /*
    * By default, configure should always work because it doesn't do anything.
    */
    public function configure()
    {
        return true;
    }

    /*
    * By default, upgrade should always work because it doesn't do anything.
    */
    public function upgrade($old_version)
    {
        return true;
    }

    public function remove()
    {
        return true;
    }

    public function install()
    {
        return true;
    }

    protected function checkRequirements()
    {
        return true;
    }
}