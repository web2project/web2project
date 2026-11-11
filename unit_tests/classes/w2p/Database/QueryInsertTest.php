<?php
/**
 * insertObject() and insertArray() leave out only null values (#559).
 *
 * @package    web2project
 * @subpackage unit_tests
 * @license    Clear BSD
 */

class w2p_Database_QueryInsertTest extends CommonSetup
{
    protected function tearDown(): void
    {
        $q = new w2p_Database_Query();
        $q->setDelete('user_tasks');
        $q->addWhere('user_id = 99901');
        $q->exec();

        parent::tearDown();
    }

    protected function loadAssignment($taskId)
    {
        $q = new w2p_Database_Query();
        $q->addTable('user_tasks');
        $q->addQuery('perc_assignment');
        $q->addWhere('user_id = 99901 AND task_id = ' . (int) $taskId);

        return $q->loadResult();
    }

    public function testInsertArrayKeepsZero()
    {
        // perc_assignment defaults to 100; an explicit 0 must be stored as 0
        $hash = array('user_id' => 99901, 'task_id' => 99901, 'perc_assignment' => 0);
        $q = new w2p_Database_Query();
        $q->insertArray('user_tasks', $hash);

        $this->assertEquals(0, $this->loadAssignment(99901));
    }

    public function testInsertObjectKeepsZeroAndSkipsNull()
    {
        $row = new stdClass();
        $row->user_id = 99901;
        $row->task_id = 99902;
        $row->perc_assignment = 0;
        $row->user_task_priority = null;
        $q = new w2p_Database_Query();
        $this->assertTrue($q->insertObject('user_tasks', $row));
        $this->assertEquals(0, $this->loadAssignment(99902));

        // null is still left out, so the column default applies
        $row = new stdClass();
        $row->user_id = 99901;
        $row->task_id = 99903;
        $row->perc_assignment = null;
        $q = new w2p_Database_Query();
        $this->assertTrue($q->insertObject('user_tasks', $row));
        $this->assertEquals(100, $this->loadAssignment(99903));
    }

    public function testInsertEmptyStringOnlyForTextColumns()
    {
        // '' for an int column still means "use the default" (strict mode
        // rejects it); 0 is stored as 0
        $hash = array('user_id' => 99901, 'task_id' => 99904, 'perc_assignment' => '');
        $q = new w2p_Database_Query();
        $this->assertNotFalse($q->insertArray('user_tasks', $hash));
        $this->assertEquals(100, $this->loadAssignment(99904));
    }
}
