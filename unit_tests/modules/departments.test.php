<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Class for testing Departments functionality
 *
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to Clear BSD License. Please see the
 *   LICENSE file in root of site for further details
 *
 * @category    Departments
 * @package     web2project
 * @subpackage  unit_tests
 * @author      D. Keith Casey, Jr.<caseydk@users.sourceforge.net>
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

/**
 * This class tests functionality for Files
 *
 * @author      D. Keith Casey, Jr.<caseydk@users.sourceforge.net>
 * @category    Departments
 * @package     web2project
 * @subpackage  unit_tests
 * @copyright   2007-2010 The web2Project Development Team <w2p-developers@web2project.net>
 * @link        http://www.web2project.net
 */
class Departments_Test extends PHPUnit_Extensions_Database_TestCase
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
      return $this->createXMLDataSet($this->getDataSetPath().'departmentsSeed.xml');
    }
    protected function getDataSetPath()
    {
      return dirname(dirname(__FILE__)).'/db_files/departments/';
    }

    protected function setUp()
    {
      parent::setUp();

      $this->obj = new CDepartment();
      $this->post_data = array(
          'dosql'             => 'do_dept_aed',
          'dept_id'           => 0,
          'dept_parent'       => 1,
          'dept_company'      => 1,
          'dept_name'         => 'My Department',
          'dept_phone'        => '815-555-1212',
          'dept_fax'          => '301-555-1212',
          'dept_address1'     => '123 Fake Street',
          'dept_address2'     => 'Suite A',
          'dept_city'         => 'Beverly Hills',
          'dept_state'        => 'CA',
          'dept_zip'          => '90210',
          'dept_country'      => 'US',
          'dept_url'          => 'http://web2project.net/',
          'dept_desc'         => 'This is my department description',
          'dept_owner'        => 1,
          'dept_email'        => 'test@example.org',
          'dept_type'         => '1'
      );
    }

    protected function tearDown()
    {
      $this->getDataSet();
    }
    /**
     * Tests the Attributes of a new Departments object.
     */
    public function testNewDepartmentAttributes()
    {
      $dept = new CDepartment();

      $this->assertInstanceOf('CDepartment', $dept);
      $this->assertObjectHasAttribute('dept_id',          $dept);
      $this->assertObjectHasAttribute('dept_parent',      $dept);
      $this->assertObjectHasAttribute('dept_company',     $dept);
      $this->assertObjectHasAttribute('dept_name',        $dept);
      $this->assertObjectHasAttribute('dept_phone',       $dept);
      $this->assertObjectHasAttribute('dept_fax',         $dept);
      $this->assertObjectHasAttribute('dept_address1',    $dept);
      $this->assertObjectHasAttribute('dept_address2',    $dept);
      $this->assertObjectHasAttribute('dept_city',        $dept);
      $this->assertObjectHasAttribute('dept_state',       $dept);
      $this->assertObjectHasAttribute('dept_zip',         $dept);
      $this->assertObjectHasAttribute('dept_country',     $dept);
      $this->assertObjectHasAttribute('dept_url',         $dept);
      $this->assertObjectHasAttribute('dept_desc',        $dept);
      $this->assertObjectHasAttribute('dept_owner',       $dept);
      $this->assertObjectHasAttribute('dept_email',       $dept);
      $this->assertObjectHasAttribute('dept_type',        $dept);
    }

    /**
     * Tests the Attribute Values of a new Department object.
     */
    public function testNewDepartmentAttributeValues()
    {
        $dept = new CDepartment();
        $this->assertInstanceOf('CDepartment', $dept);
        $this->assertEquals(0, $dept->dept_id);
        $this->assertNull($dept->dept_parent);
        $this->assertNull($dept->dept_company);
        $this->assertNull($dept->dept_name);
        $this->assertNull($dept->dept_phone);
        $this->assertNull($dept->dept_fax);
        $this->assertNull($dept->dept_address1);
        $this->assertNull($dept->dept_address2);
        $this->assertNull($dept->dept_city);
        $this->assertNull($dept->dept_state);
        $this->assertNull($dept->dept_zip);
        $this->assertNull($dept->dept_country);
        $this->assertNull($dept->dept_url);
        $this->assertNull($dept->dept_desc);
        $this->assertNull($dept->dept_owner);
        $this->assertNull($dept->dept_email);
        $this->assertNull($dept->dept_type);
    }

    /**
     * Tests that the proper error message is returned when a dept is attempted
     * to be created without a name.
     */
    public function testCreateDepartmentNoName()
    {
        global $AppUI;

        unset($this->post_data['dept_name']);
        $this->obj->bind($this->post_data);
        $errorArray = $this->obj->store($AppUI);

        /**
        * Verify we got the proper error message
        */
        $this->AssertEquals(1, count($errorArray));
        $this->assertArrayHasKey('dept_name', $errorArray);

        /**
        * Verify that dept id was not set
        */
        $this->AssertEquals(0, $this->obj->dept_id);
    }

    /**
    * Tests that the proper error message is returned when a dept is attempted
    * to be created without a url.
    */
    public function testCreateDepartmentNoCompany()
    {
        global $AppUI;

        $this->post_data['dept_company'] = '';
        $this->obj->bind($this->post_data);
        $errorArray = $this->obj->store($AppUI);

        /**
        * Verify we got the proper error message
        */
        $this->AssertEquals(1, count($errorArray));
        $this->assertArrayHasKey('dept_company', $errorArray);

        /**
        * Verify that dept id was not set
        */
        $this->AssertEquals(0, $this->obj->dept_id);
    }

    /**
    * Tests that the proper error message is returned when a dept is attempted
    * to be created without an owner.
    */
    public function testCreateDepartmentNoOwner()
    {
        global $AppUI;

        unset($this->post_data['dept_owner']);
        $this->obj->bind($this->post_data);
        $errorArray = $this->obj->store($AppUI);
        /**
        * Verify we got the proper error message
        */
        $this->AssertEquals(1, count($errorArray));
        $this->assertArrayHasKey('dept_owner', $errorArray);

        /**
        * Verify that dept id was not set
        */
        $this->AssertEquals(0, $this->obj->dept_id);
    }

//    /**
//     * Tests the proper creation of a dept
//     */
//    public function testCreateDepartment()
//    {
//      global $AppUI;
//
//      $this->obj->bind($this->post_data);
//      $result = $this->obj->store($AppUI);
//
//      $this->assertTrue($result);
//      $this->assertEquals('web2project homepage',   $this->obj->dept_name);
//      $this->assertEquals(0,                        $this->obj->dept_project);
//      $this->assertEquals(0,                        $this->obj->dept_task);
//      $this->assertEquals('http://web2project.net', $this->obj->dept_url);
//      $this->assertEquals(0,                        $this->obj->dept_parent);
//      $this->assertEquals('This is web2project',    $this->obj->dept_description);
//      $this->assertEquals(1,                        $this->obj->dept_owner);
//      $this->assertEquals('',                       $this->obj->dept_icon);
//      $this->assertEquals(0,                        $this->obj->dept_category);
//      $this->assertNotEquals(0,                     $this->obj->dept_id);
//    }
//
//    /**
//     * Tests loading the Department Object
//     */
//    public function testLoad()
//    {
//      global $AppUI;
//
//      $this->obj->bind($this->post_data);
//      $result = $this->obj->store($AppUI);
//      $this->assertTrue($result);
//
//      $dept = new CDepartment();
//      $dept->load($this->obj->dept_id);
//
//      $this->assertEquals($this->obj->dept_name,              $dept->dept_name);
//      $this->assertEquals($this->obj->dept_project,           $dept->dept_project);
//      $this->assertEquals($this->obj->dept_task,              $dept->dept_task);
//      $this->assertEquals($this->obj->dept_url,               $dept->dept_url);
//      $this->assertEquals($this->obj->dept_parent,            $dept->dept_parent);
//      $this->assertEquals($this->obj->dept_description,       $dept->dept_description);
//      $this->assertEquals($this->obj->dept_owner,             $dept->dept_owner);
//      $this->assertEquals('obj/',                             $dept->dept_icon);
//      $this->assertEquals($this->obj->dept_category,          $dept->dept_category);
//      $this->assertEquals($this->obj->dept_id,                $dept->dept_id);
//    }
//
//    /**
//     * Tests the update of a dept
//     */
//    public function testUpdateDepartment()
//    {
//      global $AppUI;
//
//      $this->obj->bind($this->post_data);
//      $result = $this->obj->store($AppUI);
//      $this->assertTrue($result);
//      $original_id = $this->obj->dept_id;
//
//      $this->obj->dept_name = 'web2project Forums';
//      $this->obj->dept_url = 'http://forums.web2project.net';
//      $result = $this->obj->store($AppUI);
//      $this->assertTrue($result);
//      $new_id = $this->obj->dept_id;
//
//      $this->assertEquals($original_id,                    $new_id);
//      $this->assertEquals('web2project Forums',            $this->obj->dept_name);
//      $this->assertEquals('http://forums.web2project.net', $this->obj->dept_url);
//      $this->assertEquals('This is web2project',           $this->obj->dept_description);
//    }
//
//    /**
//     * Tests the delete of a dept
//     */
//    public function testDeleteDepartment()
//    {
//      global $AppUI;
//
//      $this->obj->bind($this->post_data);
//      $result = $this->obj->store($AppUI);
//      $this->assertTrue($result);
//      $original_id = $this->obj->dept_id;
//
//      $result = $this->obj->delete($AppUI);
//      $this->assertTrue($result);
//
//      $dept = new CDepartment();
//      $dept->load($original_id);
//      $this->assertEquals('',              $dept->dept_name);
//      $this->assertEquals('',              $dept->dept_url);
//    }
}