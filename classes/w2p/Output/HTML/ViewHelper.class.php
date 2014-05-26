<?php
/**
 * Class w2p_Output_HTML_FormHelper
 *
 * @package     web2project\output\html
 * @author      D. Keith Casey, Jr. <contrib@caseysoftware.com>
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
            case 'datetime':
                $myDate = intval($fieldValue) ? new w2p_Utilities_Date($this->AppUI->formatTZAwareTime($fieldValue, '%Y-%m-%d %T')) : null;
                $output = $myDate ? $myDate->format($this->dtf) : '-';
                break;
            case 'email':
                $output = w2p_email($fieldValue);
                break;
            case 'url':
                $value = str_replace(array('"', '"', '<', '>'), '', $fieldValue);
                $output = w2p_url($value);
                break;
            case 'owner':
                if (!$fieldValue) {
                    return '-';
                }
                $obj = new CContact();
                $obj->findContactByUserid($fieldValue);
                $link = '?m=users&a=view&user_id='.$fieldValue;
                $output = '<a href="'.$link.'">'.$obj->contact_display_name.'</a>';
                break;
            case 'percent':
                $output = round($fieldValue).'%';
                break;
            case 'description':
                $output = w2p_textarea($fieldValue);
                break;
            case 'company':
            case 'department':
            case 'project':
                $class  = 'C'.ucfirst($suffix);
                $obj = new $class();
                $obj->load($fieldValue);
                $link = '?m='. w2p_pluralize($suffix) .'&a=view&'.$suffix.'_id='.$fieldValue;
                $output = '<a href="'.$link.'">'.$obj->{"$suffix".'_name'}.'</a>';
                break;
            default:
                $output = htmlspecialchars($fieldValue, ENT_QUOTES);
        }

        return $output;
    }

    public function showField($fieldName, $fieldValue)
    {
        echo $this->addField($fieldName, $fieldValue);
    }

    public function showAddress($name, $object)
    {
        $countries = w2PgetSysVal('GlobalCountries');

        $output  = '<div style="margin-left: 11em;">';
        $output .= '<a href="http://maps.google.com/maps?q=' . $object->{$name . '_address1'} . '+' . $object->{$name . '_address2'} . '+' . $object->{$name . '_city'} . '+' . $object->{$name . '_state'} . '+' . $object->{$name . '_zip'} . '+' . $object->{$name . '_country'} . '" target="_blank">';
        $output .= '<img src="' . w2PfindImage('googlemaps.gif') . '" class="right" alt="Find It on Google" />';
        $output .= '</a>';
        $output .=  $object->{$name . '_address1'} . (($object->{$name . '_address2'}) ? '<br />' . $object->{$name . '_address2'} : '') . (($object->{$name . '_city'}) ? '<br />' . $object->{$name . '_city'} : '') . (($object->{$name . '_state'}) ? ' ' . $object->{$name . '_state'} : '') . (($object->{$name . '_zip'}) ? ', ' . $object->{$name . '_zip'} : '') . (($object->{$name . '_country'}) ? '<br />' . $countries[$object->{$name . '_country'}] : '');
        $output .= '</div>';

        echo $output;
    }
}