<?php

abstract class w2p_Core_Setup {
    
    protected $_errors;
    protected $_AppUI;
    protected $_perms;
    protected $_config;

    public function __construct(w2p_Core_CAppUI $AppUI = null, array $config = null)
    {
        $this->_AppUI = $AppUI;
        $this->_perms = $this->_AppUI->acl();

        $this->_config = $config;
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
        $name = strtolower($this->_config['mod_name']);
        return $this->_perms->unregisterModule($name);
    }

    public function install()
    {
        $name = strtolower($this->_config['mod_name']);
        return $this->_perms->registerModule($this->_config['mod_name'], $name);
    }

    /*
     * This is a complex bit of code. There are two switch statements within the
     *   foreach. The first determines the version of the library we're actually
     *   looking for while the second determines the comparison we want.
     * The input to this is an array specifying the item, version, and comparison.
     * 
     * Here's an example:
     * $config['requirements'] = array(
     *     array('require' => 'php',         'comparator' => '>=', 'version' => '5.2.8'),
     *     array('require' => 'web2project', 'comparator' => '>=', 'version' => '3'),
     *     array('require' => 'json',        'comparator' => 'exists'),
     *     array('require' => 'mysql',       'comparator' => '==', 'version' => '1.0'),
     *     array('require' => 'Phar',        'comparator' => 'exists'),
     *     array('require' => 'gd_info',     'comparator' => 'exists'),
     *     array('require' => 'curl',        'comparator' => 'exists'));
     * @return boolean
     */
    protected function checkRequirements()
    {
        $result = true;

        $requirements = (isset($this->_config['requirements'])) ? 
                $this->_config['requirements'] : array();

        foreach ($requirements as $requirement) {
            switch ($requirement['require']) {
                case 'web2project':
                    $version = $this->_AppUI->getVersion();
                    break;
                case 'php':
                    $version = PHP_VERSION;
                    break;
                case 'gd_info':
                    $version = 0;
                    if (function_exists('gd_info')) {
                        $lib_version = gd_info();
                        $version = $lib_version['GD Version'];
                    }
                    break;
                case 'curl':
                    $version = 0;
                    if (function_exists('curl_version')) {
                        $lib_version = curl_version();
                        $version = $lib_version['version'];
                    }
                    break;
                default:
                    $version = phpversion($requirement['require']);
            }
            
            switch ($requirement['comparator']) {
                case 'exists':
                    $requirement['version'] = '0';
                    $requirement['comparator'] = '>=';
                case '>':
                case '<':
                case '==':
                case '<=':
                case '>=':
                    $version = preg_replace("/[^0-9.]/", "", $version );
                    $result = version_compare($version, $requirement['version'], $requirement['comparator']);
                    break;
                default:
                    
                    //do nothing
            }
            if (!$result) {
                $version = ('' == $version) ? 'n/a' : $version;
//TODO: This needs internationalization.
                $this->_errors[$requirement['require']] = $requirement['require'] .
                        ' version should be ' . $requirement['comparator'] . ' ' .
                        $requirement['version'] . ' instead it is '.$version;
            }
            
        }
        return $result;
    }
}