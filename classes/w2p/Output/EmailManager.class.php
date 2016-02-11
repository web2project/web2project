<?php

/**
 * As of v2.1, this class doesn't do much, it's just a place to collect all of
 * the email templates from throughout the system. Immediately, this will make
 * it easier for users to locate and update the email templates.
 *
 * Sometime in the future, we'll replace these mostly static messages with an
 * admin interface that allows templates to be edited and potentially even
 * translated.
 *
 * @package     web2project\output
 * @author      D. Keith Casey, Jr. <caseydk@users.sourceforge.net>
 */

class w2p_Output_EmailManager
{

    public $from = '';
    public $subject = '';
    public $body = '';

    protected $_AppUI = null;
    protected $templater = null;

    public function __construct(w2p_Core_CAppUI $AppUI = null)
    {
        if (is_null($AppUI)) {
            trigger_error('The w2p_Output_EmailManager constructor should receive $AppUI (an w2p_Core_CAppUI object) for proper usage.', E_USER_NOTICE);
        }

        $this->templater = new w2p_Output_Email_Template();

        $this->_AppUI = $AppUI;
    }

    public function getEventNotify(CEvent $event, $clash, $users)
    {
        $body = $this->_AppUI->_('Event') . ":\t" . $event->event_name . "\n";
        $body .= $this->_AppUI->_('URL') . ":\t" . W2P_BASE_URL . "/index.php?m=events&a=view&event_id=" . $event->event_id . "\n";

        $date_format = $this->_AppUI->getPref('SHDATEFORMAT');
        $time_format = $this->_AppUI->getPref('TIMEFORMAT');
        $fmt = $date_format . ' ' . $time_format;

//TODO: customize these date formats based on the *receivers'* timezone setting
        $start_date = new w2p_Utilities_Date($event->event_start_date);
        $end_date = new w2p_Utilities_Date($event->event_end_date);
        $body .= $this->_AppUI->_('Starts') . ":\t" . $start_date->format($fmt) . " GMT/UTC\n";
        $body .= $this->_AppUI->_('Ends') . ":\t" . $end_date->format($fmt) . " GMT/UTC\n";

        // Find the project name.
        if ($event->event_project) {
            $project = new CProject();
            $project->load($event->event_project);
            $body .= $this->_AppUI->_('Project') . ":\t" . $project->project_name . "\n";
        }

        $types = w2PgetSysVal('EventType');

        $body .= $this->_AppUI->_('Type') . ":\t" . $this->_AppUI->_($types[$event->event_type]) . "\n";
        $body .= $this->_AppUI->_('Attendees') . ":\t";

        $body_attend = '';
        foreach ($users as $user) {
            $body_attend .= ((($body_attend) ? ', ' : '') . $user['contact_name']);
        }

        $body .= $body_attend . "\n\n" . $event->event_description . "\n";

        return $body;
    }

    /**
     * @deprecated
     */
    public function getCalendarConflictEmail(w2p_Core_CAppUI $AppUI = null)
    {
        trigger_error("getCalendarConflictEmail has been deprecated in v3.0 and will be removed by v4.0. Please use getEventNotify() instead.", E_USER_NOTICE);

        $this->_AppUI = (!is_null($AppUI)) ? $AppUI : $this->_AppUI;

        $body  = "You have been invited to an event by user_display_name\n";
        $body .= "However, either you or another intended invitee has a competing event\n";
        $body .= "user_display_name has requested that you reply to this message\n";
        $body .= "and confirm if you can or can not make the requested time.\n\n";

        return $this->templater->render($body, $this->_AppUI);
    }

    public function getContactUpdateNotify(w2p_Core_CAppUI $AppUI = null, CContact $contact)
    {
        $this->_AppUI = (!is_null($AppUI)) ? $AppUI : $this->_AppUI;

        $company = new CCompany();
        $company->load($contact->contact_company);
        $contact->company_name = $company->company_name;
        $contact->user_display_name = $this->_AppUI->user_display_name;

        $body  = "Dear contact_title contact_display_name,";
        $body .= "\n\nIt was very nice to visit you";
        $body .= ($contact->contact_company) ? " and company_name." : ".";
        $body .= " Thank you for all the time that you spent with me.";
        $body .= "\n\nI have entered the data from your business card into my contact database so that we may keep in touch.";
        $body .= " We have implemented a system which allows you to view the information that I've recorded and give you the opportunity to correct it or add information as you see fit. Please click on this link to view what I've recorded:";
        $body .= "\n\n" . W2P_BASE_URL . "/updatecontact.php?updatekey=contact_updatekey";
        $body .= "\n\nI assure you that the information will be held in strict confidence and will not be available to anyone other than me. I realize that you may not feel comfortable filling out the entire form so please supply only what you're comfortable with.";
        $body .= "\n\nThank you. I look forward to seeing you again, soon.";
        $body .= "\n\nBest Regards,\nuser_display_name";

        return $this->templater->render($body, $contact);
    }

    public function getFileUpdateNotify(w2p_Core_CAppUI $AppUI = null, CFile $file)
    {
        $this->_AppUI = (!is_null($AppUI)) ? $AppUI : $this->_AppUI;

        $file->user_display_name = $this->_AppUI->user_display_name;
        $body = "\n\nFile file_name was _message by user_display_name";

        return $this->templater->render($body, $file);
    }

    public function getForumWatchEmail(CForum_Message $message, $forum_name, $message_from)
    {
        $message->forum_name = $forum_name;
        $message->message_from = $message_from;

        $body = $this->_AppUI->_('forumEmailBody', UI_OUTPUT_RAW);
        $body .= "\n\n" . $this->_AppUI->_('Forum', UI_OUTPUT_RAW) . ': forum_name';
        $body .= "\n" . $this->_AppUI->_('Subject', UI_OUTPUT_RAW) . ': message_title';
        $body .= "\n" . $this->_AppUI->_('Message From', UI_OUTPUT_RAW) . ': message_from';
        $body .= "\n\n" . W2P_BASE_URL . '/index.php?m=forums&a=viewer&forum_id=message_forum';
        $body .= "\n\nmessage_body";

        return $this->templater->render($body, $message);
    }

    public function getFileNotify(CFile $file)
    {
        $body = $this->_AppUI->_('Project') . ': ' . $file->_project->project_name;
        $body .= "\n" . $this->_AppUI->_('URL') . ':     ' . W2P_BASE_URL . '/index.php?m=projects&a=view&project_id=' . $file->_project->project_id;

        if (intval($file->_task->task_id) != 0) {
            $body .= "\n\n" . $this->_AppUI->_('Task') . ':    ' . $file->_task->task_name;
            $body .= "\n" . $this->_AppUI->_('URL') . ':     ' . W2P_BASE_URL . '/index.php?m=tasks&a=view&task_id=' . $file->_task->task_id;
            $body .= "\n" . $this->_AppUI->_('Description') . ':' . "\n" . $file->_task->task_description;
        }
        $body .= "\n\nFile " . $file->file_name . ' was ' . $file->_message . ' by ' . $this->_AppUI->user_display_name;
        if ($this->_message != 'deleted') {
            $body .= "\n" . $this->_AppUI->_('URL') . ':     ' . W2P_BASE_URL . '/fileviewer.php?file_id=' . $file->file_id;
            $body .= "\n\n" . $this->_AppUI->_('Description') . ':' . "\n" . $file->file_description;
        }

        return $body;
    }

    public function getFileNotifyContacts(CFile $file)
    {
        return $this->getFileNotify($file);
    }

    public function getTaskNotify(CTask $task, $user, $projname)
    {
        $body = $this->_AppUI->_('Project', UI_OUTPUT_RAW) . ":\t" . $projname . "\n";
        $body .= $this->_AppUI->_('Task', UI_OUTPUT_RAW) . ":\t\t" . $task->task_name . "\n";
        $body .= $this->_AppUI->_('Priority', UI_OUTPUT_RAW) . ":\t" . $task->task_priority . "\n";
        $body .= $this->_AppUI->_('Progress', UI_OUTPUT_RAW) . ":\t" . $task->task_percent_complete . "%\n";
        $tmp_tz = $this->_AppUI->getPref('TIMEZONE');
        $user_prefs = $this->_AppUI->loadPrefs($user['assignee_id'], true);
        $this->_AppUI->user_prefs['TIMEZONE'] = $user_prefs['TIMEZONE'];

        $start_date = new w2p_Utilities_Date($this->_AppUI->formatTZAwareTime($task->task_start_date, '%Y-%m-%d %T'));
        $fmt_start_date = $start_date->format($user_prefs['DISPLAYFORMAT']);
        $end_date = new w2p_Utilities_Date($this->_AppUI->formatTZAwareTime($task->task_end_date, '%Y-%m-%d %T'));
        $fmt_end_date = $end_date->format($user_prefs['DISPLAYFORMAT']);

        $timezoneObj = new Date_TimeZone($user_prefs['TIMEZONE']);
        $tzString = $timezoneObj->getShortName();
        $this->_AppUI->user_prefs['TIMEZONE'] = $tmp_tz;

        // Format dates using preferences but add T as Timezone abbreviation
        $body .= $this->_AppUI->_('Start Date') . ":\t" . $fmt_start_date . " $tzString\n";
        $body .= $this->_AppUI->_('Finish Date') . ":\t" . $fmt_end_date . " $tzString\n";

        $body .= $this->_AppUI->_('URL', UI_OUTPUT_RAW) . ":\t\t" . W2P_BASE_URL . '/index.php?m=tasks&a=view&task_id=' . $task->task_id . "\n\n";
        $body .= $this->_AppUI->_('Description', UI_OUTPUT_RAW) . ': ' . "\n" . $task->task_description;
        if ($user['creator_email']) {
            $body .= ("\n\n" . $this->_AppUI->_('Creator', UI_OUTPUT_RAW) . ':' . "\n" . $user['creator_name'] . ', ' . $user['creator_email']);
        }
        $body .= ("\n\n" . $this->_AppUI->_('Owner', UI_OUTPUT_RAW) . ':' . "\n" . $user['owner_name'] . ', ' . $user['owner_email']);
        if (isset($comment) && $comment != '') {
            $body .= "\n\n" . $comment;
        }

        return $body;
    }

    public function getTaskNotifyOwner(CTask $task)
    {
        $project = new CProject();
        $project->load($task->task_project);
        $projname = $project->project_name;

        $body = $this->_AppUI->_('Project', UI_OUTPUT_RAW) . ':     ' . $projname . "\n";
        $body .= $this->_AppUI->_('Task', UI_OUTPUT_RAW) . ':         ' . $task->task_name . "\n";
        $body .= $this->_AppUI->_('URL', UI_OUTPUT_RAW) . ':         ' . W2P_BASE_URL . '/index.php?m=tasks&a=view&task_id=' . $task->task_id . "\n\n";
        $body .= $this->_AppUI->_('Task Description', UI_OUTPUT_RAW) . ":\n" . $task->task_description . "\n";
        $body .= $this->_AppUI->_('Creator', UI_OUTPUT_RAW) . ': ' . $this->_AppUI->user_display_name . "\n\n";
        $body .= $this->_AppUI->_('Progress', UI_OUTPUT_RAW) . ': ' . $task->task_percent_complete . '%' . "\n\n";
//TODO: why is POST used here? Poor form - dkc 13 Nov 2011
        $body .= $this->_AppUI->_('Summary', UI_OUTPUT_RAW) . ': ' . "\n\n";
        $body .= w2PgetParam($_POST, 'task_log_description');

        return $body;
    }

    public function getTaskRemind(CTask $task, $msg, $project_name, $contacts)
    {
        if(isnull($project_name) || $project_name=="")
        {
            $project = new CProject();
            $project_name = $project->load($task->task_project)->project_name;
        }
        $body = $this->_AppUI->_('Task Due', UI_OUTPUT_RAW) . ': ' . $msg . "\n";
        $body .= $this->_AppUI->_('Project', UI_OUTPUT_RAW) . ': ' . $project_name . "\n";
        $body .= $this->_AppUI->_('Task', UI_OUTPUT_RAW) . ': ' . $task->task_name . "\n";
        $body .= $this->_AppUI->_('Start Date', UI_OUTPUT_RAW) . ': START-TIME' . "\n";
        $body .= $this->_AppUI->_('Finish Date', UI_OUTPUT_RAW) . ': END-TIME' . "\n";
        $body .= $this->_AppUI->_('URL', UI_OUTPUT_RAW) . ': ' . W2P_BASE_URL . '/index.php?m=tasks&a=view&task_id=' . $task->task_id . '&reminded=1' . "\n\n";
        $body .= $this->_AppUI->_('Resources', UI_OUTPUT_RAW) . ":\n";

        foreach ($contacts as $contact) {
            if (!$owner_is_not_assignee || ($owner_is_not_assignee && $contact['contact_id'] != $owner_contact)) {
                $body .= ($contact['contact_name'] . ' <' . $contact['contact_email'] . ">\n");
            }
        }
        $body .= $this->_AppUI->_('Description', UI_OUTPUT_RAW) . ":\n" . $task->task_description . "\n";

        return $body;
    }

    public function getTaskEmailLog(CTask $task, CTask_Log $log)
    {
        $project = new CProject();
        $projname = $project->load($task->task_project)->project_name;

        $contact = new CContact();
        $creatorname = $contact->findContactByUserid($log->task_log_creator)->contact_display_name;

        $body = $this->_AppUI->_('Project', UI_OUTPUT_RAW) . ': ' . $projname . "\n";
        if ($task->task_parent != $task->task_id) {
            $tmpTask = new CTask();
            $taskname = $tmpTask->load($task->task_parent)->task_name;
            $body .= $this->_AppUI->_('Parent Task', UI_OUTPUT_RAW) . ': ' . $taskname . "\n";
        }
        $body .= $this->_AppUI->_('Task', UI_OUTPUT_RAW) . ': ' . $task->task_name . "\n";
        $task_types = w2PgetSysVal('TaskType');
        $body .= $this->_AppUI->_('Task Type', UI_OUTPUT_RAW) . ':' . $task_types[$task->task_type] . "\n";
        $body .= $this->_AppUI->_('URL', UI_OUTPUT_RAW) . ': ' . W2P_BASE_URL . '/index.php?m=tasks&a=view&task_id=' . $task->task_id . "\n\n";
        $body .= "------------------------\n\n";
        $body .= $this->_AppUI->_('User', UI_OUTPUT_RAW) . ': ' . $creatorname . "\n";
        $body .= $this->_AppUI->_('Hours', UI_OUTPUT_RAW) . ': ' . $log->task_log_hours . "\n";
        $body .= $this->_AppUI->_('Summary', UI_OUTPUT_RAW) . ': ' . $log->task_log_name . "\n\n";
        $body .= $log->task_log_description;

        $user = new CUser();
        $body .= "\n--\n" . $user->load($this->_AppUI->user_id)->user_signature;

        return $body;
    }

    public function getProjectNotify(CProject $project, $isNotNew)
    {
        $status = (intval($isNotNew)) ? 'Updated' : 'Created';

        $body = $this->_AppUI->_('Project') . ': ' . $project->project_name . ' ' . $this->_AppUI->_('has been') . ' ' . $this->_AppUI->_($status);
        $body .= "\n" . $this->_AppUI->_('You can view the Project by clicking'). ':';
        $body .= "\n" . $this->_AppUI->_('URL') . ':     ' . W2P_BASE_URL . '/index.php?m=projects&a=view&project_id=' . $project->project_id;
        $body .= "\n\n(" . $this->_AppUI->_('You are receiving this message because you are affiliated with this Project') . ")";
        $body .= "\n\n" . $this->_AppUI->_('Description') . ':' . "\n $project->project_description \n\n";

        $body .= (intval($isNotNew)) ? $this->_AppUI->_('Updater') : $this->_AppUI->_('Creator');
        $body .= ': ' . $this->_AppUI->user_display_name;

        if ($project->_message == 'deleted') {
            $body .= "\n\n" . $this->_AppUI->_('Project') . $project->project_name . $this->_AppUI->_('was deleted') . '.';
            $body .= "\n" . $this->_AppUI->_('deleted by') . ': ' . $this->_AppUI->user_display_name;
        }

        return $body;
    }

    public function getProjectNotifyOwner(CProject $project, $isNotNew)
    {
        trigger_error("EmailManager->getProjectNotifyOwner() has been deprecated in v3.2 and will be removed by v5.0. Please use EmailManager->getProjectNotify instead.", E_USER_NOTICE);

        return $this->getProjectNotify($project, $isNotNew);
    }

    public function getProjectNotifyContacts(CProject $project, $isNotNew)
    {
        trigger_error("EmailManager->getProjectNotifyContacts() has been deprecated in v3.2 and will be removed by v5.0. Please use EmailManager->getProjectNotify instead.", E_USER_NOTICE);

        return $this->getProjectNotify($project, $isNotNew);
    }

    public function getNotifyNewUser($username)
    {
        $object = new stdClass();
        $object->base_url = W2P_BASE_URL;
        $object->contact_name = $username;

        $body = "Dear contact_name,\n\n";
        $body .= "Congratulations! Your account has been activated by the administrator.\n";
        $body .= "Please use the login information provided earlier.\n\n";
        $body .= "You may login at the following URL: base_url\n\n";
        $body .= "If you have any difficulties or questions, please ask the administrator for help.\n";
        $body .= "Assuring you the best of our attention at all time.\n\n";
        $body .= "Our Warmest Regards,\n\n" . "The Support Staff.\n\n";
        $body .= "****PLEASE KEEP THIS EMAIL FOR YOUR RECORDS****";

        return $this->templater->render($body, $object);
    }

    public function notifyHR($username, $logname, $address, $userid)
    {
        $object = new stdClass();
        $object->base_url = W2P_BASE_URL;
        $object->company_name = w2PgetConfig('company_name');
        $object->contact_name = $username;
        $object->user_name = $logname;
        $object->email_address = $address;

        $body = "A new user has signed up on company_name. Please go through the user details below:\n";
        $body .= 'Name: contact_name' . "\n";
        $body .= 'Username: user_name' . "\n";
        $body .= 'Email: email_address' . "\n\n";
        $body .= 'You may check this account at the following URL: base_url';
        $body .= '/index.php?m=users&a=view&user_id=' . $userid . "\n\n";
        $body .= "Thank you very much.\n\n";
        $body .= "The company_name Taskforce.\n\n" . '****PLEASE KEEP THIS EMAIL FOR YOUR RECORDS****';

        return $this->templater->render($body, $object);
    }

    public function notifyNewExternalUser($logname, $logpwd)
    {
        $object = new stdClass();
        $object->base_url = W2P_BASE_URL;
        $object->company_name = w2PgetConfig('company_name');
        $object->log_name = $logname;
        $object->log_password = $logpwd;

        $body = "You have signed up for a new account on company_name.\n\n";
        $body .= "Once the administrator approves your request, you will receive an email with confirmation.\n";
        $body .= "Your login information are below for your own record:\n\n";
        $body .= 'Username: log_name' . "\n" . 'Password: log_password' . "\n\n";
        $body .= "You may login at the following URL: " . W2P_BASE_URL;
        $body .= "\n\n" . "Thank you very much.\n\n" . 'The company_name';
        $body .= " Support Staff.\n\n" . '****PLEASE KEEP THIS EMAIL FOR YOUR RECORDS****';

        return $this->templater->render($body, $object);
    }

    public function notifyNewUserCredentials($username, $logname, $logpwd)
    {
        $object = new stdClass();
        $object->base_url = W2P_BASE_URL;
        $object->user_name = $username;
        $object->log_name = $logname;
        $object->log_password = $logpwd;

        $body = "user_name,\n\n";
        $body .= "An access account has been created for you in our web2Project project management system.\n\n";
        $body .= "You can access it here at base_url\n\n";
        $body .= "Your username is: log_name\n";
        $body .= "Your password is: log_password\n\n";
        $body .= "This account will allow you to see and interact with projects. If you have any questions please contact us.";

        return $this->templater->render($body, $object);
    }

    public function notifyPasswordReset($username, $password)
    {
        $object = new stdClass();
        $object->base_url = W2P_BASE_URL;
        $object->user_name = $username;
        $object->password = $password;

        $body = $this->_AppUI->_('sendpass0', UI_OUTPUT_RAW) . ' user_name ' .
                $this->_AppUI->_('sendpass1', UI_OUTPUT_RAW) . ' base_url ' .
                $this->_AppUI->_('sendpass2', UI_OUTPUT_RAW) . ' password ' .
                $this->_AppUI->_('sendpass3', UI_OUTPUT_RAW);

        return $this->templater->render($body, $object);
    }
}
