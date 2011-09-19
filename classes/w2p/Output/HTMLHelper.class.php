<?php /* $Id$ $URL$ */

/**
 *	@package web2project
 *	@subpackage output
 *	@version $Revision$
 */

class w2p_Output_HTMLHelper {

    protected $AppUI = null;

    public function __construct(w2p_Core_CAppUI $AppUI) {
        $this->AppUI = $AppUI;
        $this->df    = $AppUI->getPref('SHDATEFORMAT');
    }

    public static function renderContactList(w2p_Core_CAppUI $AppUI, array $contactList) {

        $output  = '<table cellspacing="1" cellpadding="2" border="0" width="100%" class="tbl">';
        $output .= '<tr><th>'.$AppUI->_('Name').'</th><th>'.$AppUI->_('Email').'</th>';
        $output .= '<th>'.$AppUI->_('Phone').'</th><th>'.$AppUI->_('Department').'</th></tr>';
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
	public function createColumn($fieldName, $row) {

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
				$s .= w2PgetConfig('currency_symbol');
				$s .= formatCurrency($row[$fieldName], $this->AppUI->getPref('CURRENCYFORM'));
				$s .= '</td>';
				break;
			case '_url':
				$s .= '<td>';
				$s .= w2p_url($row[$fieldName]);
				$s .= '</td>';
				break;
            case '_email':
                $s .= '<td>';
                $s .= w2p_email($row[$fieldName]);
                $s .= '</td>';
                break;
			case '_date':
				$myDate = intval($row[$fieldName]) ? new w2p_Utilities_Date($row[$fieldName]) : null;
				$s .= '<td nowrap="nowrap" class="center">' . ($myDate ? $myDate->format($this->df) : '-') . '</td>';
				break;
			default:
				$s .= '<td nowrap="nowrap" class="center">';
				$s .= htmlspecialchars($row[$fieldName], ENT_QUOTES);
				$s .= '</td>';
		}

		return $s;
	}

    /*
     * 
     * @deprecated 
     */
	public static function renderColumn(w2p_Core_CAppUI $AppUI, $fieldName, $row) {

        trigger_error("The static method renderColumn has been deprecated and will be removed by v4.0.", E_USER_NOTICE );

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