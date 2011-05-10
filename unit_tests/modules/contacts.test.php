<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Class for testing contacts functionality
 *
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version. Please see the LICENSE file in root of site
 * for further details
 *
 * @category    Contacts
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
 * @category    Contacts
 * @package     web2project
 * @subpackage  unit_tests
 * @copyright   2007-2010 The web2Project Development Team <w2p-developers@web2project.net>
 * @link        http://www.web2project.net
 */
class Contacts_Test extends PHPUnit_Extensions_Database_TestCase
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

    protected function getDataSet()
    {
        return $this->createXMLDataSet($this->getDataSetPath().'contactsSeed.xml');
    }

    protected function getDataSetPath()
    {
        return dirname(dirname(__FILE__)).'/db_files/contacts/';
    }

    protected function setUp()
    {
        parent::setUp();

        $this->obj = new CContact();
        $this->post_data = array(
            'dosql'                     => 'do_contact_aed',
            'contact_id'                => 0,
            'contact_first_name'        => 'Firstname2',
            'contact_last_name'         => 'Lastname2',
            'contact_display_name'      => 'Firstname2 Lastname2',
            'contact_order_by'          => 'deprecated',
            'contact_title'             => 'Your Highness',
            'contact_birthday'          => '2002-02-02',
            'contact_company'           => 0,
            'contact_department'        => 0,
            'contact_type'              => 1,
            'contact_email'             => 'firstname2@example.org',
            'contact_phone'             => '703-555-1212',
            'contact_address1'          => '234 Fake Street',
            'contact_address2'          => 'Apartment #2',
            'contact_city'              => 'Arlington',
            'contact_state'             => 'Virginia',
            'contact_zip'               => '22204',
            'contact_country'           => 'United States',
            'contact_notes'             => 'no interesting notes',
            'contact_project'           => 0,
            'contact_icon'              => 'obj/contact',
            'contact_owner'             => 1,
            'contact_private'           => 0,
            'contact_job'               => 'King',
            'contact_updatekey'         => '',
            'contact_lastupdate'        => '2010-02-02',
            'contact_updateasked'       => '2010-12-02'
        );
    }

    protected function tearDown()
    {
        $this->getDataSet();
    }

    /*
     * I'm just using this one to test recent class changes.
     */
    public function testNewContactAttributes()
    {
        $contact = new CContact();

        $this->assertType('CContact', $contact);
        $this->assertObjectHasAttribute('contact_display_name',     $contact);
    }

    public function testStoreNoOwner()
    {
        global $AppUI;

        unset($this->post_data['contact_owner']);
        $this->obj->bind($this->post_data);
        $errorArray = $this->obj->store($AppUI);

        /**
        * Verify we got the proper error message
        */
        $this->AssertEquals(1, count($errorArray));
        $this->assertArrayHasKey('contact_owner', $errorArray);

        /**
        * Verify that link id was not set
        */
        $this->AssertEquals(0, $this->obj->contact_id);
    }

    public function testStore()
    {
        global $AppUI;

        $this->obj->bind($this->post_data);
        $result = $this->obj->store($AppUI);

        $this->assertTrue($result);
        $this->assertEquals('Firstname2',           $this->obj->contact_first_name);
        $this->assertEquals('Lastname2',            $this->obj->contact_last_name);
        $this->assertEquals('Firstname2 Lastname2', $this->obj->contact_display_name);
        $this->assertEquals(0,                      $this->obj->contact_company);
        $this->assertEquals(0,                      $this->obj->contact_department);
        $this->assertEquals('obj/contact',          $this->obj->contact_icon);
        $this->assertEquals(1,                      $this->obj->contact_owner);
        $this->assertNotEquals(0,                   $this->obj->contact_id);
    }

    public function testLoad()
    {
        global $AppUI;

        $this->obj->bind($this->post_data);
        $result = $this->obj->store($AppUI);
        $this->assertTrue($result);

        $contact = new CContact();
        $contact->load($this->obj->contact_id);

        $this->assertEquals($this->obj->contact_first_name,     $contact->contact_first_name);
        $this->assertEquals($this->obj->contact_last_name,      $contact->contact_last_name);
        $this->assertEquals($this->obj->contact_display_name,   $contact->contact_display_name);
        $this->assertEquals($this->obj->contact_company,        $contact->contact_company);
        $this->assertEquals($this->obj->contact_department,     $contact->contact_department);
        $this->assertEquals($this->obj->contact_icon,           $contact->contact_icon);
        $this->assertEquals($this->obj->contact_owner,          $contact->contact_owner);
        $this->assertEquals($this->obj->contact_id,             $contact->contact_id);
    }

    public function testUpdate()
    {
        global $AppUI;

        $this->obj->bind($this->post_data);
        $result = $this->obj->store($AppUI);
        $this->assertTrue($result);
        $original_id = $this->obj->contact_id;

        $this->obj->contact_first_name = 'Firstname2';
        $this->obj->contact_display_name = 'Some other name';
        $result = $this->obj->store($AppUI);
        $this->assertTrue($result);
        $new_id = $this->obj->contact_id;

        $this->assertEquals($original_id,       $new_id);
        $this->assertEquals('Firstname2',       $this->obj->contact_first_name);
        $this->assertEquals('Some other name',  $this->obj->contact_display_name);
    }

    public function testDelete()
    {
        global $AppUI;

        $this->obj->bind($this->post_data);
        $result = $this->obj->store($AppUI);
        $this->assertTrue($result);
        $original_id = $this->obj->contact_id;

        $result = $this->obj->delete($AppUI);
        $this->assertTrue($result);

        $contact = new CContact();
        $contact->load($original_id);
        $this->assertEquals('',              $contact->link_name);
        $this->assertEquals('',              $contact->link_url);
    }

    public function testSetContactMethods()
    {
        $methods = array('phone_mobile' => '202-555-1212', 'url' => 'http://web2project.net',
                        'email_alt' => 'alternate@example.org', 'im_skype' => 'example_s',
                        'im_google' => 'example_g');

        $contact = new CContact();
        $contact->contact_id = 1;
        $contact->setContactMethods($methods);

        $results = $contact->getContactMethods();
        foreach ($methods as $key => $value) {
            $this->assertArrayHasKey($key,      $results);
            $this->assertEquals($value,         $results[$key]);
        }
    }

    public function testGetContactMethods()
    {
        $methods = array('phone_mobile' => '202-555-1212', 'url' => 'http://web2project.net',
                        'email_alt' => 'alternate@example.org', 'im_skype' => 'example_s',
                        'im_google' => 'example_g');

        $contact = new CContact();
        $contact->contact_id = 1;
        $contact->setContactMethods($methods);

        $results = $contact->getContactMethods(array('phone_mobile', 'im_skype'));

        $this->AssertEquals(2, count($results));
        $this->assertEquals($methods['phone_mobile'], $results['phone_mobile']);
        $this->assertEquals($methods['im_skype'],     $results['im_skype']);
    }

    public function testCanDelete()
    {
        $this->obj->bind($this->post_data);
        $result = $this->obj->store($AppUI);
        $cantDelete = $this->obj->canDelete('error', true);
        $this->assertFalse($cantDelete);

        $contact = new CContact();
        $contact->bind($this->post_data);
        $contact->contact_first_name = 'Firstname3';
        $contact->contact_last_name  = 'Lastname3';
        $contact->contact_display_name = '';
        $result = $contact->store($AppUI);
        $canDeleteUser = $contact->canDelete('error');
        $this->assertTrue($canDeleteUser);
    }

    public function testIsUser()
    {
        $contact = new CContact();
        $contact->contact_id = 1;
        $this->assertTrue($contact->isUser());

        $contact->contact_id = 13;
        $this->assertFalse($contact->isUser());

        $contact->contact_id = 'monkey!';
        $this->assertFalse($contact->isUser());
    }

    public function testIs_Alpha()
    {
        $contact = new CContact();

        $this->assertTrue($contact->is_alpha(123));
        $this->assertTrue($contact->is_alpha('123'));
        $this->assertFalse($contact->is_alpha('monkey'));
        $this->assertFalse($contact->is_alpha('3.14159'));
        $this->assertFalse($contact->is_alpha(3.14159));
    }

    /*
     * @expectedExcpetion PHPUnit_Framework_Error
     */
    public function testGetCompanyName()
    {
        $contact = new CContact();
        $contact->contact_company = 1;
        $this->assertEquals('UnitTestCompany',  $contact->getCompanyName());

        $contact->contact_company = 2;
        $this->assertEquals('CreatedCompany',  $contact->getCompanyName());
    }

    public function testGetCompanyDetails()
    {
        $contact = new CContact();

        $results = $contact->getCompanyDetails();
        $this->AssertEquals(2,                      count($results));
        $this->assertEquals(0,                      $results['company_id']);
        $this->assertEquals('',                     $results['company_name']);

        $contact->contact_company = 1;
        $results = $contact->getCompanyDetails();
        $this->AssertEquals(2,                      count($results));
        $this->assertEquals(1,                      $results['company_id']);
        $this->assertEquals('UnitTestCompany',      $results['company_name']);
    }

    public function testGetDepartmentDetails()
    {
        $contact = new CContact();

        $results = $contact->getDepartmentDetails();
        $this->AssertEquals(2,                      count($results));
        $this->assertEquals(0,                      $results['dept_id']);
        $this->assertEquals('',                     $results['dept_name']);

        $contact->contact_department = 1;
        $results = $contact->getDepartmentDetails();
        $this->AssertEquals(2,                      count($results));
        $this->assertEquals(1,                      $results['dept_id']);
        $this->assertEquals('Department 1',         $results['dept_name']);
    }

    public function testGetUpdateKey() {
        $contact = new CContact();
        $contact->contact_id = 1;
        $this->assertEquals('ASDFASDFASDF',         $contact->getUpdateKey());
    }

    public function testClearUpdateKey() {
        $contact = new CContact();
        $contact->load(1);
        $this->assertEquals('ASDFASDFASDF',         $contact->contact_updatekey);

        $contact->clearUpdateKey();
        $this->assertEquals('',                     $contact->contact_updatekey);

        $contact = new CContact();
        $contact->contact_id = 1;
        $this->assertEquals('',                     $contact->getUpdateKey());
    }

    public function testUpdateNotify() {
        $this->markTestSkipped('This test has not been implemented yet.');
    }

    public function testGetAllowedRecords() {
        $this->markTestSkipped('This test has not been implemented yet.');
    }

    public function testSearchContacts() {
        $this->markTestSkipped('This test has not been implemented yet.');
    }

    public function testGetFirstLetters() {
        $this->markTestSkipped('This test has not been implemented yet.');
    }

    public function testGetContactByUsername() {
        $this->markTestSkipped('This test has not been implemented yet.');
    }

    public function testGetContactByUserid() {
        $this->markTestSkipped('This test has not been implemented yet.');
    }

    public function testGetContactByEmail() {
        $this->markTestSkipped('This test has not been implemented yet.');
    }

    public function testGetContactByUpdatekey() {
        $this->markTestSkipped('This test has not been implemented yet.');
    }

    public function testGetProjects() {
        $this->markTestSkipped('This test has not been implemented yet.');
    }

    public function testClearOldUpdatekeys() {
        $this->markTestSkipped('This test has not been implemented yet.');
    }

    public function testGetUpcomingBirthdays() {
        $this->markTestSkipped('This test has not been implemented yet.');
    }
}