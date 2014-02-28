<?php
if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly.');
}
// @todo    update version information

/*
 * The original work is copyright 2004 by Adam Donnison <adam@saki.com.au> on
 *   behalf of the dotproject project.
 *
 * Major updates occurred in 2012 by Keith Casey <keith@caseysoftware.com> for
 *   use with web2project, the dotproject fork.
 */

$config = array();
$config['mod_name']        = 'Resources';           // name the module
$config['mod_version']     = '3.1.1';               // add a version number
$config['mod_directory']   = 'resources';           // tell web2project where to find this module
$config['mod_setup_class'] = 'CSetupResources';     // the name of the PHP setup class (used below)
$config['mod_type']        = 'user';                // 'core' for modules distributed with w2p by standard, 'user' for additional modules
$config['mod_ui_name']       = $config['mod_name'];   // the name that is shown in the main menu of the User Interface
$config['mod_ui_icon']     = 'resources.png';       // name of a related icon
$config['mod_description'] = 'Resources';           // some description of the module
$config['mod_config']      = false;                 // show 'configure' link in viewmods
$config['mod_main_class']  = 'CResource';

$config['permissions_item_table'] = 'resources';
$config['permissions_item_field'] = 'resource_id';
$config['permissions_item_label'] = 'resource_name';

if ($a == 'setup') {
    echo w2PshowModuleConfig($config);
}

/**
 * Class SResource
 *
 * @package     web2project\modules\misc
 */
class CSetupResources extends w2p_System_Setup
{
    public function install() {
        $q = $this->_getQuery();
        $q->createTable('resources');
        $q->createDefinition('(
            resource_id integer not null auto_increment,
            resource_name varchar(255) not null default "",
            resource_key varchar(64) not null default "",
            resource_type integer not null default 0,
            resource_note text not null default "",
            resource_max_allocation integer not null default 100,
            primary key (resource_id),
            key (resource_name),
            key (resource_type)
            ) ENGINE = MYISAM DEFAULT CHARSET=utf8 ');
        if (!$q->exec()) { return false; }

        $q->clear();
        $q->createTable('resource_tasks');
        $q->createDefinition('(
            resource_id integer not null default 0,
            task_id integer not null default 0,
            percent_allocated integer not null default 100,
            key (resource_id),
            key (task_id, resource_id)
            ) ENGINE = MYISAM DEFAULT CHARSET=utf8 ');
        if (!$q->exec()) {
            return false;
        }

        $this->addTypes();

        return parent::install();
    }

    public function remove() {
        $q = $this->_getQuery();
        $q->dropTable('resources');
        $q->exec();

        $q->clear();
        $q->dropTable('resource_tasks');
        $q->exec();

        $q->clear();
        $q->setDelete('sysvals');
        $q->addWhere("sysval_title = 'ResourceTypes'");
        $q->exec();

        return parent::remove();
    }

    public function upgrade($old_version) {
        $result = false;

        $q = $this->_getQuery();

        // NOTE: All cases should fall through so all updates are executed.
        switch ($old_version) {
            case '1.0':
                $q->alterTable('resources');
                $q->addField('resource_key', 'varchar(64) not null default ""');
                $result = $q->exec();
                $q->clear();
            case '1.0.1':
                $this->convertTypes();
            case '1.1.0':
                $q->alterTable('resources');
                $q->addField('resource_description', 'text NOT NULL');
                $result = $q->exec();
                $q->clear();

                $q->addTable('resources');
                $q->addQuery('resource_id, resource_note');
                $resources = $q->loadList();
                $q->clear();
                foreach($resources as $resource) {
                    $q->addTable('resources');
                    $q->addUpdate('resource_description', $resource['resource_note']);
                    $q->addWhere('resource_id = ' . $resource['resource_id']);
                    $q->exec();
                    $q->clear();
                }
            case '3.1.1':
                //current version
            default:
                break;
        }
        return $result;
    }

    private function addTypes()
    {
//TODO: refactor as proper sysvals handling
        $q = $this->_getQuery();

        $i = 1;
        $resourceTypes = array('All resources', 'Equipment', 'Tool', 'Venue');
        foreach ($resourceTypes as $type) {
            $q->addTable('sysvals');
            $q->addInsert('sysval_key_id', 1);
            $q->addInsert('sysval_title', 'ResourceTypes');
            $q->addInsert('sysval_value', $type);
            $q->addInsert('sysval_value_id', $i);
            $q->exec();
            $q->clear();
            $i++;
        }
        return true;
    }

    private function convertTypes()
    {
        $q = $this->_getQuery();
        $q->addTable('resource_types');
        $q->addQuery('*');
        $types = $q->loadList();

        $resourceTypes = array();
        foreach($types as $type) {
            $resourceTypes[$type['resource_type_id']] = $type['resource_type_name'];
        }

        foreach ($resourceTypes as $id => $type) {
            $q->addTable('sysvals');
            $q->addInsert('sysval_key_id', 1);
            $q->addInsert('sysval_title', 'ResourceTypes');
            $q->addInsert('sysval_value', $type);
            $q->addInsert('sysval_value_id', $id);
            $q->exec();
            $q->clear();
        }

        // This removes the dead table.
        $q->dropTable('resource_types');
        $q->exec();
    }
}