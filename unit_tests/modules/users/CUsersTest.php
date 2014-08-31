<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Class for testing admin/users functionality
 *
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to Clear BSD License. Please see the
 *   LICENSE file in root of site for further details
 *
 * @author      D. Keith Casey, Jr.<caseydk@users.sourceforge.net>
 * @category    CUsers
 * @package     web2project
 * @subpackage  unit_tests
 * @license     Clear BSD
 * @link        http://www.web2project.net
 */

class CUsersTest extends CommonSetup
{

    protected function setUp()
    {
      parent::setUp();

      global $AppUI;
      $AppUI->user_id = 1;

      $this->obj    = new CUser();
      $this->obj->overrideDatabase($this->mockDB);

      $GLOBALS['acl'] = new w2p_Mocks_Permissions();

      $this->post_data = array(
          'dosql'              => 'do_user_aed',
          'user_id'            => 0,
          'user_username'      => 'myusername',
          'user_password'      => 'myPassword',
          'user_type'          => 1,
          'user_signature'     => 'My Signature',
          'password_check'     => 'myPassword',
          'contact_id'         => 0,
          'contact_first_name' => 'Myfirstname',
          'contact_last_name'  => 'Mylastname',
          'contact_company'    => 0,
          'contact_department' => 0,
          'contact_email'      => 'web2project@test.com'
      );
    }

    public function testObjectProperties()
    {
        parent::objectPropertiesTest('CUser', 7);
    }

    /**
     * Tests that the proper error message is returned when a link is attempted
     * to be created without a name.
     */
    public function testCreateUserNoPassword()
    {
        unset($this->post_data['user_password']);
        $this->obj->bind($this->post_data);

        /**
         * Verify we got the proper error message
         */
        $this->assertFalse($this->obj->store());
        $this->assertArrayHasKey('user_password', $this->obj->getError());

        /**
        * Verify that user_id was not set
        */
        $this->assertEquals(0, $this->obj->user_id);
    }

    /**
     * Tests that the proper error message is returned we try to create a user
     *   with the same username as another.
     */
    public function testCreateUserExists()
    {
        $this->markTestIncomplete("Unfortunately, we can't test this one because
            the CUser::exists() method is static and we don't have a way
            to override its database call.");
    }

    /**
     * Tests the proper creation of a user & contact
     */
    public function testStoreCreate()
    {
        $this->obj->bind($this->post_data);
        $result = $this->obj->store();

        $contact = new CContact();
        $contact->overrideDatabase($this->mockDB);
        $contact->bind($this->post_data);
        $result = $contact->store();

        $this->assertTrue($result);
        $this->assertNotEquals(0,                   $contact->contact_id);

        $this->obj->user_contact = $contact->contact_id;
        $result = $this->obj->store();

        $this->assertTrue($result);
        $this->assertNotEquals(0,                   $this->obj->user_id);
    }

    public function testExists()
    {
        $result = $this->obj->user_exists('admin');
        $this->assertFalse($result);

        $this->mockDB->stageHashList(1, $this->post_data);
        $result = $this->obj->user_exists('admin');
        $this->assertTrue($result);
    }

    public function testGetIdByContactId()
    {
        // Don't load a hashlist so the lookup fails
        $result = $this->obj->getIdByContactId(1);
        $this->assertEquals('',                     $result);

        $this->post_data['user_id'] = 1;
        $this->mockDB->stageHashList(1, $this->post_data);
        $result = $this->obj->getIdByContactId(1);
        $this->assertEquals(1,                      $result);
    }

    public function testValidatePassword()
    {
        // Don't load a dataset so the validation fails.
        $result = $this->obj->validatePassword(1, 'password');
        $this->assertFalse($result);

        $this->mockDB->stageHashList(1, $this->post_data);
        $result = $this->obj->validatePassword(1, 'password');
        $this->assertTrue($result);
    }

    /*
     * These tests are all marked as Skipped because we don't have a way of
     *   testing the static methods without hitting the database.
     */
    public function testGenerateUserToken() {       $this->markTestSkipped(); }
    public function testGetFirstLetters() {         $this->markTestSkipped(); }
    public function testGetLogs() {                 $this->markTestSkipped(); }
    public function testGetUserDeptId() {           $this->markTestSkipped(); }
    public function testGetUserIdByToken() {        $this->markTestSkipped(); }
    public function testGetUserList() {             $this->markTestSkipped(); }
    public function testIsUserActive() {            $this->markTestSkipped(); }

    /**
     * These tests are incomplete.
     */
    public function testCanDelete() {               $this->markTestIncomplete(); }
    public function testDelete() {                  $this->markTestIncomplete(); }
    public function testGetAllowedRecords() {       $this->markTestIncomplete(); }
    public function testGetFullUserName() {         $this->markTestIncomplete(); }
    public function testLoadFull() {                $this->markTestIncomplete(); }
}
