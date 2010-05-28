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
        $this->assertNull($this->obj->task_log_problem);
        $this->assertNull($this->obj->task_log_reference);
        $this->assertNull($this->obj->task_log_related_url);
        $this->assertNull($this->obj->task_log_created);
        $this->assertNull($this->obj->task_log_updated);
        $this->assertEquals('', $this->obj->_tbl_prefix);
        $this->assertEquals('task_logs',    $this->obj->_tbl);
        $this->assertEquals('task_log_id',  $this->obj->_tbl_key);
        $this->assertEquals('',             $this->obj->_error);
        $this->assertType('DBQuery',        $this->obj->_query);
    }

}