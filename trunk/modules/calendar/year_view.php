<?php /* CALENDAR $Id$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

// check permissions for this record
$perms = &$AppUI->acl();
$canRead = $perms->checkModule($m, 'view');

if (!$canRead) {
	$AppUI->redirect('m=public&a=access_denied');
}

$AppUI->savePlace();

w2PsetMicroTime();

require_once ($AppUI->getModuleClass('companies'));
require_once ($AppUI->getModuleClass('tasks'));

// retrieve any state parameters
if (isset($_REQUEST['company_id'])) {
	$AppUI->setState('CalIdxCompany', intval(w2PgetParam($_REQUEST, 'company_id', 0)));
}
$company_id = $AppUI->getState('CalIdxCompany', $AppUI->user_company);

// Using simplified set/get semantics. Doesn't need as much code in the module.
$event_filter = $AppUI->checkPrefState('CalIdxFilter', @w2PgetParam($_REQUEST, 'event_filter', ''), 'EVENTFILTER', 'my');

// get the passed timestamp (today if none)
$date = w2PgetParam($_GET, 'date', '');

// get the list of visible companies
$company = new CCompany();
$companies = $company->getAllowedRecords($AppUI->user_id, 'company_id,company_name', 'company_name');
$companies = arrayMerge(array('0' => $AppUI->_('All')), $companies);

#echo '<pre>';print_r($events);echo '</pre>';
// setup the title block
$titleBlock = new CTitleBlock('Yearly Calendar', 'myevo-appointments.png', $m, "$m.$a");
$titleBlock->addCell($AppUI->_('Company') . ':');
$titleBlock->addCell(arraySelect($companies, 'company_id', 'onChange="document.pickCompany.submit()" class="text"', $company_id), '', '<form action="' . $_SERVER['REQUEST_URI'] . '" method="post" name="pickCompany">', '</form>');
$titleBlock->addCell($AppUI->_('Event Filter') . ':');
$titleBlock->addCell(arraySelect($event_filter_list, 'event_filter', 'onChange="document.pickFilter.submit()" class="text"', $event_filter, true), '', "<Form action='{$_SERVER['REQUEST_URI']}' method='post' name='pickFilter'>", '</form>');
$titleBlock->show();
?>

<script language="javascript">
function clickDay( uts, fdate ) {
	window.location = './index.php?m=calendar&a=day_view&date='+uts;
}
function clickWeek( uts, fdate ) {
	window.location = './index.php?m=calendar&a=week_view&date='+uts;
}
</script>

<?php
// establish the focus 'date'
if (!$date) {
	$date = new CDate();
} else {
	$date = new CDate($date);
}
$date->setDay(1);
$date->setMonth(1);
$prev_year = $date->format(FMT_TIMESTAMP_DATE);
$prev_year = (int)($prev_year - 10000);
$next_year = $date->format(FMT_TIMESTAMP_DATE);
$next_year = (int)($next_year + 10000);

?>
<table class="std" width="100%" cellspacing="0" cellpadding="0">
<tr>
	<td>
		<table width="100%" cellspacing="0" cellpadding="4">
		<tr>
			<td colspan="20" valign="top">
		    	<table border="0" cellspacing="1" cellpadding="2" width="100%" class="motitle">
		        	<tr>
		            	<td>
		                	<a href="<?php echo '?m=calendar&a=year_view&date=' . $prev_year; ?>"><img src="<?php echo w2PfindImage('prev.gif'); ?>" width="16" height="16" alt="pre" title="pre" border="0"></a>
		                </td>
		                <th width="100%" align="center">
		                	<?php echo htmlentities($date->format('%Y')); ?>
		                </th>
		                <td>
		                	<a href="<?php echo '?m=calendar&a=year_view&date=' . $next_year; ?>"><img src="<?php echo w2PfindImage('next.gif'); ?>" width="16" height="16" alt="next" title="next" border="0"></a>
		                </td>
		            </tr>
		        </table>
		    </td>
		</tr>
<?php
$minical = new CMonthCalendar($date);
$minical->setStyles('minititle', 'minical');
$minical->showArrows = false;
$minical->showWeek = true;
$minical->clickMonth = true;
$minical->setLinkFunctions('clickDay', 'clickWeek');
// prepare time period for 1st minical'events'
require_once (w2PgetConfig('root_dir') . "/modules/calendar/links_tasks.php");
require_once (w2PgetConfig('root_dir') . "/modules/calendar/links_events.php");
$first_time = new CDate($date);
$first_time->setDay(1);
$first_time->setTime(0, 0, 0);
$last_time = new CDate($date);
$last_time->setDay($date->getDaysInMonth());
$last_time->setTime(23, 59, 59);

$links = array();

// assemble the links for the tasks
// assemble the links for the events
//Pedro A.
getTaskLinks($first_time, $last_time, $links, 20, $company_id, true);
getEventLinks($first_time, $last_time, $links, 20, true);
$minical->setEvents($links);

echo '<table cellspacing="0" cellpadding="0" border="0" width="100%"><tr>';
echo '<td valign="top" align="center" width="20%">&nbsp;</td>';
echo '<td valign="top" align="center" width="200">' . $minical->show() . '</td>';

$date->addMonths(1);
$first_time = new CDate($date);
$first_time->setDay(1);
$first_time->setTime(0, 0, 0);
$last_time = new CDate($date);
$last_time->setDay($date->getDaysInMonth());
$last_time->setTime(23, 59, 59);
$links = array();
// assemble the links for the tasks
// assemble the links for the events
//Pedro A.
getTaskLinks($first_time, $last_time, $links, 20, $company_id, true);
getEventLinks($first_time, $last_time, $links, 20, true);
$minical->setEvents($links);
$minical->setDate($date);
echo '<td valign="top" align="center">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>';
echo '<td valign="top" align="center" width="200">' . $minical->show() . '</td>';

$date->addMonths(1);
$first_time = new CDate($date);
$first_time->setDay(1);
$first_time->setTime(0, 0, 0);
$last_time = new CDate($date);
$last_time->setDay($date->getDaysInMonth());
$last_time->setTime(23, 59, 59);
$links = array();
// assemble the links for the tasks
// assemble the links for the events
//Pedro A.
getTaskLinks($first_time, $last_time, $links, 20, $company_id, true);
getEventLinks($first_time, $last_time, $links, 20, true);
$minical->setEvents($links);
$minical->setDate($date);
echo '<td valign="top" align="center">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>';
echo '<td valign="top" align="center" width="200">' . $minical->show() . '</td>';

$date->addMonths(1);
$first_time = new CDate($date);
$first_time->setDay(1);
$first_time->setTime(0, 0, 0);
$last_time = new CDate($date);
$last_time->setDay($date->getDaysInMonth());
$last_time->setTime(23, 59, 59);
$links = array();
// assemble the links for the tasks
// assemble the links for the events
//Pedro A.
getTaskLinks($first_time, $last_time, $links, 20, $company_id, true);
getEventLinks($first_time, $last_time, $links, 20, true);
$minical->setEvents($links);
$minical->setDate($date);
echo '<td valign="top" align="center">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>';
echo '<td valign="top" align="center" width="200">' . $minical->show() . '</td>';
echo '<td valign="top" align="center" width="20%">&nbsp;</td>';
echo '</tr></table>';

$date->addMonths(1);
$first_time = new CDate($date);
$first_time->setDay(1);
$first_time->setTime(0, 0, 0);
$last_time = new CDate($date);
$last_time->setDay($date->getDaysInMonth());
$last_time->setTime(23, 59, 59);
$links = array();
// assemble the links for the tasks
// assemble the links for the events
//Pedro A.
getTaskLinks($first_time, $last_time, $links, 20, $company_id, true);
getEventLinks($first_time, $last_time, $links, 20, true);
$minical->setEvents($links);
$minical->setDate($date);
echo '<table cellspacing="0" cellpadding="0" border="0" width="100%"><tr>';
echo '<td valign="top" align="center" width="20%">&nbsp;</td>';
echo '<td valign="top" align="center" width="200">' . $minical->show() . '</td>';

$date->addMonths(1);
$first_time = new CDate($date);
$first_time->setDay(1);
$first_time->setTime(0, 0, 0);
$last_time = new CDate($date);
$last_time->setDay($date->getDaysInMonth());
$last_time->setTime(23, 59, 59);
$links = array();
// assemble the links for the tasks
// assemble the links for the events
//Pedro A.
getTaskLinks($first_time, $last_time, $links, 20, $company_id, true);
getEventLinks($first_time, $last_time, $links, 20, true);
$minical->setEvents($links);
$minical->setDate($date);
echo '<td valign="top" align="center">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>';
echo '<td valign="top" align="center" width="200">' . $minical->show() . '</td>';

$date->addMonths(1);
$first_time = new CDate($date);
$first_time->setDay(1);
$first_time->setTime(0, 0, 0);
$last_time = new CDate($date);
$last_time->setDay($date->getDaysInMonth());
$last_time->setTime(23, 59, 59);
$links = array();
// assemble the links for the tasks
// assemble the links for the events
//Pedro A.
getTaskLinks($first_time, $last_time, $links, 20, $company_id, true);
getEventLinks($first_time, $last_time, $links, 20, true);
$minical->setEvents($links);
$minical->setDate($date);

echo '<td valign="top" align="center">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>';
echo '<td valign="top" align="center" width="200">' . $minical->show() . '</td>';

$date->addMonths(1);
$first_time = new CDate($date);
$first_time->setDay(1);
$first_time->setTime(0, 0, 0);
$last_time = new CDate($date);
$last_time->setDay($date->getDaysInMonth());
$last_time->setTime(23, 59, 59);
$links = array();
// assemble the links for the tasks
// assemble the links for the events
//Pedro A.
getTaskLinks($first_time, $last_time, $links, 20, $company_id, true);
getEventLinks($first_time, $last_time, $links, 20, true);
$minical->setEvents($links);
$minical->setDate($date);
echo '<td valign="top" align="center">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>';
echo '<td valign="top" align="center" width="200">' . $minical->show() . '</td>';
echo '<td valign="top" align="center" width="20%">&nbsp;</td>';
echo '</tr></table>';

$date->addMonths(1);
$first_time = new CDate($date);
$first_time->setDay(1);
$first_time->setTime(0, 0, 0);
$last_time = new CDate($date);
$last_time->setDay($date->getDaysInMonth());
$last_time->setTime(23, 59, 59);
$links = array();
// assemble the links for the tasks
// assemble the links for the events
//Pedro A.
getTaskLinks($first_time, $last_time, $links, 20, $company_id, true);
getEventLinks($first_time, $last_time, $links, 20, true);
$minical->setEvents($links);
$minical->setDate($date);
echo '<table cellspacing="0" cellpadding="0" border="0" width="100%"><tr>';
echo '<td valign="top" align="center" width="20%">&nbsp;</td>';
echo '<td valign="top" align="center" width="200">' . $minical->show() . '</td>';

$date->addMonths(1);
$first_time = new CDate($date);
$first_time->setDay(1);
$first_time->setTime(0, 0, 0);
$last_time = new CDate($date);
$last_time->setDay($date->getDaysInMonth());
$last_time->setTime(23, 59, 59);
$links = array();
// assemble the links for the tasks
// assemble the links for the events
//Pedro A.
getTaskLinks($first_time, $last_time, $links, 20, $company_id, true);
getEventLinks($first_time, $last_time, $links, 20, true);
$minical->setEvents($links);
$minical->setDate($date);

echo '<td valign="top" align="center">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>';
echo '<td valign="top" align="center" width="200">' . $minical->show() . '</td>';

$date->addMonths(1);
$first_time = new CDate($date);
$first_time->setDay(1);
$first_time->setTime(0, 0, 0);
$last_time = new CDate($date);
$last_time->setDay($date->getDaysInMonth());
$last_time->setTime(23, 59, 59);
$links = array();
// assemble the links for the tasks
// assemble the links for the events
//Pedro A.
getTaskLinks($first_time, $last_time, $links, 20, $company_id, true);
getEventLinks($first_time, $last_time, $links, 20, true);
$minical->setEvents($links);
$minical->setDate($date);
echo '<td valign="top" align="center">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>';
echo '<td valign="top" align="center" width="200">' . $minical->show() . '</td>';

$date->addMonths(1);
$first_time = new CDate($date);
$first_time->setDay(1);
$first_time->setTime(0, 0, 0);
$last_time = new CDate($date);
$last_time->setDay($date->getDaysInMonth());
$last_time->setTime(23, 59, 59);
$links = array();
// assemble the links for the tasks
// assemble the links for the events
//Pedro A.
getTaskLinks($first_time, $last_time, $links, 20, $company_id, true);
getEventLinks($first_time, $last_time, $links, 20, true);
$minical->setEvents($links);
$minical->setDate($date);
echo '<td valign="top" align="center">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>';
echo '<td valign="top" align="center" width="200">' . $minical->show() . '</td>';
echo '<td valign="top" align="center" width="20%">&nbsp;</td>';
echo '</tr></table>';
?>
	</td>
</tr>
<tr>
	<td>
		<table width="100%" class="minical">
		<tr>
		  	 <td nowrap="nowrap"><?php echo $AppUI->_('Key'); ?>:</td>
		     <td>&nbsp;</td>
		     <td style="border-style:solid;border-width:1px" class="day">&nbsp;&nbsp;</td>
		     <td nowrap="nowrap"><?php echo $AppUI->_('Day'); ?></td>
		     <td>&nbsp;</td>
		     <td style="border-style:solid;border-width:1px" class="event">&nbsp;&nbsp;</td>
		     <td nowrap="nowrap"><?php echo $AppUI->_('Event'); ?></td>
		     <td>&nbsp;</td>
		     <td style="border-style:solid;border-width:1px" class="task">&nbsp;&nbsp;</td>
		     <td nowrap="nowrap"><?php echo $AppUI->_('Task'); ?></td>
		     <td>&nbsp;</td>
		     <td style="border-style:solid;border-width:1px" class="eventtask">&nbsp;&nbsp;</td>
		     <td nowrap="nowrap"><?php echo $AppUI->_('Event'); ?>+<?php echo $AppUI->_('Task'); ?></td>
		     <td>&nbsp;</td>
		     <td style="border-style:solid;border-width:1px" class="weekend">&nbsp;&nbsp;</td>
		     <td nowrap="nowrap"><?php echo $AppUI->_('Weekend'); ?></td>
		     <td>&nbsp;</td>
		     <td class="today">&nbsp;&nbsp;</td>
		     <td nowrap="nowrap"><?php echo $AppUI->_('Today'); ?></td>
		     <td>&nbsp;</td>
		     <td width="40%">&nbsp;</td>
		</tr>
		</table>
	</td>
</tr>
</table>