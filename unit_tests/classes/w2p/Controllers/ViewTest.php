<?php
/**
 * Class for testing w2p_Controllers_View functionality
 *
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to Clear BSD License. Please see the
 *   LICENSE file in root of site for further details
 *
 * @author      D. Keith Casey, Jr.<caseydk@users.sourceforge.net>
 * @category    w2p_Controllers_ViewTest
 * @package     web2project
 * @subpackage  unit_tests
 * @license     Clear BSD
 * @link        http://www.web2project.net
 */

class w2p_Controllers_ViewTest extends CommonSetup
{
    public function setUp()
    {
        parent::setUp();

        $this->obj = new w2p_Controllers_View($this->_AppUI, new \Web2project\Mocks\BaseObject(), 'baseobject');
    }

    public function testRenderDelete()
    {
        $output = $this->obj->renderDelete();

        $this->assertGreaterThan(0, strpos($output, 'do_baseobject_aed'));
        $this->assertGreaterThan(0, strpos($output, 'action="?m=baseobjects"'));
        $this->assertGreaterThan(0, strpos($output, 'name="baseobject_id"'));
    }

    public function testSetDoSQL()
    {
        $this->obj->setDoSQL('do_something_aed');
        $output = $this->obj->renderDelete();

        $this->assertGreaterThan(0, strpos($output, 'do_something_aed'));
        $this->assertGreaterThan(0, strpos($output, 'action="?m=baseobjects"'));
        $this->assertGreaterThan(0, strpos($output, 'name="baseobject_id"'));
    }

    public function testSetKey()
    {
        $this->obj->setKey('something_id');
        $output = $this->obj->renderDelete();

        $this->assertGreaterThan(0, strpos($output, 'do_baseobject_aed'));
        $this->assertGreaterThan(0, strpos($output, 'action="?m=baseobjects"'));
        $this->assertGreaterThan(0, strpos($output, 'name="something_id"'));
    }

    public function testAddField()
    {
        $this->obj->addField('somethingelse', 123);
        $output = $this->obj->renderDelete();

        $this->assertGreaterThan(0, strpos($output, 'do_baseobject_aed'));
        $this->assertGreaterThan(0, strpos($output, 'action="?m=baseobjects"'));
        $this->assertGreaterThan(0, strpos($output, 'name="baseobject_id"'));
        $this->assertGreaterThan(0, strpos($output, 'name="somethingelse"'));
    }
}