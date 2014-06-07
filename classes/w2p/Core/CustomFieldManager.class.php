<?php

/**
 * @package     web2project\core
 */

class w2p_Core_CustomFieldManager extends w2p_Core_BaseObject {

    public $field_id = 0;
    public $field_module = '';
    public $field_page = 'addedit';
    public $field_htmltype = '';
    public $field_datatype = 'alpha';
    public $field_order = 0;
    public $field_name = '';
    public $field_extratags = '';
    public $field_description = '';
    public $field_published = 1;

    protected $html_types = array();

    public function __construct() {
        parent::__construct('custom_fields_struct', 'field_id', 'system');

        $this->html_types = array('textinput' => 'Text Input', 'textarea' => 'Text Area',
                    'checkbox' => 'Checkbox', 'select' => 'Select List', 'label' => 'Label',
                    'separator' => 'Separator', 'href' => 'Weblink', 'email' => 'Email');
    }

    public function getType($name) {
        return $this->html_types[$name];
    }

    public function getTypes() {
        return $this->html_types;
    }

    public function getModuleList() {
        $q = $this->_getQuery();
        $q->addTable('modules');
        $q->addOrder('mod_ui_order');
        $q->addWhere("mod_directory IN ('companies', 'projects', 'tasks', 'calendar', 'contacts')");

        return $q->loadList();
    }

    public function getStructure($moduleName) {
        $q = $this->_getQuery();
        $q->addTable('custom_fields_struct');
        $q->addWhere("field_module = '$moduleName'");
        $q->addOrder('field_order ASC');

        return $q->loadList();
    }
}