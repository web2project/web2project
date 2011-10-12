<?php /* $Id$ $URL$ */

/**
 *	@package web2project
 *	@subpackage output
 *	@version $Revision$
 *
 *  As of v2.1, this class doesn't do much, it's just a place to collect all
 *    of the email templates from throughout the system. Immediately, this will
 *    make it easier for users to locate and update the email templates.
 *  Sometime in the future, we'll replace these mostly static messages with an
 *    admin interface that allows templates to be edited and potentially even
 *    translated.
 *
 */

class w2p_Output_EmailManager {
    public $from = '';
    public $subject = '';
    public $body = '';

    public function __construct() {
        //do nothing so far..
    }

    public function getCalendarConflictEmail(w2p_Core_CAppUI $AppUI) {
        $body = '';
        $body .= "You have been invited to an event by $AppUI->user_first_name $AppUI->user_last_name\n";
        $body .= "However, either you or another intended invitee has a competing event\n";
        $body .= "$AppUI->user_first_name $AppUI->user_last_name has requested that you reply to this message\n";
        $body .= "and confirm if you can or can not make the requested time.\n\n";

        return $body;
    }

    public function getContactUpdateNotify(w2p_Core_CAppUI $AppUI, CContact $contact) {
        $q = new w2p_Database_Query;
        $q->addTable('companies');
        $q->addQuery('company_id, company_name');
        $q->addWhere('company_id = ' . (int)$contact->contact_company);
        $contact_company = $q->loadHashList();
        $q->clear();

        $body  = "Dear: $contact->contact_title $contact->contact_first_name $contact->contact_last_name,";
        $body .= "\n\nIt was very nice to visit you";
        $body .= ($contact->contact_company) ? " and " . $contact_company[$contact->contact_company] . "." : ".";
        $body .= " Thank you for all the time that you spent with me.";
        $body .= "\n\nI have entered the data from your business card into my contact data base so that we may keep in touch.";
        $body .= "\n\nWe have implemented a system which allows you to view the information that I've recorded and give you the opportunity to correct it or add information as you see fit. Please click on this link to view what I've recorded...";
        $body .= "\n\n" . $AppUI->_('URL') . ":     " . W2P_BASE_URL . "/updatecontact.php?updatekey=$contact->contact_updatekey";
        $body .= "\n\nI assure you that the information will be held in strict confidence and will not be available to anyone other than me. I realize that you may not feel comfortable filling out the entire form so please supply only what you're comfortable with.";
        $body .= "\n\nThank you. I look forward to seeing you again, soon.";
        $body .= "\n\nBest Regards,";
        $body .= "\n\n$AppUI->user_first_name $AppUI->user_last_name";

        return $body;
    }

    public function getFileUpdateNotify(w2p_Core_CAppUI $AppUI, CFile $file) {
        $body  = "\n\nFile " . $file->file_name . ' was ' . $file->_message;
        $body .= ' by ' . $AppUI->user_first_name . ' ' . $AppUI->user_last_name;

        return $body;
    }

    public function getNotifyNewUser($username) {
        $body = "Dear $username,\n\n" .
        $body .= "Congratulations! Your account has been activated by the administrator.\n";
        $body .= "Please use the login information provided earlier.\n\n";
        $body .= "You may login at the following URL: " . W2P_BASE_URL . "\n\n";
        $body .= "If you have any difficulties or questions, please ask the administrator for help.\n";
        $body .= "Assuring you the best of our attention at all time.\n\n";
        $body .= "Our Warmest Regards,\n\n" . "The Support Staff.\n\n";
        $body .= "****PLEASE KEEP THIS EMAIL FOR YOUR RECORDS****";

        return $body;
    }

    public function notifyHR($uusername, $logname, $uaddress, $userid) {
        $body = 'A new user has signed up on ' . w2PgetConfig('company_name');
        $body .= ". Please go through the user details below:\n";
        $body .= 'Name:	' . $uusername . "\n" . 'Username:	' . $logname . "\n";
        $body .= 'Email:	' . $uaddress . "\n\n";
        $body .= 'You may check this account at the following URL: ' . W2P_BASE_URL;
        $body .= '/index.php?m=admin&a=viewuser&user_id=' . $userid . "\n\n";
        $body .= "Thank you very much.\n\n" . 'The ' . w2PgetConfig('company_name');
        $body .= " Taskforce.\n\n" . '****PLEASE KEEP THIS EMAIL FOR YOUR RECORDS****';

        return $body;
    }

    public function notifyNewExternalUser($logname, $logpwd) {
        $body = 'You have signed up for a new account on ' . w2PgetConfig('company_name');
        $body .= ".\n\n" . "Once the administrator approves your request, you will receive an email with confirmation.\n";
        $body .= "Your login information are below for your own record:\n\n";
        $body .= 'Username:	' . $logname . "\n" . 'Password:	' . $logpwd . "\n\n";
        $body .= "You may login at the following URL: " . W2P_BASE_URL;
        $body .= "\n\n" . "Thank you very much.\n\n" . 'The ' . w2PgetConfig('company_name');
        $body .= " Support Staff.\n\n" . '****PLEASE KEEP THIS EMAIL FOR YOUR RECORDS****';

        return $body;
    }

    public function notifyNewUserCredentials($username, $logname, $logpwd) {
        $body = $username . ",\n\n";
        $body .= "An access account has been created for you in our web2Project project management system.\n\n";
        $body .= "You can access it here at " . w2PgetConfig('base_url');
        $body .= "\n\n" . "Your username is: " . $logname . "\n";
        $body .= "Your password is: " . $logpwd . "\n\n";
        $body .= "This account will allow you to see and interact with projects. If you have any questions please contact us.";

        return $body;
    }

}