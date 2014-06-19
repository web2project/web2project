<?php

class w2p_Actions_BulkTasks extends CTask
{
    protected function hook_postStore()
    {
        // do nothing on purpose
    }

    protected function hook_postDelete()
    {
        // do nothing on purpose
    }

    /**
     * Import tasks from another project
     *
     * 	@param	int Project ID of the tasks come from.
     * 	@return	bool
     *
     *  @todo - this entire thing has nothing to do with projects.. it should move to the CTask class - dkc 25 Nov 2012
     *  @todo - why are we returning either an array or a boolean? You make my head hurt. - dkc 25 Nov 2012
     *
     *  @todo - we should decide if we want to include the contacts associated with each task
     *  @todo - we should decide if we want to include the files associated with each task
     *  @todo - we should decide if we want to include the links associated with each task
     *
     * Of the three - contacts, files, and links - I can see a case made for
     *   all three. Imagine you have a task which requires a particular form to
     *   be filled out (Files) but there's also documentation you need about it
     *   (Links) and once the task is underway, you need to let some people
     *   know (Contacts). - dkc 25 Nov 2012
     * */
    public function importTasks($from_project_id, $to_project_id, $project_start_date)
    {
        $errors = array();

        $old_new_task_mapping = array();
        $old_dependencies = array();
        $old_parents = array();

        $project_start_date = new w2p_Utilities_Date($project_start_date);

        $newTask = new w2p_Actions_BulkTasks();
        $task_list = $newTask->loadAll('task_start_date', "task_represents_project = 0 AND task_project = " . $from_project_id);
        $first_task = array_shift($task_list);

        /**
         * This gets the first (earliest) task start date and figures out
         *   how much we have to shift all the tasks by.
         */
        $original_start_date = new w2p_Utilities_Date($first_task['task_start_date']);
        $timeOffset = $original_start_date->dateDiff($project_start_date);

        array_unshift($task_list, $first_task);
        foreach($task_list as $orig_task) {
            $orig_id = $orig_task['task_id'];

            $new_start_date = new w2p_Utilities_Date($orig_task['task_start_date']);
            $new_start_date->addDays($timeOffset);

            $new_end_date   = new w2p_Utilities_Date($orig_task['task_end_date']);
            $new_end_date->addDays($timeOffset);

            $old_parents[$orig_id] = $orig_task['task_parent'];

            $orig_task['task_id'] = 0;
            $orig_task['task_parent'] = 0;
            $orig_task['task_project'] = $to_project_id;
            $orig_task['task_sequence'] = 0;
            $orig_task['task_path_enumeration'] = '';
            $orig_task['task_hours_worked'] = 0;

            // This is necessary because we're using bind() and it shifts by timezone
            $orig_task['task_start_date'] =
                $this->_AppUI->formatTZAwareTime($new_start_date->format(FMT_DATETIME_MYSQL), '%Y-%m-%d %T');
            $orig_task['task_end_date'] =
                $this->_AppUI->formatTZAwareTime($new_end_date->format(FMT_DATETIME_MYSQL),   '%Y-%m-%d %T');

            $_newTask = new w2p_Actions_BulkTasks();
            $_newTask->bind($orig_task);
            $_newTask->store();

            $task_map[$orig_id] = $_newTask->task_id;

            $old_dependencies[$orig_id] = array_keys($_newTask->getDependentTaskList($orig_id));
            $old_new_task_mapping[$orig_id] = $_newTask->task_id;
        }

        if (count($errors)) {
            $this->_error = $errors;
            foreach($old_new_task_mapping as $new_id) {
                $newTask->task_id = $new_id;
                $newTask->delete();
            }
        } else {
            $q = $this->_getQuery();

            /* This makes sure we have all the dependencies mapped out. */
            foreach($old_dependencies as $from => $to_array) {
                foreach($to_array as $to) {
                    $q->addTable('task_dependencies');
                    $q->addInsert('dependencies_req_task_id', $old_new_task_mapping[$from]);
                    $q->addInsert('dependencies_task_id',     $old_new_task_mapping[$to]);
                    $q->exec();
                    $q->clear();
                }
            }

            /* This makes sure all the parents are connected properly. */
            foreach($old_parents as $old_child => $old_parent) {
                if ($old_child == $old_parent) {
                    /** Remember, this means skip the rest of the loop. */
                    continue;
                }
                $q->addTable('tasks');
                $q->addUpdate('task_parent', $old_new_task_mapping[$old_parent]);
                $q->addWhere('task_id   = ' . $old_new_task_mapping[$old_child]);
                $q->exec();
                $q->clear();
            }

            /* This copies the task assigness to the new tasks. */
            foreach($old_new_task_mapping as $old_id => $new_id) {
                $newTask->task_id = $old_id;
                $newTask->copyAssignedUsers($new_id);
            }
        }

        $_task = new CTask();
        $task_list = $_task->loadAll('task_parent, task_id', "task_project = " . $to_project_id);
        foreach($task_list as $key => $data) {
            $_task->load($key);
            $_task->_updatePathEnumeration();

            if (!$_task->task_parent) {
                $q->addTable('tasks');
                $q->addUpdate('task_parent', $_task->task_id);
                $q->addUpdate('task_updated', "'" . $q->dbfnNowWithTZ() . "'", false, true);
                $q->addWhere('task_id = ' . (int) $_task->task_id);
                $q->exec();
                $q->clear();
            }
        }

        $_task->updateDynamics();

        $last_task_data = $this->getLastTaskData($to_project_id);
        CProject::updateTaskCache(
            $to_project_id,
            $last_task_data['task_id'],
            $last_task_data['last_date'],
            $this->getTaskCount($to_project_id)
        );

        return $errors;
    }
}