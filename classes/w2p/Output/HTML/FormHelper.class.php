<?php

class w2p_Output_HTML_FormHelper
{
    protected $AppUI = null;

    public function __construct($AppUI)
    {
        $this->AppUI = $AppUI;
    }

    public function addLabel($label)
    {
        return '<label>' . $this->AppUI->_($label) . ':</label>';
    }

    public function showLabel($label)
    {
        echo $this->addLabel($label);
    }

    public function addField($fieldName, $fieldValue, $options = array(), $values = array())
    {
        $pieces = explode('_', $fieldName);
        $suffix = end($pieces);

        $params = '';
        foreach ($options as $key => $value) {
            $params .= $key . '="' . $value .'" ';
        }

        switch ($suffix) {
            case 'description':
                $output  = '<textarea name="' . $fieldName . '" class="'.$suffix.'">' . w2PformSafe($fieldValue) . '</textarea>';
                break;
            case 'country':
            case 'owner':
            case 'type':
                $output  = arraySelect($values, $fieldName, 'size="1" class="text '.$suffix.'"', $fieldValue);
                break;
            case 'url':
                $output  = '<input type="text" class="text '. $suffix . '" ';
                $output .= 'name="' . $fieldName. '" value="' . w2PformSafe($fieldValue) . '" ' .$params .' />';
                $output .= '<a href="javascript: void(0);" onclick="testURL()">[' . $this->AppUI->_('test') . ']</a>';
                break;
            default:
                $output  = '<input type="text" class="text '. $suffix . '" ';
                $output .= 'name="' . $fieldName. '" value="' . w2PformSafe($fieldValue) . '" ' .$params .' />';
        }

        return $output;
    }

    public function addRow($fieldName, $fieldValue, $options = array(), $values = array())
    {
        $pieces = explode('_', $fieldName);
        $suffix = end($pieces);

        return '<p>' . $this->addLabel(ucwords($suffix)) .
            $this->addField($fieldName, $fieldValue, $options, $values) . '</p>';
    }

    public function addCancelButton()
    {
        $output = '<input type="button" value="' . $this->AppUI->_('back') . '" class="cancel button btn btn-danger" onclick="javascript:history.back(-1);" />';

        return $output;
    }
    public function addSaveButton()
    {
        $output = '<input type="button" value="' . $this->AppUI->_('save') . '" class="save button btn btn-primary" onclick="submitIt()" />';

        return $output;
    }

    public function addNonce()
    {
        $nonce = md5(time() . implode($this->AppUI->user_prefs) );
        $output = '<input type="hidden" name="__nonce" value="'. $nonce . '" />';
        $this->AppUI->__nonce = $nonce;

        return $output;
    }
}