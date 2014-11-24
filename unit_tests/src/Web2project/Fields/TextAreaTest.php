<?php
/**
 * Class for testing Web2project\Field\TextArea functionality
 *
 * @author      Keith Casey <contrib@caseysoftware.com>
 * @package     web2project
 * @subpackage  unit_tests
 * @license     Clear BSD
 * @link        http://www.web2project.net
 */

class Web2project_Fields_TextAreaTest extends CommonSetup
{
    protected function setUp()
    {
        parent::setUp();

        $this->obj = new \Web2project\Fields\TextArea();
    }

    public function testView()
    {
        $output = $this->obj->view('This is an awesome string of text with a link: http://google.com');
        $this->assertEquals('This is an awesome string of text with a link: <a href="http://google.com" target="_blank">http://google.com</a>', $output);
    }

    public function testEdit()
    {
        $output = $this->obj->edit('name', 'some value', 'id="xxx"');
        $this->assertEquals('<textarea name="name" id="xxx">some value</textarea>', $output);
    }
}