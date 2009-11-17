<?php
/**
 * Necessary global variables
 */
global $db;
global $ADODB_FETCH_MODE;
global $w2p_performance_dbtime;
global $w2p_performance_old_dbqueries;
global $AppUI;

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

    /**
     * Tests the Attributes of a new Tasks object.
     */
    public function testNewTasksAttributes()
    {
        $task = new CTask();

        $this->assertType('CTask',                                  $task);
        $this->assertObjectHasAttribute('task_id',                  $task);
        $this->assertObjectHasAttribute('task_name',                $task);
        $this->assertObjectHasAttribute('task_parent',              $task);
        $this->assertObjectHasAttribute('task_milestone',           $task);
        $this->assertObjectHasAttribute('task_project',             $task);
        $this->assertObjectHasAttribute('task_owner',               $task);
        $this->assertObjectHasAttribute('task_start_date',          $task);
        $this->assertObjectHasAttribute('task_duration',            $task);
        $this->assertObjectHasAttribute('task_duration_type',       $task);
        $this->assertObjectHasAttribute('task_hours_worked',        $task);
        $this->assertObjectHasAttribute('task_end_date',            $task);
        $this->assertObjectHasAttribute('task_status',              $task);
        $this->assertObjectHasAttribute('task_priority',            $task);
        $this->assertObjectHasAttribute('task_percent_complete',    $task);
        $this->assertObjectHasAttribute('task_description',         $task);
        $this->assertObjectHasAttribute('task_target_budget',       $task);
        $this->assertObjectHasAttribute('task_related_url',         $task);
        $this->assertObjectHasAttribute('task_creator',             $task);
        $this->assertObjectHasAttribute('task_order',               $task);
        $this->assertObjectHasAttribute('task_client_publish',      $task);
        $this->assertObjectHasAttribute('task_dynamic',             $task);
        $this->assertObjectHasAttribute('task_access',              $task);
        $this->assertObjectHasAttribute('task_notify',              $task);
        $this->assertObjectHasAttribute('task_departments',         $task);
        $this->assertObjectHasAttribute('task_contacts',            $task);
        $this->assertObjectHasAttribute('task_custom',              $task);
        $this->assertObjectHasAttribute('task_type',                $task);
        $this->assertObjectHasAttribute('_tbl_prefix',              $task);
        $this->assertObjectHasAttribute('_tbl',                     $task);
        $this->assertObjectHasAttribute('_tbl_key',                 $task);
        $this->assertObjectHasAttribute('_error',                   $task);
        $this->assertObjectHasAttribute('_query',                   $task);
        $this->assertObjectHasAttribute('task_updator',             $task);
        $this->assertObjectHasAttribute('task_created',             $task);
        $this->assertObjectHasAttribute('task_updated',             $task);
        $this->assertObjectHasAttribute('task_dep_reset_dates',     $task);
    }

    /**
     * Tests the Attribute Values of a new Task object.
     */
    public function testNewTasktAttributeValues()
    {
        $task = new CTask();

        $this->assertType('CTask', $task);
        $this->assertNull($task->task_id);
        $this->assertNull($task->task_name);
        $this->assertNull($task->task_parent);
        $this->assertNull($task->task_milestone);
        $this->assertNull($task->task_project);
        $this->assertNull($task->task_owner);
        $this->assertNull($task->task_start_date);
        $this->assertNull($task->task_duration);
        $this->assertNull($task->task_duration_type);
        $this->assertNull($task->task_hours_worked);
        $this->assertNull($task->task_end_date);
        $this->assertNull($task->task_status);
        $this->assertNull($task->task_priority);
        $this->assertNull($task->task_percent_complete);
        $this->assertNull($task->task_description);
        $this->assertNull($task->task_target_budget);
        $this->assertNull($task->task_related_url);
        $this->assertNull($task->task_creator);
        $this->assertNull($task->task_order);
        $this->assertNull($task->task_client_publish);
        $this->assertNull($task->task_dynamic);
        $this->assertNull($task->task_access);
        $this->assertNull($task->task_notify);
        $this->assertNull($task->task_departments);
        $this->assertNull($task->task_contancts);
        $this->assertNull($task->task_custom);
        $this->assertNull($task->task_type);
        $this->assertEquals('',         $task->_tbl_prefix);
        $this->assertEquals('tasks',    $task->_tbl);
        $this->assertEquals('task_id',  $task->_tbl_key);
        $this->assertEquals('',         $task->_errors);
        $this->assertType('DBQuery',    $task->_query);
        $this->assertNull($task->task_updator);
        $this->assertNull($task->task_created);
        $this->assertNull($task->task_updated);
        $this->assertNull($task->task_dep_reset_dates);
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
        $task = new CTask();

        $post_data = array (
            'dosql'                 => 'do_task_aed',
            'task_id'               => 0,
            'task_project'          => 1,
            'task_contacts'         => null,
            'task_name'             => '',
            'task_status'           => 0,
            'task_priority'         => 0,
            'task_percent_complete' => 0,
            'task_owner'            => 1,
            'task_access'           => 0,
            'task_related_url'      => 'http://www.example.org',
            'task_type'             => 0,
            'dept_ids'              => array(1),
            'task_parent'           => null,
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
            'task_dynamic'          => 0,
            'hdependencies'         => null,
            'hperc_assign'          => '1=100;',
            'percentage_assignment' => 100,
            'email_comment'         => '',
            'task_notify'           => 1,
            'hassign'               => 1,
            'hresource_assign'      => '',
            'resource_assignment'   => 100
        );

        $task->bind($post_data);
        $errorArray = $task->check();

        $this->assertArrayHasKey('task_name', $errorArray);
    }

    /**
     * Tests the check function returns the proper error message when no priority is passed
     */
    public function testCheckTaskNoPriority()
    {
        $task = new CTask();

        $post_data = array (
            'dosql'                 => 'do_task_aed',
            'task_id'               => 0,
            'task_project'          => 1,
            'task_contacts'         => null,
            'task_name'             => 'Test Task',
            'task_status'           => 0,
            'task_priority'         => null,
            'task_percent_complete' => 0,
            'task_owner'            => 1,
            'task_access'           => 0,
            'task_related_url'      => 'http://www.example.org',
            'task_type'             => 0,
            'dept_ids'              => array(1),
            'task_parent'           => null,
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
            'task_dynamic'          => 0,
            'hdependencies'         => null,
            'hperc_assign'          => '1=100;',
            'percentage_assignment' => 100,
            'email_comment'         => '',
            'task_notify'           => 1,
            'hassign'               => 1,
            'hresource_assign'      => '',
            'resource_assignment'   => 100
        );

        $task->bind($post_data);
        $errorArray = $task->check();

        $this->assertArrayHasKey('task_priority', $errorArray);
    }

	/**
     * Tests the check function returns the proper error message when no start date is passed
     */
    public function testCheckTaskNoStartDate()
    {
        $task = new CTask();

        $post_data = array (
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
            'task_parent'           => null,
            'task_target_budget'    => '1.00',
            'task_description'      => 'this is a description for test task.',
            'task_start_date'       => '',
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
            'task_dynamic'          => 0,
            'hdependencies'         => null,
            'hperc_assign'          => '1=100;',
            'percentage_assignment' => 100,
            'email_comment'         => '',
            'task_notify'           => 1,
            'hassign'               => 1,
            'hresource_assign'      => '',
            'resource_assignment'   => 100
        );

        $task->bind($post_data);
        $errorArray = $task->check();

        $this->assertArrayHasKey('task_start_date', $errorArray);
    }

	/**
     * Tests the check function returns the proper error message when no end date is passed
     */
    public function testCheckTaskNoEndDate()
    {
        $task = new CTask();

        $post_data = array (
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
            'task_parent'           => null,
            'task_target_budget'    => '1.00',
            'task_description'      => 'this is a description for test task.',
            'task_start_date'       => '200908240800',
            'start_date'            => '24/Aug/2009',
            'start_hour'            => '08',
            'start_minute'          => '00',
            'start_hour_ampm'       => 'pm',
            'task_end_date'         => '',
            'end_date'              => '26/Aug/2009',
            'end_hour'              => 17,
            'end_minute'            => 00,
            'end_hour_ampm'         => 'pm',
            'task_duration'         => 3,
            'task_duration_type'    => 1,
            'task_dynamic'          => 0,
            'hdependencies'         => null,
            'hperc_assign'          => '1=100;',
            'percentage_assignment' => 100,
            'email_comment'         => '',
            'task_notify'           => 1,
            'hassign'               => 1,
            'hresource_assign'      => '',
            'resource_assignment'   => 100
        );

        $task->bind($post_data);
        $errorArray = $task->check();

        $this->assertArrayHasKey('task_end_date', $errorArray);
    }

    /**
     * Tests that the check function returns the proper error message
     * when attempting to change to dynamic and it has dependencies.
     */
    public function testCheckTaskDynamicWithDep()
    {
        $task = new CTask();

        $post_data = array (
            'dosql'                 => 'do_task_aed',
            'task_id'               => 3,
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
            'task_parent'           => null,
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

        $task->bind($post_data);
        $errorMsg = $task->check();

        $this->assertEquals('BadDep_DynNoDep', $errorMsg);
    }

    /**
     * Tests that the check function returns the proper error message
     * when attempting to update a task with a circular dependency.
     */
    public function testCheckTaskCircularDep()
    {
        $task = new CTask();

        $post_data = array (
            'dosql'                 => 'do_task_aed',
            'task_id'               => 3,
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
            'task_parent'           => 4,
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
            'task_dynamic'          => 0,
            'hdependencies'         => null,
            'hperc_assign'          => '1=100;',
            'percentage_assignment' => 100,
            'email_comment'         => '',
            'task_notify'           => 1,
            'hassign'               => 1,
            'hresource_assign'      => '',
            'resource_assignment'   => 100
        );

        $task->bind($post_data);
        $errorMsg = $task->check();

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
        $task = new CTask();

        $post_data = array (
            'dosql'                 => 'do_task_aed',
            'task_id'               => 5,
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
            'task_parent'           => 6,
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
            'task_dynamic'          => 0,
            'hdependencies'         => null,
            'hperc_assign'          => '1=100;',
            'percentage_assignment' => 100,
            'email_comment'         => '',
            'task_notify'           => 1,
            'hassign'               => 1,
            'hresource_assign'      => '',
            'resource_assignment'   => 100
        );

        $task->bind($post_data);
        $errorMsg = $task->check();

        $this->assertEquals('BadDep_CannotDependOnParent', $errorMsg);
    }

    /**
     * Tests that the check function returns the proper error message
     * when attempting to update a task when this tasks parent is a
     * child of it
     */
    public function testCheckTaskParentCannotBeChild()
    {
        $task = new CTask();

        $post_data = array (
            'dosql'                 => 'do_task_aed',
            'task_id'               => 6,
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
            'task_parent'           => 7,
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
            'task_dynamic'          => 0,
            'hdependencies'         => null,
            'hperc_assign'          => '1=100;',
            'percentage_assignment' => 100,
            'email_comment'         => '',
            'task_notify'           => 1,
            'hassign'               => 1,
            'hresource_assign'      => '',
            'resource_assignment'   => 100
        );

        $task->bind($post_data);
        $errorMsg = $task->check();

        $this->assertEquals('BadParent_CircularParent', $errorMsg);
    }

    /**
     * Tests that the check function returns the proper error message
     * when attempting to update a task when this tasks parent is a
     * child of it
     */
    public function testCheckTaskGrandParentCannotBeChild()
    {
        $task = new CTask();

        $post_data = array (
            'dosql'                 => 'do_task_aed',
            'task_id'               => 10,
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
            'task_parent'           => 9,
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
            'task_dynamic'          => 0,
            'hdependencies'         => null,
            'hperc_assign'          => '1=100;',
            'percentage_assignment' => 100,
            'email_comment'         => '',
            'task_notify'           => 1,
            'hassign'               => 1,
            'hresource_assign'      => '',
            'resource_assignment'   => 100
        );

        $task->bind($post_data);
        $errorMsg = $task->check();

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
        $task = new CTask();

        $post_data = array (
            'dosql'                 => 'do_task_aed',
            'task_id'               => 11,
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
            'task_parent'           => 10,
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
            'task_dynamic'          => 0,
            'hdependencies'         => null,
            'hperc_assign'          => '1=100;',
            'percentage_assignment' => 100,
            'email_comment'         => '',
            'task_notify'           => 1,
            'hassign'               => 1,
            'hresource_assign'      => '',
            'resource_assignment'   => 100
        );

        $task->bind($post_data);
        $errorMsg = $task->check();

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
        $task = new CTask();

        $post_data = array (
            'dosql'                 => 'do_task_aed',
            'task_id'               => 12,
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
            'task_parent'           => 11,
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
            'task_dynamic'          => 0,
            'hdependencies'         => null,
            'hperc_assign'          => '1=100;',
            'percentage_assignment' => 100,
            'email_comment'         => '',
            'task_notify'           => 1,
            'hassign'               => 1,
            'hresource_assign'      => '',
            'resource_assignment'   => 100
        );

        $task->bind($post_data);
        $errorMsg = $task->check();

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
        $task = new CTask();

        $post_data = array (
            'dosql'                 => 'do_task_aed',
            'task_id'               => 16,
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
            'task_parent'           => 15,
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

        $task->bind($post_data);
        $errorMsg = $task->check();

        $this->assertEquals('BadParent_ChildDepOnParent', $errorMsg);
    }

    /**
     * And finally lets test that check returns nothing when there are
     * no problems with the task.
     */
    public function testCheck()
    {
        $task = new CTask();

        $post_data = array (
            'dosql'                 => 'do_task_aed',
            'task_id'               => 18,
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
            'task_parent'           => 18,
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

        $task->bind($post_data);
        $errorMsg = $task->check();

        $this->assertEquals(array(), $errorMsg);
    }

    /**
     * Testing loading a task that is not dynamic.
     */

    public function testLoad()
    {
        $task = new CTask();

        $task->load(1);

        $this->assertEquals(1,                      $task->task_id);
        $this->assertEquals('Task 1',               $task->task_name);
        $this->assertEquals(0,                      $task->task_parent);
        $this->assertEquals('',                     $task->milestone);
        $this->assertEquals(1,                      $task->task_project);
        $this->assertEquals(1,                      $task->task_owner);
        $this->assertEquals('2009-07-05 00:00:00',  $task->task_start_date);
        $this->assertEquals(2,                      $task->task_duration);
        $this->assertEquals(24,                     $task->task_duration_type);
        $this->assertEquals(0,                      $task->task_hours_worked);
        $this->assertEquals('2009-07-15 00:00:00',  $task->task_end_date);
        $this->assertEquals(0,                      $task->task_status);
        $this->assertEquals(0,                      $task->task_priority);
        $this->assertEquals(0,                      $task->task_percent_complete);
        $this->assertEquals('This is task 1',       $task->task_description);
        $this->assertEquals(0.00,                   $task->task_target_budget);
        $this->assertEquals('',                     $task->task_related_url);
        $this->assertEquals(1,                      $task->task_creator);
        $this->assertEquals(1,                      $task->task_order);
        $this->assertEquals(1,                      $task->task_client_publish);
        $this->assertEquals(0,                      $task->task_dynamic);
        $this->assertEquals(1,                      $task->task_access);
        $this->assertEquals(1,                      $task->task_notify);
        $this->assertEquals('',                     $task->task_departments);
        $this->assertEquals('',                     $task->task_contacts);
        $this->assertEquals('',                     $task->task_custom);
        $this->assertEquals(1,                      $task->task_type);
        $this->assertEquals(1,                      $task->task_updator);
        $this->assertEquals('2009-07-05 15:43:00',  $task->task_created);
        $this->assertEquals('2009-07-05 15:43:00',  $task->task_updated);
        $this->assertEquals(0,                      $task->task_dep_reset_dates);
    }

    /**
     * Tests loading a task that is dynamic skipping update.
     */
    public function testLoadDynamicSkipUpdate()
    {
        $task = new CTask();

        $task->load(18, false, true);

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
        $this->assertEquals(0,                      $task->task_dep_reset_dates);
    }

    /*
     * Tests loading a task that is dynamic not skipping update.
     */
    public function testLoadDynamic()
    {
        $task = new CTask();

        $new_task = $task->load(18, false, false);

        $this->assertEquals(18,                     $task->task_id);
        $this->assertEquals('Task 18',              $task->task_name);
        $this->assertEquals(18,                     $task->task_parent);
        $this->assertEquals(0,                      $task->task_milestone);
        $this->assertEquals(1,                      $task->task_project);
        $this->assertEquals(1,                      $task->task_owner);
        $this->assertEquals('2009-07-06 00:00:00',  $task->task_start_date);
        $this->assertEquals(4,                      $task->task_duration);
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
        $this->assertEquals(0,                      $task->task_dep_reset_dates);
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
        $task = new CTask();

        $task->loadFull(18);

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
        $this->assertEquals(0,                      $task->task_dep_reset_dates);
        $this->assertEquals('Test Project',         $task->project_name);
        $this->assertEquals('FFFFFF',               $task->project_color_identifier);
        $this->assertEquals('Admin Person',         $task->username);
    }

    /**
     * Tests that the peek function returns a task object that has
     * not had it's data updated if it is dynamic.
     */
    public function testPeek()
    {

        $test_task = new CTask();

        $task = $test_task->peek(18);

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
        $this->assertEquals(0,                      $task->task_dep_reset_dates);
    }

    /**
     * Tests updating dynamic task when from children = true, in days
     */
    public function testUpdateDynamicsFromChildrenInDays()
    {

        $task = new CTask();
        $task->load(21);
        $task->updateDynamics(true);

        $this->assertEquals(21,                     $task->task_id);
        $this->assertEquals('Task 21',              $task->task_name);
        $this->assertEquals(21,                     $task->task_parent);
        $this->assertEquals(0,                      $task->task_milestone);
        $this->assertEquals(1,                      $task->task_project);
        $this->assertEquals(1,                      $task->task_owner);
        $this->assertEquals('2009-09-09 00:00:00',  $task->task_start_date);
        $this->assertEquals(8,                      $task->task_duration);
        $this->assertEquals(24,                     $task->task_duration_type);
        $this->assertEquals(37,                     $task->task_hours_worked);
        $this->assertEquals('2009-11-02 00:00:00',  $task->task_end_date);
        $this->assertEquals(0,                      $task->task_status);
        $this->assertEquals(0,                      $task->task_priority);
        $this->assertEquals(41,                     $task->task_percent_complete);
        $this->assertEquals('This is task 21',      $task->task_description);
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
        $this->assertEquals(0,                      $task->task_dep_reset_dates);
    }

    /**
     * Tests updating dynamic task when from children = false, in days
     */
    public function testUpdateDynamicsNotFromChildrenInDays()
    {

        $task = new CTask();
        $task->load(22);
        $task->updateDynamics(false);
        $task->load(21);

        $now_secs = time();
        $min_time = $now_secs - 10;

        $this->assertEquals(21,                     $task->task_id);
        $this->assertEquals('Task 21',              $task->task_name);
        $this->assertEquals(21,                     $task->task_parent);
        $this->assertEquals(0,                      $task->task_milestone);
        $this->assertEquals(1,                      $task->task_project);
        $this->assertEquals(1,                      $task->task_owner);
        $this->assertEquals('2009-09-09 00:00:00',  $task->task_start_date);
        $this->assertEquals(8,                      $task->task_duration);
        $this->assertEquals(24,                     $task->task_duration_type);
        $this->assertEquals(37,                     $task->task_hours_worked);
        $this->assertEquals('2009-11-02 00:00:00',  $task->task_end_date);
        $this->assertEquals(0,                      $task->task_status);
        $this->assertEquals(0,                      $task->task_priority);
        $this->assertEquals(41,                     $task->task_percent_complete);
        $this->assertEquals('This is task 21',      $task->task_description);
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
        $this->assertGreaterThanOrEqual($min_time,  strtotime($task->task_updated));
        $this->assertLessThanOrEqual($now_secs,     strtotime($task->task_updated));
        $this->assertEquals(0,                      $task->task_dep_reset_dates);

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

        $task = new CTask();
        $task->load(24);
        $task->updateDynamics(true);

        $this->assertEquals(24,                     $task->task_id);
        $this->assertEquals('Task 24',              $task->task_name);
        $this->assertEquals(24,                     $task->task_parent);
        $this->assertEquals(0,                      $task->task_milestone);
        $this->assertEquals(1,                      $task->task_project);
        $this->assertEquals(1,                      $task->task_owner);
        $this->assertEquals('2009-09-09 00:00:00',  $task->task_start_date);
        $this->assertEquals(64,                     $task->task_duration);
        $this->assertEquals(1,                      $task->task_duration_type);
        $this->assertEquals(37,                     $task->task_hours_worked);
        $this->assertEquals('2009-11-02 00:00:00',  $task->task_end_date);
        $this->assertEquals(0,                      $task->task_status);
        $this->assertEquals(0,                      $task->task_priority);
        $this->assertEquals(41,                     $task->task_percent_complete);
        $this->assertEquals('This is task 24',      $task->task_description);
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
        $this->assertEquals(0,                      $task->task_dep_reset_dates);
    }

    /**
     * Tests updating dynamic task when from children = false, in hours
     */
    public function testUpdateDynamicsNotFromChildrenInHours()
    {

        $task = new CTask();
        $task->load(25);
        $task->updateDynamics(false);
        $task->load(24);

        $now_secs = time();
        $min_time = $now_secs - 10;

        $this->assertEquals(24,                     $task->task_id);
        $this->assertEquals('Task 24',              $task->task_name);
        $this->assertEquals(24,                     $task->task_parent);
        $this->assertEquals(0,                      $task->task_milestone);
        $this->assertEquals(1,                      $task->task_project);
        $this->assertEquals(1,                      $task->task_owner);
        $this->assertEquals('2009-09-09 00:00:00',  $task->task_start_date);
        $this->assertEquals(64,                     $task->task_duration);
        $this->assertEquals(1,                      $task->task_duration_type);
        $this->assertEquals(37,                     $task->task_hours_worked);
        $this->assertEquals('2009-11-02 00:00:00',  $task->task_end_date);
        $this->assertEquals(0,                      $task->task_status);
        $this->assertEquals(0,                      $task->task_priority);
        $this->assertEquals(41,                     $task->task_percent_complete);
        $this->assertEquals('This is task 24',      $task->task_description);
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
        $this->assertEquals(0,                      $task->task_dep_reset_dates);

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
        $task = new CTask();
        $task->load(26);
        $new_task = $task->copy();
        
        $now_secs = time();
        $min_time = $now_secs - 10;
        
        $this->assertEquals(27,                     $new_task->task_id);
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
        $this->assertEquals(0,                      $new_task->task_dep_reset_dates);
        
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
        $q->addWhere('task_id = 27');
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
        $task = new CTask();
        $task->load(26);
        $new_task = $task->copy(2);
        
        $now_secs = time();
        $min_time = $now_secs - 10;
        
        $this->assertEquals(27,                     $new_task->task_id);
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
        $this->assertEquals(0,                      $new_task->task_dep_reset_dates);
        
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
        $q->addWhere('task_id = 27');
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
        $task = new CTask();
        $task->load(26);
        $new_task = $task->copy(0, 1);
        
        $now_secs = time();
        $min_time = $now_secs - 10;
        
        $this->assertEquals(27,                     $new_task->task_id);
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
        $this->assertEquals(0,                      $new_task->task_dep_reset_dates);
        
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
        $q->addWhere('task_id = 27');
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
        $task = new CTask();
        $task->load(26);
        $new_task = $task->copy(2, 1);
        
        $now_secs = time();
        $min_time = $now_secs - 10;
        
        $this->assertEquals(27,                     $new_task->task_id);
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
        $this->assertEquals(0,                      $new_task->task_dep_reset_dates);
        
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
        $q->addWhere('task_id = 27');
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
        $task = new CTask();
        $task->load(1);
        $task->copyAssignedUsers(2);

        $xml_dataset = $this->createXMLDataSet($this->getDataSetPath().'tasksTestCopyAssignedUsers.xml');
        $this->assertTablesEqual($xml_dataset->getTable('user_tasks'), $this->getConnection()->createDataSet()->getTable('user_tasks'));
    }

    /**
     * Tests the deep copy function with no project or task id passed in
     */
    public function testDeepCopyNoProjectNoTask()
    {
        $task = new CTask();
        $task->load(24);
        $task->deepCopy();

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
        $q->addWhere('task_id IN(27,28,29)');
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
        $task = new CTask();
        $task->load(24);
        $task->deepCopy(0, 1);

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
        $q->addWhere('task_id IN(27,28,29)');
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
        $task = new CTask();
        $task->load(24);
        $task->deepCopy(2, 1);

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
        $q->addWhere('task_id IN(27,28,29)');
        $results = $q->loadList();
        
        foreach($results as $dates) {
            $this->assertGreaterThanOrEqual($min_time, strtotime($dates['task_created']));
            $this->assertLessThanOrEqual($now_secs, strtotime($dates['task_created']));
            $this->assertGreaterThanOrEqual($min_time, strtotime($dates['task_updated']));
            $this->assertLessThanOrEqual($now_secs, strtotime($dates['task_updated']));
        }
    }

    /**
     * Tests the move function with no project or task passed
     */
    public function testMoveNoProjectNoTask()
    {
        $this->markTestSkipped('This function does nothing with no arguments');
    }

    /**
     * Tests the move function with a project passed but no task
     */
    public function testMoveProjectNoTask()
    {
        $task = new CTask();
        $task->load(2);
        $task->move(2);

        $now_secs = time();
        $min_time = $now_secs - 10;
        
        $xml_file_dataset = $this->createXMLDataSet(dirname(__FILE__).'/../db_files/tasksTestMoveProjectNoTask.xml');
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
        $q->addWhere('task_id = 2');
        $results = $q->loadList();
        
        foreach($results as $dates) {
            $this->assertGreaterThanOrEqual($min_time, strtotime($dates['task_updated']));
            $this->assertLessThanOrEqual($now_secs, strtotime($dates['task_updated']));
        }
    }

    /**
     * Tests the move function with a project and task passed
     */
    public function testMoveProjectTask()
    {
        $task = new CTask();
        $task->load(2);
        $task->move(2,2);

        $now_secs = time();
        $min_time = $now_secs - 10;
        
        $xml_file_dataset = $this->createXMLDataSet(dirname(__FILE__).'/../db_files/tasksTestMoveProjectTask.xml');
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
        $q->addWhere('task_id = 2');
        $results = $q->loadList();
        
        foreach($results as $dates) {
            $this->assertGreaterThanOrEqual($min_time, strtotime($dates['task_updated']));
            $this->assertLessThanOrEqual($now_secs, strtotime($dates['task_updated']));
        }
    }

    /**
     * Tests the move function with a 0 passed as the task
     */
    public function testMoveZeroTask()
    {
        $task = new CTask();
        $task->load(1);
        $task->move(0,0);

        $this->assertEquals(1, $task->task_parent);
    }

    /**
     * Tests the deepMove function with no project or task passed
     */
    public function testDeepMoveNoProjectNoTask()
    {
        $this->markTestSkipped('This function doesn\'t really do anything with no arguments');
    }

    /**
     * Tests the deepMove function with a project but no task passed
     */
    public function testDeepMoveProjectNoTask()
    {
        $task = new CTask();
        $task->load(24);
        $task->deepMove(2);

        $now_secs = time();
        $min_time = $now_secs - 10;
        
        $xml_file_dataset = $this->createXMLDataSet(dirname(__FILE__).'/../db_files/tasksTestDeepMoveProjectNoTask.xml');
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
        $q->addWhere('task_id IN(24,25,26)');
        $results = $q->loadList();
        
        foreach($results as $dates) {
            $this->assertGreaterThanOrEqual($min_time, strtotime($dates['task_updated']));
            $this->assertLessThanOrEqual($now_secs, strtotime($dates['task_updated']));
        }
    }

    /**
     * Tests the deepMove function with both a project and task passed
     */
    public function testDeepMoveProjectTask()
    {
        $task = new CTask();
        $task->load(24);
        $task->deepMove(2, 2);
        
        $now_secs = time();
        $min_time = $now_secs - 10;
        
        $xml_file_dataset = $this->createXMLDataSet(dirname(__FILE__).'/../db_files/tasksTestDeepMoveProjectTask.xml');
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
        $q->addWhere('task_id IN(24,25,26)');
        $results = $q->loadList();
        
        foreach($results as $dates) {
            $this->assertGreaterThanOrEqual($min_time, strtotime($dates['task_updated']));
            $this->assertLessThanOrEqual($now_secs, strtotime($dates['task_updated']));
        }
    }

	public function testStore()
	{
		global $AppUI;
		
		$task = new CTask();

        $post_data = array (
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

        $task->bind($post_data);
        $errorMsg = $task->store();

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
        $q->addWhere('task_id IN(' . $task->task_id . ')');
        $results = $q->loadList();

        foreach($results as $dates) {
            $this->assertGreaterThanOrEqual($min_time, strtotime($dates['task_created']));
            $this->assertGreaterThanOrEqual($min_time, strtotime($dates['task_updated']));
            $this->assertLessThanOrEqual($now_secs, strtotime($dates['task_created']));
            $this->assertLessThanOrEqual($now_secs, strtotime($dates['task_updated']));
        }
		
	}
}
?>
