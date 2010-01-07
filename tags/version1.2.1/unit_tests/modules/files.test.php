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
      return $this->createXMLDataSet($this->getDataSetPath().'filesSeed.xml');
    }
    protected function getDataSetPath()
    {
      return dirname(dirname(__FILE__)).'/db_files/';
    }

    protected function setUp()
    {
      parent::setUp();

      $this->obj = new CFile();
      $this->post_data = array(
          'dosql' =>              'do_file_aed',
          'file_id' =>            0,
          'file_real_filename' => 'TheRealFileName',
          'file_project' =>       '0',
          'file_task' =>          '0',
          'file_name' =>          'thisIsTheFilename',
          'file_parent' =>        1,
          'file_description' =>   'File description',
          'file_type' =>          'image/jpeg',
          'file_owner' =>         '1',
          'file_date' =>          '20090728',
          'file_size' =>          '0',
          'file_version' =>       1,
		  'file_version_id' =>    1,
          'file_icon' =>          '',
          'file_category' =>      0,
          'file_checkout' =>      0,
          'file_co_reason' =>     '',
          'file_folder' =>        0,
          'file_indexed' =>       0
      );
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

      unset($this->post_data['file_real_filename']);
      $this->obj->bind($this->post_data);
      $errorArray = $this->obj->store($AppUI);

      /**
       * Verify we got the proper error message
       */
      $this->AssertEquals(1, count($errorArray));
      $this->assertArrayHasKey('file_real_filename', $errorArray);

      /**
       * Verify that project id was not set
       */
      $this->AssertEquals(0, $this->obj->file_id);
    }

    /**
     * Tests that the proper error message is returned when no filename is
     * passed.
     */
    public function testCreateFileNoName()
    {
      global $AppUI;

      unset($this->post_data['file_name']);
      $this->obj->bind($this->post_data);
      $errorArray = $this->obj->store($AppUI);

      /**
       * Verify we got the proper error message
       */
      $this->AssertEquals(1, count($errorArray));
      $this->assertArrayHasKey('file_name', $errorArray);

      /**
       * Verify that project id was not set
       */
      $this->AssertEquals(0, $this->obj->file_id);
    }

    /**
     * Tests that the proper error message is returned when no parent is passed.
     */
    public function testCreateFileNoParent()
    {
      global $AppUI;

      unset($this->post_data['file_parent']);
      $this->obj->bind($this->post_data);
      $errorArray = $this->obj->store($AppUI);

      /**
       * Verify we got the proper error message
       */
      $this->AssertEquals(1, count($errorArray));
      $this->assertArrayHasKey('file_parent', $errorArray);

      /**
       * Verify that project id was not set
       */
      $this->AssertEquals(0, $this->obj->file_id);
    }

    /**
     * Tests that the proper error message is returned when no type is passed.
     */
    public function testCreateFileNoType()
    {
      global $AppUI;

      unset($this->post_data['file_type']);
      $this->obj->bind($this->post_data);
      $errorArray = $this->obj->store($AppUI);

      /**
       * Verify we got the proper error message
       */
      $this->AssertEquals(1, count($errorArray));
      $this->assertArrayHasKey('file_type', $errorArray);

      /**
       * Verify that project id was not set
       */
      $this->AssertEquals(0, $this->obj->file_id);
    }

    /**
     * Tests that the proper error message is returned when no filesize is
     * passed.
     */
    public function testCreateFileNoFilesize()
    {
      global $AppUI;

      unset($this->post_data['file_size']);
      $this->obj->bind($this->post_data);
      $errorArray = $this->obj->store($AppUI);

      /**
       * Verify we got the proper error message
       */
      $this->AssertEquals(1, count($errorArray));
      $this->assertArrayHasKey('file_size', $errorArray);

      /**
       * Verify that project id was not set
       */
      $this->AssertEquals(0, $this->obj->file_id);
    }

    /**
     * Tests that the proper error messages are returned for the full check.
     */
    public function testCheck()
    {
      global $AppUI;

      unset($this->post_data['file_real_filename']);
      unset($this->post_data['file_name']);
      unset($this->post_data['file_parent']);
      unset($this->post_data['file_type']);
      unset($this->post_data['file_size']);
      $this->obj->bind($this->post_data);
      $errorArray = $this->obj->check();

      /**
       * Verify we got the proper error message
       */
      $this->AssertEquals(5, count($errorArray));
      $this->assertArrayHasKey('file_real_filename', $errorArray);
      $this->assertArrayHasKey('file_name', $errorArray);
      $this->assertArrayHasKey('file_parent', $errorArray);
      $this->assertArrayHasKey('file_type', $errorArray);
      $this->assertArrayHasKey('file_size', $errorArray);
    }

    /**
     * Tests the proper creation of a project.
     */
    public function testCreateFile()
    {
      global $AppUI;

      $this->obj->bind($this->post_data);
      $errorArray = $this->obj->store($AppUI);
      $this->assertTrue($errorArray);

      $xml_file_dataset = $this->createXMLDataSet($this->getDataSetPath().'filesTestCreate.xml');
      $xml_file_filtered_dataset = new PHPUnit_Extensions_Database_DataSet_DataSetFilter($xml_file_dataset, array('files' => array('project_created', 'project_updated')));
      $xml_db_dataset = $this->getConnection()->createDataSet();
      $xml_db_filtered_dataset = new PHPUnit_Extensions_Database_DataSet_DataSetFilter($xml_db_dataset, array('files' => array('project_created', 'project_updated')));

      $this->assertTablesEqual($xml_file_filtered_dataset->getTable('files'), $xml_db_filtered_dataset->getTable('files'));
    }
}