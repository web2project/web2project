<?php
/**
 * @package     web2project\modules\core
 */

class CSystem_Bcode extends w2p_Core_BaseObject
{

    public $_billingcode_id = null;
    public $billingcode_company = null;
    public $billingcode_id = null;
    public $billingcode_desc = null;
    public $billingcode_name = null;
    public $billingcode_value = null;
    public $billingcode_status = null;
    public $billingcode_category = null;

    public function __construct()
    {
        parent::__construct('billingcode', 'billingcode_id', 'system');
    }

    /*
     * This very specifically does not call the parent::delete() because we
     *    don't want to destroy the old data. We just want to deactivate the
     *    billing code.
     */
    public function delete()
    {
        $result = false;

        if ($this->canDelete()) {
            $q = $this->_getQuery();
            $q->addTable('billingcode');
            $q->addUpdate('billingcode_status', '1');
            $q->addWhere('billingcode_id = ' . (int) $this->billingcode_id);

            $result = $q->exec();
            if(!$result) {
                $this->_errors['delete'] = db_error();
            }
        }
        return $result;
    }

    public function canDelete()
    {
        return $this->_perms->checkModuleItem('system', 'delete');
    }

    public function canEdit()
    {
        return canEdit('system');
    }

    public function canCreate()
    {
        return canAdd('system');
    }

    public function store()
    {
        $stored = false;

//TODO: Why isn't there a canCreate branch here?
        if ($this->canEdit()) {
            $stored = parent::store();
        }
        return $stored;
    }

    public function isValid()
    {
        $baseErrorMsg = get_class($this) . '::store-check failed - ';

        $q = $this->_getQuery();
        $q->addQuery('billingcode_id');
        $q->addTable('billingcode');
        $q->addWhere('billingcode_name = \'' . $this->billingcode_name . '\'');
        $q->addWhere('billingcode_company = ' . (int) $this->billingcode_company);
        $q->addWhere('billingcode_id <> ' . (int) $this->billingcode_id);

        $found_id = $q->loadResult();

        if ($found_id) {
            $this->_error['billingcode_name'] = $baseErrorMsg . 'code already exists';
        }

        return (count($this->_error)) ? false : true;
    }

    public function getBillingCodes($company_id = -1, $activeOnly = true)
    {
        $q = $this->_getQuery();
        $q->addTable('billingcode', 'bc');
        $q->addQuery('bc.*, c.company_name');
        $q->leftJoin('companies', 'c', 'c.company_id = bc.billingcode_company');
        $q->addOrder('company_name, billingcode_name ASC');
        if ($company_id > -1) {
            $q->addWhere('bc.billingcode_company = ' . (int) $company_id);
        }
        if ($activeOnly) {
            $q->addWhere('billingcode_status = 0');
        }

        return $q->loadHashList('billingcode_id');
    }

    public function calculateTaskCost($task_id, $start_date = null, $end_date = null)
    {
        $q = $this->_getQuery();
        $q->addTable('task_log', 'tl');
        $q->addQuery('task_log_hours, billingcode_value, billingcode_category');
        $q->leftJoin('billingcode', 'bc', 'bc.billingcode_id = tl.task_log_costcode');
        $q->addWhere('tl.task_log_task = ' . (int) $task_id);
        if ($start_date && $end_date) {
            $q->addWhere("tl.task_log_date >= '$start_date'");
            $q->addWhere("tl.task_log_date <= '$end_date'");
        }
        $logs = $q->loadList();

        $results = array();
        foreach ($logs as $tasklog) {
            if (is_null($tasklog['billingcode_value'])) {
                $results['uncountedHours'] += $tasklog['task_log_hours'];
            } else {
                $category = ('' == ($tasklog['billingcode_category'])) ? 'otherCosts' : $tasklog['billingcode_category'];
                $results[$category] += $tasklog['task_log_hours'] * $tasklog['billingcode_value'];
                $results['totalCosts'] += $tasklog['task_log_hours'] * $tasklog['billingcode_value'];
            }
        }

        return $results;
    }

    public function calculateProjectCost($project_id, $start_date = null, $end_date = null)
    {
        $q = $this->_getQuery();
        $q->addTable('task_log', 'tl');
        $q->addQuery('task_log_hours, billingcode_value, billingcode_category');
        $q->addJoin('tasks', 't', 't.task_id = tl.task_log_task');
        $q->leftJoin('billingcode', 'bc', 'bc.billingcode_id = tl.task_log_costcode');
        $q->addWhere('t.task_project = ' . (int) $project_id);
        if ($start_date && $end_date) {
            $q->addWhere("tl.task_log_date >= '$start_date'");
            $q->addWhere("tl.task_log_date <= '$end_date'");
        }
        $logs = $q->loadList();

        $results = array();
        foreach ($logs as $tasklog) {
            if (is_null($tasklog['billingcode_value'])) {
                $results['uncountedHours'] += $tasklog['task_log_hours'];
            } else {
                $category = ('' == ($tasklog['billingcode_category'])) ? 'otherCosts' : $tasklog['billingcode_category'];
                if (!isset($results[$category])) {
                    $results[$category] = 0;
                }
                if (!isset($results['totalCosts'])) {
                    $results['totalCosts'] = 0;
                }
                $results[$category] += $tasklog['task_log_hours'] * $tasklog['billingcode_value'];
                $results['totalCosts'] += $tasklog['task_log_hours'] * $tasklog['billingcode_value'];
            }
        }

        return $results;
    }

}