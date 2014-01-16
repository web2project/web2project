<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

// MODULE CONFIGURATION DEFINITION
$config = array();
$config['mod_name'] = 'ProjectDesigner';
$config['mod_version'] = '3.0.0';
$config['mod_directory'] = 'projectdesigner';
$config['mod_setup_class'] = 'CSetupProjectDesigner';
$config['mod_type'] = 'user';
$config['mod_ui_name'] = 'ProjectDesigner';
$config['mod_ui_icon'] = 'projectdesigner.jpg';
$config['mod_description'] = 'A module to design projects';
$config['mod_config'] = true;

if ($a == 'setup') {
	echo w2PshowModuleConfig($config);
}

/**
 * Class CSetupProjectDesigner
 *
 * @package     web2project\modules\misc
 */
class CSetupProjectDesigner extends w2p_System_Setup
{
	public function install() {
		$result = $this->_checkRequirements();

        if (!$result) {
            //$AppUI->setMsg($this->getErrors(), UI_MSG_ERROR);
        }

        $q = $this->_getQuery();
		$q->createTable('project_designer_options');
		$q->createDefinition('(
                pd_option_id INT(11) NOT NULL auto_increment,
                pd_option_user INT(11) NOT NULL default 0 UNIQUE,
                pd_option_view_project INT(1) NOT NULL default 1,
                pd_option_view_gantt INT(1) NOT NULL default 1,
                pd_option_view_tasks INT(1) NOT NULL default 1,
                pd_option_view_actions INT(1) NOT NULL default 1,
                pd_option_view_addtasks INT(1) NOT NULL default 1,
                pd_option_view_files INT(1) NOT NULL default 1,
                PRIMARY KEY (pd_option_id)
            ) ENGINE = MYISAM DEFAULT CHARSET=utf8 ');
		if (!$q->exec()) {
            return false;
        }

        return parent::install();
	}

	public function remove() {
		$q = $this->_getQuery();
		$q->dropTable('project_designer_options');
		$q->exec();

        return parent::remove();
	}

	public function configure() {
		$this->_AppUI->redirect('m=projectdesigner&a=configure');

		return parent::configure();
	}

}