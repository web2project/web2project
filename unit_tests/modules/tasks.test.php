<?php
/**
 * Necessary global variables
 */
global $db;
global $ADODB_FETCH_MODE;
global $w2p_performance_dbtime;
global $w2p_performance_old_dbqueries;
global $AppUI;
global $tracking_dynamics;

require_once '../base.php';
require_once W2P_BASE_DIR . '/includes/config.php';
require_once W2P_BASE_DIR . '/includes/main_functions.php';
require_once W2P_BASE_DIR . '/includes/db_adodb.php';

/*
 * Need this to test actions that require permissions.
 */
$AppUI  = new CAppUI;
$_POST['login'] = 'login';
$_REQUEST['login'] = 'sql';
$AppUI->login('admin', 'passwd');
$tracking_dynamics = array('0' => '21', '1' => '31');

require_once W2P_BASE_DIR . '/includes/session.php';
require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Extensions/Database/TestCase.php';

/**
 * TaskTest Class.
 *
 * Class to test the tasks class
 * @author Trevor Morse<trevor.morse@gmail.com>
 * @package web2project
 * @subpackage unit_tests
 */
class Tasks_Test extends PHPUnit_Extensions_Database_TestCase
{

    protected $backupGlobals = FALSE;
	protected $obj = null;
	protected $post_data = array();

    /**
     * Return database connection for tests
     */
    protected function getConnection()
    {
        $pdo = new PDO(w2PgetConfig('dbtype') . ':host=' .
                       w2PgetConfig('dbhost') . ';dbname=' .
                       w2PgetConfig('dbname'),
                       w2PgetConfig('dbuser'), w2PgetConfig('dbpass'));
        return $this->createDefaultDBConnection($pdo, w2PgetConfig('dbname'));
    }

    /**
     * Set up default dataset for testing
     */
    protected function getDataSet()
    {
        return $this->createXMLDataSet($this->getDataSetPath().'tasksSeed.xml');
    }
    protected function getDataSetPath()
    {
    	return dirname(dirname(__FILE__)).'/db_files/';
    }

	public function setUp()
	{
		parent::setUp();

		$this->obj = new CTask();

		$this->post_data = array (
			'dosql'                 => 'do_task_aed',
            'task_id'               => 0,
            'task_project'          => 1,
            'task_contacts'         => null,
            'task_name'             => 'Test Task',
            'task_status'           => 0,
            'task_priority'         => 0,
            'task_percent_complete' => 0,
            'task_owner'            => 1,
            'task_access'           => 0,
            'task_related_url'      => 'http://www.example.org',
            'task_type'             => 0,
            'dept_ids'              => array(1),
            'task_parent'           => 0,
            'task_target_budget'    => '1.00',
            'task_description'      => 'this is a description for test task.',
            'task_start_date'       => '200908240800',
            'start_date'            => '24/Aug/2009',
            'start_hour'            => '08',
            'start_minute'          => '00',
            'start_hour_ampm'       => 'pm',
            'task_end_date'         => '200908261700',
            'end_date'              => '26/Aug/2009',
            'end_hour'              => 17,
            'end_minute'            => 00,
            'end_hour_ampm'         => 'pm',
            'task_duration'         => 3,
            'task_duration_type'    => 1,
            'task_dynamic'          => 1,
            'hdependencies'         => null,
            'hperc_assign'          => '1=100;',
            'percentage_assignment' => 100,
            'email_comment'         => '',
            'task_notify'           => 1,
            'hassign'               => 1,
            'hresource_assign'      => '',
            'resource_assignment'   => 100
		);
	}

	public function tearDown()
	{
		parent::tearDown();

		unset($this->obj, $this->post_data);
	}

    /**
     * Tests the Attributes of a new Tasks object.
     */
    public function testNewTasksAttributes()
    {
        $this->assertType('CTask',                                  $this->obj);
        $this->assertObjectHasAttribute('task_id',                  $this->obj);
        $this->assertObjectHasAttribute('task_name',                $this->obj);
        $this->assertObjectHasAttribute('task_parent',              $this->obj);
        $this->assertObjectHasAttribute('task_milestone',           $this->obj);
        $this->assertObjectHasAttribute('task_project',             $this->obj);
        $this->assertObjectHasAttribute('task_owner',               $this->obj);
        $this->assertObjectHasAttribute('task_start_date',          $this->obj);
        $this->assertObjectHasAttribute('task_duration',            $this->obj);
        $this->assertObjectHasAttribute('task_duration_type',       $this->obj);
        $this->assertObjectHasAttribute('task_hours_worked',        $this->obj);
        $this->assertObjectHasAttribute('task_end_date',            $this->obj);
        $this->assertObjectHasAttribute('task_status',              $this->obj);
        $this->assertObjectHasAttribute('task_priority',            $this->obj);
        $this->assertObjectHasAttribute('task_percent_complete',    $this->obj);
        $this->assertObjectHasAttribute('task_description',         $this->obj);
        $this->assertObjectHasAttribute('task_target_budget',       $this->obj);
        $this->assertObjectHasAttribute('task_related_url',         $this->obj);
        $this->assertObjectHasAttribute('task_creator',             $this->obj);
        $this->assertObjectHasAttribute('task_order',               $this->obj);
        $this->assertObjectHasAttribute('task_client_publish',      $this->obj);
        $this->assertObjectHasAttribute('task_dynamic',             $this->obj);
        $this->assertObjectHasAttribute('task_access',              $this->obj);
        $this->assertObjectHasAttribute('task_notify',              $this->obj);
        $this->assertObjectHasAttribute('task_departments',         $this->obj);
        $this->assertObjectHasAttribute('task_contacts',            $this->obj);
        $this->assertObjectHasAttribute('task_custom',              $this->obj);
        $this->assertObjectHasAttribute('task_type',                $this->obj);
        $this->assertObjectHasAttribute('_tbl_prefix',              $this->obj);
        $this->assertObjectHasAttribute('_tbl',                     $this->obj);
        $this->assertObjectHasAttribute('_tbl_key',                 $this->obj);
        $this->assertObjectHasAttribute('_error',                   $this->obj);
        $this->assertObjectHasAttribute('_query',                   $this->obj);
        $this->assertObjectHasAttribute('task_updator',             $this->obj);
        $this->assertObjectHasAttribute('task_created',             $this->obj);
        $this->assertObjectHasAttribute('task_updated',             $this->obj);
    }

    /**
     * Tests the Attribute Values of a new Task object.
     */
    public function testNewTasktAttributeValues()
    {
        $this->assertType('CTask', $this->obj);
        $this->assertNull($this->obj->task_id);
        $this->assertNull($this->obj->task_name);
        $this->assertNull($this->obj->task_parent);
        $this->assertNull($this->obj->task_milestone);
        $this->assertNull($this->obj->task_project);
        $this->assertNull($this->obj->task_owner);
        $this->assertNull($this->obj->task_start_date);
        $this->assertNull($this->obj->task_duration);
        $this->assertNull($this->obj->task_duration_type);
        $this->assertNull($this->obj->task_hours_worked);
        $this->assertNull($this->obj->task_end_date);
        $this->assertNull($this->obj->task_status);
        $this->assertNull($this->obj->task_priority);
        $this->assertNull($this->obj->task_percent_complete);
        $this->assertNull($this->obj->task_description);
        $this->assertNull($this->obj->task_target_budget);
        $this->assertNull($this->obj->task_related_url);
        $this->assertNull($this->obj->task_creator);
        $this->assertNull($this->obj->task_order);
        $this->assertNull($this->obj->task_client_publish);
        $this->assertNull($this->obj->task_dynamic);
        $this->assertNull($this->obj->task_access);
        $this->assertNull($this->obj->task_notify);
        $this->assertNull($this->obj->task_departments);
        $this->assertNull($this->obj->task_contancts);
        $this->assertNull($this->obj->task_custom);
        $this->assertNull($this->obj->task_type);
        $this->assertEquals('',         $this->obj->_tbl_prefix);
        $this->assertEquals('tasks',    $this->obj->_tbl);
        $this->assertEquals('task_id',  $this->obj->_tbl_key);
        $this->assertEquals('',         $this->obj->_errors);
        $this->assertType('DBQuery',    $this->obj->_query);
        $this->assertNull($this->obj->task_updator);
        $this->assertNull($this->obj->task_created);
        $this->assertNull($this->obj->task_updated);
    }

    /**
     * Tests the __toString function
     */
    public function test__toString()
    {
        $this->markTestSkipped('Function appears to be broken completely. Referencing variables that do not exist.');
    }

    /**
     * Tests the check function returns the proper error message when the task_id is null
     */
    public function testCheckTaskNoID()
    {
        $this->markTestSkipped('This test has been deprecated by casting the task_id via intval().');
    }

    /**
     * Tests the check function returns the proper error message when no name is passed
     */
    public function testCheckTaskNoName()
    {
		unset($this->post_data['task_name']);

        $this->obj->bind($this->post_data);
        $errorArray = $this->obj->check();

        $this->assertArrayHasKey('task_name', $errorArray);
    }

    /**
     * Tests the check function returns the proper error message when no priority is passed
     */
    public function testCheckTaskNoPriority()
    {
		unset($this->post_data['task_priority']);

        $this->obj->bind($this->post_data);
        $errorArray = $this->obj->check();

        $this->assertArrayHasKey('task_priority', $errorArray);
    }

	/**
     * Tests the check function returns the proper error message when no start date is passed
     */
    public function testCheckTaskNoStartDate()
    {
		unset($this->post_data['task_start_date']);
        $this->obj->bind($this->post_data);
        $errorArray = $this->obj->check();

        $this->assertArrayHasKey('task_start_date', $errorArray);
    }

	/**
     * Tests the check function returns the proper error message when no end date is passed
     */
    public function testCheckTaskNoEndDate()
    {
		unset($this->post_data['task_end_date']);

        $this->obj->bind($this->post_data);
        $errorArray = $this->obj->check();

        $this->assertArrayHasKey('task_end_date', $errorArray);
    }

    /**
     * Tests that the check function returns the proper error message
     * when attempting to change to dynamic and it has dependencies.
     */
    public function testCheckTaskDynamicWithDep()
    {
		$this->post_data['task_id'] = 3;

        $this->obj->bind($this->post_data);
        $errorMsg = $this->obj->check();

        $this->assertEquals('BadDep_DynNoDep', $errorMsg);
    }

    /**
     * Tests that the check function returns the proper error message
     * when attempting to update a task with a circular dependency.
     */
    public function testCheckTaskCircularDep()
    {
		$this->post_data['task_id'] 		= 3;
		$this->post_data['task_parent'] 	= 4;
		$this->post_data['task_dynamic']	= 0;

        $this->obj->bind($this->post_data);
        $errorMsg = $this->obj->check();

        $this->assertEquals('BadDep_CircularDep', $errorMsg[0]);
        $this->assertEquals('(4)', $errorMsg[1]);
    }

    /**
     * Tests that the check function returns the proper error message
     * when attempting to update a task when this tasks parent depends
     * on it.
     */
    public function testCheckTaskCannotDepOnParent()
    {
		$this->post_data['task_id'] 		= 5;
		$this->post_data['task_parent'] 	= 6;
		$this->post_data['task_dynamic']	= 0;

        $this->obj->bind($this->post_data);
        $errorMsg = $this->obj->check();

        $this->assertEquals('BadDep_CannotDependOnParent', $errorMsg);
    }

    /**
     * Tests that the check function returns the proper error message
     * when attempting to update a task when this tasks parent is a
     * child of it
     */
    public function testCheckTaskParentCannotBeChild()
    {
		$this->post_data['task_id'] 		= 6;
		$this->post_data['task_parent'] 	= 7;
		$this->post_data['task_dynamic']	= 0;

        $this->obj->bind($this->post_data);
        $errorMsg = $this->obj->check();

        $this->assertEquals('BadParent_CircularParent', $errorMsg);
    }

    /**
     * Tests that the check function returns the proper error message
     * when attempting to update a task when this tasks parent is a
     * child of it
     */
    public function testCheckTaskGrandParentCannotBeChild()
    {
		$this->post_data['task_id'] 		= 10;
		$this->post_data['task_parent'] 	= 9;
		$this->post_data['task_dynamic']	= 0;

        $this->obj->bind($this->post_data);
        $errorMsg = $this->obj->check();

        $this->assertEquals('BadParent_CircularGrandParent', $errorMsg[0]);
        $this->assertEquals('(8)', $errorMsg[1]);
    }

    /**
     * Tests that the check function returns the proper error message
     * when attempting to update a task when this tasks grand parent depends
     * on it.
     */
    public function testCheckTaskCannotDepOnGrandParent()
    {
		$this->post_data['task_id']			= 11;
		$this->post_data['task_parent']		= 10;
		$this->post_data['task_dynamic']	= 0;

        $this->obj->bind($this->post_data);
        $errorMsg = $this->obj->check();

        $this->assertEquals('BadDep_CircularGrandParent', $errorMsg[0]);
        $this->assertEquals('(9)', $errorMsg[1]);
    }

    /**
     * Tests that the check function returns the proper error message
     * when attempting to update a task when this tasks grand parent depends
     * on it.
     */
    public function testCheckTaskCircularDepOnParentDep()
    {
		$this->post_data['task_id']			= 12;
		$this->post_data['task_parent']		= 11;
		$this->post_data['task_dynamic']	= 0;

        $this->obj->bind($this->post_data);
        $errorMsg = $this->obj->check();

        $this->assertEquals('BadDep_CircularDepOnParentDependent', $errorMsg[0]);
        $this->assertEquals('(13)', $errorMsg[1]);
    }

    /**
     * Tests that the check function returns the proper error message
     * when attempting to update a task when this task is dynamic and
     * its children are dependendant on its parent
     */
    public function testCheckTaskChildDepOnParent()
    {
		$this->post_data['task_id']		= 16;
		$this->post_data['task_parent']	= 15;

        $this->obj->bind($this->post_data);
        $errorMsg = $this->obj->check();

        $this->assertEquals('BadParent_ChildDepOnParent', $errorMsg);
    }

    /**
     * And finally lets test that check returns nothing when there are
     * no problems with the task.
     */
    public function testCheck()
    {
		$this->post_data['task_id']		= 18;
		$this->post_data['task_parent']	= 18;

        $this->obj->bind($this->post_data);
        $errorMsg = $this->obj->check();

        $this->assertEquals(array(), $errorMsg);
    }

    /**
     * Testing loading a task that is not dynamic.
     */

    public function testLoad()
    {
        $this->obj->load(1);

        $this->assertEquals(1,                      $this->obj->task_id);
        $this->assertEquals('Task 1',               $this->obj->task_name);
        $this->assertEquals(0,                      $this->obj->task_parent);
        $this->assertEquals('',                     $this->obj->milestone);
        $this->assertEquals(1,                      $this->obj->task_project);
        $this->assertEquals(1,                      $this->obj->task_owner);
        $this->assertEquals('2009-07-05 00:00:00',  $this->obj->task_start_date);
        $this->assertEquals(2,                      $this->obj->task_duration);
        $this->assertEquals(24,                     $this->obj->task_duration_type);
        $this->assertEquals(0,                      $this->obj->task_hours_worked);
        $this->assertEquals('2009-07-15 00:00:00',  $this->obj->task_end_date);
        $this->assertEquals(0,                      $this->obj->task_status);
        $this->assertEquals(0,                      $this->obj->task_priority);
        $this->assertEquals(0,                      $this->obj->task_percent_complete);
        $this->assertEquals('This is task 1',       $this->obj->task_description);
        $this->assertEquals(0.00,                   $this->obj->task_target_budget);
        $this->assertEquals('',                     $this->obj->task_related_url);
        $this->assertEquals(1,                      $this->obj->task_creator);
        $this->assertEquals(1,                      $this->obj->task_order);
        $this->assertEquals(1,                      $this->obj->task_client_publish);
        $this->assertEquals(0,                      $this->obj->task_dynamic);
        $this->assertEquals(0,                      $this->obj->task_access);
        $this->assertEquals(1,                      $this->obj->task_notify);
        $this->assertEquals('',                     $this->obj->task_departments);
        $this->assertEquals('',                     $this->obj->task_contacts);
        $this->assertEquals('',                     $this->obj->task_custom);
        $this->assertEquals(1,                      $this->obj->task_type);
        $this->assertEquals(1,                      $this->obj->task_updator);
        $this->assertEquals('2009-07-05 15:43:00',  $this->obj->task_created);
        $this->assertEquals('2009-07-05 15:43:00',  $this->obj->task_updated);
    }

    /**
     * Tests loading a task that is dynamic skipping update.
     */
    public function testLoadDynamicSkipUpdate()
    {
        $this->obj->load(18, false, true);

        $this->assertEquals(18,                     $this->obj->task_id);
        $this->assertEquals('Task 18',              $this->obj->task_name);
        $this->assertEquals(18,                     $this->obj->task_parent);
        $this->assertEquals(0,                      $this->obj->task_milestone);
        $this->assertEquals(1,                      $this->obj->task_project);
        $this->assertEquals(1,                      $this->obj->task_owner);
        $this->assertEquals('2009-07-06 00:00:00',  $this->obj->task_start_date);
        $this->assertEquals(2,                      $this->obj->task_duration);
        $this->assertEquals(24,                     $this->obj->task_duration_type);
        $this->assertEquals(0,                      $this->obj->task_hours_worked);
        $this->assertEquals('2009-07-16 00:00:00',  $this->obj->task_end_date);
        $this->assertEquals(0,                      $this->obj->task_status);
        $this->assertEquals(0,                      $this->obj->task_priority);
        $this->assertEquals(0,                      $this->obj->task_percent_complete);
        $this->assertEquals('This is task 18',      $this->obj->task_description);
        $this->assertEquals(0.00,                   $this->obj->task_target_budget);
        $this->assertEquals('',                     $this->obj->task_related_url);
        $this->assertEquals(1,                      $this->obj->task_creator);
        $this->assertEquals(1,                      $this->obj->task_order);
        $this->assertEquals(1,                      $this->obj->task_client_publish);
        $this->assertEquals(1,                      $this->obj->task_dynamic);
        $this->assertEquals(1,                      $this->obj->task_access);
        $this->assertEquals(1,                      $this->obj->task_notify);
        $this->assertEquals('',                     $this->obj->task_departments);
        $this->assertEquals('',                     $this->obj->task_contacts);
        $this->assertEquals('',                     $this->obj->task_custom);
        $this->assertEquals(1,                      $this->obj->task_type);
        $this->assertEquals(1,                      $this->obj->task_updator);
        $this->assertEquals('2009-07-06 15:43:00',  $this->obj->task_created);
        $this->assertEquals('2009-07-06 15:43:00',  $this->obj->task_updated);
    }

    /*
     * Tests loading a task that is dynamic not skipping update.
     */
    public function testLoadDynamic()
    {
        $new_task = $this->obj->load(18, false, false);

        $this->assertEquals(18,                     $this->obj->task_id);
        $this->assertEquals('Task 18',              $this->obj->task_name);
        $this->assertEquals(18,                     $this->obj->task_parent);
        $this->assertEquals(0,                      $this->obj->task_milestone);
        $this->assertEquals(1,                      $this->obj->task_project);
        $this->assertEquals(1,                      $this->obj->task_owner);
        $this->assertEquals('2009-07-06 00:00:00',  $this->obj->task_start_date);
        $this->assertEquals(4,                      $this->obj->task_duration);
        $this->assertEquals(24,                     $this->obj->task_duration_type);
        $this->assertEquals(0,                      $this->obj->task_hours_worked);
        $this->assertEquals('2009-07-16 00:00:00',  $this->obj->task_end_date);
        $this->assertEquals(0,                      $this->obj->task_status);
        $this->assertEquals(0,                      $this->obj->task_priority);
        $this->assertEquals(0,                      $this->obj->task_percent_complete);
        $this->assertEquals('This is task 18',      $this->obj->task_description);
        $this->assertEquals(0.00,                   $this->obj->task_target_budget);
        $this->assertEquals('',                     $this->obj->task_related_url);
        $this->assertEquals(1,                      $this->obj->task_creator);
        $this->assertEquals(1,                      $this->obj->task_order);
        $this->assertEquals(1,                      $this->obj->task_client_publish);
        $this->assertEquals(1,                      $this->obj->task_dynamic);
        $this->assertEquals(1,                      $this->obj->task_access);
        $this->assertEquals(1,                      $this->obj->task_notify);
        $this->assertEquals('',                     $this->obj->task_departments);
        $this->assertEquals('',                     $this->obj->task_contacts);
        $this->assertEquals('',                     $this->obj->task_custom);
        $this->assertEquals(1,                      $this->obj->task_type);
        $this->assertEquals(1,                      $this->obj->task_updator);
        $this->assertEquals('2009-07-06 15:43:00',  $this->obj->task_created);
        $this->assertEquals('2009-07-06 15:43:00',  $this->obj->task_updated);
    }

    /**
     * Tests the fullLoad function.
     */
    public function testFullLoad()
    {
        $this->markTestSkipped('Deprecated, simply calls loadFull, tested below.');
    }

    /**
     * Test loadFull funtion which includes details about project and contacts
     * as well as task information.
     */
    public function testLoadFull()
    {
      	global $AppUI;

        $this->obj->loadFull($AppUI, 18);

        $this->assertEquals(18,                     $this->obj->task_id);
        $this->assertEquals('Task 18',              $this->obj->task_name);
        $this->assertEquals(18,                     $this->obj->task_parent);
        $this->assertEquals(0,                      $this->obj->task_milestone);
        $this->assertEquals(1,                      $this->obj->task_project);
        $this->assertEquals(1,                      $this->obj->task_owner);
        $this->assertEquals('2009-07-06 00:00:00',  $this->obj->task_start_date);
        $this->assertEquals(2,                      $this->obj->task_duration);
        $this->assertEquals(24,                     $this->obj->task_duration_type);
        $this->assertEquals(0,                      $this->obj->task_hours_worked);
        $this->assertEquals('2009-07-16 00:00:00',  $this->obj->task_end_date);
        $this->assertEquals(0,                      $this->obj->task_status);
        $this->assertEquals(0,                      $this->obj->task_priority);
        $this->assertEquals(0,                      $this->obj->task_percent_complete);
        $this->assertEquals('This is task 18',      $this->obj->task_description);
        $this->assertEquals(0.00,                   $this->obj->task_target_budget);
        $this->assertEquals('',                     $this->obj->task_related_url);
        $this->assertEquals(1,                      $this->obj->task_creator);
        $this->assertEquals(1,                      $this->obj->task_order);
        $this->assertEquals(1,                      $this->obj->task_client_publish);
        $this->assertEquals(1,                      $this->obj->task_dynamic);
        $this->assertEquals(1,                      $this->obj->task_access);
        $this->assertEquals(1,                      $this->obj->task_notify);
        $this->assertEquals('',                     $this->obj->task_departments);
        $this->assertEquals('',                     $this->obj->task_contacts);
        $this->assertEquals('',                     $this->obj->task_custom);
        $this->assertEquals(1,                      $this->obj->task_type);
        $this->assertEquals(1,                      $this->obj->task_updator);
        $this->assertEquals('2009-07-06 15:43:00',  $this->obj->task_created);
        $this->assertEquals('2009-07-06 15:43:00',  $this->obj->task_updated);
        $this->assertEquals('Test Project',         $this->obj->project_name);
        $this->assertEquals('FFFFFF',               $this->obj->project_color_identifier);
        $this->assertEquals('Admin Person',         $this->obj->username);
    }

    /**
     * Tests that the peek function returns a task object that has
     * not had it's data updated if it is dynamic.
     */
    public function testPeek()
    {
        $task = $this->obj->peek(18);

        $this->assertEquals(18,                     $task->task_id);
        $this->assertEquals('Task 18',              $task->task_name);
        $this->assertEquals(18,                     $task->task_parent);
        $this->assertEquals(0,                      $task->task_milestone);
        $this->assertEquals(1,                      $task->task_project);
        $this->assertEquals(1,                      $task->task_owner);
        $this->assertEquals('2009-07-06 00:00:00',  $task->task_start_date);
        $this->assertEquals(2,                      $task->task_duration);
        $this->assertEquals(24,                     $task->task_duration_type);
        $this->assertEquals(0,                      $task->task_hours_worked);
        $this->assertEquals('2009-07-16 00:00:00',  $task->task_end_date);
        $this->assertEquals(0,                      $task->task_status);
        $this->assertEquals(0,                      $task->task_priority);
        $this->assertEquals(0,                      $task->task_percent_complete);
        $this->assertEquals('This is task 18',      $task->task_description);
        $this->assertEquals(0.00,                   $task->task_target_budget);
        $this->assertEquals('',                     $task->task_related_url);
        $this->assertEquals(1,                      $task->task_creator);
        $this->assertEquals(1,                      $task->task_order);
        $this->assertEquals(1,                      $task->task_client_publish);
        $this->assertEquals(1,                      $task->task_dynamic);
        $this->assertEquals(1,                      $task->task_access);
        $this->assertEquals(1,                      $task->task_notify);
        $this->assertEquals('',                     $task->task_departments);
        $this->assertEquals('',                     $task->task_contacts);
        $this->assertEquals('',                     $task->task_custom);
        $this->assertEquals(1,                      $task->task_type);
        $this->assertEquals(1,                      $task->task_updator);
        $this->assertEquals('2009-07-06 15:43:00',  $task->task_created);
        $this->assertEquals('2009-07-06 15:43:00',  $task->task_updated);
    }

    /**
     * Tests updating dynamic task when from children = true, in days
     */
    public function testUpdateDynamicsFromChildrenInDays()
    {
        $this->obj->load(21);
        $this->obj->updateDynamics(true);

        $this->assertEquals(21,                     $this->obj->task_id);
        $this->assertEquals('Task 21',              $this->obj->task_name);
        $this->assertEquals(21,                     $this->obj->task_parent);
        $this->assertEquals(0,                      $this->obj->task_milestone);
        $this->assertEquals(1,                      $this->obj->task_project);
        $this->assertEquals(1,                      $this->obj->task_owner);
        $this->assertEquals('2009-09-09 00:00:00',  $this->obj->task_start_date);
        $this->assertEquals(8,                      $this->obj->task_duration);
        $this->assertEquals(24,                     $this->obj->task_duration_type);
        $this->assertEquals(37,                     $this->obj->task_hours_worked);
        $this->assertEquals('2009-11-02 00:00:00',  $this->obj->task_end_date);
        $this->assertEquals(0,                      $this->obj->task_status);
        $this->assertEquals(0,                      $this->obj->task_priority);
        $this->assertEquals(41,                     $this->obj->task_percent_complete);
        $this->assertEquals('This is task 21',      $this->obj->task_description);
        $this->assertEquals(0.00,                   $this->obj->task_target_budget);
        $this->assertEquals('',                     $this->obj->task_related_url);
        $this->assertEquals(1,                      $this->obj->task_creator);
        $this->assertEquals(1,                      $this->obj->task_order);
        $this->assertEquals(1,                      $this->obj->task_client_publish);
        $this->assertEquals(1,                      $this->obj->task_dynamic);
        $this->assertEquals(1,                      $this->obj->task_access);
        $this->assertEquals(1,                      $this->obj->task_notify);
        $this->assertEquals('',                     $this->obj->task_departments);
        $this->assertEquals('',                     $this->obj->task_contacts);
        $this->assertEquals('',                     $this->obj->task_custom);
        $this->assertEquals(1,                      $this->obj->task_type);
        $this->assertEquals(1,                      $this->obj->task_updator);
        $this->assertEquals('2009-07-06 15:43:00',  $this->obj->task_created);
        $this->assertEquals('2009-07-06 15:43:00',  $this->obj->task_updated);
    }

    /**
     * Tests updating dynamic task when from children = false, in days
     */
    public function testUpdateDynamicsNotFromChildrenInDays()
    {
        $this->obj->load(22);
        $this->obj->updateDynamics(false);
        $this->obj->load(21);

        $now_secs = time();
        $min_time = $now_secs - 10;

        $this->assertEquals(21,                     $this->obj->task_id);
        $this->assertEquals('Task 21',              $this->obj->task_name);
        $this->assertEquals(21,                     $this->obj->task_parent);
        $this->assertEquals(0,                      $this->obj->task_milestone);
        $this->assertEquals(1,                      $this->obj->task_project);
        $this->assertEquals(1,                      $this->obj->task_owner);
        $this->assertEquals('2009-09-09 00:00:00',  $this->obj->task_start_date);
        $this->assertEquals(8,                      $this->obj->task_duration);
        $this->assertEquals(24,                     $this->obj->task_duration_type);
        $this->assertEquals(37,                     $this->obj->task_hours_worked);
        $this->assertEquals('2009-11-02 00:00:00',  $this->obj->task_end_date);
        $this->assertEquals(0,                      $this->obj->task_status);
        $this->assertEquals(0,                      $this->obj->task_priority);
        $this->assertEquals(41,                     $this->obj->task_percent_complete);
        $this->assertEquals('This is task 21',      $this->obj->task_description);
        $this->assertEquals(0.00,                   $this->obj->task_target_budget);
        $this->assertEquals('',                     $this->obj->task_related_url);
        $this->assertEquals(1,                      $this->obj->task_creator);
        $this->assertEquals(1,                      $this->obj->task_order);
        $this->assertEquals(1,                      $this->obj->task_client_publish);
        $this->assertEquals(1,                      $this->obj->task_dynamic);
        $this->assertEquals(1,                      $this->obj->task_access);
        $this->assertEquals(1,                      $this->obj->task_notify);
        $this->assertEquals('',                     $this->obj->task_departments);
        $this->assertEquals('',                     $this->obj->task_contacts);
        $this->assertEquals('',                     $this->obj->task_custom);
        $this->assertEquals(1,                      $this->obj->task_type);
        $this->assertEquals(1,                      $this->obj->task_updator);
        $this->assertEquals('2009-07-06 15:43:00',  $this->obj->task_created);
        $this->assertGreaterThanOrEqual($min_time,  strtotime($this->obj->task_updated));
        $this->assertLessThanOrEqual($now_secs,     strtotime($this->obj->task_updated));

        $xml_file_dataset = $this->createXMLDataSet($this->getDataSetPath().'tasksTestUpdateDynamicsNotFromChildrenInDays.xml');
        $xml_file_filtered_dataset = new PHPUnit_Extensions_Database_DataSet_DataSetFilter($xml_file_dataset, array('tasks' => array('task_updated')));
        $xml_db_dataset = $this->getConnection()->createDataSet();
        $xml_db_filtered_dataset = new PHPUnit_Extensions_Database_DataSet_DataSetFilter($xml_db_dataset, array('tasks' => array('task_updated')));
        $this->assertTablesEqual($xml_file_filtered_dataset->getTable('tasks'), $xml_db_filtered_dataset->getTable('tasks'));

        /**
         * Get updated dates to test against
         */
        $q = new DBQuery;
        $q->addTable('tasks');
        $q->addQuery('task_updated');
        $q->addWhere('task_id = 21');
        $results = $q->loadColumn();

        foreach($results as $updated) {
            $this->assertGreaterThanOrEqual($min_time, strtotime($updated));
            $this->assertLessThanOrEqual($now_secs, strtotime($updated));
        }
    }

    /**
     * Tests updating dynamic task when from children = true, in hours
     */
    public function testUpdateDynamicsFromChildrenInHours()
    {
        $this->obj->load(24);
        $this->obj->updateDynamics(true);

        $this->assertEquals(24,                     $this->obj->task_id);
        $this->assertEquals('Task 24',              $this->obj->task_name);
        $this->assertEquals(24,                     $this->obj->task_parent);
        $this->assertEquals(0,                      $this->obj->task_milestone);
        $this->assertEquals(1,                      $this->obj->task_project);
        $this->assertEquals(1,                      $this->obj->task_owner);
        $this->assertEquals('2009-09-09 00:00:00',  $this->obj->task_start_date);
        $this->assertEquals(64,                     $this->obj->task_duration);
        $this->assertEquals(1,                      $this->obj->task_duration_type);
        $this->assertEquals(37,                     $this->obj->task_hours_worked);
        $this->assertEquals('2009-11-02 00:00:00',  $this->obj->task_end_date);
        $this->assertEquals(0,                      $this->obj->task_status);
        $this->assertEquals(0,                      $this->obj->task_priority);
        $this->assertEquals(41,                     $this->obj->task_percent_complete);
        $this->assertEquals('This is task 24',      $this->obj->task_description);
        $this->assertEquals(0.00,                   $this->obj->task_target_budget);
        $this->assertEquals('',                     $this->obj->task_related_url);
        $this->assertEquals(1,                      $this->obj->task_creator);
        $this->assertEquals(1,                      $this->obj->task_order);
        $this->assertEquals(1,                      $this->obj->task_client_publish);
        $this->assertEquals(1,                      $this->obj->task_dynamic);
        $this->assertEquals(1,                      $this->obj->task_access);
        $this->assertEquals(1,                      $this->obj->task_notify);
        $this->assertEquals('',                     $this->obj->task_departments);
        $this->assertEquals('',                     $this->obj->task_contacts);
        $this->assertEquals('',                     $this->obj->task_custom);
        $this->assertEquals(1,                      $this->obj->task_type);
        $this->assertEquals(1,                      $this->obj->task_updator);
        $this->assertEquals('2009-07-06 15:43:00',  $this->obj->task_created);
        $this->assertEquals('2009-07-06 15:43:00',  $this->obj->task_updated);
    }

    /**
     * Tests updating dynamic task when from children = false, in hours
     */
    public function testUpdateDynamicsNotFromChildrenInHours()
    {
        $this->obj->load(25);
        $this->obj->updateDynamics(false);
        $this->obj->load(24);

        $now_secs = time();
        $min_time = $now_secs - 10;

        $this->assertEquals(24,                     $this->obj->task_id);
        $this->assertEquals('Task 24',              $this->obj->task_name);
        $this->assertEquals(24,                     $this->obj->task_parent);
        $this->assertEquals(0,                      $this->obj->task_milestone);
        $this->assertEquals(1,                      $this->obj->task_project);
        $this->assertEquals(1,                      $this->obj->task_owner);
        $this->assertEquals('2009-09-09 00:00:00',  $this->obj->task_start_date);
        $this->assertEquals(64,                     $this->obj->task_duration);
        $this->assertEquals(1,                      $this->obj->task_duration_type);
        $this->assertEquals(37,                     $this->obj->task_hours_worked);
        $this->assertEquals('2009-11-02 00:00:00',  $this->obj->task_end_date);
        $this->assertEquals(0,                      $this->obj->task_status);
        $this->assertEquals(0,                      $this->obj->task_priority);
        $this->assertEquals(41,                     $this->obj->task_percent_complete);
        $this->assertEquals('This is task 24',      $this->obj->task_description);
        $this->assertEquals(0.00,                   $this->obj->task_target_budget);
        $this->assertEquals('',                     $this->obj->task_related_url);
        $this->assertEquals(1,                      $this->obj->task_creator);
        $this->assertEquals(1,                      $this->obj->task_order);
        $this->assertEquals(1,                      $this->obj->task_client_publish);
        $this->assertEquals(1,                      $this->obj->task_dynamic);
        $this->assertEquals(1,                      $this->obj->task_access);
        $this->assertEquals(1,                      $this->obj->task_notify);
        $this->assertEquals('',                     $this->obj->task_departments);
        $this->assertEquals('',                     $this->obj->task_contacts);
        $this->assertEquals('',                     $this->obj->task_custom);
        $this->assertEquals(1,                      $this->obj->task_type);
        $this->assertEquals(1,                      $this->obj->task_updator);
        $this->assertEquals('2009-07-06 15:43:00',  $this->obj->task_created);

        $xml_file_dataset = $this->createXMLDataSet($this->getDataSetPath().'tasksTestUpdateDynamicsNotFromChildrenInHours.xml');
        $xml_file_filtered_dataset = new PHPUnit_Extensions_Database_DataSet_DataSetFilter($xml_file_dataset, array('tasks' => array('task_updated')));
        $xml_db_dataset = $this->getConnection()->createDataSet();
        $xml_db_filtered_dataset = new PHPUnit_Extensions_Database_DataSet_DataSetFilter($xml_db_dataset, array('tasks' => array('task_updated')));
        $this->assertTablesEqual($xml_file_filtered_dataset->getTable('tasks'), $xml_db_filtered_dataset->getTable('tasks'));

        /**
         * Get updated dates to test against
         */
        $q = new DBQuery;
        $q->addTable('tasks');
        $q->addQuery('task_updated');
        $q->addWhere('task_id = 24');
        $results = $q->loadColumn();

        foreach($results as $updated) {
            $this->assertGreaterThanOrEqual($min_time, strtotime($updated));
            $this->assertLessThanOrEqual($now_secs, strtotime($updated));
        }
    }

    /**
     * Test copying task with no project or task id passed in.
     */
    public function testCopyNoProjectNoTask()
    {
        $this->obj->load(26);
        $new_task = $this->obj->copy();

        $now_secs = time();
        $min_time = $now_secs - 10;

        $this->assertEquals(31,                     $new_task->task_id);
        $this->assertEquals('Task 26',              $new_task->task_name);
        $this->assertEquals(24,                     $new_task->task_parent);
        $this->assertEquals(0,                      $new_task->task_milestone);
        $this->assertEquals(1,                      $new_task->task_project);
        $this->assertEquals(1,                      $new_task->task_owner);
        $this->assertEquals('2009-10-10 00:00:00',  $new_task->task_start_date);
        $this->assertEquals(3,                      $new_task->task_duration);
        $this->assertEquals(24,                     $new_task->task_duration_type);
        $this->assertEquals(0,                      $new_task->task_hours_worked);
        $this->assertEquals('2009-11-02 00:00:00',  $new_task->task_end_date);
        $this->assertEquals(0,                      $new_task->task_status);
        $this->assertEquals(0,                      $new_task->task_priority);
        $this->assertEquals(67,                     $new_task->task_percent_complete);
        $this->assertEquals('This is task 26',      $new_task->task_description);
        $this->assertEquals(0.00,                   $new_task->task_target_budget);
        $this->assertEquals('',                     $new_task->task_related_url);
        $this->assertEquals(1,                      $new_task->task_creator);
        $this->assertEquals(1,                      $new_task->task_order);
        $this->assertEquals(1,                      $new_task->task_client_publish);
        $this->assertEquals(0,                      $new_task->task_dynamic);
        $this->assertEquals(1,                      $new_task->task_access);
        $this->assertEquals(1,                      $new_task->task_notify);
        $this->assertEquals('',                     $new_task->task_departments);
        $this->assertEquals('',                     $new_task->task_contacts);
        $this->assertEquals('',                     $new_task->task_custom);
        $this->assertEquals(1,                      $new_task->task_type);
        $this->assertEquals(1,                      $new_task->task_updator);

        $xml_file_dataset = $this->createXMLDataSet(dirname(__FILE__).'/../db_files/tasksTestCopyNoProjectNoTask.xml');
        $xml_file_filtered_dataset = new PHPUnit_Extensions_Database_DataSet_DataSetFilter($xml_file_dataset, array('tasks' => array('task_updated', 'task_created')));
        $xml_db_dataset = $this->getConnection()->createDataSet();
        $xml_db_filtered_dataset = new PHPUnit_Extensions_Database_DataSet_DataSetFilter($xml_db_dataset, array('tasks' => array('task_updated', 'task_created')));
        $this->assertTablesEqual($xml_file_filtered_dataset->getTable('tasks'), $xml_db_filtered_dataset->getTable('tasks'));

        /**
         * Get updated dates to test against
         */
        $q = new DBQuery;
        $q->addTable('tasks');
        $q->addQuery('task_updated, task_created');
        $q->addWhere('task_id = 31');
        $results = $q->loadList();

        foreach($results as $dates) {
            $this->assertGreaterThanOrEqual($min_time, strtotime($dates['task_created']));
            $this->assertLessThanOrEqual($now_secs, strtotime($dates['task_created']));
            $this->assertGreaterThanOrEqual($min_time, strtotime($dates['task_updated']));
            $this->assertLessThanOrEqual($now_secs, strtotime($dates['task_updated']));
        }
    }

    /**
     * Test copying task with project but no task passed
     */
    public function testCopyProjectNoTask()
    {
        $this->obj->load(26);
        $new_task = $this->obj->copy(2);

        $now_secs = time();
        $min_time = $now_secs - 10;

        $this->assertEquals(31,                     $new_task->task_id);
        $this->assertEquals('Task 26',              $new_task->task_name);
        $this->assertEquals(24,                     $new_task->task_parent);
        $this->assertEquals(0,                      $new_task->task_milestone);
        $this->assertEquals(2,                      $new_task->task_project);
        $this->assertEquals(1,                      $new_task->task_owner);
        $this->assertEquals('2009-10-10 00:00:00',  $new_task->task_start_date);
        $this->assertEquals(3,                      $new_task->task_duration);
        $this->assertEquals(24,                     $new_task->task_duration_type);
        $this->assertEquals(0,                      $new_task->task_hours_worked);
        $this->assertEquals('2009-11-02 00:00:00',  $new_task->task_end_date);
        $this->assertEquals(0,                      $new_task->task_status);
        $this->assertEquals(0,                      $new_task->task_priority);
        $this->assertEquals(67,                     $new_task->task_percent_complete);
        $this->assertEquals('This is task 26',      $new_task->task_description);
        $this->assertEquals(0.00,                   $new_task->task_target_budget);
        $this->assertEquals('',                     $new_task->task_related_url);
        $this->assertEquals(1,                      $new_task->task_creator);
        $this->assertEquals(1,                      $new_task->task_order);
        $this->assertEquals(1,                      $new_task->task_client_publish);
        $this->assertEquals(0,                      $new_task->task_dynamic);
        $this->assertEquals(1,                      $new_task->task_access);
        $this->assertEquals(1,                      $new_task->task_notify);
        $this->assertEquals('',                     $new_task->task_departments);
        $this->assertEquals('',                     $new_task->task_contacts);
        $this->assertEquals('',                     $new_task->task_custom);
        $this->assertEquals(1,                      $new_task->task_type);
        $this->assertEquals(1,                      $new_task->task_updator);

        $xml_file_dataset = $this->createXMLDataSet(dirname(__FILE__).'/../db_files/tasksTestCopyProjectNoTask.xml');
        $xml_file_filtered_dataset = new PHPUnit_Extensions_Database_DataSet_DataSetFilter($xml_file_dataset, array('tasks' => array('task_updated', 'task_created')));
        $xml_db_dataset = $this->getConnection()->createDataSet();
        $xml_db_filtered_dataset = new PHPUnit_Extensions_Database_DataSet_DataSetFilter($xml_db_dataset, array('tasks' => array('task_updated', 'task_created')));
        $this->assertTablesEqual($xml_file_filtered_dataset->getTable('tasks'), $xml_db_filtered_dataset->getTable('tasks'));

        /**
         * Get updated dates to test against
         */
        $q = new DBQuery;
        $q->addTable('tasks');
        $q->addQuery('task_updated, task_created');
        $q->addWhere('task_id = 31');
        $results = $q->loadList();

        foreach($results as $dates) {
            $this->assertGreaterThanOrEqual($min_time, strtotime($dates['task_created']));
            $this->assertLessThanOrEqual($now_secs, strtotime($dates['task_created']));
            $this->assertGreaterThanOrEqual($min_time, strtotime($dates['task_updated']));
            $this->assertLessThanOrEqual($now_secs, strtotime($dates['task_updated']));
        }
    }

    /**
     * Test copying task with no project but task passed
     */
    public function testCopyNoProjectTask()
    {
        $this->obj->load(26);
        $new_task = $this->obj->copy(0, 1);

        $now_secs = time();
        $min_time = $now_secs - 10;

        $this->assertEquals(31,                     $new_task->task_id);
        $this->assertEquals('Task 26',              $new_task->task_name);
        $this->assertEquals(1,                      $new_task->task_parent);
        $this->assertEquals(0,                      $new_task->task_milestone);
        $this->assertEquals(1,                      $new_task->task_project);
        $this->assertEquals(1,                      $new_task->task_owner);
        $this->assertEquals('2009-10-10 00:00:00',  $new_task->task_start_date);
        $this->assertEquals(3,                      $new_task->task_duration);
        $this->assertEquals(24,                     $new_task->task_duration_type);
        $this->assertEquals(0,                      $new_task->task_hours_worked);
        $this->assertEquals('2009-11-02 00:00:00',  $new_task->task_end_date);
        $this->assertEquals(0,                      $new_task->task_status);
        $this->assertEquals(0,                      $new_task->task_priority);
        $this->assertEquals(67,                     $new_task->task_percent_complete);
        $this->assertEquals('This is task 26',      $new_task->task_description);
        $this->assertEquals(0.00,                   $new_task->task_target_budget);
        $this->assertEquals('',                     $new_task->task_related_url);
        $this->assertEquals(1,                      $new_task->task_creator);
        $this->assertEquals(1,                      $new_task->task_order);
        $this->assertEquals(1,                      $new_task->task_client_publish);
        $this->assertEquals(0,                      $new_task->task_dynamic);
        $this->assertEquals(1,                      $new_task->task_access);
        $this->assertEquals(1,                      $new_task->task_notify);
        $this->assertEquals('',                     $new_task->task_departments);
        $this->assertEquals('',                     $new_task->task_contacts);
        $this->assertEquals('',                     $new_task->task_custom);
        $this->assertEquals(1,                      $new_task->task_type);
        $this->assertEquals(1,                      $new_task->task_updator);

        $xml_file_dataset = $this->createXMLDataSet(dirname(__FILE__).'/../db_files/tasksTestCopyNoProjectTask.xml');
        $xml_file_filtered_dataset = new PHPUnit_Extensions_Database_DataSet_DataSetFilter($xml_file_dataset, array('tasks' => array('task_updated', 'task_created')));
        $xml_db_dataset = $this->getConnection()->createDataSet();
        $xml_db_filtered_dataset = new PHPUnit_Extensions_Database_DataSet_DataSetFilter($xml_db_dataset, array('tasks' => array('task_updated', 'task_created')));
        $this->assertTablesEqual($xml_file_filtered_dataset->getTable('tasks'), $xml_db_filtered_dataset->getTable('tasks'));

        /**
         * Get updated dates to test against
         */
        $q = new DBQuery;
        $q->addTable('tasks');
        $q->addQuery('task_updated, task_created');
        $q->addWhere('task_id = 31');
        $results = $q->loadList();

        foreach($results as $dates) {
            $this->assertGreaterThanOrEqual($min_time, strtotime($dates['task_created']));
            $this->assertLessThanOrEqual($now_secs, strtotime($dates['task_created']));
            $this->assertGreaterThanOrEqual($min_time, strtotime($dates['task_updated']));
            $this->assertLessThanOrEqual($now_secs, strtotime($dates['task_updated']));
        }
    }

    /**
     * Test copying task with with project and task passed
     */
    public function testCopyProjectTask()
    {
        $this->obj->load(26);
        $new_task = $this->obj->copy(2, 1);

        $now_secs = time();
        $min_time = $now_secs - 10;

        $this->assertEquals(31,                     $new_task->task_id);
        $this->assertEquals('Task 26',              $new_task->task_name);
        $this->assertEquals(1,                      $new_task->task_parent);
        $this->assertEquals(0,                      $new_task->task_milestone);
        $this->assertEquals(2,                      $new_task->task_project);
        $this->assertEquals(1,                      $new_task->task_owner);
        $this->assertEquals('2009-10-10 00:00:00',  $new_task->task_start_date);
        $this->assertEquals(3,                      $new_task->task_duration);
        $this->assertEquals(24,                     $new_task->task_duration_type);
        $this->assertEquals(0,                      $new_task->task_hours_worked);
        $this->assertEquals('2009-11-02 00:00:00',  $new_task->task_end_date);
        $this->assertEquals(0,                      $new_task->task_status);
        $this->assertEquals(0,                      $new_task->task_priority);
        $this->assertEquals(67,                     $new_task->task_percent_complete);
        $this->assertEquals('This is task 26',      $new_task->task_description);
        $this->assertEquals(0.00,                   $new_task->task_target_budget);
        $this->assertEquals('',                     $new_task->task_related_url);
        $this->assertEquals(1,                      $new_task->task_creator);
        $this->assertEquals(1,                      $new_task->task_order);
        $this->assertEquals(1,                      $new_task->task_client_publish);
        $this->assertEquals(0,                      $new_task->task_dynamic);
        $this->assertEquals(1,                      $new_task->task_access);
        $this->assertEquals(1,                      $new_task->task_notify);
        $this->assertEquals('',                     $new_task->task_departments);
        $this->assertEquals('',                     $new_task->task_contacts);
        $this->assertEquals('',                     $new_task->task_custom);
        $this->assertEquals(1,                      $new_task->task_type);
        $this->assertEquals(1,                      $new_task->task_updator);

        $xml_file_dataset = $this->createXMLDataSet(dirname(__FILE__).'/../db_files/tasksTestCopyProjectTask.xml');
        $xml_file_filtered_dataset = new PHPUnit_Extensions_Database_DataSet_DataSetFilter($xml_file_dataset, array('tasks' => array('task_updated', 'task_created')));
        $xml_db_dataset = $this->getConnection()->createDataSet();
        $xml_db_filtered_dataset = new PHPUnit_Extensions_Database_DataSet_DataSetFilter($xml_db_dataset, array('tasks' => array('task_updated', 'task_created')));
        $this->assertTablesEqual($xml_file_filtered_dataset->getTable('tasks'), $xml_db_filtered_dataset->getTable('tasks'));

        /**
         * Get updated dates to test against
         */
        $q = new DBQuery;
        $q->addTable('tasks');
        $q->addQuery('task_updated, task_created');
        $q->addWhere('task_id = 31');
        $results = $q->loadList();

        foreach($results as $dates) {
            $this->assertGreaterThanOrEqual($min_time, strtotime($dates['task_created']));
            $this->assertLessThanOrEqual($now_secs, strtotime($dates['task_created']));
            $this->assertGreaterThanOrEqual($min_time, strtotime($dates['task_updated']));
            $this->assertLessThanOrEqual($now_secs, strtotime($dates['task_updated']));
        }
    }

    /**
     * Tests copying assigned users from one task to another
     */
    public function testCopyAssignedUsers()
    {
        $this->obj->load(1);
        $this->obj->copyAssignedUsers(2);

        $xml_dataset = $this->createXMLDataSet($this->getDataSetPath().'tasksTestCopyAssignedUsers.xml');
        $this->assertTablesEqual($xml_dataset->getTable('user_tasks'), $this->getConnection()->createDataSet()->getTable('user_tasks'));
    }

    /**
     * Tests the deep copy function with no project or task id passed in
     */
    public function testDeepCopyNoProjectNoTask()
    {
        $this->obj->load(24);
        $this->obj->deepCopy();

        $now_secs = time();
        $min_time = $now_secs - 10;

        $xml_file_dataset = $this->createXMLDataSet(dirname(__FILE__).'/../db_files/tasksTestDeepCopyNoProjectNoTask.xml');
        $xml_file_filtered_dataset = new PHPUnit_Extensions_Database_DataSet_DataSetFilter($xml_file_dataset, array('tasks' => array('task_updated', 'task_created')));
        $xml_db_dataset = $this->getConnection()->createDataSet();
        $xml_db_filtered_dataset = new PHPUnit_Extensions_Database_DataSet_DataSetFilter($xml_db_dataset, array('tasks' => array('task_updated', 'task_created')));
        $this->assertTablesEqual($xml_file_filtered_dataset->getTable('tasks'), $xml_db_filtered_dataset->getTable('tasks'));

        /**
         * Get updated dates to test against
         */
        $q = new DBQuery;
        $q->addTable('tasks');
        $q->addQuery('task_updated, task_created');
        $q->addWhere('task_id IN(31,32,33)');
        $results = $q->loadList();

        foreach($results as $dates) {
            $this->assertGreaterThanOrEqual($min_time, strtotime($dates['task_created']));
            $this->assertLessThanOrEqual($now_secs, strtotime($dates['task_created']));
            $this->assertGreaterThanOrEqual($min_time, strtotime($dates['task_updated']));
            $this->assertLessThanOrEqual($now_secs, strtotime($dates['task_updated']));
        }
    }

    /**
     * Tests the deep copy function with no project id passed
     */
    public function testDeepCopyNoProjectTask()
    {
        $this->obj->load(24);
        $this->obj->deepCopy(0, 1);

        $now_secs = time();
        $min_time = $now_secs - 10;

        $xml_file_dataset = $this->createXMLDataSet(dirname(__FILE__).'/../db_files/tasksTestDeepCopyNoProjectTask.xml');
        $xml_file_filtered_dataset = new PHPUnit_Extensions_Database_DataSet_DataSetFilter($xml_file_dataset, array('tasks' => array('task_updated', 'task_created')));
        $xml_db_dataset = $this->getConnection()->createDataSet();
        $xml_db_filtered_dataset = new PHPUnit_Extensions_Database_DataSet_DataSetFilter($xml_db_dataset, array('tasks' => array('task_updated', 'task_created')));
        $this->assertTablesEqual($xml_file_filtered_dataset->getTable('tasks'), $xml_db_filtered_dataset->getTable('tasks'));

        /**
         * Get updated dates to test against
         */
        $q = new DBQuery;
        $q->addTable('tasks');
        $q->addQuery('task_updated, task_created');
        $q->addWhere('task_id  IN(31,32,33)');
        $results = $q->loadList();

        foreach($results as $dates) {
            $this->assertGreaterThanOrEqual($min_time, strtotime($dates['task_created']));
            $this->assertLessThanOrEqual($now_secs, strtotime($dates['task_created']));
            $this->assertGreaterThanOrEqual($min_time, strtotime($dates['task_updated']));
            $this->assertLessThanOrEqual($now_secs, strtotime($dates['task_updated']));
        }
    }

    /**
     * Tests the deep copy function with a project and task id passed
     */
    public function testDeepCopyProjectTask()
    {
        $this->obj->load(24);
        $this->obj->deepCopy(2, 1);

        $now_secs = time();
        $min_time = $now_secs - 10;

        $xml_file_dataset = $this->createXMLDataSet(dirname(__FILE__).'/../db_files/tasksTestDeepCopyProjectTask.xml');
        $xml_file_filtered_dataset = new PHPUnit_Extensions_Database_DataSet_DataSetFilter($xml_file_dataset, array('tasks' => array('task_updated', 'task_created')));
        $xml_db_dataset = $this->getConnection()->createDataSet();
        $xml_db_filtered_dataset = new PHPUnit_Extensions_Database_DataSet_DataSetFilter($xml_db_dataset, array('tasks' => array('task_updated', 'task_created')));
        $this->assertTablesEqual($xml_file_filtered_dataset->getTable('tasks'), $xml_db_filtered_dataset->getTable('tasks'));

        /**
         * Get updated dates to test against
         */
        $q = new DBQuery;
        $q->addTable('tasks');
        $q->addQuery('task_updated, task_created');
        $q->addWhere('task_id IN(31,32,33)');
        $results = $q->loadList();

        foreach($results as $dates) {
            $this->assertGreaterThanOrEqual($min_time, strtotime($dates['task_created']));
            $this->assertLessThanOrEqual($now_secs, strtotime($dates['task_created']));
            $this->assertGreaterThanOrEqual($min_time, strtotime($dates['task_updated']));
            $this->assertLessThanOrEqual($now_secs, strtotime($dates['task_updated']));
        }
    }

	/**
	 * Tests the store function with proper arguments passed.
	 */
	public function testStore()
	{
		global $AppUI;

        $this->obj->bind($this->post_data);
        $errorMsg = $this->obj->store($AppUI);

		$now_secs = time();
        $min_time = $now_secs - 10;

        $xml_file_dataset = $this->createXMLDataSet(dirname(__FILE__).'/../db_files/tasksTestStore.xml');
        $xml_file_filtered_dataset = new PHPUnit_Extensions_Database_DataSet_DataSetFilter($xml_file_dataset, array('tasks' => array('task_created', 'task_updated')));
        $xml_db_dataset = $this->getConnection()->createDataSet();
        $xml_db_filtered_dataset = new PHPUnit_Extensions_Database_DataSet_DataSetFilter($xml_db_dataset, array('tasks' => array('task_created', 'task_updated')));
        $this->assertTablesEqual($xml_file_filtered_dataset->getTable('tasks'), $xml_db_filtered_dataset->getTable('tasks'));

        /**
         * Get updated dates to test against
         */
        $q = new DBQuery;
        $q->addTable('tasks');
        $q->addQuery('task_created, task_updated');
        $q->addWhere('task_id IN(' . $this->obj->task_id . ')');
        $results = $q->loadList();

        foreach($results as $dates) {
            $this->assertGreaterThanOrEqual($min_time, strtotime($dates['task_created']));
            $this->assertGreaterThanOrEqual($min_time, strtotime($dates['task_updated']));
            $this->assertLessThanOrEqual($now_secs, strtotime($dates['task_created']));
            $this->assertLessThanOrEqual($now_secs, strtotime($dates['task_updated']));
        }

	}

	/**
	 * Tests the store function when there are sub tasks to be update as well
	 */
	public function testStoreSubTasks()
	{
		global $AppUI;

		$this->post_data['task_id']               = 24;
		$this->post_data['task_project']          = 2;
		$this->post_data['task_name']             = 'Test Task';
		$this->post_data['task_status']           = 2;
		$this->post_data['task_parent']           = 1;

        $this->obj->bind($this->post_data);
        $errorMsg = $this->obj->store($AppUI);

		$now_secs = time();
        $min_time = $now_secs - 10;

        $xml_file_dataset = $this->createXMLDataSet(dirname(__FILE__).'/../db_files/tasksTestStoreSubTasks.xml');
        $xml_file_filtered_dataset = new PHPUnit_Extensions_Database_DataSet_DataSetFilter($xml_file_dataset, array('tasks' => array('task_updated')));
        $xml_db_dataset = $this->getConnection()->createDataSet();
        $xml_db_filtered_dataset = new PHPUnit_Extensions_Database_DataSet_DataSetFilter($xml_db_dataset, array('tasks' => array('task_updated')));
        $this->assertTablesEqual($xml_file_filtered_dataset->getTable('tasks'), $xml_db_filtered_dataset->getTable('tasks'));

        /**
         * Get updated dates to test against
         */
        $q = new DBQuery;
        $q->addTable('tasks');
        $q->addQuery('task_updated');
        $q->addWhere('task_id IN(' . $this->obj->task_id . ')');
        $results = $q->loadList();

        foreach($results as $dates) {
            $this->assertGreaterThanOrEqual($min_time, strtotime($dates['task_updated']));
            $this->assertLessThanOrEqual($now_secs, strtotime($dates['task_updated']));
        }
	}

	/**
	 * Tests the store function when updating dynamics is necessary
	 */
	public function testStoreUpdateDynamics()
	{
		global $AppUI;

		$this->obj->load(24);
		$this->obj->updateDynamics(true);

        $this->assertEquals(24,                     $this->obj->task_id);
        $this->assertEquals('Task 24',              $this->obj->task_name);
        $this->assertEquals(24,                     $this->obj->task_parent);
        $this->assertEquals(0,                      $this->obj->task_milestone);
        $this->assertEquals(1,                      $this->obj->task_project);
        $this->assertEquals(1,                      $this->obj->task_owner);
        $this->assertEquals('2009-09-09 00:00:00',  $this->obj->task_start_date);
        $this->assertEquals(64,                     $this->obj->task_duration);
        $this->assertEquals(1,                      $this->obj->task_duration_type);
        $this->assertEquals(37,                     $this->obj->task_hours_worked);
        $this->assertEquals('2009-11-02 00:00:00',  $this->obj->task_end_date);
        $this->assertEquals(0,                      $this->obj->task_status);
        $this->assertEquals(0,                      $this->obj->task_priority);
        $this->assertEquals(41,                     $this->obj->task_percent_complete);
        $this->assertEquals('This is task 24',      $this->obj->task_description);
        $this->assertEquals(0.00,                   $this->obj->task_target_budget);
        $this->assertEquals('',                     $this->obj->task_related_url);
        $this->assertEquals(1,                      $this->obj->task_creator);
        $this->assertEquals(1,                      $this->obj->task_order);
        $this->assertEquals(1,                      $this->obj->task_client_publish);
        $this->assertEquals(1,                      $this->obj->task_dynamic);
        $this->assertEquals(1,                      $this->obj->task_access);
        $this->assertEquals(1,                      $this->obj->task_notify);
        $this->assertEquals('',                     $this->obj->task_departments);
        $this->assertEquals('',                     $this->obj->task_contacts);
        $this->assertEquals('',                     $this->obj->task_custom);
        $this->assertEquals(1,                      $this->obj->task_type);
        $this->assertEquals(1,                      $this->obj->task_updator);
        $this->assertEquals('2009-07-06 15:43:00',  $this->obj->task_created);
        $this->assertEquals('2009-07-06 15:43:00',  $this->obj->task_updated);

	}

	/**
	 * Tests store whilst shifting dependant tasks.
	 */
	public function testStoreShiftDependentTasks()
	{
		$this->obj->load(27);
		$this->post_data['task_id'] 		= 27;
		$this->post_data['task_end_date']	= '0912011700';
		$this->post_data['milestone'] 		= 1;
		$this->post_data['task_parent'] 	= 27;

		$this->obj->bind($this->post_data);
		$this->obj->store();

		$now_secs = time();
        $min_time = $now_secs - 10;

        $xml_file_dataset = $this->createXMLDataSet(dirname(__FILE__).'/../db_files/tasksTestStoreShiftDependentTasks.xml');
        $xml_file_filtered_dataset = new PHPUnit_Extensions_Database_DataSet_DataSetFilter($xml_file_dataset, array('tasks' => array('task_updated')));
        $xml_db_dataset = $this->getConnection()->createDataSet();
        $xml_db_filtered_dataset = new PHPUnit_Extensions_Database_DataSet_DataSetFilter($xml_db_dataset, array('tasks' => array('task_updated')));
        $this->assertTablesEqual($xml_file_filtered_dataset->getTable('tasks'), $xml_db_filtered_dataset->getTable('tasks'));

        /**
         * Get updated dates to test against
         */
        $q = new DBQuery;
        $q->addTable('tasks');
        $q->addQuery('task_updated');
        $q->addWhere('task_id IN(27, 28)');
        $results = $q->loadList();

        foreach($results as $dates) {
            $this->assertGreaterThanOrEqual($min_time, strtotime($dates['task_updated']));
            $this->assertLessThanOrEqual($now_secs, strtotime($dates['task_updated']));
        }
	}

	/**
	 * Tests store when there is no task_parent set
	 */
	public function testStoreNoTaskParent()
	{
		$this->obj->load(27);
		$this->post_data['task_id'] = 27;

		unset($this->post_data['task_parent']);

		$this->obj->bind($this->post_data);
		$this->obj->store();

		$now_secs = time();
        $min_time = $now_secs - 10;

        $xml_file_dataset = $this->createXMLDataSet(dirname(__FILE__).'/../db_files/tasksTestStoreNoTaskParent.xml');
        $xml_file_filtered_dataset = new PHPUnit_Extensions_Database_DataSet_DataSetFilter($xml_file_dataset, array('tasks' => array('task_updated')));
        $xml_db_dataset = $this->getConnection()->createDataSet();
        $xml_db_filtered_dataset = new PHPUnit_Extensions_Database_DataSet_DataSetFilter($xml_db_dataset, array('tasks' => array('task_updated')));
        $this->assertTablesEqual($xml_file_filtered_dataset->getTable('tasks'), $xml_db_filtered_dataset->getTable('tasks'));

        /**
         * Get updated dates to test against
         */
        $q = new DBQuery;
        $q->addTable('tasks');
        $q->addQuery('task_updated');
        $q->addWhere('task_id = 27');
        $results = $q->loadList();

        foreach($results as $dates) {
            $this->assertGreaterThanOrEqual($min_time, strtotime($dates['task_updated']));
            $this->assertLessThanOrEqual($now_secs, strtotime($dates['task_updated']));
        }
	}

	/**
	 * Tests store function when creating and task_parent
	 */
	public function testStoreCreateNoParent()
	{
		unset($this->post_data['task_parent'], $this->post_data['task_id']);
		$this->post_data['task_departments'] = '1,2';
		$this->post_data['task_contacts'] = '1,2';

		$this->obj->bind($this->post_data);
		$results = $this->obj->store();

		$now_secs = time();
        $min_time = $now_secs - 10;

        $xml_file_dataset = $this->createXMLDataSet(dirname(__FILE__).'/../db_files/tasksTestStoreCreateNoParent.xml');
        $xml_file_filtered_dataset = new PHPUnit_Extensions_Database_DataSet_DataSetFilter($xml_file_dataset, array('tasks' => array('task_created','task_updated')));
        $xml_db_dataset = $this->getConnection()->createDataSet();
        $xml_db_filtered_dataset = new PHPUnit_Extensions_Database_DataSet_DataSetFilter($xml_db_dataset, array('tasks' => array('task_created','task_updated')));
        $this->assertTablesEqual($xml_file_filtered_dataset->getTable('tasks'), $xml_db_filtered_dataset->getTable('tasks'));
        $this->assertTablesEqual($xml_file_filtered_dataset->getTable('task_departments'), $xml_db_filtered_dataset->getTable('task_departments'));
        $this->assertTablesEqual($xml_file_filtered_dataset->getTable('task_contacts'), $xml_db_filtered_dataset->getTable('task_contacts'));
        
        /**
         * Get updated dates to test against
         */
        $q = new DBQuery;
        $q->addTable('tasks');
        $q->addQuery('task_created,task_updated');
        $q->addWhere('task_id = 31');
        $results = $q->loadList();

        foreach($results as $dates) {
            $this->assertGreaterThanOrEqual($min_time, strtotime($dates['task_updated']));
            $this->assertLessThanOrEqual($now_secs, strtotime($dates['task_updated']));
            $this->assertGreaterThanOrEqual($min_time, strtotime($dates['task_created']));
            $this->assertLessThanOrEqual($now_secs, strtotime($dates['task_created']));
        }

        /**
         * Test to make sure project task count was updated
         */
        $project = new CProject();
        $project->load(1);

        $this->assertEquals(31, $project->project_task_count);
	}

    /**
     * Tests deleting a task with no dependencies
     */
    public function testDelete()
    {
        $this->obj->load(22);
        $this->obj->delete();

        $this->assertFalse($this->obj->load(22));
        $this->assertEquals(0, count($this->obj->getAssignedUsers(22)));
        $this->assertEquals(0, count($this->obj->getTaskLogs(22)));
        $this->assertEquals(0, count($this->obj->getAssignedUsers(22)));
        $this->assertEquals(0, count($this->obj->getDependencyList(22)));
        $this->assertEquals(0, count($this->obj->getDependentTaskList(22)));

        /**
         * Test to make sure project task count was updated
         */
        $project = new CProject();
        $project->load(1);

        $this->assertEquals(29, $project->project_task_count);
    }

    /**
     * Tests to make sure all relevant children are found
     */
    public function testGetChildren()
    {
        $this->obj->load(21);
        $children = $this->obj->getChildren();

        $this->assertEquals(2, count($children));
    }

    /**
     * Tests to make sure all relevant children and their descendants are found
     */
    public function testDeepChildren()
    {
        $this->obj->load(15);
        $children = $this->obj->getDeepChildren();

        $this->assertEquals(2, count($children));
    }

    /**
     * Tests deleting a task with children
     */
    public function testDeleteWithChildren()
    {
        $this->obj->load(15);
        $children = $this->obj->getDeepChildren();
        $this->obj->delete();

        foreach($children as $child) {
            $this->assertFalse($this->obj->load($child));
            $this->assertEquals(0, count($this->obj->getAssignedUsers($child)));
            $this->assertEquals(0, count($this->obj->getTaskLogs($child)));
            $this->assertEquals(0, count($this->obj->getAssignedUsers($child)));
            $this->assertEquals(0, count($this->obj->getDependencyList($child)));
            $this->assertEquals(0, count($this->obj->getDependentTaskList($child)));
        }

        /**
         * Test to make sure project task count was updated
         */
        $project = new CProject();
        $project->load(1);

        $this->assertEquals(27, $project->project_task_count);
    }

    /**
     * Tests deleting a task with Dependencies
     */
    public function testDeleteWithDeps()
    {
        $this->obj->load(28);
        $this->obj->delete();

        $this->assertFalse($this->obj->load(28));
        $this->assertEquals(0, count($this->obj->getAssignedUsers(28)));
        $this->assertEquals(0, count($this->obj->getTaskLogs(28)));
        $this->assertEquals(0, count($this->obj->getAssignedUsers(28)));
        $this->assertEquals(0, count($this->obj->getDependencyList(28)));
        $this->assertEquals(0, count($this->obj->getDependentTaskList(28)));
    }

    /**
     * Tests updating dependencies for a task
     */
    public function testUpdateDependencies()
    {
        $this->obj->load(28);
        $this->obj->updateDependencies('24,25,26');

        $xml_file_dataset = $this->createXMLDataSet(dirname(__FILE__).'/../db_files/tasksTestUpdateDependencies.xml');
        $xml_file_filtered_dataset = new PHPUnit_Extensions_Database_DataSet_DataSetFilter($xml_file_dataset, array());
        $xml_db_dataset = $this->getConnection()->createDataSet();
        $xml_db_filtered_dataset = new PHPUnit_Extensions_Database_DataSet_DataSetFilter($xml_db_dataset, array());
        $this->assertTablesEqual($xml_file_filtered_dataset->getTable('task_dependencies'), $xml_db_filtered_dataset->getTable('task_dependencies'));
    }

    /**
     * Tests updating dependencies for a task with an empty string passed
     */
    public function testUpdateDependenciesEmptyString()
    {
        $this->obj->load(28);
        $this->obj->updateDependencies('');

        $xml_file_dataset = $this->createXMLDataSet(dirname(__FILE__).'/../db_files/tasksTestUpdateDependenciesEmptyString.xml');
        $xml_file_filtered_dataset = new PHPUnit_Extensions_Database_DataSet_DataSetFilter($xml_file_dataset, array());
        $xml_db_dataset = $this->getConnection()->createDataSet();
        $xml_db_filtered_dataset = new PHPUnit_Extensions_Database_DataSet_DataSetFilter($xml_db_dataset, array());
        $this->assertTablesEqual($xml_file_filtered_dataset->getTable('task_dependencies'), $xml_db_filtered_dataset->getTable('task_dependencies'));
    }

    /**
     * Tests updating dependencies for a task with invalid values passed
     */
    public function testUpdateDependenciesInvalidValues()
    {
        $this->obj->load(28);
        $this->obj->updateDependencies('0, ');

        $xml_file_dataset = $this->createXMLDataSet(dirname(__FILE__).'/../db_files/tasksTestUpdateDependenciesInvalidValues.xml');
        $xml_file_filtered_dataset = new PHPUnit_Extensions_Database_DataSet_DataSetFilter($xml_file_dataset, array());
        $xml_db_dataset = $this->getConnection()->createDataSet();
        $xml_db_filtered_dataset = new PHPUnit_Extensions_Database_DataSet_DataSetFilter($xml_db_dataset, array());
        $this->assertTablesEqual($xml_file_filtered_dataset->getTable('task_dependencies'), $xml_db_filtered_dataset->getTable('task_dependencies'));
    }

    /**
     * Tests pushing dependencies for a task
     */
    public function testPushDependencies()
    {
        $this->obj->pushDependencies(28, '2009-09-10');

        $now_secs = time();
        $min_time = $now_secs - 10;

        $xml_file_dataset = $this->createXMLDataSet(dirname(__FILE__).'/../db_files/tasksTestPushDependencies.xml');
        $xml_file_filtered_dataset = new PHPUnit_Extensions_Database_DataSet_DataSetFilter($xml_file_dataset, array('tasks' => array('task_updated')));
        $xml_db_dataset = $this->getConnection()->createDataSet();
        $xml_db_filtered_dataset = new PHPUnit_Extensions_Database_DataSet_DataSetFilter($xml_db_dataset, array('tasks' => array('task_updated')));
        $this->assertTablesEqual($xml_file_filtered_dataset->getTable('tasks'), $xml_db_filtered_dataset->getTable('tasks'));

        /**
         * Get updated dates to test against
         */
        $q = new DBQuery;
        $q->addTable('tasks');
        $q->addQuery('task_updated');
        $q->addWhere('task_id = 29');
        $results = $q->loadList();

        foreach($results as $dates) {
            $this->assertGreaterThanOrEqual($min_time, strtotime($dates['task_updated']));
            $this->assertLessThanOrEqual($now_secs, strtotime($dates['task_updated']));
        }
    }
    
    /**
     * Test getting dependencies
     */
    public function testGetDependencies()
    {
        $this->obj->load(28);
        $result = $this->obj->getDependencies();
        $this->assertEquals(27, $result);

        $this->obj->load(1);
        $result = $this->obj->getDependencies();
        $this->assertEquals('', $result);
    }

    /**
     * $test getting dependecies, by passing task id in
     */
    public function testStaticGetDependencies()
    {
        $result = $this->obj->staticGetDependencies(28);
        $this->assertEquals(27, $result);

        $result = $this->obj->staticGetDependencies(1);
        $this->assertEquals('', $result);
    }

    /**
     * Tests that owner is notified when task changes
     */
    public function testNotifyOwner()
    {
        $this->markTestSkipped('Not sure how to test emails being sent.');
    }

    /**
     * Tests that owner is notified when task changes, with additional information
     */
    public function testNotify()
    {
        $this->markTestSkipped('Not sure how to test emails being sent.');
    }

    /**
     * Test that the proper people are emailed the task log
     */
    public function testEmailLog()
    {
        $this->markTestSkipped('Not sure how to test emails being sent.');
    }

    /**
     * Tests that the proper tasks are returned for Period with no company or
     * user passed
     */
    public function testGetTasksForPeriodNoCompanyNoUser()
    {
        global $AppUI;
        
        $start_date = new CDate('2009-07-05');
        $end_date   = new CDate('2009-07-16');

        $results = $this->obj->getTasksForPeriod($start_date, $end_date);

        $this->assertEquals(2,                      count($results));
        $this->assertEquals(14,                     count($results[1]));
        $this->assertEquals(14,                     count($results[2]));
        $this->assertEquals(1,                      $results[1]['task_id']);
        $this->assertEquals('Task 1',               $results[1]['task_name']);
        $this->assertEquals('2009-07-05 00:00:00',  $results[1]['task_start_date']);
        $this->assertEquals('2009-07-15 00:00:00',  $results[1]['task_end_date']);
        $this->assertEquals(2,                      $results[1]['task_duration']);
        $this->assertEquals(24,                     $results[1]['task_duration_type']);
        $this->assertEquals('FFFFFF',               $results[1]['color']);
        $this->assertEquals('Test Project',         $results[1]['project_name']);
        $this->assertEquals(0,                      $results[1]['task_milestone']);
        $this->assertEquals('This is task 1',       $results[1]['task_description']);
        $this->assertEquals(1,                      $results[1]['task_type']);
        $this->assertEquals('UnitTestCompany',      $results[1]['company_name']);
        $this->assertEquals(0,                      $results[1]['task_access']);
        $this->assertEquals(1,                      $results[1]['task_owner']);
        $this->assertEquals(2,                      $results[2]['task_id']);
        $this->assertEquals('Task 2',               $results[2]['task_name']);
        $this->assertEquals('2009-07-06 00:00:00',  $results[2]['task_start_date']);
        $this->assertEquals('2009-07-16 00:00:00',  $results[2]['task_end_date']);
        $this->assertEquals(2,                      $results[2]['task_duration']);
        $this->assertEquals(24,                     $results[2]['task_duration_type']);
        $this->assertEquals('FFFFFF',               $results[2]['color']);
        $this->assertEquals('Test Project',         $results[2]['project_name']);
        $this->assertEquals(0,                      $results[2]['task_milestone']);
        $this->assertEquals('This is task 2',       $results[2]['task_description']);
        $this->assertEquals(1,                      $results[2]['task_type']);
        $this->assertEquals('UnitTestCompany',      $results[2]['company_name']);
        $this->assertEquals(1,                      $results[2]['task_access']);
        $this->assertEquals(1,                      $results[2]['task_owner']);
    }

    /**
     * Tests that the proper tasks are returned for Period with a company but no
     * user passed
     */
    public function testGetTaskForPeriodCompanyNoUser()
    {
        global $AppUI;

        $start_date = new CDate('2009-07-05');
        $end_date   = new CDate('2009-07-16');

        $results = $this->obj->getTasksForPeriod($start_date, $end_date, 1);

        $this->assertEquals(2,                      count($results));
        $this->assertEquals(14,                     count($results[1]));
        $this->assertEquals(14,                     count($results[2]));
        $this->assertEquals(1,                      $results[1]['task_id']);
        $this->assertEquals('Task 1',               $results[1]['task_name']);
        $this->assertEquals('2009-07-05 00:00:00',  $results[1]['task_start_date']);
        $this->assertEquals('2009-07-15 00:00:00',  $results[1]['task_end_date']);
        $this->assertEquals(2,                      $results[1]['task_duration']);
        $this->assertEquals(24,                     $results[1]['task_duration_type']);
        $this->assertEquals('FFFFFF',               $results[1]['color']);
        $this->assertEquals('Test Project',         $results[1]['project_name']);
        $this->assertEquals(0,                      $results[1]['task_milestone']);
        $this->assertEquals('This is task 1',       $results[1]['task_description']);
        $this->assertEquals(1,                      $results[1]['task_type']);
        $this->assertEquals('UnitTestCompany',      $results[1]['company_name']);
        $this->assertEquals(0,                      $results[1]['task_access']);
        $this->assertEquals(1,                      $results[1]['task_owner']);
        $this->assertEquals(2,                      $results[2]['task_id']);
        $this->assertEquals('Task 2',               $results[2]['task_name']);
        $this->assertEquals('2009-07-06 00:00:00',  $results[2]['task_start_date']);
        $this->assertEquals('2009-07-16 00:00:00',  $results[2]['task_end_date']);
        $this->assertEquals(2,                      $results[2]['task_duration']);
        $this->assertEquals(24,                     $results[2]['task_duration_type']);
        $this->assertEquals('FFFFFF',               $results[2]['color']);
        $this->assertEquals('Test Project',         $results[2]['project_name']);
        $this->assertEquals(0,                      $results[2]['task_milestone']);
        $this->assertEquals('This is task 2',       $results[2]['task_description']);
        $this->assertEquals(1,                      $results[2]['task_type']);
        $this->assertEquals('UnitTestCompany',      $results[2]['company_name']);
        $this->assertEquals(1,                      $results[2]['task_access']);
        $this->assertEquals(1,                      $results[2]['task_owner']);

        $results = $this->obj->getTasksForPeriod($start_date, $end_date, 2);

        $this->assertEquals(array(), $results);
    }

    /**
     * Tests that the proper tasks are returned for Period with a company and
     * user passed
     */
    public function testGetTaskForPeriodCompanyUser()
    {
        global $AppUI;

        $start_date = new CDate('2009-07-05');
        $end_date   = new CDate('2009-07-16');

        $results = $this->obj->getTasksForPeriod($start_date, $end_date, 1, 1);

        $this->assertEquals(2,                      count($results));
        $this->assertEquals(14,                     count($results[1]));
        $this->assertEquals(14,                     count($results[2]));
        $this->assertEquals(1,                      $results[1]['task_id']);
        $this->assertEquals('Task 1',               $results[1]['task_name']);
        $this->assertEquals('2009-07-05 00:00:00',  $results[1]['task_start_date']);
        $this->assertEquals('2009-07-15 00:00:00',  $results[1]['task_end_date']);
        $this->assertEquals(2,                      $results[1]['task_duration']);
        $this->assertEquals(24,                     $results[1]['task_duration_type']);
        $this->assertEquals('FFFFFF',               $results[1]['color']);
        $this->assertEquals('Test Project',         $results[1]['project_name']);
        $this->assertEquals(0,                      $results[1]['task_milestone']);
        $this->assertEquals('This is task 1',       $results[1]['task_description']);
        $this->assertEquals(1,                      $results[1]['task_type']);
        $this->assertEquals('UnitTestCompany',      $results[1]['company_name']);
        $this->assertEquals(0,                      $results[1]['task_access']);
        $this->assertEquals(1,                      $results[1]['task_owner']);
        $this->assertEquals(2,                      $results[2]['task_id']);
        $this->assertEquals('Task 2',               $results[2]['task_name']);
        $this->assertEquals('2009-07-06 00:00:00',  $results[2]['task_start_date']);
        $this->assertEquals('2009-07-16 00:00:00',  $results[2]['task_end_date']);
        $this->assertEquals(2,                      $results[2]['task_duration']);
        $this->assertEquals(24,                     $results[2]['task_duration_type']);
        $this->assertEquals('FFFFFF',               $results[2]['color']);
        $this->assertEquals('Test Project',         $results[2]['project_name']);
        $this->assertEquals(0,                      $results[2]['task_milestone']);
        $this->assertEquals('This is task 2',       $results[2]['task_description']);
        $this->assertEquals(1,                      $results[2]['task_type']);
        $this->assertEquals('UnitTestCompany',      $results[2]['company_name']);
        $this->assertEquals(1,                      $results[2]['task_access']);
        $this->assertEquals(1,                      $results[2]['task_owner']);

        $results = $this->obj->getTasksForPeriod($start_date, $end_date, 1, 3);

        $this->assertEquals(array(), $results);

        $results = $this->obj->getTasksForPeriod($start_date, $end_date, 3, 1);

        $this->assertEquals(array(), $results);

        $results = $this->obj->getTasksForPeriod($start_date, $end_date, 3, 2);

        $this->assertEquals(array(), $results);
    }

    /**
     * Tests that canAccess throws the appropriate warning.
     */
    public function testCanAccessThrowsEUSERNOTICE()
    {

        global $AppUI;

        $this->setExpectedException('PHPUnit_Framework_Error');
        $result = $this->obj->canAccess(1);
    }

    /**
     *
     * @global <type> $AppUI Test canAccess functionality
     */
    public function testCanAccess()
    {

        global $AppUI;

        $this->obj->load(1);

        // This @ stuff is kind of gross, but we need to do it until we get to
        // 2.0 so the deprecated user warnigs go away
        //
        // admin user
        $result = @$this->obj->canAccess(1);
        $this->assertTrue($result);

        // Login as another user for permission purposes
        $old_AppUI = $AppUI;
        $AppUI  = new CAppUI;
        $_POST['login'] = 'login';
        $_REQUEST['login'] = 'sql';
        

        // public access
        $result = @$this->obj->canAccess(2);
        $this->assertTrue($result);

        // protected access
        $this->obj->load(2);
        $result = @$this->obj->canAccess(2);
        $this->assertTrue($result);

        $result = @$this->obj->canAccess(3);
        $this->assertFalse($result);

        // participant access
        $this->obj->load(3);
        $result = @$this->obj->canAccess(2);
        $this->assertTrue($result);

        $result = @$this->obj->canAccess(3);
        $this->assertFalse($result);

        // private
        $this->obj->load(4);
        $result = @$this->obj->canAccess(2);
        $this->assertTrue($result);

        $result = @$this->obj->canAccess(3);
        $this->assertFalse($result);

        // Restore AppUI for following tests since its global, yuck!
        $AppUI = $old_AppUI;
    }
}
?>
