<?php

class w2p_Core_CustomFieldManager {

    protected $html_types = array();

    public function __construct(w2p_Core_CAppUI $AppUI) {
        $this->html_types = array('textinput' => $AppUI->_('Text Input'),
                    'textarea' => $AppUI->_('Text Area'),
                    'checkbox' => $AppUI->_('Checkbox'),
                    'select' => $AppUI->_('Select List'),
                    'label' => $AppUI->_('Label'),
                    'separator' => $AppUI->_('Separator'),
                    'href' => $AppUI->_('Weblink'));
    }

    public function getType($name) {
        return $this->html_types[$name];
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
}