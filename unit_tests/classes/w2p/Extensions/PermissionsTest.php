<?php
/**
 * Class for testing Permissions functionality
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to Clear BSD License. Please see the
 *   LICENSE file in root of site for further details
 *
 * @author     D. Keith Casey, Jr.<caseydk@users.sourceforge.net>
 * @category   w2p_Extensions_Permissions
 * @package    web2project
 * @subpackage unit_tests
 * @license    Clear BSD
 * @link       http://www.web2project.net
 */

class w2p_Extensions_PermissionsTest extends CommonSetup
{
    public function testDebugText()
    {
        $perms = new w2p_Extensions_Permissions();

        $this->assertInstanceOf('w2p_Extensions_Permissions', $perms);
        $perms->debug_text('test message');

        $this->assertEquals('test message', $perms->msg());
    }

    /**
     * GHSA-qqx6-f7cc-3wf2: the role id must be a real role, never a raw
     * request value, the 'role' root group or another ARO group.
     */
    public function testCanChangeUserRoleRejectsInvalidRoles()
    {
        $perms = $this->_AppUI->acl();
        $user_id = (int) $this->_AppUI->user_id;

        $this->assertFalse($perms->canChangeUserRole(0, $user_id));
        $this->assertFalse($perms->canChangeUserRole('abc', $user_id));
        $this->assertFalse($perms->canChangeUserRole(-1, $user_id));
        $this->assertFalse($perms->canChangeUserRole(999999, $user_id));
        $this->assertFalse($perms->canChangeUserRole($perms->get_group_id('role'), $user_id));
        $this->assertFalse($perms->canChangeUserRole($perms->get_group_id('normal'), 0));
    }

    /**
     * An account with System edit rights (the test admin) may grant any
     * role, Administrator included, to any account it can edit.
     */
    public function testCanChangeUserRoleAllowsSystemAdmin()
    {
        $perms = $this->_AppUI->acl();
        $user_id = (int) $this->_AppUI->user_id;

        $this->assertTrue($perms->canChangeUserRole($perms->get_group_id('normal'), $user_id));
        $this->assertTrue($perms->canChangeUserRole($perms->get_group_id('admin'), $user_id));
        $this->assertTrue($perms->canChangeUserRole($perms->get_group_id('admin'), $user_id, true));
    }

    /**
     * The role id goes into phpGACL SQL unescaped, so anything that is not
     * a positive integer must be refused before it gets there.
     */
    public function testUserRoleWritesRejectNonIntegerIds()
    {
        $perms = $this->_AppUI->acl();
        $user_id = (int) $this->_AppUI->user_id;

        $this->assertFalse($perms->insertUserRole('0 OR 1=1', $user_id));
        $this->assertFalse($perms->insertUserRole($perms->get_group_id('normal'), 'x'));
        $this->assertFalse($perms->deleteUserRole('', $user_id));
        $this->assertFalse($perms->deleteUserRole($perms->get_group_id('normal'), 0));
    }
}
