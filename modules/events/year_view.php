<?php
/** @var w2p_Core_CAppUI $AppUI */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
// @todo    convert to template

// check permissions for this record
$perms = &$AppUI->acl();
$canRead = canView($m);

if (!$canRead) {
	$AppUI->redirect(ACCESS_DENIED);
}

w2PsetMicroTime();

// retrieve any state parameters
if (isset($_REQUEST['company_id'])) {
	$AppUI->setState('CalIdxCompany', intval(w2PgetParam($_REQUEST, 'company_id', 0)));
}
$company_id = $AppUI->getState('CalIdxCompany', $AppUI->user_company);

// Using simplified set/get semantics. Doesn't need as much code in the module.
$event_filter = $AppUI->checkPrefState('CalIdxFilter', w2PgetParam($_REQUEST, 'event_filter', ''), 'EVENTFILTER', 'my');

// get the passed timestamp (today if none)
$date = w2PgetParam($_GET, 'date', '');

$today = new w2p_Utilities_Date();
$today = $today->format(FMT_TIMESTAMP_DATE);

// get the list of visible companies
$company = new CCompany();
$companies = $company->getAllowedRecords($AppUI->user_id, 'company_id,company_name', 'company_name');
$companies = arrayMerge(array('0' => $AppUI->_('All')), $companies);
$event_filter_list = array('my' => 'My Events', 'own' => 'Events I Created', 'all' => 'All Events');

// setup the title block
$titleBlock = new w2p_Theme_TitleBlock('Yearly Calendar', 'icon.png', $m);
$titleBlock->addCrumb('?m=events&a=year_view&date=' . $date, 'year view');
$titleBlock->addCrumb('?m=events&date=' . $date, 'month view');
$titleBlock->addCrumb('?m=events&a=week_view&date=' . $date, 'week view');
$titleBlock->addCrumb('?m=events&a=day_view&date=' . $date, 'day view');
$titleBlock->addCell('<form action="' . $_SERVER['REQUEST_URI'] . '" method="post" name="pickCompany" accept-charset="utf-8">' . arraySelect($companies, 'company_id', 'onChange="document.pickCompany.submit()" class="text"', $company_id) . '</form>');
$titleBlock->addCell($AppUI->_('Company') . ':');
$titleBlock->addCell(arraySelect($event_filter_list, 'event_filter', 'onChange="document.pickFilter.submit()" class="text"', $event_filter, true), '', '<form action="'.$_SERVER['REQUEST_URI'].'" method="post" name="pickFilter" accept-charset="utf-8">', '</form>');
$titleBlock->addCell($AppUI->_('Event Filter') . ':');
$titleBlock->addButton('New event', '?m=events&a=addedit&date=' . $today);
$titleBlock->show();
?>

<script language="javascript" type="text/javascript">
function clickDay( uts, fdate ) {
	window.location = './index.php?m=events&a=day_view&date='+uts;
}
function clickWeek( uts, fdate ) {
	window.location = './index.php?m=events&a=week_view&date='+uts;
}
</script>

<?php
// establish the focus 'date'
if (!$date) {
	$date = new w2p_Utilities_Date();
} else {
	$date = new w2p_Utilities_Date($date);
}
$date->setDay(1);
$date->setMonth(1);
$prev_year = $date->format(FMT_TIMESTAMP_DATE);
$prev_year = (int)($prev_year - 10000);
$next_year = $date->format(FMT_TIMESTAMP_DATE);
$next_year = (int)($next_year + 10000);

?>
<div class="std w2p-year-view">
	<div class="d-flex align-items-center gap-2 motitle">
		<a href="<?php echo '?m=events&amp;a=year_view&amp;date=' . $prev_year; ?>"><img src="<?php echo w2PfindImage('prev.gif'); ?>" alt="pre" title="pre" /></a>
		<div class="flex-grow-1 text-center fw-bold w2p-cal-month">
			<?php echo htmlspecialchars($date->format('%Y')); ?>
		</div>
		<a href="<?php echo '?m=events&amp;a=year_view&amp;date=' . $next_year; ?>"><img src="<?php echo w2PfindImage('next.gif'); ?>" alt="next" title="next" /></a>
	</div>
	<?php
		$minical = new w2p_Output_MonthCalendar($date);
		$minical->setStyles('minititle', 'minical');
		$minical->showArrows = false;
		$minical->showWeek = true;
		$minical->clickMonth = true;
		$minical->setLinkFunctions('clickDay', 'clickWeek');
		$s = '<div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-3 mx-auto" style="max-width: 60rem;">';

		for ($j = 0; $j < 3; $j++) {
			for ($i = 0; $i <= 3; $i++) {
				// prepare time period for minical'events'
				$first_time = new w2p_Utilities_Date($date);
				$first_time->setDay(1);
				$first_time->setTime(0, 0, 0);
				$last_time = new w2p_Utilities_Date($date);
				$last_time->setDay($date->getDaysInMonth());
				$last_time->setTime(23, 59, 59);
				$links = array();
				// assemble the links for the tasks
				// assemble the links for the events
				//Pedro A.
                $links = getTaskLinks($first_time, $last_time, $links, 20, $company_id, true);
                $links += getEventLinks($first_time, $last_time, $links, 20, true);
				$minical->setEvents($links);
				$minical->setDate($date);
				$s .= '<div class="col w2p-minical">' . $minical->show() . '</div>';
				$date->addMonths(1);
			}
		}
		$s .= '</div>';
		echo $s;
	?>
	<div class="d-flex flex-wrap justify-content-center align-items-center gap-3 my-3 small w2p-cal-key">
		<span><?php echo $AppUI->_('Key'); ?>:</span>
		<span class="text-nowrap"><span class="d-inline-block border w2p-swatch day">&nbsp;</span> <?php echo $AppUI->_('Day'); ?></span>
		<span class="text-nowrap"><span class="d-inline-block border w2p-swatch event">&nbsp;</span> <?php echo $AppUI->_('Event'); ?></span>
		<span class="text-nowrap"><span class="d-inline-block border w2p-swatch task">&nbsp;</span> <?php echo $AppUI->_('Task'); ?></span>
		<span class="text-nowrap"><span class="d-inline-block border w2p-swatch eventtask">&nbsp;</span> <?php echo $AppUI->_('Event'); ?>+<?php echo $AppUI->_('Task'); ?></span>
		<span class="text-nowrap"><span class="d-inline-block border w2p-swatch weekend">&nbsp;</span> <?php echo $AppUI->_('Weekend'); ?></span>
		<span class="text-nowrap"><span class="d-inline-block w2p-swatch today">&nbsp;</span> <?php echo $AppUI->_('Today'); ?></span>
	</div>
</div>