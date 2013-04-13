<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$AppUI->loadCalendarJS();

$filter_param = w2PgetParam($_REQUEST, 'filter', '');

$options = array();
$options[-1] = $AppUI->_('All modules');
$options = $options + $AppUI->getActiveModules();
$options['login'] = $AppUI->_('Login/Logouts');

/*
 * This validates that anything provided via the filter_param is definitely an
 *   active module and not some other crazy garbage.
 */
if (!isset($options[$filter_param])) {
    $filter_param = 'projects';
}

// format dates
$df = $AppUI->getPref('SHDATEFORMAT');
$start_date = new w2p_Utilities_Date(w2PgetParam($_REQUEST, 'history_start_date', date('Ymd', time() - 2592000)));
$end_date = new w2p_Utilities_Date(w2PgetParam($_REQUEST, 'history_end_date', date('Ymd')));

?>
<form name="filter" action="?m=history" method="post" accept-charset="utf-8">
<input type="hidden" name="datePicker" value="history" />

<script language="javascript" type="text/javascript">

function setDateSubmit(control) {
    setDate_new('filter', control);
    document.filter.submit();
}

function setCalendarSubmit(control) {
    showCalendar(control, '<?php echo $df; ?>', 'filter', null, true, true);
}

</script>
<?php

$titleBlock = new w2p_Theme_TitleBlock('History', 'stock_book_blue_48.png', 'history', 'history.' . $a);
$titleBlock->addCell('<input type="hidden" name="history_end_date" id="history_end_date" value="' . ($end_date ? $end_date->format(FMT_TIMESTAMP_DATE) : '') . '" />' .
		     '<input type="text" name="end_date" id="end_date" onchange="setDateSubmit(\'end_date\');" value="' . ($end_date ? $end_date->format($df) : '') . '" class="text" size="10"/>' .
                     '<a href="javascript: void(0);" onclick="setCalendarSubmit(\'end_date\')">' .
                     '<img style="vertical-align: middle" src="' . w2PfindImage('calendar.gif') . '" width="24" height="12" alt="' . $AppUI->_('Calendar') . '" border="0" />' .
                     '</a>');
$titleBlock->addCell($AppUI->_('End date') . ': ');
$titleBlock->addCell('<input type="hidden" name="history_start_date" id="history_start_date" value="' . ($start_date ? $start_date->format(FMT_TIMESTAMP_DATE) : '') . '" />' .
		     '<input type="text" name="start_date" id="start_date" onchange="setDateSubmit(\'start_date\');" value="' . ($start_date ? $start_date->format($df) : '') . '" class="text" size="10"/>' .
                     '<a href="javascript: void(0);" onclick="setCalendarSubmit(\'start_date\')">' .
                     '<img style="vertical-align: middle" src="' . w2PfindImage('calendar.gif') . '" width="24" height="12" alt="' . $AppUI->_('Calendar') . '" border="0" />' .
                     '</a>');
$titleBlock->addCell($AppUI->_('Start date') . ': ');
$titleBlock->addCell(arraySelect($options, 'filter', 'size="1" class="text" onChange="document.filter.submit();"', $filter_param, true));
$titleBlock->addCell($AppUI->_('Show history for') . ': ');
$titleBlock->show();

?>
</form>
<?php

$tabBox = new CTabBox('?m=history', W2P_BASE_DIR . '/modules/history/');
$tabBox->add('index_table', $AppUI->_('History'));
$tabBox->show();