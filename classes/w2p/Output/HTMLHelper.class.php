<?php /* $Id$ $URL$ */

/**
 * 	@package web2project
 * 	@subpackage output
 * 	@version $Revision$
 */
class w2p_Output_HTMLHelper
{

    protected $AppUI = null;

    public function __construct(w2p_Core_CAppUI $AppUI)
    {
        $this->AppUI = $AppUI;
        $this->df = $AppUI->getPref('SHDATEFORMAT');
        $this->dtf = $this->df . ' ' . $AppUI->getPref('TIMEFORMAT');
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

    /*
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

        $last_underscore = strrpos($fieldName, '_');
        $shortname = ($last_underscore !== false) ? substr($fieldName, $last_underscore) : $fieldName;

        $additional = '';
        $class = '';

        switch ($shortname) {
			case '_task':
                $task = new CTask();
                $task->load($value);
                $cell = '<a href="?m=tasks&a=view&task_id='.$value.'">'.$task->task_name.'</a>';
                break;



            case '_category':
                $cell = $custom[$fieldName][$value];
                break;
            case '_creator':
			case '_owner':
                $additional = 'nowrap="nowrap"';
                $cell = w2PgetUsernameFromID($value);
                break;
            case '_budget':
                $cell = w2PgetConfig('currency_symbol');
                $cell .= formatCurrency($value, $this->AppUI->getPref('CURRENCYFORM'));
                break;
            case '_url':
                $cell = w2p_url($value);
                break;
            case '_email':
                $cell = w2p_email($value);
                break;
            case '_date':
                $additional = 'nowrap="nowrap"';
                $myDate = intval($value) ? new w2p_Utilities_Date($value) : null;
                $cell = $myDate ? $myDate->format($this->df) : '-';
                break;
            case '_datetime':
                $additional = 'nowrap="nowrap"';
                $myDate = intval($value) ? new w2p_Utilities_Date($this->AppUI->formatTZAwareTime($value, '%Y-%m-%d %T')) : null;
                $cell = $myDate ? $myDate->format($this->dtf) : '-';
                break;
            case '_description':
                $cell = w2p_textarea($value);
                break;
            case '_count':
                $cell = $value;
                break;
            case '_complete':
            case '_assignment':
                $cell = $value.'%';
                break;
            case '_url':
                $cell = w2p_url($value);
                break;
            case '_duration':
                $cell = $value;
                break;
            case '_hours':
            default:
                $additional = 'nowrap="nowrap"';
                $cell = htmlspecialchars($value, ENT_QUOTES);
        }

        $begin = '<td '.$additional.' class="data '.$shortname.'">';
        $end = '</td>';

        return $begin . $cell . $end;
    }

    public function createColumn($fieldName, $value)
    {
        trigger_error("The method createColumn has been deprecated in v3.0 and will be removed by v4.0. Please use createCell instead.", E_USER_NOTICE);

        return $this->createCell($fieldName, $value[$fieldName]);
    }

    /*
     * 
     * @deprecated 
     */

    public static function renderColumn(w2p_Core_CAppUI $AppUI, $fieldName, $row)
    {

        trigger_error("The static method renderColumn has been deprecated and will be removed by v4.0.", E_USER_NOTICE);

        $last_underscore = strrpos($fieldName, '_');
        $shortname = ($last_underscore !== false) ? substr($fieldName, $last_underscore) : $fieldName;

        switch ($shortname) {
            case '_creator':
            case '_owner':
                $s .= '<td nowrap="nowrap">';
                $s .= w2PgetUsernameFromID($row[$fieldName]);
                $s .= '</td>';
                break;
            case '_budget':
                $s .= '<td>';
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