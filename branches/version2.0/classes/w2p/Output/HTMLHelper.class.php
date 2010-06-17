<?php /* $Id$ $URL$ */

/**
 *	@package web2project
 *	@subpackage output
 *	@version $Revision$
 */

class w2p_Output_HTMLHelper {

    public static function renderContactList(CAppUI $AppUI, $contactList) {

        $output  = '<table cellspacing="1" cellpadding="2" border="0" width="100%" class="tbl">';
        $output .= '<tr><th>'.$AppUI->_('Name').'</th><th>'.$AppUI->_('Email').'</th>';
        $output .= '<th>'.$AppUI->_('Phone').'</th><th>'.$AppUI->_('Department').'</th></tr>';
        foreach ($contactList as $contact_id => $contact_data) {
            $contact = new CContact();
            $contact->contact_id = $contact_id;
            $info = $contact->getContactMethods(array('email_primary', 'phone_primary'));

            $output .= '<tr>';
            $output .= '<td class="hilite"><a href="index.php?m=contacts&a=addedit&contact_id=' . $contact_id . '">' . $contact_data['contact_order_by'] . '</a></td>';
            $output .= '<td class="hilite"><a href="mailto: ' . $info['email_primary'] . '">' . $info['email_primary'] . '</a></td>';
            $output .= '<td class="hilite">' . $info['phone_primary'] . '</td>';
            $output .= '<td class="hilite">' . $contact_data['dept_name'] . '</td>';
            $output .= '</tr>';
        }
        $output .= '</table>';

        return $output;
    }
}