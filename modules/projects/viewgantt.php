<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
// @todo    convert to template

global $AppUI, $company_id, $dept_ids, $department, $min_view, $m, $a, $user_id, $tab, $cal_sdf;
$AppUI->getTheme()->loadCalendarJS();

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

if ($display_option == 'custom') {
	// custom dates
	$start_date = intval($sdate) ? new w2p_Utilities_Date($sdate) : new w2p_Utilities_Date();
	$end_date = intval($edate) ? new w2p_Utilities_Date($edate) : new w2p_Utilities_Date();
} else {
	// month
	$start_date = new w2p_Utilities_Date();
	$start_date->day = 1;
	$end_date = new w2p_Utilities_Date($start_date);
	$end_date->addMonths($scroll_date);
}

// setup the title block
if (!$min_view) {
	$titleBlock = new w2p_Theme_TitleBlock('Gantt Chart', 'icon.png', $m);
	$titleBlock->addCrumb('?m=' . $m, 'projects list');
	$titleBlock->show();
}

?>

<script language="javascript" type="text/javascript">

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
	document.editFrm.display_option.value = 'custom';
	f.submit()
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
	document.editFrm.display_option.value = 'custom';
	f.submit()
}

function showThisMonth() {
	document.editFrm.display_option.value = 'this_month';
	document.editFrm.submit();
}

function showFullProject() {
	document.editFrm.display_option.value = 'all';
	document.editFrm.submit();
}

</script>
<form name="editFrm" method="post" action="?<?php echo 'm=' . $m . '&a=' . $a . (isset($user_id) ? '&user_id=' . $user_id : '') . '&tab=' . $tab; ?>" accept-charset="utf-8">
    <input type="hidden" name="display_option" value="<?php echo $display_option; ?>" />
    <input type="hidden" name="datePicker" value="project" />

    <table class="tbl" width="100%" border="0" cellpadding="4" cellspacing="0">
        <tr>
            <td>
                <table border="0" cellpadding="4" cellspacing="0" align="center" class="tbl">
                    <tr>
                        <td align="left" valign="top" width="20">
                        <?php if ($display_option != "all") { ?>
                            <a href="javascript:scrollPrev()">
                                <img src="<?php echo w2PfindImage('prev.gif'); ?>" alt="<?php echo $AppUI->_('previous'); ?>" />
                            </a>
                        <?php } ?>
                        </td>

                        <td align="right" nowrap="nowrap"><?php echo $AppUI->_('From'); ?>:</td>
                        <td align="left" nowrap="nowrap">
                            <input type="hidden" name="project_start_date" id="project_start_date" value="<?php echo $start_date ? $start_date->format(FMT_TIMESTAMP_DATE) : ''; ?>" />
                            <input type="text" name="start_date" id="start_date" onchange="setDate_new('editFrm', 'start_date');" value="<?php echo $start_date ? $start_date->format($df) : ''; ?>" class="text" />
                            <a href="javascript: void(0);" onclick="return showCalendar('start_date', '<?php echo $df ?>', 'editFrm', null, true, true)">
                            <img src="<?php echo w2PfindImage('calendar.gif'); ?>" /></a>
                        </td>

                        <td align="right" nowrap="nowrap"><?php echo $AppUI->_('To'); ?>:</td>
                        <td align="left" nowrap="nowrap">
                            <input type="hidden" name="project_end_date" id="project_end_date" value="<?php echo $end_date ? $end_date->format(FMT_TIMESTAMP_DATE) : ''; ?>" />
                            <input type="text" name="end_date" id="end_date" onchange="setDate_new('editFrm', 'end_date');" value="<?php echo $end_date ? $end_date->format($df) : ''; ?>" class="text" />
                            <a href="javascript: void(0);" onclick="return showCalendar('end_date', '<?php echo $df ?>', 'editFrm', null, true, true)">
                            <img src="<?php echo w2PfindImage('calendar.gif'); ?>" /></a>
                        <td>
                            <?php echo arraySelect($projFilter, 'proFilter', 'size="1" class="text"', $proFilter, true); ?>
                        </td>
                        <td>
                            <input type="checkbox" name="showLabels" id="showLabels" value="1" <?php echo (($showLabels == 1) ? 'checked="checked"' : ""); ?> /><td><label for="showLabels"><?php echo $AppUI->_('Show captions'); ?></label>
                        </td>
                        <td>
                            <input type="checkbox" value="1" name="showInactive" id="showInactive" <?php echo (($showInactive == 1) ? 'checked="checked"' : ""); ?> /><td><label for="showInactive"><?php echo $AppUI->_('Show Archived/Templates'); ?></label>
                        </td>
                        <td>
                            <input type="checkbox" value="1" name="showAllGantt" id="showAllGantt" <?php echo (($showAllGantt == 1) ? 'checked="checked"' : ""); ?> /><td><label for="showAllGantt"><?php echo $AppUI->_('Show Tasks'); ?></label>
                        </td>
                        <td valign="top">
                            <input type="checkbox" value="1" name="sortTasksByName" id="sortTasksByName" <?php echo (($sortTasksByName == 1) ? 'checked="checked"' : ""); ?> /><td><label for="sortTasksByName"><?php echo $AppUI->_('Sort Tasks By Name'); ?></label>
                        </td>
                        <td align="left">
                            <input type="button" class="button" value="<?php echo $AppUI->_('submit'); ?>" onclick='document.editFrm.display_option.value="custom";submit();' />
                        </td>

                        <td align="right" valign="top" width="20">
                        <?php if ($display_option != 'all') { ?>
                            <a href="javascript:scrollNext()">
                                <img src="<?php echo w2PfindImage('next.gif'); ?>" alt="<?php echo $AppUI->_('next'); ?>" />
                            </a>
                        <?php } ?>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" valign="bottom" colspan="16">
                                <?php echo "<a href='javascript:showThisMonth()'>" . $AppUI->_('show this month') . "</a> : <a href='javascript:showFullProject()'>" . $AppUI->_('show all') . "</a><br>"; ?>
                        </td>
                    </tr>
                </table>
                <table cellspacing="0" cellpadding="0" border="1" align="center" class="tbl">
                    <tr>
                        <td>
                            <?php
                            $src = '?m=projects&a=gantt&suppressHeaders=1' . ($display_option == 'all' ? '' : '&start_date=' . $start_date->format('%Y-%m-%d') . '&end_date=' . $end_date->format('%Y-%m-%d')) . "&width=' + ((navigator.appName=='Netscape'?window.innerWidth:document.body.offsetWidth)*0.95) + '&showLabels=$showLabels&sortTasksByName=$sortTasksByName&proFilter=$proFilter&showInactive=$showInactive&company_id=$company_id&department=$department&dept_ids=$dept_ids&showAllGantt=$showAllGantt&user_id=$user_id&addPwOiD=$addPwOiD";
                            echo "<script>document.write('<img src=\"$src\">')</script>";
                            ?>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</form>