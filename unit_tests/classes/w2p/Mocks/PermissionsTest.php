<?php
/**
 * Class for testing Permissions Mock functionality
 *
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to Clear BSD License. Please see the
 *   LICENSE file in root of site for further details
 *
 * @author      D. Keith Casey, Jr.<caseydk@users.sourceforge.net>
 * @category    w2p_Mocks_Permissions
 * @package     web2project
 * @subpackage  unit_tests
 * @license     Clear BSD
 * @link        http://www.web2project.net
 */

class w2p_Mocks_PermissionsTest extends CommonSetup
{
    protected $perms = null;

    protected function setUp()
    {
        parent::setUp();
        $this->perms = new w2p_Mocks_Permissions();
    }

    public function testW2Pacl_nuclear()
    {
        $results = $this->perms->w2Pacl_nuclear(1, 'anymodule', new stdClass());

        $this->assertEquals(2,         count($results));
        $this->assertEquals(1,         $results['access']);
        $this->assertEquals('checked', $results['acl_id']);
    }
}
