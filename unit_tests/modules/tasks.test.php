<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Class for testing tasks functionality
 *
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version. Please see the LICENSE file in root of site
 * for further details
 *
 * @category    Tasks
 * @package     web2project
 * @subpackage  unit_tests
 * @author      Trevor Morse <trevor.morse@gmail.com>
 * @copyright   2007-2010 The web2Project Development Team <w2p-developers@web2project.net>
 * @link        http://www.web2project.net
 */

/**
 * Necessary global variables
 */
global $db;
global $ADODB_FETCH_MODE;
global $w2p_performance_dbtime;
global $w2p_performance_old_dbqueries;
global $AppUI;
global $tracking_dynamics;
global $tracked_dynamics;
global $w2Pconfig;

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
/*
 * Need this to not get the annoying timezone warnings in tests.
 */
$defaultTZ = w2PgetConfig('system_timezone', 'Europe/London');
$defaultTZ = ('' == $defaultTZ) ? 'Europe/London' : $defaultTZ;
date_default_timezone_set($defaultTZ);

$tracking_dynamics = array('0' => '21', '1' => '31');
$tracked_dynamics = array('0' => '0', '1' => '1', '2' => '31');

require_once W2P_BASE_DIR . '/includes/session.php';
require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Extensions/Database/TestCase.php';

/**
 * This class tests functionality for Tasks
 *
 * @author      Trevor Morse <trevor.morse@gmail.com>
 * @category    Tasks
 * @package     web2project
 * @subpackage  unit_tests
 * @copyright   2007-2010 The web2Project Development Team <w2p-developers@web2project.net>
 * @link        http://www.web2project.net
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

    /**
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

        $xml_file_dataset = $this->createXMLDataSet($this->getDataSetPath().'tasksTestCopyNoProjectNoTask.xml');
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

        $xml_file_dataset = $this->createXMLDataSet($this->getDataSetPath().'tasksTestCopyProjectNoTask.xml');
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

        $xml_file_dataset = $this->createXMLDataSet($this->getDataSetPath().'tasksTestCopyNoProjectTask.xml');
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

        $xml_file_dataset = $this->createXMLDataSet($this->getDataSetPath().'tasksTestCopyProjectTask.xml');
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

        $xml_file_dataset = $this->createXMLDataSet($this->getDataSetPath().'tasksTestDeepCopyNoProjectNoTask.xml');
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

        $xml_file_dataset = $this->createXMLDataSet($this->getDataSetPath().'tasksTestDeepCopyNoProjectTask.xml');
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

        $xml_file_dataset = $this->createXMLDataSet($this->getDataSetPath().'tasksTestDeepCopyProjectTask.xml');
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

        $xml_file_dataset = $this->createXMLDataSet($this->getDataSetPath().'tasksTestStore.xml');
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

        $xml_file_dataset = $this->createXMLDataSet($this->getDataSetPath().'tasksTestStoreSubTasks.xml');
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
		$this->post_data['task_end_date']	= '200912011700';
		$this->post_data['milestone'] 		= 1;
		$this->post_data['task_parent'] 	= 27;

		$this->obj->bind($this->post_data);
		$this->obj->store();

		$now_secs = time();

        $xml_file_dataset = $this->createXMLDataSet($this->getDataSetPath().'tasksTestStoreShiftDependentTasks.xml');
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
        $q->addWhere('task_id IN (27, 28)');
        $results = $q->loadList();

        $this->assertGreaterThanOrEqual($now_secs, strtotime($results[0]['task_updated']));
        $this->assertLessThanOrEqual($now_secs, strtotime($results[1]['task_updated']));
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

        $xml_file_dataset = $this->createXMLDataSet($this->getDataSetPath().'tasksTestStoreNoTaskParent.xml');
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

        $xml_file_dataset = $this->createXMLDataSet($this->getDataSetPath().'tasksTestStoreCreateNoParent.xml');
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

        $this->assertEquals(2,  count($children));
        $this->assertEquals(22, $children[0]);
        $this->assertEquals(23, $children[1]);
    }

    /**
     * Tests to make sure all relevant children and their descendants are found
     */
    public function testDeepChildren()
    {
        $this->obj->load(15);
        $children = $this->obj->getDeepChildren();

        $this->assertEquals(2,  count($children));
        $this->assertEquals(16, $children[0]);
        $this->assertEquals(17, $children[1]);
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

        $xml_file_dataset = $this->createXMLDataSet($this->getDataSetPath().'tasksTestUpdateDependencies.xml');
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

        $xml_file_dataset = $this->createXMLDataSet($this->getDataSetPath().'tasksTestUpdateDependenciesEmptyString.xml');
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

        $xml_file_dataset = $this->createXMLDataSet($this->getDataSetPath().'tasksTestUpdateDependenciesInvalidValues.xml');
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

        $xml_file_dataset = $this->createXMLDataSet($this->getDataSetPath().'tasksTestPushDependencies.xml');
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
     * Tests the canAccess function
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

    /**
     * Tests that dependentTasks returns nothing if no task id provided
     */
    public function testDependentTasksNoTaskID()
    {
        unset($this->obj);
        $this->obj = new CTask();
        $result = $this->obj->dependentTasks();
        $this->assertEquals('', $result);
    }

    /**
     * Tests that dependentTasks returns proper list if object id is set
     */
    public function testDependentTasksTaskFromObject()
    {
        $this->obj->load(28);
        $result = $this->obj->dependentTasks();
        $this->assertEquals('29,30', $result);
    }

    /**
     * Tests that dependentTasks returns proper list is returned if task id is
     * passed in as well as object id set
     */
    public function testDependentTasksTaskID()
    {
        $result = $this->obj->dependentTasks(28);
        $this->assertEquals('29,30', $result);
    }

    /**
     * Tests dependentTasks returns proper list with recursive dependencies
     */
    public function testDependentTasksTaskIDWithRecurseDeps()
    {
        $result = $this->obj->dependentTasks(27);
        $this->assertEquals('29,30,28', $result);
    }

    /**
     * Tests dependentTasks returns proper list when not recursing
     */
    public function testDependentTasksTaskIDNoRecurse()
    {
        $result = $this->obj->dependentTasks(27, false, false);
        $this->assertEquals('28', $result);
    }

    /**
     * Tests that dependent tasks are properly shifted
     */
    public function testShiftDependentTasks()
    {
        $this->obj->load(27);
        $this->obj->shiftDependentTasks();

        $now_secs = time();
        $min_time = $now_secs - 10;

        $xml_file_dataset = $this->createXMLDataSet($this->getDataSetPath().'tasksTestShiftDependentTasks.xml');
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
        $q->addWhere('task_id IN(28,29,30)');

        $results = $q->loadList();

        foreach($results as $dates) {
            $this->assertGreaterThanOrEqual($min_time, strtotime($dates['task_updated']));
            $this->assertLessThanOrEqual($now_secs, strtotime($dates['task_updated']));
        }
    }

    /*
     * Tests that dependentTasks returns nothing if no task id provided
     */
    public function testDependentTasksNoTaskID()
    {
        unset($this->obj);
        $this->obj = new CTask();
        $result = $this->obj->dependentTasks();
        $this->assertEquals('', $result);
    }

    /**
     * Tests that dependentTasks returns proper list if object id is set
     */
    public function testDependentTasksTaskFromObject()
    {
        $this->obj->load(28);
        $result = $this->obj->dependentTasks();
        $this->assertEquals('29,30', $result);
    }

    /**
     * Tests that dependentTasks returns proper list is returned if task id is
     * passed in as well as object id set
     */
    public function testDependentTasksTaskID()
    {
        $result = $this->obj->dependentTasks(28);
        $this->assertEquals('29,30', $result);
    }

    /**
     * Tests dependentTasks returns proper list with recursive dependencies
     */
    public function testDependentTasksTaskIDWithRecurseDeps()
    {
        $result = $this->obj->dependentTasks(27);
        $this->assertEquals('29,30,28', $result);
    }

    /**
     * Tests dependentTasks returns proper list when not recursing
     */
    public function testDependentTasksTaskIDNoRecurse()
    {
        $result = $this->obj->dependentTasks(27, false, false);
        $this->assertEquals('28', $result);
    }

    /**
     * Tests that dependent dates are updated properly for current task
     */
    public function testUpdateDepDate()
    {
        $this->obj->update_dep_dates(28);

        $xml_file_dataset = $this->createXMLDataSet($this->getDataSetPath().'tasksTestUpdateDepDates.xml');
        $xml_file_filtered_dataset = new PHPUnit_Extensions_Database_DataSet_DataSetFilter($xml_file_dataset, array('tasks' => array('task_updated')));
        $xml_db_dataset = $this->getConnection()->createDataSet();
        $xml_db_filtered_dataset = new PHPUnit_Extensions_Database_DataSet_DataSetFilter($xml_db_dataset, array('tasks' => array('task_updated')));
        $this->assertTablesEqual($xml_file_filtered_dataset->getTable('tasks'), $xml_db_filtered_dataset->getTable('tasks'));

        $now_secs = time();
        $min_time = $now_secs - 10;

        /**
         * Get updated dates to test against
         */
        $q = new DBQuery;
        $q->addTable('tasks');
        $q->addQuery('task_updated');
        $q->addWhere('task_id IN(28)');

        $results = $q->loadList();

        foreach($results as $dates) {
            $this->assertGreaterThanOrEqual($min_time, strtotime($dates['task_updated']));
            $this->assertLessThanOrEqual($now_secs, strtotime($dates['task_updated']));
        }
    }

    /**
     * Tests that the max end date based on dependencies for a task is properly returned
     */
    public function testGetDepsMaxEndDate()
    {
        /**
         * Tests when a dependency is tracked and has an end date
         */
        $task = new CTask();
        $task->load(28);
        $max_end_date = $this->obj->get_deps_max_end_date($task);
        $this->assertEquals('2009-11-02 00:00:00', $max_end_date);

        /**
         * Tests when no dependency is tracked and/or has an end date
         * so default to project start date
         */
        $task = new CTask();
        $task->load(1);
        $max_end_date = $this->obj->get_deps_max_end_date($task);
        $this->assertEquals('2009-10-10 00:00:00', $max_end_date);
    }

    /**
     * Tests that the proper number of hours per day this task occupies
     */
    public function testGetTaskDurationPerDay()
    {
        $this->obj->load(1);
        $this->assertEquals(2, $this->obj->getTaskDurationPerDay());
        $this->assertEquals(4, $this->obj->getTaskDurationPerDay(true));
    }

    /**
     * Tests that the proper number of hours per week this task occupies
     */
    public function testGetTaskDurationPerWeek()
    {
        $this->obj->load(1);
        $this->assertEquals(10, $this->obj->getTaskDurationPerWeek());
        $this->assertEquals(20, $this->obj->getTaskDurationPerWeek(true));
    }

    /**
     * Tests removing an assigned user from a task
     */
    public function testRemoveAssigned()
    {
        $this->obj->load(1);
        $this->obj->removeAssigned(1);

        $xml_file_dataset = $this->createXMLDataSet($this->getDataSetPath().'tasksTestRemoveAssigned.xml');
        $xml_db_dataset = $this->getConnection()->createDataSet();
        $this->assertTablesEqual($xml_file_dataset->getTable('user_tasks'), $xml_db_dataset->getTable('user_tasks'));
    }

    /**
     * Tests updating assigned users for task
     */
     public function testUpdateAssigned()
     {
        global $w2Pconfig;

        // god this is ugly but needs to be done to test this funcationlity properly
        $old_config = $w2Pconfig['check_overallocation'];
        $w2Pconfig['check_overallocation'] = true;

        $this->obj->load(1);
        $over_assigned = $this->obj->updateAssigned(1, array('1' => '99'), false, false);
        $this->assertEquals('', $over_assigned);

        $xml_file_dataset = $this->createXMLDataSet($this->getDataSetPath().'tasksTestUpdateAssigned.xml');
        $xml_db_dataset = $this->getConnection()->createDataSet();
        $this->assertTablesEqual($xml_file_dataset->getTable('user_tasks'), $xml_db_dataset->getTable('user_tasks'));


        $w2Pconfig['check_overallocation'] = $old_config;
     }

    /**
     * Tests updating assigned users for task
     */
     public function testUpdateAssigned()
     {
        global $w2Pconfig;

        // god this is ugly but needs to be done to test this funcationlity properly
        $old_config = $w2Pconfig['check_overallocation'];
        $w2Pconfig['check_overallocation'] = true;

        $this->obj->load(1);
        $over_assigned = $this->obj->updateAssigned(1, array('1' => '99'), false, false);
        $this->assertEquals('', $over_assigned);

        $xml_file_dataset = $this->createXMLDataSet($this->getDataSetPath().'tasksTestUpdateAssigned.xml');
        $xml_db_dataset = $this->getConnection()->createDataSet();
        $this->assertTablesEqual($xml_file_dataset->getTable('user_tasks'), $xml_db_dataset->getTable('user_tasks'));


        $w2Pconfig['check_overallocation'] = $old_config;
     }

    /**
     * Tests updating assigned users for task
     */
    public function testUpdateAssignedOverAssignment()
    {
        global $w2Pconfig;

        // god this is ugly but needs to be done to test this funcationlity properly
        $old_config = $w2Pconfig['check_overallocation'];
        $w2Pconfig['check_overallocation'] = true;

        $this->obj->load(1);
        $over_assigned = $this->obj->updateAssigned(1, array('1' => '101'), false, false);
        $this->assertEquals(' Admin Person [0%]', $over_assigned);

        $this->obj->load(1);
        $over_assigned = $this->obj->updateAssigned(1, array('1' => '99'), false, false);
        $this->assertEquals('', $over_assigned);

        $w2Pconfig['check_overallocation'] = $old_config;
    }

    /**
     * Tests updating assigned users for task
     */
    public function testUpdateAssignedWithDelete()
    {
        global $w2Pconfig;

        // god this is ugly but needs to be done to test this funcationlity properly
        $old_config = $w2Pconfig['check_overallocation'];
        $w2Pconfig['check_overallocation'] = true;

        $this->obj->load(2);
        $over_assigned = $this->obj->updateAssigned(1, array('1' => '99'), true, false);
        $this->assertEquals('', $over_assigned);

        $xml_file_dataset = $this->createXMLDataSet($this->getDataSetPath().'tasksTestUpdateAssignedWithDelete.xml');
        $xml_db_dataset = $this->getConnection()->createDataSet();
        $this->assertTablesEqual($xml_file_dataset->getTable('user_tasks'), $xml_db_dataset->getTable('user_tasks'));

        $w2Pconfig['check_overallocation'] = $old_config;
    }

    /**
     * Tests updating assigned users for task
     */
    public function testUpdateAssignedWithDeleteAndRemoveUsers()
    {
        global $w2Pconfig;

        // god this is ugly but needs to be done to test this funcationlity properly
        $old_config = $w2Pconfig['check_overallocation'];
        $w2Pconfig['check_overallocation'] = true;

        $this->obj->load(2);
        $over_assigned = $this->obj->updateAssigned(1, array('1' => '99'), true, true);
        $this->assertEquals('', $over_assigned);

        $xml_file_dataset = $this->createXMLDataSet($this->getDataSetPath().'tasksTestUpdateAssignedWithDeleteAndRemoveUsers.xml');
        $xml_db_dataset = $this->getConnection()->createDataSet();
        $this->assertTablesEqual($xml_file_dataset->getTable('user_tasks'), $xml_db_dataset->getTable('user_tasks'));

        $w2Pconfig['check_overallocation'] = $old_config;
    }

    /**
     * Tests getting a list of assigned users for a particular task
     */
    public function testGetAssignedUsers()
    {
        $assigned_users = $this->obj->getAssignedUsers(1);

        $this->assertEquals(1, count($assigned_users));
        $this->assertEquals(26, count($assigned_users[1]));

        $this->assertEquals(1,                                  $assigned_users[1]['user_id']);
        $this->assertEquals(1,                                  $assigned_users[1]['user_contact']);
        $this->assertEquals('admin',                            $assigned_users[1]['user_username']);
        $this->assertEquals('76a2173be6393254e72ffa4d6df1030a', $assigned_users[1]['user_password']);
        $this->assertEquals(0,                                  $assigned_users[1]['user_parent']);
        $this->assertEquals(1,                                  $assigned_users[1]['user_type']);
        $this->assertEquals('Admin User',                       $assigned_users[1]['user_signature']);
        $this->assertEquals(0,                                  $assigned_users[1]['user_empireint_special']);
        $this->assertEquals(0,                                  $assigned_users[1]['user_department']);
        $this->assertEquals(0,                                  $assigned_users[1]['user_company']);
        $this->assertEquals(50,                                 $assigned_users[1]['perc_assignment']);
        $this->assertEquals(42,                                 $assigned_users[1]['user_task_priority']);
        $this->assertEquals('Person',                           $assigned_users[1]['contact_last_name']);
        $this->assertEquals(1,                                  $assigned_users[1][0]);
        $this->assertEquals(1,                                  $assigned_users[1][1]);
        $this->assertEquals('admin',                            $assigned_users[1][2]);
        $this->assertEquals('76a2173be6393254e72ffa4d6df1030a', $assigned_users[1][3]);
        $this->assertEquals(0,                                  $assigned_users[1][4]);
        $this->assertEquals(1,                                  $assigned_users[1][5]);
        $this->assertEquals('Admin User',                       $assigned_users[1][6]);
        $this->assertEquals(0,                                  $assigned_users[1][7]);
        $this->assertEquals(0,                                  $assigned_users[1][8]);
        $this->assertEquals(0,                                  $assigned_users[1][9]);
        $this->assertEquals(50,                                 $assigned_users[1][10]);
        $this->assertEquals(42,                                 $assigned_users[1][11]);
        $this->assertEquals('Person',                           $assigned_users[1][12]);
    }
    /**
     * Test getting a list of dependencies
     */
    public function testGetDependencyList()
    {
        $dependencies = $this->obj->getDependencyList(3);

        $this->assertEquals(1, count($dependencies));
        $this->assertEquals('Task 4', $dependencies[4]);

        $dependencies = $this->obj->getDependencyList(200);
        $this->assertEquals(0, count($dependencies));
    }

    /**
     * Test getting a list of tasks dependent on this one
     */
    public function testGetDependentTaskList()
    {
        $dependent_tasks = $this->obj->getDependentTaskList(28);

        $this->assertEquals(2,          count($dependent_tasks));
        $this->assertEquals('Task 29',  $dependent_tasks[29]);
        $this->assertEquals('Task 30',  $dependent_tasks[30]);

        $dependent_tasks = $this->obj->getDependentTaskList(200);

        $this->assertEquals(0, count($dependent_tasks));
    }

    /**
     * Test getting a list of task contacts
     */
    public function testGetContacts()
    {
        global $AppUI;

        $task_contacts = $this->obj->getContacts($AppUI, 1);

        $this->assertEquals(1,                      count($task_contacts));
        $this->assertEquals(1,                      $task_contacts[1]['contact_id']);
        $this->assertEquals('Admin',                $task_contacts[1]['contact_first_name']);
        $this->assertEquals('Person',               $task_contacts[1]['contact_last_name']);
        $this->assertEquals('',                     $task_contacts[1]['contact_order_by']);
        $this->assertEquals('',                     $task_contacts[1]['dept_name']);
        $this->assertEquals(1,                      $task_contacts[1][0]);
        $this->assertEquals('Admin',                $task_contacts[1][1]);
        $this->assertEquals('Person',               $task_contacts[1][2]);
        $this->assertEquals('',                     $task_contacts[1][3]);
        $this->assertEquals('',                     $task_contacts[1][4]);

        // Login as another user for permission purposes
        $old_AppUI = $AppUI;
        $AppUI  = new CAppUI;
        $_POST['login'] = 'login';
        $_REQUEST['login'] = 'sql';

        $task_contacts = $this->obj->getContacts($AppUI, 2);

        $this->assertNull($task_contacts);

        // AppUI
        $AppUI = $old_AppUI;
    }

    /**
     * Test getting allocation of users, with no user list and not
     * checking over allocation
     */
    public function testGetAllocationNoGetUserListNoCheckOverallocation()
    {
        global $AppUI;
        global $w2Pconfig;

        // Ensure our global setting for check_overallocation is set properly for this
        $old_check_overallocation = $w2Pconfig['check_overallocation'];
        $w2Pconfig['check_overallocation'] = false;

        $allocation = $this->obj->getAllocation(null, null, false);

        $this->assertEquals(0,      count($allocation));
        $this->assertType('array',  $allocation);

        $w2Pconfig['check_overallocation'] = $old_check_overallocation;

    }

    /**
     * Test getting allocation of users with a user list but not
     * checking over allocation
     */
    public function testGetAllocationGetUserListNoCheckallocation()
    {
        global $AppUI;
        global $w2Pconfig;

        // Ensure our global setting for check_overallocation is set properly for this
        $old_check_overallocation = $w2Pconfig['check_overallocation'];
        $w2Pconfig['check_overallocation'] = false;
        $allocation = $this->obj->getAllocation(null, null, true);

        $this->assertEquals(2,                      count($allocation));
        $this->assertType('array',                  $allocation);
        $this->assertEquals(1,                      $allocation[1]['user_id']);
        $this->assertEquals('admin',                $allocation[1]['user_username']);
        $this->assertEquals('Person',               $allocation[1]['contact_last_name']);
        $this->assertEquals('Admin',                $allocation[1]['contact_first_name']);
        $this->assertEquals('UnitTestCompany',      $allocation[1]['company_name']);
        $this->assertEquals(1,                      $allocation[1]['contact_company']);
        $this->assertEquals('',                     $allocation[1]['dept_id']);
        $this->assertEquals('',                     $allocation[1]['dept_name']);
        $this->assertEquals('Admin Person',         $allocation[1]['contact_name']);
        $this->assertEquals(1,                      $allocation[1]['user_type']);
        $this->assertEquals('contact1@example.org', $allocation[1]['contact_email']);
        $this->assertEquals(1,                      $allocation[1][0]);
        $this->assertEquals('admin',                $allocation[1][1]);
        $this->assertEquals('Person',               $allocation[1][2]);
        $this->assertEquals('Admin',                $allocation[1][3]);
        $this->assertEquals('UnitTestCompany',      $allocation[1][4]);
        $this->assertEquals(1,                      $allocation[1][5]);
        $this->assertEquals('',                     $allocation[1][6]);
        $this->assertEquals('',                     $allocation[1][7]);
        $this->assertEquals('Admin Person',         $allocation[1][8]);
        $this->assertEquals(1,                      $allocation[1][9]);
        $this->assertEquals('contact1@example.org', $allocation[1][10]);
        $this->assertEquals('Admin Person',         $allocation[1]['userFC']);

        $this->assertEquals(2,                      $allocation[2]['user_id']);
        $this->assertEquals('reg_user',             $allocation[2]['user_username']);
        $this->assertEquals('Number 1',             $allocation[2]['contact_last_name']);
        $this->assertEquals('Contact',              $allocation[2]['contact_first_name']);
        $this->assertEquals('UnitTestCompany',      $allocation[2]['company_name']);
        $this->assertEquals(1,                      $allocation[2]['contact_company']);
        $this->assertEquals('',                     $allocation[2]['dept_id']);
        $this->assertEquals('',                     $allocation[2]['dept_name']);
        $this->assertEquals('Contact Number 1',     $allocation[2]['contact_name']);
        $this->assertEquals(1,                      $allocation[2]['user_type']);
        $this->assertEquals(2,                      $allocation[2][0]);
        $this->assertEquals('reg_user',             $allocation[2][1]);
        $this->assertEquals('Number 1',             $allocation[2][2]);
        $this->assertEquals('Contact',              $allocation[2][3]);
        $this->assertEquals('UnitTestCompany',      $allocation[2][4]);
        $this->assertEquals(1,                      $allocation[2][5]);
        $this->assertEquals('',                     $allocation[2][6]);
        $this->assertEquals('',                     $allocation[2][7]);
        $this->assertEquals('Contact Number 1',     $allocation[2][8]);
        $this->assertEquals(1,                      $allocation[2][9]);
        $this->assertEquals('Contact Number 1',     $allocation[2]['userFC']);

        $w2Pconfig['check_overallocation'] = $old_check_overallocation;
    }

    /**
     * Test getting allocation of users with checking over allocation
     */
    public function testGetAllocationCheckOverAllocation()
    {
        global $AppUI;
        global $w2Pconfig;

        // Ensure our global setting for check_overallocation is set properly for this
        $old_check_overallocation = $w2Pconfig['check_overallocation'];
        $w2Pconfig['check_overallocation'] = true;

        $allocation = $this->obj->getAllocation();

        $this->assertEquals('Admin Person [0%]', $allocation[1]);

        $w2Pconfig['check_overallocation'] = $old_check_overallocation;
    }

    /**
     * Tests getting allocation of users with hash passed for array key
     */
    public function testGetAllocationCheckOverAllocationHash()
    {
        global $AppUI;
        global $w2Pconfig;

        // Ensure our global setting for check_overallocation is set properly for this
        $old_check_overallocation = $w2Pconfig['check_overallocation'];
        $w2Pconfig['check_overallocation'] = true;

        $allocation = $this->obj->getAllocation('user_username');

        $this->assertEquals(1,                      $allocation['admin']['user_id']);
        $this->assertEquals('Admin Person [0%]',    $allocation['admin']['userFC']);
        $this->assertEquals(200,                    $allocation['admin']['charge']);
        $this->assertEquals('admin',                $allocation['admin']['user_username']);
        $this->assertEquals(100,                    $allocation['admin']['chargeMax']);
        $this->assertEquals(100,                    $allocation['admin']['freeCapacity']);
        $this->assertEquals(1,                      $allocation['admin'][0]);
        $this->assertEquals('Admin Person [0%]',    $allocation['admin'][1]);
        $this->assertEquals(200,                    $allocation['admin'][2]);
        $this->assertEquals('admin',                $allocation['admin'][3]);
        $this->assertEquals(100,                    $allocation['admin'][4]);
        $this->assertEquals(100,                    $allocation['admin'][5]);

        $w2Pconfig['check_overallocation'] = $old_check_overallocation;
    }

    /**
     * Tests getting allocation of users with user id specified
     */
    public function testGetAllocationCheckOverAllocationUsers()
    {
        global $AppUI;
        global $w2Pconfig;

        // Ensure our global setting for check_overallocation is set properly for this
        $old_check_overallocation = $w2Pconfig['check_overallocation'];
        $w2Pconfig['check_overallocation'] = true;

        $allocation = $this->obj->getAllocation(null, array(1));

        $this->assertEquals('Admin Person [0%]', $allocation[1]);

        $allocation = $this->obj->getAllocation(null, array(2));

        $this->assertEquals(0, count($allocation));

        $w2Pconfig['check_overallocation'] = $old_check_overallocation;

    }

    /**
     * Test getting specific task priority with no user or task passed
     */
    public function testGetUserSpecificTaskPriorityNoUserIdNoTaskId()
    {
        $this->obj->load(1);

        $this->assertNull($this->obj->getUserSpecificTaskPriority());
    }

    /**
     * Test getting specific task priority with a user id passed
     */
    public function testGetUserSpecificTaskPriorityUserIdNoTaskId()
    {
        $this->obj->load(1);

        $priority = $this->obj->getUserSpecificTaskPriority(1);

        $this->assertEquals(42, $priority);
    }

    /**
     * Test getting specific task priority with user id and task id passed
     */
    public function testGetUserSpecificTaskPriorityUserIdTaskId()
    {
        $this->obj->load(1);

        $priority = $this->obj->getUserSpecificTaskPriority(1, 2);

        $this->assertEquals(52, $priority);
    }

    /**
     * Test update specific task priority with no priority, user id or task id
     */
    public function testUpdateUserSpecificTaskPriorityNoPriorityNoUserIdNoTaskId()
    {
        $this->obj->load(1);

        $this->obj->updateUserSpecificTaskPriority();

        $xml_file_dataset = $this->createXMLDataSet($this->getDataSetPath().'tasksTestUpdateUserSpecificTaskPriorityNoPriorityNoUserIdNoTaskId.xml');
        $xml_db_dataset = $this->getConnection()->createDataSet();
        $this->assertTablesEqual($xml_file_dataset->getTable('user_tasks'), $xml_db_dataset->getTable('user_tasks'));
    }

    /**
     * Test update specific task priority with priority, no user id or task id
     */
    public function testUpdateUserSpecificTaskPriorityPriorityNoUserIdNoTaskId()
    {
        $this->obj->load(1);

        $this->obj->updateUserSpecificTaskPriority(1);

        $xml_file_dataset = $this->createXMLDataSet($this->getDataSetPath().'tasksTestUpdateUserSpecificTaskPriorityPriorityNoUserIdNoTaskId.xml');
        $xml_db_dataset = $this->getConnection()->createDataSet();
        $this->assertTablesEqual($xml_file_dataset->getTable('user_tasks'), $xml_db_dataset->getTable('user_tasks'));
    }

    /**
     * Test update specific task priority with priority, user id but no task id
     */
    public function testUpdateUserSpecificTaskPriorityPriorityUserIdNoTaskId()
    {
        $this->obj->load(1);

        $this->obj->updateUserSpecificTaskPriority(1, 1);

        $xml_file_dataset = $this->createXMLDataSet($this->getDataSetPath().'tasksTestUpdateUserSpecificTaskPriorityPriorityUserIdNoTaskId.xml');
        $xml_db_dataset = $this->getConnection()->createDataSet();
        $this->assertTablesEqual($xml_file_dataset->getTable('user_tasks'), $xml_db_dataset->getTable('user_tasks'));
    }

    /**
     * Test update specific task priority with priority, user id and task id
     */
    public function testUpdateUserSpecificTaskPriorityPriorityUserIdTaskId()
    {
        $this->obj->load(1);

        $this->obj->updateUserSpecificTaskPriority(1, 1, 2);

        $xml_file_dataset = $this->createXMLDataSet($this->getDataSetPath().'tasksTestUpdateUserSpecificTaskPriorityPriorityUserIdTaskId.xml');
        $xml_db_dataset = $this->getConnection()->createDataSet();
        $this->assertTablesEqual($xml_file_dataset->getTable('user_tasks'), $xml_db_dataset->getTable('user_tasks'));
    }

    /**
     * Test getting the project of currently loaded task
     */
    public function testGetProject()
    {
        $this->obj->load(1);

        $project = $this->obj->getProject();

        $this->assertEquals('Test Project', $project['project_name']);
        $this->assertEquals('TP',           $project['project_short_name']);
        $this->assertEquals('FFFFFF',       $project['project_color_identifier']);
    }

    /**
     * Tests updating subtasks status with no task id passed
     */
    public function testUpdateSubTasksStatusNoTaskId()
    {
        $this->obj->load(11);

        $this->obj->updateSubTasksStatus(99);

        $xml_file_dataset = $this->createXMLDataSet($this->getDataSetPath().'tasksTestUpdateSubTasksStatusNoTaskId.xml');
        $xml_db_dataset = $this->getConnection()->createDataSet();
        $this->assertTablesEqual($xml_file_dataset->getTable('tasks'), $xml_db_dataset->getTable('tasks'));
    }

    /**
     * Tests updating subtasks status with a task id passed
     */
    public function testUpdateSubTasksStatusTaskId()
    {
        $this->obj->load(1);

        $this->obj->updateSubTasksStatus(99, 11);

        $xml_file_dataset = $this->createXMLDataSet($this->getDataSetPath().'tasksTestUpdateSubTasksStatusTaskId.xml');
        $xml_db_dataset = $this->getConnection()->createDataSet();
        $this->assertTablesEqual($xml_file_dataset->getTable('tasks'), $xml_db_dataset->getTable('tasks'));
    }

    /**
     * Test updating project of sub tasks with no task id passed
     */
    public function testUpdateSubTasksProjectNoTaskId()
    {
        $this->obj->load(11);

        $this->obj->updateSubTasksProject(2);

        $xml_file_dataset = $this->createXMLDataSet($this->getDataSetpath().'tasksTestUpdateSubTasksProjectNoTaskId.xml');
        $xml_db_dataset = $this->getConnection()->createDataSet();
        $this->assertTablesEqual($xml_file_dataset->getTable('tasks'), $xml_db_dataset->getTable('tasks'));
    }

    /**
     * Tests updating project of sub tasks with task id passed
     */
    public function testUpdateSubTasksProjectTaskId()
    {
        $this->obj->load(1);

        $this->obj->updateSubTasksProject(2, 11);

        $xml_file_dataset = $this->createXMLDataset($this->getDataSetPath().'tasksTestUpdateSubTasksProjectTaskId.xml');
        $xml_db_dataset = $this->getConnection()->createDataSet();
        $this->assertTablesEqual($xml_file_dataset->getTable('tasks'), $xml_db_dataset->getTable('tasks'));
    }

    /**
     * Tests checking time information priviliges when user is a task owner
     */
    public function testCanUserEditTimeInformationTaskOwner()
    {
        global $AppUI;
        global $w2Pconfig;

        $this->obj->load(1);

        // Ensure our global setting for restrict_task_time_editing is set properly for this
        $old_restrict_task_time_editing = $w2Pconfig['restrict_task_time_editing'];
        $w2Pconfig['restrict_task_time_editing'] = true;

        $this->assertTrue(@$this->obj->canUserEditTimeInformation());

        $w2Pconfig['restrict_task_time_editing'] = $old_restrict_task_time_editing;
    }

    /**
     * Tests checking time information priviliges when user is project owner
     */
    public function testCanUserEditTimeInformationProjectOwner()
    {
        global $AppUI;
        global $w2Pconfig;

        $this->obj->load(4);

        // Ensure our global setting for restrict_task_time_editing is set properly for this
        $old_restrict_task_time_editing = $w2Pconfig['restrict_task_time_editing'];
        $w2Pconfig['restrict_task_time_editing'] = true;

        $this->assertTrue(@$this->obj->canUserEditTimeInformation());

        $w2Pconfig['restrict_task_time_editing'] = $old_restrict_task_time_editing;
    }

    /**
     * Tests checking time information priviliges when user is admin
     */
    public function testCanUserEditTimeInformationAdmin()
    {
        global $AppUI;
        global $w2Pconfig;

        $this->obj->load(4);

        // Ensure our global setting for restrict_task_time_editing is set properly for this
        $old_restrict_task_time_editing = $w2Pconfig['restrict_task_time_editing'];
        $w2Pconfig['restrict_task_time_editing'] = true;

        $this->assertTrue(@$this->obj->canUserEditTimeInformation());

        $w2Pconfig['restrict_task_time_editing'] = $old_restrict_task_time_editing;
    }

    /**
     * Tests checking time information priviliges without acess
     */
    public function testCanUserEditTimeInformationNoAccess()
    {
        global $AppUI;
        global $w2Pconfig;

        $this->obj->load(4);

        // Ensure our global setting for restrict_task_time_editing is set properly for this
        $old_restrict_task_time_editing = $w2Pconfig['restrict_task_time_editing'];
        $w2Pconfig['restrict_task_time_editing'] = true;

        // Login as another user for permission purposes
        $old_AppUI = $AppUI;
        $AppUI  = new CAppUI;
        $_POST['login'] = 'login';
        $_REQUEST['login'] = 'sql';

        $this->assertFalse(@$this->obj->canUserEditTimeInformation());

        $AppUI = $old_AppUI;
        $w2Pconfig['restrict_task_time_editing'] = $old_restrict_task_time_editing;
    }

    /**
     * Tests checking time information privileges with no rescrtions
     */
    public function testCanUserEditTimeInformationNoRestriction()
    {
        global $AppUI;
        global $w2Pconfig;

        $this->obj->load(4);

        // Ensure our global setting for restrict_task_time_editing is set properly for this
        $old_restrict_task_time_editing = $w2Pconfig['restrict_task_time_editing'];
        $w2Pconfig['restrict_task_time_editing'] = false;

        $this->assertTrue(@$this->obj->canUserEditTimeInformation());

        $w2Pconfig['restrict_task_time_editing'] = $old_restrict_task_time_editing;
    }

    /**
     * Tests add reminder task_reminder_control is off
     */
    public function testaddReminderNoTaskReminderControl()
    {
        global $AppUI;
        global $w2Pconfig;

        $this->obj->load(1);

        // Ensure our global setting for task_reminder_control is set properly for this
        $old_task_reminder_control = $w2Pconfig['task_reminder_control'];
        $w2Pconfig['task_reminder_control'] = false;

        $this->assertNull($this->obj->addReminder());

        $w2Pconfig['task_reminder_control'] = $old_task_reminder_control;
    }

    /**
     * Tests add reminder when there is no end date for task, so should remove from queue
     */
    public function testAddReminderNoTaskEndDate()
    {
        global $AppUI;
        global $w2Pconfig;

        unset($this->obj->task_end_date);

        // Ensure our global setting for task_reminder_control is set properly for this
        $old_task_reminder_control = $w2Pconfig['task_reminder_control'];
        $w2Pconfig['task_reminder_control'] = true;

        $this->obj->addReminder();

        $xml_file_dataset = $this->createXMLDataset($this->getDataSetPath().'tasksTestAddReminderNoTaskEndDate.xml');
        $xml_db_dataset = $this->getConnection()->createDataSet();
        $this->assertTablesEqual($xml_file_dataset->getTable('event_queue'), $xml_db_dataset->getTable('event_queue'));

        $w2Pconfig['task_reminder_control'] = $old_task_reminder_control;
    }

    /**
     * Tests add reminder when the task is complete, so should remove from queue
     */
    public function testAddReminderComplete()
    {
        global $AppUI;
        global $w2Pconfig;

        $this->obj->load(1);
        $this->obj->task_percent_complete = 100;

        // Ensure our global setting for task_reminder_control is set properly for this
        $old_task_reminder_control = $w2Pconfig['task_reminder_control'];
        $w2Pconfig['task_reminder_control'] = true;

        $this->obj->addReminder();

        $xml_file_dataset = $this->createXMLDataset($this->getDataSetPath().'tasksTestAddReminderComplete.xml');
        $xml_db_dataset = $this->getConnection()->createDataSet();
        $this->assertTablesEqual($xml_file_dataset->getTable('event_queue'), $xml_db_dataset->getTable('event_queue'));

        $w2Pconfig['task_reminder_control'] = $old_task_reminder_control;
    }

    /**
     * Tests adding a reminder to a task
     */
    public function testAddReminder()
    {
        global $AppUI;
        global $w2Pconfig;

        $this->obj->load(1);

        // Ensure our global setting for task_reminder_control is set properly for this
        $old_task_reminder_control = $w2Pconfig['task_reminder_control'];
        $w2Pconfig['task_reminder_control'] = true;

        $this->obj->addReminder();
        $this->obj->task_percent_complete = 50;

        $xml_file_dataset = $this->createXMLDataset($this->getDataSetPath().'tasksTestAddReminder.xml');
        $xml_file_filtered_dataset = new PHPUnit_Extensions_Database_DataSet_DataSetFilter($xml_file_dataset, array('event_queue' => array('queue_start')));
        $xml_db_dataset = $this->getConnection()->createDataSet();
        $xml_db_filtered_dataset = new PHPUnit_Extensions_Database_DataSet_DataSetFilter($xml_db_dataset, array('event_queue' => array('queue_start')));
        $this->assertTablesEqual($xml_file_filtered_dataset->getTable('event_queue'), $xml_db_filtered_dataset->getTable('event_queue'));

        $now_secs = time();
        $min_time = $now_secs - 10;

        /**
         * Get updated dates to test against
         */
        $q = new DBQuery;
        $q->addTable('event_queue');
        $q->addQuery('queue_start');
        $q->addWhere('queue_id = 2');
        $results = $q->loadColumn();

        foreach($results as $queue_start) {
            $this->assertGreaterThanOrEqual($min_time, $queue_start);
            $this->assertLessThanOrEqual($now_secs, $queue_start);
        }

        $w2Pconfig['task_reminder_control'] = $old_task_reminder_control;
    }

    /**
     * Tests the remind function when it is not a working day
     */
    public function testRemindNotWorkingDay()
    {
        global $AppUI;
        global $w2Pconfig;

        $this->obj->load(1);

        // Ensure our global setting for task_reminder_control is set properly for this
        $old_cal_working_days = $w2Pconfig['cal_working_days'];
        $w2Pconfig['cal_working_days'] = array();

        $nothing = array();

        $this->assertTrue($this->obj->remind('not used', 'not used', 1, 1, $nothing));

        $w2Pconfig['cal_working_days'] = $old_cal_working_days;
    }

    /**
     * Tests the remind function when a task is complete
     */
    public function testRemindTaskComplete()
    {
        global $AppUI;
        global $w2Pconfig;

        $this->post_data['task_percent_complete'] = '100';
        $this->obj->bind($this->post_data);
        $results = $this->obj->store();

        // Ensure our global setting for task_reminder_control is set properly for this
        $old_cal_working_days = $w2Pconfig['cal_working_days'];
        $w2Pconfig['cal_working_days'] = '0,1,2,3,4,5,6';

        $nothing = array();

        $this->assertEquals(-1, $this->obj->remind('not used', 'not used', 31, 1, $nothing));
        $w2Pconfig['cal_working_days'] = $old_cal_working_days;
    }

    /**
     * Test the remind function
     * @todo Not sure how we can test reliably that the email was actually
     * generated and sent
     */
    public function testRemind()
    {
        global $AppUI;
        global $w2Pconfig;

        // Ensure our global setting for task_reminder_control is set properly for this
        $old_cal_working_days = $w2Pconfig['cal_working_days'];
        $w2Pconfig['cal_working_days'] = '1,2,3,4,5,6,7';

        $nothing = array();

        $this->assertTrue($this->obj->remind('not used', 'not used', 1, 1, $nothing));
        $w2Pconfig['cal_working_days'] = $old_cal_working_days;
    }

    /**
     * Tests clearing a reminder when we are checking that it is complete
     */
    public function testClearReminderDoCheck()
    {
        $this->obj->load(1);

        $this->obj->clearReminder();

        $xml_file_dataset = $this->createXMLDataSet($this->getDataSetPath().'tasksTestClearReminderDoCheck.xml');
        $xml_db_dataset = $this->getConnection()->createDataSet();
        $this->assertTablesEqual($xml_file_dataset->getTable('event_queue'), $xml_db_dataset->getTable('event_queue'));
    }

    /**
     * Tests clearing a reminder when we are checking it is complete and it is complete
     */
    public function testClearReminderDoCheckTaskComplete()
    {
        $this->obj->load(1);
        $this->obj->task_percent_complete = 100;

        $this->obj->clearReminder();

        $xml_file_dataset = $this->createXMLDataSet($this->getDataSetPath().'tasksTestClearReminderDoCheckTaskComplete.xml');
        $xml_db_dataset = $this->getConnection()->createDataSet();
        $this->assertTablesEqual($xml_file_dataset->getTable('event_queue'), $xml_db_dataset->getTable('event_queue'));
    }

    /**
     * Tests clearing a reminder when not checking that the task is complete
     */
    public function testClearReminderDontCheck()
    {
        $this->obj->load(1);

        $this->obj->clearReminder(true);

        $xml_file_dataset = $this->createXMLDataSet($this->getDataSetPath().'tasksTestClearReminderDontCheck.xml');
        $xml_db_dataset = $this->getConnection()->createDataSet();
        $this->assertTablesEqual($xml_file_dataset->getTable('event_queue'), $xml_db_dataset->getTable('event_queue'));
    }

    /**
     * Test getting allowed records with a uid passed
     */
    public function testGetAllowedRecordUid()
    {
        global $AppUI;

        $allowed_records = $this->obj->getAllowedRecords(1);

        $this->assertEquals(30,        count($allowed_records));
        $this->assertEquals('Task 1',  $allowed_records[1]);
        $this->assertEquals('Task 2',  $allowed_records[2]);
        $this->assertEquals('Task 3',  $allowed_records[3]);
        $this->assertEquals('Task 4',  $allowed_records[4]);
        $this->assertEquals('Task 5',  $allowed_records[5]);
        $this->assertEquals('Task 6',  $allowed_records[6]);
        $this->assertEquals('Task 7',  $allowed_records[7]);
        $this->assertEquals('Task 8',  $allowed_records[8]);
        $this->assertEquals('Task 9',  $allowed_records[9]);
        $this->assertEquals('Task 10', $allowed_records[10]);
        $this->assertEquals('Task 11', $allowed_records[11]);
        $this->assertEquals('Task 12', $allowed_records[12]);
        $this->assertEquals('Task 13', $allowed_records[13]);
        $this->assertEquals('Task 14', $allowed_records[14]);
        $this->assertEquals('Task 15', $allowed_records[15]);
        $this->assertEquals('Task 16', $allowed_records[16]);
        $this->assertEquals('Task 17', $allowed_records[17]);
        $this->assertEquals('Task 18', $allowed_records[18]);
        $this->assertEquals('Task 19', $allowed_records[19]);
        $this->assertEquals('Task 20', $allowed_records[20]);
        $this->assertEquals('Task 21', $allowed_records[21]);
        $this->assertEquals('Task 22', $allowed_records[22]);
        $this->assertEquals('Task 23', $allowed_records[23]);
        $this->assertEquals('Task 24', $allowed_records[24]);
        $this->assertEquals('Task 25', $allowed_records[25]);
        $this->assertEquals('Task 26', $allowed_records[26]);
        $this->assertEquals('Task 27', $allowed_records[27]);
        $this->assertEquals('Task 28', $allowed_records[28]);
        $this->assertEquals('Task 29', $allowed_records[29]);
        $this->assertEquals('Task 30', $allowed_records[30]);
    }

    /**
     * Test getting allowed records when passing a uid and fields
     */
    public function testGetAllowedRecordsFields()
    {
        $allowed_records = $this->obj->getAllowedRecords(1, 'tasks.task_id, tasks.task_start_date');

        $this->assertEquals(30,                     count($allowed_records));
        $this->assertEquals('2009-07-05 00:00:00',  $allowed_records[1]);
        $this->assertEquals('2009-07-06 00:00:00',  $allowed_records[2]);
        $this->assertEquals('2009-07-06 00:00:00',  $allowed_records[3]);
        $this->assertEquals('2009-07-06 00:00:00',  $allowed_records[4]);
        $this->assertEquals('2009-07-06 00:00:00',  $allowed_records[5]);
        $this->assertEquals('2009-07-06 00:00:00',  $allowed_records[6]);
        $this->assertEquals('2009-07-06 00:00:00',  $allowed_records[7]);
        $this->assertEquals('2009-07-06 00:00:00',  $allowed_records[8]);
        $this->assertEquals('2009-07-06 00:00:00',  $allowed_records[9]);
        $this->assertEquals('2009-07-06 00:00:00',  $allowed_records[10]);
        $this->assertEquals('2009-07-06 00:00:00',  $allowed_records[11]);
        $this->assertEquals('2009-07-06 00:00:00',  $allowed_records[12]);
        $this->assertEquals('2009-07-06 00:00:00',  $allowed_records[13]);
        $this->assertEquals('2009-07-06 00:00:00',  $allowed_records[14]);
        $this->assertEquals('2009-07-06 00:00:00',  $allowed_records[15]);
        $this->assertEquals('2009-07-06 00:00:00',  $allowed_records[16]);
        $this->assertEquals('2009-07-06 00:00:00',  $allowed_records[17]);
        $this->assertEquals('2009-07-06 00:00:00',  $allowed_records[18]);
        $this->assertEquals('2009-07-06 00:00:00',  $allowed_records[19]);
        $this->assertEquals('2009-07-06 00:00:00',  $allowed_records[20]);
        $this->assertEquals('2009-07-06 00:00:00',  $allowed_records[21]);
        $this->assertEquals('2009-09-09 00:00:00',  $allowed_records[22]);
        $this->assertEquals('2009-10-10 00:00:00',  $allowed_records[23]);
        $this->assertEquals('2009-07-06 00:00:00',  $allowed_records[24]);
        $this->assertEquals('2009-09-09 00:00:00',  $allowed_records[25]);
        $this->assertEquals('2009-10-10 00:00:00',  $allowed_records[26]);
        $this->assertEquals('2009-10-10 00:00:00',  $allowed_records[27]);
        $this->assertEquals('2009-10-10 00:00:00',  $allowed_records[28]);
        $this->assertEquals('2009-07-06 00:00:00',  $allowed_records[29]);
        $this->assertEquals('2009-10-10 00:00:00',  $allowed_records[30]);
    }

    /**
     * Tests getting allowed records with an order by
     */
    public function testGetAllowedRecordsOrderBy()
    {
        $allowed_records = $this->obj->getAllowedRecords(1, '*', 'tasks.task_start_date');

        $this->assertEquals(30,        count($allowed_records));
        $this->assertEquals('Task 1',  $allowed_records[1]);
        $this->assertEquals('Task 2',  $allowed_records[2]);
        $this->assertEquals('Task 3',  $allowed_records[3]);
        $this->assertEquals('Task 4',  $allowed_records[4]);
        $this->assertEquals('Task 5',  $allowed_records[5]);
        $this->assertEquals('Task 6',  $allowed_records[6]);
        $this->assertEquals('Task 7',  $allowed_records[7]);
        $this->assertEquals('Task 8',  $allowed_records[8]);
        $this->assertEquals('Task 9',  $allowed_records[9]);
        $this->assertEquals('Task 10', $allowed_records[10]);
        $this->assertEquals('Task 11', $allowed_records[11]);
        $this->assertEquals('Task 12', $allowed_records[12]);
        $this->assertEquals('Task 13', $allowed_records[13]);
        $this->assertEquals('Task 14', $allowed_records[14]);
        $this->assertEquals('Task 15', $allowed_records[15]);
        $this->assertEquals('Task 16', $allowed_records[16]);
        $this->assertEquals('Task 17', $allowed_records[17]);
        $this->assertEquals('Task 18', $allowed_records[18]);
        $this->assertEquals('Task 19', $allowed_records[19]);
        $this->assertEquals('Task 20', $allowed_records[20]);
        $this->assertEquals('Task 21', $allowed_records[21]);
        $this->assertEquals('Task 22', $allowed_records[22]);
        $this->assertEquals('Task 23', $allowed_records[23]);
        $this->assertEquals('Task 24', $allowed_records[24]);
        $this->assertEquals('Task 25', $allowed_records[25]);
        $this->assertEquals('Task 26', $allowed_records[26]);
        $this->assertEquals('Task 27', $allowed_records[27]);
        $this->assertEquals('Task 28', $allowed_records[28]);
        $this->assertEquals('Task 29', $allowed_records[29]);
        $this->assertEquals('Task 30', $allowed_records[30]);
    }

    /**
     * Test getting allowed records with an index specified
     */
    public function testGetAllowedRecordsIndex()
    {
        $allowed_records = $this->obj->getAllowedRecords(1, 'tasks.task_name', '', 'task_name');

        $this->assertEquals(30,        count($allowed_records));
        $this->assertEquals('Task 1',  $allowed_records['Task 1']['task_name']);
        $this->assertEquals('Task 1',  $allowed_records['Task 1'][0]);
        $this->assertEquals('Task 2',  $allowed_records['Task 2']['task_name']);
        $this->assertEquals('Task 2',  $allowed_records['Task 2'][0]);
        $this->assertEquals('Task 3',  $allowed_records['Task 3']['task_name']);
        $this->assertEquals('Task 3',  $allowed_records['Task 3'][0]);
        $this->assertEquals('Task 4',  $allowed_records['Task 4']['task_name']);
        $this->assertEquals('Task 4',  $allowed_records['Task 4'][0]);
        $this->assertEquals('Task 5',  $allowed_records['Task 5']['task_name']);
        $this->assertEquals('Task 5',  $allowed_records['Task 5'][0]);
        $this->assertEquals('Task 6',  $allowed_records['Task 6']['task_name']);
        $this->assertEquals('Task 6',  $allowed_records['Task 6'][0]);
        $this->assertEquals('Task 7',  $allowed_records['Task 7']['task_name']);
        $this->assertEquals('Task 7',  $allowed_records['Task 7'][0]);
        $this->assertEquals('Task 8',  $allowed_records['Task 8']['task_name']);
        $this->assertEquals('Task 8',  $allowed_records['Task 8'][0]);
        $this->assertEquals('Task 9',  $allowed_records['Task 9']['task_name']);
        $this->assertEquals('Task 9',  $allowed_records['Task 9'][0]);
        $this->assertEquals('Task 10', $allowed_records['Task 10']['task_name']);
        $this->assertEquals('Task 10', $allowed_records['Task 10'][0]);
        $this->assertEquals('Task 11', $allowed_records['Task 11']['task_name']);
        $this->assertEquals('Task 11', $allowed_records['Task 11'][0]);
        $this->assertEquals('Task 12', $allowed_records['Task 12']['task_name']);
        $this->assertEquals('Task 12', $allowed_records['Task 12'][0]);
        $this->assertEquals('Task 13', $allowed_records['Task 13']['task_name']);
        $this->assertEquals('Task 13', $allowed_records['Task 13'][0]);
        $this->assertEquals('Task 14', $allowed_records['Task 14']['task_name']);
        $this->assertEquals('Task 14', $allowed_records['Task 14'][0]);
        $this->assertEquals('Task 15', $allowed_records['Task 15']['task_name']);
        $this->assertEquals('Task 15', $allowed_records['Task 15'][0]);
        $this->assertEquals('Task 16', $allowed_records['Task 16']['task_name']);
        $this->assertEquals('Task 16', $allowed_records['Task 16'][0]);
        $this->assertEquals('Task 17', $allowed_records['Task 17']['task_name']);
        $this->assertEquals('Task 17', $allowed_records['Task 17'][0]);
        $this->assertEquals('Task 18', $allowed_records['Task 18']['task_name']);
        $this->assertEquals('Task 18', $allowed_records['Task 18'][0]);
        $this->assertEquals('Task 19', $allowed_records['Task 19']['task_name']);
        $this->assertEquals('Task 19', $allowed_records['Task 19'][0]);
        $this->assertEquals('Task 20', $allowed_records['Task 20']['task_name']);
        $this->assertEquals('Task 20', $allowed_records['Task 20'][0]);
        $this->assertEquals('Task 21', $allowed_records['Task 21']['task_name']);
        $this->assertEquals('Task 21', $allowed_records['Task 21'][0]);
        $this->assertEquals('Task 22', $allowed_records['Task 22']['task_name']);
        $this->assertEquals('Task 22', $allowed_records['Task 22'][0]);
        $this->assertEquals('Task 23', $allowed_records['Task 23']['task_name']);
        $this->assertEquals('Task 23', $allowed_records['Task 23'][0]);
        $this->assertEquals('Task 24', $allowed_records['Task 24']['task_name']);
        $this->assertEquals('Task 24', $allowed_records['Task 24'][0]);
        $this->assertEquals('Task 25', $allowed_records['Task 25']['task_name']);
        $this->assertEquals('Task 25', $allowed_records['Task 25'][0]);
        $this->assertEquals('Task 26', $allowed_records['Task 26']['task_name']);
        $this->assertEquals('Task 26', $allowed_records['Task 26'][0]);
        $this->assertEquals('Task 27', $allowed_records['Task 27']['task_name']);
        $this->assertEquals('Task 27', $allowed_records['Task 27'][0]);
        $this->assertEquals('Task 28', $allowed_records['Task 28']['task_name']);
        $this->assertEquals('Task 28', $allowed_records['Task 28'][0]);
        $this->assertEquals('Task 29', $allowed_records['Task 29']['task_name']);
        $this->assertEquals('Task 29', $allowed_records['Task 29'][0]);
        $this->assertEquals('Task 30', $allowed_records['Task 30']['task_name']);
        $this->assertEquals('Task 30', $allowed_records['Task 30'][0]);
    }

    /**
     * Test getting allowed records with extra data specified
     */
    public function testGetAllowedRecordsExtra()
    {
        $extra = array('from' => 'tasks', 'join' => 'projects', 'on' => 'project_id = task_project',
                       'where' => 'project_id = 2');
        $allowed_records = $this->obj->getAllowedRecords(1, '*', '', null, $extra);

        $this->assertType(PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $allowed_records);
        $this->assertEquals(0, count($allowed_records));
    }

    /**
     * Test getting list of users assigned to a task
     */
    public function testGetAssigned()
    {
        $this->obj->load(1);
        $assigned = $this->obj->getAssigned();

        $this->assertEquals(1,              count($assigned));
        $this->assertEquals(1,              $assigned[1]['user_id']);
        $this->assertEquals('Admin Person', $assigned[1]['user_name']);
        $this->assertEquals(50,             $assigned[1]['perc_assignment']);
        $this->assertEquals(1,              $assigned[1][0]);
        $this->assertEquals('Admin Person', $assigned[1][1]);
        $this->assertEquals(50,             $assigned[1][2]);
    }

    /**
     *  Test getting list of task logs with no problems
     */
    public function testGetTaskLogs()
    {
        $task_logs = $this->obj->getTaskLogs(22);

        $this->assertEquals(1,                              count($task_logs));
        $this->assertEquals(1,                              $task_logs[0]['task_log_id']);
        $this->assertEquals(22,                             $task_logs[0]['task_log_task']);
        $this->assertEquals(1,                              $task_logs[0]['task_log_help_desk_id']);
        $this->assertEquals('Task Log 1',                   $task_logs[0]['task_log_name']);
        $this->assertEquals('Did some working on task 22.', $task_logs[0]['task_log_description']);
        $this->assertEquals(1,                              $task_logs[0]['task_log_creator']);
        $this->assertEquals(20,                             $task_logs[0]['task_log_hours']);
        $this->assertEquals('2009-10-19',                   $task_logs[0]['task_log_date']);
        $this->assertEquals('Cheap',                        $task_logs[0]['task_log_costcode']);
        $this->assertEquals('',                             $task_logs[0]['task_log_problem']);
        $this->assertEquals('',                             $task_logs[0]['task_log_references']);
        $this->assertEquals('http://www.example.org',       $task_logs[0]['task_log_related_url']);
        $this->assertEquals(1,                              $task_logs[0]['task_log_project']);
        $this->assertEquals(1,                              $task_logs[0]['task_log_company']);
        //$this->assertEquals('2009-10-19 00:00:00',          $task_logs[0]['task_log_created']);
        $this->assertEquals('2009-10-19 00:00:00',          $task_logs[0]['task_log_updated']);
        $this->assertEquals(0,                              $task_logs[0]['task_log_updator']);
        $this->assertEquals('admin',                        $task_logs[0]['user_username']);
        $this->assertEquals('Admin Person',                 $task_logs[0]['real_name']);
    }

    /**
     *  Test getting list of task logs with problems
     */
    public function testGetTaskLogsWithProblems()
    {
        $task_logs = $this->obj->getTaskLogs(23);

        $this->assertEquals(1,                              count($task_logs));
        $this->assertEquals(2,                              $task_logs[0]['task_log_id']);
        $this->assertEquals(23,                             $task_logs[0]['task_log_task']);
        $this->assertEquals(1,                              $task_logs[0]['task_log_help_desk_id']);
        $this->assertEquals('Task Log 2',                   $task_logs[0]['task_log_name']);
        $this->assertEquals('Did some working on task 23.', $task_logs[0]['task_log_description']);
        $this->assertEquals(1,                              $task_logs[0]['task_log_creator']);
        $this->assertEquals(17,                             $task_logs[0]['task_log_hours']);
        $this->assertEquals('2009-10-19',                   $task_logs[0]['task_log_date']);
        $this->assertEquals('Cheap',                        $task_logs[0]['task_log_costcode']);
        $this->assertEquals(1,                              $task_logs[0]['task_log_problem']);
        $this->assertEquals('',                             $task_logs[0]['task_log_references']);
        $this->assertEquals('http://www.example.org',       $task_logs[0]['task_log_related_url']);
        $this->assertEquals(1,                              $task_logs[0]['task_log_project']);
        $this->assertEquals(1,                              $task_logs[0]['task_log_company']);
        //$this->assertEquals('2009-10-19 00:00:00',          $task_logs[0]['task_log_created']);
        $this->assertEquals('2009-10-19 00:00:00',          $task_logs[0]['task_log_updated']);
        $this->assertEquals(0,                              $task_logs[0]['task_log_updator']);
        $this->assertEquals('admin',                        $task_logs[0]['user_username']);
        $this->assertEquals('Admin Person',                 $task_logs[0]['real_name']);
    }

    /**
    * Test getting list of tasks for user, including ical intregration
    */
    public function testHookCalendar()
    {
        $calendar_tasks = $this->obj->hook_calendar(1);

        $this->assertEquals(6,                                                          count($calendar_tasks));
        $this->assertEquals(1,                                                          $calendar_tasks[0]['id']);
        $this->assertEquals('Start: Task 1',                                            $calendar_tasks[0]['name']);
        $this->assertEquals('This is task 1',                                           $calendar_tasks[0]['description']);
        $this->assertEquals('2009-07-05 00:00:00',                                      $calendar_tasks[0]['startDate']);
        $this->assertEquals('2009-07-05 00:00:00',                                      $calendar_tasks[0]['endDate']);
        $this->assertRegExp('^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}^',  $calendar_tasks[0]['updatedDate']);
        $this->assertContains('index.php?m=tasks&a=view&task_id=1',                     $calendar_tasks[0]['url']);
        $this->assertEquals(1,                                                          $calendar_tasks[0]['project_id']);
        $this->assertEquals('Test Project',                                             $calendar_tasks[0]['project_name']);
        $this->assertEquals(1,                                                          $calendar_tasks[1]['id']);
        $this->assertEquals('End: Task 1',                                              $calendar_tasks[1]['name']);
        $this->assertEquals('This is task 1',                                           $calendar_tasks[1]['description']);
        $this->assertEquals('2009-07-15 00:00:00',                                      $calendar_tasks[1]['startDate']);
        $this->assertEquals('2009-07-15 00:00:00',                                      $calendar_tasks[1]['endDate']);
        $this->assertRegExp('^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}^',  $calendar_tasks[1]['updatedDate']);
        $this->assertContains('index.php?m=tasks&a=view&task_id=1',                     $calendar_tasks[1]['url']);
        $this->assertEquals(1,                                                          $calendar_tasks[1]['project_id']);
        $this->assertEquals('Test Project',                                             $calendar_tasks[1]['project_name']);
        $this->assertEquals(2,                                                          $calendar_tasks[2]['id']);
        $this->assertEquals('Start: Task 2',                                            $calendar_tasks[2]['name']);
        $this->assertEquals('This is task 2',                                           $calendar_tasks[2]['description']);
        $this->assertEquals('2009-07-06 00:00:00',                                      $calendar_tasks[2]['startDate']);
        $this->assertEquals('2009-07-06 00:00:00',                                      $calendar_tasks[2]['endDate']);
        $this->assertRegExp('^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}^',  $calendar_tasks[2]['updatedDate']);
        $this->assertContains('index.php?m=tasks&a=view&task_id=2',                     $calendar_tasks[2]['url']);
        $this->assertEquals(1,                                                          $calendar_tasks[2]['project_id']);
        $this->assertEquals('Test Project',                                             $calendar_tasks[2]['project_name']);
        $this->assertEquals(2,                                                          $calendar_tasks[3]['id']);
        $this->assertEquals('End: Task 2',                                              $calendar_tasks[3]['name']);
        $this->assertEquals('This is task 2',                                           $calendar_tasks[3]['description']);
        $this->assertEquals('2009-07-16 00:00:00',                                      $calendar_tasks[3]['startDate']);
        $this->assertEquals('2009-07-16 00:00:00',                                      $calendar_tasks[3]['endDate']);
        $this->assertRegExp('^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}^',  $calendar_tasks[3]['updatedDate']);
        $this->assertContains('index.php?m=tasks&a=view&task_id=2',                     $calendar_tasks[3]['url']);
        $this->assertEquals(1,                                                          $calendar_tasks[3]['project_id']);
        $this->assertEquals('Test Project',                                             $calendar_tasks[3]['project_name']);
        $this->assertEquals(22,                                                         $calendar_tasks[4]['id']);
        $this->assertEquals('Start: Task 22',                                           $calendar_tasks[4]['name']);
        $this->assertEquals('This is task 22',                                          $calendar_tasks[4]['description']);
        $this->assertEquals('2009-09-09 00:00:00',                                      $calendar_tasks[4]['startDate']);
        $this->assertEquals('2009-09-09 00:00:00',                                      $calendar_tasks[4]['endDate']);
        $this->assertRegExp('^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}^',  $calendar_tasks[4]['updatedDate']);
        $this->assertContains('index.php?m=tasks&a=view&task_id=22',                    $calendar_tasks[4]['url']);
        $this->assertEquals(1,                                                          $calendar_tasks[4]['project_id']);
        $this->assertEquals('Test Project',                                             $calendar_tasks[4]['project_name']);
        $this->assertEquals(22,                                                         $calendar_tasks[5]['id']);
        $this->assertEquals('End: Task 22',                                             $calendar_tasks[5]['name']);
        $this->assertEquals('This is task 22',                                          $calendar_tasks[5]['description']);
        $this->assertEquals('2009-11-01 00:00:00',                                      $calendar_tasks[5]['startDate']);
        $this->assertEquals('2009-11-01 00:00:00',                                      $calendar_tasks[5]['endDate']);
        $this->assertRegExp('^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}^',  $calendar_tasks[5]['updatedDate']);
        $this->assertContains('index.php?m=tasks&a=view&task_id=22',                    $calendar_tasks[5]['url']);
        $this->assertEquals(1,                                                          $calendar_tasks[5]['project_id']);
        $this->assertEquals('Test Project',                                             $calendar_tasks[5]['project_name']);
    }

    /**
     * Test returning proper data for search hook
     */
    public function testHookSearch()
    {
        $search_data = $this->obj->hook_search();

        $this->assertEquals(8,                                      count($search_data));
        $this->assertEquals('tasks',                                $search_data['table']);
        $this->assertEquals('tasks',                                $search_data['table_module']);
        $this->assertEquals('task_id',                              $search_data['table_key']);
        $this->assertEquals('index.php?m=tasks&a=view&task_id=',    $search_data['table_link']);
        $this->assertEquals('Tasks',                                $search_data['table_title']);
        $this->assertEquals('task_name',                            $search_data['table_orderby']);
        $this->assertEquals(6,                                      count($search_data['search_fields']));
        $this->assertEquals('task_name',                            $search_data['search_fields'][0]);
        $this->assertEquals('task_description',                     $search_data['search_fields'][1]);
        $this->assertEquals('task_related_url',                     $search_data['search_fields'][2]);
        $this->assertEquals('task_departments',                     $search_data['search_fields'][3]);
        $this->assertEquals('task_contacts',                        $search_data['search_fields'][4]);
        $this->assertEquals('task_custom',                          $search_data['search_fields'][5]);
        $this->assertEquals(6,                                      count($search_data['display_fields']));
        $this->assertEquals('task_name',                            $search_data['display_fields'][0]);
        $this->assertEquals('task_description',                     $search_data['display_fields'][1]);
        $this->assertEquals('task_related_url',                     $search_data['display_fields'][2]);
        $this->assertEquals('task_departments',                     $search_data['display_fields'][3]);
        $this->assertEquals('task_contacts',                        $search_data['display_fields'][4]);
        $this->assertEquals('task_custom',                          $search_data['display_fields'][5]);
    }

    /**
     * Test getting task list with no days passed
     */
    public function testGetTaskListNoDays()
    {
        $task_list = $this->obj->getTaskList(1);

        $this->assertEquals(3,                                                          count($task_list));
        $this->assertEquals(1,                                                          $task_list[0]['id']);
        $this->assertEquals('Task 1',                                                   $task_list[0]['name']);
        $this->assertEquals('This is task 1',                                           $task_list[0]['description']);
        $this->assertEquals('2009-07-05 00:00:00',                                      $task_list[0]['startDate']);
        $this->assertEquals('2009-07-15 00:00:00',                                      $task_list[0]['endDate']);
        $this->assertRegExp('^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}^',  $task_list[0]['updatedDate']);
        $this->assertContains('index.php?m=tasks&a=view&task_id=1',                     $task_list[0]['url']);
        $this->assertEquals(1,                                                          $task_list[0]['project_id']);
        $this->assertEquals('Test Project',                                             $task_list[0]['project_name']);
        $this->assertEquals(2,                                                          $task_list[1]['id']);
        $this->assertEquals('Task 2',                                                   $task_list[1]['name']);
        $this->assertEquals('This is task 2',                                           $task_list[1]['description']);
        $this->assertEquals('2009-07-06 00:00:00',                                      $task_list[1]['startDate']);
        $this->assertEquals('2009-07-16 00:00:00',                                      $task_list[1]['endDate']);
        $this->assertRegExp('^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}^',  $task_list[1]['updatedDate']);
        $this->assertContains('index.php?m=tasks&a=view&task_id=2',                     $task_list[1]['url']);
        $this->assertEquals(1,                                                          $task_list[1]['project_id']);
        $this->assertEquals('Test Project',                                             $task_list[1]['project_name']);
        $this->assertEquals(22,                                                         $task_list[2]['id']);
        $this->assertEquals('Task 22',                                                  $task_list[2]['name']);
        $this->assertEquals('This is task 22',                                          $task_list[2]['description']);
        $this->assertEquals('2009-09-09 00:00:00',                                      $task_list[2]['startDate']);
        $this->assertEquals('2009-11-01 00:00:00',                                      $task_list[2]['endDate']);
        $this->assertRegExp('^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}^',  $task_list[2]['updatedDate']);
        $this->assertContains('index.php?m=tasks&a=view&task_id=22',                    $task_list[2]['url']);
        $this->assertEquals(1,                                                          $task_list[2]['project_id']);
        $this->assertEquals('Test Project',                                             $task_list[2]['project_name']);
    }

    /**
     * Test getting tasks with a number of days passed
     */
    public function testGetTaskListDays()
    {
        $task_list = $this->obj->getTaskList(1, -10000);

        $this->assertType('array',  $task_list);
        $this->assertEquals(0,      count($task_list));
    }

    /**
     * Test getting allowed tasks with permissions and no project passed
     */
    public function testGetAllowedTaskListAllowedNoProject()
    {
        global $AppUI;

        $allowed_task_list = $this->obj->getAllowedTaskList($AppUI);

        $this->assertEquals(30,         count($allowed_task_list));
        $this->assertEquals(1,          $allowed_task_list[0]['task_id']);
        $this->assertEquals('Task 1',   $allowed_task_list[0]['task_name']);
        $this->assertEquals(0,          $allowed_task_list[0]['task_parent']);
        $this->assertEquals(0,          $allowed_task_list[0]['task_access']);
        $this->assertEquals(1,          $allowed_task_list[0]['task_owner']);
        $this->assertEquals(3,          $allowed_task_list[1]['task_id']);
        $this->assertEquals('Task 3',   $allowed_task_list[1]['task_name']);
        $this->assertEquals(0,          $allowed_task_list[1]['task_parent']);
        $this->assertEquals(2,          $allowed_task_list[1]['task_access']);
        $this->assertEquals(1,          $allowed_task_list[1]['task_owner']);
        $this->assertEquals(4,          $allowed_task_list[2]['task_id']);
        $this->assertEquals('Task 4',   $allowed_task_list[2]['task_name']);
        $this->assertEquals(0,          $allowed_task_list[2]['task_parent']);
        $this->assertEquals(3,          $allowed_task_list[2]['task_access']);
        $this->assertEquals(2,          $allowed_task_list[2]['task_owner']);
        $this->assertEquals(5,          $allowed_task_list[3]['task_id']);
        $this->assertEquals('Task 5',   $allowed_task_list[3]['task_name']);
        $this->assertEquals(0,          $allowed_task_list[3]['task_parent']);
        $this->assertEquals(1,          $allowed_task_list[3]['task_access']);
        $this->assertEquals(1,          $allowed_task_list[3]['task_owner']);
        $this->assertEquals(2,          $allowed_task_list[4]['task_id']);
        $this->assertEquals('Task 2',   $allowed_task_list[4]['task_name']);
        $this->assertEquals(1,          $allowed_task_list[4]['task_parent']);
        $this->assertEquals(1,          $allowed_task_list[4]['task_access']);
        $this->assertEquals(1,          $allowed_task_list[4]['task_owner']);
        $this->assertEquals(7,          $allowed_task_list[5]['task_id']);
        $this->assertEquals('Task 7',   $allowed_task_list[5]['task_name']);
        $this->assertEquals(6,          $allowed_task_list[5]['task_parent']);
        $this->assertEquals(1,          $allowed_task_list[5]['task_access']);
        $this->assertEquals(1,          $allowed_task_list[5]['task_owner']);
        $this->assertEquals(6,          $allowed_task_list[6]['task_id']);
        $this->assertEquals('Task 6',   $allowed_task_list[6]['task_name']);
        $this->assertEquals(7,          $allowed_task_list[6]['task_parent']);
        $this->assertEquals(1,          $allowed_task_list[6]['task_access']);
        $this->assertEquals(1,          $allowed_task_list[6]['task_owner']);
        $this->assertEquals(9,          $allowed_task_list[7]['task_id']);
        $this->assertEquals('Task 9',   $allowed_task_list[7]['task_name']);
        $this->assertEquals(8,          $allowed_task_list[7]['task_parent']);
        $this->assertEquals(1,          $allowed_task_list[7]['task_access']);
        $this->assertEquals(1,          $allowed_task_list[7]['task_owner']);
        $this->assertEquals(10,         $allowed_task_list[8]['task_id']);
        $this->assertEquals('Task 10',  $allowed_task_list[8]['task_name']);
        $this->assertEquals(9,          $allowed_task_list[8]['task_parent']);
        $this->assertEquals(1,          $allowed_task_list[8]['task_access']);
        $this->assertEquals(1,          $allowed_task_list[8]['task_owner']);
        $this->assertEquals(11,         $allowed_task_list[9]['task_id']);
        $this->assertEquals('Task 11',  $allowed_task_list[9]['task_name']);
        $this->assertEquals(10,         $allowed_task_list[9]['task_parent']);
        $this->assertEquals(1,          $allowed_task_list[9]['task_access']);
        $this->assertEquals(1,          $allowed_task_list[9]['task_owner']);
        $this->assertEquals(8,          $allowed_task_list[10]['task_id']);
        $this->assertEquals('Task 8',   $allowed_task_list[10]['task_name']);
        $this->assertEquals(10,         $allowed_task_list[10]['task_parent']);
        $this->assertEquals(1,          $allowed_task_list[10]['task_access']);
        $this->assertEquals(1,          $allowed_task_list[10]['task_owner']);
        $this->assertEquals(12,         $allowed_task_list[11]['task_id']);
        $this->assertEquals('Task 12',  $allowed_task_list[11]['task_name']);
        $this->assertEquals(11,         $allowed_task_list[11]['task_parent']);
        $this->assertEquals(1,          $allowed_task_list[11]['task_access']);
        $this->assertEquals(1,          $allowed_task_list[11]['task_owner']);
        $this->assertEquals(13,         $allowed_task_list[12]['task_id']);
        $this->assertEquals('Task 13',  $allowed_task_list[12]['task_name']);
        $this->assertEquals(12,         $allowed_task_list[12]['task_parent']);
        $this->assertEquals(1,          $allowed_task_list[12]['task_access']);
        $this->assertEquals(1,          $allowed_task_list[12]['task_owner']);
        $this->assertEquals(14,         $allowed_task_list[13]['task_id']);
        $this->assertEquals('Task 14',  $allowed_task_list[13]['task_name']);
        $this->assertEquals(14,         $allowed_task_list[13]['task_parent']);
        $this->assertEquals(1,          $allowed_task_list[13]['task_access']);
        $this->assertEquals(1,          $allowed_task_list[13]['task_owner']);
        $this->assertEquals(15,         $allowed_task_list[14]['task_id']);
        $this->assertEquals('Task 15',  $allowed_task_list[14]['task_name']);
        $this->assertEquals(15,         $allowed_task_list[14]['task_parent']);
        $this->assertEquals(1,          $allowed_task_list[14]['task_access']);
        $this->assertEquals(1,          $allowed_task_list[14]['task_owner']);
        $this->assertEquals(16,         $allowed_task_list[15]['task_id']);
        $this->assertEquals('Task 16',  $allowed_task_list[15]['task_name']);
        $this->assertEquals(15,         $allowed_task_list[15]['task_parent']);
        $this->assertEquals(1,          $allowed_task_list[15]['task_access']);
        $this->assertEquals(1,          $allowed_task_list[15]['task_owner']);
        $this->assertEquals(17,         $allowed_task_list[16]['task_id']);
        $this->assertEquals('Task 17',  $allowed_task_list[16]['task_name']);
        $this->assertEquals(16,         $allowed_task_list[16]['task_parent']);
        $this->assertEquals(1,          $allowed_task_list[16]['task_access']);
        $this->assertEquals(1,          $allowed_task_list[16]['task_owner']);
        $this->assertEquals(18,         $allowed_task_list[17]['task_id']);
        $this->assertEquals('Task 18',  $allowed_task_list[17]['task_name']);
        $this->assertEquals(18,         $allowed_task_list[17]['task_parent']);
        $this->assertEquals(1,          $allowed_task_list[17]['task_access']);
        $this->assertEquals(1,          $allowed_task_list[17]['task_owner']);
        $this->assertEquals(19,         $allowed_task_list[18]['task_id']);
        $this->assertEquals('Task 19',  $allowed_task_list[18]['task_name']);
        $this->assertEquals(18,         $allowed_task_list[18]['task_parent']);
        $this->assertEquals(1,          $allowed_task_list[18]['task_access']);
        $this->assertEquals(1,          $allowed_task_list[18]['task_owner']);
        $this->assertEquals(20,         $allowed_task_list[19]['task_id']);
        $this->assertEquals('Task 20',  $allowed_task_list[19]['task_name']);
        $this->assertEquals(18,         $allowed_task_list[19]['task_parent']);
        $this->assertEquals(1,          $allowed_task_list[19]['task_access']);
        $this->assertEquals(1,          $allowed_task_list[19]['task_owner']);
        $this->assertEquals(21,         $allowed_task_list[20]['task_id']);
        $this->assertEquals('Task 21',  $allowed_task_list[20]['task_name']);
        $this->assertEquals(21,         $allowed_task_list[20]['task_parent']);
        $this->assertEquals(1,          $allowed_task_list[20]['task_access']);
        $this->assertEquals(1,          $allowed_task_list[20]['task_owner']);
        $this->assertEquals(22,         $allowed_task_list[21]['task_id']);
        $this->assertEquals('Task 22',  $allowed_task_list[21]['task_name']);
        $this->assertEquals(21,         $allowed_task_list[21]['task_parent']);
        $this->assertEquals(1,          $allowed_task_list[21]['task_access']);
        $this->assertEquals(1,          $allowed_task_list[21]['task_owner']);
        $this->assertEquals(23,         $allowed_task_list[22]['task_id']);
        $this->assertEquals('Task 23',  $allowed_task_list[22]['task_name']);
        $this->assertEquals(21,         $allowed_task_list[22]['task_parent']);
        $this->assertEquals(1,          $allowed_task_list[22]['task_access']);
        $this->assertEquals(1,          $allowed_task_list[22]['task_owner']);
        $this->assertEquals(24,         $allowed_task_list[23]['task_id']);
        $this->assertEquals('Task 24',  $allowed_task_list[23]['task_name']);
        $this->assertEquals(24,         $allowed_task_list[23]['task_parent']);
        $this->assertEquals(1,          $allowed_task_list[23]['task_access']);
        $this->assertEquals(1,          $allowed_task_list[23]['task_owner']);
        $this->assertEquals(25,         $allowed_task_list[24]['task_id']);
        $this->assertEquals('Task 25',  $allowed_task_list[24]['task_name']);
        $this->assertEquals(24,         $allowed_task_list[24]['task_parent']);
        $this->assertEquals(1,          $allowed_task_list[24]['task_access']);
        $this->assertEquals(1,          $allowed_task_list[24]['task_owner']);
        $this->assertEquals(26,         $allowed_task_list[25]['task_id']);
        $this->assertEquals('Task 26',  $allowed_task_list[25]['task_name']);
        $this->assertEquals(24,         $allowed_task_list[25]['task_parent']);
        $this->assertEquals(1,          $allowed_task_list[25]['task_access']);
        $this->assertEquals(1,          $allowed_task_list[25]['task_owner']);
        $this->assertEquals(27,         $allowed_task_list[26]['task_id']);
        $this->assertEquals('Task 27',  $allowed_task_list[26]['task_name']);
        $this->assertEquals(27,         $allowed_task_list[26]['task_parent']);
        $this->assertEquals(1,          $allowed_task_list[26]['task_access']);
        $this->assertEquals(1,          $allowed_task_list[26]['task_owner']);
        $this->assertEquals(28,         $allowed_task_list[27]['task_id']);
        $this->assertEquals('Task 28',  $allowed_task_list[27]['task_name']);
        $this->assertEquals(28,         $allowed_task_list[27]['task_parent']);
        $this->assertEquals(1,          $allowed_task_list[27]['task_access']);
        $this->assertEquals(1,          $allowed_task_list[27]['task_owner']);
        $this->assertEquals(29,         $allowed_task_list[28]['task_id']);
        $this->assertEquals('Task 29',  $allowed_task_list[28]['task_name']);
        $this->assertEquals(29,         $allowed_task_list[28]['task_parent']);
        $this->assertEquals(1,          $allowed_task_list[28]['task_access']);
        $this->assertEquals(1,          $allowed_task_list[28]['task_owner']);
        $this->assertEquals(30,         $allowed_task_list[29]['task_id']);
        $this->assertEquals('Task 30',  $allowed_task_list[29]['task_name']);
        $this->assertEquals(30,         $allowed_task_list[29]['task_parent']);
        $this->assertEquals(1,          $allowed_task_list[29]['task_access']);
        $this->assertEquals(1,          $allowed_task_list[29]['task_owner']);

        foreach ($allowed_task_list as $task) {
            $this->assertEquals(8, count($task));
        }
    }

    /**
     * Test getting allowed tasks with a project
     */
    public function testGetAllowedTaskListAllowedProject()
    {
        global $AppUI;

        $allowed_task_list = $this->obj->getAllowedTaskList($AppUI, 1);

        $this->assertEquals(30,         count($allowed_task_list));
        $this->assertEquals(1,          $allowed_task_list[0]['task_id']);
        $this->assertEquals('Task 1',   $allowed_task_list[0]['task_name']);
        $this->assertEquals(0,          $allowed_task_list[0]['task_parent']);
        $this->assertEquals(0,          $allowed_task_list[0]['task_access']);
        $this->assertEquals(1,          $allowed_task_list[0]['task_owner']);
        $this->assertEquals(3,          $allowed_task_list[1]['task_id']);
        $this->assertEquals('Task 3',   $allowed_task_list[1]['task_name']);
        $this->assertEquals(0,          $allowed_task_list[1]['task_parent']);
        $this->assertEquals(2,          $allowed_task_list[1]['task_access']);
        $this->assertEquals(1,          $allowed_task_list[1]['task_owner']);
        $this->assertEquals(4,          $allowed_task_list[2]['task_id']);
        $this->assertEquals('Task 4',   $allowed_task_list[2]['task_name']);
        $this->assertEquals(0,          $allowed_task_list[2]['task_parent']);
        $this->assertEquals(3,          $allowed_task_list[2]['task_access']);
        $this->assertEquals(2,          $allowed_task_list[2]['task_owner']);
        $this->assertEquals(5,          $allowed_task_list[3]['task_id']);
        $this->assertEquals('Task 5',   $allowed_task_list[3]['task_name']);
        $this->assertEquals(0,          $allowed_task_list[3]['task_parent']);
        $this->assertEquals(1,          $allowed_task_list[3]['task_access']);
        $this->assertEquals(1,          $allowed_task_list[3]['task_owner']);
        $this->assertEquals(2,          $allowed_task_list[4]['task_id']);
        $this->assertEquals('Task 2',   $allowed_task_list[4]['task_name']);
        $this->assertEquals(1,          $allowed_task_list[4]['task_parent']);
        $this->assertEquals(1,          $allowed_task_list[4]['task_access']);
        $this->assertEquals(1,          $allowed_task_list[4]['task_owner']);
        $this->assertEquals(7,          $allowed_task_list[5]['task_id']);
        $this->assertEquals('Task 7',   $allowed_task_list[5]['task_name']);
        $this->assertEquals(6,          $allowed_task_list[5]['task_parent']);
        $this->assertEquals(1,          $allowed_task_list[5]['task_access']);
        $this->assertEquals(1,          $allowed_task_list[5]['task_owner']);
        $this->assertEquals(6,          $allowed_task_list[6]['task_id']);
        $this->assertEquals('Task 6',   $allowed_task_list[6]['task_name']);
        $this->assertEquals(7,          $allowed_task_list[6]['task_parent']);
        $this->assertEquals(1,          $allowed_task_list[6]['task_access']);
        $this->assertEquals(1,          $allowed_task_list[6]['task_owner']);
        $this->assertEquals(9,          $allowed_task_list[7]['task_id']);
        $this->assertEquals('Task 9',   $allowed_task_list[7]['task_name']);
        $this->assertEquals(8,          $allowed_task_list[7]['task_parent']);
        $this->assertEquals(1,          $allowed_task_list[7]['task_access']);
        $this->assertEquals(1,          $allowed_task_list[7]['task_owner']);
        $this->assertEquals(10,         $allowed_task_list[8]['task_id']);
        $this->assertEquals('Task 10',  $allowed_task_list[8]['task_name']);
        $this->assertEquals(9,          $allowed_task_list[8]['task_parent']);
        $this->assertEquals(1,          $allowed_task_list[8]['task_access']);
        $this->assertEquals(1,          $allowed_task_list[8]['task_owner']);
        $this->assertEquals(11,         $allowed_task_list[9]['task_id']);
        $this->assertEquals('Task 11',  $allowed_task_list[9]['task_name']);
        $this->assertEquals(10,         $allowed_task_list[9]['task_parent']);
        $this->assertEquals(1,          $allowed_task_list[9]['task_access']);
        $this->assertEquals(1,          $allowed_task_list[9]['task_owner']);
        $this->assertEquals(8,          $allowed_task_list[10]['task_id']);
        $this->assertEquals('Task 8',   $allowed_task_list[10]['task_name']);
        $this->assertEquals(10,         $allowed_task_list[10]['task_parent']);
        $this->assertEquals(1,          $allowed_task_list[10]['task_access']);
        $this->assertEquals(1,          $allowed_task_list[10]['task_owner']);
        $this->assertEquals(12,         $allowed_task_list[11]['task_id']);
        $this->assertEquals('Task 12',  $allowed_task_list[11]['task_name']);
        $this->assertEquals(11,         $allowed_task_list[11]['task_parent']);
        $this->assertEquals(1,          $allowed_task_list[11]['task_access']);
        $this->assertEquals(1,          $allowed_task_list[11]['task_owner']);
        $this->assertEquals(13,         $allowed_task_list[12]['task_id']);
        $this->assertEquals('Task 13',  $allowed_task_list[12]['task_name']);
        $this->assertEquals(12,         $allowed_task_list[12]['task_parent']);
        $this->assertEquals(1,          $allowed_task_list[12]['task_access']);
        $this->assertEquals(1,          $allowed_task_list[12]['task_owner']);
        $this->assertEquals(14,         $allowed_task_list[13]['task_id']);
        $this->assertEquals('Task 14',  $allowed_task_list[13]['task_name']);
        $this->assertEquals(14,         $allowed_task_list[13]['task_parent']);
        $this->assertEquals(1,          $allowed_task_list[13]['task_access']);
        $this->assertEquals(1,          $allowed_task_list[13]['task_owner']);
        $this->assertEquals(15,         $allowed_task_list[14]['task_id']);
        $this->assertEquals('Task 15',  $allowed_task_list[14]['task_name']);
        $this->assertEquals(15,         $allowed_task_list[14]['task_parent']);
        $this->assertEquals(1,          $allowed_task_list[14]['task_access']);
        $this->assertEquals(1,          $allowed_task_list[14]['task_owner']);
        $this->assertEquals(16,         $allowed_task_list[15]['task_id']);
        $this->assertEquals('Task 16',  $allowed_task_list[15]['task_name']);
        $this->assertEquals(15,         $allowed_task_list[15]['task_parent']);
        $this->assertEquals(1,          $allowed_task_list[15]['task_access']);
        $this->assertEquals(1,          $allowed_task_list[15]['task_owner']);
        $this->assertEquals(17,         $allowed_task_list[16]['task_id']);
        $this->assertEquals('Task 17',  $allowed_task_list[16]['task_name']);
        $this->assertEquals(16,         $allowed_task_list[16]['task_parent']);
        $this->assertEquals(1,          $allowed_task_list[16]['task_access']);
        $this->assertEquals(1,          $allowed_task_list[16]['task_owner']);
        $this->assertEquals(18,         $allowed_task_list[17]['task_id']);
        $this->assertEquals('Task 18',  $allowed_task_list[17]['task_name']);
        $this->assertEquals(18,         $allowed_task_list[17]['task_parent']);
        $this->assertEquals(1,          $allowed_task_list[17]['task_access']);
        $this->assertEquals(1,          $allowed_task_list[17]['task_owner']);
        $this->assertEquals(19,         $allowed_task_list[18]['task_id']);
        $this->assertEquals('Task 19',  $allowed_task_list[18]['task_name']);
        $this->assertEquals(18,         $allowed_task_list[18]['task_parent']);
        $this->assertEquals(1,          $allowed_task_list[18]['task_access']);
        $this->assertEquals(1,          $allowed_task_list[18]['task_owner']);
        $this->assertEquals(20,         $allowed_task_list[19]['task_id']);
        $this->assertEquals('Task 20',  $allowed_task_list[19]['task_name']);
        $this->assertEquals(18,         $allowed_task_list[19]['task_parent']);
        $this->assertEquals(1,          $allowed_task_list[19]['task_access']);
        $this->assertEquals(1,          $allowed_task_list[19]['task_owner']);
        $this->assertEquals(21,         $allowed_task_list[20]['task_id']);
        $this->assertEquals('Task 21',  $allowed_task_list[20]['task_name']);
        $this->assertEquals(21,         $allowed_task_list[20]['task_parent']);
        $this->assertEquals(1,          $allowed_task_list[20]['task_access']);
        $this->assertEquals(1,          $allowed_task_list[20]['task_owner']);
        $this->assertEquals(22,         $allowed_task_list[21]['task_id']);
        $this->assertEquals('Task 22',  $allowed_task_list[21]['task_name']);
        $this->assertEquals(21,         $allowed_task_list[21]['task_parent']);
        $this->assertEquals(1,          $allowed_task_list[21]['task_access']);
        $this->assertEquals(1,          $allowed_task_list[21]['task_owner']);
        $this->assertEquals(23,         $allowed_task_list[22]['task_id']);
        $this->assertEquals('Task 23',  $allowed_task_list[22]['task_name']);
        $this->assertEquals(21,         $allowed_task_list[22]['task_parent']);
        $this->assertEquals(1,          $allowed_task_list[22]['task_access']);
        $this->assertEquals(1,          $allowed_task_list[22]['task_owner']);
        $this->assertEquals(24,         $allowed_task_list[23]['task_id']);
        $this->assertEquals('Task 24',  $allowed_task_list[23]['task_name']);
        $this->assertEquals(24,         $allowed_task_list[23]['task_parent']);
        $this->assertEquals(1,          $allowed_task_list[23]['task_access']);
        $this->assertEquals(1,          $allowed_task_list[23]['task_owner']);
        $this->assertEquals(25,         $allowed_task_list[24]['task_id']);
        $this->assertEquals('Task 25',  $allowed_task_list[24]['task_name']);
        $this->assertEquals(24,         $allowed_task_list[24]['task_parent']);
        $this->assertEquals(1,          $allowed_task_list[24]['task_access']);
        $this->assertEquals(1,          $allowed_task_list[24]['task_owner']);
        $this->assertEquals(26,         $allowed_task_list[25]['task_id']);
        $this->assertEquals('Task 26',  $allowed_task_list[25]['task_name']);
        $this->assertEquals(24,         $allowed_task_list[25]['task_parent']);
        $this->assertEquals(1,          $allowed_task_list[25]['task_access']);
        $this->assertEquals(1,          $allowed_task_list[25]['task_owner']);
        $this->assertEquals(27,         $allowed_task_list[26]['task_id']);
        $this->assertEquals('Task 27',  $allowed_task_list[26]['task_name']);
        $this->assertEquals(27,         $allowed_task_list[26]['task_parent']);
        $this->assertEquals(1,          $allowed_task_list[26]['task_access']);
        $this->assertEquals(1,          $allowed_task_list[26]['task_owner']);
        $this->assertEquals(28,         $allowed_task_list[27]['task_id']);
        $this->assertEquals('Task 28',  $allowed_task_list[27]['task_name']);
        $this->assertEquals(28,         $allowed_task_list[27]['task_parent']);
        $this->assertEquals(1,          $allowed_task_list[27]['task_access']);
        $this->assertEquals(1,          $allowed_task_list[27]['task_owner']);
        $this->assertEquals(29,         $allowed_task_list[28]['task_id']);
        $this->assertEquals('Task 29',  $allowed_task_list[28]['task_name']);
        $this->assertEquals(29,         $allowed_task_list[28]['task_parent']);
        $this->assertEquals(1,          $allowed_task_list[28]['task_access']);
        $this->assertEquals(1,          $allowed_task_list[28]['task_owner']);
        $this->assertEquals(30,         $allowed_task_list[29]['task_id']);
        $this->assertEquals('Task 30',  $allowed_task_list[29]['task_name']);
        $this->assertEquals(30,         $allowed_task_list[29]['task_parent']);
        $this->assertEquals(1,          $allowed_task_list[29]['task_access']);
        $this->assertEquals(1,          $allowed_task_list[29]['task_owner']);

        foreach ($allowed_task_list as $task) {
            $this->assertEquals(8, count($task));
        }
    }

    /**
     * Tests getting allowed task list with no permission and no project specified
     */
    public function testGetAllowedTaskListNotAllowedNoProject()
    {
        global $AppUI;

        // Login as another user for permission purposes
        $old_AppUI = $AppUI;
        $AppUI  = new CAppUI;
        $_POST['login'] = 'login';
        $_REQUEST['login'] = 'sql';

        $allowed_task_list = $this->obj->getAllowedTaskList($AppUI);

        $this->assertEquals(1,          count($allowed_task_list));
        $this->assertEquals(8,          count($allowed_task_list[0]));
        $this->assertEquals(1,          $allowed_task_list[0]['task_id']);
        $this->assertEquals('Task 1',   $allowed_task_list[0]['task_name']);
        $this->assertEquals(0,          $allowed_task_list[0]['task_parent']);
        $this->assertEquals(0,          $allowed_task_list[0]['task_access']);
        $this->assertEquals(1,          $allowed_task_list[0]['task_owner']);
        // Restore AppUI for following tests since its global, yuck!
        $AppUI = $old_AppUI;
    }

    /**
     * Tests getting allowed task list with no permission and a project specified
     */
    public function testGetAllowedTaskListNotAllowedProject()
    {
        global $AppUI;

        // Login as another user for permission purposes
        $old_AppUI = $AppUI;
        $AppUI  = new CAppUI;
        $_POST['login'] = 'login';
        $_REQUEST['login'] = 'sql';

        $allowed_task_list = $this->obj->getAllowedTaskList($AppUI, 2);
        $this->assertEquals(0,          count($allowed_task_list));

        // Restore AppUI for following tests since its global, yuck!
        $AppUI = $old_AppUI;
    }

    /**
     * Test getting task count for a project
     */
    public function testGetTaskCount()
    {
        $task_count = $this->obj->getTaskCount(1);

        $this->assertEquals(30, $task_count);

        $task_count = $this->obj->getTaskCount(2);

        $this->assertEquals(0, $task_count);
    }

    /**
     * Test pinning task for user
     */
    public function testPinUserTask()
    {
        $return_val = $this->obj->pinUserTask(1, 1);

        $this->assertTrue($return_val);

        $xml_file_dataset = $this->createXMLDataSet($this->getDataSetPath().'tasksTestPinUserTask.xml');
        $xml_db_dataset = $this->getConnection()->createDataSet();
        $this->assertTablesEqual($xml_file_dataset->getTable('user_task_pin'), $xml_db_dataset->getTable('user_task_pin'));
    }

    /**
     * Test unpinning task for user
     */
    public function testUnpinUserTask()
    {
        $return_val = $this->obj->unpinUserTask(1, 1);

        $this->assertTrue($return_val);

        $xml_file_dataset = $this->createXMLDataSet($this->getDataSetPath().'tasksTestUnpinUserTask.xml');
        $xml_db_dataset = $this->getConnection()->createDataSet();
        $this->assertTablesEqual($xml_file_dataset->getTable('user_task_pin'), $xml_db_dataset->getTable('user_task_pin'));
    }

    /**
     * Test updating hours worked for a task
     */
    public function testUpdateHoursWorked()
    {
        CTask::updateHoursWorked(1, 25);

        $xml_file_dataset = $this->createXMLDataSet($this->getDataSetPath().'tasksTestUpdateHoursWorked.xml');
        $xml_db_dataset = $this->getConnection()->createDataSet();
        $this->assertTablesEqual($xml_file_dataset->getTable('tasks'), $xml_db_dataset->getTable('tasks'));
    }

    /**
     * Test getting the project of currently loaded task
     */
    public function testGetProject()
    {
        $this->obj->load(1);

        $project = $this->obj->getProject();

        $this->assertEquals('Test Project', $project['project_name']);
        $this->assertEquals('TP',           $project['project_short_name']);
        $this->assertEquals('FFFFFF',       $project['project_color_identifier']);
    }
}
