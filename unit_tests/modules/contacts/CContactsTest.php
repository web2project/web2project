<?php
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
 * @license     Clear BSD
 * @link        http://www.web2project.net
 */

class CContactsTest extends CommonSetup
{

    protected function setUp()
    {
        parent::setUp();

        $this->obj = new CContact();
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

    public function testObjectProperties()
    {
        $unset = array('contact_methods');

        parent::objectPropertiesTest('CContact', 26, $unset);
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
        $this->obj->bind($this->post_data);
        $result = $this->obj->store();
        $this->assertTrue($result);
        $original_id = $this->obj->contact_id;
        $result = $this->obj->delete();

        $item = new CContact();
        $item->overrideDatabase($this->mockDB);
        $this->mockDB->stageHash(array('contact_first_name' => '', 'contact_display_name' => ''));
        $item->load($original_id);

        $this->assertTrue(is_a($item, 'CContact'));
        $this->assertEquals('',              $item->contact_first_name);
        $this->assertEquals('',              $item->contact_display_name);
    }

    public function testGetContactMethods()
    {
        $methods = array('phone_mobile' => '202-555-1212', 'url' => 'http://web2project.net',
                        'email_alt' => 'alternate@example.org', 'im_skype' => 'example_s',
                        'im_google' => 'example_g');

        foreach ($methods as $key => $value) {
            $this->mockDB->stageList(array('method_name' => $key, 'method_value' => $value));
        }

        $results = $this->obj->getContactMethods();
        foreach ($methods as $key => $value) {
            $this->assertContains($key,   $results['fields']);
            $this->assertContains($value, $results['values']);
        }
    }

    public function testIsUser()
    {
        $this->obj->contact_id = 1;
        $this->mockDB->stageResult(1);
        $this->assertTrue($this->obj->isUser());

        $this->obj->contact_id = 13;
        $this->mockDB->stageResult(0);
        $this->assertFalse($this->obj->isUser());

        $this->obj->contact_id = 'monkey!';
        $this->mockDB->stageResult(0);
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
        $this->mockDB->stageHash(array('company_id' => 0, 'company_name' => ''));
        $results = $this->obj->getCompanyDetails();
        $this->AssertEquals(2,                      count($results));
        $this->assertNull($results['company_id']);
        $this->assertNull($results['company_name']);
        $this->mockDB->clearHash();

        $this->mockDB->stageHash(array('company_id' => 1, 'company_name' => 'UnitTestCompany'));
        $this->obj->contact_company = 1;
        $results = $this->obj->getCompanyDetails();
        $this->AssertEquals(2,                      count($results));
        $this->assertEquals(1,                      $results['company_id']);
        $this->assertEquals('UnitTestCompany',      $results['company_name']);
    }

    public function testGetDepartmentDetails()
    {
        $this->mockDB->stageHash(array('dept_id' => 0, 'dept_name' => ''));
        $results = $this->obj->getDepartmentDetails();
        $this->AssertEquals(2,                      count($results));
        $this->assertNull($results['dept_id']);
        $this->assertNull($results['dept_name']);
        $this->mockDB->clearHash();

        $this->mockDB->stageHash(array('dept_id' => 1, 'dept_name' => 'Department 1'));
        $this->obj->contact_department = 1;
        $results = $this->obj->getDepartmentDetails();
        $this->AssertEquals(2,                      count($results));
        $this->assertEquals(1,                      $results['dept_id']);
        $this->assertEquals('Department 1',         $results['dept_name']);
    }

    public function testGetUpdateKey()
    {
        $this->obj->contact_id = 1;
        $this->mockDB->stageHashList($this->obj->contact_id, 'ASDFASDFASDF');

        $this->assertEquals('ASDFASDFASDF',         $this->obj->getUpdateKey());
    }

    public function testClearUpdateKey()
    {
        $this->mockDB->stageHash(array('contact_updatekey' => 'ASDFASDFASDF'));

        $this->obj->load(1);
        $this->assertEquals('ASDFASDFASDF',         $this->obj->contact_updatekey);

        $this->obj->clearUpdateKey();
        $this->assertEquals('',                     $this->obj->contact_updatekey);

        $this->obj->contact_id = 1;
        $this->assertEquals('',                     $this->obj->getUpdateKey());
    }

    public function testUpdateNotify()
    {
        $this->markTestSkipped('This test has not been implemented yet.');
    }

    public function testGetAllowedRecords()
    {
        $this->markTestSkipped('This test has not been implemented yet.');
    }

    public function testSearchContacts()
    {
        $this->markTestSkipped('This method is static.');
    }

    public function testGetFirstLetters()
    {
        $this->markTestSkipped('This method is static.');
    }

    public function testGetContactByUsername()
    {
        $this->markTestSkipped('This method is static.');
    }

    public function testGetContactByUserid()
    {
        $this->markTestSkipped('This method is static.');
    }

    public function testGetContactByEmail()
    {
        $this->markTestSkipped('This method is static.');
    }

    public function testGetContactByUpdatekey()
    {
        $this->markTestSkipped('This method is static.');
    }

    public function testGetProjects()
    {
        $this->markTestSkipped('This method is static.');
    }

    public function testClearOldUpdatekeys()
    {
        $this->markTestSkipped('This test has not been implemented yet.');
    }

    public function testLoadFull()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    public function testCanDelete()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    public function testGetCompanyID()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    public function testNotify()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    public function testHook_calendar()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }
}
