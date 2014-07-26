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

class w2p_Output_Email_Manager
{

    public $from = '';
    public $subject = '';
    public $body = '';

    protected $AppUI = null;
    protected $templater = null;

    public function __construct(w2p_Core_CAppUI $AppUI = null)
    {
        $this->AppUI = (is_null($AppUI)) ? new w2p_Core_CAppUI() : $AppUI;

        $this->templater = new \Web2project\Output\Email\Manager();
    }

    public function getEventNotify(CEvent $event, $notUsed, $users)
    {
        $date_format = $this->AppUI->getPref('SHDATEFORMAT');
        $time_format = $this->AppUI->getPref('TIMEFORMAT');
        $fmt = $date_format . ' ' . $time_format;

//TODO: customize these date formats based on the *receivers'* timezone setting
        $start_date = new w2p_Utilities_Date($event->event_start_date);
        $event->event_start_date = $start_date->format($fmt);
        $end_date = new w2p_Utilities_Date($event->event_end_date);
        $event->event_end_date = $end_date->format($fmt);

        // Find the project name.
        if ($event->event_project) {
            $project = new CProject();
            $event->project_name = $project->load($event->event_project)->project_name;
        }
        $types = w2PgetSysVal('EventType');
        $event->event_type = $this->AppUI->_($types[$event->event_type]);

        $body = $this->AppUI->_('Event') . ":\t{{event_name}}\n";
        $body .= $this->AppUI->_('URL') . ":\t" . W2P_BASE_URL . "/index.php?m=events&a=view&event_id={{event_id}}\n";
        $body .= $this->AppUI->_('Starts') . ":\t{{event_start_date}} GMT/UTC\n";
        $body .= $this->AppUI->_('Ends') . ":\t{{event_end_date}} GMT/UTC\n";

        // Find the project name.
        if ($event->event_project) {
            $body .= $this->AppUI->_('Project') . ":\t{{project_name}}\n";
        }

        $body .= $this->AppUI->_('Type') . ":\t{{event_type}}\n";
        $body .= $this->AppUI->_('Attendees') . ":\t";

        $body_attend = '';
        foreach ($users as $user) {
            $body_attend .= ((($body_attend) ? ', ' : '') . $user['contact_name']);
        }

        $body .= $body_attend . "\n\n{{event_description}}\n";

        return $this->templater->render($body, $event);
    }

    /**
     * @deprecated
     */
    public function getCalendarConflictEmail(w2p_Core_CAppUI $AppUI = null)
    {
        trigger_error("getCalendarConflictEmail has been deprecated in v3.0 and will be removed by v4.0. Please use getEventNotify() instead.", E_USER_NOTICE);

        $this->AppUI = (!is_null($AppUI)) ? $AppUI : $this->AppUI;

        $body  = "You have been invited to an event by {{user_display_name}}\n";
        $body .= "However, either you or another intended invitee has a competing event\n";
        $body .= "{{user_display_name}} has requested that you reply to this message\n";
        $body .= "and confirm if you can or can not make the requested time.\n\n";

        return $this->templater->render($body, $this->AppUI);
    }

    public function getContactUpdateNotify(w2p_Core_CAppUI $AppUI = null, CContact $contact)
    {
        $this->AppUI = (!is_null($AppUI)) ? $AppUI : $this->AppUI;

        $company = new CCompany();
        $company->load($contact->contact_company);
        $contact->company_name = $company->company_name;
        $contact->user_display_name = $this->AppUI->user_display_name;

        $body  = "Dear {{contact_title}} {{contact_display_name}},";
        $body .= "\n\nIt was very nice to visit you";
        $body .= ($contact->contact_company) ? " and {{company_name}}." : ".";
        $body .= " Thank you for all the time that you spent with me.";
        $body .= "\n\nI have entered the data from your business card into my contact database so that we may keep in touch.";
        $body .= " We have implemented a system which allows you to view the information that I've recorded and give you the opportunity to correct it or add information as you see fit. Please click on this link to view what I've recorded:";
        $body .= "\n\n" . W2P_BASE_URL . "/updatecontact.php?updatekey={{contact_updatekey}}";
        $body .= "\n\nI assure you that the information will be held in strict confidence and will not be available to anyone other than me. I realize that you may not feel comfortable filling out the entire form so please supply only what you're comfortable with.";
        $body .= "\n\nThank you. I look forward to seeing you again, soon.";
        $body .= "\n\nBest Regards,\n{{user_display_name}}";

        return $this->templater->render($body, $contact);
    }

    public function getFileUpdateNotify(w2p_Core_CAppUI $AppUI = null, CFile $file)
    {
        $this->AppUI = (!is_null($AppUI)) ? $AppUI : $this->AppUI;

        $file->user_display_name = $this->AppUI->user_display_name;
        $body = "\n\nFile {{file_name}} was {{_message}} by {{user_display_name}}";

        return $this->templater->render($body, $file);
    }

    public function getForumWatchEmail(CForum_Message $message, $forum_name, $message_from)
    {
        $message->forum_name = $forum_name;
        $message->message_from = $message_from;

        $body = $this->AppUI->_('forumEmailBody', UI_OUTPUT_RAW);
        $body .= "\n\n" . $this->AppUI->_('Forum', UI_OUTPUT_RAW) . ': {{forum_name}}';
        $body .= "\n" . $this->AppUI->_('Subject', UI_OUTPUT_RAW) . ': {{message_title}}';
        $body .= "\n" . $this->AppUI->_('Message From', UI_OUTPUT_RAW) . ': {{message_from}}';
        $body .= "\n\n" . W2P_BASE_URL . '/index.php?m=forums&a=viewer&forum_id={{message_forum}}';
        $body .= "\n\n{{message_body}}";

        return $this->templater->render($body, $message);
    }

    public function getFileNotify(CFile $file)
    {
        $file->project_name = $file->_project->project_name;
        $file->project_id = $file->_project->project_id;
        $file->task_name = $file->_task->task_name;
        $file->task_id = $file->_task->task_id;
        $file->task_description = $file->_task->task_description;
        $file->user_display_name = $this->AppUI->user_display_name;

        unset($file->_project);
        unset($file->_task);

        $body = $this->AppUI->_('Project') . ': {{project_name}}';
        $body .= "\n" . $this->AppUI->_('URL') . ':     ' . W2P_BASE_URL . '/index.php?m=projects&a=view&project_id={{project_id}}';

        if ((int) $file->_task->task_id) {
            $body .= "\n\n" . $this->AppUI->_('Task') . ':    {{task_name}}';
            $body .= "\n" . $this->AppUI->_('URL') . ':     ' . W2P_BASE_URL . '/index.php?m=tasks&a=view&task_id={{task_id}}';
            $body .= "\n" . $this->AppUI->_('Description') . ':' . "\n{{task_description}}";
        }
        $body .= "\n\nFile {{file_name}}" . ' was {{_message}}' . ' by {{user_display_name}}';
        if ($this->_message != 'deleted') {
            $body .= "\n" . $this->AppUI->_('URL') . ':     ' . W2P_BASE_URL . '/fileviewer.php?file_id={{file_id}}';
            $body .= "\n\n" . $this->AppUI->_('Description') . ':' . "\n{{file_description}}";
        }

        return $this->templater->render($body, $file);
    }

    public function getFileNotifyContacts(CFile $file)
    {
        return $this->getFileNotify($file);
    }

    public function getTaskNotify(CTask $task, $user, $projname)
    {
        $task->project_name = $projname;

        $body = $this->AppUI->_('Project', UI_OUTPUT_RAW) . ":\t{{project_name}}\n";
        $body .= $this->AppUI->_('Task', UI_OUTPUT_RAW) . ":\t\t{{task_name}}\n";
        $body .= $this->AppUI->_('Priority', UI_OUTPUT_RAW) . ":\t\t{{task_priority}}\n";
        $body .= $this->AppUI->_('Progress', UI_OUTPUT_RAW) . ":\t\t{{task_percent_complete}}%\n";

        $user_prefs = $this->AppUI->loadPrefs($user['assignee_id'], true);
        $start_date = new w2p_Utilities_Date($this->AppUI->formatTZAwareTime($task->task_start_date, '%Y-%m-%d %T'));
        $task->task_start_date = $start_date->format($user_prefs['DISPLAYFORMAT']);
        $end_date = new w2p_Utilities_Date($this->AppUI->formatTZAwareTime($task->task_end_date, '%Y-%m-%d %T'));
        $task->task_end_date = $end_date->format($user_prefs['DISPLAYFORMAT']);

        //$tmp_tz = $this->AppUI->getPref('TIMEZONE');
        $timezoneObj = new Date_TimeZone($user_prefs['TIMEZONE']);
        $task->timezone = $timezoneObj->getShortName();

        // Format dates using preferences but add T as Timezone abbreviation
        $body .= $this->AppUI->_('Start Date') . ":\t{{task_start_date}} {{timezone}}\n";
        $body .= $this->AppUI->_('Finish Date') . ":\t{{task_end_date}} {{timezone}}\n";

        $body .= $this->AppUI->_('URL', UI_OUTPUT_RAW) . ":\t\t" . W2P_BASE_URL . '/index.php?m=tasks&a=view&task_id={{task_id}}' . "\n\n";
        $body .= $this->AppUI->_('Description', UI_OUTPUT_RAW) . ': ' . "\n{{task_description}}";
        if ($user['creator_email']) {
            $body .= ("\n\n" . $this->AppUI->_('Creator', UI_OUTPUT_RAW) . ':' . "\n" . $user['creator_name'] . ', ' . $user['creator_email']);
        }
        $body .= ("\n\n" . $this->AppUI->_('Owner', UI_OUTPUT_RAW) . ':' . "\n" . $user['owner_name'] . ', ' . $user['owner_email']);
        if (isset($comment) && $comment != '') {
            $body .= "\n\n" . $comment;
        }

        return $this->templater->render($body, $task);
    }

    public function getTaskNotifyOwner(CTask $task)
    {
        $project = new CProject();
        $task->project_name = $project->load($task->task_project)->project_name;
        $task->user_display_name = $this->AppUI->user_display_name;

        $body = $this->AppUI->_('Project', UI_OUTPUT_RAW) . ':     {{project_name}}' . "\n";
        $body .= $this->AppUI->_('Task', UI_OUTPUT_RAW) . ':         {{task_name}}' . "\n";
        $body .= $this->AppUI->_('URL', UI_OUTPUT_RAW) . ':         ' . W2P_BASE_URL . '/index.php?m=tasks&a=view&task_id={{task_id}}' . "\n\n";
        $body .= $this->AppUI->_('Task Description', UI_OUTPUT_RAW) . ":\n{{task_description}}\n";
        $body .= $this->AppUI->_('Creator', UI_OUTPUT_RAW) . ': {{user_display_name}}' . "\n\n";
        $body .= $this->AppUI->_('Progress', UI_OUTPUT_RAW) . ': {{task_percent_complete}}%' . "\n\n";
//TODO: why is POST used here? Poor form - dkc 13 Nov 2011
        $body .= $this->AppUI->_('Summary', UI_OUTPUT_RAW) . ': ' . "\n\n";
        $body .= w2PgetParam($_POST, 'task_log_description');

        return $this->templater->render($body, $task);
    }

    public function getTaskRemind(CTask $task, $msg, $project_name, $contacts)
    {
        $task->task_due = $msg;
        $task->project_name = $project_name;

        $body = $this->AppUI->_('Task Due', UI_OUTPUT_RAW) . ': {{task_due}}' . "\n";
        $body .= $this->AppUI->_('Project', UI_OUTPUT_RAW) . ': {{project_name}}' . "\n";
        $body .= $this->AppUI->_('Task', UI_OUTPUT_RAW) . ': {{task_name}}' . "\n";
        $body .= $this->AppUI->_('Start Date', UI_OUTPUT_RAW) . ': START-TIME' . "\n";
        $body .= $this->AppUI->_('Finish Date', UI_OUTPUT_RAW) . ': END-TIME' . "\n";
        $body .= $this->AppUI->_('URL', UI_OUTPUT_RAW) . ': ' . W2P_BASE_URL . '/index.php?m=tasks&a=view&task_id={{task_id}}&reminded=1' . "\n\n";
        $body .= $this->AppUI->_('Resources', UI_OUTPUT_RAW) . ":\n";

        foreach ($contacts as $contact) {
            if (!$owner_is_not_assignee || ($owner_is_not_assignee && $contact['contact_id'] != $owner_contact)) {
                $body .= ($contact['contact_name'] . ' <' . $contact['contact_email'] . ">\n");
            }
        }
        $body .= $this->AppUI->_('Description', UI_OUTPUT_RAW) . ":\n{{task_description}}\n";

        return $this->templater->render($body, $task);
    }

    public function getTaskEmailLog(CTask $task, CTask_Log $log)
    {
        $project = new CProject();
        $task->project_name = $project->load($task->task_project)->project_name;

        $contact = new CContact();
        $task->creator_name = $contact->findContactByUserid($log->task_log_creator)->contact_display_name;

        $task_types = w2PgetSysVal('TaskType');
        $task->task_type = $task_types[$task->task_type];
        $task->task_log_hours = $log->task_log_hours;
        $task->task_log_name = $log->task_log_name;
        $task->task_log_description = $log->task_log_description;

        $user = new CUser();
        $task->user_signature = $user->load($this->AppUI->user_id)->user_signature;

        $body = $this->AppUI->_('Project', UI_OUTPUT_RAW) . ': {{project_name}}' . "\n";
        if ($task->task_parent != $task->task_id) {
            $tmpTask = new CTask();
            $task->parent_task_name = $tmpTask->load($task->task_parent)->task_name;
            $body .= $this->AppUI->_('Parent Task', UI_OUTPUT_RAW) . ': {{parent_task_name}}' . "\n";
        }
        $body .= $this->AppUI->_('Task', UI_OUTPUT_RAW) . ': {{task_name}}' . "\n";
        $body .= $this->AppUI->_('Task Type', UI_OUTPUT_RAW) . ': {{task_type}}' . "\n";
        $body .= $this->AppUI->_('URL', UI_OUTPUT_RAW) . ': ' . W2P_BASE_URL . '/index.php?m=tasks&a=view&task_id={{task_id}}' . "\n\n";
        $body .= "------------------------\n\n";
        $body .= $this->AppUI->_('User', UI_OUTPUT_RAW) . ': {{creator_name}}' . "\n";
        $body .= $this->AppUI->_('Hours', UI_OUTPUT_RAW) . ': {{task_log_hours}}' . "\n";
        $body .= $this->AppUI->_('Summary', UI_OUTPUT_RAW) . ': {{task_log_name}}' . "\n\n{{task_log_description}}";
        $body .= "\n--\n{{user_signature}}";

        return $this->templater->render($body, $task);
    }

    public function getProjectNotify(CProject $project, $isNotNew)
    {
        $project->user_display_name = $this->AppUI->user_display_name;

        $status = (intval($isNotNew)) ? 'Updated' : 'Created';

        $body = $this->AppUI->_('Project') . ': {{project_name}} ' . $this->AppUI->_('has been') . ' ' . $this->AppUI->_($status);
        $body .= "\n" . $this->AppUI->_('You can view the Project by clicking'). ':';
        $body .= "\n" . $this->AppUI->_('URL') . ':     ' . W2P_BASE_URL . '/index.php?m=projects&a=view&project_id={{project_id}}';
        $body .= "\n\n(" . $this->AppUI->_('You are receiving this message because you are affiliated with this Project') . ")";
        $body .= "\n\n" . $this->AppUI->_('Description') . ':' . "\n {{project_description}} \n\n";

        $body .= (intval($isNotNew)) ? $this->AppUI->_('Updater') : $this->AppUI->_('Creator');
        $body .= ': {{user_display_name}}';

        if ($project->_message == 'deleted') {
            $body .= "\n\n" . $this->AppUI->_('Project') . '{{project_name}}' . $this->AppUI->_('was deleted') . '.';
            $body .= "\n" . $this->AppUI->_('deleted by') . ': {{user_display_name}}';
        }

        return $this->templater->render($body, $project);
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

        $template = new CSystem_Template();
        $template->loadTemplate('new-account-created');
        $body = $template->email_template_body;

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
        $object->user_id = $userid;

        $template = new CSystem_Template();
        $template->loadTemplate('new-account-requested');
        $body = $template->email_template_body;

        return $this->templater->render($body, $object);
    }

    public function notifyNewExternalUser($logname, $logpwd)
    {
        $object = new stdClass();
        $object->base_url = W2P_BASE_URL;
        $object->company_name = w2PgetConfig('company_name');
        $object->log_name = $logname;
        $object->log_password = $logpwd;

        $body = "You have signed up for a new account on {{company_name}}.\n\n";
        $body .= "Once the administrator approves your request, you will receive an email with confirmation.\n";
        $body .= "Your login information are below for your own record:\n\n";
        $body .= 'Username: {{log_name}}' . "\n" . 'Password: {{log_password}}' . "\n\n";
        $body .= "You may login at the following URL: " . W2P_BASE_URL;
        $body .= "\n\n" . "Thank you very much.\n\n" . 'The {{company_name}}';
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

        $body = "{{user_name}},\n\n";
        $body .= "An access account has been created for you in our web2Project project management system.\n\n";
        $body .= "You can access it here at {{base_url}}\n\n";
        $body .= "Your username is: {{log_name}}\n";
        $body .= "Your password is: {{log_password}}\n\n";
        $body .= "This account will allow you to see and interact with projects. If you have any questions please contact us.";

        return $this->templater->render($body, $object);
    }

    public function notifyPasswordReset($username, $password)
    {
        $object = new stdClass();
        $object->base_url = W2P_BASE_URL;
        $object->user_name = $username;
        $object->password = $password;

        $body = $this->AppUI->_('sendpass0', UI_OUTPUT_RAW) . ' {{user_name}} ' .
                $this->AppUI->_('sendpass1', UI_OUTPUT_RAW) . ' {{base_url}} ' .
                $this->AppUI->_('sendpass2', UI_OUTPUT_RAW) . ' {{password}} ' .
                $this->AppUI->_('sendpass3', UI_OUTPUT_RAW);

        return $this->templater->render($body, $object);
    }
}