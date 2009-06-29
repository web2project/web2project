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
require_once W2P_BASE_DIR . '/classes/CustomFields.class.php';
require_once W2P_BASE_DIR . '/modules/projects/projects.class.php';
require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Extensions/Database/TestCase.php';

/**
 * Project_Test Class.
 * 
 * Class to test the projects class
 * @author Trevor Morse<trevor.morse@gmail.com>
 * @package web2project
 * @subpackage unit_tests
 */
class Projects_Test extends PHPUnit_Extensions_Database_TestCase 
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
        return $this->createXMLDataSet($this->getDataSetPath().'projectsSeed.xml');
    }
    protected function getDataSetPath()
    {
    	return dirname(dirname(__FILE__)).'/db_files/';
    }
    
    /**
     * Tests the Attributes of a new Projec object.
     */
    public function testNewProjectAttributes() 
    {
    	
    	$project = new CProject();
    	
    	$this->assertType('CProject', $project);
    	$this->assertObjectHasAttribute('project_id',                  $project);
    	$this->assertObjectHasAttribute('project_company',             $project);
    	$this->assertObjectHasAttribute('project_department',          $project);
    	$this->assertObjectHasAttribute('project_name',                $project);
    	$this->assertObjectHasAttribute('project_short_name',          $project);
    	$this->assertObjectHasAttribute('project_owner',               $project);
    	$this->assertObjectHasAttribute('project_url',                 $project);
    	$this->assertObjectHasAttribute('project_demo_url',            $project);
    	$this->assertObjectHasAttribute('project_start_date',          $project);
    	$this->assertObjectHasAttribute('project_end_date',            $project);
    	$this->assertObjectHasAttribute('project_actual_end_date',     $project);
    	$this->assertObjectHasAttribute('project_status',              $project);
    	$this->assertObjectHasAttribute('project_percent_complete',    $project);
    	$this->assertObjectHasAttribute('project_color_identifier',    $project);
    	$this->assertObjectHasAttribute('project_description',         $project);
    	$this->assertObjectHasAttribute('project_target_budget',       $project);
    	$this->assertObjectHasAttribute('project_actual_budget',       $project);
    	$this->assertObjectHasAttribute('project_creator',             $project);
    	$this->assertObjectHasAttribute('project_active',              $project);
    	$this->assertObjectHasAttribute('project_private',             $project);
    	$this->assertObjectHasAttribute('project_departments',         $project);
    	$this->assertObjectHasAttribute('project_contacts',            $project);
    	$this->assertObjectHasAttribute('project_priority',            $project);
    	$this->assertObjectHasAttribute('project_type',                $project);
    	$this->assertObjectHasAttribute('project_parent',              $project);
    	$this->assertObjectHasAttribute('project_original_parent',     $project);
    	$this->assertObjectHasAttribute('project_location',            $project);
    	$this->assertObjectHasAttribute('_tbl_prefix',                 $project);
    	$this->assertObjectHasAttribute('_tbl',                        $project);
    	$this->assertObjectHasAttribute('_tbl_key',                    $project);
    	$this->assertObjectHasAttribute('_error',                      $project);
    	$this->assertObjectHasAttribute('_query',                      $project);
    }

    /**
     * Tests the Attribute Values of a new Project object.
     */
    public function testNewProjectAttributeValues()
    {
        $project = new CProject();
        
        $this->assertType('CProject', $project);
        $this->assertNull($project->project_id);
        $this->assertNull($project->project_company);
        $this->assertNull($project->project_department);
        $this->assertNull($project->project_name);
        $this->assertNull($project->project_short_name);
        $this->assertNull($project->project_owner);
        $this->assertNull($project->project_url);
        $this->assertNull($project->project_demo_url);
        $this->assertNull($project->project_start_date);
        $this->assertNull($project->project_end_date);
        $this->assertNull($project->project_actual_end_date);
        $this->assertNull($project->project_status);
        $this->assertNull($project->project_percent_complete);
        $this->assertNull($project->project_color_identifier);
        $this->assertNull($project->project_description);
        $this->assertNull($project->project_target_budget);
        $this->assertNull($project->project_actual_buget);
        $this->assertNull($project->project_creator);
        $this->assertNull($project->project_active);
        $this->assertNull($project->project_private);
        $this->assertNull($project->project_departments);
        $this->assertNull($project->project_contacts);
        $this->assertNull($project->project_priority);
        $this->assertNull($project->project_type);
        $this->assertNull($project->project_parent);
        $this->assertNull($project->project_original_parent);
        $this->assertEquals('',             $project->project_location);
        $this->assertEquals('',             $project->_tbl_prefix);
        $this->assertEquals('projects',     $project->_tbl);
        $this->assertEquals('project_id',   $project->_tbl_key);
        $this->assertEquals('',             $project->_error);
        $this->assertType('DBQuery', $project->_query);
    } 

    /**
     * Tests that the proper error message is returned when no ID is passed.
     */
    public function testCreateProjectNoID()
    {
    	$project = new CProject();
    	
    	$post_data = array(
            'dosql' =>                       'do_project_aed', 
			'project_creator' =>             1, 
			'project_contacts' =>            '',
			'project_name' =>                'New Project',
			'project_parent' =>              '',
			'project_owner' =>               1, 
			'project_company' =>             1,
			'project_location' =>            '', 
			'project_start_date' =>          '20090628', 
			'project_end_date' =>            '20090728', 
			'project_target_budget' =>       '', 
			'project_actual_budget' =>       '', 
			'project_url' =>                 '', 
			'project_demo_url' =>            '', 
			'project_priority' =>            '-1',
			'project_short_name' =>          'nproject',
			'project_color_identifier' =>    'FFFFFF',
			'project_type' =>                0,
			'project_status' =>              0, 
			'project_description' =>         '', 
			'email_project_owner' =>         1,
			'email_project_contacts' =>      1    	
    	);
    	
    	$project->bind($post_data);
    	$msg = $project->store();
    	
    	/**
         * Verify we got the proper error message
         */
        $this->AssertEquals('CProject::store-check failed project id is NULL', $msg);
        
        /**
         * Verify that project id was not set
         */
        $this->AssertNull($project->project_id);
    }
    
/**
     * Tests that the proper error message is returned when no ID is passed.
     */
    public function testCreateProjectNoName()
    {
        $project = new CProject();
        
        $post_data = array(
            'dosql' =>                       'do_project_aed', 
            'project_id' =>                  0,
            'project_creator' =>             1, 
            'project_contacts' =>            '',
            'project_parent' =>              '',
            'project_owner' =>               1, 
            'project_company' =>             1,
            'project_location' =>            '', 
            'project_start_date' =>          '20090628', 
            'project_end_date' =>            '20090728', 
            'project_target_budget' =>       '', 
            'project_actual_budget' =>       '', 
            'project_url' =>                 '', 
            'project_demo_url' =>            '', 
            'project_priority' =>            '-1',
            'project_short_name' =>          'nproject',
            'project_color_identifier' =>    'FFFFFF',
            'project_type' =>                0,
            'project_status' =>              0, 
            'project_description' =>         '', 
            'email_project_owner' =>         1,
            'email_project_contacts' =>      1      
        );
        
        $project->bind($post_data);
        $msg = $project->store();
        
        /**
         * Verify we got the proper error message
         */
        $this->AssertEquals('CProject::store-check failed project name is NULL', $msg);
        
        /**
         * Verify that project id was not set
         */
        $this->AssertNull($project->project_id);
    }
}