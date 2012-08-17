<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Class for testing budget functionality
 *
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to Clear BSD License. Please see the
 *   LICENSE file in root of site for further details
 *
 * @author      D. Keith Casey, Jr.<caseydk@users.sourceforge.net>
 * @category    CSystem_Budget
 * @package     web2project
 * @subpackage  unit_tests
 * @copyright   2007-2012 The web2Project Development Team <w2p-developers@web2project.net>
 * @license     Clear BSD
 * @link        http://www.web2project.net
 */

// NOTE: This path is relative to Phing's build.xml, not this test.
include_once 'CommonSetup.php';

class CSystemBcode_Test extends CommonSetup
{

    protected function setUp()
    {
      parent::setUp();

      $this->obj    = new CSystem_Bcode();
      $this->obj->overrideDatabase($this->mockDB);

      $GLOBALS['acl'] = new w2p_Mocks_Permissions();

      $this->post_data = array(
          'dosql'             => 'do_budgeting_aed',
          'budget_id'         => 0,
          'budget_company'    => 0,
          'budget_dept'       => 0,
          'budget_start_date' => '20120105',
          'budget_end_date'   => '20120119',
          'budget_amount'     => 500.21,
          'budget_category'   => 1
      );
    }

    public function testObjectProperties()
    {
        parent::testObjectProperties('CSystem_Bcode', 8);
    }

    /**
     * Tests that the proper error message is returned when a budget of zero
     *   is attempted.
     */
    public function testCreateDuplicateCode()
    {
        $this->markTestIncomplete("Not sure how to test this one..");
    }

    /**
     * Tests the proper creation of a bcode
     */
    public function testStoreCreate()
    {
        $this->markTestIncomplete("Underway..");

        $this->obj->bind($this->post_data);
        $result = $this->obj->store();

        $this->assertTrue($result);

        /*
         *  These fields come from the $_POST data and should be pass throughs.
         */
        $this->assertEquals(0,                     $this->obj->budget_company);
        $this->assertEquals(0,                     $this->obj->budget_dept);
        $this->assertEquals(500.21,                $this->obj->budget_amount);
        $this->assertEquals(1,                     $this->obj->budget_category);
        /*
         *  These fields are from the $_POST but are modified in the store().
         */
        $this->assertEquals('2012-01-05 00:00:00', $this->obj->budget_start_date);
        $this->assertEquals('2012-01-19 00:00:00', $this->obj->budget_end_date);
        $this->assertNotEquals(0,                  $this->obj->budget_id);
    }

    /**
     * Tests loading the CSystem_Bcode Object
     */
    public function testLoad()
    {
        $this->markTestIncomplete("Underway..");

        $this->obj->bind($this->post_data);
        $result = $this->obj->store();
        $this->assertTrue($result);

        $item = new CSystem_Budget();
        $item->overrideDatabase($this->mockDB);
        $this->post_data['budget_id'] = $this->obj->budget_id;
        $this->mockDB->stageHash($this->post_data);
        $item->load($this->obj->budget_id);

        $this->assertEquals($this->obj->budget_company,    $item->budget_company);
        $this->assertEquals($this->obj->budget_dept,       $item->budget_dept);
        $this->assertEquals($this->obj->budget_start_date, '2012-01-05 00:00:00');
        $this->assertEquals($this->obj->budget_end_date,   '2012-01-19 00:00:00');
        $this->assertEquals($this->obj->budget_amount,     $item->budget_amount);
        $this->assertEquals($this->obj->budget_category,   $item->budget_category);
    }

    /**
     * Tests the update of a CSystem_Bcode Object
     */
    public function testStoreUpdate()
    {
        $this->markTestIncomplete("Underway..");

        $this->obj->bind($this->post_data);
        $result = $this->obj->store();
        $this->assertTrue($result);
        $original_id = $this->obj->budget_id;

        $this->obj->budget_amount       = 8000;

        $result = $this->obj->store();
        $this->assertTrue($result);
        $new_id = $this->obj->budget_id;

        $this->assertEquals($original_id, $new_id);
        $this->assertEquals(8000,         $this->obj->budget_amount);
    }

    /**
     * Tests the delete of a bcode
     */
    public function testDelete()
    {
        $this->markTestIncomplete("Underway..");

        $this->obj->bind($this->post_data);
        $result = $this->obj->store();
        $this->assertTrue($result);
        $original_id = $this->obj->budget_id;
        $result = $this->obj->delete();

        $item = new CSystem_Budget();
        $item->overrideDatabase($this->mockDB);
        $this->mockDB->stageHash(array('budget_amount' => null, 'budget_start_date' => null));
        $item->load($original_id);

        $this->assertTrue(is_a($item, 'CSystem_Budget'));
        $this->assertEquals('',              $item->budget_amount);
        $this->assertEquals('',              $item->budget_start_date);
    }
}