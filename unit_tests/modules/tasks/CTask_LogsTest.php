<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Class for testing task log functionality
 *
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to Clear BSD License. Please see the
 *   LICENSE file in root of site for further details
 *
 * @author      Trevor Morse <trevor.morse@gmail.com>
 * @category    CTask_Logs
 * @package     web2project
 * @subpackage  unit_tests
 * @license     Clear BSD
 * @link        http://www.web2project.net
 */

class CTask_LogsTest extends CommonSetup
{

    protected function setUp()
    {
        parent::setUp();

        $this->obj = new CTask_Log();
        $this->obj->overrideDatabase($this->mockDB);

        $this->post_data = array(
            'task_log_id'                           => 0,
            'task_log_task'                         => 1,
            'task_log_name'                         => 'This is a task log name.',
            'task_log_description'                  => 'This is a description.',
            'task_log_creator'                      => 1,
            'task_log_hours'                        => 2.75,
            'task_log_date'                         => '2010-05-30 09:15:30',
            'task_log_costcode'                     => 1,
            'task_log_problem'                      => 1,
            'task_log_reference'                    => 1,
            'task_log_related_url'                  => 'http://www.example.com',
            'task_log_created'                      => '2010-05-30 09:15:30',
            'task_log_updated'                      => '2010-05-30 09:15:30',
            'task_log_record_creator'               => 1,
            'task_log_percent_complete'             => 20,
            'task_log_task_end_date'                => '20111007'
        );
    }

    public function testObjectProperties()
    {
        $unset = array('task_log_problem');

        parent::objectPropertiesTest('CTask_Log', 15, $unset);
    }

    /**
     * Tests storing task log in database
     */
    public function testStoreCreate()
    {
        $this->obj->bind($this->post_data);

        $results = $this->obj->store();
        $this->assertTrue($results);
        $this->assertEquals(1,                            $this->obj->task_log_id);
        /*
         *  This field is auto-generated in the CTask_Log->store method.
         */
        $this->assertNotNull($this->obj->task_log_created);
        $this->assertEquals($this->obj->task_log_created, $this->obj->task_log_updated);
        /*
         *  These fields are formatted by the CTask_Log->store method.
         */

        $this->assertEquals('2010-05-30 09:15:30',        $this->obj->task_log_date);
        $this->assertEquals(2.75,                         $this->obj->task_log_hours);
        /*
         *  These fields come from the $_POST data and should be pass throughs.
         */
        $this->assertEquals('This is a task log name.',   $this->obj->task_log_name);
        $this->assertEquals('This is a description.',     $this->obj->task_log_description);
        $this->assertEquals('http://www.example.com',     $this->obj->task_log_related_url);
        $this->assertEquals(1,                            $this->obj->task_log_record_creator);

        //TODO: figure out a way to test the CTask cascading totals
        //TODO: figure out a way to test the CProject cascading totals
    }

    /**
     * Tests storing task log in database
     */
    public function testStoreUpdate()
    {
        $this->obj->bind($this->post_data);
        $result = $this->obj->store();
        $this->assertTrue($result);
        $original_id = $this->obj->task_log_id;

        $this->obj->task_log_name           = 'Updated Task Log';
        $this->obj->task_log_description    = 'My new description';
        $this->obj->task_log_record_creator = 2;

        /*
         * This sleep() is used because we need at least a second to pass for the
         *   task_log_updated time to be different than the task_log_created earlier
         *   in this test.
         */
        sleep(1);
        $result = $this->obj->store();
        $this->assertTrue($result);
        $new_id = $this->obj->task_log_id;

        $this->assertEquals($original_id,                    $new_id);
        $this->assertEquals('Updated Task Log',              $this->obj->task_log_name);
        $this->assertEquals('My new description',            $this->obj->task_log_description);
        $this->assertEquals(2,                               $this->obj->task_log_record_creator);
        $this->assertNotEquals($this->obj->task_log_created, $this->obj->task_log_updated);

        //TODO: figure out a way to test the CTask cascading totals
        //TODO: figure out a way to test the CProject cascading totals
    }

    /**
     * Test deleting a tasklog
     *
     */
    public function testDelete()
    {
        $this->obj->bind($this->post_data);
        $result = $this->obj->store();
        $this->assertTrue($result);
        $original_id = $this->obj->task_log_id;
        $result = $this->obj->delete();

        $item = new CTask_Log();
        $item->overrideDatabase($this->mockDB);
        $this->mockDB->stageHash(array('task_log_name' => '', 'task_log_description' => ''));
        $item->load($original_id);

        $this->assertTrue(is_a($item, 'CTask_Log'));
        $this->assertEquals('',              $item->task_log_name);
        $this->assertEquals('',              $item->task_log_description);

        //TODO: figure out a way to test the CTask cascading totals
        //TODO: figure out a way to test the CProject cascading totals
    }

    /**
     * TODO: This should not be in the TaskLog object and should instead be on
     *   the main w2p object: w2p_Core_BaseObject
     *
     * Test trimming all trimmable characters from all object properties
     */
    public function testW2PTrimAll()
    {
        $this->obj->bind($this->post_data, null, true, true);

        /*
         * Add all the trimmable characters to each variable in the object
         * so we can trim them all back off
         */
        $vars = get_object_vars($this->obj);

        foreach ($vars as $var_name => $var_value) {
            if (!is_object($var_value)) {
                $this->obj->$var_name = " \t\n" . $var_value . "\r\0\x0B";
            }
        }

        $this->obj->w2PTrimAll();

        $this->assertEquals(0,                                     $this->obj->task_log_id);
        $this->assertEquals(1,                                     $this->obj->task_log_task);
        $this->assertEquals('This is a task log name.',            $this->obj->task_log_name);
        $this->assertEquals(" \t\nThis is a description.\r\0\x0B", $this->obj->task_log_description);
        $this->assertEquals(1,                                     $this->obj->task_log_creator);
        $this->assertEquals(2.75,                                  $this->obj->task_log_hours);
        $this->assertEquals('2010-05-30 09:15:30',                 $this->obj->task_log_date);
        $this->assertEquals(1,                                     $this->obj->task_log_costcode);
        $this->assertEquals(1,                                     $this->obj->task_log_problem);
        $this->assertEquals(1,                                     $this->obj->task_log_reference);
        $this->assertEquals('http://www.example.com',              $this->obj->task_log_related_url);
        $this->assertEquals('2010-05-30 09:15:30',                 $this->obj->task_log_created);
        $this->assertEquals('2010-05-30 09:15:30',                 $this->obj->task_log_updated);
        $this->assertEquals(1,                                     $this->obj->task_log_creator);
    }

    /**
     * Test canDelete
     */
    public function testCanDelete()
    {
        $msg = '';

        $return = $this->obj->canDelete($msg);

        $this->assertTrue($return);
        $this->assertEquals('', $msg);
    }

    /**
     * Test getting allowed records with a uid passed
     */
    public function testGetAllowedRecordUid()
    {
        $this->mockDB->stageHashList(1, 1);

        $allowed_records = $this->obj->getAllowedRecords(1);
        $this->assertEquals(1, count($allowed_records));
        $this->assertEquals(1, $allowed_records[1]);
    }

    /**
     * Test getting allowed records when passing a uid and fields
     */
    public function testGetAllowedRecordsFields()
    {
        $this->mockDB->stageHashList(1, 'Task Log 1');

        $allowed_records = $this->obj->getAllowedRecords(1, 'task_log.task_log_task, task_log.task_log_name');
        $this->assertEquals(1,              count($allowed_records));
        $this->assertEquals('Task Log 1',   $allowed_records[1]);
    }


    /**
     * Tests getting allowed records with an order by
     */
    public function testGetAllowedRecordsOrderBy()
    {
        $this->mockDB->stageHashList(1, 1);

        $allowed_records = $this->obj->getAllowedRecords(1, '*', 'task_log.task_log_hours');
        $this->assertEquals(1, count($allowed_records));
        $this->assertEquals(1, $allowed_records[1]);
    }

    /**
     * Test getting allowed records with an index specified
     */
    public function testGetAllowedRecordsIndex()
    {
        $this->mockDB->stageHashList('Task Log 1', array('Task Log 1', 'task_log_name' => 'Task Log 1'));

        $allowed_records = $this->obj->getAllowedRecords(1, 'task_log.task_log_name', '', 'task_log_name');
        $this->assertEquals(1,              count($allowed_records));
        $this->assertEquals(2,              count($allowed_records['Task Log 1']));
        $this->assertEquals('Task Log 1',   $allowed_records['Task Log 1']['task_log_name']);
        $this->assertEquals('Task Log 1',   $allowed_records['Task Log 1'][0]);
    }

    /**
     * Test getting allowed records with extra data specified
     */
    public function testGetAllowedRecordsExtra()
    {
        $extra = array('from' => 'task_log', 'join' => 'tasks', 'on' => 'task_id = task_log_task',
                       'where' => 'task_id = 1');
        $this->mockDB->stageHashList(1, 1);

        $allowed_records = $this->obj->getAllowedRecords(1, '*', '', null, $extra);
        $this->assertEquals(1, count($allowed_records));
        $this->assertEquals(1, $allowed_records[1]);
    }

    /**
     * Tests that the proper error message is returned when a task log is attempted
     * to be created without a task.
     */
    public function testCreateTaskLogNoTask()
    {
        unset($this->post_data['task_log_task']);
        $this->obj->bind($this->post_data);

        /**
        * Verify we got the proper error message
        */
        $this->assertFalse($this->obj->store());
        $this->assertArrayHasKey('task_log_task', $this->obj->getError());

        /**
        * Verify that the id was not set
        */
        $this->AssertEquals(0, $this->obj->task_log_id);
    }

    /**
     * Tests that the proper error message is returned when a task log is attempted
     * to be created without a name.
     */
    public function testCreateTaskLogNoName()
    {
        $this->post_data['task_log_name'] = '';
        $this->obj->bind($this->post_data);

        /**
        * Verify we got the proper error message
        */
        $this->assertFalse($this->obj->store());
        $this->assertArrayHasKey('task_log_name', $this->obj->getError());

        /**
        * Verify that the id was not set
        */
        $this->AssertEquals(0, $this->obj->task_log_id);
    }
}
