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
require_once W2P_BASE_DIR . '/classes/ui.class.php';
require_once W2P_BASE_DIR . '/classes/query.class.php';

/*
 * Need this to test actions that require permissions.
 */
$AppUI  = new CAppUI;
$_POST['login'] = 'login';
$_REQUEST['login'] = 'sql';
$AppUI->login('admin', 'passwd');

require_once W2P_BASE_DIR . '/classes/permissions.class.php';
require_once W2P_BASE_DIR . '/includes/session.php';
require_once W2P_BASE_DIR . '/modules/tasks/tasks.class.php';
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
    }
}
?>
