<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Class for testing task log functionality
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

require_once W2P_BASE_DIR . '/includes/session.php';
require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Extensions/Database/TestCase.php';
require_once 'PHPUnit/Extensions/Database/DataSet/DataSetFilter.php';

/**
 * This class tests functionality for Task Logs
 *
 * @author      Trevor Morse <trevor.morse@gmail.com>
 * @category    Task Logs
 * @package     web2project
 * @subpackage  unit_tests
 * @copyright   2007-2010 The web2Project Development Team <w2p-developers@web2project.net>
 * @link        http://www.web2project.net
 */
class TaskLogs_Test extends PHPUnit_Extensions_Database_TestCase
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
        return $this->createXMLDataSet($this->getDataSetPath().'tasklogsSeed.xml');
    }

    /**
     * Get path to dataset
     */
    protected function getDataSetPath()
    {
    	return dirname(dirname(__FILE__)).'/db_files/tasks/';
    }

    /**
     * Set Up function to be run before tests
     */
    protected function setUp ()
	{
		parent::setUp();

		$this->obj = new CTaskLog();

		$this->post_data = array(
            'task_log_id'                           => 0,
            'task_log_task'                         => 1,
            'task_log_help_desk_id'                 => 1,
            'task_log_name'                         => 'This is a task log name.',
            'task_log_description'                  => 'This is a task log description.',
            'task_log_creator'                      => 1,
            'task_log_hours'                        => 2.75,
            'task_log_date'                         => '2010-05-30 09:15:30',
            'task_log_costcode'                     => 1,
            'task_log_problem'                      => 1,
            'task_log_reference'                    => 1,
            'task_log_related_url'                  => 'http://www.example.com',
            'task_log_project'                      => 1,
            'task_log_company'                      => 1,
            'task_log_created'                      => '2010-05-30 09:15:30',
            'task_log_updated'                      => '2010-05-30 09:15:30',
            'task_log_updator'                      => 1
		);
	}

    /**
     * Tear Down function to be run after tests
     */
    public function tearDown()
	{
		parent::tearDown();

		unset($this->obj, $this->post_data);
	}

    /**
     * Tests the attributes of a new TaskLog object
     */
    public function testNewTaskLogsAttributes()
    {
        $this->assertObjectHasAttribute('task_log_id',          $this->obj);
        $this->assertObjectHasAttribute('task_log_task',        $this->obj);
        $this->assertObjectHasAttribute('task_log_name',        $this->obj);
        $this->assertObjectHasAttribute('task_log_description', $this->obj);
        $this->assertObjectHasAttribute('task_log_creator',     $this->obj);
        $this->assertObjectHasAttribute('task_log_hours',       $this->obj);
        $this->assertObjectHasAttribute('task_log_date',        $this->obj);
        $this->assertObjectHasAttribute('task_log_costcode',    $this->obj);
        $this->assertObjectHasAttribute('task_log_problem',     $this->obj);
        $this->assertObjectHasAttribute('task_log_reference',   $this->obj);
        $this->assertObjectHasAttribute('task_log_related_url', $this->obj);
        $this->assertObjectHasAttribute('task_log_created',     $this->obj);
        $this->assertObjectHasAttribute('task_log_updated',     $this->obj);
    }

    /**
     * Tests the values of attributes of a new TaskLog object
     */
    public function testNewTaskLogsAttributeValues()
    {
        $this->assertNull($this->obj->task_log_id);
        $this->assertNull($this->obj->task_log_task);
        $this->assertNull($this->obj->task_log_name);
        $this->assertNull($this->obj->task_log_description);
        $this->assertNull($this->obj->task_log_creator);
        $this->assertNull($this->obj->task_log_hours);
        $this->assertNull($this->obj->task_log_date);
        $this->assertNull($this->obj->task_log_costcode);
        $this->assertNull($this->obj->task_log_reference);
        $this->assertNull($this->obj->task_log_related_url);
        $this->assertNull($this->obj->task_log_created);
        $this->assertNull($this->obj->task_log_updated);
        $this->assertEquals(0,              $this->obj->task_log_problem);
    }

    /**
     * Tests storing task log in database
     */
    public function testStoreCreate()
    {
        $this->obj->bind($this->post_data, null, true, true);
        $this->obj->store();

        $xml_file_dataset = $this->createXMLDataSet($this->getDataSetPath().'tasklogsTestStoreCreate.xml');
        $xml_file_filtered_dataset = new PHPUnit_Extensions_Database_DataSet_DataSetFilter($xml_file_dataset, array('task_log' => array('task_log_created', 'task_log_updated')));
        $xml_db_dataset = $this->getConnection()->createDataSet();
        $xml_db_filtered_dataset = new PHPUnit_Extensions_Database_DataSet_DataSetFilter($xml_db_dataset, array('task_log' => array('task_log_created', 'task_log_updated')));
        $this->assertTablesEqual($xml_file_filtered_dataset->getTable('task_log'), $xml_db_filtered_dataset->getTable('task_log'));
        $this->assertTablesEqual($xml_file_filtered_dataset->getTable('tasks'), $xml_db_filtered_dataset->getTable('tasks'));
        $this->assertTablesEqual($xml_file_filtered_dataset->getTable('projects'), $xml_db_filtered_dataset->getTable('projects'));

        /**
         * Get updated dates to test against
         */
        $now_secs = time();
        $min_time = $now_secs - 10;

        $q = new w2p_Database_Query;
        $q->addTable('task_log');
        $q->addQuery('task_log_created, task_log_updated');
        $q->addWhere('task_log_id = 2');
        $results = $q->loadList();

        foreach($results as $dates) {
            $this->assertGreaterThanOrEqual($min_time, strtotime($dates['task_log_created']));
            $this->assertGreaterThanOrEqual($min_time, strtotime($dates['task_log_updated']));
            $this->assertLessThanOrEqual($now_secs, strtotime($dates['task_log_created']));
            $this->assertLessThanOrEqual($now_secs, strtotime($dates['task_log_updated']));
        }
    }

    /**
     * Tests storing task log in database
     */
    public function testStoreUpdate()
    {
        $this->obj->bind($this->post_data, null, true, true);
        $this->obj->task_log_id = 1;
        unset($this->obj->task_log_created);
        $this->obj->store();

        $xml_file_dataset = $this->createXMLDataSet($this->getDataSetPath().'tasklogsTestStoreUpdate.xml');
        $xml_file_filtered_dataset = new PHPUnit_Extensions_Database_DataSet_DataSetFilter($xml_file_dataset, array('task_log' => array('task_log_created', 'task_log_updated')));
        $xml_db_dataset = $this->getConnection()->createDataSet();
        $xml_db_filtered_dataset = new PHPUnit_Extensions_Database_DataSet_DataSetFilter($xml_db_dataset, array('task_log' => array('task_log_created', 'task_log_updated')));
        $this->assertTablesEqual($xml_file_filtered_dataset->getTable('task_log'), $xml_db_filtered_dataset->getTable('task_log'));
        $this->assertTablesEqual($xml_file_filtered_dataset->getTable('tasks'), $xml_db_filtered_dataset->getTable('tasks'));
        $this->assertTablesEqual($xml_file_filtered_dataset->getTable('projects'), $xml_db_filtered_dataset->getTable('projects'));

        /**
         * Get updated dates to test against
         */
        $now_secs = time();
        $min_time = $now_secs - 10;

        $q = new w2p_Database_Query;
        $q->addTable('task_log');
        $q->addQuery('task_log_updated');
        $q->addWhere('task_log_id = 1');
        $results = $q->loadList();

        foreach($results as $dates) {
            $this->assertGreaterThanOrEqual($min_time, strtotime($dates['task_log_updated']));
            $this->assertLessThanOrEqual($now_secs, strtotime($dates['task_log_updated']));
        }
    }

    /**
     * Test deleting a tasklog
     */
    public function testDelete()
    {
        global $AppUI;

        $this->obj->load(1);
        $msg = $this->obj->delete();

        $xml_file_dataset = $this->createXMLDataSet($this->getDataSetPath().'tasklogsTestDelete.xml');
        $xml_file_filtered_dataset = new PHPUnit_Extensions_Database_DataSet_DataSetFilter($xml_file_dataset, array('task_log' => array('task_log_updated')));
        $xml_db_dataset = $this->getConnection()->createDataSet();
        $xml_db_filtered_dataset = new PHPUnit_Extensions_Database_DataSet_DataSetFilter($xml_db_dataset, array('task_log' => array('task_log_updated')));
        $this->assertTablesEqual($xml_file_filtered_dataset->getTable('task_log'), $xml_db_filtered_dataset->getTable('task_log'));
        $this->assertTablesEqual($xml_file_filtered_dataset->getTable('tasks'), $xml_db_filtered_dataset->getTable('tasks'));
        $this->assertTablesEqual($xml_file_filtered_dataset->getTable('projects'), $xml_db_filtered_dataset->getTable('projects'));
    }

    /**
     * TODO: This should not be in the TaskLog object and should instead be on
     *   the main w2p object: w2p_Core_BaseObject
     *
     * Test trimming all trimmable characters from all object properties
     */
    public function testW2PTrimAll()
    {
        $this->obj->bind($this->post_data, null, true, true);

        /*
         * Add all the trimmable characters to each variable in the object
         * so we can trim them all back off
         */
        $vars = get_object_vars($this->obj);

        foreach( $vars as $var_name => $var_value) {
            if (!is_object($var_value)) {
                $this->obj->$var_name = " \t\n" . $var_value . "\r\0\x0B";
            }
        }

        $this->obj->w2PTrimAll();

        $this->assertEquals(0,                                              $this->obj->task_log_id);
        $this->assertEquals(1,                                              $this->obj->task_log_task);
        $this->assertEquals(1,                                              $this->obj->task_log_help_desk_id);
        $this->assertEquals('This is a task log name.',                     $this->obj->task_log_name);
        $this->assertEquals(" \t\nThis is a task log description.\r\0\x0B", $this->obj->task_log_description);
        $this->assertEquals(1,                                              $this->obj->task_log_creator);
        $this->assertEquals(2.75,                                           $this->obj->task_log_hours);
        $this->assertEquals('2010-05-30 09:15:30',                          $this->obj->task_log_date);
        $this->assertEquals(1,                                              $this->obj->task_log_costcode);
        $this->assertEquals(1,                                              $this->obj->task_log_problem);
        $this->assertEquals(1,                                              $this->obj->task_log_reference);
        $this->assertEquals('http://www.example.com',                       $this->obj->task_log_related_url);
        $this->assertEquals(1,                                              $this->obj->task_log_project);
        $this->assertEquals(1,                                              $this->obj->task_log_company);
        $this->assertEquals('2010-05-30 09:15:30',                          $this->obj->task_log_created);
        $this->assertEquals('2010-05-30 09:15:30',                          $this->obj->task_log_updated);
        $this->assertEquals(1,                                              $this->obj->task_log_updator);
    }

    /**
     * Test canDelete with proper permissions
     */
    public function testCanDelete()
    {
        global $AppUI;
        $msg = '';

        $return = $this->obj->canDelete($msg);

        $this->assertTrue($return);
        $this->assertEquals('', $msg);
    }

    /**
     * Test canDelete without permissions
     */
    public function testCanDeleteNoPermissions()
    {
        global $AppUI;
        $msg = '';

         // Login as another user for permission purposes
        $old_AppUI = $AppUI;
        $AppUI  = new CAppUI;
        $_POST['login'] = 'login';
        $_REQUEST['login'] = 'sql';

        $return = $this->obj->canDelete($msg);

        // Restore AppUI for following tests since its global, yuck!
        $AppUI = $old_AppUI;

        $this->assertFalse($return);
        $this->assertEquals('noDeletePermission', $msg);
    }

    /**
     * Tests canDelete with passing an object id
     */
    public function testCanDeleteOid()
    {
        global $AppUI;
        $msg = '';

        $return = $this->obj->canDelete($msg, 2);

        $this->assertTrue($return);
        $this->assertEquals('', $msg);
    }

    /**
     * Tests canDelete with passing an object id and a join array
     */
    public function testCanDeleteOidJoins()
    {
        $this->markTestSkipped('Nothing uses tasklog_id as a foreign key so joins won\'t work');
    }

    /**
     * Test getting allowed records with a uid passed
     */
    public function testGetAllowedRecordUid()
    {
        global $AppUI;

        $allowed_records = $this->obj->getAllowedRecords(1);

        $this->assertEquals(1, count($allowed_records));
        $this->assertEquals(1, $allowed_records[1]);
    }

    /**
     * Test getting allowed records when passing a uid and fields
     */
    public function testGetAllowedRecordsFields()
    {
        global $AppUI;

        $allowed_records = $this->obj->getAllowedRecords(1, 'task_log.task_log_task, task_log.task_log_name');

        $this->assertEquals(1,              count($allowed_records));
        $this->assertEquals('Task Log 1',   $allowed_records[1]);
    }


    /**
     * Tests getting allowed records with an order by
     */
    public function testGetAllowedRecordsOrderBy()
    {
        global $AppUI;

        $allowed_records = $this->obj->getAllowedRecords(1, '*', 'task_log.task_log_hours');

        $this->assertEquals(1, count($allowed_records));
        $this->assertEquals(1, $allowed_records[1]);
    }

    /**
     * Test getting allowed records with an index specified
     */
    public function testGetAllowedRecordsIndex()
    {
        global $AppUI;

        $allowed_records = $this->obj->getAllowedRecords(1, 'task_log.task_log_name', '', 'task_log_name');

        $this->assertEquals(1,              count($allowed_records));
        $this->assertEquals(2,              count($allowed_records['Task Log 1']));
        $this->assertEquals('Task Log 1',   $allowed_records['Task Log 1']['task_log_name']);
        $this->assertEquals('Task Log 1',   $allowed_records['Task Log 1'][0]);
    }

    /**
     * Test getting allowed records with extra data specified
     */
    public function testGetAllowedRecordsExtra()
    {
        $extra = array('from' => 'task_log', 'join' => 'tasks', 'on' => 'task_id = task_log_task',
                       'where' => 'task_id = 1');
        $allowed_records = $this->obj->getAllowedRecords(1, '*', '', null, $extra);

        $this->assertEquals(1, count($allowed_records));
        $this->assertEquals(1, $allowed_records[1]);
    }
}