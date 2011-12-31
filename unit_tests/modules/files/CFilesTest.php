<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

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
 * @copyright   2007-2012 The web2Project Development Team <w2p-developers@web2project.net>
 * @license     Clear BSD
 * @link        http://www.web2project.net
 */

// NOTE: This path is relative to Phing's build.xml, not this test.
include_once 'CommonSetup.php';

class CFiles_Test extends CommonSetup
{

    protected function setUp()
    {
      parent::setUp();

      $this->obj = new CFile();
      $this->mockDB = new w2p_Mocks_Query();
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

    /**
     * Tests the Attributes of a new Files object.
     */
    public function testNewFilesAttributes()
    {
      $file = new CFile();

      $this->assertInstanceOf('CFile',                            $file);
      $this->assertObjectHasAttribute('file_id',                  $file);
      $this->assertObjectHasAttribute('file_real_filename',       $file);
      $this->assertObjectHasAttribute('file_project',             $file);
      $this->assertObjectHasAttribute('file_task',                $file);
      $this->assertObjectHasAttribute('file_name',                $file);
      $this->assertObjectHasAttribute('file_parent',              $file);
      $this->assertObjectHasAttribute('file_description',         $file);
      $this->assertObjectHasAttribute('file_type',                $file);
      $this->assertObjectHasAttribute('file_owner',               $file);
      $this->assertObjectHasAttribute('file_date',                $file);
      $this->assertObjectHasAttribute('file_size',                $file);
      $this->assertObjectHasAttribute('file_version',             $file);
      $this->assertObjectHasAttribute('file_icon',                $file);
      $this->assertObjectHasAttribute('file_category',            $file);
      $this->assertObjectHasAttribute('file_checkout',            $file);
      $this->assertObjectHasAttribute('file_co_reason',           $file);
      $this->assertObjectHasAttribute('file_folder',              $file);
      $this->assertObjectHasAttribute('file_indexed',             $file);
    }

    /**
     * Tests the Attribute Values of a new File object.
     */
    public function testNewFilesAttributeValues()
    {
      $file = new CFile();

      $this->assertInstanceOf('CFile', $file);
      $this->assertNull($file->file_id);
      $this->assertNull($file->file_real_filename);
      $this->assertNull($file->file_project);
      $this->assertNull($file->file_task);
      $this->assertNull($file->file_name);
      $this->assertNull($file->file_parent);
      $this->assertNull($file->file_description);
      $this->assertNull($file->file_type);
      $this->assertNull($file->file_owner);
      $this->assertNull($file->file_date);
      $this->assertNull($file->file_size);
      $this->assertNull($file->file_version);
      $this->assertNull($file->file_icon);
      $this->assertNull($file->file_category);
      $this->assertNull($file->file_checkout);
      $this->assertNull($file->file_co_reason);
      $this->assertNull($file->file_folder);
      $this->assertNull($file->file_indexed);
    }

    /**
     * Tests that the proper error message is returned when no filename is
     * passed.
     */
    public function testCreateFileNoRealName()
    {
      global $AppUI;

      unset($this->post_data['file_real_filename']);
      $this->obj->bind($this->post_data);
      $errorArray = $this->obj->store($AppUI);

      /**
       * Verify we got the proper error message
       */
      $this->AssertEquals(1, count($errorArray));
      $this->assertArrayHasKey('file_real_filename', $errorArray);

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
      global $AppUI;

      unset($this->post_data['file_name']);
      $this->obj->bind($this->post_data);
      $errorArray = $this->obj->store($AppUI);

      /**
       * Verify we got the proper error message
       */
      $this->AssertEquals(1, count($errorArray));
      $this->assertArrayHasKey('file_name', $errorArray);

      /**
       * Verify that project id was not set
       */
      $this->AssertEquals(0, $this->obj->file_id);
    }

    /**
     * Tests that the proper error message is returned when no parent is passed.
     */
    public function testCreateFileNoParent()
    {
      global $AppUI;

      unset($this->post_data['file_parent']);
      $this->obj->bind($this->post_data);
      $errorArray = $this->obj->store($AppUI);

      /**
       * Verify we got the proper error message
       */
      $this->AssertEquals(1, count($errorArray));
      $this->assertArrayHasKey('file_parent', $errorArray);

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
      global $AppUI;

      unset($this->post_data['file_type']);
      $this->obj->bind($this->post_data);
      $errorArray = $this->obj->store($AppUI);

      /**
       * Verify we got the proper error message
       */
      $this->AssertEquals(1, count($errorArray));
      $this->assertArrayHasKey('file_type', $errorArray);

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
      global $AppUI;

      unset($this->post_data['file_size']);
      $this->obj->bind($this->post_data);
      $errorArray = $this->obj->store($AppUI);

      /**
       * Verify we got the proper error message
       */
      $this->AssertEquals(1, count($errorArray));
      $this->assertArrayHasKey('file_size', $errorArray);

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
      global $AppUI;

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
      $this->AssertEquals(5, count($errorArray));
      $this->assertArrayHasKey('file_real_filename', $errorArray);
      $this->assertArrayHasKey('file_name',          $errorArray);
      $this->assertArrayHasKey('file_parent',        $errorArray);
      $this->assertArrayHasKey('file_type',          $errorArray);
      $this->assertArrayHasKey('file_size',          $errorArray);
    }

    public function testDelete()
    {
        global $AppUI;

        $this->obj->bind($this->post_data);
        $result = $this->obj->store($AppUI);
        $this->assertTrue($result);
        $original_id = $this->obj->link_id;
        $result = $this->obj->delete($AppUI);

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
        global $AppUI;

        $this->obj->bind($this->post_data);
        $result = $this->obj->store($AppUI);
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
        global $AppUI;

        $this->obj->bind($this->post_data);
        $result = $this->obj->store($AppUI);

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
      global $AppUI;

      $this->obj->bind($this->post_data);
      $result = $this->obj->store($AppUI);
      $this->assertTrue($result);
      $original_id = $this->obj->file_id;
      $original_date = $this->obj->file_date;   // Once created, this should not change

      $this->obj->file_name = 'Some new file name';
      $this->obj->file_description = 'A new file description';
      $result = $this->obj->store($AppUI);
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
     * @todo Implement testGetFileList().
     */
    public function testGetFileList() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testAddHelpDeskTaskLog().
     */
    public function testAddHelpDeskTaskLog() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testCanAdmin().
     */
    public function testCanAdmin() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testCheckout().
     */
    public function testCheckout() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testCancelCheckout().
     */
    public function testCancelCheckout() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testDeleteFile().
     */
    public function testDeleteFile() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testMoveFile().
     */
    public function testMoveFile() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testDuplicateFile().
     */
    public function testDuplicateFile() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testMoveTemp().
     */
    public function testMoveTemp() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testIndexStrings().
     */
    public function testIndexStrings() {
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
     * @todo Implement testNotifyContacts().
     */
    public function testNotifyContacts() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testGetOwner().
     */
    public function testGetOwner() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testGetTaskName().
     */
    public function testGetTaskName() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }
}