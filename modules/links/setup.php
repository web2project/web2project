<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$config = array();
$config['mod_name'] = 'Links';
$config['mod_version'] = '3.0.0';
$config['mod_directory'] = 'links';                     // tell web2Project where to find this module
$config['mod_setup_class'] = 'CSetupLinks';             // the name of the PHP setup class (used below)
$config['mod_type'] = 'user';                           // 'core' for modules distributed with w2P by standard, 'user' for additional modules from dotmods
$config['mod_ui_name'] = 'Links';
$config['mod_ui_icon'] = 'communicate.gif';
$config['mod_description'] = 'Links related to tasks';
$config['mod_config'] = false;                          // show 'configure' link in viewmods
$config['mod_main_class'] = 'CLink';
$config['permissions_item_table'] = 'links';
$config['permissions_item_field'] = 'link_id';
$config['permissions_item_label'] = 'link_name';

if ($a == 'setup') {
	echo w2PshowModuleConfig($config);
}

/**
 * Class CSetupLinks
 *
 * @package     web2project\modules\misc
 */
class CSetupLinks extends w2p_System_Setup
{
	public function remove()
    {
		$q = $this->_getQuery();
		$q->dropTable('links');
		$q->exec();

		$q = $this->_getQuery();
		$q->setDelete('sysvals');
		$q->addWhere('sysval_title = \'LinkType\'');
		$q->exec();

        return parent::remove();
	}

	public function install()
    {
        $result = $this->_checkRequirements();

        if (!$result) {
            return false;
        }

        $q = $this->_getQuery();
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
		if (!$q->exec()) {
            return false;
        }

        $i = 0;
        $linkTypes = array('Unknown', 'Document', 'Application');
//TODO: refactor as proper sysvals handling
        foreach ($linkTypes as $linkType) {
            $q = $this->_getQuery();
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