<?php
/**
 * Class for testing Web2project\Output\EmailManager functionality
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to Clear BSD License. Please see the
 *   LICENSE file in root of site for further details
 *
 * @author      Keith Casey <contrib@caseysoftware.com>
 * @category    EmailManager
 * @package     web2project
 * @subpackage  unit_tests
 * @license     Clear BSD
 * @link        http://www.web2project.net
 */

class Web2project_Output_EmailManagerTest extends CommonSetup
{
    protected $manager = null;

    protected function setUp()
    {
        parent::setUp();

        $this->manager = new w2p_Output_EmailManager($this->_AppUI);
    }

    public function testGetEventNotify()
    {
        $event = new CEvent();
        $event->event_name = 'Something Cool';
        $event->event_id = -1;
        $event->event_start_date = '2010-01-03 12:45:02';
        $event->event_end_date = '2010-02-03 12:45:02';
        $event->event_type = 1;
        $event->event_description = 'This will be a great event.';

        $target_body  = "Event:\tSomething Cool\nURL:\t" . W2P_BASE_URL . "/index.php?m=events&a=view&event_id=-1\n";
        $target_body .= "Starts:\t03/Jan/2010 12:45 pm GMT/UTC\nEnds:\t03/Feb/2010 12:45 pm GMT/UTC\n";
        $target_body .= "Type:\tAppointment\nAttendees:\t\n\nThis will be a great event.\n";
//todo: format dates

        $actual_body = $this->manager->getEventNotify($event, false, array());

        $this->assertEquals($target_body, $actual_body);
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testGetCalendarConflictEmail()
    {
        $target_body = "You have been invited to an event by Admin Person\nHowever, either you or another intended invitee has a competing event\n";
        $target_body .= "Admin Person has requested that you reply to this message\nand confirm if you can or can not make the requested time.\n\n";

        $actual_body = $this->manager->getCalendarConflictEmail($this->_AppUI);

        $this->assertEquals($target_body, $actual_body);
    }

    public function testGetContactUpdateNotify()
    {
        $contact = new CContact();
        $contact->contact_title = 'Mr.';
        $contact->contact_display_name = 'Monkey';
        $contact->contact_updatekey = 'testkey';

        $target_body  = "Dear Mr. Monkey,\n\nIt was very nice to visit you. Thank you for all the time that you spent with me.\n\n";
        $target_body .= "I have entered the data from your business card into my contact database so that we may keep in touch. We have implemented a system which allows you to view the information that I've recorded and give you the opportunity to correct it or add information as you see fit. Please click on this link to view what I've recorded:\n\n";
        $target_body .= W2P_BASE_URL . "/updatecontact.php?updatekey=testkey\n\n";
        $target_body .= "I assure you that the information will be held in strict confidence and will not be available to anyone other than me. I realize that you may not feel comfortable filling out the entire form so please supply only what you're comfortable with.\n\n";
        $target_body .= "Thank you. I look forward to seeing you again, soon.\n\nBest Regards,\nAdmin Person";

        $actual_body = $this->manager->getContactUpdateNotify($this->_AppUI, $contact);

        $this->assertEquals($target_body, $actual_body);
    }

    public function testGetFileUpdateNotify()
    {
        $this->_AppUI->user_display_name = 'Admin Person';
        $file = new CFile();
        $file->file_name = 'autoexec.bat';
        $file->_message = 'updated';

        $target_body  = "\n\nFile autoexec.bat was updated by Admin Person";

        $actual_body  = $this->manager->getFileUpdateNotify($this->_AppUI, $file);

        $this->assertEquals($target_body, $actual_body);
    }

    public function testGetForumWatchEmail()
    {
        $target_body  = "forumEmailBody\n\nForum: name1\nSubject: Title\nMessage From: name2\n\n";
        $target_body .= W2P_BASE_URL . "/index.php?m=forums&a=viewer&forum_id=-1\n\nMy Body";

        $message = new CForum_Message();
        $message->message_title = 'Title';
        $message->message_body = 'My Body';
        $message->message_forum = -1;

        $actual_body  = $this->manager->getForumWatchEmail($message, 'name1', 'name2');

        $this->assertEquals($target_body, $actual_body);
    }

    public function testGetFileNotify()
    {
        $this->_AppUI->user_display_name = 'Admin Person';
        $file = new CFile();
        $file->file_id = 1;
        $file->file_name = 'autoexec.bat';
        $file->file_description = "Some Description";
        $file->_message = 'updated';
        $project = new CProject();
        $project->project_name = 'My Project';
        $project->project_id = -1;
        $file->_project = $project;

        $target_body  = "Project: My Project\nURL:     " . W2P_BASE_URL . "/index.php?m=projects&a=view&project_id=-1\n\n";
        $target_body .= "File autoexec.bat was updated by Admin Person\nURL:     " . W2P_BASE_URL . "/fileviewer.php?file_id=1\n\n";
        $target_body .= "Description:\nSome Description";

        $actual_body  = $this->manager->getFileNotify($file);

        $this->assertEquals($target_body, $actual_body);
    }

    public function testGetTaskNotify()
    {
        $task = new CTask();
        $task->task_id = -1;
        $task->task_name = 'My Task';
        $task->task_priority = 1;
        $task->task_percent_complete = 50;
        $task->task_start_date = '2010-01-30 12:30:30';
        $task->task_end_date   = '2011-02-02 12:45:15';
        $task->task_description = 'one two three';

        $target_body  = "Project:\tProject Name\n";
        $target_body .= "Task:\t\tMy Task\n";
        $target_body .= "Priority:\t1\n";
        $target_body .= "Progress:\t50%\n";
        $target_body .= "Start Date:\t30/Jan/2010 06:30 am CST\n";
        $target_body .= "Finish Date:\t02/Feb/2011 06:45 am CST\n";
        $target_body .= "URL:\t\t" . W2P_BASE_URL . "/index.php?m=tasks&a=view&task_id=-1\n\n";
        $target_body .= "Description: \none two three\n\n";
        $target_body .= "Owner:\n, ";

        $actual_body  = $this->manager->getTaskNotify($task, array(), 'Project Name');

        $this->assertEquals($target_body, $actual_body);
    }

    public function testGetTaskNotifyOwner()
    {
        $task = new CTask();
        $task->task_name = 'Task Name';
        $task->task_id = -1;
        $task->task_description = 'something cool';
        $task->task_percent_complete = 45;

        $target_body  = "Project:     \n";
        $target_body .= "Task:         Task Name\n";
        $target_body .= "URL:         " . W2P_BASE_URL . "/index.php?m=tasks&a=view&task_id=-1\n\n";
        $target_body .= "Task Description:\nsomething cool\n";
        $target_body .= "Creator: Admin Person\n\n";
        $target_body .= "Progress: 45%\n\n";
        $target_body .= "Summary: \n\n";
        $target_body .= "";

        $actual_body = $this->manager->getTaskNotifyOwner($task);

        $this->assertEquals($target_body, $actual_body);
    }

    public function testGetTaskRemind()
    {
        $contacts = array(array('contact_name' => 'Admin Person', 'contact_email' => 'admin@web2project.net'));
        $task = new CTask();
        $task->task_id = -1;
        $task->task_name = 'Task Name';
        $task->task_description = 'description';

        $target_body  = "Task Due: one\nProject: two\nTask: Task Name\nStart Date: START-TIME\nFinish Date: END-TIME\n";
        $target_body .= "URL: " . W2P_BASE_URL . "/index.php?m=tasks&a=view&task_id=-1&reminded=1\n\n";
        $target_body .= "Resources:\nAdmin Person <admin@web2project.net>\nDescription:\ndescription\n";

        $actual_body = $this->manager->getTaskRemind($task, 'one', 'two', $contacts);

        $this->assertEquals($target_body, $actual_body);
    }

    public function testGetTaskEmailLog()
    {
        $task = new CTask();
        $task->task_id = -1;
        $task->task_parent = -1;
        $task->task_name = 'Task Name';
        $task->task_type = 1;
        $task_log = new CTask_Log();
        $task_log->task_log_hours = 2.0;
        $task_log->task_log_name = 'Something cool';

        $target_body  = "Project: \nTask: Task Name\nTask Type:Administrative\n";
        $target_body .= "URL: " . W2P_BASE_URL . "/index.php?m=tasks&a=view&task_id=-1\n\n";
        $target_body .= "------------------------\n\nUser: \nHours: 2\nSummary: Something cool\n\n\n--\n";

        $actual_body = $this->manager->getTaskEmailLog($task, $task_log);

        $this->assertEquals($target_body, $actual_body);
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testGetProjectNotifyOwner()
    {
        $project = new CProject();
        $project->project_id = -1;
        $project->project_name = 'Project Name';
        $project->project_description = 'description';

        $target_body  = "Project: Project Name has been Created\nYou can view the Project by clicking:\n";
        $target_body .= "URL:     " . W2P_BASE_URL . "/index.php?m=projects&a=view&project_id=-1\n\n";
        $target_body .= "(You are receiving this message because you are the owner of this Project)\n\n";
        $target_body .= "Description:\n description \n\n";
        $target_body .= "Creator: Admin Person";

        $actual_body = $this->manager->getProjectNotifyOwner($project, false);

        $this->assertEquals($target_body, $actual_body);
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testGetProjectNotifyContacts()
    {
        $project = new CProject();
        $project->project_name = 'Project Name';
        $project->project_description = 'something interesting';
        $project->project_id = -1;

        $target_body  = "Project: Project Name Has Been Created Via Project Manager. You can view the Project by clicking: \n";
        $target_body .= "URL:     " . W2P_BASE_URL . "/index.php?m=projects&a=view&project_id=-1\n\n";
        $target_body .= "(You are receiving this message because you are a contact or assignee for this Project)\n\n";
        $target_body .= "Description:\n something interesting \n\n";
        $target_body .= "Creator: Admin Person";

        $actual_body = $this->manager->getProjectNotifyContacts($project, false);

        $this->assertEquals($target_body, $actual_body);
    }

    public function testGetProjectNotify()
    {
        $project = new CProject();
        $project->project_name = 'Project Name';
        $project->project_description = 'something interesting';
        $project->project_id = -1;

        $target_body = "Project: Project Name has been Created\n";
        $target_body .= "You can view the Project by clicking:\n";
        $target_body .= "URL:     " . W2P_BASE_URL . "/index.php?m=projects&a=view&project_id=-1\n\n";
        $target_body .= "(You are receiving this message because you are affiliated with this Project)\n\n";
        $target_body .= "Description:\n something interesting \n\n";
        $target_body .= "Creator: Admin Person";

        $actual_body = $this->manager->getProjectNotify($project, false);

        $this->assertEquals($target_body, $actual_body);
    }

    public function testGetNotifyNewUser()
    {
        $target_body  = "Dear Keith Casey,\n\n";
        $target_body .= "Congratulations! Your account has been activated by the administrator.\n";
        $target_body .= "Please use the login information provided earlier.\n\n";
        $target_body .= "You may login at the following URL: " . W2P_BASE_URL . "\n\n";
        $target_body .= "If you have any difficulties or questions, please ask the administrator for help.\n";
        $target_body .= "Assuring you the best of our attention at all time.\n\n";
        $target_body .= "Our Warmest Regards,\n\nThe Support Staff.\n\n****PLEASE KEEP THIS EMAIL FOR YOUR RECORDS****";

        $actual_body = $this->manager->getNotifyNewUser('Keith Casey');

        $this->assertEquals($target_body, $actual_body);
    }

    public function testNotifyHR()
    {
        $target_body  = "A new user has signed up on web2Project Development. Please go through the user details below:\n";
        $target_body .= "Name: Keith Casey\nUsername: caseysoftware\nEmail: test@test.com\n\n";
        $target_body .= "You may check this account at the following URL: ";
        $target_body .= W2P_BASE_URL . "/index.php?m=users&a=view&user_id=-1\n\n";
        $target_body .= "Thank you very much.\n\nThe web2Project Development Taskforce.\n\n";
        $target_body .= "****PLEASE KEEP THIS EMAIL FOR YOUR RECORDS****";

        $actual_body = $this->manager->notifyHR("Keith Casey", "caseysoftware", "test@test.com", -1);

        $this->assertEquals($target_body, $actual_body);
    }

    public function testNotifyNewExternalUser()
    {
        $target_body  = "You have signed up for a new account on web2Project Development.\n\n";
        $target_body .= "Once the administrator approves your request, you will receive an email with confirmation.\n";
        $target_body .= "Your login information are below for your own record:\n\n";
        $target_body .= "Username: admin\nPassword: password\n\n";
        $target_body .= "You may login at the following URL: " . W2P_BASE_URL . "\n\nThank you very much.\n\n";
        $target_body .= "The web2Project Development Support Staff.\n\n";
        $target_body .= "****PLEASE KEEP THIS EMAIL FOR YOUR RECORDS****";

        $actual_body = $this->manager->notifyNewExternalUser('admin', 'password');

        $this->assertEquals($target_body, $actual_body);
    }

    public function testNotifyNewUserCredentials()
    {
        $target_body  = "Admin,\n\nAn access account has been created for you in our web2Project project management system.\n\n";
        $target_body .= "You can access it here at " . W2P_BASE_URL . "\n\n";
        $target_body .= "Your username is: admin2\nYour password is: password\n\n";
        $target_body .= "This account will allow you to see and interact with projects. If you have any questions please contact us.";

        $actual_body = $this->manager->notifyNewUserCredentials('Admin', 'admin2', 'password');

        $this->assertEquals($target_body, $actual_body);
    }

    public function testNotifyPasswordReset()
    {
        $target_body  = "The user account admin has this email associated with it.\n";
        $target_body .= "A web user from " . W2P_BASE_URL ." has just requested that a new password be sent.\n\n";
        $target_body .= "Your New Password is: password If you didn't ask for this, don't worry. You are seeing this message, not them. If this was an error just login with your new password and then change your password to what you would like it to be.";

        $actual_body = $this->manager->notifyPasswordReset('admin', 'password');

        $this->assertEquals($target_body, $actual_body);
    }
}