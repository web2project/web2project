<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Class for testing contacts functionality
 *
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to Clear BSD License. Please see the
 *   LICENSE file in root of site for further details
 *
 * @author      D. Keith Casey, Jr.<caseydk@users.sourceforge.net>
 * @category    CContacts
 * @package     web2project
 * @subpackage  unit_tests
 * @copyright   2007-2012 The web2Project Development Team <w2p-developers@web2project.net>
 * @license     Clear BSD
 * @link        http://www.web2project.net
 */

// NOTE: This path is relative to Phing's build.xml, not this test.
include_once 'CommonSetup.php';

class CContacts_Test extends CommonSetup
{

    protected function setUp()
    {
        parent::setUp();

        $this->obj = new CContact();
        $this->mockDB = new w2p_Mocks_Query();
        $this->obj->overrideDatabase($this->mockDB);

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

    /*
     * I'm just using this one to test recent class changes.
     */
    public function testNewContactAttributes()
    {
        $this->assertInstanceOf('CContact', $this->obj);
        $this->assertObjectHasAttribute('contact_display_name',     $this->obj);
    }

    public function testStoreCreate()
    {
        $this->obj->bind($this->post_data);
        $result = $this->obj->store();

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
        $this->obj->bind($this->post_data);
        $result = $this->obj->store();
        $this->assertTrue($result);

        $item = new CContact();
        $item->overrideDatabase($this->mockDB);
        $this->post_data['contact_id'] = $this->obj->contact_id;
        $this->mockDB->stageHash($this->post_data);
        $item->load($this->obj->contact_id);

        $this->assertEquals($this->obj->contact_first_name,     $item->contact_first_name);
        $this->assertEquals($this->obj->contact_last_name,      $item->contact_last_name);
        $this->assertEquals($this->obj->contact_display_name,   $item->contact_display_name);
        $this->assertEquals($this->obj->contact_company,        $item->contact_company);
        $this->assertEquals($this->obj->contact_department,     $item->contact_department);
        $this->assertEquals($this->obj->contact_icon,           $item->contact_icon);
        $this->assertEquals($this->obj->contact_owner,          $item->contact_owner);
        $this->assertEquals($this->obj->contact_id,             $item->contact_id);
    }

    public function testStoreUpdate()
    {
        $this->obj->bind($this->post_data);
        $result = $this->obj->store();
        $this->assertTrue($result);
        $original_id = $this->obj->contact_id;

        $this->obj->contact_first_name = 'Firstname2';
        $this->obj->contact_display_name = 'Some other name';
        $result = $this->obj->store();
        $this->assertTrue($result);
        $new_id = $this->obj->contact_id;

        $this->assertEquals($original_id,       $new_id);
        $this->assertEquals('Firstname2',       $this->obj->contact_first_name);
        $this->assertEquals('Some other name',  $this->obj->contact_display_name);
    }

    public function testDelete()
    {
        $this->markTestIncomplete(
                "I tried basing this on the CLink_Test->testDelete method and
                no matter what I get 'bindHashToObject : object expected' as
                the error message.");
    }

    public function testSetContactMethods()
    {
        $methods = array('phone_mobile' => '202-555-1212', 'url' => 'http://web2project.net',
                        'email_alt' => 'alternate@example.org', 'im_skype' => 'example_s',
                        'im_google' => 'example_g');

        $this->obj->contact_id = 1;
        $this->obj->overrideDatabase($this->mockDB);
        $this->obj->setContactMethods($methods);

        $results = $this->obj->getContactMethods();
        foreach ($methods as $key => $value) {
            $this->assertContains($key,   $results['fields']);
            $this->assertContains($value, $results['values']);
        }
    }

    public function testGetContactMethods()
    {
        $methods = array('phone_mobile' => '202-555-1212', 'url' => 'http://web2project.net',
                        'email_alt' => 'alternate@example.org', 'im_skype' => 'example_s',
                        'im_google' => 'example_g');

        $this->obj->contact_id = 1;
        $this->obj->setContactMethods($methods);

        $results = $this->obj->getContactMethods(array('phone_mobile', 'im_skype'));

        $this->AssertEquals(2, count($results['fields']));

        $id = array_search('phone_mobile', $results['fields']);
        $this->assertEquals($methods['phone_mobile'], $results['values'][$id]);

        $id = array_search('im_skype', $results['fields']);
        $this->assertEquals($methods['im_skype'],     $results['values'][$id]);
    }

    public function testIsUser()
    {
        $this->obj->contact_id = 1;
        $this->assertTrue($this->obj->isUser());

        $contact->contact_id = 13;
        $this->assertFalse($this->obj->isUser());

        $contact->contact_id = 'monkey!';
        $this->assertFalse($this->obj->isUser());
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testIs_Alpha()
    {
        $this->assertTrue($this->obj->is_alpha(123));
        $this->assertTrue($this->obj->is_alpha('123'));
        $this->assertFalse($this->obj->is_alpha('monkey'));
        $this->assertFalse($this->obj->is_alpha('3.14159'));
        $this->assertFalse($this->obj->is_alpha(3.14159));
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testGetCompanyName()
    {
        $this->obj->contact_company = 1;
        $this->assertEquals('UnitTestCompany',  $this->obj->getCompanyName());

        $this->obj->contact_company = 2;
        $this->assertEquals('CreatedCompany',  $this->obj->getCompanyName());
    }

    public function testGetCompanyDetails()
    {
        $results = $this->obj->getCompanyDetails();
        $this->AssertEquals(2,                      count($results));
        $this->assertEquals(0,                      $results['company_id']);
        $this->assertEquals('',                     $results['company_name']);

        $this->obj->contact_company = 1;
        $results = $this->obj->getCompanyDetails();
        $this->AssertEquals(2,                      count($results));
        $this->assertEquals(1,                      $results['company_id']);
        $this->assertEquals('UnitTestCompany',      $results['company_name']);
    }

    public function testGetDepartmentDetails()
    {
        $results = $this->obj->getDepartmentDetails();
        $this->AssertEquals(2,                      count($results));
        $this->assertEquals(0,                      $results['dept_id']);
        $this->assertEquals('',                     $results['dept_name']);

        $this->obj->contact_department = 1;
        $results = $this->obj->getDepartmentDetails();

        $this->AssertEquals(2,                      count($results));
        $this->assertEquals(1,                      $results['dept_id']);
        $this->assertEquals('Department 1',         $results['dept_name']);
    }

    public function testGetUpdateKey() {
        $this->mockDB->stageResult('ASDFASDFASDF');

        $this->assertEquals('ASDFASDFASDF',         $this->obj->getUpdateKey());
    }

    public function testClearUpdateKey() {
        $this->mockDB->stageHash(array('contact_updatekey' => 'ASDFASDFASDF'));

        $this->obj->load(1);
        $this->assertEquals('ASDFASDFASDF',         $this->obj->contact_updatekey);

        $this->obj->clearUpdateKey();
        $this->assertEquals('',                     $this->obj->contact_updatekey);

        $this->obj->contact_id = 1;
        $this->assertEquals('',                     $this->obj->getUpdateKey());
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

    /**
     * @todo Implement testLoadFull().
     */
    public function testLoadFull() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testCanDelete().
     */
    public function testCanDelete() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testGetCompanyID().
     */
    public function testGetCompanyID() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testNotify().
     */
    public function testNotify() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testHook_cron().
     */
    public function testHook_cron() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testHook_search().
     */
    public function testHook_search() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testHook_calendar().
     */
    public function testHook_calendar() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }
}