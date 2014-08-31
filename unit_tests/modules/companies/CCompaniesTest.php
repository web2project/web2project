<?php
/**
 * Class for testing companies functionality
 *
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to Clear BSD License. Please see the
 *   LICENSE file in root of site for further details
 *
 * @author      Trevor Morse <trevor.morse@gmail.com>
 * @category    CCompanies
 * @package     web2project
 * @subpackage  unit_tests
 * @license     Clear BSD
 * @link        http://www.web2project.net
 */

class CCompaniesTest extends CommonSetup
{

    public function setUp()
    {
        parent::setUp();

        $this->obj = new CCompany();
        $this->obj->overrideDatabase($this->mockDB);

        $GLOBALS['acl'] = new w2p_Mocks_Permissions();

        $this->post_data = array (
            'dosql'                 => 'do_company_aed',
            'company_id'            => 0,
            'company_name'          => 'UnitTestCompany',
            'company_email'         => 'web2project@example.org',
            'company_phone1'        => '1.999.999.9999',
            'company_phone2'        => '1.999.999.9998',
            'company_fax'           => '1.999.999.9997',
            'company_address1'      => 'Address 1',
            'company_address2'      => 'Address 2',
            'company_city'          => 'City',
            'company_state'         => 'CA',
            'company_zip'           => '90210',
            'company_country'       => 'US',
            'company_primary_url'   => 'web2project.net',
            'company_owner'         => 1,
            'company_type'          => 2,
            'company_description'   => 'This is a company.'

        );
    }

    public function testObjectProperties()
    {
        parent::objectPropertiesTest('CCompany', 16);
    }

    /**
     * Tests that the proper error message is returned when a company
     * is attempted to be created without a name.
     */
    public function testCreateCompanyNoName()
    {
        unset($this->post_data['company_name']);
        $this->obj->bind($this->post_data);

        /**
         * Verify we got the proper error message
         */
        $this->assertFalse($this->obj->store());
        $this->assertArrayHasKey('company_name', $this->obj->getError());

        /**
         * Verify that company id was not set
         */
        $this->assertEquals(0, $this->obj->company_id);
    }

    /**
     * Tests the proper creation of a company
     */
    public function testStoreCreate()
    {
        $this->obj->bind($this->post_data);
        $result = $this->obj->store();

        $this->assertTrue($result);
        $this->assertEquals('UnitTestCompany',          $this->obj->company_name);
        $this->assertEquals('web2project@example.org',  $this->obj->company_email);
        $this->assertEquals('1.999.999.9999',           $this->obj->company_phone1);
        $this->assertEquals('1.999.999.9998',           $this->obj->company_phone2);
        $this->assertEquals('1.999.999.9997',           $this->obj->company_fax);
        $this->assertEquals('Address 1',                $this->obj->company_address1);
        $this->assertEquals('Address 2',                $this->obj->company_address2);
        $this->assertEquals('City',                     $this->obj->company_city);
        $this->assertEquals('CA',                       $this->obj->company_state);
        $this->assertEquals('90210',                    $this->obj->company_zip);
        $this->assertEquals('US',                       $this->obj->company_country);
        $this->assertEquals('web2project.net',          $this->obj->company_primary_url);
        $this->assertEquals(1,                          $this->obj->company_owner);
        $this->assertEquals(2,                          $this->obj->company_type);
        $this->assertEquals('This is a company.' ,      $this->obj->company_description);
        $this->assertNotEquals(0,                       $this->obj->company_id);
    }

    /**
     * Tests loading the Company Object
     */
    public function testLoad()
    {
        $this->obj->bind($this->post_data);
        $result = $this->obj->store();
        $this->assertTrue($result);

        $item = new CCompany();
        $item->overrideDatabase($this->mockDB);
        $this->post_data['company_id'] = $this->obj->contact_id;
        $this->mockDB->stageHash($this->post_data);
        $item->load($this->obj->company_id);

        $this->assertEquals($this->obj->company_name,         $item->company_name);
        $this->assertEquals($this->obj->company_primary_url,  $item->company_primary_url);
        $this->assertEquals($this->obj->company_owner,        $item->company_owner);
        $this->assertEquals($this->obj->company_type,         $item->company_type);
    }

    /**
     * Tests loading the Company Object
     */
    public function testLoadFull()
    {
        $this->mockDB->stageHash($this->post_data);

        $this->obj->loadFull(null, 1);

        $this->assertEquals('UnitTestCompany',          $this->obj->company_name);
        $this->assertEquals('web2project@example.org',  $this->obj->company_email);
        $this->assertEquals('1.999.999.9999',           $this->obj->company_phone1);
        $this->assertEquals('1.999.999.9998',           $this->obj->company_phone2);
        $this->assertEquals('1.999.999.9997',           $this->obj->company_fax);
        $this->assertEquals('Address 1',                $this->obj->company_address1);
        $this->assertEquals('Address 2',                $this->obj->company_address2);
        $this->assertEquals('City',                     $this->obj->company_city);
        $this->assertEquals('CA',                       $this->obj->company_state);
        $this->assertEquals('90210',                    $this->obj->company_zip);
        $this->assertEquals('US',                       $this->obj->company_country);
        $this->assertEquals('web2project.net',          $this->obj->company_primary_url);
        $this->assertEquals(1,                          $this->obj->company_owner);
        $this->assertEquals(2,                          $this->obj->company_type);
        $this->assertEquals('This is a company.' ,      $this->obj->company_description);
    }

    /**
     * Tests the update of a company
     */
    public function testStoreUpdate()
    {
        $this->obj->bind($this->post_data);
        $result = $this->obj->store();
        $this->assertTrue($result);
        $original_id = $this->obj->company_id;

        $this->obj->company_name = 'UpdatedCompany';
        $this->obj->company_address1 = 'Updated Address 1';
        $result = $this->obj->store();
        $this->assertTrue($result);
        $new_id = $this->obj->company_id;

        $this->assertEquals($original_id,        $new_id);
        $this->assertEquals('UpdatedCompany',    $this->obj->company_name);
        $this->assertEquals('Updated Address 1', $this->obj->company_address1);
    }

    /**
     * Tests the delete of a company
     */
    public function testDelete()
    {
        $this->obj->bind($this->post_data);
        $result = $this->obj->store();
        $this->assertTrue($result);
        $original_id = $this->obj->company_id;
        $result = $this->obj->delete();

        $item = new CCompany();
        $item->overrideDatabase($this->mockDB);
        $this->mockDB->stageHash(array('company_name' => '', 'company_owner' => ''));
        $item->load($original_id);

        $this->assertEquals('',              $item->company_name);
        $this->assertEquals('',              $item->company_owner);
    }

    /**
     * Tests loading list of companies with no criteria
     */
    public function testGetCompanyListNoCriteria()
    {

        $this->mockDB->stageList(
                array('company_id' => 2, 'company_name' => 'CreatedCompany',
                    'company_type' => 1, 'countp' => 1,
                    'contact_display_name' => 'Admin Person'));
        $this->mockDB->stageList(
                array('company_id' => 3, 'company_name' => 'CreatedCompany',
                    'company_type' => 2, 'countp' => 0,
                    'contact_display_name' => 'Admin Person'));
        $this->mockDB->stageList(
                array('company_id' => 1, 'company_name' => 'UnitTestCompany',
                    'company_type' => 2, 'countp' => 1,
                    'contact_display_name' => 'Admin Person'));
        $this->mockDB->stageList(
                array('company_id' => 4, 'company_name' => 'UpdatedCompany',
                    'company_type' => 2, 'countp' => 0,
                    'contact_display_name' => 'Admin Person'));

        $companies = $this->obj->getCompanyList();

        $this->assertInternalType(PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $companies);
        $this->assertEquals(4,                             count($companies));
        $this->assertEquals(2,                             $companies[0]['company_id']);
        $this->assertEquals('CreatedCompany',              $companies[0]['company_name']);
        $this->assertEquals(1,                             $companies[0]['company_type']);
        $this->assertEquals(1,                             $companies[0]['countp']);
        $this->assertEquals('Admin Person',                $companies[0]['contact_display_name']);

        $this->assertEquals(3,                             $companies[1]['company_id']);
        $this->assertEquals('CreatedCompany',              $companies[1]['company_name']);
        $this->assertEquals(2,                             $companies[1]['company_type']);
        $this->assertEquals(0,                             $companies[1]['countp']);
        $this->assertEquals('Admin Person',                $companies[1]['contact_display_name']);

        $this->assertEquals(1,                             $companies[2]['company_id']);
        $this->assertEquals('UnitTestCompany',             $companies[2]['company_name']);
        $this->assertEquals(2,                             $companies[2]['company_type']);
        $this->assertEquals(1,                             $companies[2]['countp']);
        $this->assertEquals('Admin Person',                $companies[2]['contact_display_name']);

        $this->assertEquals(4,                             $companies[3]['company_id']);
        $this->assertEquals('UpdatedCompany',              $companies[3]['company_name']);
        $this->assertEquals(2,                             $companies[3]['company_type']);
        $this->assertEquals(0,                             $companies[3]['countp']);
        $this->assertEquals('Admin Person',                $companies[3]['contact_display_name']);
    }

    /**
    * Tests loading list of companies by Type
    */
    public function testGetCompanyListByTypeNoMatch()
    {
        $results = $this->obj->getCompanyList(null, 3);

        $this->assertInternalType(PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $results);
        $this->assertEquals(0, count($results));
    }

    /**
    * Tests loading list of companies by Type
    */
    public function testGetCompanyListByType()
    {
        $this->mockDB->stageList(
                array('company_id' => 2, 'company_name' => 'CreatedCompany',
                    'company_type' => 1, 'countp' => 1,
                    'contact_display_name' => 'Admin Person'));

        $companies = $this->obj->getCompanyList(null, 1);

        $this->assertInternalType(PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $companies);
        $this->assertEquals(1,                             count($companies));
        $this->assertEquals(2,                             $companies[0]['company_id']);
        $this->assertEquals('CreatedCompany',              $companies[0]['company_name']);
        $this->assertEquals(1,                             $companies[0]['company_type']);
        $this->assertEquals(1,                             $companies[0]['countp']);
        $this->assertEquals('Admin Person',                $companies[0]['contact_display_name']);
    }

    /**
    * Tests loading list of companies by search string
    */
    public function testGetCompanyListByStringNoMatch()
    {
        $results = $this->obj->getCompanyList(null, -1, 'This is a company');

        $this->assertInternalType(PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $results);
        $this->assertEquals(0, count($results));
    }

    /**
    * Tests loading list of companies by owner id
    */
    public function testGetCompanyListByOwnerIDNoMatch()
    {
        $results = $this->obj->getCompanyList(null, -1, '', 2);

        $this->assertInternalType(PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $results);
        $this->assertEquals(0, count($results));
    }

    /**
    * Tests loading list of companies by owner id
    */
    public function testGetCompanyListByOwnerID()
    {
        $this->mockDB->stageList(
                array('company_id' => 2, 'company_name' => 'CreatedCompany',
                    'company_type' => 1, 'countp' => 1,
                    'contact_display_name' => 'Admin Person'));
        $this->mockDB->stageList(
                array('company_id' => 3, 'company_name' => 'CreatedCompany',
                    'company_type' => 2, 'countp' => 0,
                    'contact_display_name' => 'Admin Person'));
        $this->mockDB->stageList(
                array('company_id' => 1, 'company_name' => 'UnitTestCompany',
                    'company_type' => 2, 'countp' => 1,
                    'contact_display_name' => 'Admin Person'));
        $this->mockDB->stageList(
                array('company_id' => 4, 'company_name' => 'UpdatedCompany',
                    'company_type' => 2, 'countp' => 0,
                    'contact_display_name' => 'Admin Person'));

        $companies = $this->obj->getCompanyList(null, -1, '', 1);

        $this->assertInternalType(PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $companies);
        $this->assertEquals(4,                             count($companies));
        $this->assertEquals(2,                             $companies[0]['company_id']);
        $this->assertEquals('CreatedCompany',              $companies[0]['company_name']);
        $this->assertEquals(1,                             $companies[0]['company_type']);
        $this->assertEquals(1,                             $companies[0]['countp']);
        $this->assertEquals('Admin Person',                $companies[0]['contact_display_name']);

        $this->assertEquals(3,                             $companies[1]['company_id']);
        $this->assertEquals('CreatedCompany',              $companies[1]['company_name']);
        $this->assertEquals(2,                             $companies[1]['company_type']);
        $this->assertEquals(0,                             $companies[1]['countp']);
        $this->assertEquals('Admin Person',                $companies[1]['contact_display_name']);

        $this->assertEquals(1,                             $companies[2]['company_id']);
        $this->assertEquals('UnitTestCompany',             $companies[2]['company_name']);
        $this->assertEquals(2,                             $companies[2]['company_type']);
        $this->assertEquals(1,                             $companies[2]['countp']);
        $this->assertEquals('Admin Person',                $companies[2]['contact_display_name']);

        $this->assertEquals(4,                             $companies[3]['company_id']);
        $this->assertEquals('UpdatedCompany',              $companies[3]['company_name']);
        $this->assertEquals(2,                             $companies[3]['company_type']);
        $this->assertEquals(0,                             $companies[3]['countp']);
        $this->assertEquals('Admin Person',                $companies[3]['contact_display_name']);
    }

    /**
     * @todo Implement testCanDelete().
     */
    public function testCanDelete()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testGetCompanyList().
     */
    public function testGetCompanyList()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    public function testGetCompanies()
    {
        $results = $this->obj->loadAll();
        $this->assertEquals(0,                  count($results));

        $this->mockDB->stageHashList(1, array('company_id' => 1, 'company_name' => 'First Company'));
        $this->mockDB->stageHashList(2, array('company_id' => 2, 'company_name' => 'Second Company'));
        $results = $this->obj->loadAll();

        $this->assertEquals(2,                  count($results));
        $this->assertEquals('First Company',    $results[1]['company_name']);
        $this->assertEquals('Second Company',   $results[2]['company_name']);
    }

    /**
     * @todo Implement testGetProjects().
     */
    public function testGetProjects()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testGetContacts().
     */
    public function testGetContacts()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testGetUsers().
     */
    public function testGetUsers()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testGetDepartments().
     */
    public function testGetDepartments()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }
}
