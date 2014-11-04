<?php
/**
* Class for testing Email Mock functionality
*
* PHP version 5
*
* LICENSE: This source file is subject to Clear BSD License. Please see the
*   LICENSE file in root of site for further details
*
* @author      D. Keith Casey, Jr.<caseydk@users.sourceforge.net>
* @category    w2p_Mocks_Email
* @package     web2project
* @subpackage  unit_tests
* @license     Clear BSD
* @link        http://www.web2project.net
*/

class w2p_Mocks_EmailTest extends CommonSetup
{
    public function testW2Pacl_nuclear()
    {
        $object = new w2p_Mocks_Email();

        $this->assertTrue($object->Send());
    }
}