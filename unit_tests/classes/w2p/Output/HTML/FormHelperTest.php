<?php
/**
 * Class for testing HTML FormHelper functionality
 *
 * Many of the tests are quite similar with the only difference being the field
 *   names being tested. The duplication is on purpose because the formatting is
 *   vitally important to various parts of the system. If the formatting on a
 *   field changes, we need to know about it.
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to Clear BSD License. Please see the
 *   LICENSE file in root of site for further details
 *
 * @author      D. Keith Casey, Jr.<contrib@caseysoftware.com>
 * @category    w2p_Output_HTMLHelper
 * @package     web2project
 * @subpackage  unit_tests
 * @license     Clear BSD
 * @link        http://www.web2project.net
 */

class w2p_Output_HTML_FormHelperTest extends CommonSetup
{
    protected function setUp()
    {
        parent::setUp();

        $this->obj = new w2p_Output_HTML_FormHelper($this->_AppUI);
    }

    public function testShowLabel()
    {
        $this->expectOutputString('<label>Project:</label>');
        $this->obj->showLabel('Project');
    }

    public function testShowCancelButton()
    {
        $this->expectOutputString('<input type="button" value="back" class="cancel button btn btn-danger" onclick="javascript:history.back(-1);" />');
        $this->obj->showCancelButton();
    }

    public function testShowSaveButton()
    {
        $this->expectOutputString('<input type="button" value="save" class="save button btn btn-primary" onclick="submitIt()" />');
        $this->obj->showSaveButton();
    }

    public function testAddNonce()
    {
        $this->assertGreaterThan(0, strpos($this->obj->addNonce(), '__nonce'));
    }

    public function testAddField()
    {
        $options = array();
        $values  = array();

        $output = $this->obj->addField('description', 'test');
        $this->assertEquals('<textarea name="description" class="text description">test</textarea>', $output);

        $output = $this->obj->addField('birthday', '2014-02-01');
        // @todo $this->assertEquals('<input type="text" class="text birthday" name="birthday" value="2014-02-01" />', $output);

        $output = $this->obj->addField('task_end_date', '2014-02-01');
        $this->assertGreaterThan(0, strpos($output, 'value="20140201"'));
        $this->assertGreaterThan(0, strpos($output, 'value="01/Feb/2014"'));
        $this->assertGreaterThan(0, strpos($output, "return showCalendar('end_date'"));

        $output = $this->obj->addField('private', 'fieldvalue');
        // @todo $this->assertEquals('<input type="checkbox" value="1" class="text private" name="private" />', $output);

        $output = $this->obj->addField('type', 0, array(), array(0 => 'monkey', 2 => 'dog'));
        $this->assertGreaterThan(0, strpos($output, '<option value="0" selected="selected">monkey</option>'));

        $output = $this->obj->addField('url', 'http://google.com', $options, $values);
        $this->assertGreaterThan(0, strpos($output, 'class="text url"'));
        $this->assertGreaterThan(0, strpos($output, 'value="http://google.com"'));

        $output = $this->obj->addField('other', 'fieldvalue', $options, $values);
        // @todo $this->assertEquals('<input type="text" xx="text other" name="other" value="fieldvalue" />', $output);
        $output = $this->obj->addField('task_parent', 0, array(), array(0 => 'department 1', 2 => 'dept 2'));
        $this->assertEquals('<select id="task_parent" name="task_parent" size="1" class="text department"><option value="0" selected="selected">department 1</option><option value="2">dept 2</option></select>', $output);

    }

    public function testShowField()
    {
        $this->expectOutputString('<textarea name="description" class="text description">test</textarea>');
        $this->obj->showField('description', 'test');
    }
}
