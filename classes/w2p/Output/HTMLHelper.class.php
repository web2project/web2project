<?php

/**
 * @package     web2project\output
 * @author      D. Keith Casey, Jr. <caseydk@users.sourceforge.net>
 */

class w2p_Output_HTMLHelper extends w2p_Output_HTML_Base
{
    protected $tableRowData = array();

    /**
     * @deprecated
     * @codeCoverageIgnore
     */
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

    public function renderContactTable($moduleName, array $contactList)
    {
        $module = new w2p_System_Module();
        $fields = $module->loadSettings('contacts', $moduleName.'_view');

        if (0 == count($fields)) {
            $fieldList = array('contact_name', 'contact_email', 'contact_phone', 'dept_name');
            $fieldNames = array('Name', 'Email', 'Phone', 'Department');

            $module->storeSettings('contacts', $moduleName.'_view', $fieldList, $fieldNames);
            $fields = array_combine($fieldList, $fieldNames);
        }

        $listTable = new w2p_Output_ListTable($this->AppUI);

        $output  = $listTable->startTable();
        $output .= $listTable->buildHeader($fields);
        $output .= $listTable->buildRows($contactList);
        $output .= $listTable->endTable();

        return $output;
    }

    public function stageRowData($myArray) {
        $this->tableRowData = $myArray;
    }

    /**
     * createColumn is handy because it can take any input $fieldName and use
     *   its suffix to determine how the field should be displayed.
     *
     * This allows us to treat project_description, task_description,
     *   company_description, or even some_other_crazy_wacky_description in
     *   exactly the same way without additional lines of code or configuration.
     *   If you want to do your own, feel free... but this is probably easier.
     * 
     * Examples: _budget, _date, _name, _owner
     * 
     * This may not work for things like company_type or project_type which are
     *   actually just references to look up tables, ... but should work on
     *   fields like project_company, dept_company because we still have a 
     *   common suffix.
     *
     * @note I'm kind of annoyed about the complexity and sheer number of
     *   paths of this method but overall I think it's laid out reasonably
     *   well. I think the more important part is that I've been able to
     *   encapsulate it all here instead of spreading it all over the modules
     *   and views.
     */
    public function createCell($fieldName, $value, $custom = array()) {
        $additional = '';

        if ('' == $value) {
            return '<td>-</td>';
        }

        $pieces = explode('_', $fieldName);
        $prefix = $pieces[0];
        $suffix = '_'.end($pieces);

        if ($fieldName == 'project_actual_end_date') {
            $suffix='_actual';
        }

        switch ($suffix) {
//BEGIN: object-based linkings
/*
 * TODO: The following cases are likely to change once we have an approach to 
 *   handle module-level objects and their proper mapping/linkings.
*/
            case '_company':
            case '_contact':
            case '_task':
            case '_department':
                $module = substr($suffix, 1);
                $class  = 'C'.ucfirst($module);

                $obj = new $class();
                $obj->load($value);

                $field = new Web2project\Fields\Module();
                $field->setObject($obj, substr($suffix, 1));
                $cell = $field->view($value);
                break;
            case '_folder':
                $obj = new CFile_Folder();
                $obj->load($value);
                $foldername = ($value) ? $obj->file_folder_name : 'Root';
                $image = '<img src="'.w2PfindImage('folder5_small.png', 'files').'" />';
                $link = '?m=files&tab=4&folder=' . (int) $value;
                $cell = '<a href="'.$link.'">' . $image . ' ' . $foldername . '</a>';
                $suffix .= ' _name';
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
                $page   = ($prefix == 'forum' || $prefix == 'message') ? 'viewer' : 'view';
                $link   = '?m='. w2p_pluralize($prefix) .'&a='.$page.'&';
                $link   = ($prefix == 'message') ? '?m=forums&a='.$page . '&' : $link;
                $prefix = ($prefix == 'department') ? 'dept' : $prefix;
                $link  .= $prefix.'_id='.$this->tableRowData[$prefix.'_id'];
                $link  .= ($prefix == 'task_log') ? '&tab=1&task_id='.$this->tableRowData['task_id'] : '';
                $icon   = ($fieldName == 'file_name') ? '<img src="' . 
                    w2PfindImage(getIcon($this->tableRowData['file_type']), 'files') . '" />&nbsp;' : '';
                $cell   = '<a href="'.$link.'">'.$icon.$value.'</a>';
//TODO: task_logs are another oddball..
                $cell = ($prefix == 'task_log') ? str_replace('task_logs', 'tasks', $cell) : $cell;
                break;
            case '_user':
            case '_username':
                $value = $this->tableRowData['user_id'];
            case '_author':
            case '_creator':
            case '_owner':
            case '_updator':
                if ((int) $value) {
                    $obj = new CContact();
                    $obj->findContactByUserid($value);

                    $field = new Web2project\Fields\Module();
                    $field->setObject($obj, 'user');
                    $cell = $field->view($value);
                } else {
                    $field = new Web2project\Fields\Text();
                    $cell = $field->view($value);
                }
                break;
                // The above are all contact/user display names, the below are numbers.
            case '_count':
            case '_hours':
                $field = new Web2project\Fields\Text();
                $cell = $field->view($value);
                break;
            case '_duration':
                $durnTypes = w2PgetSysVal('TaskDurationType');
                $cell = $value . '&nbsp;' . $this->AppUI->_($durnTypes[$this->tableRowData['task_duration_type']]);
                break;
            case '_size':
                $cell = file_size($value);
                break;
            case '_budget':
                $field = new Web2project\Fields\Currency();
                $field->setOptions(w2PgetConfig('currency_symbol'), $this->AppUI->getPref('CURRENCYFORM'));
                $cell = $field->view($value);
                break;
            case '_url':
                $field = new Web2project\Fields\Url();
                $cell = $field->view($value);
                break;
            case '_email':
                $field = new Web2project\Fields\Email();
                $cell = $field->view($value);
                break;
            case '_birthday':
            case '_date':
                $field = new Web2project\Fields\Date($this->AppUI);
                $cell = $field->view($value);
                break;
            case '_actual':
                $end_date = intval($this->tableRowData['project_end_date']) ? new w2p_Utilities_Date($this->tableRowData['project_end_date']) : null;
                $actual_end_date = intval($this->tableRowData['project_actual_end_date']) ? new w2p_Utilities_Date($this->tableRowData['project_actual_end_date']) : null;
                $style = (($actual_end_date < $end_date) && !empty($end_date)) ? 'style="color:red; font-weight:bold"' : '';
                if ($actual_end_date) {
                    $cell = '<a href="?m=tasks&a=view&task_id=' . $this->tableRowData['project_last_task'] . '" ' . $style . '>' . $actual_end_date->format($this->df) . '</a>';
                } else {
                    $cell = '-';
                }
                break;
            case '_created':
            case '_datetime':
            case '_update':
            case '_updated':
                $field = new Web2project\Fields\DateTime($this->AppUI);
                $cell = $field->view($value);
                break;
            case '_description':
                $field = new Web2project\Fields\TextArea();
                $cell = $field->view($value);
                break;
            case '_priority':
                $mod = ($value > 0) ? '+' : '-';
                $image = '<img src="' . w2PfindImage('icons/priority' . $mod . abs($value) . '.gif') . '" width="13" height="16" alt="">';
                $cell = ($value != 0) ? $image : '';
                break;
            case '_identifier':
                $additional = 'style="background-color:#'.$value.'; color:'.bestColor($value).'" ';
                $value = $this->tableRowData['project_percent_complete'];
                // this drops through on purpose
            case '_complete':
            case '_assignment':
            case '_allocated':
            case '_allocation':
                $field = new Web2project\Fields\Percent();
                $cell = $field->view($value);
                break;
            case '_password':
                $value = '('.$this->AppUI->_('hidden').')';
                $field = new Web2project\Fields\Text();
                $cell = $field->view($value);
                break;
            case '_version':
                $value = (int) (100 * $value);
                $cell = number_format($value/100, 2);
                break;
            case '_project':
                $module = substr($suffix, 1);
                $class  = 'C'.ucfirst($module);

                $obj = new $class();
                $obj->load($value);
                $color = $obj->project_color_identifier;
                $link = '?m='. w2p_pluralize($module) .'&a=view&'.$module.'_id='.$value;
                $cell = '<span style="background-color:#'.$color.'; padding: 3px"><a href="'.$link.'" style="color:'.bestColor($color) .'">'.$obj->{"$module".'_name'}.'</a></span>';
                $suffix .= ' _name';
                break;
            case '_assignees':
                $cell = $value;
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
                $value = (isset($custom[$fieldName])) ? $custom[$fieldName][$value] : $value;
                $cell = htmlspecialchars($value, ENT_QUOTES);
        }

        $begin = '<td '.$additional.'class="'.$suffix.'">';
        $end = '</td>';

        return $begin . $cell . $end;
    }
}