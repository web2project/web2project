<?php
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
 * @license     Clear BSD
 * @link        http://www.web2project.net
 */

class w2p_Output_HTMLHelperTest extends CommonSetup
{
    protected function setUp()
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
        $this->assertEquals('<td class="_name"><a href="?m=projects&a=view&project_id=1">project name</a></td>', $cell);

        // This handles the department/dept misnaming special case
        $cell = $this->obj->createCell('dept_name', 'department name');
        $this->assertEquals('<td class="_name"><a href="?m=departments&a=view&dept_id=2">department name</a></td>', $cell);

        // This handles the forum module's different path special case
        $cell = $this->obj->createCell('message_name', 'message name');
        $this->assertEquals('<td class="_name"><a href="?m=forums&a=viewer&message_id=3">message name</a></td>', $cell);

        // This handles the file module's different path special case
        $cell = $this->obj->createCell('forum_name', 'forum name');
        $this->assertEquals('<td class="_name"><a href="?m=forums&a=viewer&forum_id=4">forum name</a></td>', $cell);
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
        $this->assertEquals('<td class="_type">default value</td>', $cell);
        $cell = $this->obj->createCell('x_category', 'one', $custom);
        $this->assertEquals('<td class="_category">a</td>', $cell);
        $cell = $this->obj->createCell('x_category', 'notthere', $custom);
        $this->assertEquals('<td class="_category"></td>', $cell);
        $cell = $this->obj->createCell('x_status', 'three', $custom);
        $this->assertEquals('<td class="_status">c</td>', $cell);
        $cell = $this->obj->createCell('x_type', 'five', $custom);
        $this->assertEquals('<td class="_type">e</td>', $cell);
    }

    /*
     * This test checks to make sure that these fields pass through without any
     *   formatting or modification applied.
     */
    public function testCreateCell_passthrough()
    {
        // The formatting for _author, _creator, _owner, and _updator are the same.
        $cell = $this->obj->createCell('x_author', 'default value');
        $this->assertEquals('<td class="_author">default value</td>', $cell);
        $cell = $this->obj->createCell('x_owner', 'default value');
        $this->assertEquals('<td class="_owner">default value</td>', $cell);
        $cell = $this->obj->createCell('x_creator', 'default value');
        $this->assertEquals('<td class="_creator">default value</td>', $cell);
        $cell = $this->obj->createCell('x_updator', 'default value');
        $this->assertEquals('<td class="_updator">default value</td>', $cell);

        // The formatting for _count, _duration, and _hours are the same.
        $cell = $this->obj->createCell('x_count', 'default value');
        $this->assertEquals('<td class="_count">default value</td>', $cell);
        $cell = $this->obj->createCell('x_duration', 'default value');
        $this->assertEquals('<td class="_duration">default value </td>', $cell);
        $cell = $this->obj->createCell('x_hours', 'default value');
        $this->assertEquals('<td class="_hours">default value</td>', $cell);
    }

    public function testCreateCell_size()
    {
        $cell = $this->obj->createCell('x_size', 5000);
        $this->assertEquals('<td class="_size">4.88 Kb</td>', $cell);
        $cell = $this->obj->createCell('x_size', 'monkey');
        $this->assertEquals('<td class="_size">0 B</td>', $cell);
    }

    /*
     * This test covers the general purpose fields used throughout the system.
     *   They're not related to any specific module.
     */
    public function testCreateCell_common()
    {
        $cell = $this->obj->createCell('x_budget', 12345.67);
        $this->assertEquals('<td class="_budget">$USD12,345.67</td>', $cell);

        $cell = $this->obj->createCell('x_url', 'http://web2project.net');
        $this->assertEquals('<td class="_url"><a href="http://web2project.net" target="_new">http://web2project.net</a></td>', $cell);

        $cell = $this->obj->createCell('x_email', 'admin@web2project.net');
        $this->assertEquals('<td class="_email"><a href="mailto:admin@web2project.net">admin@web2project.net</a></td>', $cell);

        // The formatting for _complete and _assignment are the same.
        $cell = $this->obj->createCell('x_complete', '37.7');
        $this->assertEquals('<td class="_complete">38%</td>', $cell);
        $cell = $this->obj->createCell('x_assignment', 'xxx');
        $this->assertEquals('<td class="_assignment">0%</td>', $cell);

        $cell = $this->obj->createCell('x_password', 'monkey');
        $this->assertEquals('<td class="_password">(hidden)</td>', $cell);

        $cell = $this->obj->createCell('x_version', '1');
        $this->assertEquals('<td class="_version">1.00</td>', $cell);
        $cell = $this->obj->createCell('x_version', '1.8');
        $this->assertEquals('<td class="_version">1.80</td>', $cell);
        $cell = $this->obj->createCell('x_version', 'monkey');
        $this->assertEquals('<td class="_version">0.00</td>', $cell);

        $cell = $this->obj->createCell('x_priority', '0');
        $this->assertEquals('<td class="_priority"></td>', $cell);
        $cell = $this->obj->createCell('x_priority', 1);
        $this->assertEquals('<td class="_priority"><img src="./style/web2project/images/icons/priority+1.gif" width="13" height="16" alt=""></td>', $cell);
        $cell = $this->obj->createCell('x_priority', -2);
        $this->assertEquals('<td class="_priority"><img src="./style/web2project/images/icons/priority-2.gif" width="13" height="16" alt=""></td>', $cell);

        $cell = $this->obj->createCell('x_description', 'This is a simple test');
        $this->assertEquals('<td class="_description">This is a simple test</td>', $cell);
        $cell = $this->obj->createCell('x_description', 'This is a url test with http://google.com in the middle');
        $this->assertEquals('<td class="_description">This is a url test ' .
                'with <a href="http://google.com" target="_blank">http://google.com</a> ' .
                'in the middle</td>', $cell);
    }

    public function testCreateCell_dates()
    {
        // The formatting for _birthday and _date are exactly the same
        $cell = $this->obj->createCell('x_birthday', '1776-07-04');
        $this->assertEquals('<td class="_birthday">04/Jul/1776</td>', $cell);
        $cell = $this->obj->createCell('x_date', '1776-07-04');
        $this->assertEquals('<td class="_date">04/Jul/1776</td>', $cell);
        $cell = $this->obj->createCell('x_date', 0);
        $this->assertEquals('<td>-</td>', $cell);

        $cell = $this->obj->createCell('x_datetime', null);
        $this->assertEquals('<td>-</td>', $cell);
        // The formatting for _created, _datetime, _update, _updated are the same
        $cell = $this->obj->createCell('x_created', '2012-04-01 12:00:00');
        $this->assertEquals('<td class="_created">01/Apr/2012 07:00 am</td>', $cell);
        $cell = $this->obj->createCell('x_update', '2012-04-01 15:00:00');
        $this->assertEquals('<td class="_update">01/Apr/2012 10:00 am</td>', $cell);
        $cell = $this->obj->createCell('x_updated', '2012-04-01 01:00:00');
        $this->assertEquals('<td class="_updated">31/Mar/2012 08:00 pm</td>', $cell);
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

    public function testRenderContactTableEmpty()
    {
        $result = $this->obj->renderContactTable('companies', array());

        $this->assertEquals('<table class="tbl list"><tr><th>Name</th><th>Email</th><th>Phone</th><th>Department</th></tr><tr><td colspan="4">No data available</td></tr></table>',     $result);
    }

    public function testRenderContactTable()
    {
        $result = $this->obj->renderContactTable('companies',
            array(
                1 => array( 'contact_id' => 1,
                            'contact_name' => 'Tony Stark',
                            'contact_email' => 'iron.man@example.com',
                            'contact_phone' => '1212555IRON')
            ));

        $this->assertEquals('<table class="tbl list"><tr><th>Name</th><th>Email</th><th>Phone</th><th>Department</th></tr><tr><td class="_name"><a href="?m=contacts&a=view&contact_id=1">Tony Stark</a></td><td class="_email"><a href="mailto:iron.man@example.com">iron.man@example.com</a></td><td class="_phone">1212555IRON</td><td>-</td></tr></table>',     $result);
    }
}
