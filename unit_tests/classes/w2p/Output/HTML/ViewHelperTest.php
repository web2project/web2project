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
        $output = $this->obj->addField('field', '');

        $output = $this->obj->addField('field_datetime', '12/13/2014 23:45');
        $this->assertEquals('13/Dec/2014 05:45 pm', $output);

        $output = $this->obj->addField('field_birthday', '1979-09-14');
        $this->assertEquals('14/Sep/1979', $output);

        $output = $this->obj->addField('field_email', 'value');
        $this->assertEquals('<a href="mailto:value">value</a>', $output);

        $output = $this->obj->addField('field_url', 'value');
        $this->assertEquals('<a href="http://value" target="_new">http://value</a>', $output);

        $output = $this->obj->addField('field_owner', 'value');
        $this->assertEquals('<a href="?m=users&a=view&user_id=value"></a>', $output);

        $output = $this->obj->addField('field_percent', '34.1');
        $this->assertEquals('34%', $output);

        $output = $this->obj->addField('field_name', 'value');
        $this->assertEquals('value', $output);
    }

    public function testShowField()
    {
        $this->expectOutputString('<a href="mailto:value">value</a>');
        $this->obj->showField('field_email', 'value');
    }

    public function testShowAddress()
    {
        $object = new stdClass();
        $object->monkey_address1 = '123 Fake Street';
        $object->monkey_city = 'Austin';
        $object->monkey_state = 'TX';
        $object->monkey_zip = '78704';
        $object->monkey_country = 'US';

        $this->expectOutputString('<div style="margin-left: 11em;"><a href="http://maps.google.com/maps?q=123 Fake Street++Austin+TX+78704+US" target="_blank"><img src="./style/web2project/images/googlemaps.gif" class="right" alt="Find It on Google" /></a>123 Fake Street<br />Austin TX, 78704<br />United States</div>');
        $this->obj->showAddress('monkey', $object);
    }
}