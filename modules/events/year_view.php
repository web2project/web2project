<?php
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
<table class="std view">
	<tr>
		<td align="center">
			<table width="100%" cellspacing="0" cellpadding="4">
				<tr>
					<td colspan="20" valign="top">
				    	<table border="0" cellspacing="1" cellpadding="2" width="100%" class="motitle">
				        	<tr>
				            	<td>
				                	<a href="<?php echo '?m=events&a=year_view&date=' . $prev_year; ?>"><img src="<?php echo w2PfindImage('prev.gif'); ?>" alt="pre" title="pre" /></a>
				                </td>
				                <th width="100%" align="center">
				                	<?php echo htmlspecialchars($date->format('%Y')); ?>
				                </th>
				                <td>
				                	<a href="<?php echo '?m=events&a=year_view&date=' . $next_year; ?>"><img src="<?php echo w2PfindImage('next.gif'); ?>" alt="next" title="next" /></a>
				                </td>
				            </tr>
				        </table>
				    </td>
				</tr>
			</table>
			<?php
				$minical = new w2p_Output_MonthCalendar($date);
				$minical->setStyles('minititle', 'minical');
				$minical->showArrows = false;
				$minical->showWeek = true;
				$minical->clickMonth = true;
				$minical->setLinkFunctions('clickDay', 'clickWeek');
				$s = '';
				
				for ($j = 0; $j < 3; $j++) {
					$s .= '<table cellspacing="0" cellpadding="0" border="0" width="100%"><tr>';
					$s .= '<td valign="top" align="center" width="20%">&nbsp;</td>';
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
                        $links = getEventLinks($first_time, $last_time, $links, 20, true);
						$minical->setEvents($links);
						$minical->setDate($date);
						$s .= '<td valign="top" align="center">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>';
						$s .= '<td valign="top" align="center" width="200">' . $minical->show() . '</td>';
						$date->addMonths(1);
					}
					$s .= '<td valign="top" align="center" width="20%">&nbsp;</td>';
					$s .= '</tr></table>';
				}
				echo $s;
			?>
			<table width="100%" class="minical">
				<tr>
					<td valign="top" align="center" width="20%">&nbsp;</td>
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
					<td width="20%">&nbsp;</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
