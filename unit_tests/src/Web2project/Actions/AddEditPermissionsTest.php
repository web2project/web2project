<?php
/**
 * Class for testing \Web2project\Actions\AddEditPermission functionality
 *
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to Clear BSD License. Please see the
 *   LICENSE file in root of site for further details
 *
 * @author      D. Keith Casey, Jr. <caseydk@users.sourceforge.net>
 * @category    \Web2project\Actions\AddEditPermission
 * @package     web2project
 * @subpackage  unit_tests
 * @license     Clear BSD
 * @link        http://www.web2project.net
 */

class Web2project_Actions_AddEditPermissionsTest extends CommonSetup
{
    protected function setUp()
    {
        parent::setUp();

        $GLOBALS['acl'] = new w2p_Mocks_Permissions();

        $this->obj = new \Web2project\Actions\AddEditPermissions($GLOBALS['acl'], false, 'prefix', '/success', '/failure');
    }

    /**
     * Testing process() with first a well-formed POST and then a damaged POST.
     */
    public function testProcessNoPermissions()
    {
        $this->_AppUI->__nonce = '';
        $this->obj->process($this->_AppUI, $this->post_data);
        $this->assertEquals('/failure',   $this->obj->resultPath);
    }
}