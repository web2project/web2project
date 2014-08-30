<?php
/**
 * Class for testing links functionality
 *
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to Clear BSD License. Please see the
 *   LICENSE file in root of site for further details
 *
 * @author      D. Keith Casey, Jr.<caseydk@users.sourceforge.net>
 * @category    CLinks
 * @package     web2project
 * @subpackage  unit_tests
 * @license     Clear BSD
 * @link        http://www.web2project.net
 */

class CLinksTest extends CommonSetup
{

    protected function setUp()
    {
      parent::setUp();

      $this->obj    = new CLink();
      $this->obj->overrideDatabase($this->mockDB);

      $GLOBALS['acl'] = new w2p_Mocks_Permissions();

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

    public function testObjectProperties()
    {
        parent::objectPropertiesTest('CLink', 11);
    }

    /**
     * Tests that the proper error message is returned when a link is attempted
     * to be created without a name.
     */
    public function testCreateLinkNoName()
    {
        unset($this->post_data['link_name']);
        $this->obj->bind($this->post_data);

        /**
        * Verify we got the proper error message
        */
        $this->assertFalse($this->obj->store());
        $this->assertArrayHasKey('link_name', $this->obj->getError());

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
        $this->post_data['link_url'] = '';
        $this->obj->bind($this->post_data);

        /**
        * Verify we got the proper error message
        */
        $this->assertFalse($this->obj->store());
        $this->assertArrayHasKey('link_url', $this->obj->getError());

        /**
        * Verify that link id was not set
        */
        $this->AssertEquals(0, $this->obj->link_id);
    }

    /**
     * Tests the proper creation of a link
     */
    public function testStoreCreate()
    {
        $this->obj->bind($this->post_data);
        $result = $this->obj->store();

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
        $this->obj->bind($this->post_data);
        $result = $this->obj->store();
        $this->assertTrue($result);

        $item = new CLink();
        $item->overrideDatabase($this->mockDB);
        $this->post_data['link_id'] = $this->obj->link_id;
        $this->mockDB->stageHash($this->post_data);
        $item->load($this->obj->link_id);

        $this->assertEquals($this->obj->link_name,              $item->link_name);
        $this->assertEquals($this->obj->link_project,           $item->link_project);
        $this->assertEquals($this->obj->link_task,              $item->link_task);
        $this->assertEquals($this->obj->link_url,               $item->link_url);
        $this->assertEquals($this->obj->link_parent,            $item->link_parent);
        $this->assertEquals($this->obj->link_description,       $item->link_description);
        $this->assertEquals($this->obj->link_owner,             $item->link_owner);
        $this->assertEquals($this->obj->link_category,          $item->link_category);
        $this->assertNotEquals($this->obj->link_date,           '');
    }

    /**
     * Tests the update of a link
     */
    public function testStoreUpdate()
    {
        $this->obj->bind($this->post_data);
        $result = $this->obj->store();
        $this->assertTrue($result);
        $original_id = $this->obj->link_id;

        $this->obj->link_name = 'web2project Forums';
        $this->obj->link_url = 'http://forums.web2project.net';
        $result = $this->obj->store();
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
    public function testDelete()
    {
        $this->obj->bind($this->post_data);
        $result = $this->obj->store();
        $this->assertTrue($result);
        $original_id = $this->obj->link_id;
        $result = $this->obj->delete();

        $item = new CLink();
        $item->overrideDatabase($this->mockDB);
        $this->mockDB->stageHash(array('link_name' => '', 'link_url' => ''));
        $item->load($original_id);

        $this->assertTrue(is_a($item, 'CLink'));
        $this->assertEquals('',              $item->link_name);
        $this->assertEquals('',              $item->link_url);
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
     * @todo Implement testGetProjectTaskLinksByCategory().
     */
    public function testGetProjectTaskLinksByCategory()
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

    public function testHook_search() {
        $search = $this->obj->hook_search();

        $this->assertTrue(array_key_exists('search_fields', $search));
        $this->assertEquals(count($search), 9);
    }
}
