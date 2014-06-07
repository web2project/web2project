<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
global $AppUI, $cal_sdf;
$AppUI->getTheme()->loadCalendarJS();

$budget_id = (int) w2PgetParam($_GET, 'budget_id', 0);

$canEdit   = canEdit('system');
$canDelete = canView('system');
if (!$canEdit) {
	$AppUI->redirect(ACCESS_DENIED);
}
$df = $AppUI->getPref('SHDATEFORMAT');

// get a list of permitted companies
$company = new CCompany();
$companies = $company->getAllowedRecords($AppUI->user_id, 'company_id,company_name', 'company_name');
$companies = arrayMerge(array('0' => $AppUI->_('None specified')), $companies);

// load the record data
$budget = new CSystem_Budget();
$budget->load($budget_id);

$titleBlock = new w2p_Theme_TitleBlock('Setup Budgets', 'myevo-weather.png', $m);
$titleBlock->addCrumb('?m=system', 'system admin');
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
    <?php
    $fieldList = array('company_name', 'budget_start_date', 'budget_end_date', 'budget_amount', 'budget_category');
    $fieldNames = array('Company', 'Start Date', 'End Date', 'Amount', 'Billing Category');
    
    $htmlHelper = new w2p_Output_HTMLHelper($AppUI);
    $budgetCategory = w2PgetSysVal('BudgetCategory');
    $customLookups = array('budget_category' => $budgetCategory);

    ?>
    <table class="std list">
        <tr>
            <th></th>
            <?php foreach ($fieldNames as $index => $name) { ?>
                <th><?php echo $AppUI->_($fieldNames[$index]); ?></th>
            <?php } ?>
            <th></th>
        </tr>

        <?php
		$budgets = $budget->getBudgetAmounts();
        foreach ($budgets as $row) {
            echo '<tr>';
            echo '<td>';
            if ($canEdit) {
                echo '<a href="?m=system&a=budgeting&budget_id=' . $row['budget_id'] . '" title="' . $AppUI->_('edit') . '">' . w2PshowImage('icons/stock_edit-16.png', '16', '16') . '</a>';
            }
            echo '</td>';

            $htmlHelper->stageRowData($row);
            foreach ($fieldList as $index => $column) {
                echo $htmlHelper->createCell($fieldList[$index], $row[$fieldList[$index]], $customLookups);
            }
            
            echo '<td>';
            if ($canDelete) {
                echo '<a href="javascript:delIt(' . $row['budget_id'] . ')" title="' . $AppUI->_('delete') . '">' . w2PshowImage('icons/stock_delete-16.png', '16', '16') . '</a>';
            }
            echo '</td>';
            echo '</tr>';
        }
		?>
        <tr><td colspan="<?php echo (count($fieldList) + 2); ?>">&nbsp;</td></tr>
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
                    <img src="<?php echo w2PfindImage('calendar.gif'); ?>" alt="<?php echo $AppUI->_('Calendar'); ?>" />
                </a>
            </td>
            <td align="center">
                <input type="hidden" name="budget_end_date" id="budget_end_date" value="<?php echo $end_date ? $end_date->format(FMT_TIMESTAMP_DATE) : ''; ?>" />
                <input type="text" name="end_date" id="end_date" onchange="setDate_new('frmAddcode', 'end_date');" value="<?php echo $end_date ? $end_date->format($df) : ''; ?>" class="text" />
                <a href="javascript: void(0);" onclick="return showCalendar('end_date', '<?php echo $df ?>', 'frmAddcode', null, true, true)">
                    <img src="<?php echo w2PfindImage('calendar.gif'); ?>" alt="<?php echo $AppUI->_('Calendar'); ?>" />
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
				<input class="save button" type="button" value="<?php echo $AppUI->_('save'); ?>" onclick="submitIt()" />
			</td>
		</tr>
	</table>
</form>