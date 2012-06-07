<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

/**
 *  Name: Links
 *  Directory: links
 *  Version 1.0
 *  Type: user
 *  UI Name: Links
 *  UI Icon: ?
 */

$config = array();
$config['mod_name'] = 'Links'; // name the module
$config['mod_version'] = '1.0'; // add a version number
$config['mod_directory'] = 'links'; // tell web2Project where to find this module
$config['mod_setup_class'] = 'CSetupLinks'; // the name of the PHP setup class (used below)
$config['mod_type'] = 'user'; // 'core' for modules distributed with w2P by standard, 'user' for additional modules from dotmods
$config['mod_ui_name'] = 'Links'; // the name that is shown in the main menu of the User Interface
$config['mod_ui_icon'] = 'communicate.gif'; // name of a related icon
$config['mod_description'] = 'Links related to tasks'; // some description of the module
$config['mod_config'] = false; // show 'configure' link in viewmods
$config['mod_main_class'] = 'CLink'; // the name of the PHP class used by the module
$config['permissions_item_table'] = 'links';
$config['permissions_item_field'] = 'link_id';
$config['permissions_item_label'] = 'link_name';

$config['requirements'] = array(
        array('require' => 'php',   'comparator' => '>=', 'version' => '5.3.8'),
        array('require' => 'web2project',   'comparator' => '>=', 'version' => '4.0.0'),
        array('require' => 'libxml',   'comparator' => '>=', 'version' => '5.4.0'),
        array('require' => 'json', 'comparator' => '>=', 'version' => '5.3.0'),
        array('require' => 'mysql', 'comparator' => '>=', 'version' => '5.3.0'),
        array('require' => 'ldap', 'comparator' => '>=', 'version' => '5.3.0'),
        array('require' => 'mbstring', 'comparator' => '>=', 'version' => '5.3.0'),
        array('require' => 'Phar', 'comparator' => '>=', 'version' => '5.3.0'),
        array('require' => 'gd_info', 'comparator' => '>=', 'version' => '5.3.0'),
        array('require' => 'curl', 'comparator' => '>=', 'version' => '5.3.0'),
    );

if ($a == 'setup') {
	echo w2PshowModuleConfig($config);
}

class CSetupLinks {
    protected $_errors;
    
	public function configure() {
		return true;
	}

    /**
     * 	@return string or array Returns the error message
     */
    public function getErrors()
    {
        return $this->_errors;
    }
    protected function checkRequirements(array $requirements = null)
    {
global $AppUI;
        $result = true;

        foreach ($requirements as $requirement) {
            switch ($requirement['require']) {
                case 'web2project':
                    $version = $AppUI->getVersion();
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
                    $requirement['version'] = 0;
                    $requirement['comparator'] = '>';
                    break;
                case '>':
                case '<':
                case '==':
                case '<=':
                case '>=':
                    $result = version_compare($version, $requirement['version'], $requirement['comparator']);
                    break;
                default:
                    
                    //do nothing
            }
            if (!$result) {
                $version = ('' == $version) ? 'n/a' : $version;
                $this->_errors[$requirement['require']] = $requirement['require'] .
                        ' version should be ' . $requirement['comparator'] . ' ' .
                        $requirement['version'] . ' instead it is '.$version;
            }
            
        }
        return $result;
        
    }

	public function remove() {
		$q = new w2p_Database_Query();
		$q->dropTable('links');
		$q->exec();

		$q->clear();
		$q->setDelete('sysvals');
		$q->addWhere('sysval_title = \'LinkType\'');
		$q->exec();
	}

	public function upgrade($old_version) {
		return true;
	}

	public function install(array $config = null) {
        global $AppUI;

        if (isset($config['requirements'])) {
            $result = $this->checkRequirements($config['requirements']);
        } else {
            $result = true;
        }
echo '<pre>'; print_r($this->getErrors()); echo '</pre>';
        if (!$result) {
            $AppUI->setMsg($this->getErrors(), UI_MSG_ERROR);
        }
die();
        $q = new w2p_Database_Query();
		$q->createTable('links');
		$q->createDefinition('(
            link_id int( 11 ) NOT NULL AUTO_INCREMENT ,
            link_url varchar( 255 ) NOT NULL default "",
            link_project int( 11 ) NOT NULL default "0",
            link_task int( 11 ) NOT NULL default "0",
            link_name varchar( 255 ) NOT NULL default "",
            link_parent int( 11 ) default "0",
            link_description text,
            link_owner int( 11 ) default "0",
            link_date datetime default NULL ,
            link_icon varchar( 20 ) default "obj/",
            link_category int( 11 ) NOT NULL default "0",
            PRIMARY KEY ( link_id ) ,
            KEY idx_link_task ( link_task ) ,
            KEY idx_link_project ( link_project ) ,
            KEY idx_link_parent ( link_parent )
            ) ENGINE = MYISAM DEFAULT CHARSET=utf8 ');

		$q->exec($sql);

        $i = 0;
        $linkTypes = array('Unknown', 'Document', 'Application');
        foreach ($linkTypes as $linkType) {
            $q->clear();
            $q->addTable('sysvals');
            $q->addInsert('sysval_key_id', 1);
            $q->addInsert('sysval_title', 'LinkType');
            $q->addInsert('sysval_value', $linkType);
            $q->addInsert('sysval_value_id', $i);
            $q->exec();
            $i++;
        }

        $perms = $AppUI->acl();
        return $perms->registerModule('Links', 'links');
	}
}