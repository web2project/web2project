<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Class for testing admin/users functionality
 *
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to Clear BSD License. Please see the
 *   LICENSE file in root of site for further details
 *
 * @category    Users
 * @package     web2project
 * @subpackage  unit_tests
 * @author      D. Keith Casey, Jr.<caseydk@users.sourceforge.net>
 * @copyright   2007-2012 The web2Project Development Team <w2p-developers@web2project.net>
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
$AppUI  = new w2p_Core_CAppUI();
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
 * This class tests functionality for CAdmin_Users
 *
 * @author      D. Keith Casey, Jr.<caseydk@users.sourceforge.net>
 * @category    CAdmin_Users
 * @package     web2project
 * @subpackage  unit_tests
 * @copyright   2007-2012 The web2Project Development Team <w2p-developers@web2project.net>
 * @link        http://www.web2project.net
 */
class Admin_Users_Test extends PHPUnit_Framework_TestCase
{
    protected $backupGlobals = FALSE;
    protected $obj = null;
    protected $post_data = array();
    protected $mockDB = null;

    protected function setUp()
    {
      parent::setUp();

      global $AppUI;
      $AppUI->user_id = 1;

      $this->obj    = new CAdmin_User();
      $this->mockDB = new w2p_Mocks_Query();
      $this->obj->overrideDatabase($this->mockDB);

      $GLOBALS['acl'] = new w2p_Mocks_Permissions();

      $this->post_data = array(
          'dosql'              => 'do_user_aed',
          'user_id'            => 0,
          'user_username'      => 'myusername',
          'user_password'      => 'myPassword',
          'user_type'          => 1,
          'user_signature'     => 'My Signature',
          'password_check'     => 'myPassword',
          'contact_id'         => 0,
          'contact_first_name' => 'Myfirstname',
          'contact_last_name'  => 'Mylastname',
          'contact_company'    => 0,
          'contact_department' => 0,
          'contact_email'      => 'web2project@test.com'
      );
    }

    /**
     * Tests the Attributes of a new CAdmin_Users object.
     */
    public function testAttributes()
    {
        $this->assertInstanceOf('CAdmin_User',            $this->obj);
        $this->assertObjectHasAttribute('user_username',  $this->obj);
        $this->assertObjectHasAttribute('user_password',  $this->obj);
        $this->assertObjectHasAttribute('user_type',      $this->obj);
        $this->assertObjectHasAttribute('user_contact',   $this->obj);
        $this->assertObjectHasAttribute('user_signature', $this->obj);
    }

    /**
     * Tests the Attribute Values of a new Link object.
     */
    public function testAttributeValues()
    {
        $this->assertInstanceOf('CAdmin_User',            $this->obj);
        $this->assertNull($this->obj->user_username);
        $this->assertNull($this->obj->user_password);
        $this->assertNull($this->obj->user_type);
        $this->assertNull($this->obj->user_contact);
        $this->assertNull($this->obj->user_signature);
    }

    /**
     * Tests that the proper error message is returned when a link is attempted
     * to be created without a name.
     */
    public function testCreateUserNoPassword()
    {
        unset($this->post_data['user_password']);
        $this->obj->bind($this->post_data);
        $this->obj->store();

        /**
        * Verify we got the proper error message
        */
        $errorArray = $this->obj->getError();
        $this->assertEquals(1,                    count($errorArray));
        $this->assertArrayHasKey('user_password', $errorArray);

        /**
        * Verify that user_id was not set
        */
        $this->assertEquals(0, $this->obj->user_id);
    }

    /**
     * Tests that the proper error message is returned we try to create a user
     *   with the same username as another.
     */
    public function testCreateUserExists()
    {
        $this->markTestIncomplete("Unfortunately, we can't test this one because
            the CAdmin_User::exists() method is static and we don't have a way
            to override its database call.");
    }

    /**
     * Tests the proper creation of a user & contact
     */
    public function testStoreCreate()
    {
        $this->obj->bind($this->post_data);
        $result = $this->obj->store();

        $contact = new CContact();
        $contact->overrideDatabase($this->mockDB);
        $contact->bind($this->post_data);
        $result = $contact->store();

        $this->assertTrue($result);
        $this->assertNotEquals(0,                 $contact->contact_id);

        $this->obj->user_contact = $contact->contact_id;
        $result = $this->obj->store();

        $this->assertTrue($result);
        $this->assertNotEquals(0,                 $this->obj->user_id);
    }

    /**
     * Tests loading the Link Object
     */
//    public function testLoad()
//    {
//        global $AppUI;
//
//        $this->obj->bind($this->post_data);
//        $result = $this->obj->store($AppUI);
//        $this->assertTrue($result);
//
//        $item = new CLink();
//        $item->overrideDatabase($this->mockDB);
//        $this->post_data['link_id'] = $this->obj->link_id;
//        $this->mockDB->stageHash($this->post_data);
//        $item->load($this->obj->link_id);
//
//        $this->assertEquals($this->obj->link_name,              $item->link_name);
//        $this->assertEquals($this->obj->link_project,           $item->link_project);
//        $this->assertEquals($this->obj->link_task,              $item->link_task);
//        $this->assertEquals($this->obj->link_url,               $item->link_url);
//        $this->assertEquals($this->obj->link_parent,            $item->link_parent);
//        $this->assertEquals($this->obj->link_description,       $item->link_description);
//        $this->assertEquals($this->obj->link_owner,             $item->link_owner);
//        $this->assertEquals($this->obj->link_category,          $item->link_category);
//        $this->assertNotEquals($this->obj->link_date,           '');
//    }

    /**
     * Tests the update of a link
     */
//    public function testStoreUpdate()
//    {
//      global $AppUI;
//
//      $this->obj->bind($this->post_data);
//      $result = $this->obj->store($AppUI);
//      $this->assertTrue($result);
//      $original_id = $this->obj->link_id;
//
//      $this->obj->link_name = 'web2project Forums';
//      $this->obj->link_url = 'http://forums.web2project.net';
//      $result = $this->obj->store($AppUI);
//      $this->assertTrue($result);
//      $new_id = $this->obj->link_id;
//
//      $this->assertEquals($original_id,                    $new_id);
//      $this->assertEquals('web2project Forums',            $this->obj->link_name);
//      $this->assertEquals('http://forums.web2project.net', $this->obj->link_url);
//      $this->assertEquals('This is web2project',           $this->obj->link_description);
//    }

    /**
     * Tests the delete of a link
     */
//    public function testDelete()
//    {
//        global $AppUI;
//
//        $this->obj->bind($this->post_data);
//        $result = $this->obj->store($AppUI);
//        $this->assertTrue($result);
//        $original_id = $this->obj->link_id;
//        $result = $this->obj->delete($AppUI);
//
//        $item = new CLink();
//        $item->overrideDatabase($this->mockDB);
//        $this->mockDB->stageHash(array('link_name' => '', 'link_url' => ''));
//        $item->load($original_id);
//
//        $this->assertTrue(is_a($item, 'CLink'));
//        $this->assertEquals('',              $item->link_name);
//        $this->assertEquals('',              $item->link_url);
//    }
}