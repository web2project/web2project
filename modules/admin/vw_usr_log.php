<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$date_reg = date('Y-m-d');
$start_date = intval($date_reg) ? new CDate(w2PgetParam($_REQUEST, 'log_start_date', date('Y-m-d'))) : null;
$end_date = intval($date_reg) ? new CDate(w2PgetParam($_REQUEST, 'log_end_date', date('Y-m-d'))) : null;

$df = $AppUI->getPref('SHDATEFORMAT');
global $currentTabId, $cal_sdf;
if ($a = w2PgetParam($_REQUEST, 'a', '') == '') {
	$a = '&tab=' . $currentTabId . '&showdetails=1';
} else {
	$user_id = w2PgetParam($_REQUEST, 'user_id', 0);
	$a = '&a=viewuser&user_id=' . $user_id . '&tab=' . $currentTabId . '&showdetails=1';
}

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
function setDate( frm_name, f_date ) {
	fld_date = eval( 'document.' + frm_name + '.' + f_date );
	fld_real_date = eval( 'document.' + frm_name + '.' + 'log_' + f_date );
	if (fld_date.value.length>0) {
      if ((parseDate(fld_date.value))==null) {
            alert('The Date/Time you typed does not match your prefered format, please retype.');
            fld_real_date.value = '';
            fld_date.style.backgroundColor = 'red';
        } else {
        	fld_real_date.value = formatDate(parseDate(fld_date.value), 'yyyyMMdd');
        	fld_date.value = formatDate(parseDate(fld_date.value), '<?php echo $cal_sdf ?>');
            fld_date.style.backgroundColor = '';
  		}
	} else {
      	fld_real_date.value = '';
	}
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
<table align="center" width="100%">
	<tr align="center">
		<td align="right" width="45%" ><?php echo $AppUI->_('Start Date'); ?></td>
			<td width="55%" align="left">
				<input type="hidden" name="log_start_date" id="log_start_date" value="<?php echo $start_date ? $start_date->format(FMT_TIMESTAMP_DATE) : ''; ?>" />
				<input type="text" name="start_date" id="start_date" onchange="setDate('frmDate', 'start_date');" value="<?php echo $start_date ? $start_date->format($df) : ''; ?>" class="text" />
				<a href="javascript: void(0);" onclick="return showCalendar('start_date', '<?php echo $df ?>', 'frmDate', null, true)">
				<img src="<?php echo w2PfindImage('calendar.gif'); ?>" width="24" height="12" alt="<?php echo $AppUI->_('Calendar'); ?>" border="0" /></a>
			</td>
	</tr>
	<tr align="center">
		<td align="right" width="45%"><?php echo $AppUI->_('End Date'); ?></td>
			<td width="55%" align="left">
				<input type="hidden" name="log_end_date" id="log_end_date" value="<?php echo $end_date ? $end_date->format(FMT_TIMESTAMP_DATE) : ''; ?>" />
				<input type="text" name="end_date" id="end_date" onchange="setDate('frmDate', 'end_date');" value="<?php echo $end_date ? $end_date->format($df) : ''; ?>" class="text" />
				<a href="javascript: void(0);" onclick="return showCalendar('end_date', '<?php echo $df ?>', 'frmDate', null, true)">
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
		$start_date = date('Y-m-d', strtotime(w2PgetParam($_REQUEST, 'log_start_date', date('Y-m-d'))));
		$end_date = date('Y-m-d 23:59:59', strtotime(w2PgetParam($_REQUEST, 'log_end_date', date('Y-m-d'))));
        $userId = isset($userId) ? $userId : 0;
		$logs = CUser::getLogs($userId, $start_date, $end_date);
	?>
	<table align="center" class="tbl" width="50%">
		<tr>
			<th nowrap="nowrap" ><?php echo $AppUI->_('Name(s)'); ?></th>
			<th nowrap="nowrap" ><?php echo $AppUI->_('Last Name'); ?></th>
			<th nowrap="nowrap" ><?php echo $AppUI->_('Internet Address'); ?></th>
			<th nowrap="nowrap" ><?php echo $AppUI->_('Date Time IN'); ?></th>
			<th nowrap="nowrap" ><?php echo $AppUI->_('Date Time OUT'); ?></th>
		</tr>
		<?php foreach ($logs as $detail) { ?>
			<tr>
				<td align="center"><?php echo $detail['contact_first_name']; ?></td>
				<td align="center"><?php echo $detail['contact_last_name']; ?></td>
				<td align="center"><?php echo $detail['user_ip']; ?></td>
				<td align="center"><?php echo $detail['date_time_in']; ?></td>
				<td align="center"><?php echo $detail['date_time_out']; ?></td>
			</tr>
		<?php } ?>
	</table>
<?php } ?>