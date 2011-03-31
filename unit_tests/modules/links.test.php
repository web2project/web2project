<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Class for testing links functionality
 *
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version. Please see the LICENSE file in root of site
 * for further details
 *
 * @category    Links
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
require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Extensions/Database/TestCase.php';

/**
 * This class tests functionality for Files
 *
 * @author      D. Keith Casey, Jr.<caseydk@users.sourceforge.net>
 * @category    Links
 * @package     web2project
 * @subpackage  unit_tests
 * @copyright   2007-2010 The web2Project Development Team <w2p-developers@web2project.net>
 * @link        http://www.web2project.net
 */
class Links_Test extends PHPUnit_Extensions_Database_TestCase
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
      return $this->createXMLDataSet($this->getDataSetPath().'linksSeed.xml');
    }
    protected function getDataSetPath()
    {
      return dirname(dirname(__FILE__)).'/db_files/';
    }

    protected function setUp()
    {
      parent::setUp();

      $this->obj = new CLink();
      $this->post_data = array(
          'dosql'             => 'do_link_aed',
          'link_id'           => 0,
          'link_name'         => 'web2project homepage',
          'link_project'      => 0,
          'link_task'         => 0,
          'link_url'          => 'http://web2project.net',
          'link_parent'       => '0',
          'link_description'  => 'This is web2project',
          'link_owner'        => 1,
          'link_date'         => '2009-01-01',
          'link_icon'         => '',
          'link_category'     => 0
      );
    }

    protected function tearDown()
    {
      $this->getDataSet();
    }
    /**
     * Tests the Attributes of a new Links object.
     */
    public function testNewLinkAttributes()
    {
      $link = new CLink();

      $this->assertType('CLink', $link);
      $this->assertObjectHasAttribute('link_id',          $link);
      $this->assertObjectHasAttribute('link_project',     $link);
      $this->assertObjectHasAttribute('link_url',         $link);
      $this->assertObjectHasAttribute('link_task',        $link);
      $this->assertObjectHasAttribute('link_name',        $link);
      $this->assertObjectHasAttribute('link_parent',      $link);
      $this->assertObjectHasAttribute('link_description', $link);
      $this->assertObjectHasAttribute('link_owner',       $link);
      $this->assertObjectHasAttribute('link_date',        $link);
      $this->assertObjectHasAttribute('link_icon',        $link);
      $this->assertObjectHasAttribute('link_category',    $link);
    }

    /**
     * Tests the Attribute Values of a new Link object.
     */
    public function testNewLinkAttributeValues()
    {
        $link = new CLink();
        $this->assertType('CLink', $link);
        $this->assertNull($link->link_id);
        $this->assertNull($link->link_project);
        $this->assertNull($link->link_url);
        $this->assertNull($link->link_task);
        $this->assertNull($link->link_name);
        $this->assertNull($link->link_parent);
        $this->assertNull($link->link_description);
        $this->assertNull($link->link_owner);
        $this->assertNull($link->link_date);
        $this->assertNull($link->link_icon);
        $this->assertNull($link->link_category);
    }

    /**
     * Tests that the proper error message is returned when a link is attempted
     * to be created without a name.
     */
    public function testCreateLinkNoName()
    {
      global $AppUI;

      unset($this->post_data['link_name']);
      $this->obj->bind($this->post_data);
      $errorArray = $this->obj->store($AppUI);

      /**
       * Verify we got the proper error message
       */
      $this->AssertEquals(1, count($errorArray));
      $this->assertArrayHasKey('link_name', $errorArray);

      /**
       * Verify that link id was not set
       */
      $this->AssertEquals(0, $this->obj->link_id);
    }

    /**
     * Tests that the proper error message is returned when a link is attempted
     * to be created without a url.
     */
    public function testCreateLinkNoUrl()
    {
      global $AppUI;

      $this->post_data['link_url'] = '';
      $this->obj->bind($this->post_data);
      $errorArray = $this->obj->store($AppUI);

      /**
       * Verify we got the proper error message
       */
      $this->AssertEquals(1, count($errorArray));
      $this->assertArrayHasKey('link_url', $errorArray);

      /**
       * Verify that link id was not set
       */
      $this->AssertEquals(0, $this->obj->link_id);
    }

    /**
     * Tests that the proper error message is returned when a link is attempted
     * to be created without an owner.
     */
    public function testCreateLinkNoOwner()
    {
      global $AppUI;

      unset($this->post_data['link_owner']);
      $this->obj->bind($this->post_data);
      $errorArray = $this->obj->store($AppUI);
      /**
       * Verify we got the proper error message
       */
      $this->AssertEquals(1, count($errorArray));
      $this->assertArrayHasKey('link_owner', $errorArray);

      /**
       * Verify that link id was not set
       */
      $this->AssertEquals(0, $this->obj->link_id);
    }

    /**
     * Tests the proper creation of a link
     */
    public function testCreateLink()
    {
      global $AppUI;

      $this->obj->bind($this->post_data);
      $result = $this->obj->store($AppUI);

      $this->assertTrue($result);
      $this->assertEquals('web2project homepage',   $this->obj->link_name);
      $this->assertEquals(0,                        $this->obj->link_project);
      $this->assertEquals(0,                        $this->obj->link_task);
      $this->assertEquals('http://web2project.net', $this->obj->link_url);
      $this->assertEquals(0,                        $this->obj->link_parent);
      $this->assertEquals('This is web2project',    $this->obj->link_description);
      $this->assertEquals(1,                        $this->obj->link_owner);
      $this->assertEquals('',                       $this->obj->link_icon);
      $this->assertEquals(0,                        $this->obj->link_category);
      $this->assertNotEquals(0,                     $this->obj->link_id);
    }

    /**
     * Tests loading the Link Object
     */
    public function testLoad()
    {
      global $AppUI;

      $this->obj->bind($this->post_data);
      $result = $this->obj->store($AppUI);
      $this->assertTrue($result);

      $link = new CLink();
      $link->load($this->obj->link_id);

      $this->assertEquals($this->obj->link_name,              $link->link_name);
      $this->assertEquals($this->obj->link_project,           $link->link_project);
      $this->assertEquals($this->obj->link_task,              $link->link_task);
      $this->assertEquals($this->obj->link_url,               $link->link_url);
      $this->assertEquals($this->obj->link_parent,            $link->link_parent);
      $this->assertEquals($this->obj->link_description,       $link->link_description);
      $this->assertEquals($this->obj->link_owner,             $link->link_owner);
      $this->assertEquals('obj/',                             $link->link_icon);
      $this->assertEquals($this->obj->link_category,          $link->link_category);
      $this->assertEquals($this->obj->link_id,                $link->link_id);
    }

    /**
     * Tests the update of a link
     */
    public function testUpdateLink()
    {
      global $AppUI;

      $this->obj->bind($this->post_data);
      $result = $this->obj->store($AppUI);
      $this->assertTrue($result);
      $original_id = $this->obj->link_id;

      $this->obj->link_name = 'web2project Forums';
      $this->obj->link_url = 'http://forums.web2project.net';
      $result = $this->obj->store($AppUI);
      $this->assertTrue($result);
      $new_id = $this->obj->link_id;

      $this->assertEquals($original_id,                    $new_id);
      $this->assertEquals('web2project Forums',            $this->obj->link_name);
      $this->assertEquals('http://forums.web2project.net', $this->obj->link_url);
      $this->assertEquals('This is web2project',           $this->obj->link_description);
    }

    /**
     * Tests the delete of a link
     */
    public function testDeleteLink()
    {
      global $AppUI;

      $this->obj->bind($this->post_data);
      $result = $this->obj->store($AppUI);
      $this->assertTrue($result);
      $original_id = $this->obj->link_id;

      $result = $this->obj->delete($AppUI);
      $this->assertTrue($result);

      $link = new CLink();
      $link->load($original_id);
      $this->assertEquals('',              $link->link_name);
      $this->assertEquals('',              $link->link_url);
    }
}