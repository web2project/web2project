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
        array('require' => 'php',           'comparator' => '>=', 'version' => '5.2.8'),
        array('require' => 'web2project',   'comparator' => '>=', 'version' => '3'),
        array('require' => 'json',          'comparator' => 'exists'),
        array('require' => 'mysql',         'comparator' => '==', 'version' => '1.0'),
        array('require' => 'Phar',          'comparator' => 'exists'),
        array('require' => 'gd_info',       'comparator' => 'exists'),
        array('require' => 'curl',          'comparator' => 'exists'),
    );

if ($a == 'setup') {
	echo w2PshowModuleConfig($config);
}

class CSetupLinks extends w2p_Core_Setup {

	public function remove() {
		$q = new w2p_Database_Query();
		$q->dropTable('links');
		$q->exec();

		$q->clear();
		$q->setDelete('sysvals');
		$q->addWhere('sysval_title = \'LinkType\'');
		$q->exec();

        return parent::remove();
	}

	public function install() {

        $result = $this->checkRequirements();

        if (!$result) {
            $AppUI->setMsg($this->getErrors(), UI_MSG_ERROR);
        }

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

        return parent::install();
	}
}