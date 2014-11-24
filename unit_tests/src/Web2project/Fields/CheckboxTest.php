<?php
/**
 * Class for testing Web2project\Field\Checkbox functionality
 *
 * @author      Keith Casey <contrib@caseysoftware.com>
 * @package     web2project
 * @subpackage  unit_tests
 * @license     Clear BSD
 * @link        http://www.web2project.net
 */

class Web2project_Fields_CheckboxTest extends CommonSetup
{
    protected function setUp()
    {
        parent::setUp();

        $this->obj = new \Web2project\Fields\Checkbox();
    }

    public function testView()
    {
        $output = $this->obj->view('Yes');
        $this->assertEquals('Yes', $output);
    }

    public function testEdit()
    {
        $output = $this->obj->edit('theName', 'checked="checked"', ' id="theId"');
        $this->assertEquals('<input type="checkbox" name="theName" value="1" checked="checked" id="theId"/>', $output);
    }
}