<?php
/**
 * Class for testing TitleBlock functionality
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to Clear BSD License. Please see the
 *   LICENSE file in root of site for further details
 *
 * @author      D. Keith Casey, Jr.<caseydk@users.sourceforge.net>
 * @category    w2p_Theme_TitleBlockTest
 * @package     web2project
 * @subpackage  unit_tests
 * @license     Clear BSD
 * @link        http://www.web2project.net
 */

class w2p_Theme_TitleBlockTest extends CommonSetup
{
    public function setUp()
    {
        parent::setUp();

        $this->obj = new w2p_Theme_TitleBlock('Monkey');
    }

    public function testAddCell()
    {
        $this->assertEquals(0, count($this->obj->cells1));

        $this->obj->addCell('one', 'two', 'three', 'four');
        $this->assertEquals(1, count($this->obj->cells1));
    }

    public function testAddSearchCell()
    {
        $this->assertEquals(0, count($this->obj->cells1));

        $this->obj->addSearchCell('search');
        $this->assertEquals(2, count($this->obj->cells1));
    }

    public function testAddFilterCell()
    {
        $this->assertEquals(0, count($this->obj->cells1));

        $this->obj->addFilterCell('label', 'field', array(1, 2, 3), 1);
        $this->assertEquals(2, count($this->obj->cells1));
    }
}