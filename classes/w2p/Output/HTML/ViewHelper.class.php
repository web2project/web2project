<?php
/**
 * Class w2p_Output_HTML_FormHelper
 *
 * @package web2project\output\html
 * @author  D. Keith Casey, Jr. <contrib@caseysoftware.com>
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
                $output = '<a href="'.$link.'">'.$this->escape($obj->contact_display_name).'</a>';
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
            case 'task':
                if (!$fieldValue) {
                    return '-';
                }
                $class  = 'C'.ucfirst($suffix);
                $obj = new $class();
                $obj->load($fieldValue);
                $field = $suffix.'_name';
                $name = ($fieldValue) ? $obj->$field : '-';
                $link = '?m='. w2p_pluralize($suffix) .'&a=view&'.$suffix.'_id='.$fieldValue;
                $output = '<a href="'.$link.'">' . $this->escape($name) . '</a>';
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

        $part = function ($field) use ($object, $name) {
            return htmlspecialchars((string) $object->{$name . '_' . $field}, ENT_QUOTES, 'UTF-8');
        };
        $country = $object->{$name . '_country'};
        $countryName = $country ? ($countries[$country] ?? $country) : '';

        $output  = '<span class="d-inline-block align-top w2p-address">';
        $output .=  $part('address1') . (($object->{$name . '_address2'}) ? '<br />' . $part('address2') : '') . (($object->{$name . '_city'}) ? '<br />' . $part('city') : '') . (($object->{$name . '_state'}) ? ' ' . $part('state') : '') . (($object->{$name . '_zip'}) ? ', ' . $part('zip') : '') . (($country) ? '<br />' . htmlspecialchars((string) $countryName, ENT_QUOTES, 'UTF-8') : '');
        $output .= '</span>';
        $query = array();
        foreach (array('address1', 'address2', 'city', 'state', 'zip', 'country') as $field) {
            $query[] = (string) $object->{$name . '_' . $field};
        }
        $output .= '<a href="http://maps.google.com/maps?q=' . htmlspecialchars(urlencode(implode(' ', $query)), ENT_QUOTES, 'UTF-8') . '" target="_blank">';
        $output .= '<img src="' . w2PfindImage('googlemaps.gif') . '" alt="Find It on Google" />';
        $output .= '</a>';

        echo $output;
    }
}