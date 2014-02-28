<?php

/**
 * Produces a SELECT list, extends the load method so that the option list
 *    can be loaded from a seperate table
 *
 * @package     web2project\core
 */

class w2p_Core_CustomFieldSelect extends w2p_Core_CustomField
{
    public $options;

    public function getHTML($mode) {
        switch ($mode) {
            case 'edit':
                $html = $this->field_description . ': </td><td>';
                $html .= $this->options->getHTML($this->fieldName(), $this->intValue());
                break;
            case 'view':
                $html = $this->field_description . ': </td><td class="hilite" width="100%">' . $this->options->itemAtIndex($this->intValue());
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