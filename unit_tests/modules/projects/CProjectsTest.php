<?php
/**
 * Class for testing projects functionality
 *
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to Clear BSD License. Please see the
 *   LICENSE file in root of site for further details
 *
 * @author      Trevor Morse <trevor.morse@gmail.com>
 * @category    CProjects
 * @package     web2project
 * @subpackage  unit_tests
 * @license     Clear BSD
 * @link        http://www.web2project.net
 */

class CProjectsTest extends CommonSetup
{
    protected function setUp()
    {
        parent::setUp();

        $this->obj = new CProject();
        $this->mockDB = new w2p_Mocks_Query();
$this->obj->overrideDatabase($this->mockDB);

        $GLOBALS['acl'] = new w2p_Mocks_Permissions();

        $this->post_data = array(
            'dosql' =>                      'do_project_aed',
            'project_id' =>                 0,
            'project_creator' =>            1,
            'project_contacts' =>           '',
            'project_name' =>               'New Project',
            'project_parent' =>             '',
            'project_owner' =>              1,
            'project_company' =>            1,
            'project_location' =>           '',
            'project_start_date' =>         '20090628',
            'project_end_date' =>           '20090728',
            'project_target_budget' =>      5,
            'project_actual_budget' =>      10,
            'project_scheduled_hours' =>    0,
            'project_worked_hours' =>       0,
            'project_task_count' =>         0,
            'project_url' =>                'project.example.org',
            'project_demo_url' =>           'projectdemo.example.org',
            'project_priority' =>           '-1',
            'project_short_name' =>         'nproject',
            'project_color_identifier' =>   'FFFFFF',
            'project_type' =>               0,
            'project_status' =>             0,
            'project_description' =>        'This is a project.',
            'email_project_owner' =>        1,
            'email_project_contacts' =>     1
        );
    }

    public function testObjectProperties()
    {
        parent::objectPropertiesTest('CProject', 31);
    }

    /**
     * Tests that the proper error message is returned when no name is passed.
     */
    public function testCreateProjectNoName()
    {
        unset($this->post_data['project_name']);
        $this->obj->bind($this->post_data);

        /**
         * Verify we got the proper error message
         */
        $this->assertFalse($this->obj->store());
        $this->assertArrayHasKey('project_name', $this->obj->getError());

        /**
         * Verify that project id was not set
         */
        $this->AssertEquals(0, $this->obj->project_id);
    }

    /**
     * Tests that the proper error message is returned when no company is passed.
     */
    public function testCreateProjectNoCompany()
    {
        unset($this->post_data['project_company']);
        $this->obj->bind($this->post_data);

        /**
         * Verify we got the proper error message
         */
        $this->assertFalse($this->obj->store());
        $this->assertArrayHasKey('project_company', $this->obj->getError());

        /**
         * Verify that project id was not set
         */
        $this->AssertEquals(0, $this->obj->project_id);
    }

    /**
     * Tests that the proper error message is returned when no priority is passed.
     */
    public function testCreateProjectNoPriority()
    {
        unset($this->post_data['project_priority']);
        $this->obj->bind($this->post_data);

        /**
         * Verify we didn't get this error message.
         */
        $this->assertTrue($this->obj->store());
        $this->assertArrayNotHasKey('project_priority', $this->obj->getError());

        /**
         * Verify that project id was not set
         */
        $this->assertGreaterThan(0, $this->obj->project_id);
    }

    /**
     * Tests that the proper error message is returned when no color identifier is passed.
     */
    public function testCreateProjectNoColorIdentifier()
    {
        unset($this->post_data['project_color_identifier']);
        $this->obj->bind($this->post_data);

        /**
         * Verify we got the proper error message
         */
        $this->assertFalse($this->obj->store());
        $this->assertArrayHasKey('project_color_identifier', $this->obj->getError());

        /**
         * Verify that project id was not set
         */
        $this->AssertEquals(0, $this->obj->project_id);
    }

    /**
     * Tests that the proper error message is returned when no type is passed.
     */
    public function testCreateProjectNoType()
    {
        unset($this->post_data['project_type']);
        $this->obj->bind($this->post_data);

        /**
         * Verify we didn't get this error message.
         */
        $this->assertTrue($this->obj->store());
        $this->assertArrayNotHasKey('project_type', $this->obj->getError());

        /**
         * Verify that project id was not set
         */
        $this->assertGreaterThan(0, $this->obj->project_id);
    }

    /**
     * Tests that the proper error message is returned when no status is passed.
     */
    public function testCreateProjectNoStatus()
    {
        unset($this->post_data['project_status']);
        $this->obj->bind($this->post_data);

        /**
         * Verify we didn't get this error message.
         */
        $this->assertTrue($this->obj->store());
        $this->assertArrayNotHasKey('project_status', $this->obj->getError());

        /**
         * Verify that project id was not set
         */
        $this->assertGreaterThan(0, $this->obj->project_id);
    }

    /**
     * Tests that the proper error message is returned when no creator is passed.
     */
    public function testCreateProjectNoCreator()
    {
         unset($this->post_data['project_creator']);
        $this->obj->bind($this->post_data);

        /**
         * Verify we didn't get this error message.
         */
        $this->assertTrue($this->obj->store());
        $this->assertArrayNotHasKey('project_creator', $this->obj->getError());

        /**
         * Verify that project id was not set
         */
        $this->assertGreaterThan(0, $this->obj->project_id);
    }

    /**
     * Tests the proper creation of a project.
     */
    public function testStoreCreate()
    {
        $this->obj->bind($this->post_data);

        $results = $this->obj->store();
        $this->assertTrue($results);
        $this->assertEquals(1,                           $this->obj->project_id);
        /*
         *  These fields are all auto-generated in the CProject->store method.
         */
        $this->assertEquals(1,                           $this->obj->project_parent);
        $this->assertEquals(1,                           $this->obj->project_original_parent);
        $this->assertEquals(1,                           $this->obj->project_company);
        $this->assertNotNull($this->obj->project_created);
        $this->assertEquals($this->obj->project_created, $this->obj->project_updated);
        /*
         *  These fields are from the $_POST but are modified in the store().
         */
        $this->assertEquals('2009-06-28 00:00:00',       $this->obj->project_start_date);
        $this->assertEquals('2009-07-28 00:00:00',       $this->obj->project_end_date);
        /*
         *  These fields come from the $_POST data and should be pass throughs.
         */
        $this->assertEquals('New Project',               $this->obj->project_name);
        $this->assertEquals('nproject',                  $this->obj->project_short_name);
        $this->assertEquals(1,                           $this->obj->project_owner);
        $this->assertEquals('FFFFFF',                    $this->obj->project_color_identifier);
        $this->assertEquals('project.example.org',       $this->obj->project_url);
        $this->assertEquals('projectdemo.example.org',   $this->obj->project_demo_url);
        /*
         *  These fields are deprecated and should always be empty.
         */
        $this->assertEquals('',                          $this->obj->project_department);
        $this->assertEquals(array(0 => ''),              $this->obj->project_contacts);
    }

    /**
     * Tests that the check function returns the proper error message when project_name is null.
     */
    public function testCheckNullName()
    {
        unset($this->post_data['project_name']);

        $this->obj->bind($this->post_data);
        $errorArray = $this->obj->check();
        $this->assertArrayHasKey('project_name', $errorArray);
    }

    /**
     * Tests that the check function returns the nothing when data is correct.
     */
    public function testCheck()
    {
        $this->obj->bind($this->post_data);
        $errorArray = $this->obj->check();
        $this->assertEquals(0, count($errorArray));
    }

    /**
     * Tests loading the Project object.
     */
    public function testLoad()
    {
        $this->obj->bind($this->post_data);
        $this->assertEquals($this->obj->project_created,    '');
        $this->assertEquals($this->obj->project_updated,    '');

        $result = $this->obj->store();
        $this->assertTrue($result);

        $item = new CProject();
        $item->overrideDatabase($this->mockDB);
        $this->post_data['link_id'] = $this->obj->project_id;
        $this->mockDB->stageHash($this->post_data);
        $item->load($this->obj->project_id);

        $this->assertEquals($this->obj->project_name,       $item->project_name);
        //$this->assertEquals($this->obj->project_id,         $item->project_parent);
        //$this->assertEquals($this->obj->project_id,         $item->project_original_parent);
        $this->assertNotEquals($this->obj->project_created, '');
        $this->assertNotEquals($this->obj->project_updated, '');
    }

    /**
     * Tests the update of a project.
     */
    public function testStoreUpdate()
    {
        $this->obj->bind($this->post_data);
        $result = $this->obj->store();
        $this->assertTrue($result);
        $original_id = $this->obj->project_id;

        $this->obj->project_name        = 'Updated Project';
        $this->obj->project_location    = 'Somewhere Updated';

        /*
         * This sleep() is used because we need at least a second to pass for the
         *   project_updated time to be different than the project_created earlier
         *   in this test.
         */
        sleep(1);
        $result = $this->obj->store();
        $this->assertTrue($result);
        $new_id = $this->obj->project_id;

        $this->assertEquals($original_id,                   $new_id);
        $this->assertEquals('Updated Project',              $this->obj->project_name);
        $this->assertEquals('Somewhere Updated',            $this->obj->project_location);
        $this->assertNotEquals($this->obj->project_created, $this->obj->project_updated);
    }

    /**
     * Tests checking allowed records with no permissions
     */
    public function testGetAllowedRecordsNoPermissions()
    {
        $allowed_records = $this->obj->getAllowedRecords(2);

        $this->assertEquals(0, count($allowed_records));
    }

    /**
     * Tests checking allowed records with where set
     */
    public function testGetAllowedRecordsWithWhere()
    {
        $this->mockDB->stageHashList(1, 'Test Project');

        $extra = array('where' => 'project_active = 1');
        $allowed_records = $this->obj->getAllowedRecords(1, 'projects.project_id,project_name', null, null, $extra);

        $this->assertEquals(1, count($allowed_records));
        $this->assertEquals('Test Project', $allowed_records[1]);
    }

    /**
     * Tests getting a list of allowed project by user
     *
     * @expectedException PHPUnit_Framework_Error
     */
    public function testGetAllowedProjectsInRows()
    {
        $project_in_rows = $this->obj->getAllowedProjectsInRows(1);

        $this->assertEquals(4, db_num_rows($project_in_rows));

        $row = db_fetch_assoc($project_in_rows);
        $this->assertEquals(1,                     $row[0]);
        $this->assertEquals(1,                     $row['project_id']);
        $this->assertEquals(0,                     $row[1]);
        $this->assertEquals(0,                     $row['project_status']);
        $this->assertEquals('Test Project',        $row[2]);
        $this->assertEquals('Test Project',        $row['project_name']);
        $this->assertEquals('This is a project',   $row[3]);
        $this->assertEquals('This is a project',   $row['project_description']);
        $this->assertEquals('TP',                  $row[4]);
        $this->assertEquals('TP',                  $row['project_short_name']);

        $project_in_rows = $this->obj->getAllowedProjectsInRows(2);

        $this->assertEquals(0, db_num_rows($project_in_rows));
    }

    /**
     * Tests getting the most critical tasks with project loaded
     */
    public function testGetCriticalTasksNoArgs()
    {
        $this->mockDB->stageList(
                array('task_id' => 1, 'task_name' => 'Task',
                    'task_start_date' => '2009-07-05 00:00:00',
                    'task_end_date' => '2009-07-15 00:00:00',
                    'task_created' => '2009-07-05 15:43:00', 'task_updated' => '2009-07-05 15:43:00')
        );

        $this->obj->load(1);

        $critical_tasks = $this->obj->getCriticalTasks();

        $this->assertEquals(1,                      count($critical_tasks));
        $this->assertEquals(1,                      $critical_tasks[0]['task_id']);
        $this->assertEquals('Task',                 $critical_tasks[0]['task_name']);
        $this->assertEquals('2009-07-05 00:00:00',  $critical_tasks[0]['task_start_date']);
        $this->assertEquals('2009-07-15 00:00:00',  $critical_tasks[0]['task_end_date']);
        $this->assertEquals('2009-07-05 15:43:00',  $critical_tasks[0]['task_created']);
        $this->assertEquals('2009-07-05 15:43:00',  $critical_tasks[0]['task_updated']);
    }

    /**
     * Tests getting critical tasks with no project loaded and
     * project id passed as argument
     */
    public function testGetCriticalTasksProjectID()
    {
        $this->mockDB->stageList(
                array('task_id' => 1, 'task_name' => 'Task',
                    'task_start_date' => '2009-07-05 00:00:00',
                    'task_end_date' => '2009-07-15 00:00:00',
                    'task_created' => '2009-07-05 15:43:00', 'task_updated' => '2009-07-05 15:43:00')
        );

        $critical_tasks = $this->obj->getCriticalTasks(1);

        $this->assertEquals(1,                      count($critical_tasks));
        $this->assertEquals(1,                      $critical_tasks[0]['task_id']);
        $this->assertEquals('Task',                 $critical_tasks[0]['task_name']);
        $this->assertEquals('2009-07-05 00:00:00',  $critical_tasks[0]['task_start_date']);
        $this->assertEquals('2009-07-15 00:00:00',  $critical_tasks[0]['task_end_date']);
        $this->assertEquals('2009-07-05 15:43:00',  $critical_tasks[0]['task_created']);
        $this->assertEquals('2009-07-05 15:43:00',  $critical_tasks[0]['task_updated']);
    }

    /**
     * Tests getting critical tasks with no project loaded and
     * project id and limit passed as arguments
     */
    public function testGetCriticalTasksProjectIDAndLimit()
    {
        $this->mockDB->stageList(
                array('task_id' => 1, 'task_name' => 'Task',
                    'task_start_date' => '2009-07-05 00:00:00',
                    'task_end_date' => '2009-07-15 00:00:00',
                    'task_created' => '2009-07-05 15:43:00', 'task_updated' => '2009-07-05 15:43:00'));
        $this->mockDB->stageList(
                array('task_id' => 2, 'task_name' => 'Task 2',
                    'task_start_date' => '2009-07-06 00:00:00',
                    'task_end_date' => '2009-07-15 00:00:00',
                    'task_created' => '2009-07-08 15:43:00', 'task_updated' => '2009-07-08 15:43:00'));
        $critical_tasks = $this->obj->getCriticalTasks(1,2);

        $this->assertEquals(2,                      count($critical_tasks));
        $this->assertEquals(1,                      $critical_tasks[0]['task_id']);
        $this->assertEquals('Task',                 $critical_tasks[0]['task_name']);
        $this->assertEquals('2009-07-05 00:00:00',  $critical_tasks[0]['task_start_date']);
        $this->assertEquals('2009-07-15 00:00:00',  $critical_tasks[0]['task_end_date']);
        $this->assertEquals('2009-07-05 15:43:00',  $critical_tasks[0]['task_created']);
        $this->assertEquals('2009-07-05 15:43:00',  $critical_tasks[0]['task_updated']);

        $this->assertEquals('Task 2',               $critical_tasks[1]['task_name']);
        $this->assertEquals('2009-07-06 00:00:00',  $critical_tasks[1]['task_start_date']);
        $this->assertEquals('2009-07-15 00:00:00',  $critical_tasks[1]['task_end_date']);
        $this->assertEquals('2009-07-08 15:43:00',  $critical_tasks[1]['task_created']);
        $this->assertEquals('2009-07-08 15:43:00',  $critical_tasks[1]['task_updated']);
    }

    /**
     * Testing further functionality of store, specifically the contacts and
     * departments saving. The basic functionality is covered in the
     * create and update tests.
     */
    public function testStoreCreateContactsDepartments()
    {
        $this->post_data['project_departments'] = array(1,2);
        $this->post_data['project_contacts'] = array(3,4);
        $this->obj->bind($this->post_data);
        $result = $this->obj->store();

        $this->assertTrue($result);
        $this->assertEquals(1,                   $this->obj->project_id);
        /*
         * All of the rest of the fields are tested in the testStoreCreate and
         *   testStoreUpdate methods, so no need to retest here.
         */
        $this->assertEquals(2,                   count($this->obj->stored_departments));
        $this->assertTrue(isset($this->obj->stored_departments[1]));
        $this->assertTrue(isset($this->obj->stored_departments[2]));
        $this->assertEquals(2,                   count($this->obj->stored_contacts));
        $this->assertTrue(isset($this->obj->stored_contacts[3]));
        $this->assertTrue(isset($this->obj->stored_contacts[4]));
    }

    /**
     * Tests getting allowed projects that are active.
     */
    public function testGetAllowedProjectsActiveOnly()
    {
        $this->mockDB->stageHashList(1,
                array('project_id' => 1, 'project_name' => 'Test Project',
                    'project_start_date' => '2009-07-05 00:00:00', 'project_end_date' => '2009-07-15 23:59:59'));
        $allowed_projects = $this->obj->getAllowedProjects(1);

        $this->assertEquals(1,                      count($allowed_projects));
        $this->assertEquals(1,                      $allowed_projects[1]['project_id']);
        $this->assertEquals('Test Project',         $allowed_projects[1]['project_name']);
        $this->assertEquals('2009-07-05 00:00:00',  $allowed_projects[1]['project_start_date']);
        $this->assertEquals('2009-07-15 23:59:59',  $allowed_projects[1]['project_end_date']);
    }

    /**
     * Tests getting allowed projects that are active or inactive.
     */
    public function testGetAllowedProjectsAll()
    {
        $this->mockDB->stageHashList(1,
                array('project_id' => 1, 'project_name' => 'Test Project',
                    'project_start_date' => '2009-07-05 00:00:00', 'project_end_date' => '2009-07-15 23:59:59'));
        $this->mockDB->stageHashList(2,
                array('project_id' => 2, 'project_name' => 'Test Project 2',
                    'project_start_date' => '2009-07-08 00:00:00', 'project_end_date' => '2009-07-18 23:59:59'));
        $this->mockDB->stageHashList(3,
                array('project_id' => 3, 'project_name' => 'Test Project 3',
                    'project_start_date' => '2009-07-08 00:00:00', 'project_end_date' => '2009-07-18 23:59:59'));
        $this->mockDB->stageHashList(4,
                array('project_id' => 4, 'project_name' => 'Test Project 4',
                    'project_start_date' => '2009-07-08 00:00:00', 'project_end_date' => '2009-07-18 23:59:59'));
        $allowed_projects = $this->obj->getAllowedProjects(1, false);

        $this->assertEquals(4,                      count($allowed_projects));
        $this->assertEquals(1,                      $allowed_projects[1]['project_id']);
        $this->assertEquals('Test Project',         $allowed_projects[1]['project_name']);
        $this->assertEquals('2009-07-05 00:00:00',  $allowed_projects[1]['project_start_date']);
        $this->assertEquals('2009-07-15 23:59:59',  $allowed_projects[1]['project_end_date']);
        $this->assertEquals(2,                      $allowed_projects[2]['project_id']);
        $this->assertEquals('Test Project 2',       $allowed_projects[2]['project_name']);
        $this->assertEquals('2009-07-08 00:00:00',  $allowed_projects[2]['project_start_date']);
        $this->assertEquals('2009-07-18 23:59:59',  $allowed_projects[2]['project_end_date']);
        $this->assertEquals(3,                      $allowed_projects[3]['project_id']);
        $this->assertEquals('Test Project 3',       $allowed_projects[3]['project_name']);
        $this->assertEquals('2009-07-08 00:00:00',  $allowed_projects[3]['project_start_date']);
        $this->assertEquals('2009-07-18 23:59:59',  $allowed_projects[3]['project_end_date']);
        $this->assertEquals(4,                      $allowed_projects[4]['project_id']);
        $this->assertEquals('Test Project 4',       $allowed_projects[4]['project_name']);
        $this->assertEquals('2009-07-08 00:00:00',  $allowed_projects[4]['project_start_date']);
        $this->assertEquals('2009-07-18 23:59:59',  $allowed_projects[4]['project_end_date']);
    }

    /**
     * Tests finding contacts of project that does have contact
     */
    public function testGetContacts()
    {
        $this->mockDB->stageHashList(1,
                array('contact_id' => 1, 'contact_first_name' => 'Admin',
                    'contact_last_name' => 'Person', 'contact_order_by' => '', 'dept_name' => ''));

        $this->obj->project_id = 1;
        $contacts = $this->obj->getContactList();

        $this->assertEquals(1,                      count($contacts));
        $this->assertEquals(1,                      $contacts[1]['contact_id']);
        $this->assertEquals('Admin',                $contacts[1]['contact_first_name']);
        $this->assertEquals('Person',               $contacts[1]['contact_last_name']);
        $this->assertEquals('',                     $contacts[1]['contact_order_by']);
        $this->assertEquals('',                     $contacts[1]['dept_name']);
    }

    /**
     * Test finding of departments of project
     *
     * @expectedException PHPUnit_Framework_Error
     */
    public function testGetDepartments()
    {
        $departments = CProject::getDepartments(null, 1);
        /*
         * Beyond the deprecation notice, nothing else should be tested here. The
         *   useful test is CProject->testGetDepartmentList().
         */
    }

    /**
     * Test finding of departments of project
     *
     */
    public function testGetDepartmentList()
    {
        $this->mockDB->stageHashList(1,
                array('dept_id' => 1, 'dept_name' => 'Department 1', 'dept_phone' => ''));
        $this->mockDB->stageHashList(2,
                array('dept_id' => 2, 'dept_name' => 'Department 2', 'dept_phone' => ''));

        $this->obj->project_id = 1;
        $departments = $this->obj->getDepartmentList();

        if ($this->_AppUI->isActiveModule('departments')) {
            $this->assertEquals(2,              count($departments));
            $this->assertEquals(1,              $departments[1]['dept_id']);
            $this->assertEquals('Department 1', $departments[1]['dept_name']);
            $this->assertEquals('',             $departments[1]['dept_phone']);
            $this->assertEquals(2,              $departments[2]['dept_id']);
            $this->assertEquals('Department 2', $departments[2]['dept_name']);
            $this->assertEquals('',             $departments[2]['dept_phone']);
        } else {
            $this->assertEquals(0,              count($departments));
        }
    }

    /**
     * Tests finding of forums of project
     */
    public function testGetForums()
    {
        $this->mockDB->stageHashList(1,
                array('forum_id' => 1, 'forum_name' => 'Test Forum',
                    'forum_project' => 1, 'forum_owner' => 1,
                    'forum_message_count' => 1, 'forum_last_date' => '04-Aug-2009 17:03',
                    'project_id' => 1, 'project_name' => 'Test Project'));

        $this->obj->project_id = 1;
        $forums = $this->obj->getForumList();

        if ($this->_AppUI->isActiveModule('forums')) {
            $this->assertEquals(1,                  count($forums));
            $this->assertEquals(1,                  $forums[1]['forum_id']);
            $this->assertEquals(1,                  $forums[1]['forum_project']);
            $this->assertEquals(1,                  $forums[1]['forum_owner']);
            $this->assertEquals('Test Forum',       $forums[1]['forum_name']);
            $this->assertEquals(1,                  $forums[1]['forum_message_count']);
            $this->assertEquals('04-Aug-2009 17:03',$forums[1]['forum_last_date']);
            $this->assertEquals('Test Project',     $forums[1]['project_name']);
            $this->assertEquals(1,                  $forums[1]['project_id']);
        } else {
            $this->assertEquals(0,                  count($forums));
        }
    }

    /**
     * Tests finding if project id passed has children
     */
    public function testHasChildProjectsWithArg()
    {
        $this->mockDB->stageResult(3);
        $has_children = $this->obj->hasChildProjects(1);

        $this->assertEquals(3, $has_children);
    }

    /**
     * Tests finding if project has children if project is loaded and no argument passed
     */
    public function testHasChildProjects()
    {
        $this->mockDB->stageHash(array(
            'project_id' => 1, 'project_original_parent' => 1
        ));
        $this->mockDB->stageResult(3);

        $this->obj->load(1);
        $has_children = $this->obj->hasChildProjects();

        $this->assertEquals(3, $has_children);
    }

    /**
     * Tests finding if project has children if no project loaded and no argument passed
     */
    public function testHasChildProjectNoProjectID()
    {
        $this->mockDB->stageResult(0);
        $has_children = $this->obj->hasChildProjects();

        $this->assertEquals(0, $has_children);
    }

    /**
     * Tests getting task logs with no filters passed
     */
    public function testGetTaskLogsNoArgs()
    {
        $this->mockDB->stageList(
                array('task_log_id' => 1, 'task_log_task' => 1, 'task_log_description' => 'Task Log 1',
                    'user_username' => 'admin', 'real_name' => 'Admin Person'));
        $this->mockDB->stageList(
                array('task_log_id' => 2, 'task_log_task' => 1, 'task_log_description' => 'Task Log 2',
                    'user_username' => 'admin', 'real_name' => 'Admin Person'));
        $this->mockDB->stageList(
                array('task_log_id' => 3, 'task_log_task' => 2, 'task_log_description' => 'Task Log 3',
                    'user_username' => 'admin', 'real_name' => 'Admin Person'));
        $this->mockDB->stageList(
                array('task_log_id' => 4, 'task_log_task' => 2, 'task_log_description' => 'Task Log 4',
                    'user_username' => 'another_admin', 'real_name' => 'Contact Number 1'));

        $task_logs = $this->obj->getTaskLogs(null, 1);

        $this->assertEquals(4,                  count($task_logs));
        $this->assertEquals(1,                  $task_logs[0]['task_log_id']);
        $this->assertEquals(1,                  $task_logs[0]['task_log_task']);
        $this->assertEquals('Task Log 1',       $task_logs[0]['task_log_description']);
        $this->assertEquals('admin',            $task_logs[0]['user_username']);
        $this->assertEquals('Admin Person',     $task_logs[0]['real_name']);
        $this->assertEquals(2,                  $task_logs[1]['task_log_id']);
        $this->assertEquals(1,                  $task_logs[1]['task_log_task']);
        $this->assertEquals('Task Log 2',       $task_logs[1]['task_log_description']);
        $this->assertEquals('admin',            $task_logs[1]['user_username']);
        $this->assertEquals('Admin Person',     $task_logs[1]['real_name']);
        $this->assertEquals(3,                  $task_logs[2]['task_log_id']);
        $this->assertEquals(2,                  $task_logs[2]['task_log_task']);
        $this->assertEquals('Task Log 3',       $task_logs[2]['task_log_description']);
        $this->assertEquals('admin',            $task_logs[2]['user_username']);
        $this->assertEquals('Admin Person',     $task_logs[2]['real_name']);
        $this->assertEquals(4,                  $task_logs[3]['task_log_id']);
        $this->assertEquals(2,                  $task_logs[3]['task_log_task']);
        $this->assertEquals('Task Log 4',       $task_logs[3]['task_log_description']);
        $this->assertEquals('another_admin',    $task_logs[3]['user_username']);
        $this->assertEquals('Contact Number 1', $task_logs[3]['real_name']);
    }

    /**
     * Tests getting task logs with user id passed
     */
    public function testGetTaskLogsUserID()
    {
        $this->mockDB->stageList(
                array('task_log_id' => 4, 'task_log_task' => 2, 'task_log_description' => 'Task Log 4',
                    'user_username' => 'another_admin', 'real_name' => 'Contact Number 1'));
        $task_logs = $this->obj->getTaskLogs(null, 1, 2);

        $this->assertEquals(1,                  count($task_logs));
        $this->assertEquals(4,                  $task_logs[0]['task_log_id']);
        $this->assertEquals(2,                  $task_logs[0]['task_log_task']);
        $this->assertEquals('Task Log 4',       $task_logs[0]['task_log_description']);
        $this->assertEquals('another_admin',    $task_logs[0]['user_username']);
        $this->assertEquals('Contact Number 1', $task_logs[0]['real_name']);
    }

    /**
     * Tests getting task logs, hiding inactive
     */
    public function testGetTaskLogsHideInactive()
    {
        $this->mockDB->stageList(
                array('task_log_id' => 1, 'task_log_task' => 1, 'task_log_description' => 'Task Log 1',
                    'user_username' => 'admin', 'real_name' => 'Admin Person'));
        $this->mockDB->stageList(
                array('task_log_id' => 2, 'task_log_task' => 1, 'task_log_description' => 'Task Log 2',
                    'user_username' => 'admin', 'real_name' => 'Admin Person'));
        $task_logs = $this->obj->getTaskLogs(null, 1, 0, true);

        $this->assertEquals(2,                  count($task_logs));
        $this->assertEquals(1,                  $task_logs[0]['task_log_id']);
        $this->assertEquals(1,                  $task_logs[0]['task_log_task']);
        $this->assertEquals('Task Log 1',       $task_logs[0]['task_log_description']);
        $this->assertEquals('admin',            $task_logs[0]['user_username']);
        $this->assertEquals('Admin Person',     $task_logs[0]['real_name']);
        $this->assertEquals(2,                  $task_logs[1]['task_log_id']);
        $this->assertEquals(1,                  $task_logs[1]['task_log_task']);
        $this->assertEquals('Task Log 2',       $task_logs[1]['task_log_description']);
        $this->assertEquals('admin',            $task_logs[1]['user_username']);
        $this->assertEquals('Admin Person',     $task_logs[1]['real_name']);
    }

    /**
     * Tests getting task logs, hiding completed tasks
     */
    public function testGetTaskLogsHideComplete()
    {
        $this->mockDB->stageList(
                array('task_log_id' => 1, 'task_log_task' => 1, 'task_log_description' => 'Task Log 1',
                    'user_username' => 'admin', 'real_name' => 'Admin Person'));
        $this->mockDB->stageList(
                array('task_log_id' => 2, 'task_log_task' => 1, 'task_log_description' => 'Task Log 2',
                    'user_username' => 'admin', 'real_name' => 'Admin Person'));
        $task_logs = $this->obj->getTaskLogs(null, 1, 0, false, true);

        $this->assertEquals(2,                  count($task_logs));
        $this->assertEquals(1,                  $task_logs[0]['task_log_id']);
        $this->assertEquals(1,                  $task_logs[0]['task_log_task']);
        $this->assertEquals('Task Log 1',       $task_logs[0]['task_log_description']);
        $this->assertEquals('admin',            $task_logs[0]['user_username']);
        $this->assertEquals('Admin Person',     $task_logs[0]['real_name']);
        $this->assertEquals(2,                  $task_logs[1]['task_log_id']);
        $this->assertEquals(1,                  $task_logs[1]['task_log_task']);
        $this->assertEquals('Task Log 2',       $task_logs[1]['task_log_description']);
        $this->assertEquals('admin',            $task_logs[1]['user_username']);
        $this->assertEquals('Admin Person',     $task_logs[1]['real_name']);
    }

    /**
     * Tests getting task logs, hiding completed tasks
     */
    public function testGetTaskLogsWithCostCode()
    {
        $this->mockDB->stageList(
                array('task_log_id' => 4, 'task_log_task' => 2, 'task_log_description' => 'Task Log 4',
                    'user_username' => 'another_admin', 'real_name' => 'Contact Number 1'));
        $task_logs = $this->obj->getTaskLogs(null, 1, 0, false, false, 2);

        $this->assertEquals(1,                  count($task_logs));
        $this->assertEquals(4,                  $task_logs[0]['task_log_id']);
        $this->assertEquals(2,                  $task_logs[0]['task_log_task']);
        $this->assertEquals('Task Log 4',       $task_logs[0]['task_log_description']);
        $this->assertEquals('another_admin',    $task_logs[0]['user_username']);
        $this->assertEquals('Contact Number 1', $task_logs[0]['real_name']);
    }

    /**
     * Tests getting projects from outside project class
     */
    public function testGetProjects()
    {
        $this->mockDB->stageHashList(1,
                array('project_id' => 1, 'project_name' => 'Test Project', 'project_parent' => 1));
        $this->mockDB->stageHashList(2,
                array('project_id' => 2, 'project_name' => 'Test Project 2', 'project_parent' => 1));
        $this->mockDB->stageHashList(3,
                array('project_id' => 3, 'project_name' => 'Test Project 3', 'project_parent' => 1));
        $this->mockDB->stageHashList(4,
                array('project_id' => 4, 'project_name' => 'Test Project 4', 'project_parent' => 1));
        $projects = $this->obj->getProjects();

        $this->assertEquals(4,                  count($projects));
        $this->assertEquals(1,                  $projects[1]['project_id']);
        $this->assertEquals('Test Project',     $projects[1]['project_name']);
        $this->assertEquals(1,                  $projects[1]['project_parent']);
        $this->assertEquals(2,                  $projects[2]['project_id']);
        $this->assertEquals('Test Project 2',   $projects[2]['project_name']);
        $this->assertEquals(1,                  $projects[2]['project_parent']);
        $this->assertEquals(3,                  $projects[3]['project_id']);
        $this->assertEquals('Test Project 3',   $projects[3]['project_name']);
        $this->assertEquals(1,                  $projects[3]['project_parent']);
        $this->assertEquals(4,                  $projects[4]['project_id']);
        $this->assertEquals('Test Project 4',   $projects[4]['project_name']);
        $this->assertEquals(1,                  $projects[4]['project_parent']);
    }

    /**
     * Tests find_proj_child with no level passed
     */
    public function testFindProjChildNoLevel()
    {
        $this->mockDB->stageList(
                array('project_id' => 1, 'project_name' => 'Test Project', 'project_parent' => 1));
        $this->mockDB->stageList(
                array('project_id' => 1, 'project_name' => 'Test Project', 'project_parent' => 1));
        $this->mockDB->stageList(
                array('project_id' => 2, 'project_name' => 'Test Project 2', 'project_parent' => 1));
        $this->mockDB->stageList(
                array('project_id' => 3, 'project_name' => 'Test Project 3', 'project_parent' => 1));
        $this->mockDB->stageList(
                array('project_id' => 4, 'project_name' => 'Test Project 4', 'project_parent' => 1));
        $st_projects = $this->mockDB->loadList();

        $this->obj->find_proj_child($st_projects, 1);

        $this->assertEquals(5,                  count($st_projects));
        $this->assertEquals(3,                  count($st_projects[0]));
        $this->assertEquals(3,                  count($st_projects[1]));
        $this->assertEquals(3,                  count($st_projects[2]));
        $this->assertEquals(3,                  count($st_projects[3]));
        $this->assertEquals(3,                  count($st_projects[4]));
        $this->assertEquals(1,                  $st_projects[0]['project_id']);
        $this->assertEquals('Test Project',     $st_projects[0]['project_name']);
        $this->assertEquals(1,                  $st_projects[0]['project_parent']);
        $this->assertEquals(1,                  $st_projects[1]['project_id']);
        $this->assertEquals('Test Project',     $st_projects[1]['project_name']);
        $this->assertEquals(1,                  $st_projects[1]['project_parent']);
        $this->assertEquals(2,                  $st_projects[2]['project_id']);
        $this->assertEquals('Test Project 2',   $st_projects[2]['project_name']);
        $this->assertEquals(1,                  $st_projects[2]['project_parent']);
        $this->assertEquals(3,                  $st_projects[3]['project_id']);
        $this->assertEquals('Test Project 3',   $st_projects[3]['project_name']);
        $this->assertEquals(1,                  $st_projects[3]['project_parent']);
        $this->assertEquals(4,                  $st_projects[4]['project_id']);
        $this->assertEquals('Test Project 4',   $st_projects[4]['project_name']);
        $this->assertEquals(1,                  $st_projects[4]['project_parent']);
    }

    /**
     * Test find_proj_child with passing in a level
     */
    public function testFindProjChildWithLevel()
    {
        global $st_projects_arr;
        $st_projects_arr = array();

        $this->mockDB->stageList(
                array('project_id' => 1, 'project_name' => 'Test Project', 'project_parent' => 1));
        $this->mockDB->stageList(
                array('project_id' => 1, 'project_name' => 'Test Project', 'project_parent' => 1));
        $this->mockDB->stageList(
                array('project_id' => 2, 'project_name' => 'Test Project 2', 'project_parent' => 1));
        $this->mockDB->stageList(
                array('project_id' => 3, 'project_name' => 'Test Project 3', 'project_parent' => 1));
        $this->mockDB->stageList(
                array('project_id' => 4, 'project_name' => 'Test Project 4', 'project_parent' => 1));
        $st_projects = $this->mockDB->loadList();

        $this->obj->find_proj_child($st_projects, 1, 2);

        $this->assertEquals(5,                  count($st_projects));
        $this->assertEquals(3,                  count($st_projects[0]));
        $this->assertEquals(3,                  count($st_projects[1]));
        $this->assertEquals(3,                  count($st_projects[2]));
        $this->assertEquals(3,                  count($st_projects[3]));
        $this->assertEquals(3,                  count($st_projects[4]));
        $this->assertEquals(1,                  $st_projects[0]['project_id']);
        $this->assertEquals('Test Project',     $st_projects[0]['project_name']);
        $this->assertEquals(1,                  $st_projects[0]['project_parent']);
        $this->assertEquals(1,                  $st_projects[1]['project_id']);
        $this->assertEquals('Test Project',     $st_projects[1]['project_name']);
        $this->assertEquals(1,                  $st_projects[1]['project_parent']);
        $this->assertEquals(2,                  $st_projects[2]['project_id']);
        $this->assertEquals('Test Project 2',   $st_projects[2]['project_name']);
        $this->assertEquals(1,                  $st_projects[2]['project_parent']);
        $this->assertEquals(3,                  $st_projects[3]['project_id']);
        $this->assertEquals('Test Project 3',   $st_projects[3]['project_name']);
        $this->assertEquals(1,                  $st_projects[3]['project_parent']);
        $this->assertEquals(4,                  $st_projects[4]['project_id']);
        $this->assertEquals('Test Project 4',   $st_projects[4]['project_name']);
        $this->assertEquals(1,                  $st_projects[4]['project_parent']);
    }

    /**
     * Tests getStructuredProjects passing no args
     */
    public function testGetStructedProjectsNoArgs()
    {
        $this->mockDB->stageList(
                array('project_id' => 1, 'project_name' => 'Test Project', 'project_parent' => 1));
        $this->mockDB->stageList(
                array('project_id' => 2, 'project_name' => 'Test Project 2', 'project_parent' => 1));
        $this->mockDB->stageList(
                array('project_id' => 3, 'project_name' => 'Test Project 3', 'project_parent' => 1));
        $this->mockDB->stageList(
                array('project_id' => 4, 'project_name' => 'Test Project 4', 'project_parent' => 1));
        $st_projects_arr = $this->obj->getStructuredProjects();

        $this->assertEquals(4,                  count($st_projects_arr));
        $this->assertEquals(3,                  count($st_projects_arr[0][0]));
        $this->assertEquals(3,                  count($st_projects_arr[1][0]));
        $this->assertEquals(3,                  count($st_projects_arr[2][0]));
        $this->assertEquals(1,                  $st_projects_arr[0][0]['project_id']);
        $this->assertEquals('Test Project',     $st_projects_arr[0][0]['project_name']);
        $this->assertEquals(1,                  $st_projects_arr[0][0]['project_parent']);
        $this->assertEquals(0,                  $st_projects_arr[0][1]);
        $this->assertEquals(2,                  $st_projects_arr[1][0]['project_id']);
        $this->assertEquals('Test Project 2',   $st_projects_arr[1][0]['project_name']);
        $this->assertEquals(1,                  $st_projects_arr[1][0]['project_parent']);
        $this->assertEquals(1,                  $st_projects_arr[1][1]);
        $this->assertEquals(3,                  $st_projects_arr[2][0]['project_id']);
        $this->assertEquals('Test Project 3',   $st_projects_arr[2][0]['project_name']);
        $this->assertEquals(1,                  $st_projects_arr[2][0]['project_parent']);
        $this->assertEquals(1,                  $st_projects_arr[2][1]);
        $this->assertEquals(4,                  $st_projects_arr[3][0]['project_id']);
        $this->assertEquals('Test Project 4',   $st_projects_arr[3][0]['project_name']);
        $this->assertEquals(1,                  $st_projects_arr[3][0]['project_parent']);
        $this->assertEquals(1,                  $st_projects_arr[3][1]);
    }

    /**
     * Test getting structured projects with a specific original project id
     */
    public function testGetStructuredProjectsOriginalProjectID()
    {
        $this->mockDB->stageList(
                array('project_id' => 1, 'project_name' => 'Test Project', 'project_parent' => 1));
        $this->mockDB->stageList(
                array('project_id' => 2, 'project_name' => 'Test Project 2', 'project_parent' => 1));
        $this->mockDB->stageList(
                array('project_id' => 3, 'project_name' => 'Test Project 3', 'project_parent' => 1));
        $this->mockDB->stageList(
                array('project_id' => 4, 'project_name' => 'Test Project 4', 'project_parent' => 1));
        $st_projects_arr = $this->obj->getStructuredProjects(1);

        $this->assertEquals(4,                  count($st_projects_arr));
        $this->assertEquals(3,                  count($st_projects_arr[0][0]));
        $this->assertEquals(3,                  count($st_projects_arr[1][0]));
        $this->assertEquals(3,                  count($st_projects_arr[2][0]));
        $this->assertEquals(3,                  count($st_projects_arr[3][0]));
        $this->assertEquals(1,                  $st_projects_arr[0][0]['project_id']);
        $this->assertEquals('Test Project',     $st_projects_arr[0][0]['project_name']);
        $this->assertEquals(1,                  $st_projects_arr[0][0]['project_parent']);
        $this->assertEquals(0,                  $st_projects_arr[0][1]);
        $this->assertEquals(2,                  $st_projects_arr[1][0]['project_id']);
        $this->assertEquals('Test Project 2',   $st_projects_arr[1][0]['project_name']);
        $this->assertEquals(1,                  $st_projects_arr[1][0]['project_parent']);
        $this->assertEquals(1,                  $st_projects_arr[1][1]);
        $this->assertEquals(3,                  $st_projects_arr[2][0]['project_id']);
        $this->assertEquals('Test Project 3',   $st_projects_arr[2][0]['project_name']);
        $this->assertEquals(1,                  $st_projects_arr[2][0]['project_parent']);
        $this->assertEquals(1,                  $st_projects_arr[2][1]);
        $this->assertEquals(4,                  $st_projects_arr[3][0]['project_id']);
        $this->assertEquals('Test Project 4',   $st_projects_arr[3][0]['project_name']);
        $this->assertEquals(1,                  $st_projects_arr[3][0]['project_parent']);
        $this->assertEquals(1,                  $st_projects_arr[3][1]);
    }

    /**
     * Tests getting structured projects that are active
     */
    public function testGetStructedProjectsActiveOnly()
    {
        $this->mockDB->stageList(
                array('project_id' => 1, 'project_name' => 'Test Project',
                    'project_parent' => 1));

        $this->obj->project_original_parent = 0;
        $this->obj->project_status = -1;
        $result = $this->obj->getStructuredProjects(true);

        $this->assertEquals(1,              count($result));
        $this->assertEquals(3,              count($result[0][0]));
        $this->assertEquals(1,              $result[0][0]['project_id']);
        $this->assertEquals('Test Project', $result[0][0]['project_name']);
        $this->assertEquals(1,              $result[0][0]['project_parent']);
    }

    /**
     * Tests getting index of project in an array
     */
    public function testGetProjectIndex()
    {
        $array = array(
            0 => array('project_id' => 1),
            1 => array('project_id' => 2),
            2 => array('project_id' => 3)
        );

        $project_index = getProjectIndex($array, 2);

        $this->assertEquals(1, $project_index);
    }
}
