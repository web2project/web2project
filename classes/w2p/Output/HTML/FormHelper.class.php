<?php
/**
 * Class w2p_Output_HTML_FormHelper
 *
 * @package     web2project\output\html
 * @author      D. Keith Casey, Jr. <contrib@caseysoftware.com>
 */
class w2p_Output_HTML_FormHelper extends w2p_Output_HTML_Base
{
    public function addField($fieldName, $fieldValue, $options = array(), $values = array())
    {
        $pieces = explode('_', $fieldName);
        $suffix = end($pieces);

        $params = '';
        foreach ($options as $key => $value) {
            $params .= $key . '="' . $value .'" ';
        }

        switch ($suffix) {
            case 'desc':            // @todo This is a special case because department->dept_notes should be renamed department->dept_description
            case 'note':            // @todo This is a special case because resource->resource_note should be renamed resource->resource_description
            case 'notes':           // @todo This is a special case because contact->contact_notes should be renamed contact->contact_description
            case 'signature':       // @todo This is a special case because user->user_signature should be renamed to something else..?
            case 'description':
                $field = new Web2project\Fields\TextArea();
                $output = $field->edit($fieldName, $fieldValue, "class=\"text $suffix\"");
                break;
            case 'date':
                $field = new Web2project\Fields\Date();
                $field->setDateInformation($this->AppUI, $pieces, $this->df);
                $output = $field->edit($fieldName, $fieldValue, "class=\"text $suffix\"");
                break;
            case 'private':
            case 'updateask':       // @todo This is unique to the contacts module
                $field = new Web2project\Fields\Checkbox();
                $output = $field->edit($fieldName, $fieldValue, "class=\"text $suffix\"");
                break;
            case 'parent':          // @note This drops through on purpose
                $suffix = 'department';
            case 'allocation':
            case 'category':
            case 'country':
            case 'owner':
            case 'priority':
            case 'project':
            case 'status':
            case 'type':
                $field = new Web2project\Fields\Select();
                $field->setOptions($values);
                $output = $field->edit($fieldName, $fieldValue, "class=\"text $suffix\"");
                break;
            case 'url':
                $field = new Web2project\Fields\Url();
                $output = $field->edit($fieldName, $fieldValue, "class=\"text $suffix\"");
                break;
            /**
             * This handles the default input text input box. It currently covers these fields:
             *   all names, email, phone1, phone2, url, address1, address2, city, state, zip, fax, title, job
             */
            default:
                $field = new Web2project\Fields\Text();
                $output = $field->edit($fieldName, $fieldValue, "class=\"text $suffix\"");
        }

        return $output;
    }

    public function showField($fieldName, $fieldValue, $options = array(), $values = array())
    {
        echo $this->addField($fieldName, $fieldValue, $options, $values);
    }

    public function addCancelButton()
    {
        $output = '<input type="button" value="' . $this->AppUI->_('back') . '" class="cancel button btn btn-danger" onclick="javascript:history.back(-1);" />';

        return $output;
    }
    public function showCancelButton()
    {
        echo $this->addCancelButton();
    }
    public function addSaveButton()
    {
        $output = '<input type="button" value="' . $this->AppUI->_('save') . '" class="save button btn btn-primary" onclick="submitIt()" />';

        return $output;
    }
    public function showSaveButton()
    {
        echo $this->addSaveButton();
    }

    public function addNonce()
    {
        $nonce = md5(time() . implode($this->AppUI->user_prefs) );
        $output = '<input type="hidden" name="__nonce" value="'. $nonce . '" />';
        $this->AppUI->__nonce = $nonce;

        return $output;
    }
}