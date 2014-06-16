<?php

/**
 * Loads all custom fields related to a module, produces a html table of all
 * custom fields. Also loads values automatically if the obj_id parameter is
 * supplied. The obj_id parameter is the ID of the module object
 * eg. company_id for companies module
 *
 * @package     web2project\core
 */

class w2p_Core_CustomFields {
    public $m;
    public $a;
    public $mode;
    public $obj_id;
    public $order;
    public $published;

    public $fields = array();

    public function __construct($m, $notUsed, $obj_id = null, $mode = 'edit', $published = 0) {
        $this->m = $m;
        $this->a = 'addedit'; // only addedit pages can carry the custom field for now
        $this->obj_id = $obj_id;
        $this->mode = $mode;
        $this->published = $published;

        // Get Custom Fields for this Module
        $q = new w2p_Database_Query;
        $q->addTable('custom_fields_struct');
        $q->addWhere('field_module = \'' . $this->m . '\' AND field_page = \'' . $this->a . '\'');
        if ($published) {
            $q->addWhere('field_published = 1');
        }
        $q->addOrder('field_order ASC');
        $rows = $q->loadList();
        if (count($rows)) {
            foreach ($rows as $row) {
                switch ($row['field_htmltype']) {
                    case 'checkbox':
                        $this->fields[$row['field_name']] = new w2p_Core_CustomFieldCheckbox( $row['field_id'], $row['field_name'], $row['field_order'], stripslashes($row['field_description']), stripslashes($row['field_extratags']), $row['field_published']);
                        break;
                    case 'href':
                        $this->fields[$row['field_name']] = new w2p_Core_CustomFieldWeblink(  $row['field_id'], $row['field_name'], $row['field_order'], stripslashes($row['field_description']), stripslashes($row['field_extratags']), $row['field_published']);
                        break;
                    case 'textarea':
                        $this->fields[$row['field_name']] = new w2p_Core_CustomFieldTextArea( $row['field_id'], $row['field_name'], $row['field_order'], stripslashes($row['field_description']), stripslashes($row['field_extratags']), $row['field_published']);
                        break;
                    case 'select':
                        $this->fields[$row['field_name']] = new w2p_Core_CustomFieldSelect(   $row['field_id'], $row['field_name'], $row['field_order'], stripslashes($row['field_description']), stripslashes($row['field_extratags']), $row['field_published']);
                        break;
                    case 'label':
                        $this->fields[$row['field_name']] = new w2p_Core_CustomFieldLabel(    $row['field_id'], $row['field_name'], $row['field_order'], stripslashes($row['field_description']), stripslashes($row['field_extratags']), $row['field_published']);
                        break;
                    case 'separator':
                        $this->fields[$row['field_name']] = new w2p_Core_CustomFieldSeparator($row['field_id'], $row['field_name'], $row['field_order'], stripslashes($row['field_description']), stripslashes($row['field_extratags']), $row['field_published']);
                        break;
                    case 'email':
                        $this->fields[$row['field_name']] = new w2p_Core_CustomFieldEmail(    $row['field_id'], $row['field_name'], $row['field_order'], stripslashes($row['field_description']), stripslashes($row['field_extratags']), $row['field_published']);
                        break;
                    default:
                        $this->fields[$row['field_name']] = new w2p_Core_CustomFieldText(     $row['field_id'], $row['field_name'], $row['field_order'], stripslashes($row['field_description']), stripslashes($row['field_extratags']), $row['field_published']);
                        break;
                }
            }

            if ($obj_id > 0) {
                //Load Values
                foreach ($this->fields as $key => $notUsed) {
                    $this->fields[$key]->load($this->obj_id);
                }
            }
        }

    }

    public function add($field_name, $field_description, $field_htmltype, $field_datatype, $field_extratags, $field_order, $field_published, &$error_msg) {
        global $db;

        $q = new w2p_Database_Query;
        $q->addTable('custom_fields_struct');
        $q->addQuery('MAX(field_id)');
        $max_id = $q->loadResult();
        $next_id = $max_id ? $max_id + 1 : 1;

        $field_order = $field_order ? $field_order : 1;
        $field_published = $field_published ? 1 : 0;

        $field_a = 'addedit';

        // TODO - module pages other than addedit
        // TODO - validation that field_name doesnt already exist
        $q = new w2p_Database_Query;
        $q->addTable('custom_fields_struct');
        $q->addInsert('field_id', $next_id);
        $q->addInsert('field_module', $this->m);
        $q->addInsert('field_page', $field_a);
        $q->addInsert('field_htmltype', $field_htmltype);
        $q->addInsert('field_datatype', $field_datatype);
        $q->addInsert('field_order', $field_order);
        $q->addInsert('field_name', $field_name);
        $q->addInsert('field_description', $field_description);
        $q->addInsert('field_extratags', $field_extratags);
        $q->addInsert('field_order', $field_order);
        $q->addInsert('field_published', $field_published);

        if (!$q->exec()) {
            $error_msg = $db->ErrorMsg();
            $q->clear();
            return 0;
        } else {
            $q->clear();
            return $next_id;
        }
    }

    public function update($field_id, $field_name, $field_description, $field_htmltype, $field_datatype, $field_extratags, $field_order, $field_published, &$error_msg) {
        global $db;

        $q = new w2p_Database_Query;
        $q->addTable('custom_fields_struct');
        $q->addUpdate('field_name', $field_name);
        $q->addUpdate('field_description', $field_description);
        $q->addUpdate('field_htmltype', $field_htmltype);
        $q->addUpdate('field_datatype', $field_datatype);
        $q->addUpdate('field_extratags', $field_extratags);
        $q->addUpdate('field_order', $field_order);
        $q->addUpdate('field_published', $field_published);
        $q->addWhere('field_id = ' . $field_id);
        if (!$q->exec()) {
            $error_msg = $db->ErrorMsg();
            $q->clear();
            return 0;
        } else {
            $q->clear();
            return $field_id;
        }
    }

    public function fieldWithId($field_id) {
        if (count($this->fields)) {
            foreach ($this->fields as $k => $notUsed) {
                if ($this->fields[$k]->field_id == $field_id) {
                    return $this->fields[$k];
                }
            }
        }
    }

    /**
     * This method binds the $hash values to the Custom Fields. If you include a
     *   field in the $hash, it will always get updated, even if the value is
     *   null. If you don't want to update the field, don't include it.
     *
     * @param type $hash
     */
    public function bind($hash) {
        if (!count($this->fields) == 0) {
            foreach ($this->fields as $k => $notUsed) {
                if (isset($hash[$k])) {
                    $this->fields[$k]->setValue($hash[$k]);
                } else {
                    $this->fields[$k]->setValue(0);
                }
            }
        }
    }

    public function store($object_id) {
        if (!count($this->fields) == 0) {
            $store_errors = '';
            foreach ($this->fields as $k => $notUsed) {
                $result = $this->fields[$k]->store($object_id);
                if ($result) {
                    $store_errors .= 'Error storing custom field ' . $k . ':' . $result;
                }
            }

            //if ($store_errors) return $store_errors;
            if ($store_errors) {
                echo $store_errors;
            }
        }
    }

    /**
     * This deletes the field itself, *not* just the data for a given object's
     *  entry. Be *very* careful with it.
     *
     * @global type $db
     * @param type $field_id
     * @return type
     */
    public function deleteField($field_id) {
        global $db;
        $q = new w2p_Database_Query;
        $q->setDelete('custom_fields_struct');
        $q->addWhere('field_id = ' . $field_id);
        if (!$q->exec()) {
            $q->clear();
            return $db->ErrorMsg();
        }
    }

    public function count() {
        return count($this->fields);
    }

    public function getHTML() {
        $html = '';
        foreach ($this->fields as $cfield) {
            if ($cfield->field_published) {
                $html .= '<p>' . $cfield->getHTML($this->mode) . '</p>';
            }
        }
        return $html;
    }

    public function printHTML() {
        echo $this->getHTML();
    }

    public function search($moduleTable, $moduleTableId, $moduleTableName, $keyword) {
        $q = new w2p_Database_Query;
        $q->addTable('custom_fields_values', 'cfv');
        $q->addQuery('m.' . $moduleTableId);
        $q->addQuery('m.' . $moduleTableName);
        $q->addQuery('cfv.value_charvalue');
        $q->addJoin('custom_fields_struct', 'cfs', 'cfs.field_id = cfv.value_field_id');
        $q->addJoin($moduleTable, 'm', 'm.' . $moduleTableId . ' = cfv. value_object_id');
        $q->addWhere('cfs.field_module = \'' . $this->m . '\'');
        $q->addWhere('cfv.value_charvalue LIKE \'%' . $keyword . '%\'');
        return $q->loadList();
    }
    public static function getCustomFieldList($module) {
        $q = new w2p_Database_Query;
        $q->addTable('custom_fields_struct', 'cfs');
        $q->addWhere("cfs.field_module = '$module'");
        $q->addOrder('cfs.field_order');

        return $q->loadList();
    }
    public static function getCustomFieldByModule($notUsed, $module, $objectId) {
        $canRead = canView($module, $objectId);

        if ($canRead) {
            $q = new w2p_Database_Query;
            $q->addTable('custom_fields_struct', 'cfs');
            $q->addQuery('cfv.value_charvalue, cfl.list_value');
            $q->leftJoin('custom_fields_values', 'cfv', 'cfv.value_field_id = cfs.field_id');
            $q->leftJoin('custom_fields_lists', 'cfl', 'cfl.list_option_id = cfv.value_intvalue');
            $q->addWhere("cfs.field_module = '$module'");
            $q->addWhere('cfv.value_object_id ='. $objectId);
            return $q->loadList();
        }
    }
}