<?php
/**
 * @package     web2project\system
 *
 * @abstract
 */

abstract class w2p_System_Setup {
    
    protected $_errors;
    protected $_AppUI;
    protected $_perms;
    protected $_config;

    public function __construct(w2p_Core_CAppUI $AppUI = null, 
            array $config = null, w2p_Database_Query $query = null)
    {
        $this->_AppUI = $AppUI;
        $this->_perms = $this->_AppUI->acl();
        $this->_query = (is_null($query)) ? new w2p_Database_Query() : $query;

        $this->_config = $config;
    }

    /**
     *     @return string or array Returns the error message
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
    public function upgrade()
    {
        return true;
    }

    public function remove()
    {
        $name = strtolower($this->_config['mod_name']);

        $q = $this->_getQuery();
        $q->setDelete('module_config');
        $q->addWhere("module_name = '$name'");
        $q->exec();

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
    protected function _meetsRequirements()
    {
        $result = true;

        $requirements = (isset($this->_config['requirements'])) ? 
                $this->_config['requirements'] : array();
        $modules = $this->_AppUI->getActiveModules();

        foreach ($requirements as $requirement) {
            switch ($requirement['require']) {
                // This gets the web2project version
                case 'web2project':
                    $version = $this->_AppUI->getVersion();
                    break;
                // This gets the PHP version
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
                    if (isset($modules[$requirement['require']])) {
                        // This gets the version of a specific module
                        $q = $this->_getQuery();
                        $q->addTable('modules');
                        $q->addQuery('mod_version');
                        $q->addWhere("mod_directory = '".$requirement['require']."'");
                        $version = $q->loadResult();
                    } else {
                        // And if all else fails, we check php libraries
                        $version = phpversion($requirement['require']);
                    }
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

    protected function _checkRequirements()
    {
        trigger_error("The _checkRequirements method has been deprecated. Please use meetsRequirements instead.", E_USER_NOTICE );
        return $this->_meetsRequirements();
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