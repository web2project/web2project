<?php
/**
 * @package     web2project\modules\misc
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

    /**
     * The history entries are creatable by everyone but not editable or
     *    deletable by anyone. This is to protect its integrity as an audit log.
     *
     * @return boolean
     */
    public function delete()    {   return false;   }
    public function canDelete() {   return false;   }
    public function canCreate() {   return true;    }
    public function canEdit()   {   return false;   }

    /**
     * @todo TODO: This needs to be fleshed out.
     *
     * @return boolean
     */
    public function store() {
        return true;
    }

    /**
     * @todo TODO: This should validate that we can actually view this specific
     *    (module-aware) log entry instead of the log entry itself.
     */
    public function canView() {

        return true;
    }

    /**
     * Cean up this monstrousity to use the naming conventions.
     *
     * @param type $history
     * @return string 
     */
    public function show_history($history) {
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
            return $this->_AppUI->_('User') . ' "' . $history['history_description'] . '" ' . $this->_AppUI->_($history['history_action']);
        }

        if ($history['history_action'] == 'add') {
            $msg = $this->_AppUI->_('Added new') . ' ';
        } elseif ($history['history_action'] == 'update') {
            $msg = $this->_AppUI->_('Modified') . ' ';
        } elseif ($history['history_action'] == 'delete') {
            return $this->_AppUI->_('Deleted') . ' "' . $history['history_description'] . '" ' . $this->_AppUI->_('from') . ' ' . $this->_AppUI->_($module) . ' ' . $this->_AppUI->_('module');
        }

        $q = $this->_getQuery();
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
        $msg .= $this->_AppUI->_('item') . " '$link' " . $this->_AppUI->_('in') . ' ' . $this->_AppUI->_(ucfirst($module)) . ' ' . $this->_AppUI->_('module'); // . $history;

        return $msg;
    }
}