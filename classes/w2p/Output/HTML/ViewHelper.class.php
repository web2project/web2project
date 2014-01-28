<?php
/**
 * Class w2p_Output_HTML_FormHelper
 */
class w2p_Output_HTML_ViewHelper extends w2p_Output_HTML_Base
{
    public function addField($fieldName, $fieldValue)
    {
        if ('' == $fieldValue) {
            return '-';
        }

        $pieces = explode('_', $fieldName);
        $suffix = end($pieces);

        switch($suffix) {
            case 'email':
                $output = w2p_email($fieldValue);
                break;
            case 'url':
                $value = str_replace(array('"', '"', '<', '>'), '', $fieldValue);
                $output = w2p_url($value);
                break;
            case 'owner':
                $obj = new CContact();
                $obj->findContactByUserid($fieldValue);
                $link = '?m=users&a=view&user_id='.$fieldValue;
                $output = '<a href="'.$link.'">'.$obj->contact_display_name.'</a>';
                break;
            default:
                $output = htmlspecialchars($fieldValue, ENT_QUOTES);
        }

        return $output;
    }

    public function showField($fieldName, $fieldValue)
    {
        echo $this->addField($fieldName, $fieldValue, $options, $values);
    }
}