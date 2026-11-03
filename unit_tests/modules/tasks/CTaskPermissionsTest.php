<?php
/**
 * Tests for the task permission checks that take the task's project into
 * account (GHSA-48ww-5h48-vwx9).
 *
 * LICENSE: This source file is subject to Clear BSD License. Please see the
 *   LICENSE file in root of site for further details
 *
 * @category   CTask
 * @package    web2project
 * @subpackage unit_tests
 * @license    Clear BSD
 * @link       http://www.web2project.net
 */

class CTaskPermissionsTest extends CommonSetup
{
    /**
     * A task whose permission checks answer from $allowed, keyed
     * "module:op" or "module:op:item"; anything not listed is refused.
     *
     * @param array<string, bool> $allowed
     */
    private function taskWith(array $allowed): CTask
    {
        $task = new class extends CTask {
            /** @param array<string, bool> $allowed */
            public function usePermissions(array $allowed): void
            {
                $this->_perms = new class ($allowed) {
                    /** @var array<string, bool> */
                    private array $allowed;

                    /** @param array<string, bool> $allowed */
                    public function __construct(array $allowed)
                    {
                        $this->allowed = $allowed;
                    }

                    public function checkModuleItem(string $module, string $op, mixed $item = null): bool
                    {
                        $key = $module . ':' . $op . ($item ? ':' . (int) $item : '');

                        return !empty($this->allowed[$key]);
                    }
                };
            }
        };
        $task->overrideDatabase($this->mockDB);
        $task->usePermissions($allowed);

        return $task;
    }

    public function testCanCreateNeedsTheProject(): void
    {
        $task = $this->taskWith(array('tasks:add' => true, 'projects:view:1' => true));

        $task->task_project = 1;
        $this->assertTrue($task->canCreate());

        // GHSA-48ww-5h48-vwx9: "add" on Tasks alone let any handler add a
        // task to a project the user can't see.
        $task->task_project = 2;
        $this->assertFalse($task->canCreate());
    }

    public function testCanCreateBeforeAProjectIsChosen(): void
    {
        // The blank add page asks before the project is known; isValid()
        // refuses to store a task without one.
        $this->assertTrue($this->taskWith(array('tasks:add' => true))->canCreate());
        $this->assertFalse($this->taskWith(array())->canCreate());
    }

    public function testCanEditInItsOwnProject(): void
    {
        $task = $this->taskWith(array('tasks:edit:7' => true));
        $task->task_id = 7;
        $task->task_project = 1;
        $this->mockDB->stageResult(1); // stored project

        $this->assertTrue($task->canEdit());
    }

    public function testCanEditNeedsTheTask(): void
    {
        $task = $this->taskWith(array('tasks:edit' => true, 'projects:view:1' => true));
        $task->task_id = 7;
        $task->task_project = 1;
        $this->mockDB->stageResult(1);

        $this->assertFalse($task->canEdit());
    }

    /**
     * GHSA-48ww-5h48-vwx9: bind() takes task_project from the request, so
     *   saving an editable task could move it into any project.
     */
    public function testMovingToAnotherProjectNeedsThatProject(): void
    {
        $task = $this->taskWith(array('tasks:edit:7' => true, 'tasks:add' => true, 'projects:view:1' => true));
        $task->task_id = 7;
        $this->mockDB->stageResult(1); // stored in project 1

        $task->task_project = 2;
        $this->assertFalse($task->canEdit());

        $allowed = $this->taskWith(array('tasks:edit:7' => true, 'tasks:add' => true,
            'projects:view:1' => true, 'projects:view:2' => true));
        $allowed->task_id = 7;
        $allowed->task_project = 2;
        $this->assertTrue($allowed->canEdit());

        $noAdd = $this->taskWith(array('tasks:edit:7' => true, 'projects:view:2' => true));
        $noAdd->task_id = 7;
        $noAdd->task_project = 2;
        $this->assertFalse($noAdd->canEdit());
    }
}
