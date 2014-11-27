<?php

/**
 * Produces a SELECT list, extends the load method so that the option list
 *    can be loaded from a seperate table
 *
 * @package     w2p\Core
 */

class w2p_Core_CustomFieldSelect extends w2p_Core_CustomField
{
    public $options;
    public $field_htmltype = 'select';

    public function __construct($field_id, $field_name, $field_order, $field_description, $field_extratags, $field_published)
    {
        parent::__construct($field_id, $field_name, $field_order, $field_description, $field_extratags, $field_published);

        $this->options = new w2p_Core_CustomOptionList($field_id);
        $this->options->load();
    }

    public function getHTML($mode)
    {
        $field = new Web2project\Fields\Select();

        $html = '<label>' . $this->field_description . ':</label>';

        switch ($mode) {
            case 'edit':
                $field->setOptions($this->options->getOptions());
                $html .= $field->edit($this->fieldName(), $this->intValue());
                break;
            case 'view':
                $html .= $field->view($this->options->itemAtIndex($this->intValue()));
                break;
        }
        return $html;
    }

    public function setValue($v) {
        $this->value_intvalue = $v;
    }

    public function value() {
        return $this->value_intvalue;
    }
}