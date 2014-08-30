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

class w2p_Output_HTML_ViewHelperTest extends CommonSetup
{
    protected function setUp()
    {
        parent::setUp();

        $this->obj = new w2p_Output_HTML_ViewHelper($this->_AppUI);
    }

    public function testAddLabel()
    {
        $this->assertEquals('<label>Project:</label>', $this->obj->addLabel('Project'));
    }

    public function testAddField()
    {
        $output = $this->obj->addField('email', 'monkey@example.com');
        $this->assertEquals('<a href="mailto:monkey@example.com">monkey@example.com</a>', $output);

        $output = $this->obj->addField('url', 'http://google.com');
        $this->assertEquals('<a href="http://google.com" target="_new">http://google.com</a>', $output);

        $output = $this->obj->addField('owner', '2');
        $this->assertEquals('<a href="?m=users&a=view&user_id=2">Contact Number 1</a>', $output);

        $output = $this->obj->addField('description', 'test');
        $this->assertEquals('test', $output);

        $output = $this->obj->addField('description', 'test w/ a link: http://web2project.net');
        $this->assertEquals('test w/ a link: <a href="http://web2project.net" target="_blank">http://web2project.net</a>', $output);

        $output = $this->obj->addField('percent', '38.7');
        $this->assertEquals('39%', $output);
        /**
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

        $output = $this->obj->addField('company', '1');
        $this->assertEquals('<a href="?m=companies&a=view&company_id=1">UnitTestCompany</a>', $output);

        $output = $this->obj->addField('other', 'fieldvalue', $options, $values);
        // @todo $this->assertEquals('<input type="text" xx="text other" name="other" value="fieldvalue" />', $output);
 */
    }
}
