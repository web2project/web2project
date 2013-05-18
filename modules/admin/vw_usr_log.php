<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$date_reg = date('Y-m-d');
$start_date = intval($date_reg) ? new w2p_Utilities_Date(w2PgetParam($_REQUEST, 'log_start_date', date('Y-m-d'))) : null;
$end_date = intval($date_reg) ? new w2p_Utilities_Date(w2PgetParam($_REQUEST, 'log_end_date', date('Y-m-d'))) : null;
$user_id = (int) w2PgetParam($_REQUEST, 'user_id', 0);

global $AppUI, $currentTabId, $cal_sdf;
$df = $AppUI->getPref('SHDATEFORMAT');

$a = ($user_id) ? '&a=viewuser&user_id=' . $user_id : '';
$a .= '&tab=' . $currentTabId . '&showdetails=1';

$AppUI->loadCalendarJS();
?>
<script language="javascript" type="text/javascript">
function checkDate(){
    if (document.frmDate.log_start_date.value == '' || document.frmDate.log_end_date.value== ''){
        alert('<?php echo $AppUI->_('You must fill fields', UI_OUTPUT_JS) ?>');
        return false;
    }
    return true;
}
</script>

<table align="center">
	<tr>
		<td>
			<h1><?php echo $AppUI->_('User Log'); ?></h1>
		</td>
	</tr>
</table>

<form action="index.php?m=admin<?php echo $a; ?>" method="post" name="frmDate" accept-charset="utf-8">
    <input type="hidden" name="user_id" id="user_id" value="<?php echo $user_id; ?>" />
    <input type="hidden" name="datePicker" value="log" />
    <table align="center" width="100%">
        <tr align="center">
            <td align="right" width="45%" ><?php echo $AppUI->_('Start Date'); ?></td>
            <td width="55%" align="left">
                <input type="hidden" name="log_start_date" id="log_start_date" value="<?php echo $start_date ? $start_date->format(FMT_TIMESTAMP_DATE) : ''; ?>" />
                <input type="text" name="start_date" id="start_date" onchange="setDate_new('frmDate', 'start_date');" value="<?php echo $start_date ? $start_date->format($df) : ''; ?>" class="text" />
                <a href="javascript: void(0);" onclick="return showCalendar('start_date', '<?php echo $df ?>', 'frmDate', null, true, true)">
                <img src="<?php echo w2PfindImage('calendar.gif'); ?>" width="24" height="12" alt="<?php echo $AppUI->_('Calendar'); ?>" border="0" /></a>
            </td>
        </tr>
        <tr align="center">
            <td align="right" width="45%"><?php echo $AppUI->_('End Date'); ?></td>
            <td width="55%" align="left">
                <input type="hidden" name="log_end_date" id="log_end_date" value="<?php echo $end_date ? $end_date->format(FMT_TIMESTAMP_DATE) : ''; ?>" />
                <input type="text" name="end_date" id="end_date" onchange="setDate_new('frmDate', 'end_date');" value="<?php echo $end_date ? $end_date->format($df) : ''; ?>" class="text" />
                <a href="javascript: void(0);" onclick="return showCalendar('end_date', '<?php echo $df ?>', 'frmDate', null, true, true)">
                <img src="<?php echo w2PfindImage('calendar.gif'); ?>" width="24" height="12" alt="<?php echo $AppUI->_('Calendar'); ?>" border="0" /></a>
            </td>
        </tr>
    </table>
    <table align="center">
        <tr align="center">
            <td><input type="submit" class="button" value="<?php echo $AppUI->_('Submit'); ?>" onclick="return checkDate('start','end')" /></td>
        </tr>
    </table>
</form>

<?php
if (w2PgetParam($_REQUEST, 'showdetails', 0) == 1) {
    
    $fieldList = array('user_username', 'contact_last_name', 'company_name', 'date_time_in', 'user_ip');
    $fieldNames = array('First Name', 'Last Name', 'Internet Address', 'Date Time IN', 'Date Time OUT');
    
    $start_date = date('Y-m-d', strtotime(w2PgetParam($_POST, 'log_start_date', date('Y-m-d'))));
    $start_date = $AppUI->convertToSystemTZ($start_date);
    $end_date = date('Y-m-d 23:59:59', strtotime(w2PgetParam($_POST, 'log_end_date', date('Y-m-d'))));
    $end_date = $AppUI->convertToSystemTZ($end_date);
    $user_id = isset($user_id) ? $user_id : 0;
    $user = new CUser();
    $rows = $user->getLogList($user_id, $start_date, $end_date);
    ?>
    <table class="tbl list center" width="50%">
        <tr>
            <?php foreach ($fieldNames as $index => $name) { ?>
                <th><?php echo $AppUI->_($fieldNames[$index]); ?></th>
            <?php } ?>
        </tr>
        <?php
        $htmlHelper = new w2p_Output_HTMLHelper($AppUI);

        foreach ($rows as $row) {
            $htmlHelper->stageRowData($row);
            ?><tr><?php
                echo $htmlHelper->createCell('na', $row['contact_first_name']);
                echo $htmlHelper->createCell('na', $row['contact_last_name']);
                echo $htmlHelper->createCell('user_ip', $row['user_ip']);
                echo $htmlHelper->createCell('log_in_datetime', $row['date_time_in']);
                if ($row['date_time_out'] != '0000-00-00 00:00:00') {
                    echo $htmlHelper->createCell('log_out_datetime', $row['date_time_out']);
                } else {
                    echo '<td></td>';
                }
            ?></tr><?php
        } ?>
    </table>
    <?php
}