<?php

/**
 * Class for testing Web2project\Field\Date functionality
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to Clear BSD License. Please see the
 *   LICENSE file in root of site for further details
 *
 * @author      Keith Casey <contrib@caseysoftware.com>
 * @package     web2project
 * @subpackage  unit_tests
 * @license     Clear BSD
 * @link        http://www.web2project.net
 */

// NOTE: This path is relative to Phing's build.xml, not this test.
include_once 'unit_tests/CommonSetup.php';

class SelectTest extends CommonSetup
{
    protected function setUp()
    {
        parent::setUp();

        $this->obj = new \Web2project\Fields\Select();
    }

    public function testView()
    {
        $output = $this->obj->view('Yes');
        $this->assertEquals('Yes', $output);
    }

    public function testEdit()
    {
        $this->obj->setOptions(array('one', 'two'));
        $output = $this->obj->edit('myName', 'awesome value');
        $this->assertEquals('<select id="myName" name="myName" size="1" ><option value="0">one</option><option value="1">two</option></select>', $output);
    }
}