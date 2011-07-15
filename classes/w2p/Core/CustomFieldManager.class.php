<?php

class w2p_Core_CustomFieldManager extends w2p_Core_BaseObject {

    public $field_id = 0;
    public $field_module = '';
    public $field_page = '';
    public $field_htmltype = '';
    public $field_datatype = '';
    public $field_order = 0;
    public $field_name = '';
    public $field_extratags = '';
    public $field_description = '';
    public $field_published = 1;

    protected $html_types = array();

    public function __construct() {
        parent::__construct('custom_fields_struct', 'field_id');

        $this->html_types = array('textinput' => 'Text Input', 'textarea' => 'Text Area',
                    'checkbox' => 'Checkbox', 'select' => 'Select List', 'label' => 'Label',
                    'separator' => 'Separator', 'href' => 'Weblink');
    }

    public function getType($name) {
        return $this->html_types[$name];
    }

    public function getTypes() {
        return $this->html_types;
    }

    public function getModuleList() {
        $q = new w2p_Database_Query;
        $q->addTable('modules');
        $q->addOrder('mod_ui_order');
        $q->addWhere("mod_directory IN ('companies', 'projects', 'tasks', 'calendar', 'contacts')");

        return $q->loadList();
    }

    public function getStructure($moduleName) {
        $q = new w2p_Database_Query;
        $q->addTable('custom_fields_struct');
        $q->addWhere("field_module = '$moduleName'");
        $q->addOrder('field_order ASC');

        return $q->loadList();
    }

    public function canDelete() {
        return true;
    }

    public function delete(CAppUI $AppUI) {
        $perms = $AppUI->acl();

        if (canEdit('system')) {
            if ($msg = parent::delete()) {
                return $msg;
            }
            return true;
        }
        return false;
    }

    public function store(CAppUI $AppUI) {
        $perms = $AppUI->acl();
        $stored = false;

        $errorMsgArray = $this->check();
        if (count($errorMsgArray) > 0) {
            return $errorMsgArray;
        }
        /*
         * TODO: I don't like the duplication on each of these two branches, but I
         *   don't have a good idea on how to fix it at the moment...
         */
        if ($this->field_id && canEdit('system')) {

            if (($msg = parent::store())) {
                return $msg;
            }
            $stored = true;
        }
        if (0 == $this->field_id && canEdit('system')) {
            if (($msg = parent::store())) {
                return $msg;
            }
            $stored = true;
        }
        return $stored;
    }
}