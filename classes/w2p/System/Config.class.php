<?php
/**
 * @package     web2project\system
 */

class w2p_System_Config extends w2p_Core_BaseObject
{
    public $config_id;
    public $config_name;
    public $config_value;
    public $config_group;
    public $config_type;

    protected $keepReminders = false;
    
    public function __construct() {
        parent::__construct('config', 'config_id', 'system');
    }

    public function getChildren($id) {
        $q = $this->_getQuery();
        $q->addTable('config_list');
        $q->addOrder('config_list_id');
        $q->addWhere('config_id = ' . (int)$id);
        $result = $q->loadHashList('config_list_id');

        return $result;
    }

    public function canCreate() {
        return $this->_perms->checkModule($this->_tbl_module, 'add');
    }
    public function canEdit() {
        return $this->_perms->checkModule($this->_tbl_module, 'edit');
    }

    public function hook_postStore()
    {
        if ('task_reminder_control' == $this->config_name) {
            $this->keepReminders = true;
        }
    }

    public function cleanUp()
    {
        if (!$this->keepReminders) {
            $queue = new w2p_System_EventQueue();
            $reminders = $queue->find('tasks', 'remind');
            $queue->delete_list = array_keys($reminders);
            $queue->commit_updates();
        }
    }
}