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
    public function setUp()
    {
        parent::setUp();

        $this->obj = new w2p_Utilities_Mail();
    }

    public function testW2Pacl_nuclear()
    {
        $object = new \Web2project\Mocks\Email();

        $this->assertTrue($object->Send());
    }

    public function testSubject()
    {
        $this->assertEquals('', $this->obj->Subject);

        $this->obj->Subject('This is a subject');
        $this->assertEquals('[web2Project] This is a subject', $this->obj->Subject);
    }

    public function testFrom()
    {
        $result = $this->obj->From(null);
        $this->assertFalse($result);
    }

    public function testReplyTo()
    {
        $result = $this->obj->From(null);
        $this->assertFalse($result);
    }

    public function testReceipt()
    {
        $this->obj->Receipt();
        $this->assertTrue($this->obj->receipt);
    }

    public function testValidEmail()
    {
        $result = $this->obj->ValidEmail('admin@web2project.net');
        $this->assertTrue($result);

        $result = $this->obj->ValidEmail('admin+web2project.net');
        $this->assertFalse($result);

        $result = $this->obj->ValidEmail('admin');
        $this->assertFalse($result);

        $result = $this->obj->ValidEmail('web2project.net');
        $this->assertFalse($result);
    }

    public function testPriority()
    {
        $this->obj->Priority(3);
        $this->assertEquals(3, $this->obj->Priority);

        $this->obj->Priority(0);
        $this->assertEquals(1, $this->obj->Priority);

        $this->obj->Priority(12);
        $this->assertEquals(5, $this->obj->Priority);
    }
}