<?php
/**
 * Class for testing Web2project\Field\Url functionality
 *
 * @author      Keith Casey <contrib@caseysoftware.com>
 * @package     web2project
 * @subpackage  unit_tests
 * @license     Clear BSD
 * @link        http://www.web2project.net
 */

class Web2project_Fields_UrlTest extends CommonSetup
{
    protected function setUp()
    {
        parent::setUp();

        $this->obj = new \Web2project\Fields\Url();
    }

    public function testView()
    {
        $output = $this->obj->view('http://google.com');
        $this->assertEquals('<a href="http://google.com" target="_new">http://google.com</a>', $output);
    }
}