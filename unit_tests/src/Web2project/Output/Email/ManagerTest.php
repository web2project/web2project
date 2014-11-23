<?php

/**
 * Class for testing Web2project\Output\Email\Template functionality
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to Clear BSD License. Please see the
 *   LICENSE file in root of site for further details
 *
 * @author      Keith Casey <contrib@caseysoftware.com>
 * @category    Email
 * @package     web2project
 * @subpackage  unit_tests
 * @license     Clear BSD
 * @link        http://www.web2project.net
 */

class Web2project_Output_Email_ManagerTest extends CommonSetup
{
    protected $manager = null;

    protected function setUp()
    {
        parent::setUp();

        $sender  = new \Web2project\Mocks\Email();

        $templater = new \CSystem_Template();
        $templater->overrideDatabase($this->mockDB);

        $this->manager = new \Web2project\Output\Email\Manager($sender, $templater);
    }

    public function testRender()
    {
        $values = array('task_name' => 'A task name', 'company_id' => '12345', 'project_name' => 'My Project');

        $raw_template = "My task is named {{task_name}} but my project is named {{project_name}}. By the way, they're owned by {{company_id}}.";
        $target_output = "My task is named A task name but my project is named My Project. By the way, they're owned by 12345.";

        $actual_output = $this->manager->render($raw_template, $values);

        $this->assertEquals($target_output, $actual_output);
    }

    public function testLoadTemplate()
    {
        $this->mockDB->stageHash(array('email_template_subject' => 'fake subject', 'email_template_body' => 'fake body'));
        $this->manager->loadTemplate('sample-name', 'en-us');

    }

    public function testSend()
    {
        $result = $this->manager->send('test@test.com', array());

        $this->assertTrue($result);
    }

    public function testSendAll()
    {
        $result = $this->manager->sendAll(array('test@test.com', 'test2@test.com'), array());

        $this->assertEquals(2, count($result));
    }
}