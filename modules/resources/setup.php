<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly.');
}

/*
 * The original work is copyright 2004 by Adam Donnison <adam@saki.com.au> on
 *   behalf of the dotproject project.
 *
 * Major updates occured in 2012 by Keith Casey <keith@caseysoftware.com> for
 *   use with web2project, the dotproject fork.
 */

$config = array();
$config['mod_name']        = 'Resources';           // name the module
$config['mod_version']     = '1.1.0';               // add a version number
$config['mod_directory']   = 'resources';           // tell web2project where to find this module
$config['mod_setup_class'] = 'SResource';           // the name of the PHP setup class (used below)
$config['mod_type']        = 'user';                // 'core' for modules distributed with w2p by standard, 'user' for additional modules
$config['mod_ui_name']	   = $config['mod_name'];   // the name that is shown in the main menu of the User Interface
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

class SResource {
    public function install() {
        global $AppUI;

        $q = new w2p_Database_Query;
        $sql = '(
            resource_id integer not null auto_increment,
            resource_name varchar(255) not null default "",
            resource_key varchar(64) not null default "",
            resource_type integer not null default 0,
            resource_note text not null default "",
            resource_max_allocation integer not null default 100,
            primary key (resource_id),
            key (resource_name),
            key (resource_type)
        )';
        $q->createTable('resources', $sql);
		if (!$q->exec()) {
            return false;
        }

        $q->clear();
        $sql = '(
            resource_id integer not null default 0,
            task_id integer not null default 0,
            percent_allocated integer not null default 100,
            key (resource_id),
            key (task_id, resource_id)
        )';
        $q->createTable('resource_tasks', $sql);
		if (!$q->exec()) {
            return false;
        }

        $this->addTypes();

        $perms = $AppUI->acl();
        return $perms->registerModule('Resources', 'resources');
    }

    public function remove() {
        $q = new w2p_Database_Query;
        $q->dropTable('resources');
        $q->exec();
        $q->clear();
        $q->dropTable('resource_tasks');
        $q->exec();

        global $AppUI;
        $perms = $AppUI->acl();
        return $perms->unregisterModule('resources');
    }

    public function upgrade($old_version) {
        $result = false;

        $q = new w2p_Database_Query;

        // NOTE: All cases should fall through so all updates are executed.
        switch ($old_version) {
            case '1.0':
                $q->addTable('resources');
                $q->addField('resource_key', 'varchar(64) not null default ""');
                $result = $q->exec();
                $q->clear();
            case '1.0.1':
                $resource = new CResource();
                $resource->convertTypes();
            case '1.1.0':
                //current version
            default:
                break;
        }
        return $result;
    }

    private function addTypes()
    {
//TODO: refactor as proper sysvals handling
        $q = new w2p_Database_Query();

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
}