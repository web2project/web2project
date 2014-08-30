<?php
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
 * @license     Clear BSD
 * @link        http://www.web2project.net
 */

class CSystem_BcodeTest extends CommonSetup
{

    protected function setUp()
    {
      parent::setUp();

      $this->obj    = new CSystem_Bcode();
      $this->obj->overrideDatabase($this->mockDB);

      $GLOBALS['acl'] = new w2p_Mocks_Permissions();

      $this->post_data = array(
          'dosql'                => 'do_billingcode_aed',
          'billingcode_id'       => 0,
          'billingcode_company'  => 1,
          'billingcode_desc'     => 'A description',
          'billingcode_name'     => 'bc-name',
          'billingcode_value'    => 5000,
          'billingcode_status'   => 1,
          'billingcode_category' => 1
      );
    }

    public function testObjectProperties()
    {
        parent::objectPropertiesTest('CSystem_Bcode', 8);
    }

    /**
     * Tests that the proper error message is returned when a budget of zero
     *   is attempted.
     */
    public function testCreateDuplicateCode()
    {
        $this->mockDB->stageResult(7);
        $this->obj->bind($this->post_data);

        /**
        * Verify we got the proper error message
        */
        $this->assertFalse($this->obj->store());
        $this->assertArrayHasKey('billingcode_name', $this->obj->getError());

        /**
        * Verify that the id was not set
        */
        $this->AssertEquals(0, $this->obj->billingcode_id);
    }

    /**
     * Tests the proper creation of a bcode
     */
    public function testStoreCreate()
    {
        $this->obj->bind($this->post_data);
        $result = $this->obj->store();

        $this->assertTrue($result);
        $this->assertEquals('bc-name',          $this->obj->billingcode_name);
        $this->assertEquals('A description',    $this->obj->billingcode_desc);
        $this->assertNotEquals(0,               $this->obj->billingcode_id);
    }

    /**
     * Tests loading the CSystem_Bcode Object
     */
    public function testLoad()
    {
        $this->obj->bind($this->post_data);
        $result = $this->obj->store();
        $this->assertTrue($result);

        $item = new CSystem_Bcode();
        $item->overrideDatabase($this->mockDB);
        $this->post_data['billingcode_id'] = $this->obj->billingcode_id;
        $this->mockDB->stageHash($this->post_data);
        $item->load($this->obj->billingcode_id);

        $this->assertEquals($this->obj->billingcode_id,     $item->billingcode_id);
        $this->assertEquals($this->obj->billingcode_name,   $item->billingcode_name);
        $this->assertEquals($this->obj->billingcode_desc,   $item->billingcode_desc);
    }

    /**
     * Tests the update of a CSystem_Bcode Object
     */
    public function testStoreUpdate()
    {
        $this->obj->bind($this->post_data);
        $result = $this->obj->store();
        $this->assertTrue($result);
        $original_id = $this->obj->billingcode_id;

        $this->obj->billingcode_value       = 8000;

        $result = $this->obj->store();
        $this->assertTrue($result);
        $new_id = $this->obj->billingcode_id;

        $this->assertEquals($original_id, $new_id);
        $this->assertEquals(8000,         $this->obj->billingcode_value);
    }

    /**
     * Tests the delete of a bcode
     */
    public function testDelete()
    {
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
