<?php /* $Id$ $URL$ */

/**
 *	@package web2project
 *	@subpackage output
 *	@version $Revision$
 */

class w2p_Output_HTMLHelper {

    public static function renderContactList(CAppUI $AppUI, array $contactList) {

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

	public static function renderColumn(CAppUI $AppUI, $fieldName, $row) {

		switch ($fieldName) {
			case 'project_creator':
			case 'project_owner':
				$s .= '<td nowrap="nowrap">';
				$s .= w2PgetUsernameFromID($row[$fieldName]);
				$s .= '</td>';
				break;
			case 'project_target_budget':
			case 'project_actual_budget':
				$s .= '<td>';
				$s .= $w2Pconfig['currency_symbol'];
				$s .= formatCurrency($row[$fieldName], $AppUI->getPref('CURRENCYFORM'));
				$s .= '</td>';
				break;
			case 'project_url':
			case 'project_demo_url':
				$s .= '<td>';
				$s .= w2p_url($row[$fieldName]);
				$s .= '</td>';
				break;
			case 'project_start_date':
			case 'project_end_date':
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