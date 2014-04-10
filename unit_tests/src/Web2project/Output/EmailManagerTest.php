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

// NOTE: This path is relative to Phing's build.xml, not this test.
include_once 'unit_tests/CommonSetup.php';

class Web2project_Output_EmailManagerTest extends CommonSetup
{
    protected $manager = null;

    protected function setUp()
    {
        parent::setUp();

        $this->manager = new EmailManager($this->_AppUI);
    }

    public function testGetEventNotify() {              $this->markTestIncomplete();    }
    public function testGetCalendarConflictEmail() {    $this->markTestIncomplete();    }
    public function testGetContactUpdateNotify() {      $this->markTestIncomplete();    }
    public function testGetFileUpdateNotify() {         $this->markTestIncomplete();    }
    public function testGetForumWatchEmail() {          $this->markTestIncomplete();    }
    public function testGetFileNotify() {               $this->markTestIncomplete();    }
    public function testGetFileNotifyContacts() {       $this->markTestIncomplete();    }
    public function testGetTaskNotify() {               $this->markTestIncomplete();    }
    public function testGetTaskNotifyOwner() {          $this->markTestIncomplete();    }
    public function testGetTaskRemind() {               $this->markTestIncomplete();    }
    public function testGetTaskEmailLog() {             $this->markTestIncomplete();    }
    public function testGetProjectNotifyOwner() {       $this->markTestIncomplete();    }
    public function testGetProjectNotifyContacts() {    $this->markTestIncomplete();    }
    public function testGetNotifyNewUser() {            $this->markTestIncomplete();    }
    public function testNotifyHR() {                    $this->markTestIncomplete();    }
    public function testNotifyNewExternalUser() {       $this->markTestIncomplete();    }
    public function testNotifyNewUserCredentials() {    $this->markTestIncomplete();    }
    public function testNotifyPasswordReset() {         $this->markTestIncomplete();    }
}