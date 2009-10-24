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
 * FilesTest Class.
 *
 * Class to test the files class
 * @author D. Keith Casey, Jr.<caseydk@users.sourceforge.net>
 * @package web2project
 * @subpackage unit_tests
 */
class Files_Test extends PHPUnit_Extensions_Database_TestCase
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
      return $this->createXMLDataSet($this->getDataSetPath().'filesSeed.xml');
    }
    protected function getDataSetPath()
    {
      return dirname(dirname(__FILE__)).'/db_files/';
    }

    /**
     * Tests the Attributes of a new Files object.
     */
    public function testNewFilesAttributes()
    {
      $file = new CFile();

      $this->assertType('CFile',                                  $file);
      $this->assertObjectHasAttribute('file_id',                  $file);
      $this->assertObjectHasAttribute('file_real_filename',       $file);
      $this->assertObjectHasAttribute('file_project',             $file);
      $this->assertObjectHasAttribute('file_task',                $file);
      $this->assertObjectHasAttribute('file_name',                $file);
      $this->assertObjectHasAttribute('file_parent',              $file);
      $this->assertObjectHasAttribute('file_description',         $file);
      $this->assertObjectHasAttribute('file_type',                $file);
      $this->assertObjectHasAttribute('file_owner',               $file);
      $this->assertObjectHasAttribute('file_date',                $file);
      $this->assertObjectHasAttribute('file_size',                $file);
      $this->assertObjectHasAttribute('file_version',             $file);
      $this->assertObjectHasAttribute('file_icon',                $file);
      $this->assertObjectHasAttribute('file_category',            $file);
      $this->assertObjectHasAttribute('file_checkout',            $file);
      $this->assertObjectHasAttribute('file_co_reason',           $file);
      $this->assertObjectHasAttribute('file_folder',              $file);
      $this->assertObjectHasAttribute('file_indexed',             $file);
      $this->assertObjectHasAttribute('_tbl_prefix',              $file);
      $this->assertObjectHasAttribute('_tbl',                     $file);
      $this->assertObjectHasAttribute('_tbl_key',                 $file);
      $this->assertObjectHasAttribute('_error',                   $file);
      $this->assertObjectHasAttribute('_query',                   $file);
    }

    /**
     * Tests the Attribute Values of a new File object.
     */
    public function testNewFilesAttributeValues()
    {
      $file = new CFile();

      $this->assertType('CFile', $file);
      $this->assertNull($file->file_id);
      $this->assertNull($file->file_real_filename);
      $this->assertNull($file->file_project);
      $this->assertNull($file->file_task);
      $this->assertNull($file->file_name);
      $this->assertNull($file->file_parent);
      $this->assertNull($file->file_description);
      $this->assertNull($file->file_type);
      $this->assertNull($file->file_owner);
      $this->assertNull($file->file_date);
      $this->assertNull($file->file_size);
      $this->assertNull($file->file_version);
      $this->assertNull($file->file_icon);
      $this->assertNull($file->file_category);
      $this->assertNull($file->file_checkout);
      $this->assertNull($file->file_co_reason);
      $this->assertNull($file->file_folder);
      $this->assertNull($file->file_indexed);
      $this->assertEquals('',         $file->_tbl_prefix);
      $this->assertEquals('files',    $file->_tbl);
      $this->assertEquals('file_id',  $file->_tbl_key);
      $this->assertEquals('',         $file->_errors);
      $this->assertType('DBQuery',    $file->_query);
    }

    /**
     * Tests that the proper error message is returned when no filename is
     * passed.
     */
    public function testCreateFileNoRealName()
    {
      global $AppUI;

      $file = new CFile();

      $post_data = array(
          'dosql' =>              'do_file_aed',
          'file_id' =>            0,
          'file_real_filename' => '',
          'file_project' =>       '0',
          'file_task' =>          '0',
          'file_name' =>          'thisIsTheFilename',
          'file_parent' =>        1,
          'file_description' =>   'File description',
          'file_type' =>          'jpeg/jpg',
          'file_owner' =>         '1',
          'file_date' =>          '20090728',
          'file_size' =>          '0',
          'file_version' =>       '',
          'file_icon' =>          '',
          'file_category' =>      0,
          'file_checkout' =>      0,
          'file_co_reason' =>     '',
          'file_folder' =>        '',
          'file_indexed' =>       '-1'
      );

      $file->bind($post_data);
      $errorArray = $file->store($AppUI);

      /**
       * Verify we got the proper error message
       */
      $this->assertArrayHasKey('file_real_filename', $errorArray);

      /**
       * Verify that project id was not set
       */
      $this->AssertEquals(0, $file->file_id);
    }

    /**
     * Tests that the proper error message is returned when no project is
     * passed.
     */
    public function testCreateFileNoProject()
    {
      global $AppUI;

      $file = new CFile();

      $post_data = array(
          'dosql' =>              'do_file_aed',
          'file_id' =>            0,
          'file_real_filename' => 'thisIsTheRealFilename',
          'file_project' =>       '',
          'file_task' =>          '0',
          'file_name' =>          'thisIsTheFilename',
          'file_parent' =>        1,
          'file_description' =>   'File description',
          'file_type' =>          'jpeg/jpg',
          'file_owner' =>         '1',
          'file_date' =>          '20090728',
          'file_size' =>          '0',
          'file_version' =>       '',
          'file_icon' =>          '',
          'file_category' =>      0,
          'file_checkout' =>      0,
          'file_co_reason' =>     '',
          'file_folder' =>        '',
          'file_indexed' =>       '-1'
      );

      $file->bind($post_data);
      $errorArray = $file->store($AppUI);

      /**
       * Verify we got the proper error message
       */
      $this->assertArrayHasKey('file_project', $errorArray);

      /**
       * Verify that project id was not set
       */
      $this->AssertEquals(0, $file->file_id);
    }

    /**
     * Tests that the proper error message is returned when no task is passed.
     */
    public function testCreateFileNoTask()
    {
      global $AppUI;

      $file = new CFile();

      $post_data = array(
          'dosql' =>              'do_file_aed',
          'file_id' =>            0,
          'file_real_filename' => 'thisIsTheRealFilename',
          'file_project' =>       '0',
          'file_task' =>          '',
          'file_name' =>          'thisIsTheFilename',
          'file_parent' =>        1,
          'file_description' =>   'File description',
          'file_type' =>          'jpeg/jpg',
          'file_owner' =>         '1',
          'file_date' =>          '20090728',
          'file_size' =>          '0',
          'file_version' =>       '',
          'file_icon' =>          '',
          'file_category' =>      0,
          'file_checkout' =>      0,
          'file_co_reason' =>     '',
          'file_folder' =>        '',
          'file_indexed' =>       '-1'
      );

      $file->bind($post_data);
      $errorArray = $file->store($AppUI);

      /**
       * Verify we got the proper error message
       */
      $this->assertArrayHasKey('file_task', $errorArray);

      /**
       * Verify that project id was not set
       */
      $this->AssertEquals(0, $file->file_id);
    }

    /**
     * Tests that the proper error message is returned when no filename is
     * passed.
     */
    public function testCreateFileNoName()
    {
      global $AppUI;

      $file = new CFile();

      $post_data = array(
          'dosql' =>              'do_file_aed',
          'file_id' =>            0,
          'file_real_filename' => 'thisIsTheRealFilename',
          'file_project' =>       '0',
          'file_task' =>          '0',
          'file_name' =>          '',
          'file_parent' =>        1,
          'file_description' =>   'File description',
          'file_type' =>          'jpeg/jpg',
          'file_owner' =>         '1',
          'file_date' =>          '20090728',
          'file_size' =>          '0',
          'file_version' =>       '',
          'file_icon' =>          '',
          'file_category' =>      0,
          'file_checkout' =>      0,
          'file_co_reason' =>     '',
          'file_folder' =>        '',
          'file_indexed' =>       '-1'
      );

      $file->bind($post_data);
      $errorArray = $file->store($AppUI);

      /**
       * Verify we got the proper error message
       */
      $this->assertArrayHasKey('file_name', $errorArray);

      /**
       * Verify that project id was not set
       */
      $this->AssertEquals(0, $file->file_id);
    }

    /**
     * Tests that the proper error message is returned when no parent is passed.
     */
    public function testCreateFileNoParent()
    {
      global $AppUI;

      $file = new CFile();

      $post_data = array(
          'dosql' =>              'do_file_aed',
          'file_id' =>            0,
          'file_real_filename' => 'thisIsTheRealFilename',
          'file_project' =>       '0',
          'file_task' =>          '0',
          'file_name' =>          'theIsTheFilename',
          'file_parent' =>        '',
          'file_description' =>   'File description',
          'file_type' =>          'jpeg/jpg',
          'file_owner' =>         '1',
          'file_date' =>          '20090728',
          'file_size' =>          '0',
          'file_version' =>       '',
          'file_icon' =>          '',
          'file_category' =>      0,
          'file_checkout' =>      0,
          'file_co_reason' =>     '',
          'file_folder' =>        '',
          'file_indexed' =>       '-1'
      );

      $file->bind($post_data);
      $errorArray = $file->store($AppUI);

      /**
       * Verify we got the proper error message
       */
      $this->assertArrayHasKey('file_parent', $errorArray);

      /**
       * Verify that project id was not set
       */
      $this->AssertEquals(0, $file->file_id);
    }

    /**
     * Tests that the proper error message is returned when no type is passed.
     */
    public function testCreateFileNoType()
    {
      global $AppUI;

      $file = new CFile();

      $post_data = array(
          'dosql' =>              'do_file_aed',
          'file_id' =>            0,
          'file_real_filename' => 'thisIsTheRealFilename',
          'file_project' =>       '0',
          'file_task' =>          '0',
          'file_name' =>          'thisIsTheFilename',
          'file_parent' =>        1,
          'file_description' =>   'File description',
          'file_type' =>          '',
          'file_owner' =>         '1',
          'file_date' =>          '20090728',
          'file_size' =>          '0',
          'file_version' =>       '',
          'file_icon' =>          '',
          'file_category' =>      0,
          'file_checkout' =>      0,
          'file_co_reason' =>     '',
          'file_folder' =>        '',
          'file_indexed' =>       '-1'
      );

      $file->bind($post_data);
      $errorArray = $file->store($AppUI);

      /**
       * Verify we got the proper error message
       */
      $this->assertArrayHasKey('file_type', $errorArray);

      /**
       * Verify that project id was not set
       */
      $this->AssertEquals(0, $file->file_id);
    }

    /**
     * Tests that the proper error message is returned when no filesize is
     * passed.
     */
    public function testCreateFileNoFilesize()
    {
      global $AppUI;

      $file = new CFile();

      $post_data = array(
          'dosql' =>              'do_file_aed',
          'file_id' =>            0,
          'file_real_filename' => 'thisIsTheRealFilename',
          'file_project' =>       '0',
          'file_task' =>          '0',
          'file_name' =>          'thisIsTheFilename',
          'file_parent' =>        1,
          'file_description' =>   'File description',
          'file_type' =>          'jpeg/jpg',
          'file_owner' =>         '1',
          'file_date' =>          '20090728',
          'file_size' =>          '',
          'file_version' =>       '',
          'file_icon' =>          '',
          'file_category' =>      0,
          'file_checkout' =>      0,
          'file_co_reason' =>     '',
          'file_folder' =>        '',
          'file_indexed' =>       '-1'
      );

      $file->bind($post_data);
      $errorArray = $file->store($AppUI);

      /**
       * Verify we got the proper error message
       */
      $this->assertArrayHasKey('file_size', $errorArray);

      /**
       * Verify that project id was not set
       */
      $this->AssertEquals(0, $file->file_id);
    }

    /**
     * Tests the proper creation of a project.
     */
    public function testCreateFile()
    {
      global $AppUI;

      $file = new CFile();

      $post_data = array(
          'dosql' =>              'do_file_aed',
          'file_id' =>            0,
          'file_real_filename' => 'thisIsTheRealFilename',
          'file_project' =>       '0',
          'file_task' =>          '0',
          'file_name' =>          'thisIsTheFilename',
          'file_parent' =>        1,
          'file_description' =>   'File description',
          'file_type' =>          'jpeg/jpg',
          'file_owner' =>         '1',
          'file_date' =>          '20090728',
          'file_size' =>          '0',
          'file_version' =>       '',
          'file_icon' =>          '',
          'file_category' =>      0,
          'file_checkout' =>      0,
          'file_co_reason' =>     '',
          'file_folder' =>        '',
          'file_indexed' =>       '-1'
      );

      $file->bind($post_data);
      $errorArray = $file->store($AppUI);

      $this->assertTrue($errorArray);
      $this->assertEquals(1,                          $file->file_id);
      $this->assertEquals('thisIsTheRealFilename',    $file->file_real_filename);
      $this->assertEquals(0,                          $file->file_project);
      $this->assertEquals(0,                          $file->file_task);
      $this->assertEquals('thisIsTheFilename',        $file->file_name);
      $this->assertEquals(1,                          $file->file_parent);
      $this->assertEquals('File description',         $file->file_description);
      $this->assertEquals('jpeg/jpg',                 $file->file_type);
      $this->assertEquals(1,                          $file->file_owner);
      $this->assertEquals('2009-07-28 23:59:59',      $file->file_date);
      $this->assertEquals(0,                          $file->file_size);
      $this->assertEquals('',                         $file->file_version);
      $this->assertEquals('obj/',                     $file->file_icon);
      $this->assertEquals(0,                          $file->file_category);
      $this->assertEquals(0,                          $file->file_checkout);
      $this->assertEquals('',                         $file->file_co_reason);
      $this->assertEquals('',                         $file->file_folder);
      $this->assertEquals(-1,                         $file->file_indexed);

      $xml_file_dataset = $this->createXMLDataSet($this->getDataSetPath().'testCreateFile.xml');
      $xml_file_filtered_dataset = new PHPUnit_Extensions_Database_DataSet_DataSetFilter($xml_file_dataset, array('files' => array('project_created', 'project_updated')));
      $xml_db_dataset = $this->getConnection()->createDataSet();
      $xml_db_filtered_dataset = new PHPUnit_Extensions_Database_DataSet_DataSetFilter($xml_db_dataset, array('files' => array('project_created', 'project_updated')));

      $this->assertTablesEqual($xml_file_filtered_dataset->getTable('files'), $xml_db_filtered_dataset->getTable('files'));
    }

    /**
     * Tests that the check function returns the proper error message when project_name is null.
     */
    public function testCheckNullName()
    {
        $file = new CProject();
$this->markTestIncomplete('This test has been deprecated by casting the project_id via intval().');
return;
        $post_data = array(
            'dosql' =>                      'do_project_aed',
            'project_id' =>                 0,
            'project_creator' =>            1,
            'project_contacts' =>           '',
            'project_name' =>               '',
            'project_parent' =>             '',
            'project_owner' =>              1,
            'project_company' =>            1,
            'project_location' =>           '',
            'project_start_date' =>         '20090628',
            'project_end_date' =>           '20090728',
            'project_target_budget' =>      '',
            'project_actual_budget' =>      '',
            'project_scheduled_hours' =>    0,
            'project_worked_hours' =>       0,
            'project_task_count' =>         0,
            'project_url' =>                '',
            'project_demo_url' =>           '',
            'project_priority' =>           '-1',
            'project_short_name' =>         'nproject',
            'project_color_identifier' =>   '',
            'project_type' =>               0,
            'project_status' =>             '',
            'project_description' =>        '',
            'email_project_owner' =>        1,
            'email_project_contacts' =>     1
        );

        $file->bind($post_data);
        $errorArray = $file->check();
        $this->assertArrayHasKey('project_name', $errorArray);
    }

    /**
     * Tests that the check function returns the nothing when data is correct.
     */
    public function testCheck()
    {
        $file = new CProject();
$this->markTestIncomplete('This test has been deprecated by casting the project_id via intval().');
return;
        $post_data = array(
            'dosql' =>                      'do_project_aed',
            'project_id' =>                 0,
            'project_creator' =>            1,
            'project_contacts' =>           '',
            'project_name' =>               'Test Name',
            'project_parent' =>             '',
            'project_owner' =>              1,
            'project_company' =>            1,
            'project_location' =>           '',
            'project_start_date' =>         '20090628',
            'project_end_date' =>           '20090728',
            'project_target_budget' =>      '',
            'project_actual_budget' =>      '',
            'project_scheduled_hours' =>    0,
            'project_worked_hours' =>       0,
            'project_task_count' =>         0,
            'project_url' =>                '',
            'project_demo_url' =>           '',
            'project_priority' =>           '-1',
            'project_short_name' =>         'nproject',
            'project_color_identifier' =>   'FFFFFF',
            'project_type' =>               0,
            'project_status' =>             1,
            'project_description' =>        '',
            'email_project_owner' =>        1,
            'email_project_contacts' =>     1
        );

        $file->bind($post_data);
        $errorArray = $file->check();
        $this->assertEquals(0, count($errorArray));
    }

    /**
     * Tests loading the Project object.
     */
    public function testLoad()
    {
$this->markTestIncomplete('This test has been deprecated by casting the project_id via intval().');
return;
      $file = new CProject();
      $file->load(1);

      $this->assertEquals(1,                                  $file->project_id);
      $this->assertEquals(1,                                  $file->project_company);
      $this->assertEquals(0,                                  $file->project_department);
      $this->assertEquals('Test Project',                     $file->project_name);
      $this->assertEquals('TP',                               $file->project_short_name);
      $this->assertEquals(1,                                  $file->project_owner);
      $this->assertEquals('http://project1.example.org',      $file->project_url);
      $this->assertEquals('http://project1-demo.example.org', $file->project_demo_url);
      $this->assertEquals('2009-07-05 00:00:00',              $file->project_start_date);
      $this->assertEquals('2009-07-15 23:59:59',              $file->project_end_date);
      $this->assertEquals('2009-08-15 00:00:00',              $file->project_actual_end_date);
      $this->assertEquals(0,                                  $file->project_status);
      $this->assertEquals('',                                 $file->project_percent_complete);
      $this->assertEquals('FFFFFF',                           $file->project_color_identifier);
      $this->assertEquals('This is a project',                $file->project_description);
      $this->assertEquals('15.00',                            $file->project_target_budget);
      $this->assertEquals('5.00',                             $file->project_actual_budget);
      $this->assertEquals(0,                                  $file->project_scheduled_hours);
      $this->assertEquals(0,                                  $file->project_worked_hours);
      $this->assertEquals(0,                                  $file->project_task_count);
      $this->assertEquals(1,                                  $file->project_creator);
      $this->assertEquals(1,                                  $file->project_active);
      $this->assertEquals(0,                                  $file->project_private);
      $this->assertEquals('',                                 $file->project_departments);
      $this->assertEquals('',                                 $file->project_contacts);
      $this->assertEquals(-1,                                 $file->project_priority);
      $this->assertEquals(0,                                  $file->project_type);
      $this->assertEquals(1,                                  $file->project_parent);
      $this->assertEquals(1,                                  $file->project_original_parent);
      $this->assertEquals('Somewhere',                        $file->project_location);
    }

    /**
     * Test loading the Project object.
     */
    public function testFullLoad()
    {
$this->markTestIncomplete('This test has been deprecated by casting the project_id via intval().');
return;
      $file = new CProject();
      $file->fullLoad(1);

      $this->assertEquals(1,                                  $file->project_id);
      $this->assertEquals(1,                                  $file->project_company);
      $this->assertEquals(0,                                  $file->project_department);
      $this->assertEquals('Test Project',                     $file->project_name);
      $this->assertEquals('TP',                               $file->project_short_name);
      $this->assertEquals(1,                                  $file->project_owner);
      $this->assertEquals('http://project1.example.org',      $file->project_url);
      $this->assertEquals('http://project1-demo.example.org', $file->project_demo_url);
      $this->assertEquals('2009-07-05 00:00:00',              $file->project_start_date);
      $this->assertEquals('2009-07-15 23:59:59',              $file->project_end_date);
      $this->assertEquals('2009-08-15 00:00:00',              $file->project_actual_end_date);
      $this->assertEquals(0,                                  $file->project_status);
      $this->assertEquals(15.789473684211,                    $file->project_percent_complete, '', 0.000000000001);
      $this->assertEquals('FFFFFF',                           $file->project_color_identifier);
      $this->assertEquals('This is a project',                $file->project_description);
      $this->assertEquals('15.00',                            $file->project_target_budget);
      $this->assertEquals('5.00',                             $file->project_actual_budget);
      $this->assertEquals(0,                                  $file->project_scheduled_hours);
      $this->assertEquals(0,                                  $file->project_worked_hours);
      $this->assertEquals(0,                                  $file->project_task_count);
      $this->assertEquals(1,                                  $file->project_creator);
      $this->assertEquals(1,                                  $file->project_active);
      $this->assertEquals(0,                                  $file->project_private);
      $this->assertEquals('',                                 $file->project_departments);
      $this->assertEquals('',                                 $file->project_contacts);
      $this->assertEquals(-1,                                 $file->project_priority);
      $this->assertEquals(0,                                  $file->project_type);
      $this->assertEquals(1,                                  $file->project_parent);
      $this->assertEquals(1,                                  $file->project_original_parent);
      $this->assertEquals('Somewhere',                        $file->project_location);
      $this->assertEquals('UnitTestCompany',                  $file->company_name);
      $this->assertEquals('Admin Person',                     $file->user_name);
    }

    /**
     * Tests the update of a project.
     */
    public function testUpdateProject()
    {
      global $AppUI;
$this->markTestIncomplete('This test has been deprecated by casting the project_id via intval().');
return;
      $file = new CProject();
      $file->load(1);

      $post_data = array(
            'dosql' =>                      'do_project_aed',
            'project_id' =>                 1,
            'project_creator' =>            1,
            'project_contacts' =>           '',
            'project_name' =>               'Updated Project',
            'project_parent' =>             '',
            'project_owner' =>              1,
            'project_company' =>            1,
            'project_location' =>           'Somewhere Updated',
            'project_start_date' =>         '20090728',
            'project_end_date' =>           '20090828',
            'project_scheduled_hours' =>    0,
            'project_worked_hours' =>       0,
            'project_task_count' =>         0,
            'project_target_budget' =>      15,
            'project_actual_budget' =>      15,
            'project_url' =>                'project-update.example.org',
            'project_demo_url' =>           'project-updatedemo.example.org',
            'project_priority' =>           '1',
            'project_short_name' =>         'uproject',
            'project_color_identifier' =>   'CCCEEE',
            'project_type' =>               1,
            'project_status' =>             1,
            'project_description' =>        'This is an updated project.',
            'email_project_owner' =>        1,
            'email_project_contacts' =>     0
        );

        $file->bind($post_data);
        $results = $file->store($AppUI);

        $this->assertTrue($results);
        $this->assertEquals(1,                                  $file->project_id);
        $this->assertEquals(1,                                  $file->project_company);
        $this->assertEquals(0,                                  $file->project_department);
        $this->assertEquals('Updated Project',                  $file->project_name);
        $this->assertEquals('uproject',                         $file->project_short_name);
        $this->assertEquals(1,                                  $file->project_owner);
        $this->assertEquals('project-update.example.org',       $file->project_url);
        $this->assertEquals('project-updatedemo.example.org',   $file->project_demo_url);
        $this->assertEquals('2009-07-28 00:00:00',              $file->project_start_date);
        $this->assertEquals('2009-08-28 23:59:59',              $file->project_end_date);
        $this->assertEquals('2009-08-15 00:00:00',              $file->project_actual_end_date);
        $this->assertEquals(1,                                  $file->project_status);
        $this->assertEquals('',                                 $file->project_percent_complete);
        $this->assertEquals('CCCEEE',                           $file->project_color_identifier);
        $this->assertEquals('This is an updated project.',      $file->project_description);
        $this->assertEquals(15,                                 $file->project_target_budget);
        $this->assertEquals(15,                                 $file->project_actual_budget);
        $this->assertEquals(0,                                  $file->project_scheduled_hours);
        $this->assertEquals(0,                                  $file->project_worked_hours);
        $this->assertEquals(0,                                  $file->project_task_count);
        $this->assertEquals(1,                                  $file->project_creator);
        $this->assertEquals(1,                                  $file->project_active);
        $this->assertEquals(0,                                  $file->project_private);
        $this->assertEquals('',                                 $file->project_departments);
        $this->assertEquals('',                                 $file->project_contacts);
        $this->assertEquals(1,                                  $file->project_priority);
        $this->assertEquals(1,                                  $file->project_type);
        $this->assertEquals(1,                                  $file->project_parent);
        $this->assertEquals(1,                                  $file->project_original_parent);
        $this->assertEquals('Somewhere Updated',                $file->project_location);

        $xml_file_dataset = $this->createXMLDataSet($this->getDataSetPath().'testUpdateProject.xml');
        $xml_file_filtered_dataset = new PHPUnit_Extensions_Database_DataSet_DataSetFilter($xml_file_dataset, array('projects' => array('project_updated')));
        $xml_db_dataset = $this->getConnection()->createDataSet();
        $xml_db_filtered_dataset = new PHPUnit_Extensions_Database_DataSet_DataSetFilter($xml_db_dataset, array('projects' => array('project_updated')));

        $this->assertTablesEqual($xml_file_filtered_dataset->getTable('projects'), $xml_db_filtered_dataset->getTable('projects'));

        /**
         * Get updated date to test against
         */
        $q = new DBQuery;
        $q->addTable('projects');
        $q->addQuery('project_updated');
        $q->addWhere('project_id = ' . $file->project_id);
        $file_updated = $q->loadResult();
        $file_updated =  strtotime($file_updated);

        $now_secs = time();
        $min_time = $now_secs - 10;

        $this->assertGreaterThanOrEqual($min_time, $file_updated);
        $this->assertLessThanOrEqual($now_secs, $file_updated);
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
$this->markTestIncomplete('This test has been deprecated by casting the project_id via intval().');
return;
        $file = new CProject();
        $file->load(1);
        $file->delete($AppUI);

        $xml_dataset = $this->createXMLDataSet($this->getDataSetPath().'testDeleteProject.xml');
        $this->assertTablesEqual($xml_dataset->getTable('projects'),            $this->getConnection()->createDataSet()->getTable('projects'));
        $this->assertTablesEqual($xml_dataset->getTable('project_contacts'),    $this->getConnection()->createDataSet()->getTable('project_contacts'));
        $this->assertTablesEqual($xml_dataset->getTable('tasks'),               $this->getConnection()->createDataSet()->getTable('tasks'));
        $this->assertTablesEqual($xml_dataset->getTable('user_tasks'),          $this->getConnection()->createDataSet()->getTable('user_tasks'));
        $this->assertTablesEqual($xml_dataset->getTable('task_dependencies'),   $this->getConnection()->createDataSet()->getTable('task_dependencies'));
        $this->assertTablesEqual($xml_dataset->getTable('files'),               $this->getConnection()->createDataSet()->getTable('files'));
        $this->assertTablesEqual($xml_dataset->getTable('events'),              $this->getConnection()->createDataSet()->getTable('events'));
    }

    /**
     * Tests importing tasks from one project to another
     */
    public function testImportTasks()
    {
$this->markTestIncomplete('This test has been deprecated by casting the project_id via intval().');
return;
        $file = new CProject();
        $file->load(2);
        $file->importTasks(1);

        $xml_file_dataset = $this->createXMLDataSet($this->getDataSetPath().'testImportTasks.xml');
        $xml_file_filtered_dataset = new PHPUnit_Extensions_Database_DataSet_DataSetFilter($xml_file_dataset, array('tasks' => array('task_created', 'task_updated')));
        $xml_db_dataset = $this->getConnection()->createDataSet();
        $xml_db_filtered_dataset = new PHPUnit_Extensions_Database_DataSet_DataSetFilter($xml_db_dataset, array('tasks' => array('task_created', 'task_updated')));
        $this->assertTablesEqual($xml_file_filtered_dataset->getTable('tasks'), $xml_db_filtered_dataset->getTable('tasks'));

        $now_secs = time();
        $min_time = $now_secs - 10;

        /**
         * Get created dates to test against
         */
        $q = new DBQuery;
        $q->addTable('tasks');
        $q->addQuery('task_created');
        $q->addWhere('task_project = 2');
        $results = $q->loadColumn();

        foreach($results as $created) {
            $this->assertGreaterThanOrEqual($min_time, strtotime($created));
            $this->assertLessThanOrEqual($now_secs, strtotime($created));
        }

        /**
         * Get updated dates to test against
         */
        $q = new DBQuery;
        $q->addTable('tasks');
        $q->addQuery('task_updated');
        $q->addWhere('task_project = 2');
        $results = $q->loadColumn();

        foreach($results as $updated) {
            $this->assertGreaterThanOrEqual($min_time, strtotime($updated));
            $this->assertLessThanOrEqual($now_secs, strtotime($updated));
        }

        $xml_dataset = $this->createXMLDataSet($this->getDataSetPath().'testImportTasks.xml');
        $this->assertTablesEqual($xml_dataset->getTable('user_tasks'), $this->getConnection()->createDataSet()->getTable('user_tasks'));
        $this->assertTablesEqual($xml_dataset->getTable('task_dependencies'), $this->getConnection()->createDataSet()->getTable('task_dependencies'));

    }

    /**
     * Tests checking allowed records with no permissions
     */
    public function testGetAllowedRecordsNoPermissions()
    {
$this->markTestIncomplete('This test has been deprecated by casting the project_id via intval().');
return;
        $file = new CProject();

        $allowed_records = $file->getAllowedRecords(2);

        $this->assertEquals(0, count($allowed_records));
    }

    /**
     * Tests checking allowed records with where set
     */
    public function testGetAllowedRecordsWithWhere()
    {
$this->markTestIncomplete('This test has been deprecated by casting the project_id via intval().');
return;
        $file = new CProject();

        $extra = array('where' => 'project_active = 1');
        $allowed_records = $file->getAllowedRecords(1, 'projects.project_id,project_name', null, null, $extra);

        $this->assertEquals(1, count($allowed_records));
        $this->assertEquals('Test Project', $allowed_records[1]);
    }

    /**
     * Tests getting a list of allowed project by user
     *
     */
    public function testGetAllowedProjectsInRows()
    {
$this->markTestIncomplete('This test has been deprecated by casting the project_id via intval().');
return;
      $file = new CProject();
      $file_in_rows = $file->getAllowedProjectsInRows(1);

      $this->assertEquals(2, db_num_rows($file_in_rows));

      $row = db_fetch_assoc($file_in_rows);
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

      $row = db_fetch_assoc($file_in_rows);
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

      $file_in_rows = $file->getAllowedProjectsInRows(2);

      $this->assertEquals(0, db_num_rows($file_in_rows));
    }

    /**
     * Tests getting the most critical tasks with project loaded
     */
    public function testGetCriticalTasksNoArgs()
    {
$this->markTestIncomplete('This test has been deprecated by casting the project_id via intval().');
return;
        $file = new CProject();
        $file->load(1);

        $critical_tasks = $file->getCriticalTasks();

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
$this->markTestIncomplete('This test has been deprecated by casting the project_id via intval().');
return;
        $file = new CProject();

        $critical_tasks = $file->getCriticalTasks(1);

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
$this->markTestIncomplete('This test has been deprecated by casting the project_id via intval().');
return;
        $file = new CProject();

        $critical_tasks = $file->getCriticalTasks(1,2);

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
$this->markTestIncomplete('This test has been deprecated by casting the project_id via intval().');
return;
       $file = new CProject();
       $file->load(1);

       $post_data = array(
            'dosql' =>                      'do_project_aed',
            'project_id' =>                 1,
            'project_creator' =>            1,
            'project_contacts' =>           '',
            'project_name' =>               'Updated Project',
            'project_parent' =>             '',
            'project_owner' =>              1,
            'project_company' =>            1,
            'project_location' =>           'Somewhere Updated',
            'project_start_date' =>         '20090728',
            'project_end_date' =>           '20090828',
            'project_target_budget' =>      15,
            'project_actual_budget' =>      15,
            'project_scheduled_hours' =>    0,
            'project_worked_hours' =>       0,
            'project_task_count' =>         0,
            'project_url' =>                'project-update.example.org',
            'project_demo_url' =>           'project-updatedemo.example.org',
            'project_priority' =>           '1',
            'project_short_name' =>         'uproject',
            'project_color_identifier' =>   'CCCEEE',
            'project_type' =>               1,
            'project_status' =>             1,
            'project_description' =>        'This is an updated project.',
            'email_project_owner' =>        1,
            'email_project_contacts' =>     0,
            'project_departments' =>        '1,2',
            'project_contacts' =>           '3,4'
        );

        $file->bind($post_data);
        $results = $file->store($AppUI);

        $this->assertTrue($results);

        $xml_dataset = $this->createXMLDataSet($this->getDataSetPath().'testStore.xml');
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
$this->markTestIncomplete('This test has been deprecated by casting the project_id via intval().');
return;
        $file = new CProject();

        $allowed_projects = $file->getAllowedProjects(1);

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
$this->markTestIncomplete('This test has been deprecated by casting the project_id via intval().');
return;
        $file = new CProject();

        $allowed_projects = $file->getAllowedProjects(1, false);

        $this->assertEquals(2,                      count($allowed_projects));
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
    }

    /**
     * Tests finding contacts of project that does have contact
     */
    public function testGetContacts()
    {
        global $AppUI;
$this->markTestIncomplete('This test has been deprecated by casting the project_id via intval().');
return;
        $contacts = CProject::getContacts($AppUI, 1);

        $this->assertEquals(1,                      count($contacts));
        $this->assertEquals(1,                      $contacts[1]['contact_id']);
        $this->assertEquals('Admin',                $contacts[1]['contact_first_name']);
        $this->assertEquals('Person',               $contacts[1]['contact_last_name']);
        $this->assertEquals('contact1@example.org', $contacts[1]['contact_email']);
        $this->assertEquals('1.999.999.9999',       $contacts[1]['contact_phone']);
        $this->assertEquals('',                     $contacts[1]['dept_name']);
        $this->assertEquals(1,                      $contacts[1][0]);
        $this->assertEquals('Admin',                $contacts[1][1]);
        $this->assertEquals('Person',               $contacts[1][2]);
        $this->assertEquals('contact1@example.org', $contacts[1][3]);
        $this->assertEquals('1.999.999.9999',       $contacts[1][4]);
        $this->assertEquals('',                     $contacts[1][5]);
    }

    /**
     * Test finding of departments of project
     */
    public function testGetDepartments()
    {
        global $AppUI;
$this->markTestIncomplete('This test has been deprecated by casting the project_id via intval().');
return;
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
$this->markTestIncomplete('This test has been deprecated by casting the project_id via intval().');
return;
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
     * Tests updating a projects status
     */
    public function testUpdateStatus()
    {
        global $AppUI;
$this->markTestIncomplete('This test has been deprecated by casting the project_id via intval().');
return;
        CProject::updateStatus($AppUI, 1, 2);
        $xml_dataset = $this->createXMLDataSet($this->getDataSetPath().'testUpdateStatus.xml');
        $this->assertTablesEqual($xml_dataset->getTable('projects'), $this->getConnection()->createDataSet()->getTable('projects'));
    }

    /**
     * Tests finding if project id passed has children
     */
    public function testHasChildProjectsWithArg()
    {
$this->markTestIncomplete('This test has been deprecated by casting the project_id via intval().');
return;
        $file = new CProject();

        $has_children = $file->hasChildProjects(1);

        $this->assertEquals(1, $has_children);
    }

    /**
     * Tests finding if project has children if project is loaded and no argument passed
     */
    public function testHasChildProjects()
    {
$this->markTestIncomplete('This test has been deprecated by casting the project_id via intval().');
return;
        $file = new CProject();
        $file->load(1);

        $has_children = $file->hasChildProjects();

        $this->assertEquals(1, $has_children);
    }

    /**
     * Tests finding if project has children if no project loaded and no argument passed
     */
    public function testHasChildProjectNoProjectID()
    {
$this->markTestIncomplete('This test has been deprecated by casting the project_id via intval().');
return;
        $file = new CProject();

        $has_children = $file->hasChildProjects();

        $this->assertEquals(-1, $has_children);
    }

    /**
     * Tests getting task logs with no filters passed
     */
    public function testGetTaskLogsNoArgs()
    {
        global $AppUI;
$this->markTestIncomplete('This test has been deprecated by casting the project_id via intval().');
return;
        $file = new CProject();

        $task_logs = $file->getTaskLogs($AppUI, 1);

        $this->assertEquals(4,                  count($task_logs));
        $this->assertEquals(28,                 count($task_logs[0]));
        $this->assertEquals(28,                 count($task_logs[1]));
        $this->assertEquals(28,                 count($task_logs[2]));
        $this->assertEquals(28,                 count($task_logs[3]));
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
$this->markTestIncomplete('This test has been deprecated by casting the project_id via intval().');
return;
        $file = new CProject();

        $task_logs = $file->getTaskLogs($AppUI, 1, 2);

        $this->assertEquals(1,                  count($task_logs));
        $this->assertEquals(28,                 count($task_logs[0]));
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
$this->markTestIncomplete('This test has been deprecated by casting the project_id via intval().');
return;
        $file = new CProject();

        $task_logs = $file->getTaskLogs($AppUI, 1, 0, true);

        $this->assertEquals(2,                  count($task_logs));
        $this->assertEquals(28,                 count($task_logs[0]));
        $this->assertEquals(28,                 count($task_logs[1]));
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
$this->markTestIncomplete('This test has been deprecated by casting the project_id via intval().');
return;
        $file = new CProject();

        $task_logs = $file->getTaskLogs($AppUI, 1, 0, false, true);

        $this->assertEquals(2,                  count($task_logs));
        $this->assertEquals(28,                 count($task_logs[0]));
        $this->assertEquals(28,                 count($task_logs[1]));
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
$this->markTestIncomplete('This test has been deprecated by casting the project_id via intval().');
return;
        $file = new CProject();

        $task_logs = $file->getTaskLogs($AppUI, 1, 0, false, false, 2);

        $this->assertEquals(1,                  count($task_logs));
        $this->assertEquals(28,                 count($task_logs[0]));
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
$this->markTestIncomplete('This test has been deprecated by casting the project_id via intval().');
return;
        $files = getProjects();

        $this->assertEquals(2,                  count($files));
        $this->assertEquals(1,                  $files[1]['project_id']);
        $this->assertEquals('Test Project',     $files[1]['project_name']);
        $this->assertEquals(1,                  $files[1]['project_parent']);
        $this->assertEquals(1,                  $files[1][0]);
        $this->assertEquals('Test Project',     $files[1][1]);
        $this->assertEquals('',                 $files[1][2]);
        $this->assertEquals(2,                  $files[2]['project_id']);
        $this->assertEquals('Test Project 2',   $files[2]['project_name']);
        $this->assertEquals(1,                  $files[2]['project_parent']);
        $this->assertEquals(2,                  $files[2][0]);
        $this->assertEquals('Test Project 2',   $files[2][1]);
        $this->assertEquals(1,                  $files[2][2]);
    }
}