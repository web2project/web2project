<?php

/**
 * @package     web2project\modules\core
 */

class CSystem_Budget extends w2p_Core_BaseObject
{

    public $budget_id = null;
    public $budget_company = null;
    // @todo convert this to budget_department for v4.0
    public $budget_dept = null;
    public $budget_start_date = null;
    public $budget_end_date = null;
    public $budget_amount = null;
    public $budget_category = null;

    public function __construct()
    {
        parent::__construct('budgets', 'budget_id', 'system');
    }

    public function canEdit()
    {
        return canEdit('system');
    }
    public function canDelete($notUsed = null, $notUsed2 = null, $notUsed3 = null)
    {
        return canDelete('system');
    }
    public function canCreate()
    {
        return canAdd('system');
    }

    public function isValid()
    {
        $baseErrorMsg = get_class($this) . '::store-check failed - ';

        if (0 == trim($this->budget_amount)) {
            $this->_error['budget_amount'] = $baseErrorMsg . 'budget amount must be greater than zero';
        }

        return (count($this->_error)) ? false : true;
    }

    public function getBudgetAmounts($company_id = -1, $dept_id = -1)
    {
        $q = $this->_getQuery();
        $q->addTable('budgets', 'b');
        $q->addQuery('b.*, c.company_name, c.company_id');
        $q->leftJoin('companies', 'c', 'c.company_id = b.budget_company');
        $q->addOrder('budget_start_date, budget_end_date, company_name ASC');
        if ($company_id > -1) {
            $q->addWhere('b.budget_company = ' . (int) $company_id);
        }
        if ($dept_id > -1) {
            $q->addWhere('b.budget_dept = ' . (int) $dept_id);
        }

        return $q->loadHashList('budget_id');
    }

    protected function  hook_preStore() {
        if ($this->budget_start_date) {
            $date = new w2p_Utilities_Date($this->budget_start_date);
            $this->budget_start_date = $date->format(FMT_DATETIME_MYSQL);
        }
        if ($this->budget_end_date) {
            $date = new w2p_Utilities_Date($this->budget_end_date);
            $this->budget_end_date = $date->format(FMT_DATETIME_MYSQL);
        }

        parent::hook_preStore();
    }
}