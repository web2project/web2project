<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
global $AppUI, $cal_sdf;
$AppUI->loadCalendarJS();

$budget_id = (int) w2PgetParam($_GET, 'budget_id', 0);

if (!canEdit('system')) {
	$AppUI->redirect('m=public&a=access_denied');
}
$df = $AppUI->getPref('SHDATEFORMAT');

// get a list of permitted companies
$company = new CCompany();
$companies = $company->getAllowedRecords($AppUI->user_id, 'company_id,company_name', 'company_name');
$companies = arrayMerge(array('0' => $AppUI->_('None specified')), $companies);

$budgetCategory = w2PgetSysVal('BudgetCategory');
$budgetCategory = arrayMerge(array('0' => $AppUI->_('None specified')), $budgetCategory);

// load the record data
$budget = new CSystem_Budget();
$budget->load($budget_id);

$titleBlock = new w2p_Theme_TitleBlock('Setup Budgets', 'myevo-weather.png', $m, $m . '.' . $a);
$titleBlock->addCrumb('?m=system', 'system admin');
$titleBlock->addCrumb('?m=system&a=budgeting_allocated', 'budgets allocated');
$titleBlock->show();
?>
<script language="javascript" type="text/javascript">
	function submitIt(){
		document.frmAddcode.submit();
	}
<?php
// security improvement:
// some javascript functions may not appear on client side in case of user not having write permissions
// else users would be able to arbitrarily run 'bad' functions
if ($canDelete) {
?>
function delIt(input) {
	if (confirm( '<?php echo $AppUI->_('doDelete', UI_OUTPUT_JS) . ' ' . $AppUI->_('Budget', UI_OUTPUT_JS) . '?'; ?>' )) {
		document.frmDelete.budget_id.value = input;
        document.frmDelete.submit();
	}
}
<?php } ?>
</script>
<form name="frmDelete" action="./index.php?m=system" method="post" accept-charset="utf-8">
	<input type="hidden" name="dosql" value="do_budgeting_aed" />
	<input type="hidden" name="del" value="1" />
	<input type="hidden" name="budget_id" value="0" />
</form>
<form name="frmAddcode" action="./index.php?m=system" method="post" accept-charset="utf-8">
    <input type="hidden" name="dosql" value="do_budgeting_aed" />
    <input type="hidden" name="budget_id" value="<?php echo $budget_id; ?>" />
    <input type="hidden" name="datePicker" value="budget" />
    <table width="100%" border="0" cellpadding="1" cellspacing="1" class="std">
        <tr>
            <th>&nbsp;</th>
            <th><?php echo $AppUI->_('Company'); ?></th>
			<!--<th><?php echo $AppUI->_('Department'); ?></th>-->
            <th align="center"><?php echo $AppUI->_('Start Date'); ?></th>
			<th align="center"><?php echo $AppUI->_('End Date'); ?></th>
            <th><?php echo $AppUI->_('Amount'); ?></th>
            <th><?php echo $AppUI->_('Billing Category'); ?></th>
			<th>&nbsp;</th>
        </tr>
        <?php
		$budgets = $budget->getBudgetAmounts();
        foreach ($budgets as $amounts) {
            $start_date = intval($amounts['budget_start_date']) ? new w2p_Utilities_Date($AppUI->formatTZAwareTime($amounts['budget_start_date'], '%Y-%m-%d')) : null;
			$end_date = intval($amounts['budget_end_date']) ? new w2p_Utilities_Date($AppUI->formatTZAwareTime($amounts['budget_end_date'], '%Y-%m-%d')) : null;
			?><tr>
                <td>
                    <a href="?m=system&a=budgeting&budget_id=<?php echo $amounts['budget_id']; ?>" title="<?php echo $AppUI->_('edit'); ?>">
                        <img src="<?php echo w2PfindImage('icons/stock_edit-16.png'); ?>" border="0" alt="<?php echo $AppUI->_('edit'); ?>" />
                    </a>
                    <a href="javascript:delIt(<?php echo (int) $amounts['budget_id']; ?>)" title="<?php echo $AppUI->_('delete'); ?>">
                        <img src="<?php echo w2PfindImage('icons/stock_delete-16.png'); ?>" border="0" alt="<?php echo $AppUI->_('edit'); ?>" style="float: right;" />
                    </a>
                </td>
                <td align="left">&nbsp;<?php echo (('' != $amounts['company_name']) ? $amounts['company_name'] : 'None specified'); ?></td>
                <td align="center">&nbsp;<?php echo $start_date ? $start_date->format($df) : '-'; ?></td>
                <td align="center">&nbsp;<?php echo $end_date ? $end_date->format($df) : '-'; ?></td>
                <td nowrap="nowrap" align="center"><?php echo $amounts['budget_amount']; ?></td>
                <td nowrap="nowrap"><?php echo $budgetCategory[$amounts['budget_category']]; ?></td>
            </tr><?php
        }
		$start_date = intval($budget->budget_start_date) ? new w2p_Utilities_Date($AppUI->formatTZAwareTime($budget->budget_start_date, '%Y-%m-%d')) : null;
		$end_date = intval($budget->budget_end_date) ? new w2p_Utilities_Date($AppUI->formatTZAwareTime($budget->budget_end_date, '%Y-%m-%d')) : null;
		?>
		<tr>
			<td>Add budgeting amount:</td>
			<td align="center">
                <?php
                    echo arraySelect($companies, 'budget_company', 'size="1" class="text"', $budget->budget_company, false);
                ?>
			</td>
            <td align="center">
                <input type="hidden" name="budget_start_date" id="budget_start_date" value="<?php echo $start_date ? $start_date->format(FMT_TIMESTAMP_DATE) : ''; ?>" />
                <input type="text" name="start_date" id="start_date" onchange="setDate_new('frmAddcode', 'start_date');" value="<?php echo $start_date ? $start_date->format($df) : ''; ?>" class="text" />
                <a href="javascript: void(0);" onclick="return showCalendar('start_date', '<?php echo $df ?>', 'frmAddcode', null, true, true)">
                    <img src="<?php echo w2PfindImage('calendar.gif'); ?>" width="24" height="12" alt="<?php echo $AppUI->_('Calendar'); ?>" border="0" />
                </a>
            </td>
            <td align="center">
                <input type="hidden" name="budget_end_date" id="budget_end_date" value="<?php echo $end_date ? $end_date->format(FMT_TIMESTAMP_DATE) : ''; ?>" />
                <input type="text" name="end_date" id="end_date" onchange="setDate_new('frmAddcode', 'end_date');" value="<?php echo $end_date ? $end_date->format($df) : ''; ?>" class="text" />
                <a href="javascript: void(0);" onclick="return showCalendar('end_date', '<?php echo $df ?>', 'frmAddcode', null, true, true)">
                    <img src="<?php echo w2PfindImage('calendar.gif'); ?>" width="24" height="12" alt="<?php echo $AppUI->_('Calendar'); ?>" border="0" />
                </a>
            </td>
			<td align="center">
				<input type="text" class="text" name="budget_amount" value="<?php echo $budget->budget_amount; ?>" size="10" />
			</td>
			<td align="center">
                <?php
                    echo arraySelect($budgetCategory, 'budget_category', 'size="1" class="text"', $budget->budget_category, false);
                ?>
			</td>
			<td align="right" width="20">
				<input class="button" type="button" value="<?php echo $AppUI->_('submit'); ?>" onclick="submitIt()" />
			</td>
		</tr>
	</table>
</form>