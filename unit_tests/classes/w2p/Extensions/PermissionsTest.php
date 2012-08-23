<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

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
 * @copyright   2007-2012 The web2Project Development Team <w2p-developers@web2project.net>
 * @license     Clear BSD
 * @link        http://www.web2project.net
 */

// NOTE: This path is relative to Phing's build.xml, not this test.
include_once 'unit_tests/CommonSetup.php';

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