<?php

/**
 *	@package web2Project
 *	@subpackage modules
 *	@version $Revision$
 */

class CHistory extends w2p_Core_BaseObject {
    public $history_id = null;
    public $history_date = null;
    public $history_user = null;
    public $history_action = null;
    public $history_item = null;
    public $history_table = null;
    public $history_project = null;
    public $history_name = null;
    public $history_changes = null;
    public $history_description = null;

    public function __construct() {
        parent::__construct('history', 'history_id');
    }

    public function check() {
        // ensure the integrity of some variables
        $errorArray = array();
        $baseErrorMsg = get_class($this) . '::store-check failed - ';
        //there aren't any checks yet

        return $errorArray;
    }

    public function delete(CAppUI $AppUI) {
        $perms = $AppUI->acl();

        return true;
    }

    public function store(CAppUI $AppUI) {
        global $AppUI;
        $perms = $AppUI->acl();

        return true;
    }

    public function show_history($history) {
        global $AppUI;

        $id = $history['history_item'];
        $module = $history['history_table'];
        $secondary_key = '';
        if ($module == 'companies') {
            $table_id = 'company_id';
        } elseif ($module == 'modules') {
            $table_id = 'mod_id';
        } elseif ($module == 'departments') {
            $table_id = 'dept_id';
        } elseif ($module == 'forums') {
            $table_id = 'forum_id';
        } elseif ($module == 'forum_messages') {
            $table_id = 'message_id';
        } elseif ($module == 'task_log') {
            $table_id = (substr($module, -1) == 's' ? substr($module, 0, -1) : $module) . '_id';
            $secondary_key = ', task_log_task';
        } else {
            $table_id = (substr($module, -1) == 's' ? substr($module, 0, -1) : $module) . '_id';
        }

        if ($module == 'login') {
            return $AppUI->_('User') . ' "' . $history['history_description'] . '" ' . $AppUI->_($history['history_action']);
        }

        if ($history['history_action'] == 'add') {
            $msg = $AppUI->_('Added new') . ' ';
        } elseif ($history['history_action'] == 'update') {
            $msg = $AppUI->_('Modified') . ' ';
        } elseif ($history['history_action'] == 'delete') {
            return $AppUI->_('Deleted') . ' "' . $history['history_description'] . '" ' . $AppUI->_('from') . ' ' . $AppUI->_($module) . ' ' . $AppUI->_('module');
        }

        $q = new w2p_Database_Query;
        $q->addTable($module);
        $q->addQuery($table_id.$secondary_key);
        $q->addWhere($table_id . ' =' . $id);
        $result = $q->loadHash();
        if ($result) {
            switch ($module) {
                case 'history':
                    $link = '&a=addedit&history_id=';
                    break;
                case 'files':
                    $link = '&a=addedit&file_id=';
                    break;
                case 'tasks':
                    $link = '&a=view&task_id=';
                    break;
                case 'forums':
                    $link = '&a=viewer&forum_id=';
                    break;
                case 'projects':
                    $link = '&a=view&project_id=';
                    break;
                case 'companies':
                    $link = '&a=view&company_id=';
                    break;
                case 'contacts':
                    $link = '&a=view&contact_id=';
                    break;
                case 'task_log':
                    $module = 'tasks';
                    $link = '&a=view&task_id='.$result['task_log_task'].'&tab=0#tasklog';
                    break;
            }
        }
        $q->clear();

        if (!empty($link)) {
            $link = '<a href="?m=' . $module . $link . $id . '">' . $history['history_description'] . '</a>';
        } else {
            $link = $history['history_description'];
        }
        $msg .= $AppUI->_('item') . " '$link' " . $AppUI->_('in') . ' ' . $AppUI->_(ucfirst($module)) . ' ' . $AppUI->_('module'); // . $history;

        return $msg;
    }
}