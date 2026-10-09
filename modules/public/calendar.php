<?php
/** @var w2p_Core_CAppUI $AppUI */
if (!defined('W2P_BASE_DIR')) {
	die('You should not call this file directly.');
}

$callback = isset($_GET['callback']) ? w2PgetParam($_GET, 'callback', '') : 0;
$date = w2PgetParam($_GET, 'date', null);
$prev_date = w2PgetParam($_GET, 'uts', null);

// if $date is empty, set to null
$date = $date !== '' ? $date : null;

$this_month = new w2p_Utilities_Date($date);

$uistyle = $AppUI->getPref('UISTYLE') ? $AppUI->getPref('UISTYLE') : w2PgetConfig('host_style');
?>
<a href="javascript: void(0);" onclick="clickDay('', '');">clear date</a>
<?php
$cal = new w2p_Output_MonthCalendar($this_month);
$cal->setStyles('poptitle', 'popcal');
$cal->showWeek = false;
$cal->callback = $callback;
$cal->setLinkFunctions('clickDay');

if (isset($prev_date)) {
	$highlights = array($prev_date => '#FF8888');
	$cal->setHighlightedDays($highlights);
	$cal->showHighlightedDays = true;
}

echo $cal->show();
?>
<script language="javascript" type="text/javascript">
/**
 *	@param string Input date in the format YYYYMMDD
 *	@param string Formatted date
 */
	function clickDay( idate, fdate ) {
		window.opener.<?php echo $callback; ?>(idate,fdate);
		window.close();
	}
</script>
<div class="d-flex justify-content-between mt-2">
<?php
$s = '';
for ($i = 0; $i < 12; $i++) {
	$this_month->setMonth($i + 1);
	$s .= '<a href="index.php?m=public&amp;a=calendar&amp;dialog=1&amp;callback=' . $callback . '&amp;date=' . $this_month->format(FMT_TIMESTAMP_DATE) . '&amp;uts=' . $prev_date . '">' . substr($this_month->format('%b'), 0, 1) . '</a>';
}
echo $s;
?>
</div>
<div class="d-flex justify-content-between">
<?php
echo "<a href=\"index.php?m=public&amp;a=calendar&amp;dialog=1&amp;callback=$callback&amp;date=" . $cal->prev_year->format(FMT_TIMESTAMP_DATE) . "&amp;uts=$prev_date\">" . $cal->prev_year->getYear() . '</a>';
echo "<a href=\"index.php?m=public&amp;a=calendar&amp;dialog=1&amp;callback=$callback&amp;date=" . $cal->next_year->format(FMT_TIMESTAMP_DATE) . "&amp;uts=$prev_date\">" . $cal->next_year->getYear() . '</a>';
?>
</div>