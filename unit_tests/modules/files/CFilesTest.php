<?php
/**
 * Class for testing files functionality
 *
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to Clear BSD License. Please see the
 *   LICENSE file in root of site for further details
 *
 * @author      D. Keith Casey, Jr.<caseydk@users.sourceforge.net>
 * @category    CFiles
 * @package     web2project
 * @subpackage  unit_tests
 * @license     Clear BSD
 * @link        http://www.web2project.net
 */

class CFilesTest extends CommonSetup
{

    protected function setUp()
    {
      parent::setUp();

      $this->obj = new CFile();
      $this->obj->overrideDatabase($this->mockDB);

      $GLOBALS['acl'] = new w2p_Mocks_Permissions();

      $this->post_data = array(
          'dosql' =>              'do_file_aed',
          'file_id' =>            0,
          'file_real_filename' => 'TheRealFileName',
          'file_project' =>       '0',
          'file_task' =>          '0',
          'file_name' =>          'thisIsTheFilename',
          'file_parent' =>        1,
          'file_description' =>   'File description',
          'file_type' =>          'image/jpeg',
          'file_owner' =>         '1',
          'file_size' =>          '0',
          'file_version' =>       1,
          'file_version_id' =>    1,
          'file_icon' =>          '',
          'file_category' =>      0,
          'file_checkout' =>      0,
          'file_co_reason' =>     '',
          'file_folder' =>        0,
          'file_indexed' =>       0
      );
    }

    public function testObjectProperties()
    {
        parent::objectPropertiesTest('CFile', 19);
    }

    /**
     * Tests that the proper error message is returned when no filename is
     * passed.
     */
    public function testCreateFileNoRealName()
    {
        unset($this->post_data['file_real_filename']);
        $this->obj->bind($this->post_data);

        /**
        * Verify we got the proper error message
        */
        $this->assertFalse($this->obj->store());
        $this->assertArrayHasKey('file_real_filename', $this->obj->getError());

        /**
        * Verify that project id was not set
        */
        $this->AssertEquals(0, $this->obj->file_id);
    }

    /**
     * Tests that the proper error message is returned when no filename is
     * passed.
     */
    public function testCreateFileNoName()
    {
        unset($this->post_data['file_name']);
        $this->obj->bind($this->post_data);

        /**
        * Verify we got the proper error message
        */
        $this->assertFalse($this->obj->store());
        $this->assertArrayHasKey('file_name', $this->obj->getError());

        /**
        * Verify that project id was not set
        */
        $this->AssertEquals(0, $this->obj->file_id);
    }

    /**
     * Tests that the proper error message is returned when no type is passed.
     */
    public function testCreateFileNoType()
    {
        unset($this->post_data['file_type']);
        $this->obj->bind($this->post_data);

        /**
        * Verify we got the proper error message
        */
        $this->assertFalse($this->obj->store());
        $this->assertArrayHasKey('file_type', $this->obj->getError());

        /**
        * Verify that project id was not set
        */
        $this->AssertEquals(0, $this->obj->file_id);
    }

    /**
     * Tests that the proper error message is returned when no filesize is
     * passed.
     */
    public function testCreateFileNoFilesize()
    {
        unset($this->post_data['file_size']);
        $this->obj->bind($this->post_data);

        /**
        * Verify we got the proper error message
        */
        $this->assertFalse($this->obj->store());
        $this->assertArrayHasKey('file_size', $this->obj->getError());

        /**
        * Verify that project id was not set
        */
        $this->AssertEquals(0, $this->obj->file_id);
    }

    /**
     * Tests that the proper error messages are returned for the full check.
     */
    public function testCheck()
    {
        unset($this->post_data['file_real_filename']);
        unset($this->post_data['file_name']);
        unset($this->post_data['file_parent']);
        unset($this->post_data['file_type']);
        unset($this->post_data['file_size']);
        $this->obj->bind($this->post_data);
        $errorArray = $this->obj->check();

        /**
        * Verify we got the proper error message
        */
        $this->AssertEquals(4, count($errorArray));
        $this->assertArrayHasKey('file_real_filename', $errorArray);
        $this->assertArrayHasKey('file_name',          $errorArray);
        $this->assertArrayHasKey('file_type',          $errorArray);
        $this->assertArrayHasKey('file_size',          $errorArray);
    }

    public function testDelete()
    {
        $this->obj->bind($this->post_data);
        $result = $this->obj->store();
        $this->assertTrue($result);
        $original_id = $this->obj->link_id;
        $result = $this->obj->delete();

        $item = new CFile();
        $item->overrideDatabase($this->mockDB);
        $this->mockDB->stageHash(
                array('file_real_filename' => '', 'file_name' => '', 'file_parent' => '',
                    'file_type' => '', 'file_size' => '')
        );
        $item->load($original_id);

        $this->assertTrue(is_a($item, 'CFile'));
        $this->assertEquals('',              $item->link_name);
        $this->assertEquals('',              $item->link_url);
    }

    public function testLoad()
    {
        $this->obj->bind($this->post_data);
        $result = $this->obj->store();
        $this->assertTrue($result);

        $item = new CFile();
        $item->overrideDatabase($this->mockDB);
        $this->post_data['file_id'] = $this->obj->file_id;
        $this->mockDB->stageHash($this->post_data);
        $item->load($this->obj->file_id);

        $this->assertEquals($this->obj->file_name,              $item->file_name);
        $this->assertEquals($this->obj->file_real_filename,     $item->file_real_filename);
        $this->assertEquals($this->obj->file_parent,            $item->file_parent);
        $this->assertEquals($this->obj->file_description,       $item->file_description);
        $this->assertNotEquals($this->obj->file_date,           '');
    }

    /**
     * Tests the proper creation of a file
     */
    public function testStoreCreate()
    {
        $this->obj->bind($this->post_data);
        $result = $this->obj->store();

        $this->assertTrue($result);
        $this->assertEquals('TheRealFileName',   $this->obj->file_real_filename);
        $this->assertEquals('thisIsTheFilename', $this->obj->file_name);
        $this->assertEquals(1,                   $this->obj->file_parent);
        $this->assertEquals('File description',  $this->obj->file_description);
        $this->assertNotEquals(0,                $this->obj->file_id);
    }

    /**
     * Tests the update of a file
     */
    public function testStoreUpdate()
    {
      $this->obj->bind($this->post_data);
      $result = $this->obj->store();
      $this->assertTrue($result);
      $original_id = $this->obj->file_id;
      $original_date = $this->obj->file_date;   // Once created, this should not change

      $this->obj->file_name = 'Some new file name';
      $this->obj->file_description = 'A new file description';
      $result = $this->obj->store();
      $this->assertTrue($result);
      $new_id = $this->obj->file_id;

      $this->assertEquals($original_id,             $new_id);
      $this->assertEquals($original_date,           $this->obj->file_date);
      $this->assertEquals('Some new file name',     $this->obj->file_name);
      $this->assertEquals('A new file description', $this->obj->file_description);
    }

    /**
     * @todo Implement testHook_cron().
     */
    public function testHook_cron()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    public function testHook_search() {
        $search = $this->obj->hook_search();

        $this->assertTrue(array_key_exists('search_fields', $search));
        $this->assertEquals(count($search), 10);
    }

    /**
     * @todo Implement testGetFileList().
     */
    public function testGetFileList()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testAddHelpDeskTaskLog().
     */
    public function testAddHelpDeskTaskLog()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testCanAdmin().
     */
    public function testCanAdmin()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testCheckout().
     */
    public function testCheckout()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testCancelCheckout().
     */
    public function testCancelCheckout()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testDeleteFile().
     */
    public function testDeleteFile()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testMoveFile().
     */
    public function testMoveFile()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testDuplicateFile().
     */
    public function testDuplicateFile()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testMoveTemp().
     */
    public function testMoveTemp()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testIndexStrings().
     */
    public function testIndexStrings()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testNotify().
     */
    public function testNotify()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testNotifyContacts().
     */
    public function testNotifyContacts()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testGetOwner().
     */
    public function testGetOwner()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testGetTaskName().
     */
    public function testGetTaskName()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }
}
