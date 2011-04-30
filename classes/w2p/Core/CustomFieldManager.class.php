<?php

class w2p_Core_CustomFieldManager {

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