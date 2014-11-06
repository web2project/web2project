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
                $field = new Web2project\Fields\DateTime($this->AppUI);
                break;
            case 'birthday':
                $field = new Web2project\Fields\Date($this->AppUI);
                break;
            case 'email':
                $field = new Web2project\Fields\Email();
                break;
            case 'url':
                $field = new Web2project\Fields\Url();
                break;
            case 'owner':
                $obj = new CContact();
                $obj->findContactByUserid($fieldValue);

                $field = new Web2project\Fields\Module();
                $field->setObject($obj, 'user');
                break;
            case 'percent':
                $field = new Web2project\Fields\Percent();
                break;
            case 'company':
            case 'department':
            case 'project':
                $class  = 'C'.ucfirst($suffix);
                $obj = new $class();
                $obj->load($fieldValue);

                $field = new Web2project\Fields\Module();
                $field->setObject($obj, $suffix);
                break;
            default:
                $field = new Web2project\Fields\Text();
        }

        return $field->view($fieldValue);
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
        return $output;
    }
}