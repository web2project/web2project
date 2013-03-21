<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $AppUI, $company_id, $dept_ids, $department, $min_view, $m, $a, $user_id, $tab, $cal_sdf;
$AppUI->loadCalendarJS();

$min_view = defVal($min_view, false);
$project_id = w2PgetParam($_GET, 'project_id', 0);
$user_id = w2PgetParam($_GET, 'user_id', $AppUI->user_id);
// sdate and edate passed as unix time stamps
$sdate = w2PgetParam($_POST, 'project_start_date', 0);
$edate = w2PgetParam($_POST, 'project_end_date', 0);
$showInactive = w2PgetParam($_POST, 'showInactive', '0');
$showLabels = w2PgetParam($_POST, 'showLabels', '0');
$sortTasksByName = w2PgetParam($_POST, 'sortTasksByName', '0');
$showAllGantt = w2PgetParam($_POST, 'showAllGantt', '0');
$showTaskGantt = w2PgetParam($_POST, 'showTaskGantt', '0');
$addPwOiD = w2PgetParam($_POST, 'add_pwoid', isset($addPwOiD) ? $addPwOiD : 0);

//if set GantChart includes user labels as captions of every GantBar
if ($showLabels != '0') {
	$showLabels = '1';
}
if ($showInactive != '0') {
	$showInactive = '1';
}

if ($showAllGantt != '0') {
	$showAllGantt = '1';
}

if ($sortTasksByName != '0') {
	$sortTasksByName = '1';
}

$projectStatus = w2PgetSysVal('ProjectStatus');

if (isset($_POST['proFilter'])) {
	$AppUI->setState('ProjectIdxFilter', $_POST['proFilter']);
}
$proFilter = $AppUI->getState('ProjectIdxFilter') !== null ? $AppUI->getState('ProjectIdxFilter') : '-1';

$projFilter = arrayMerge(array('-1' => 'All Projects'), $projectStatus);
// months to scroll
$scroll_date = 1;

$display_option = w2PgetParam($_POST, 'display_option', 'this_month');

// format dates
$df = $AppUI->getPref('SHDATEFORMAT');

$start_date = intval($sdate) ? new w2p_Utilities_Date($sdate) : new w2p_Utilities_Date();
$end_date = intval($edate) ? new w2p_Utilities_Date($edate) : new w2p_Utilities_Date();
if ($display_option == 'this_month') {
	// month
	$start_date->day = 1;
	$end_date = new w2p_Utilities_Date($start_date);
	$end_date->addMonths($scroll_date);
}

// setup the title block
if (!$min_view) {
	$titleBlock = new w2p_Theme_TitleBlock('Gantt Chart', 'applet-48.png', $m, $m . '.' . $a);
	$titleBlock->addCrumb('?m=' . $m, 'projects list');
	$titleBlock->show();
}

?>

<script language="javascript" type="text/javascript">
    var calendarField = "";

    function popCalendar(field) {
         calendarField = field;
         idate = eval("document.editFrm." + field + ".value");
         window.open("index.php?m=public&a=calendar&dialog=1&callback=setCalendar&date=" + idate,
                     "calwin", "width=250, height=230, scrollbars=no, status=no"); ////chaged height from 220
    }
    /**
     *     @param string Input date in the format YYYYMMDD
     *     @param string Formatted date
     */
    function setCalendar(idate, fdate) {
         fld_date = eval("document.editFrm." + calendarField);
         fld_fdate = eval("document.editFrm.show_" + calendarField);
         fld_date.value = idate;
         fld_fdate.value = fdate;
    }

    function scrollPrev() {
        f = document.editFrm;
        <?php
        $new_start = new w2p_Utilities_Date($start_date);
        $new_start->day = 1;
        $new_end = new w2p_Utilities_Date($end_date);
        $new_start->addMonths(-$scroll_date);
        $new_end->addMonths(-$scroll_date);
        echo "f.project_start_date.value='" . $new_start->format(FMT_TIMESTAMP_DATE) . "';";
        echo "f.project_end_date.value='" . $new_end->format(FMT_TIMESTAMP_DATE) . "';";
        ?>
	submitIt();
    }

    function scrollNext() {
        f = document.editFrm;
        <?php
        $new_start = new w2p_Utilities_Date($start_date);
        $new_start->day = 1;
        $new_end = new w2p_Utilities_Date($end_date);
        $new_start->addMonths($scroll_date);
        $new_end->addMonths($scroll_date);
        echo "f.project_start_date.value='" . $new_start->format(FMT_TIMESTAMP_DATE) . "';";
        echo "f.project_end_date.value='" . $new_end->format(FMT_TIMESTAMP_DATE) . "';";
        ?>
	submitIt();
    }

    function submitIt() {
         document.editFrm.submit();
    }
</script>


<form name="editFrm" method="post" action="?<?php echo 'm=' . $m . '&a=' . $a . (isset($user_id) ? '&user_id=' . $user_id : '') . '&tab=' . $tab; ?>" accept-charset="utf-8">
    <input type="hidden" name="display_option" value="<?php echo $display_option; ?>" />
    <input type="hidden" name="datePicker" value="project" />

    <table cellspacing="0" cellpadding="2" border="0" width="100%">
    <tr><td valign="top" bgcolor="white">

	<table border="0" cellpadding="4" cellspacing="0" class="std" width="100%">
	        <tr><td valign="top">
			<strong><?php echo $AppUI->_('Period to display'); ?>:</strong><br><br>
			<input type="radio" name="display_option" id="show_all" value="all" <?php echo $display_option == 'all' ? ' checked' : '';?> onclick="submitIt();" />
			<label for="show_all"><?php echo $AppUI->_('The project\'s complete timeline'); ?></label><br>
			<input type="radio" name="display_option" id="single_month" value="this_month" <?php echo $display_option == 'this_month' ? ' checked' : '';?> onclick="submitIt();" />
			<label for="single_month"><?php echo $AppUI->_('A single month'); ?></label><br>
			<input type="radio" name="display_option" id="custom_period" value="custom" <?php echo $display_option == 'custom' ? ' checked' : '';?> onclick="submitIt();" />
			<label for="custom_period"><?php echo $AppUI->_('A custom period'); ?></label><br><br>
	                <input type="hidden" name="project_start_date" id="project_start_date" value="<?php echo $start_date ? $start_date->format(FMT_TIMESTAMP_DATE) : ''; ?>" />
	                <input type="hidden" name="project_end_date" id="project_end_date" value="<?php echo $end_date ? $end_date->format(FMT_TIMESTAMP_DATE) : ''; ?>" />
			<?php if($display_option == 'custom') { ?>
				<table border="0">
				<tr><td><?php echo $AppUI->_('From'); ?>:</td><td>
		                <input type="text" name="start_date" id="start_date" onchange="setDate_new('editFrm', 'start_date');" value="<?php echo $start_date ? $start_date->format($df) : ''; ?>" class="text" />
		                <a href="javascript: void(0);" onclick="return showCalendar('start_date', '<?php echo $df ?>', 'editFrm', null, true, true)">
		                <img style="vertical-align: middle;" src="<?php echo w2PfindImage('calendar.gif'); ?>" width="24" height="12" alt="" border="0" /></a>
				</td></tr>
				<tr><td><?php echo $AppUI->_('To'); ?>:</td><td>
		                <input type="text" name="end_date" id="end_date" onchange="setDate_new('editFrm', 'end_date');" value="<?php echo $end_date ? $end_date->format($df) : ''; ?>" class="text" />
		                <a href="javascript: void(0);" onclick="return showCalendar('end_date', '<?php echo $df ?>', 'editFrm', null, true, true)">
		                <img style="vertical-align: middle;" src="<?php echo w2PfindImage('calendar.gif'); ?>" width="24" height="12" alt="" border="0" /></a>
				</td></tr>
				</table>
			<?php } ?>
		</td><td valign="top">
			<strong><?php echo $AppUI->_('Data to display'); ?>:</strong><br><br>
			<div><?php echo $AppUI->_('Project type') . ':' . arraySelect($projFilter, 'proFilter', 'size="1" class="text"', $proFilter, true); ?></div><br>
			<input type="checkbox" name="showLabels" id="showLabels" value="1" <?php echo (($showLabels == 1) ? 'checked="checked"' : ""); ?> />
			<label for="showLabels"><?php echo $AppUI->_('Show captions'); ?></label><br><br>
			<input type="checkbox" name="showInactive" id="showInactive" <?php echo (($showInactive == 1) ? 'checked="checked"' : ''); ?> />
			<label for="showInactive"><?php echo $AppUI->_('Archived/Template Projects'); ?></label><br>
			<input type="checkbox" name="showAllGantt" id="showAllGantt" <?php echo (($showAllGantt == 1) ? 'checked="checked"' : ''); ?> />
			<label for="showAllGantt"><?php echo $AppUI->_('Show Tasks'); ?></label><br>
			<input type="checkbox" name="sortTasksByName" id="sortTasksByName" <?php echo (($sortTasksByName == 1) ? 'checked="checked"' : ''); ?> />
			<label for="sortTasksByName"><?php echo $AppUI->_('Sort Tasks By Name'); ?></label><br>
		</td><td align="right" valign="bottom">
			<input type="button" class="button" value="<?php echo $AppUI->_('Redraw'); ?>" onclick='submitIt();'" /><br><br>
		</td></tr>
	</table>

    </td><td valign="top" bgcolor="white">

	<table cellspacing="0" cellpadding="2" border="1" align="center" bgcolor="white" width="100%">
  	    <tr><th colspan="2"> Gantt chart key: </th></tr>
	    <tr>
		<td align="right"><?php echo $AppUI->_('Dynamic Task')?>&nbsp;</td>
		<td align="center"><img src="<?php echo W2P_BASE_URL;?>/modules/tasks/images/task_dynamic.png" alt=""/></td>
	    </tr><tr>
		<td align="right">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $AppUI->_('Task (planned)')?>&nbsp;</td>
		<td align="center"><img src="<?php echo W2P_BASE_URL;?>/modules/tasks/images/task_planned.png" alt=""/></td>
	    </tr><tr>
		<td align="right">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $AppUI->_('Task (in progress)')?>&nbsp;</td>
		<td align="center"><img src="<?php echo W2P_BASE_URL;?>/modules/tasks/images/task_in_progress.png" alt=""/></td>
	    </tr><tr>
		<td align="right">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $AppUI->_('Task (completed)')?>&nbsp;</td>
		<td align="center"><img src="<?php echo W2P_BASE_URL;?>/modules/tasks/images/task_completed.png" alt=""/></td>
	    </tr><tr>
		<td align="right"><?php echo $AppUI->_('Milestone (planned)')?>&nbsp;</td>
		<td align="center"><img src="<?php echo W2P_BASE_URL;?>/modules/tasks/images/milestone_planned.png" alt=""/></td>
	    </tr><tr>
		<td align="right">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $AppUI->_('Milestone (completed)')?>&nbsp;</td>
		<td align="center"><img src="<?php echo W2P_BASE_URL;?>/modules/tasks/images/milestone_completed.png" alt=""/></td>
	    </tr><tr>
		<td align="right">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $AppUI->_('Milestone (in progress)')?>&nbsp;</td>
		<td align="center"><img src="<?php echo W2P_BASE_URL;?>/modules/tasks/images/milestone_in_progress.png" alt=""/></td>
	    </tr><tr>
		<td align="right">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $AppUI->_('Milestone (overdue)')?>&nbsp;</td>
		<td align="center"><img src="<?php echo W2P_BASE_URL;?>/modules/tasks/images/milestone_overdue.png" alt=""/></td>
	    </tr>
	</table>
    </td></tr></table>

    <?php if ($display_option == "this_month") { ?>
	<table border="0" cellpadding="4" cellspacing="0" class="std" width="100%">
     		<tr><td align="left" valign="top" width="20px">
			<a href="javascript:scrollPrev()">
			<img src="<?php echo w2PfindImage('prev.gif'); ?>" width="16" height="16" alt="<?php echo $AppUI->_('previous'); ?>" border="0">
			</a>
		</td>
		<?php
			$s = '<th align="center">';
			setlocale(LC_TIME, 'C');
			$s .= $AppUI->_($start_date->format('%B')) . ' ' . $start_date->format('%Y');
			setlocale(LC_ALL, $AppUI->user_lang);
			$s .= '</th>';
			echo $s;
		?>
		<td align="right" valign="top" width="20px">
			<a href="javascript:scrollNext()">
			<img src="<?php echo w2PfindImage('next.gif'); ?>" width="16" height="16" alt="<?php echo $AppUI->_('next'); ?>" border="0" />
	                </a>
		</td></tr>
	</table>
     <?php } ?>

</form>

<table cellspacing="0" cellpadding="0" border="1" align="center" class="std" width="100%">
	<tr><td>
		<?php
			$src = '?m=projects&a=gantt&suppressHeaders=1' . ($display_option == 'all' ? '' : '&start_date=' . $start_date->format('%Y-%m-%d') . '&end_date=' . $end_date->format('%Y-%m-%d')) . "&width=' + ((navigator.appName=='Netscape'?window.innerWidth:document.body.offsetWidth)*0.95) + '&showLabels=$showLabels&sortTasksByName=$sortTasksByName&proFilter=$proFilter&showInactive=$showInactive&company_id=$company_id&department=$department&dept_ids=$dept_ids&showAllGantt=$showAllGantt&user_id=$user_id&addPwOiD=$addPwOiD";
			echo "<script>document.write('<img src=\"$src\">')</script>";
		?>
	</td></tr>
</table>
