<?php
/**
 * Class for testing Web2project\Field\Text functionality
 *
 * @author      Keith Casey <contrib@caseysoftware.com>
 * @package     web2project
 * @subpackage  unit_tests
 * @license     Clear BSD
 * @link        http://www.web2project.net
 */

// NOTE: This path is relative to Phing's build.xml, not this test.
include_once 'unit_tests/CommonSetup.php';

class TextTest extends CommonSetup
{
    protected function setUp()
    {
        parent::setUp();

        $this->obj = new \Web2project\Fields\Text();
    }

    public function testView()
    {
        $output = $this->obj->view('test');
        $this->assertEquals('test', $output);

        $output = $this->obj->view('test w/ a link: http://web2project.net');
        $this->assertEquals('test w/ a link: <a href="http://web2project.net" target="_blank">http://web2project.net</a>', $output);
    }

    public function testEdit()
    {
        $output = $this->obj->edit('myName', 'awesome value');
        $this->assertEquals('<input type="text" name="myName" value="awesome value" class="text" />', $output);
    }
}