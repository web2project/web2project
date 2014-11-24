<?php
/**
 * Class for testing Web2project\Field\DateTime functionality
 *
 * @author      Keith Casey <contrib@caseysoftware.com>
 * @package     web2project
 * @subpackage  unit_tests
 * @license     Clear BSD
 * @link        http://www.web2project.net
 */

class Web2project_Fields_DateTimeTest extends CommonSetup
{
    protected function setUp()
    {
        parent::setUp();

        $this->obj = new \Web2project\Fields\DateTime($this->_AppUI);
    }

    public function testView()
    {
        global $AppUI;
        $AppUI->user_prefs['TIMEZONE'] = 'CST';

        $output = $this->obj->view('2010-04-21 01:23:45');
        $this->assertEquals('20/Apr/2010 08:23 pm', $output);
    }

    public function testEdit()
    {
        $this->obj->setDateInformation(array('project', 'monkey', 'date'));
        $output = $this->obj->edit('date', '2010-04-21 01:23:45');

        $this->assertGreaterThan(0, strpos($output, '20100421'));
        $this->assertGreaterThan(0, strpos($output, 'monkey_date'));
        $this->assertGreaterThan(0, strpos($output, '21/Apr/2010 01:23 am'));
    }
}