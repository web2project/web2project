<?php
/**
 * Class for testing Departments functionality
 *
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to Clear BSD License. Please see the
 *   LICENSE file in root of site for further details
 *
 * @author      D. Keith Casey, Jr.<caseydk@users.sourceforge.net>
 * @category    CDepartments
 * @package     web2project
 * @subpackage  unit_tests
 * @license     Clear BSD
 * @link        http://www.web2project.net
 */

class CDepartmentsTest extends CommonSetup
{

    protected function setUp()
    {
      parent::setUp();

      $this->obj = new CDepartment();
      $this->obj->overrideDatabase($this->mockDB);

      $this->post_data = array(
          'dosql'             => 'do_dept_aed',
          'dept_id'           => 0,
          'dept_parent'       => 0,
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

    public function testObjectProperties()
    {
        parent::objectPropertiesTest('CDepartment', 17);
    }

    /**
     * Tests that the proper error message is returned when a dept is attempted
     * to be created without a name.
     */
    public function testCreateDepartmentNoName()
    {
        unset($this->post_data['dept_name']);
        $this->obj->bind($this->post_data);

        /**
        * Verify we got the proper error message
        */
        $this->assertFalse($this->obj->store());
        $this->assertArrayHasKey('dept_name', $this->obj->getError());

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
        $this->post_data['dept_company'] = '';
        $this->obj->bind($this->post_data);

        /**
        * Verify we got the proper error message
        */
        $this->assertFalse($this->obj->store());
        $this->assertArrayHasKey('dept_company', $this->obj->getError());

        /**
        * Verify that dept id was not set
        */
        $this->AssertEquals(0, $this->obj->dept_id);
    }

    /**
     * Tests loading the Department Object
     */
    public function testLoad()
    {
        $this->obj->bind($this->post_data);
        $result = $this->obj->store();
        $this->assertTrue($result);

        $item = new CDepartment();
        $item->overrideDatabase($this->mockDB);
        $this->post_data['dept_id'] = $this->obj->dept_id;
        $this->mockDB->stageHash($this->post_data);
        $item->load($this->obj->dept_id);

        $this->assertEquals($this->obj->dept_name,      $item->dept_name);
        $this->assertEquals($this->obj->dept_company,   $item->dept_company);
        $this->assertEquals($this->obj->dept_parent,    $item->dept_parent);
        $this->assertEquals($this->obj->dept_owner,     $item->dept_owner);
        $this->assertEquals($this->obj->dept_type,      $item->dept_type);
        $this->assertEquals($this->obj->dept_id,        $item->dept_id);
    }

    /**
     * Tests the proper creation of a dept
     */
    public function testStoreCreate()
    {
      $this->obj->bind($this->post_data);
      $result = $this->obj->store();

      $this->assertTrue($result);
      $this->assertEquals('My Department',  $this->obj->dept_name);
      $this->assertEquals(1,                $this->obj->dept_company);
      $this->assertEquals(0,                $this->obj->dept_parent);
      $this->assertEquals(1,                $this->obj->dept_owner);
      $this->assertEquals(1,                $this->obj->dept_type);
      $this->assertNotEquals(0,             $this->obj->dept_id);
    }

    /**
     * Tests the update of a dept
     */
    public function testStoreUpdate()
    {
        $this->obj->bind($this->post_data);
        $result = $this->obj->store();
        $this->assertTrue($result);
        $original_id = $this->obj->dept_id;

        $this->obj->dept_name = 'Change the department name';
        $result = $this->obj->store();

        $this->assertTrue($result);
        $new_id = $this->obj->dept_id;

        $this->assertEquals($original_id,                    $new_id);
        $this->assertEquals('Change the department name',    $this->obj->dept_name);
    }

    /**
     * Tests the delete of a dept
     */
    public function testDelete()
    {
        $this->obj->bind($this->post_data);
        $result = $this->obj->store();
        $this->assertTrue($result);
        $original_id = $this->obj->dept_id;
        $result = $this->obj->delete();

        $item = new CDepartment();
        $item->overrideDatabase($this->mockDB);
        $this->mockDB->stageHash(array('dept_name' => '', 'dept_owner' => ''));
        $item->load($original_id);

        $this->assertEquals('',              $item->dept_name);
        $this->assertEquals('',              $item->dept_owner);
    }

    /**
     * @todo Implement testLoadFull().
     */
    public function testLoadFull()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testLoadOtherDepts().
     */
    public function testLoadOtherDepts()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testGetFilteredDepartmentList().
     */
    public function testGetFilteredDepartmentList()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testCheck().
     */
    public function testCheck()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testGetAllowedRecords().
     */
    public function testGetAllowedRecords()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testGetAllowedSQL().
     */
    public function testGetAllowedSQL()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testSetAllowedSQL().
     */
    public function testSetAllowedSQL()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testGetDepartmentList().
     */
    public function testGetDepartmentList()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testGetContactList().
     */
    public function testGetContactList()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    public function testHook_search() {
        $search = $this->obj->hook_search();

        $this->assertTrue(array_key_exists('search_fields', $search));
        $this->assertEquals(count($search), 8);
    }
}
