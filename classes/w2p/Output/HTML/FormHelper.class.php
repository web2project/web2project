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
            case 'company':
                $class  = 'C'.ucfirst($suffix);

                $obj = new $class();
                $obj->load($fieldValue);
                $link = '?m='. w2p_pluralize($suffix) .'&a=view&'.$suffix.'_id='.$fieldValue;
                $output = '<a href="'.$link.'">'.$obj->{"$suffix".'_name'}.'</a>';
                break;
            case 'desc':            // @todo This is a special case because department->dept_notes should be renamed department->dept_description
            case 'note':            // @todo This is a special case because resource->resource_note should be renamed resource->resource_description
            case 'notes':           // @todo This is a special case because contact->contact_notes should be renamed contact->contact_description
            case 'signature':       // @todo This is a special case because user->user_signature should be renamed to something else..?
            case 'description':
                $output  = '<textarea name="' . $fieldName . '" class="'.$suffix.'">' . w2PformSafe($fieldValue) . '</textarea>';
                break;
            case 'birthday':        // @todo This is a special case because contact->contact_birthday should be renamed contact->contact_birth_date
                $myDate = intval($fieldValue) ? new w2p_Utilities_Date($fieldValue) : null;
                $date = $myDate ? $myDate->format('%Y-%m-%d') : '-';
                $output  = '<input type="text" class="text '. $suffix . '" ';
                $output .= 'name="' . $fieldName. '" value="' . w2PformSafe($date) . '" ' .$params .' />';
                break;
            case 'date':
                $date = ($fieldValue) ? new w2p_Utilities_Date($fieldValue) : null;
                unset($pieces[0]);
                $datename = implode('_', $pieces);

                $output = '<input type="hidden" name="'.$fieldName.'" id="'.$fieldName.'" value="' . ($date ? $date->format(FMT_TIMESTAMP_DATE) : '') .'" />';
                $output .= '<input type="text" name="'.$datename.'" id="'.$datename.'" onchange="setDate_new(\'editFrm\', \''.$datename.'\');" value="' . ($date ? $date->format($this->df) : '') . '" class="text" />';
                $output .= '<a href="javascript: void(0);" onclick="return showCalendar(\''.$datename.'\', \'' . $this->df . '\', \'editFrm\', null, true, true)">';
                $output .= '<img src="' . w2PfindImage('calendar.gif') . '" alt="' . $this->AppUI->_('Calendar') . '" />';
                $output .= '</a>';
                break;
            case 'private':
            case 'updateask':       // @todo This is unique to the contacts module
                $output  = '<input type="checkbox" value="1" class="text '. $suffix . '" ';
                $output .= 'name="' . $fieldName. '" ' .$params .' />';
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
                $output  = arraySelect($values, $fieldName, 'size="1" class="text '.$suffix.'"', $fieldValue);
                break;
            case 'url':
                $output  = 'http://<input type="text" class="text '. $suffix . '" ';
                $output .= 'name="' . $fieldName. '" value="' . w2PformSafe($fieldValue) . '" ' .$params .' />';
                $output .= '<a href="javascript: void(0);" onclick="testURL()">[' . $this->AppUI->_('test') . ']</a>';
                break;
            /**
             * This handles the default input text input box. It currently covers these fields:
             *   all names, email, phone1, phone2, url, address1, address2, city, state, zip, fax, title, job
             */
            default:
                $output  = '<input type="text" class="text '. $suffix . '" ';
                $output .= 'name="' . $fieldName. '" value="' . w2PformSafe($fieldValue) . '" ' .$params .' />';
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