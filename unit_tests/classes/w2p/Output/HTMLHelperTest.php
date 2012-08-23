<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Class for testing HTMLHelper functionality
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
 * @author      D. Keith Casey, Jr.<caseydk@users.sourceforge.net>
 * @category    w2p_Output_HTMLHelper
 * @package     web2project
 * @subpackage  unit_tests
 * @copyright   2007-2012 The web2Project Development Team <w2p-developers@web2project.net>
 * @license     Clear BSD
 * @link        http://www.web2project.net
 */

// NOTE: This path is relative to Phing's build.xml, not this test.
include_once 'unit_tests/CommonSetup.php';

class w2p_Output_HTMLHelperTest extends CommonSetup
{
    protected function setUp ()
	{
		parent::setUp();

        $this->obj = new w2p_Output_HTMLHelper($this->_AppUI);
    }

    /*
     * This test makes sure that the right formatting is applied to all of the
     *   individual _name fields within the modules.
     */
    public function testCreateCell_name()
    {
        $this->obj->stageRowData(
                array('project_id' => 1, 'dept_id' => 2, 'message_id' => 3, 'forum_id' => 4)
            );

        // This is the default case used for most modules
        $cell = $this->obj->createCell('project_name', 'project name');
        $this->assertEquals('<td class="data _name"><a href="?m=projects&a=view&project_id=1">project name</a></td>', $cell);

        // This handles the department/dept misnaming special case
        $cell = $this->obj->createCell('dept_name', 'department name');
        $this->assertEquals('<td class="data _name"><a href="?m=departments&a=view&dept_id=2">department name</a></td>', $cell);

        // This handles the forum module's different path special case
        $cell = $this->obj->createCell('message_name', 'message name');
        $this->assertEquals('<td class="data _name"><a href="?m=forums&a=viewer&message_id=3&forum_id=4">message name</a></td>', $cell);

        // This handles the file module's different path special case
        $cell = $this->obj->createCell('forum_name', 'forum name');
        $this->assertEquals('<td class="data _name"><a href="?m=forums&a=viewer&message_id=3&forum_id=4">forum name</a></td>', $cell);
    }

    /*
     * This test makes sure that the lookups against our custom arrays are
     *   applied properly.
     */
    public function testCreateCell_lookups()
    {
        $custom = array(
            'x_category'=> array('one' => 'a',   'two' => 'b'),
            'x_status'=>   array('three' => 'c', 'four' => 'd'),
            'x_type'=>     array('five' => 'e',  'six' => 'f'),
          );

        $cell = $this->obj->createCell('file_type', 'default value', $custom);
        $this->assertEquals('<td class="data _type nowrap">default value</td>', $cell);
        $cell = $this->obj->createCell('x_category', 'one', $custom);
        $this->assertEquals('<td class="data _category nowrap">a</td>', $cell);
        $cell = $this->obj->createCell('x_category', 'notthere', $custom);
        $this->assertEquals('<td class="data _category nowrap"></td>', $cell);
        $cell = $this->obj->createCell('x_status', 'three', $custom);
        $this->assertEquals('<td class="data _status nowrap">c</td>', $cell);
        $cell = $this->obj->createCell('x_type', 'five', $custom);
        $this->assertEquals('<td class="data _type nowrap">e</td>', $cell);
    }

    /*
     * This test checks to make sure that these fields pass through without any
     *   formatting or modification applied.
     */
    public function testCreateCell_passthrough()
    {
        // The formatting for _author, _creator, _owner, and _updator are the same.
        $cell = $this->obj->createCell('x_author', 'default value');
        $this->assertEquals('<td class="data _author nowrap">default value</td>', $cell);
        $cell = $this->obj->createCell('x_owner', 'default value');
        $this->assertEquals('<td class="data _owner nowrap">default value</td>', $cell);
        $cell = $this->obj->createCell('x_creator', 'default value');
        $this->assertEquals('<td class="data _creator nowrap">default value</td>', $cell);
        $cell = $this->obj->createCell('x_updator', 'default value');
        $this->assertEquals('<td class="data _updator nowrap">default value</td>', $cell);

        // The formatting for _count, _duration, and _hours are the same.
        $cell = $this->obj->createCell('x_count', 'default value');
        $this->assertEquals('<td class="data _count">default value</td>', $cell);
        $cell = $this->obj->createCell('x_duration', 'default value');
        $this->assertEquals('<td class="data _duration">default value</td>', $cell);
        $cell = $this->obj->createCell('x_hours', 'default value');
        $this->assertEquals('<td class="data _hours">default value</td>', $cell);
    }

    public function testCreateCell_size()
    {
        $cell = $this->obj->createCell('x_size', 5000);
        $this->assertEquals('<td class="data _size nowrap">4.88 Kb</td>', $cell);
        $cell = $this->obj->createCell('x_size', 'monkey');
        $this->assertEquals('<td class="data _size nowrap">0 B</td>', $cell);
    }

    /*
     * This test covers the general purpose fields used throughout the system.
     *   They're not related to any specific module.
     */
    public function testCreateCell_common()
    {
        $cell = $this->obj->createCell('x_budget', 12345.67);
        $this->assertEquals('<td class="data _budget">$USD 12,345.67</td>', $cell);

        $cell = $this->obj->createCell('x_url', 'http://web2project.net');
        $this->assertEquals('<td class="data _url"><a href="http://web2project.net" target="_new">http://web2project.net</a></td>', $cell);

        $cell = $this->obj->createCell('x_email', 'admin@web2project.net');
        $this->assertEquals('<td class="data _email"><a href="mailto:admin@web2project.net">admin@web2project.net</a></td>', $cell);

        // The formatting for _complete and _assignment are the same.
        $cell = $this->obj->createCell('x_complete', '37.7');
        $this->assertEquals('<td class="data _complete">38%</td>', $cell);
        $cell = $this->obj->createCell('x_assignment', 'xxx');
        $this->assertEquals('<td class="data _assignment">0%</td>', $cell);

        $cell = $this->obj->createCell('x_password', 'monkey');
        $this->assertEquals('<td class="data _password">(hidden)</td>', $cell);

        $cell = $this->obj->createCell('x_version', '1');
        $this->assertEquals('<td class="data _version">1.00</td>', $cell);
        $cell = $this->obj->createCell('x_version', '1.8');
        $this->assertEquals('<td class="data _version">1.80</td>', $cell);
        $cell = $this->obj->createCell('x_version', 'monkey');
        $this->assertEquals('<td class="data _version">0.00</td>', $cell);

        $cell = $this->obj->createCell('x_priority', '0');
        $this->assertEquals('<td class="data _priority"></td>', $cell);
        $cell = $this->obj->createCell('x_priority', 1);
        $this->assertEquals('<td class="data _priority"><img src="./style/web2project/images/icons/priority+1.gif" width="13" height="16" alt=""></td>', $cell);
        $cell = $this->obj->createCell('x_priority', -2);
        $this->assertEquals('<td class="data _priority"><img src="./style/web2project/images/icons/priority-2.gif" width="13" height="16" alt=""></td>', $cell);

        $cell = $this->obj->createCell('x_description', 'This is a simple test');
        $this->assertEquals('<td class="data _description">This is a simple test</td>', $cell);
        $cell = $this->obj->createCell('x_description', 'This is a url test with http://google.com in the middle');
        $this->assertEquals('<td class="data _description">This is a url test ' .
                'with <a href="http://google.com" target="_blank">http://google.com</a> ' .
                'in the middle</td>', $cell);
    }

    public function testCreateCell_dates()
    {
        // The formatting for _birthday and _date are exactly the same
        $cell = $this->obj->createCell('x_birthday', '1776-07-04');
        $this->assertEquals('<td class="data _birthday nowrap">04/Jul/1776</td>', $cell);
        $cell = $this->obj->createCell('x_date', '1776-07-04');
        $this->assertEquals('<td class="data _date nowrap">04/Jul/1776</td>', $cell);
        $cell = $this->obj->createCell('x_date', 0);
        $this->assertEquals('<td class="data _date nowrap">-</td>', $cell);

        $cell = $this->obj->createCell('x_datetime', null);
        $this->assertEquals('<td class="data _datetime nowrap">-</td>', $cell);
        // The formatting for _created, _datetime, _update, _updated are the same
        $cell = $this->obj->createCell('x_created', '2012-04-01 12:00:00');
        $this->assertEquals('<td class="data _created nowrap">01/Apr/2012 06:00 am</td>', $cell);
        $cell = $this->obj->createCell('x_update', '2012-04-01 15:00:00');
        $this->assertEquals('<td class="data _update nowrap">01/Apr/2012 09:00 am</td>', $cell);
        $cell = $this->obj->createCell('x_updated', '2012-04-01 01:00:00');
        $this->assertEquals('<td class="data _updated nowrap">31/Mar/2012 07:00 pm</td>', $cell);
    }

    /*
     * I really have no clue on how to properly test these cases.. they're
     *   dependent on doing a $object->load() but we don't have dependency
     *   injection so we need to figure something else out.
     */
    public function testCreateCell_classes()
    {
        //case '_company':
        //case '_contact':
        //case '_project':
        //case '_task':
        //case '_department':
        //case '_folder':
        //case '_user':
        //case '_username':
$this->markTestIncomplete('These tests have yet to be written because we need to think about dependency injection for our database mock..');
    }
}