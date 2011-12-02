<?php
/**
 *	@package web2project
 *	@subpackage core
 *	@version $Revision$
 */

class budgets extends w2p_Core_BaseObject
{
	public $budget_id = 0;
	public $budget_company = 0;
	public $budget_dept = 0;
	public $budget_start_date = null;
	public $budget_end_date = null;
	public $budget_amount = 0;
	public $budget_category = '';

	public function __construct() {
		parent::__construct('budgets', 'budget_id');
	}

    public function getBudgetAmounts($company_id = -1, $dept_id = -1) {
        $q = new w2p_Database_Query();
        $q->addTable('budgets', 'b');
        $q->addQuery('b.*, c.company_name');
        $q->leftJoin('companies', 'c', 'c.company_id = b.budget_company');
        $q->addOrder('budget_start_date, budget_end_date, company_name ASC');
        if ($company_id > -1) {
            $q->addWhere('b.budget_company = ' . (int) $company_id);
        }
        if ($deptId > -1) {
            $q->addWhere('b.budget_dept = ' . (int) $dept_id);
        }

        return $q->loadHashList('budget_id');
    }

	public function store(CAppUI $AppUI) {
        $perms = $AppUI->acl();
        $stored = false;

        $errorMsgArray = $this->check();

        if (count($errorMsgArray) > 0) {
            return $errorMsgArray;
        }

        if ($this->budget_start_date) {
            $date = new w2p_Utilities_Date($this->budget_start_date);
            $this->budget_start_date = $date->format(FMT_DATETIME_MYSQL);
        }
        if ($this->budget_end_date) {
            $date = new w2p_Utilities_Date($this->budget_end_date);
            $this->budget_end_date = $date->format(FMT_DATETIME_MYSQL);
        }

        if ($perms->checkModuleItem('system', 'edit')) {
            if (($msg = parent::store())) {
                return $msg;
            }
            $stored = true;
        }
        return $stored;
	}
}