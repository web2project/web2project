<?php
/**
 * Class for testing AppUI functionality
 *
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to Clear BSD License. Please see the
 *   LICENSE file in root of site for further details
 *
 * @author      D. Keith Casey, Jr.<caseydk@users.sourceforge.net>
 * @category    w2p_Database_Query
 * @package     web2project
 * @subpackage  unit_tests
 * @license     Clear BSD
 * @link        http://www.web2project.net
 */

class w2p_Database_QueryTest extends CommonSetup
{
    protected function setUp()
    {
        parent::setUp();

        $this->obj = new w2p_Database_Query();
    }

    public function testAddMap()
    {
        $this->obj->addMap('one', 'two', null);

        $this->assertTrue(isset($this->obj->one));
        $this->assertEquals('two', $this->obj->one[0]);
        $this->assertEquals(1, count($this->obj->one));

        $this->obj->addMap('one', 'four', 'three');
        $this->assertTrue(isset($this->obj->one));
        $this->assertEquals(2, count($this->obj->one));
        $this->assertEquals('four', $this->obj->one['three']);

        $this->obj->addMap('one', 'five', 'three');
        $this->assertEquals(2, count($this->obj->one));
        $this->assertEquals('five', $this->obj->one['three']);
    }

    public function testAddInsertSelect()
    {
        $this->obj = $this->obj->addInsertSelect('test');

        $this->assertEquals('insert_select', $this->obj->type);
    }
}