<?php

/**
 * Class for testing Web2project\Field\Url functionality
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to Clear BSD License. Please see the
 *   LICENSE file in root of site for further details
 *
 * @author      Keith Casey <contrib@caseysoftware.com>
 * @category    EmailManager
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
}