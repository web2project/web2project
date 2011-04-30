<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Class for testing projects functionality
 *
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version. Please see the LICENSE file in root of site
 * for further details
 *
 * @category    Projects
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
 * This class tests functionality for Projects
 *
 * @author      Trevor Morse <trevor.morse@gmail.com>
 * @category    Projects
 * @package     web2project
 * @subpackage  unit_tests
 * @copyright   2007-2010 The web2Project Development Team <w2p-developers@web2project.net>
 * @link        http://www.web2project.net
 */
class Projects_Test extends PHPUnit_Extensions_Database_TestCase
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
        return $this->createXMLDataSet($this->getDataSetPath().'projectsSeed.xml');
    }
    protected function getDataSetPath()
    {
    	return dirname(dirname(__FILE__)).'/db_files/projects/';
    }

	protected function setUp ()
	{
		parent::setUp();

		$this->obj = new CProject();

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

	public function tearDown()
	{
		parent::tearDown();

		unset($this->obj, $this->post_data);
	}

    /**
     * Tests the Attributes of a new Projec object.
     */
    public function testNewProjectAttributes()
    {
    	$this->assertType('CProject', $this->obj);
    	$this->assertObjectHasAttribute('project_id',                  $this->obj);
    	$this->assertObjectHasAttribute('project_company',             $this->obj);
    	$this->assertObjectHasAttribute('project_name',                $this->obj);
    	$this->assertObjectHasAttribute('project_short_name',          $this->obj);
    	$this->assertObjectHasAttribute('project_owner',               $this->obj);
    	$this->assertObjectHasAttribute('project_url',                 $this->obj);
    	$this->assertObjectHasAttribute('project_demo_url',            $this->obj);
    	$this->assertObjectHasAttribute('project_start_date',          $this->obj);
    	$this->assertObjectHasAttribute('project_end_date',            $this->obj);
    	$this->assertObjectHasAttribute('project_actual_end_date',     $this->obj);
    	$this->assertObjectHasAttribute('project_status',              $this->obj);
    	$this->assertObjectHasAttribute('project_percent_complete',    $this->obj);
    	$this->assertObjectHasAttribute('project_color_identifier',    $this->obj);
    	$this->assertObjectHasAttribute('project_description',         $this->obj);
    	$this->assertObjectHasAttribute('project_target_budget',       $this->obj);
    	$this->assertObjectHasAttribute('project_actual_budget',       $this->obj);
        $this->assertObjectHasAttribute('project_scheduled_hours',     $this->obj);
        $this->assertObjectHasAttribute('project_worked_hours',        $this->obj);
        $this->assertObjectHasAttribute('project_task_count',          $this->obj);
    	$this->assertObjectHasAttribute('project_creator',             $this->obj);
    	$this->assertObjectHasAttribute('project_active',              $this->obj);
    	$this->assertObjectHasAttribute('project_private',             $this->obj);
    	$this->assertObjectHasAttribute('project_departments',         $this->obj);
    	$this->assertObjectHasAttribute('project_contacts',            $this->obj);
    	$this->assertObjectHasAttribute('project_priority',            $this->obj);
    	$this->assertObjectHasAttribute('project_type',                $this->obj);
    	$this->assertObjectHasAttribute('project_parent',              $this->obj);
    	$this->assertObjectHasAttribute('project_original_parent',     $this->obj);
    	$this->assertObjectHasAttribute('project_location',            $this->obj);
    }

    /**
     * Tests the Attribute Values of a new Project object.
     */
    public function testNewProjectAttributeValues()
    {
        $this->assertType('CProject', $this->obj);
        $this->assertNull($this->obj->project_id);
        $this->assertNull($this->obj->project_company);
        $this->assertNull($this->obj->project_department);
        $this->assertNull($this->obj->project_name);
        $this->assertNull($this->obj->project_short_name);
        $this->assertNull($this->obj->project_owner);
        $this->assertNull($this->obj->project_url);
        $this->assertNull($this->obj->project_demo_url);
        $this->assertNull($this->obj->project_start_date);
        $this->assertNull($this->obj->project_end_date);
        $this->assertNull($this->obj->project_actual_end_date);
        $this->assertNull($this->obj->project_status);
        $this->assertNull($this->obj->project_percent_complete);
        $this->assertNull($this->obj->project_color_identifier);
        $this->assertNull($this->obj->project_description);
        $this->assertNull($this->obj->project_target_budget);
        $this->assertNull($this->obj->project_actual_buget);
        $this->assertNull($this->obj->project_scheduled_hours);
        $this->assertNull($this->obj->project_worked_hours);
        $this->assertNull($this->obj->project_task_count);
        $this->assertNull($this->obj->project_creator);
        $this->assertNull($this->obj->project_active);
        $this->assertNull($this->obj->project_private);
        $this->assertNull($this->obj->project_departments);
        $this->assertNull($this->obj->project_contacts);
        $this->assertNull($this->obj->project_priority);
        $this->assertNull($this->obj->project_type);
        $this->assertNull($this->obj->project_parent);
        $this->assertNull($this->obj->project_original_parent);
    }

    /**
     * Tests that the proper error message is returned when no ID is passed.
     */
    public function testCreateProjectNoID()
    {
    	$this->markTestSkipped('This test has been deprecated by casting the project_id via intval().');
    }

    /**
     * Tests that the proper error message is returned when no name is passed.
     */
    public function testCreateProjectNoName()
    {
        global $AppUI;

		unset($this->post_data['project_name']);
        $this->obj->bind($post_data);
        $errorArray = $this->obj->store($AppUI);

        /**
         * Verify we got the proper error message
         */
        $this->assertArrayHasKey('project_name', $errorArray);

        /**
         * Verify that project id was not set
         */
        $this->AssertNull($this->obj->project_id);
    }

    /**
     * Tests that the proper error message is returned when no company is passed.
     */
    public function testCreateProjectNoCompany()
    {
        global $AppUI;

		unset($this->post_data['project_company']);

        $this->obj->bind($this->post_data);
        $errorArray = $this->obj->store($AppUI);

        /**
         * Verify we got the proper error message
         */
        $this->assertArrayHasKey('project_company', $errorArray);

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
        global $AppUI;

		unset($this->post_data['project_priority']);

        $this->obj->bind($this->post_data);
        $errorArray = $this->obj->store($AppUI);

        /**
         * Verify we got the proper error message
         */
        $this->assertArrayHasKey('project_priority', $errorArray);

        /**
         * Verify that project id was not set
         */
        $this->AssertEquals(0, $this->obj->project_id);
    }

    /**
     * Tests that the proper error message is returned when no short name is passed.
     */
    public function testCreateProjectNoShortName()
    {
        global $AppUI;

		unset($this->post_data['project_short_name']);

        $this->obj->bind($this->post_data);
        $errorArray = $this->obj->store($AppUI);

        /**
         * Verify we got the proper error message
         */
        $this->assertArrayHasKey('project_short_name', $errorArray);

        /**
         * Verify that project id was not set
         */
        $this->AssertEquals(0, $this->obj->project_id);
    }

    /**
     * Tests that the proper error message is returned when no color identifier is passed.
     */
    public function testCreateProjectNoColorIdentifier()
    {
        global $AppUI;

        $project = new CProject();

		unset($this->post_data['project_color_identifier']);

        $this->obj->bind($this->post_data);
        $errorArray = $this->obj->store($AppUI);

        /**
         * Verify we got the proper error message
         */
        $this->assertArrayHasKey('project_color_identifier', $errorArray);

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
        global $AppUI;

		unset($this->post_data['project_type']);

        $this->obj->bind($this->post_data);
        $errorArray = $this->obj->store($AppUI);

        /**
         * Verify we got the proper error message
         */
        $this->assertArrayHasKey('project_type', $errorArray);

        /**
         * Verify that project id was not set
         */
        $this->AssertEquals(0, $this->obj->project_id);
    }

    /**
     * Tests that the proper error message is returned when no status is passed.
     */
    public function testCreateProjectNoStatus()
    {
        global $AppUI;

		unset($this->post_data['project_status']);

        $this->obj->bind($this->post_data);
        $errorArray = $this->obj->store($AppUI);

        /**
         * Verify we got the proper error message
         */
        $this->assertArrayHasKey('project_status', $errorArray);

        /**
         * Verify that project id was not set
         */
        $this->AssertEquals(0, $this->obj->project_id);
    }

    /**
     * Tests that the proper error message is returned when no owner is passed.
     */
    public function testCreateProjectNoOwner()
    {
        global $AppUI;

		unset($this->post_data['project_owner']);

        $this->obj->bind($this->post_data);
        $results = $this->obj->store($AppUI);

        /**
         * Verify we got the proper error message
         */
        $this->assertArrayHasKey('project_owner', $results);

        /**
         * Verify that project id was not set
         */
        $this->AssertEquals(0, $this->obj->project_id);
    }

    /**
     * Tests that the proper error message is returned when no creator is passed.
     */
    public function testCreateProjectNoCreator()
    {
        global $AppUI;

		unset($this->post_data['project_creator']);

        $this->obj->bind($this->post_data);
        $results = $this->obj->store($AppUI);

        /**
         * Verify we got the proper error message
         */
        $this->assertArrayHasKey('project_creator', $results);

        /**
         * Verify that project id was not set
         */
        $this->AssertEquals(0, $this->obj->project_id);
    }

    /**
     * Tests the proper creation of a project.
     */
    public function testCreateProject()
    {
        global $AppUI;

        $this->obj->bind($this->post_data);
        $results = $this->obj->store($AppUI);

        $this->assertTrue($results);
        $this->assertEquals(5,                          $this->obj->project_id);
        $this->assertEquals(1,                          $this->obj->project_company);
        $this->assertEquals('',                         $this->obj->project_department);
        $this->assertEquals('New Project',              $this->obj->project_name);
        $this->assertEquals('nproject',                 $this->obj->project_short_name);
        $this->assertEquals(1,                          $this->obj->project_owner);
        $this->assertEquals('project.example.org',      $this->obj->project_url);
        $this->assertEquals('projectdemo.example.org',  $this->obj->project_demo_url);
        $this->assertEquals('2009-06-28 00:00:00',      $this->obj->project_start_date);
        $this->assertEquals('2009-07-28 23:59:59',      $this->obj->project_end_date);
        $this->assertEquals('',                         $this->obj->project_actual_end_date);
        $this->assertEquals(0,                          $this->obj->project_status);
        $this->assertEquals('',                         $this->obj->project_percent_complete);
        $this->assertEquals('FFFFFF',                   $this->obj->project_color_identifier);
        $this->assertEquals('This is a project.',       $this->obj->project_description);
        $this->assertEquals(5,                          $this->obj->project_target_budget);
        $this->assertEquals(10,                         $this->obj->project_actual_budget);
        $this->assertEquals(0,                          $this->obj->project_scheduled_hours);
        $this->assertEquals(0,                          $this->obj->project_worked_hours);
        $this->assertEquals(0,                          $this->obj->project_task_count);
        $this->assertEquals(1,                          $this->obj->project_creator);
        $this->assertEquals(0,                          $this->obj->project_active);
        $this->assertEquals(0,                          $this->obj->project_private);
        $this->assertEquals('',                         $this->obj->project_departments);
        $this->assertEquals('',							$this->obj->project_contacts);
        $this->assertEquals(-1,                         $this->obj->project_priority);
        $this->assertEquals(0,                          $this->obj->project_type);
        $this->assertEquals(5,                          $this->obj->project_parent);
        $this->assertEquals(5,                          $this->obj->project_original_parent);
        $this->assertEquals('',                         $this->obj->project_location);

        $xml_file_dataset = $this->createXMLDataSet($this->getDataSetPath().'projectsTestCreateProject.xml');
        $xml_file_filtered_dataset = new PHPUnit_Extensions_Database_DataSet_DataSetFilter($xml_file_dataset, array('projects' => array('project_created', 'project_updated')));
        $xml_db_dataset = $this->getConnection()->createDataSet();
        $xml_db_filtered_dataset = new PHPUnit_Extensions_Database_DataSet_DataSetFilter($xml_db_dataset, array('projects' => array('project_created', 'project_updated')));

        $this->assertTablesEqual($xml_file_filtered_dataset->getTable('projects'), $xml_db_filtered_dataset->getTable('projects'));

        /**
         * Get created date to test against
         */
        $q = new w2p_Database_Query;
        $q->addTable('projects');
        $q->addQuery('project_created');
        $q->addWhere('project_id = ' . $this->obj->project_id);
        $project_created = $q->loadResult();
        $project_created = strtotime($project_created);

        /**
         * Get updated date to test against
         */
        $q = new w2p_Database_Query;
        $q->addTable('projects');
        $q->addQuery('project_updated');
        $q->addWhere('project_id = ' . $this->obj->project_id);
        $project_updated = $q->loadResult();
        $project_updated =  strtotime($project_updated);

        $now_secs = time();
        $min_time = $now_secs - 10;

        $this->assertGreaterThanOrEqual($min_time, $project_created);
        $this->assertLessThanOrEqual($now_secs, $project_created);
        $this->assertGreaterThanOrEqual($min_time, $project_updated);
        $this->assertLessThanOrEqual($now_secs, $project_updated);
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
    	$this->obj->load(1);

    	$this->assertEquals(1,                                  $this->obj->project_id);
    	$this->assertEquals(1,                                  $this->obj->project_company);
      	$this->assertEquals('Test Project',                     $this->obj->project_name);
      	$this->assertEquals('TP',                               $this->obj->project_short_name);
      	$this->assertEquals(1,                                  $this->obj->project_owner);
      	$this->assertEquals('http://project1.example.org',      $this->obj->project_url);
      	$this->assertEquals('http://project1-demo.example.org', $this->obj->project_demo_url);
      	$this->assertEquals('2009-07-05 00:00:00',              $this->obj->project_start_date);
      	$this->assertEquals('2009-07-15 23:59:59',              $this->obj->project_end_date);
      	$this->assertEquals('2009-08-15 00:00:00',              $this->obj->project_actual_end_date);
      	$this->assertEquals(0,                                  $this->obj->project_status);
      	$this->assertEquals(0,                                  $this->obj->project_percent_complete);
      	$this->assertEquals('FFFFFF',                           $this->obj->project_color_identifier);
      	$this->assertEquals('This is a project',                $this->obj->project_description);
      	$this->assertEquals('15.00',                            $this->obj->project_target_budget);
      	$this->assertEquals('5.00',                             $this->obj->project_actual_budget);
      	$this->assertEquals(0,                                  $this->obj->project_scheduled_hours);
      	$this->assertEquals(0,                                  $this->obj->project_worked_hours);
      	$this->assertEquals(0,                                  $this->obj->project_task_count);
      	$this->assertEquals(1,                                  $this->obj->project_creator);
      	$this->assertEquals(1,                                  $this->obj->project_active);
      	$this->assertEquals(0,                                  $this->obj->project_private);
      	$this->assertEquals('',                                 $this->obj->project_departments);
      	$this->assertEquals('',                                 $this->obj->project_contacts);
      	$this->assertEquals(-1,                                 $this->obj->project_priority);
      	$this->assertEquals(0,                                  $this->obj->project_type);
      	$this->assertEquals(1,                                  $this->obj->project_parent);
      	$this->assertEquals(1,                                  $this->obj->project_original_parent);
      	$this->assertEquals('Somewhere',                        $this->obj->project_location);
    }

    /**
     * Test loading the Project object.
     */
    public function testLoadFull()
    {
        global $AppUI;

        $this->obj->loadFull($AppUI, 1);

    	$this->assertEquals(1,                                  $this->obj->project_id);
      	$this->assertEquals(1,                                  $this->obj->project_company);
      	$this->assertEquals('Test Project',                     $this->obj->project_name);
      	$this->assertEquals('TP',                               $this->obj->project_short_name);
      	$this->assertEquals(1,                                  $this->obj->project_owner);
      	$this->assertEquals('http://project1.example.org',      $this->obj->project_url);
      	$this->assertEquals('http://project1-demo.example.org', $this->obj->project_demo_url);
      	$this->assertEquals('2009-07-05 00:00:00',              $this->obj->project_start_date);
      	$this->assertEquals('2009-07-15 23:59:59',              $this->obj->project_end_date);
      	$this->assertEquals('2009-08-15 00:00:00',              $this->obj->project_actual_end_date);
      	$this->assertEquals(0,                                  $this->obj->project_status);
      	$this->assertEquals(0.00,                               $this->obj->project_percent_complete);
      	$this->assertEquals('FFFFFF',                           $this->obj->project_color_identifier);
      	$this->assertEquals('This is a project',                $this->obj->project_description);
      	$this->assertEquals('15.00',                            $this->obj->project_target_budget);
      	$this->assertEquals('5.00',                             $this->obj->project_actual_budget);
      	$this->assertEquals(0,                                  $this->obj->project_scheduled_hours);
      	$this->assertEquals(0,                                  $this->obj->project_worked_hours);
      	$this->assertEquals(0,                                  $this->obj->project_task_count);
      	$this->assertEquals(1,                                  $this->obj->project_creator);
      	$this->assertEquals(1,                                  $this->obj->project_active);
      	$this->assertEquals(0,                                  $this->obj->project_private);
      	$this->assertEquals('',                                 $this->obj->project_departments);
      	$this->assertEquals('',                                 $this->obj->project_contacts);
      	$this->assertEquals(-1,                                 $this->obj->project_priority);
      	$this->assertEquals(0,                                  $this->obj->project_type);
      	$this->assertEquals(1,                                  $this->obj->project_parent);
      	$this->assertEquals(1,                                  $this->obj->project_original_parent);
      	$this->assertEquals('Somewhere',                        $this->obj->project_location);
      	$this->assertEquals('UnitTestCompany',                  $this->obj->company_name);
      	$this->assertEquals('Admin Person',                     $this->obj->user_name);
    }

    /**
     * Tests the update of a project.
     */
    public function testUpdateProject()
    {
      	global $AppUI;

    	$this->obj->load(1);

		$this->post_data['dosql'] 						= 'do_project_aed';
		$this->post_data['project_id'] 					= 1;
		$this->post_data['project_creator'] 			= 1;
		$this->post_data['project_contacts'] 			= '';
		$this->post_data['project_name'] 				= 'Updated Project';
		$this->post_data['project_parent'] 				= '';
		$this->post_data['project_owner'] 				= 1;
		$this->post_data['project_company'] 			= 1;
		$this->post_data['project_location'] 			= 'Somewhere Updated';
		$this->post_data['project_start_date'] 			= '20090728';
		$this->post_data['project_end_date'] 			= '20090828';
		$this->post_data['project_scheduled_hours'] 	= 0;
		$this->post_data['project_worked_hours'] 		= 0;
		$this->post_data['project_task_count'] 			= 0;
		$this->post_data['project_target_budget'] 		= 15;
		$this->post_data['project_actual_budget'] 		= 15;
		$this->post_data['project_url'] 				= 'project-update.example.org';
		$this->post_data['project_demo_url'] 			= 'project-updatedemo.example.org';
		$this->post_data['project_priority'] 			= '1';
		$this->post_data['project_short_name'] 			= 'uproject';
		$this->post_data['project_color_identifier']	= 'CCCEEE';
		$this->post_data['project_type'] 				= 1;
		$this->post_data['project_status'] 				= 1;
		$this->post_data['project_description'] 		= 'This is an updated project.';
		$this->post_data['email_project_owner'] 		= 1;
		$this->post_data['email_project_contacts'] 		= 0;

        $this->obj->bind($this->post_data);
        $results = $this->obj->store($AppUI);

        $this->assertTrue($results);
        $this->assertEquals(1,                                  $this->obj->project_id);
        $this->assertEquals(1,                                  $this->obj->project_company);
        $this->assertEquals('Updated Project',                  $this->obj->project_name);
        $this->assertEquals('uproject',                         $this->obj->project_short_name);
        $this->assertEquals(1,                                  $this->obj->project_owner);
        $this->assertEquals('project-update.example.org',       $this->obj->project_url);
        $this->assertEquals('project-updatedemo.example.org',   $this->obj->project_demo_url);
        $this->assertEquals('2009-07-28 00:00:00',              $this->obj->project_start_date);
        $this->assertEquals('2009-08-28 23:59:59',              $this->obj->project_end_date);
        $this->assertEquals('2009-08-15 00:00:00',              $this->obj->project_actual_end_date);
        $this->assertEquals(1,                                  $this->obj->project_status);
        $this->assertEquals(0.00,                               $this->obj->project_percent_complete);
        $this->assertEquals('CCCEEE',                           $this->obj->project_color_identifier);
        $this->assertEquals('This is an updated project.',      $this->obj->project_description);
        $this->assertEquals(15,                                 $this->obj->project_target_budget);
        $this->assertEquals(15,                                 $this->obj->project_actual_budget);
        $this->assertEquals(0,                                  $this->obj->project_scheduled_hours);
        $this->assertEquals(0,                                  $this->obj->project_worked_hours);
        $this->assertEquals(0,                                  $this->obj->project_task_count);
        $this->assertEquals(1,                                  $this->obj->project_creator);
        $this->assertEquals(1,                                  $this->obj->project_active);
        $this->assertEquals(0,                                  $this->obj->project_private);
        $this->assertEquals('',                                 $this->obj->project_departments);
        $this->assertEquals('',									$this->obj->project_contacts);
        $this->assertEquals(1,                                  $this->obj->project_priority);
        $this->assertEquals(1,                                  $this->obj->project_type);
        $this->assertEquals(1,                                  $this->obj->project_parent);
        $this->assertEquals(1,                                  $this->obj->project_original_parent);
        $this->assertEquals('Somewhere Updated',                $this->obj->project_location);

        $xml_file_dataset = $this->createXMLDataSet($this->getDataSetPath().'projectsTestUpdateProject.xml');
        $xml_file_filtered_dataset = new PHPUnit_Extensions_Database_DataSet_DataSetFilter($xml_file_dataset, array('projects' => array('project_updated')));
        $xml_db_dataset = $this->getConnection()->createDataSet();
        $xml_db_filtered_dataset = new PHPUnit_Extensions_Database_DataSet_DataSetFilter($xml_db_dataset, array('projects' => array('project_updated')));

        $this->assertTablesEqual($xml_file_filtered_dataset->getTable('projects'), $xml_db_filtered_dataset->getTable('projects'));

        /**
         * Get updated date to test against
         */
        $q = new w2p_Database_Query;
        $q->addTable('projects');
        $q->addQuery('project_updated');
        $q->addWhere('project_id = ' . $this->obj->project_id);
        $project_updated = $q->loadResult();
        $project_updated =  strtotime($project_updated);

        $now_secs = time();
        $min_time = $now_secs - 10;

        $this->assertGreaterThanOrEqual($min_time, $project_updated);
        $this->assertLessThanOrEqual($now_secs, $project_updated);
    }

    /**
     * Tests the canDelete function of a project
     */
    public function testCanDelete()
    {
        $this->markTestSkipped('This test has been skipped because it simply returns parent::canDelete, new functionality has been disabled.');
    }

    /**
     * Tests deletion of a project.
     */
    public function testDeleteProject()
    {
        global $AppUI;

        $this->obj->load(1);
        $this->obj->delete($AppUI);

        $xml_dataset = $this->createXMLDataSet($this->getDataSetPath().'projectsTestDeleteProject.xml');
        $this->assertTablesEqual($xml_dataset->getTable('projects'),            $this->getConnection()->createDataSet()->getTable('projects'));
        $this->assertTablesEqual($xml_dataset->getTable('project_contacts'),    $this->getConnection()->createDataSet()->getTable('project_contacts'));
        $this->assertTablesEqual($xml_dataset->getTable('tasks'),               $this->getConnection()->createDataSet()->getTable('tasks'));
        $this->assertTablesEqual($xml_dataset->getTable('user_tasks'),          $this->getConnection()->createDataSet()->getTable('user_tasks'));
        $this->assertTablesEqual($xml_dataset->getTable('task_dependencies'),   $this->getConnection()->createDataSet()->getTable('task_dependencies'));
        $this->assertTablesEqual($xml_dataset->getTable('files'),               $this->getConnection()->createDataSet()->getTable('files'));
        $this->assertTablesEqual($xml_dataset->getTable('events'),              $this->getConnection()->createDataSet()->getTable('events'));
    }

    /**
     * Tests importing tasks from one project to another, when tasks are
     * invalid and causes failure. So the project should be removed and
     * tasks cleaned up.
     */
    public function testImportTasksFail()
    {
        $this->obj->load(2);
        $response = $this->obj->importTasks(1);

        $this->assertEquals(2, count($response));
        $this->assertEquals('Task: BadDep_CircularDep', $response[0]);
        $this->assertEquals('Task: (5)', $response[1]);

        $xml_file_dataset = $this->createXMLDataSet($this->getDataSetPath().'projectsTestImportTaskFail.xml');
        $xml_file_filtered_dataset = new PHPUnit_Extensions_Database_DataSet_DataSetFilter($xml_file_dataset, array('tasks' => array('task_created', 'task_updated')));
        $xml_db_dataset = $this->getConnection()->createDataSet();
        $xml_db_filtered_dataset = new PHPUnit_Extensions_Database_DataSet_DataSetFilter($xml_db_dataset, array('tasks' => array('task_created', 'task_updated')));
        $this->assertTablesEqual($xml_file_filtered_dataset->getTable('tasks'), $xml_db_filtered_dataset->getTable('tasks'));

        $xml_dataset = $this->createXMLDataSet($this->getDataSetPath().'projectsTestImportTaskFail.xml');
        $this->assertTablesEqual($xml_dataset->getTable('user_tasks'), $this->getConnection()->createDataSet()->getTable('user_tasks'));
        $this->assertTablesEqual($xml_dataset->getTable('task_dependencies'), $this->getConnection()->createDataSet()->getTable('task_dependencies'));
    }

    /**
     * Tests importing tasks from one project to another
     */
    public function testImportTasks()
    {
        $this->obj->load(4);
        $response = $this->obj->importTasks(3);

        $this->assertEquals(array(), $response);

        $xml_file_dataset = $this->createXMLDataSet($this->getDataSetPath().'projectsTestImportTasks.xml');
        $xml_file_filtered_dataset = new PHPUnit_Extensions_Database_DataSet_DataSetFilter($xml_file_dataset, array('tasks' => array('task_created', 'task_updated')));
        $xml_db_dataset = $this->getConnection()->createDataSet();
        $xml_db_filtered_dataset = new PHPUnit_Extensions_Database_DataSet_DataSetFilter($xml_db_dataset, array('tasks' => array('task_created', 'task_updated')));
        $this->assertTablesEqual($xml_file_filtered_dataset->getTable('tasks'), $xml_db_filtered_dataset->getTable('tasks'));

        $now_secs = time();
        $min_time = $now_secs - 10;

        /**
         * Get created dates to test against
         */
        $q = new w2p_Database_Query;
        $q->addTable('tasks');
        $q->addQuery('task_created');
        $q->addWhere('task_project = 4');
        $results = $q->loadColumn();

        foreach($results as $created) {
            $this->assertGreaterThanOrEqual($min_time, strtotime($created));
            $this->assertLessThanOrEqual($now_secs, strtotime($created));
        }

        /**
         * Get updated dates to test against
         */
        $q = new w2p_Database_Query;
        $q->addTable('tasks');
        $q->addQuery('task_updated');
        $q->addWhere('task_project = 4');
        $results = $q->loadColumn();

        foreach($results as $updated) {
            $this->assertGreaterThanOrEqual($min_time, strtotime($updated));
            $this->assertLessThanOrEqual($now_secs, strtotime($updated));
        }

        $xml_dataset = $this->createXMLDataSet($this->getDataSetPath().'projectsTestImportTasks.xml');
        $this->assertTablesEqual($xml_dataset->getTable('user_tasks'), $this->getConnection()->createDataSet()->getTable('user_tasks'));
        $this->assertTablesEqual($xml_dataset->getTable('task_dependencies'), $this->getConnection()->createDataSet()->getTable('task_dependencies'));

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
        $extra = array('where' => 'project_active = 1');
        $allowed_records = $this->obj->getAllowedRecords(1, 'projects.project_id,project_name', null, null, $extra);

        $this->assertEquals(1, count($allowed_records));
        $this->assertEquals('Test Project', $allowed_records[1]);
    }

    /**
     * Tests the custom getAllowedSQL function
     *
     */
    public function testGetAllowedSQL()
    {
    	$this->markTestSkipped('Not sure how to test this, everything I have tried has not results.');
    }

    /**
     * Tests the custom setAllowedSQL function
     *
     */
    public function testSetAllowedSQL()
    {
    	$this->markTestSkipped('Not sure hot to test this.');
    }

    /**
     * Tests the custom getDeniedRecords function
     */
    public function testGetDeniedRecords()
    {
    	$this->markTestSkipped('Not sure how to test this, everything I have tried has not results.');
    }

    /**
     * Tests getting a list of allowed project by user
     *
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

    	$row = db_fetch_assoc($project_in_rows);
    	$this->assertEquals(2,                     $row[0]);
      	$this->assertEquals(2,                     $row['project_id']);
      	$this->assertEquals(1,                     $row[1]);
      	$this->assertEquals(1,                     $row['project_status']);
      	$this->assertEquals('Test Project 2',      $row[2]);
      	$this->assertEquals('Test Project 2',      $row['project_name']);
      	$this->assertEquals('This is a project 2', $row[3]);
      	$this->assertEquals('This is a project 2', $row['project_description']);
      	$this->assertEquals('TP2',                 $row[4]);
      	$this->assertEquals('TP2',                 $row['project_short_name']);

      	$project_in_rows = $this->obj->getAllowedProjectsInRows(2);

      	$this->assertEquals(0, db_num_rows($project_in_rows));
    }

    /**
     * Tests getting the most critical tasks with project loaded
     */
    public function testGetCriticalTasksNoArgs()
    {
        $this->obj->load(1);

        $critical_tasks = $this->obj->getCriticalTasks();

        $this->assertEquals(1,                      count($critical_tasks));
        $this->assertEquals(1,                      $critical_tasks[0]['task_id']);
        $this->assertEquals('Task',                 $critical_tasks[0]['task_name']);
        $this->assertEquals(0,                      $critical_tasks[0]['task_parent']);
        $this->assertEquals(0,                      $critical_tasks[0]['task_milestone']);
        $this->assertEquals(1,                      $critical_tasks[0]['task_project']);
        $this->assertEquals(1,                      $critical_tasks[0]['task_owner']);
        $this->assertEquals('2009-07-05 00:00:00',  $critical_tasks[0]['task_start_date']);
        $this->assertEquals(2,                      $critical_tasks[0]['task_duration']);
        $this->assertEquals(24,                     $critical_tasks[0]['task_duration_type']);
        $this->assertEquals(0,                      $critical_tasks[0]['task_hours_worked']);
        $this->assertEquals('2009-07-15 00:00:00',  $critical_tasks[0]['task_end_date']);
        $this->assertEquals(0,                      $critical_tasks[0]['task_status']);
        $this->assertEquals(0,                      $critical_tasks[0]['task_priority']);
        $this->assertEquals(0,                      $critical_tasks[0]['task_percent_complete']);
        $this->assertEquals('This is task 1',       $critical_tasks[0]['task_description']);
        $this->assertEquals('0.00',                 $critical_tasks[0]['task_target_budget']);
        $this->assertEquals('',                     $critical_tasks[0]['task_related_url']);
        $this->assertEquals(1,                      $critical_tasks[0]['task_creator']);
        $this->assertEquals(1,                      $critical_tasks[0]['task_order']);
        $this->assertEquals(1,                      $critical_tasks[0]['task_client_publish']);
        $this->assertEquals(0,                      $critical_tasks[0]['task_dynamic']);
        $this->assertEquals(1,                      $critical_tasks[0]['task_access']);
        $this->assertEquals(1,                      $critical_tasks[0]['task_notify']);
        $this->assertEquals('',                     $critical_tasks[0]['task_departments']);
        $this->assertEquals('',                     $critical_tasks[0]['task_contacts']);
        $this->assertEquals('',                     $critical_tasks[0]['task_custom']);
        $this->assertEquals(1,                      $critical_tasks[0]['task_type']);
        $this->assertEquals(1,                      $critical_tasks[0]['task_updator']);
        $this->assertEquals('2009-07-05 15:43:00',  $critical_tasks[0]['task_created']);
        $this->assertEquals('2009-07-05 15:43:00',  $critical_tasks[0]['task_updated']);
        $this->assertEquals(0,                      $critical_tasks[0]['task_dep_reset_dates']);
    }

    /**
     * Tests getting critical tasks with no project loaded and
     * project id passed as argument
     */
    public function testGetCriticalTasksProjectID()
    {
        $critical_tasks = $this->obj->getCriticalTasks(1);

        $this->assertEquals(1,                      count($critical_tasks));
        $this->assertEquals(1,                      $critical_tasks[0]['task_id']);
        $this->assertEquals('Task',                 $critical_tasks[0]['task_name']);
        $this->assertEquals(0,                      $critical_tasks[0]['task_parent']);
        $this->assertEquals(0,                      $critical_tasks[0]['task_milestone']);
        $this->assertEquals(1,                      $critical_tasks[0]['task_project']);
        $this->assertEquals(1,                      $critical_tasks[0]['task_owner']);
        $this->assertEquals('2009-07-05 00:00:00',  $critical_tasks[0]['task_start_date']);
        $this->assertEquals(2,                      $critical_tasks[0]['task_duration']);
        $this->assertEquals(24,                     $critical_tasks[0]['task_duration_type']);
        $this->assertEquals(0,                      $critical_tasks[0]['task_hours_worked']);
        $this->assertEquals('2009-07-15 00:00:00',  $critical_tasks[0]['task_end_date']);
        $this->assertEquals(0,                      $critical_tasks[0]['task_status']);
        $this->assertEquals(0,                      $critical_tasks[0]['task_priority']);
        $this->assertEquals(0,                      $critical_tasks[0]['task_percent_complete']);
        $this->assertEquals('This is task 1',       $critical_tasks[0]['task_description']);
        $this->assertEquals('0.00',                 $critical_tasks[0]['task_target_budget']);
        $this->assertEquals('',                     $critical_tasks[0]['task_related_url']);
        $this->assertEquals(1,                      $critical_tasks[0]['task_creator']);
        $this->assertEquals(1,                      $critical_tasks[0]['task_order']);
        $this->assertEquals(1,                      $critical_tasks[0]['task_client_publish']);
        $this->assertEquals(0,                      $critical_tasks[0]['task_dynamic']);
        $this->assertEquals(1,                      $critical_tasks[0]['task_access']);
        $this->assertEquals(1,                      $critical_tasks[0]['task_notify']);
        $this->assertEquals('',                     $critical_tasks[0]['task_departments']);
        $this->assertEquals('',                     $critical_tasks[0]['task_contacts']);
        $this->assertEquals('',                     $critical_tasks[0]['task_custom']);
        $this->assertEquals(1,                      $critical_tasks[0]['task_type']);
        $this->assertEquals(1,                      $critical_tasks[0]['task_updator']);
        $this->assertEquals('2009-07-05 15:43:00',  $critical_tasks[0]['task_created']);
        $this->assertEquals('2009-07-05 15:43:00',  $critical_tasks[0]['task_updated']);
        $this->assertEquals(0,                      $critical_tasks[0]['task_dep_reset_dates']);
    }

    /**
     * Tests getting critical tasks with no project loaded and
     * project id and limit passed as arguments
     */
    public function testGetCriticalTasksProjectIDAndLimit()
    {
        $critical_tasks = $this->obj->getCriticalTasks(1,2);

        $this->assertEquals(2,                      count($critical_tasks));
        $this->assertEquals(1,                      $critical_tasks[0]['task_id']);
        $this->assertEquals('Task',                 $critical_tasks[0]['task_name']);
        $this->assertEquals(0,                      $critical_tasks[0]['task_parent']);
        $this->assertEquals(0,                      $critical_tasks[0]['task_milestone']);
        $this->assertEquals(1,                      $critical_tasks[0]['task_project']);
        $this->assertEquals(1,                      $critical_tasks[0]['task_owner']);
        $this->assertEquals('2009-07-05 00:00:00',  $critical_tasks[0]['task_start_date']);
        $this->assertEquals(2,                      $critical_tasks[0]['task_duration']);
        $this->assertEquals(24,                     $critical_tasks[0]['task_duration_type']);
        $this->assertEquals(0,                      $critical_tasks[0]['task_hours_worked']);
        $this->assertEquals('2009-07-15 00:00:00',  $critical_tasks[0]['task_end_date']);
        $this->assertEquals(0,                      $critical_tasks[0]['task_status']);
        $this->assertEquals(0,                      $critical_tasks[0]['task_priority']);
        $this->assertEquals(0,                      $critical_tasks[0]['task_percent_complete']);
        $this->assertEquals('This is task 1',       $critical_tasks[0]['task_description']);
        $this->assertEquals('0.00',                 $critical_tasks[0]['task_target_budget']);
        $this->assertEquals('',                     $critical_tasks[0]['task_related_url']);
        $this->assertEquals(1,                      $critical_tasks[0]['task_creator']);
        $this->assertEquals(1,                      $critical_tasks[0]['task_order']);
        $this->assertEquals(1,                      $critical_tasks[0]['task_client_publish']);
        $this->assertEquals(0,                      $critical_tasks[0]['task_dynamic']);
        $this->assertEquals(1,                      $critical_tasks[0]['task_access']);
        $this->assertEquals(1,                      $critical_tasks[0]['task_notify']);
        $this->assertEquals('',                     $critical_tasks[0]['task_departments']);
        $this->assertEquals('',                     $critical_tasks[0]['task_contacts']);
        $this->assertEquals('',                     $critical_tasks[0]['task_custom']);
        $this->assertEquals(1,                      $critical_tasks[0]['task_type']);
        $this->assertEquals(1,                      $critical_tasks[0]['task_updator']);
        $this->assertEquals('2009-07-05 15:43:00',  $critical_tasks[0]['task_created']);
        $this->assertEquals('2009-07-05 15:43:00',  $critical_tasks[0]['task_updated']);
        $this->assertEquals(0,                      $critical_tasks[0]['task_dep_reset_dates']);
        $this->assertEquals(2,                      $critical_tasks[1]['task_id']);
        $this->assertEquals('Task 2',               $critical_tasks[1]['task_name']);
        $this->assertEquals(0,                      $critical_tasks[1]['task_parent']);
        $this->assertEquals(0,                      $critical_tasks[1]['task_milestone']);
        $this->assertEquals(1,                      $critical_tasks[1]['task_project']);
        $this->assertEquals(1,                      $critical_tasks[1]['task_owner']);
        $this->assertEquals('2009-07-06 00:00:00',  $critical_tasks[1]['task_start_date']);
        $this->assertEquals(3,                      $critical_tasks[1]['task_duration']);
        $this->assertEquals(1,                      $critical_tasks[1]['task_duration_type']);
        $this->assertEquals(0,                      $critical_tasks[1]['task_hours_worked']);
        $this->assertEquals('2009-07-15 00:00:00',  $critical_tasks[1]['task_end_date']);
        $this->assertEquals(-1,                     $critical_tasks[1]['task_status']);
        $this->assertEquals(0,                      $critical_tasks[1]['task_priority']);
        $this->assertEquals(100,                    $critical_tasks[1]['task_percent_complete']);
        $this->assertEquals('This is task 2',       $critical_tasks[1]['task_description']);
        $this->assertEquals('0.00',                 $critical_tasks[1]['task_target_budget']);
        $this->assertEquals('',                     $critical_tasks[1]['task_related_url']);
        $this->assertEquals(1,                      $critical_tasks[1]['task_creator']);
        $this->assertEquals(2,                      $critical_tasks[1]['task_order']);
        $this->assertEquals(1,                      $critical_tasks[1]['task_client_publish']);
        $this->assertEquals(0,                      $critical_tasks[1]['task_dynamic']);
        $this->assertEquals(1,                      $critical_tasks[1]['task_access']);
        $this->assertEquals(1,                      $critical_tasks[1]['task_notify']);
        $this->assertEquals('',                     $critical_tasks[1]['task_departments']);
        $this->assertEquals('',                     $critical_tasks[1]['task_contacts']);
        $this->assertEquals('',                     $critical_tasks[1]['task_custom']);
        $this->assertEquals(1,                      $critical_tasks[1]['task_type']);
        $this->assertEquals(1,                      $critical_tasks[1]['task_updator']);
        $this->assertEquals('2009-07-08 15:43:00',  $critical_tasks[1]['task_created']);
        $this->assertEquals('2009-07-08 15:43:00',  $critical_tasks[1]['task_updated']);
        $this->assertEquals(0,                      $critical_tasks[1]['task_dep_reset_dates']);
    }

    /**
     * Testing further functionality of store, specifically the contacts and
     * departments saving. The basic functionality is covered in the
     * create and update tests.
     */
    public function testStore()
    {
		global $AppUI;

		$this->obj->load(1);

		$this->post_data['dosql'] = 'do_project_aed';
		$this->post_data['project_id'] = 1;
		$this->post_data['project_creator'] = 1;
		$this->post_data['project_contacts'] = '';
		$this->post_data['project_name'] = 'Updated Project';
		$this->post_data['project_parent'] = '';
		$this->post_data['project_owner'] = 1;
		$this->post_data['project_company'] = 1;
		$this->post_data['project_location'] = 'Somewhere Updated';
		$this->post_data['project_start_date'] = '20090728';
		$this->post_data['project_end_data'] = '20090828';
		$this->post_data['project_target_budget'] = 15;
		$this->post_data['project_actual_budget'] = 15;
		$this->post_data['project_scheduled_hours'] = 0;
		$this->post_data['project_worked_hours'] = 0;
		$this->post_data['project_task_count'] = 0;
		$this->post_data['project_url'] = 'project-update.example.org';
		$this->post_data['project_demo_url'] = 'project-updatedemo.example.org';
		$this->post_data['project_priority'] = '1';
		$this->post_data['project_short_name'] = 'uproject';
		$this->post_data['project_color_identifier'] = 'CCCEEE';
		$this->post_data['project_type'] = 1;
		$this->post_data['project_status'] = 1;
		$this->post_data['project_description'] = 'This is an updated project.';
		$this->post_data['email_project_owner'] = 1;
		$this->post_data['email_project_contacts'] = 0;
		$this->post_data['project_departments'] = array(1,2);
		$this->post_data['project_contacts'] = array(3,4);

        $this->obj->bind($this->post_data);
        $results = $this->obj->store($AppUI);

        $this->assertTrue($results);

        $xml_dataset = $this->createXMLDataSet($this->getDataSetPath().'projectsTestStore.xml');
        $this->assertTablesEqual($xml_dataset->getTable('project_departments'), $this->getConnection()->createDataSet()->getTable('project_departments'));
        $this->assertTablesEqual($xml_dataset->getTable('project_contacts'), $this->getConnection()->createDataSet()->getTable('project_contacts'));
    }

    /**
     * Test that owner is notified on change of project.
     */
    public function testNotifyOwner()
    {
        $this->markTestSkipped('Not sure how to test emails being sent.');
    }

    /**
     * Test that contacs are notified on change of project.
     */
    public function testNotifyContacts()
    {
        $this->markTestSkipped('Not sure how to test emails being sent.');
    }

    /**
     * Tests getting allowed projects that are active.
     */
    public function testGetAllowedProjectsActiveOnly()
    {
        $allowed_projects = $this->obj->getAllowedProjects(1);

        $this->assertEquals(1,                      count($allowed_projects));
        $this->assertEquals(1,                      $allowed_projects[1]['project_id']);
        $this->assertEquals('FFFFFF',               $allowed_projects[1]['project_color_identifier']);
        $this->assertEquals('Test Project',         $allowed_projects[1]['project_name']);
        $this->assertEquals('2009-07-05 00:00:00',  $allowed_projects[1]['project_start_date']);
        $this->assertEquals('2009-07-15 23:59:59',  $allowed_projects[1]['project_end_date']);
        $this->assertEquals(1,                      $allowed_projects[1][0]);
        $this->assertEquals('FFFFFF',               $allowed_projects[1][1]);
        $this->assertEquals('Test Project',         $allowed_projects[1][2]);
        $this->assertEquals('2009-07-05 00:00:00',  $allowed_projects[1][3]);
        $this->assertEquals('2009-07-15 23:59:59',  $allowed_projects[1][4]);
    }

    /**
     * Tests getting allowed projects that are active or inactive.
     */
    public function testGetAllowedProjectsAll()
    {
        $allowed_projects = $this->obj->getAllowedProjects(1, false);

        $this->assertEquals(4,                      count($allowed_projects));
        $this->assertEquals(1,                      $allowed_projects[1]['project_id']);
        $this->assertEquals('FFFFFF',               $allowed_projects[1]['project_color_identifier']);
        $this->assertEquals('Test Project',         $allowed_projects[1]['project_name']);
        $this->assertEquals('2009-07-05 00:00:00',  $allowed_projects[1]['project_start_date']);
        $this->assertEquals('2009-07-15 23:59:59',  $allowed_projects[1]['project_end_date']);
        $this->assertEquals(1,                      $allowed_projects[1][0]);
        $this->assertEquals('FFFFFF',               $allowed_projects[1][1]);
        $this->assertEquals('Test Project',         $allowed_projects[1][2]);
        $this->assertEquals('2009-07-05 00:00:00',  $allowed_projects[1][3]);
        $this->assertEquals('2009-07-15 23:59:59',  $allowed_projects[1][4]);
        $this->assertEquals(2,                      $allowed_projects[2]['project_id']);
        $this->assertEquals('EEEEEE',               $allowed_projects[2]['project_color_identifier']);
        $this->assertEquals('Test Project 2',       $allowed_projects[2]['project_name']);
        $this->assertEquals('2009-07-08 00:00:00',  $allowed_projects[2]['project_start_date']);
        $this->assertEquals('2009-07-18 23:59:59',  $allowed_projects[2]['project_end_date']);
        $this->assertEquals(2,                      $allowed_projects[2][0]);
        $this->assertEquals('EEEEEE',               $allowed_projects[2][1]);
        $this->assertEquals('Test Project 2',       $allowed_projects[2][2]);
        $this->assertEquals('2009-07-08 00:00:00',  $allowed_projects[2][3]);
        $this->assertEquals('2009-07-18 23:59:59',  $allowed_projects[2][4]);
        $this->assertEquals(3,                      $allowed_projects[3]['project_id']);
        $this->assertEquals('EEEEEE',               $allowed_projects[3]['project_color_identifier']);
        $this->assertEquals('Test Project 3',       $allowed_projects[3]['project_name']);
        $this->assertEquals('2009-07-08 00:00:00',  $allowed_projects[3]['project_start_date']);
        $this->assertEquals('2009-07-18 23:59:59',  $allowed_projects[3]['project_end_date']);
        $this->assertEquals(3,                      $allowed_projects[3][0]);
        $this->assertEquals('EEEEEE',               $allowed_projects[3][1]);
        $this->assertEquals('Test Project 3',       $allowed_projects[3][2]);
        $this->assertEquals('2009-07-08 00:00:00',  $allowed_projects[3][3]);
        $this->assertEquals('2009-07-18 23:59:59',  $allowed_projects[3][4]);
        $this->assertEquals(4,                      $allowed_projects[4]['project_id']);
        $this->assertEquals('EEEEEE',               $allowed_projects[4]['project_color_identifier']);
        $this->assertEquals('Test Project 4',       $allowed_projects[4]['project_name']);
        $this->assertEquals('2009-07-08 00:00:00',  $allowed_projects[4]['project_start_date']);
        $this->assertEquals('2009-07-18 23:59:59',  $allowed_projects[4]['project_end_date']);
        $this->assertEquals(4,                      $allowed_projects[4][0]);
        $this->assertEquals('EEEEEE',               $allowed_projects[4][1]);
        $this->assertEquals('Test Project 4',       $allowed_projects[4][2]);
        $this->assertEquals('2009-07-08 00:00:00',  $allowed_projects[4][3]);
        $this->assertEquals('2009-07-18 23:59:59',  $allowed_projects[4][4]);
    }

    /**
     * Tests finding contacts of project that does have contact
     */
    public function testGetContacts()
    {
        global $AppUI;

        $contacts = CProject::getContacts($AppUI, 1);

        $this->assertEquals(1,                      count($contacts));
        $this->assertEquals(1,                      $contacts[1]['contact_id']);
        $this->assertEquals('Admin',                $contacts[1]['contact_first_name']);
        $this->assertEquals('Person',               $contacts[1]['contact_last_name']);
        $this->assertEquals('',                     $contacts[1]['contact_order_by']);
        $this->assertEquals('',                     $contacts[1]['dept_name']);
        $this->assertEquals(1,                      $contacts[1][0]);
        $this->assertEquals('Admin',                $contacts[1][1]);
        $this->assertEquals('Person',               $contacts[1][2]);
        $this->assertEquals('',                     $contacts[1][3]);
        $this->assertEquals('',                     $contacts[1][4]);
    }

    /**
     * Test finding of departments of project
     */
    public function testGetDepartments()
    {
        global $AppUI;

        $departments = CProject::getDepartments($AppUI, 1);

        $this->assertEquals(2,              count($departments));
        $this->assertEquals(1,              $departments[1]['dept_id']);
        $this->assertEquals('Department 1', $departments[1]['dept_name']);
        $this->assertEquals('',             $departments[1]['dept_phone']);
        $this->assertEquals(1,              $departments[1][0]);
        $this->assertEquals('Department 1', $departments[1][1]);
        $this->assertEquals('',             $departments[1][2]);
        $this->assertEquals(2,              $departments[2]['dept_id']);
        $this->assertEquals('Department 1', $departments[2]['dept_name']);
        $this->assertEquals('',             $departments[2]['dept_phone']);
        $this->assertEquals(2,              $departments[2][0]);
        $this->assertEquals('Department 1', $departments[2][1]);
        $this->assertEquals('',             $departments[2][2]);
    }

    /**
     * Tests finding of forums of project
     */
    public function testGetForums()
    {
        global $AppUI;

        $forums = CProject::getForums($AppUI, 1);

        $this->assertEquals(1,                  count($forums));
        $this->assertEquals(1,                  $forums[1]['forum_id']);
        $this->assertEquals(1,                  $forums[1]['forum_project']);
        $this->assertEquals('This is a forum.', $forums[1]['forum_description']);
        $this->assertEquals(1,                  $forums[1]['forum_owner']);
        $this->assertEquals('Test Forum',       $forums[1]['forum_name']);
        $this->assertEquals(1,                  $forums[1]['forum_message_count']);
        $this->assertEquals('04-Aug-2009 17:03',$forums[1]['forum_last_date']);
        $this->assertEquals('Test Project',     $forums[1]['project_name']);
        $this->assertEquals('FFFFFF',           $forums[1]['project_color_identifier']);
        $this->assertEquals(1,                  $forums[1]['project_id']);
        $this->assertEquals(1,                  $forums[1][0]);
        $this->assertEquals(1,                  $forums[1][1]);
        $this->assertEquals('This is a forum.', $forums[1][2]);
        $this->assertEquals(1,                  $forums[1][3]);
        $this->assertEquals('Test Forum',       $forums[1][4]);
        $this->assertEquals(1,                  $forums[1][5]);
        $this->assertEquals('04-Aug-2009 17:03',$forums[1][6]);
        $this->assertEquals('Test Project',     $forums[1][7]);
        $this->assertEquals('FFFFFF',           $forums[1][8]);
        $this->assertEquals(1,                  $forums[1][9]);
    }

    /**
     * Tests finding company of project
     */
    public function testGetCompany()
    {
        $company = CProject::getCompany(1);

        $this->assertEquals(1, $company);
    }

    /**
     * Tests getting billing codes with all set to false, so any
     * billing codes that match this company, or have no company assigned
     * and billingcode_status = 1
     */
    public function testGetBillingCodes()
    {
        $billing_codes = CProject::getBillingCodes(1);

        $this->assertEquals(1,          count($billing_codes));
        $this->assertEquals('Cheap',    $billing_codes[1]);
    }

    /**
     * Tests getting billing codes with all set to true, so any billing
     * codes with this company id or no company assigned.
     */
    public function testGetBillingCodesAll()
    {
        $billing_codes = CProject::getBillingCodes(1, true);

        $this->assertEquals(3,              count($billing_codes));
        $this->assertEquals('Cheap',        $billing_codes[1]);
        $this->assertEquals('Medium',       $billing_codes[2]);
        $this->assertEquals('Expensive',    $billing_codes[3]);
    }

    /**
     * Tests getting a list of project owners.
     */
    public function testGetOwners()
    {
        $owners = CProject::getOwners();

        $this->assertEquals(1,              count($owners));
        $this->assertEquals('Admin Person', $owners[1]);
    }

    /**
     * Tests updating a projects status
     */
    public function testUpdateStatus()
    {
        global $AppUI;

        CProject::updateStatus($AppUI, 1, 2);
        $xml_dataset = $this->createXMLDataSet($this->getDataSetPath().'projectsTestUpdateStatus.xml');
        $this->assertTablesEqual($xml_dataset->getTable('projects'), $this->getConnection()->createDataSet()->getTable('projects'));
    }

    /**
     * Tests finding if project id passed has children
     */
    public function testHasChildProjectsWithArg()
    {
        $has_children = $this->obj->hasChildProjects(1);

        $this->assertEquals(3, $has_children);
    }

    /**
     * Tests finding if project has children if project is loaded and no argument passed
     */
    public function testHasChildProjects()
    {
        $this->obj->load(1);

        $has_children = $this->obj->hasChildProjects();

        $this->assertEquals(3, $has_children);
    }

    /**
     * Tests finding if project has children if no project loaded and no argument passed
     */
    public function testHasChildProjectNoProjectID()
    {
        $has_children = $this->obj->hasChildProjects();

        $this->assertEquals(-1, $has_children);
    }

    /**
     * Tests finding if project has tasks associated with it.
     */
    public function testHasTasks()
    {
        $has_tasks_1 = $this->obj->hasTasks(1);
        $has_tasks_2 = $this->obj->hasTasks(2);

        $this->assertEquals(2, $has_tasks_1);
        $this->assertEquals(0, $has_tasks_2);
    }

    /**
     * Tests getting total hours worked
     */
    public function testGetWorkedHours()
    {
        $this->markTestSkipped('This test has been deprecated by calculating the total hours at tasklog update.');
    }

    /**
     * Tests getting total hours assigned to tasks within the project
     */
    public function testGetTotalHours()
    {
        $this->markTestSkipped('Being deprecated.');
    }

    /**
     * Tests getting total hours assigned to tasks within the project
     */
    public function testGetTotalProjectHours()
    {
        global $w2Pconfig;

        $this->obj->load(1);
        $total_hours_1 = $this->obj->getTotalProjectHours();

        $this->obj->load(2);
        $total_hours_2 = $this->obj->getTotalProjectHours();

        $this->assertEquals(19,     $total_hours_1);
        $this->assertEquals(0,      $total_hours_2);
    }

    /**
     * Tests getting task logs with no filters passed
     */
    public function testGetTaskLogsNoArgs()
    {
        global $AppUI;

        $task_logs = $this->obj->getTaskLogs($AppUI, 1);

        $this->assertEquals(4,                  count($task_logs));
        $this->assertEquals(21,                 count($task_logs[0]));
        $this->assertEquals(21,                 count($task_logs[1]));
        $this->assertEquals(21,                 count($task_logs[2]));
        $this->assertEquals(21,                 count($task_logs[3]));
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
        $this->assertEquals('Contact Number 1',$task_logs[3]['real_name']);
    }

    /**
     * Tests getting task logs with user id passed
     */
    public function testGetTaskLogsUserID()
    {
        global $AppUI;

        $task_logs = $this->obj->getTaskLogs($AppUI, 1, 2);

        $this->assertEquals(1,                  count($task_logs));
        $this->assertEquals(21,                 count($task_logs[0]));
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
        global $AppUI;

        $task_logs = $this->obj->getTaskLogs($AppUI, 1, 0, true);

        $this->assertEquals(2,                  count($task_logs));
        $this->assertEquals(21,                 count($task_logs[0]));
        $this->assertEquals(21,                 count($task_logs[1]));
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
        global $AppUI;

        $task_logs = $this->obj->getTaskLogs($AppUI, 1, 0, false, true);

        $this->assertEquals(2,                  count($task_logs));
        $this->assertEquals(21,                 count($task_logs[0]));
        $this->assertEquals(21,                 count($task_logs[1]));
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
        global $AppUI;

        $task_logs = $this->obj->getTaskLogs($AppUI, 1, 0, false, false, 2);

        $this->assertEquals(1,                  count($task_logs));
        $this->assertEquals(21,                 count($task_logs[0]));
        $this->assertEquals(4,                  $task_logs[0]['task_log_id']);
        $this->assertEquals(2,                  $task_logs[0]['task_log_task']);
        $this->assertEquals('Task Log 4',       $task_logs[0]['task_log_description']);
        $this->assertEquals('another_admin',    $task_logs[0]['user_username']);
        $this->assertEquals('Contact Number 1', $task_logs[0]['real_name']);
    }

    /**
     * Tests the projects_list_data function
     */
    public function testProjectsListData()
    {
        $this->markTestSkipped('Untestable? Fills a $buffer variable with html and does nothing with it!?');
    }

    /**
     * Tests the shownavbar_links_prj function
     */
    public function testShownavbarLinksPrj()
    {
        $this->markTestSkipped('Untestable? Echos out some html.');
    }

    /**
     * Tests getting projects from outside project class
     */
    public function testGetProjects()
    {
        $projects = getProjects();

        $this->assertEquals(4,                  count($projects));
        $this->assertEquals(1,                  $projects[1]['project_id']);
        $this->assertEquals('Test Project',     $projects[1]['project_name']);
        $this->assertEquals(1,                  $projects[1]['project_parent']);
        $this->assertEquals(1,                  $projects[1][0]);
        $this->assertEquals('Test Project',     $projects[1][1]);
        $this->assertEquals('',                 $projects[1][2]);
        $this->assertEquals(2,                  $projects[2]['project_id']);
        $this->assertEquals('Test Project 2',   $projects[2]['project_name']);
        $this->assertEquals(1,                  $projects[2]['project_parent']);
        $this->assertEquals(2,                  $projects[2][0]);
        $this->assertEquals('Test Project 2',   $projects[2][1]);
        $this->assertEquals(1,                  $projects[2][2]);
        $this->assertEquals(3,                  $projects[3]['project_id']);
        $this->assertEquals('Test Project 3',   $projects[3]['project_name']);
        $this->assertEquals(1,                  $projects[3]['project_parent']);
        $this->assertEquals(3,                  $projects[3][0]);
        $this->assertEquals('Test Project 3',   $projects[3][1]);
        $this->assertEquals(1,                  $projects[3][2]);
        $this->assertEquals(4,                  $projects[4]['project_id']);
        $this->assertEquals('Test Project 4',   $projects[4]['project_name']);
        $this->assertEquals(1,                  $projects[4]['project_parent']);
        $this->assertEquals(4,                  $projects[4][0]);
        $this->assertEquals('Test Project 4',   $projects[4][1]);
        $this->assertEquals(1,                  $projects[4][2]);
    }

    /**
     * Tests resetting project parents.
     */
    public function testResetProjectParents()
    {
        global $AppUI;
	    $st_projects = array(0 => '');
	    $q = new w2p_Database_Query();
	    $q->addTable('projects');
	    $q->addQuery('project_id, project_name, project_parent');
	    $q->addOrder('project_name');
	    $st_projects = $q->loadHashList('project_id');
	    reset_project_parents($st_projects);

	    $this->assertEquals(4,                  count($st_projects));
        $this->assertEquals(1,                  $st_projects[1]['project_id']);
        $this->assertEquals('Test Project',     $st_projects[1]['project_name']);
        $this->assertEquals(1,                  $st_projects[1]['project_parent']);
        $this->assertEquals(1,                  $st_projects[1][0]);
        $this->assertEquals('Test Project',     $st_projects[1][1]);
        $this->assertEquals('',                 $st_projects[1][2]);
        $this->assertEquals(2,                  $st_projects[2]['project_id']);
        $this->assertEquals('Test Project 2',   $st_projects[2]['project_name']);
        $this->assertEquals(1,                  $st_projects[2]['project_parent']);
        $this->assertEquals(2,                  $st_projects[2][0]);
        $this->assertEquals('Test Project 2',   $st_projects[2][1]);
        $this->assertEquals(1,                  $st_projects[2][2]);
        $this->assertEquals(3,                  $st_projects[3]['project_id']);
        $this->assertEquals('Test Project 3',   $st_projects[3]['project_name']);
        $this->assertEquals(1,                  $st_projects[3]['project_parent']);
        $this->assertEquals(3,                  $st_projects[3][0]);
        $this->assertEquals('Test Project 3',   $st_projects[3][1]);
        $this->assertEquals(1,                  $st_projects[3][2]);
        $this->assertEquals(4,                  $st_projects[4]['project_id']);
        $this->assertEquals('Test Project 4',   $st_projects[4]['project_name']);
        $this->assertEquals(1,                  $st_projects[4]['project_parent']);
        $this->assertEquals(4,                  $st_projects[4][0]);
        $this->assertEquals('Test Project 4',   $st_projects[4][1]);
        $this->assertEquals(1,                  $st_projects[4][2]);
    }

    /**
     * Tests show_st_project function
     */
    public function testShowStProject()
    {
        global $st_projects_arr;
        $st_projects_arr = array();

        $st_projects = array(0 => '');
	    $q = new w2p_Database_Query();
	    $q->addTable('projects');
	    $q->addJoin('companies', '', 'projects.project_company = company_id', 'inner');
	    $q->addJoin('project_departments', 'pd', 'pd.project_id = projects.project_id');
	    $q->addJoin('departments', 'dep', 'pd.department_id = dep.dept_id');
	    $q->addQuery('projects.project_id, project_name, project_parent');
		$q->addWhere('projects.project_id = 1');
		$st_projects = $q->loadList();

		show_st_project($st_projects[1]);

		$this->assertEquals(1, count($st_projects_arr));
		$this->assertEquals(2, count($st_projects_arr[0]));
		$this->assertEquals(1, $st_projects_arr[0][0]['project_id']);
		$this->assertEquals('Test Project', $st_projects_arr[0][0]['project_name']);
		$this->assertEquals(1, $st_projects_arr[0][0]['project_parent']);
		$this->assertEquals(0, $st_projects_arr[0][1]);
    }

    /**
     * Tests find_proj_child with no level passed
     */
    public function testFindProjChildNoLevel()
    {
        global $st_projects_arr;
        $st_projects_arr = array();

        $st_projects = array(0 => '');
	    $q = new w2p_Database_Query();
	    $q->addTable('projects');
	    $q->addJoin('companies', '', 'projects.project_company = company_id', 'inner');
	    $q->addJoin('project_departments', 'pd', 'pd.project_id = projects.project_id');
	    $q->addJoin('departments', 'dep', 'pd.department_id = dep.dept_id');
	    $q->addQuery('projects.project_id, project_name, project_parent');
		$st_projects = $q->loadList();

		find_proj_child($st_projects, 1);

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

        $st_projects = array(0 => '');
	    $q = new w2p_Database_Query();
	    $q->addTable('projects');
	    $q->addJoin('companies', '', 'projects.project_company = company_id', 'inner');
	    $q->addJoin('project_departments', 'pd', 'pd.project_id = projects.project_id');
	    $q->addJoin('departments', 'dep', 'pd.department_id = dep.dept_id');
	    $q->addQuery('projects.project_id, project_name, project_parent');
		$st_projects = $q->loadList();

		find_proj_child($st_projects, 1, 2);

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
        global $AppUI, $st_projects_arr;
        $st_projects_arr = array();

        getStructuredProjects();

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
        $this->assertEquals('Test Project 4',   $st_projects_arr[3][0]['project_name']);
        $this->assertEquals(1,                  $st_projects_arr[3][0]['project_parent']);
        $this->assertEquals(1,                  $st_projects_arr[3][1]);
    }

    /**
     * Test getting structured projects with a specific original project id
     */
    public function testGetStructuredProjectsOriginalProjectID()
    {
        global $AppUI, $st_projects_arr;
        $st_projects_arr = array();

        getStructuredProjects(1);

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
     * Tests getting structured projects when passing a project status
     */
    public function testGetStructuredProjectsProjectStatus()
    {
        global $AppUI, $st_projects_arr;
        $st_projects_arr = array();

        getStructuredProjects(0, 0);

        $this->assertEquals(1,              count($st_projects_arr));
        $this->assertEquals(3,              count($st_projects_arr[0][0]));
        $this->assertEquals(1,              $st_projects_arr[0][0]['project_id']);
        $this->assertEquals('Test Project', $st_projects_arr[0][0]['project_name']);
        $this->assertEquals(1,              $st_projects_arr[0][0]['project_parent']);
        $this->assertEquals(0,              $st_projects_arr[0][1]);
    }

    /**
     * Tests getting structured projects that are active
     */
    public function testGetStructedProjectsActiveOnly()
    {
        global $AppUI, $st_projects_arr;
        $st_projects_arr = array();

        getStructuredProjects(0, -1, true);

        $this->assertEquals(1,              count($st_projects_arr));
        $this->assertEquals(3,              count($st_projects_arr[0][0]));
        $this->assertEquals(1,              $st_projects_arr[0][0]['project_id']);
        $this->assertEquals('Test Project', $st_projects_arr[0][0]['project_name']);
        $this->assertEquals(1,              $st_projects_arr[0][0]['project_parent']);
        $this->assertEquals(0,              $st_projects_arr[0][1]);
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

    /**
     * Tests generating options for a department selection list.
     */
    public function testGetDepartmentSelectionListIDOnly()
    {
        global $AppUI, $departments_count;
        $departments_count = 0;

        $options = getDepartmentSelectionList(1);

        $this->assertEquals('<option value="1">Department 1</option>', $options);
    }

    /**
     * Tests generating options for a department selection list with some checked
     */
    public function testGetDepartmentSelectionListCheckedArray()
    {
        global $AppUI, $departments_count;
        $departments_count = 0;
        $checked = array(1);

        $options = getDepartmentSelectionList(1, $checked);

        $this->assertEquals('<option value="1" selected="selected">Department 1</option>', $options);
    }

    /**
     * Tests generating options for a department selection list with a dept parent passed
     */
    public function testGetDepartmentSelectionListDeptParent()
    {
        global $AppUI, $departments_count;
        $departments_count = 0;

        $options = getDepartmentSelectionList(1, array(), 1);

        $this->assertEquals('', $options);
    }

    /**
     * Tests generating options for a department selection list with
     * set spaces in front to the option
     */
    public function testGetDepartmentSelectionListSpaces()
    {
        global $AppUI, $departments_count;
        $departments_count = 0;

        $options = getDepartmentSelectionList(1, array(), 0, 1);

        $this->assertEquals('<option value="1">&nbsp;Department 1</option>', $options);

        $options = getDepartmentSelectionList(1, array(), 0, 5);

        $this->assertEquals('<option value="1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Department 1</option>', $options);
    }
}
