<?php
/**
 * Class for testing Permissions functionality
 *
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to Clear BSD License. Please see the
 *   LICENSE file in root of site for further details
 *
 * @author      D. Keith Casey, Jr.<caseydk@users.sourceforge.net>
 * @category    w2p_Extensions_Permissions
 * @package     web2project
 * @subpackage  unit_tests
 * @license     Clear BSD
 * @link        http://www.web2project.net
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
}
