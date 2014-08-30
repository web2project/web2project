<?php
/**
 * Class for testing folders functionality
 *
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to Clear BSD License. Please see the
 *   LICENSE file in root of site for further details
 *
 * @author      D. Keith Casey, Jr.<caseydk@users.sourceforge.net>
 * @category    CFolders
 * @package     web2project
 * @subpackage  unit_tests
 * @license     Clear BSD
 * @link        http://www.web2project.net
 */

class CFile_FoldersTest extends CommonSetup
{

    protected function setUp()
    {
      parent::setUp();

      $this->obj    = new CFile_Folder();
      $this->obj->overrideDatabase($this->mockDB);

      $GLOBALS['acl'] = new w2p_Mocks_Permissions();

      $this->post_data = array(
          'dosql'                   => 'do_folder_aed',
          'file_folder_id'          => 0,
          'file_folder_parent'      => 0,
          'file_folder_name'        => 'My folder name',
          'file_folder_description' => 'This is a great description'
      );
    }

    public function testObjectProperties()
    {
        parent::objectPropertiesTest('CFile_Folder', 4);
    }

    /**
     * Tests that the proper error message is returned when a link is attempted
     * to be created without a name.
     */
    public function testCreateFolderNoName()
    {
        unset($this->post_data['file_folder_name']);
        $this->obj->bind($this->post_data);

        /**
        * Verify we got the proper error message
        */
        $this->assertFalse($this->obj->store());
        $this->assertArrayHasKey('file_folder_name', $this->obj->getError());

        /**
        * Verify that link id was not set
        */
        $this->AssertEquals(0, $this->obj->file_folder_id);
    }

    /**
     * Tests the proper creation of a link
     */
    public function testStoreCreate()
    {
        $this->obj->bind($this->post_data);
        $result = $this->obj->store();

        $this->assertTrue($result);
        $this->assertEquals('My folder name',              $this->obj->file_folder_name);
        $this->assertEquals(0,                             $this->obj->file_folder_parent);
        $this->assertEquals('This is a great description', $this->obj->file_folder_description);
        $this->assertNotEquals(0,                          $this->obj->file_folder_id);
    }

    /**
     * Tests loading the Link Object
     */
    public function testLoad()
    {
        $this->obj->bind($this->post_data);
        $result = $this->obj->store();
        $this->assertTrue($result);

        $item = new CFile_Folder();
        $item->overrideDatabase($this->mockDB);
        $this->post_data['file_folder_id'] = $this->obj->file_folder_id;
        $this->mockDB->stageHash($this->post_data);
        $item->load($this->obj->file_folder_id);

        $this->assertEquals($this->obj->file_folder_name,        $item->file_folder_name);
        $this->assertEquals($this->obj->file_folder_parent,      $item->file_folder_parent);
        $this->assertEquals($this->obj->file_folder_description, $item->file_folder_description);
    }

    /**
     * Tests the update of a link
     */
    public function testStoreUpdate()
    {
        $this->obj->bind($this->post_data);
        $result = $this->obj->store();
        $this->assertTrue($result);
        $original_id = $this->obj->file_folder_id;

        $this->obj->file_folder_name = 'A new folder';
        $result = $this->obj->store();
        $this->assertTrue($result);
        $new_id = $this->obj->file_folder_id;

        $this->assertEquals($original_id,              $new_id);
        $this->assertEquals('A new folder',            $this->obj->file_folder_name);
    }

    /**
     * Tests the delete of a link
     */
    public function testDelete()
    {
        $this->obj->bind($this->post_data);
        $result = $this->obj->store();
        $this->assertTrue($result);
        $original_id = $this->obj->file_folder_id;
        $result = $this->obj->delete();

        $item = new CFile_Folder();
        $item->overrideDatabase($this->mockDB);
        $this->mockDB->stageHash(array('file_folder_name' => ''));
        $item->load($original_id);

        $this->assertTrue(is_a($item,   'CFile_Folder'));
        $this->assertEquals('',         $item->file_folder_name);
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

    /**
     * @todo Implement testHook_search().
     */
    public function testHook_search()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }
}