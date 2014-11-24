<?php
/**
 * Class for testing Web2project\Field\Select functionality
 *
 * @author      Keith Casey <contrib@caseysoftware.com>
 * @package     web2project
 * @subpackage  unit_tests
 * @license     Clear BSD
 * @link        http://www.web2project.net
 */

class Web2project_Fields_SelectTest extends CommonSetup
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