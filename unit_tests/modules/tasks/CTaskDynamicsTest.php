<?php
/**
 * updateDynamics() finds a dynamic task's subtasks by path (#539).
 *
 * @package    web2project
 * @subpackage unit_tests
 * @license    Clear BSD
 */

class CTaskDynamicsTest extends CommonSetup
{
    protected $projectId = 99901;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cleanUp();

        $q = new w2p_Database_Query();
        $rows = array(
            array(99901, 99901, 1, '99901', '2026-01-01 08:00:00', '2026-01-02 17:00:00'),
            array(99902, 99901, 0, '99901/99902', '2026-02-02 08:00:00', '2026-02-05 17:00:00'),
            array(99903, 99901, 0, '99901/99903', '2026-02-10 08:00:00', '2026-02-20 17:00:00'),
        );
        foreach ($rows as $row) {
            list($id, $parent, $dynamic, $path, $start, $end) = $row;
            $q->clear();
            $q->addTable('tasks');
            $q->addInsert('task_id', $id);
            $q->addInsert('task_name', 'dynamics test ' . $id);
            $q->addInsert('task_parent', $parent);
            $q->addInsert('task_project', $this->projectId);
            $q->addInsert('task_owner', 1);
            $q->addInsert('task_dynamic', $dynamic);
            $q->addInsert('task_path_enumeration', $path);
            $q->addInsert('task_start_date', $start);
            $q->addInsert('task_end_date', $end);
            $q->addInsert('task_duration', 1);
            $q->addInsert('task_duration_type', 24);
            $q->exec();
        }
    }

    protected function tearDown(): void
    {
        $this->cleanUp();
        parent::tearDown();
    }

    protected function cleanUp()
    {
        $q = new w2p_Database_Query();
        $q->setDelete('tasks');
        $q->addWhere('task_project = ' . $this->projectId);
        $q->exec();
    }

    public function testDynamicParentFollowsItsSubtasks()
    {
        $task = new CTask();
        $task->task_project = $this->projectId;
        $task->updateDynamics();

        $q = new w2p_Database_Query();
        $q->addTable('tasks');
        $q->addQuery('task_start_date, task_end_date');
        $q->addWhere('task_id = 99901');
        $parent = $q->loadHash();

        $this->assertEquals('2026-02-02 08:00:00', $parent['task_start_date']);
        $this->assertEquals('2026-02-20 17:00:00', $parent['task_end_date']);
    }
}
