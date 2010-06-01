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
    	return dirname(dirname(__FILE__)).'/db_files/';
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
            'task_log_changelog'                    => 1,
            'task_log_changelog_servers'            => '10.10.10.101',
            'task_log_changelog_whom'               => 1,
            'task_log_changelog_datetime'           => '2010-05-31 10:15:25',
            'task_log_changelog_duration'           => '35 minutes',
            'task_log_changelog_expected_downtime'  => 1,
            'task_log_changelog_description'        => 'This is a changelog description',
            'task_log_changelog_backout_plan'       => 'There is no backout plan',
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
        $this->assertType('CTaskLog',                           $this->obj);
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
        $this->assertObjectHasAttribute('_tbl_prefix',          $this->obj);
        $this->assertObjectHasAttribute('_tbl',                 $this->obj);
        $this->assertObjectHasAttribute('_tbl_key',             $this->obj);
        $this->assertObjectHasAttribute('_error',               $this->obj);
        $this->assertObjectHasAttribute('_query',               $this->obj);
    }

    /**
     * Tests the values of attributes of a new TaskLog object
     */
    public function testNewTaskLogsAttributeValues()
    {
        $this->assertType('CTaskLog', $this->obj);
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
        $this->assertEquals('',             $this->obj->_tbl_prefix);
        $this->assertEquals('task_log',     $this->obj->_tbl);
        $this->assertEquals('task_log_id',  $this->obj->_tbl_key);
        $this->assertEquals('',             $this->obj->_error);
        $this->assertType('DBQuery',        $this->obj->_query);
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

        $q = new DBQuery;
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
        $xml_file_filtered_dataset = new PHPUnit_Extensions_Database_DataSet_DataSetFilter($xml_file_dataset, array('task_log' => array('task_log_updated')));
        $xml_db_dataset = $this->getConnection()->createDataSet();
        $xml_db_filtered_dataset = new PHPUnit_Extensions_Database_DataSet_DataSetFilter($xml_db_dataset, array('task_log' => array('task_log_updated')));
        $this->assertTablesEqual($xml_file_filtered_dataset->getTable('task_log'), $xml_db_filtered_dataset->getTable('task_log'));
        $this->assertTablesEqual($xml_file_filtered_dataset->getTable('tasks'), $xml_db_filtered_dataset->getTable('tasks'));
        $this->assertTablesEqual($xml_file_filtered_dataset->getTable('projects'), $xml_db_filtered_dataset->getTable('projects'));

        /**
         * Get updated dates to test against
         */
        $now_secs = time();
        $min_time = $now_secs - 10;

        $q = new DBQuery;
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
        $this->obj->load(1);
        $this->obj->delete();

        $xml_file_dataset = $this->createXMLDataSet($this->getDataSetPath().'tasklogsTestDelete.xml');
        $xml_file_filtered_dataset = new PHPUnit_Extensions_Database_DataSet_DataSetFilter($xml_file_dataset, array('task_log' => array('task_log_updated')));
        $xml_db_dataset = $this->getConnection()->createDataSet();
        $xml_db_filtered_dataset = new PHPUnit_Extensions_Database_DataSet_DataSetFilter($xml_db_dataset, array('task_log' => array('task_log_updated')));
        $this->assertTablesEqual($xml_file_filtered_dataset->getTable('task_log'), $xml_db_filtered_dataset->getTable('task_log'));
        $this->assertTablesEqual($xml_file_filtered_dataset->getTable('tasks'), $xml_db_filtered_dataset->getTable('tasks'));
        $this->assertTablesEqual($xml_file_filtered_dataset->getTable('projects'), $xml_db_filtered_dataset->getTable('projects'));
    }
}