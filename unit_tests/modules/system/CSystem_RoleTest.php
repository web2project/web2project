<?php
/**
 * Class for testing role functionality
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to Clear BSD License. Please see the
 *   LICENSE file in root of site for further details
 *
 * @author      D. Keith Casey, Jr.<caseydk@users.sourceforge.net>
 * @category    CSystem_Role
 * @package     web2project
 * @subpackage  unit_tests
 * @license     Clear BSD
 * @link        http://www.web2project.net
 */

class CSystem_RoleTest extends CommonSetup
{
    protected function setUp()
    {
        parent::setUp();

        $this->obj    = new CSystem_Role('name', 'description');
        $this->obj->overrideDatabase($this->mockDB);

        $this->post_data = array(
            'dosql'             => 'do_role_aed',
            'role_id'           => 0,
            'role_name'         => 'A name',
            'role_description'  => 'A description'
        );
    }

    public function testObjectProperties()
    {
        $unset = array('_AppUI');
        $this->obj->role_name = null;
        $this->obj->role_description = null;
        $this->obj->perms = null;

        parent::objectPropertiesTest('CSystem_Role', 4, $unset);
    }

    public function testBind()
    {
        $this->obj->bind($this->post_data);

        $this->assertEquals($this->obj->role_name,          $this->post_data['role_name']);
        $this->assertEquals($this->obj->role_description,   $this->post_data['role_description']);
    }

    public function testBindFail()
    {
        $this->assertFalse($this->obj->bind(null));
    }

    public function testCheck()
    {
        $this->AssertEquals(0,                      count($this->obj->check()));
    }

    public function testSleep()
    {
        $result = $this->obj->__sleep();

        $this->assertContains('role_id',            $result);
        $this->assertContains('role_name',          $result);
        $this->assertContains('role_description',   $result);
    }

    /**
     * @todo Implement testStore().
     */
    public function testStore()
    {
        $this->obj->bind($this->post_data);

        $this->AssertEquals(0,                      $this->obj->role_id);
        $this->obj->store();
        $this->assertNotEquals(0,                   (int) $this->obj->role_id);
        $this->obj->delete();
    }

    /**
     * @todo Implement testDelete().
     */
    public function testDelete()
    {
        $roles = $this->obj->getRoles();

        $this->AssertEquals(5,                      count($roles));
        $this->obj->bind($this->post_data);
        $this->obj->store();
        $this->obj->delete();

        $roles = $this->obj->getRoles();
        $this->AssertEquals(5,                      count($roles));
    }

    public function testGetRoles()
    {
        $roles = $this->obj->getRoles();

        $this->AssertEquals(5,                      count($roles));
        $this->AssertEquals('Administrator',        $roles[0]['name']);
        $this->AssertEquals('normal',               $roles[4]['value']);
    }

    public function testRename_array_fail()
    {
        $roles = $this->obj->getRoles();

        $result = $this->obj->rename_array($roles, array('one'), array());

        $this->assertFalse($result);
    }

    public function testRename_array()
    {
        $roles = $this->obj->getRoles();

        $result = $this->obj->rename_array($roles, array('one'), array('two'));

        $this->assertTrue($result);
    }

    public function testCopyPermissions()
    {
        // Remove the following lines when you implement this test.
        $this->assertTrue($this->obj->copyPermissions(20, 21));
    }

    public function testCopyPermissionsFail()
    {
        $this->assertFalse($this->obj->copyPermissions(null, 1));
    }
}
