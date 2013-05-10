<?php

/**
 * @package     web2project\output
 * @author      D. Keith Casey, Jr. <caseydk@users.sourceforge.net>
 */

class w2p_Output_HTMLHelper
{

    protected $_AppUI = null;
    protected $tableRowData = array();

    public function __construct(w2p_Core_CAppUI $AppUI)
    {
        $this->_AppUI = $AppUI;
        $this->df     = $AppUI->getPref('SHDATEFORMAT');
        $this->dtf    = $this->df . ' ' . $AppUI->getPref('TIMEFORMAT');
    }

    public static function renderContactList(w2p_Core_CAppUI $AppUI, array $contactList)
    {

        $output = '<table cellspacing="1" cellpadding="2" border="0" width="100%" class="tbl">';
        $output .= '<tr><th>' . $AppUI->_('Name') . '</th><th>' . $AppUI->_('Email') . '</th>';
        $output .= '<th>' . $AppUI->_('Phone') . '</th><th>' . $AppUI->_('Department') . '</th></tr>';
        foreach ($contactList as $contact_id => $contact_data) {
            $contact = new CContact();
            $contact->contact_id = $contact_id;

            $output .= '<tr>';
            $output .= '<td class="hilite"><a href="index.php?m=contacts&amp;a=addedit&amp;contact_id=' . $contact_id . '">' . $contact_data['contact_order_by'] . '</a></td>';
            $output .= '<td class="hilite">' . w2p_email($contact_data['contact_email']) . '</td>';
            $output .= '<td class="hilite">' . $contact_data['contact_phone'] . '</td>';
            $output .= '<td class="hilite">' . $contact_data['dept_name'] . '</td>';
            $output .= '</tr>';
        }
        $output .= '</table>';

        return $output;
    }

    public function renderContactTable($moduleName, array $contactList) {

        $fieldList = array();
        $fieldNames = array();

        $module = new w2p_Core_Module();
        $fields = $module->loadSettings('contacts', $moduleName.'_view');

        if (count($fields) > 0) {
            $fieldList = array_keys($fields);
            $fieldNames = array_values($fields);
        } else {
            // TODO: This is only in place to provide an pre-upgrade-safe 
            //   state for versions earlier than v3.0
            //   At some point at/after v4.0, this should be deprecated
            $fieldList = array('contact_name', 'contact_email', 'contact_phone', 'dept_name');
            $fieldNames = array('Name', 'Email', 'Phone', 'Department');

            $module->storeSettings('contacts', $moduleName.'_view', $fieldList, $fieldNames);
        }
        $output  = '<table cellspacing="1" cellpadding="2" border="0" width="100%" class="tbl">';
        $output .= '<tr>';
        foreach ($fieldNames as $index => $notUsed) {
            $output .= '<th nowrap="nowrap">';
//TODO: Should we support sorting here?
            $output .= $this->_AppUI->_($fieldNames[$index]);
            $output .= '</th>';
        }
        $output .= '</tr>';
        
        foreach ($contactList as $row) {
            $output .= '<tr>';
            $this->stageRowData($row);
            foreach ($fieldList as $index => $notUsed) {
                $output .= $this->createCell($fieldList[$index], $row[$fieldList[$index]]);
            }
            $output .= '</tr>';
        }
        $output .= '</table>';

        return $output;
    }
    /*
     * I really hate this option, but I'm not sure of a better way to get the 
     *   _name case of createCell's switch statement. I'm option to suggestions.
     *          ~ caseydk 09 Feb 2012
     */
    public function stageRowData($myArray) {
        $this->tableRowData = $myArray;
    }
    /**
     * createColumn is handy because it can take any input $fieldName and use
     *   suffix to determine how the field should be displayed.
     *
     * This allows us to treat project_description, task_description,
     *   company_description, or even some_other_crazy_waky_description in
     *   exactly the same way without additional lines of code or configuration.
     *   If you want to do your own, feel free... but this is probably easier.
     *   differently, why not use everything after the last underscore (or suffix)
     *   to determine the display formatting? Basically the fields become self-descriptive.
     * 
     * Examples: _budget, _date, _name, _owner
     * 
     * This may not work for things like company_type or project_type which are
     *   actually just references to look up tables, ... but should work on
     *   fields like project_company, dept_company because we still have a 
     *   common suffix.
     */
    public function createCell($fieldName, $value, $custom = array()) {
        $additional = '';

        $last_underscore = strrpos($fieldName, '_');
        $prefix = ($last_underscore !== false) ? substr($fieldName, 0, $last_underscore) : $fieldName;
        $suffix = ($last_underscore !== false) ? substr($fieldName, $last_underscore) : $fieldName;

        switch ($suffix) {
//BEGIN: object-based linkings
/*
 * TODO: The following cases are likely to change once we have an approach to 
 *   handle module-level objects and their proper mapping/linkings.
*/
            case '_company':
            case '_contact':
            case '_project':
            case '_task':
                $module = substr($suffix, 1);
                $class  = 'C'.ucfirst($module);

                $obj = new $class();
                $obj->load($value);
                $link = '?m='. w2p_pluralize($module) .'&a=view&'.$module.'_id='.$value;
                $cell = '<a href="'.$link.'">'.$obj->{"$module".'_name'}.'</a>';
                $suffix .= ' _name';
                break;
            case '_department':
                $obj = new CDepartment();
                $obj->load($value);
                $mod = substr($suffix, 1);
                $link = '?m='. w2p_pluralize($mod) .'&a=view&dept_id='.$value;
                $cell = '<a href="'.$link.'">'.$obj->dept_name.'</a>';
                $suffix .= ' _name';
                break;
            case '_folder':
                $obj = new CFile_Folder();
                $obj->load($value);
                $foldername = ($value) ? $obj->file_folder_name : 'Root';
                $image = '<img src="'.w2PfindImage('folder5_small.png', 'files').'" />';
                $link = '?m=files&tab=4&folder=' . $value;
                $cell = '<a href="'.$link.'">' . $image . ' ' . $foldername . '</a>';
                $suffix .= ' _name';
                break;
            case '_user':
            case '_username':
                $obj = new CContact();
                $obj->findContactByUserid($this->tableRowData['user_id']);
                $mod = substr($suffix, 1);
                $link = '?m=admin&a=viewuser&user_id='.$this->tableRowData['user_id'];
                $cell = '<a href="'.$link.'">'.$obj->contact_display_name.'</a>';
                break;
//END: object-based linkings

/*
 * TODO: These two prefix adjustments are an ugly hack because our departments 
 *   table doesn't follow the same convention as every other table we have. 
 *   This needs to be fixed in v4.0 - caseydk 13 Feb 2012
 *
 * TODO: And unfortunately, the forums module is screwy using 'viewer' instead 
 *   of our standard 'view' for the page. ~ caseydk 16 Feb 2012
*/
            case '_name':
                $prefix = ($prefix == 'project_short')  ? 'project' : $prefix;
                $prefix = ($prefix == 'dept')  ? 'department' : $prefix;
                $prefix = ($prefix == 'message')  ? 'forum' : $prefix;
                $page   = ($prefix == 'forum') ? 'viewer&message_id='.$this->tableRowData['message_id'] : 'view';
                $link   = ($prefix == 'file') ? 'fileviewer.php?' : '?m='. w2p_pluralize($prefix) .'&a='.$page.'&';
                $link   = ($prefix == 'event') ? '?m=calendar&a='.$page.'&' : $link;
                $link   = ($prefix == 'user') ? '?m=admin&a=viewuser&' : $link;
                $prefix = ($prefix == 'department') ? 'dept' : $prefix;
                $link  .= $prefix.'_id='.$this->tableRowData[$prefix.'_id'];
                $link  .= ($prefix == 'task_log') ? '&tab=1&task_id='.$this->tableRowData['task_id'] : '';
                $icon   = ($fieldName == 'file_name') ? '<img src="' . 
                    w2PfindImage(getIcon($this->tableRowData['file_type']), 'files') . '" />&nbsp;' : '';
                $cell   = '<a href="'.$link.'">'.$icon.$value.'</a>';
//TODO: task_logs are another oddball..
                $cell = ($prefix == 'task_log') ? str_replace('task_logs', 'tasks', $cell) : $cell;
                break;
            case '_author':
            case '_creator':
			case '_owner':
            case '_updator':
                if ((int) $value) {
                    $obj = new CContact();
                    $obj->findContactByUserid($value);
                    $mod = substr($suffix, 1);
                    $suffix .= ' nowrap';
                    $link = '?m=admin&a=viewuser&user_id='.$this->tableRowData['user_id'];
                    $cell = '<a href="'.$link.'">'.$obj->contact_display_name.'</a>';
                } else {
                    $cell = $value;
                }
                break;
                // The above are all contact/user display names, the below are numbers.
            case '_count':
            case '_duration':
            case '_hours':
                $cell = $value;
                break;
            case '_size':
                $cell = file_size($value);
                break;
			case '_budget':
				$cell = w2PgetConfig('currency_symbol');
				$cell .= formatCurrency($value, $this->_AppUI->getPref('CURRENCYFORM'));
				break;
			case '_url':
				$cell = w2p_url($value);
				break;
            case '_email':
                $cell = w2p_email($value);
                break;
            case '_birthday':
			case '_date':
                $myDate = intval($value) ? new w2p_Utilities_Date($value) : null;
				$cell = $myDate ? $myDate->format($this->df) : '-';
				break;
			case '_created':
            case '_datetime':
            case '_update':
            case '_updated':
                $myDate = intval($value) ? new w2p_Utilities_Date($this->_AppUI->formatTZAwareTime($value, '%Y-%m-%d %T')) : null;
				$cell = $myDate ? $myDate->format($this->dtf) : '-';
				break;
            case '_description':
                $cell = w2p_textarea($value);
                break;
            case '_priority':
                $mod = ($value > 0) ? '+' : '-';
                $image = '<img src="' . w2PfindImage('icons/priority' . $mod . abs($value) . '.gif') . '" width="13" height="16" alt="">';
                $cell = ($value != 0) ? $image : '';
                break;
            case '_complete':
            case '_assignment':
            case '_allocated':
            case '_allocation':
                $cell = round($value).'%';
                break;
            case '_password':
                $cell = '('.$this->_AppUI->_('hidden').')';
                break;
            case '_version':
                $value = (int) (100 * $value);
                $cell = number_format($value/100, 2);
                break;
            case '_identifier':
                $additional = 'style="background-color:#'.$value.'; color:'.bestColor($value).'" ';
                $cell = $this->tableRowData['project_percent_complete'].'%';
                break;
            case '_problem':
                if ($value) {
                    $cell  = '<a href="?m=tasks&a=index&f=all&project_id=' . $this->tableRowData['project_id'] . '">';
                    $cell .= w2PshowImage('icons/dialog-warning5.png', 16, 16, 'Problem', 'Problem');
                    $cell .= '</a>';
                } else {
                    $cell = '-';
                }
                break;
			default:
//TODO: use this when we get a chance - http://www.w3schools.com/cssref/pr_text_white-space.asp ?
                $value = (isset($custom[$fieldName])) ? $custom[$fieldName][$value] : $value;
				$cell = htmlspecialchars($value, ENT_QUOTES);
		}

        $begin = '<td '.$additional.'class="'.$suffix.'">';
        $end = '</td>';

        return $begin . $cell . $end;
    }

    /**
     * @deprecated
     */
    public function createColumn($fieldName, $value) {
        trigger_error("The method createColumn has been deprecated in v3.0 and will be removed by v4.0. Please use createCell instead.", E_USER_NOTICE );

        return $this->createCell($fieldName, $value[$fieldName]);
    }

    /**
     * @deprecated
     */
    public static function renderColumn(w2p_Core_CAppUI $AppUI, $fieldName, $row)
    {
        trigger_error("The static method renderColumn has been deprecated and will be removed by v4.0.", E_USER_NOTICE);

        $last_underscore = strrpos($fieldName, '_');
        $suffix = ($last_underscore !== false) ? substr($fieldName, $last_underscore) : $fieldName;

        switch ($suffix) {
			case '_creator':
			case '_owner':
				$s .= '<td nowrap="nowrap">';
				$s .= w2PgetUsernameFromID($row[$fieldName]);
				$s .= '</td>';
				break;
			case '_budget':
				$s .= '<td>';
                global $w2Pconfig;
				$s .= $w2Pconfig['currency_symbol'];
				$s .= formatCurrency($row[$fieldName], $AppUI->getPref('CURRENCYFORM'));
				$s .= '</td>';
				break;
			case '_url':
				$s .= '<td>';
				$s .= w2p_url($row[$fieldName]);
				$s .= '</td>';
				break;
			case '_date':
				$df = $AppUI->getPref('SHDATEFORMAT');
				$myDate = intval($row[$fieldName]) ? new w2p_Utilities_Date($row[$fieldName]) : null;
				$s .= '<td nowrap="nowrap" class="center">' . ($myDate ? $myDate->format($df) : '-') . '</td>';
				break;
			default:
				$s .= '<td nowrap="nowrap" class="center">';
				$s .= htmlspecialchars($row[$fieldName], ENT_QUOTES);
				$s .= '</td>';
		}

		return $s;
	}
}