<?php
/** @var w2p_Core_CAppUI $AppUI */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
// @todo    remove database query

global $AppUI, $min_view, $m, $a, $user_id, $tab, $tasks, $cal_sdf;
GLOBAL $gantt_map, $currentGanttImgSource, $filter_task_list, $caller;
$AppUI->getTheme()->loadCalendarJS();

$min_view = defVal($min_view, false);

$project_id = (int) w2PgetParam($_GET, 'project_id', 0);

$project = new CProject();
$project->load($project_id);

// sdate and edate passed as unix time stamps
$sdate = w2PgetParam($_POST, 'project_start_date', 0);
$edate = w2PgetParam($_POST, 'project_end_date', 0);

//if set GantChart includes user labels as captions of every GantBar
$showLabels = w2PgetParam($_POST, 'showLabels', '0');
$showLabels = (($showLabels != '0') ? '1' : $showLabels);

$showWork = w2PgetParam($_POST, 'showWork', '0');
$showWork = (($showWork != '0') ? '1' : $showWork);

$showWork_days = w2PgetParam($_POST, 'showWork_days', '0');
$showWork_days = (($showWork_days != '0') ? '1' : $showWork_days);

$showMilestonesOnly = '';
$showNoMilestones = '';
$addLinksToGantt = '';
$ganttTaskFilter = '';
$monospacefont = '';
$showTaskNameOnly = '';
$showhgrid = '';

if ($a == 'todo') {
	if (isset($_POST['show_form'])) {
		$AppUI->setState('TaskDayShowArc', w2PgetParam($_POST, 'showArcProjs', 0));
		$AppUI->setState('TaskDayShowLow', w2PgetParam($_POST, 'showLowTasks', 0));
		$AppUI->setState('TaskDayShowHold', w2PgetParam($_POST, 'showHoldProjs', 0));
		$AppUI->setState('TaskDayShowDyn', w2PgetParam($_POST, 'showDynTasks', 0));
		$AppUI->setState('TaskDayShowPin', w2PgetParam($_POST, 'showPinned', 0));
	}

	$showArcProjs = $AppUI->getState('TaskDayShowArc', 0);
	$showLowTasks = $AppUI->getState('TaskDayShowLow', 1);
	$showHoldProjs = $AppUI->getState('TaskDayShowHold', 0);
	$showDynTasks = $AppUI->getState('TaskDayShowDyn', 0);
	$showPinned = $AppUI->getState('TaskDayShowPin', 0);
} else {
	$showPinned = w2PgetParam($_POST, 'showPinned', '0');
	$showPinned = (($showPinned != '0') ? '1' : $showPinned);

	$showArcProjs = w2PgetParam($_POST, 'showArcProjs', '0');
	$showArcProjs = (($showArcProjs != '0') ? '1' : $showArcProjs);

	$showHoldProjs = w2PgetParam($_POST, 'showHoldProjs', '0');
	$showHoldProjs = (($showHoldProjs != '0') ? '1' : $showHoldProjs);

	$showDynTasks = w2PgetParam($_POST, 'showDynTasks', '0');
	$showDynTasks = (($showDynTasks != '0') ? '1' : $showDynTasks);

	$showLowTasks = w2PgetParam($_POST, 'showLowTasks', '0');
	$showLowTasks = (($showLowTasks != '0') ? '1' : $showLowTasks);
}


/**
  * Prepare the array with the tasks to display in the task filter
  * (for the most part this is code harvested from gantt.php)
  */
$filter_task_list = array();
$projectObject = new CProject();
$projects = $projectObject->getAllowedProjects($AppUI->user_id);

$proTasks = __extract_from_tasks_viewgantt($project_id, $AppUI);

$filter_task_list = array ();
$orrarr[] = array('task_id'=>0, 'order_up'=>0, 'order'=>'');
foreach ($proTasks as $row) {
     $projects[$row['task_project']]['tasks'][] = $row;
}
unset($proTasks);
$parents = array();

foreach ($projects as $p) {
     global $parents, $task_id;
     $parents = array();
     $tnums = 0;
     if (isset($p['tasks'])) {
        $tnums = count($p['tasks']);
     }
     for ($i=0; $i < $tnums; $i++) {
          $t = $p['tasks'][$i];
          if (!(isset($parents[$t['task_parent']]))) {
               $parents[$t['task_parent']] = false;
          }
          if ($t['task_parent'] == $t['task_id']) {
               showfiltertask($t);
               findfiltertaskchild($p['tasks'], $t['task_id']);
          }
     }
     // Check for ophans.
     foreach ($parents as $id => $ok) {
          if (!($ok)) {
               findfiltertaskchild($p['tasks'], $id);
          }
     }
}
/**
 * The results of the above bits are stored in $filter_task_list (array)
 */


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
	$titleBlock->addCrumb('?m=tasks', 'tasks list');
    if ($project_id) {
        $titleBlock->addCrumb('?m=projects&a=view&project_id=' . $project_id, 'view this project');
    } else {
        $titleBlock->addCrumb('?m=projects', 'view all projects');
    }
    $titleBlock->addCrumb('#" onclick="javascript:toggleLayer(\'displayOptions\');', 'show/hide display options');
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
        f.submit();
    }

    function showThisMonth() {
        document.editFrm.display_option.value = "this_month";
        document.editFrm.submit();
    }

    function showFullProject() {
         document.editFrm.display_option.value = "all";
         document.editFrm.submit();
    }

    function toggleLayer( whichLayer ) {
         var elem, vis;
         if( document.getElementById ) // this is the way the standards work
              elem = document.getElementById( whichLayer );
         else if( document.all ) // this is the way old msie versions work
              elem = document.all[whichLayer];
         else if( document.layers ) // this is the way nn4 works
              elem = document.layers[whichLayer];
         vis = elem.style;
         // if the style.display value is blank we try to figure it out here
         if(vis.display==''&&elem.offsetWidth!=undefined&&elem.offsetHeight!=undefined)
              vis.display = (elem.offsetWidth!=0&&elem.offsetHeight!=0)?'block':'none';
              vis.display = (vis.display==''||vis.display=='block')?'none':'block';
    }

    function submitIt() {
         document.editFrm.submit();
    }
</script>

<div id="displayOptions"> <!-- start of div used to show/hide formatting options -->
<form name="editFrm" method="post" action="?<?php echo "m=$m&a=$a&tab=$tab&project_id=$project_id"; ?>" accept-charset="utf-8">
    <input type="hidden" name="display_option" value="<?php echo $display_option; ?>" />
	<input type="hidden" name="caller" value="<?php echo $a; ?>" />
    <input type="hidden" name="datePicker" value="project" />

    <div class="std well p-2">
        <div class="d-flex flex-wrap align-items-center justify-content-center gap-2">
            <?php if ($display_option != "all") { ?>
            <a href="javascript:scrollPrev()">
                <img src="<?php echo w2PfindImage('prev.gif'); ?>" alt="<?php echo $AppUI->_('previous'); ?>" />
            </a>
            <?php } ?>
            <em>Date Filter:</em>
            <span class="text-nowrap"><?php echo $AppUI->_('From'); ?>:</span>
            <span class="text-nowrap">
                <input type="hidden" name="project_start_date" id="project_start_date" value="<?php echo $start_date ? $start_date->format(FMT_TIMESTAMP_DATE) : ''; ?>" />
                <input type="text" name="start_date" id="start_date" onchange="setDate_new('editFrm', 'start_date');" value="<?php echo $start_date ? $start_date->format($df) : ''; ?>" class="text" />
                <a href="javascript: void(0);" onclick="return showCalendar('start_date', '<?php echo $df ?>', 'editFrm', null, true, true)">
                <img src="<?php echo w2PfindImage('calendar.gif'); ?>" alt="" /></a>
            </span>
            <span class="text-nowrap"><?php echo $AppUI->_('To'); ?>:</span>
            <span class="text-nowrap">
                <input type="hidden" name="project_end_date" id="project_end_date" value="<?php echo $end_date ? $end_date->format(FMT_TIMESTAMP_DATE) : ''; ?>" />
                <input type="text" name="end_date" id="end_date" onchange="setDate_new('editFrm', 'end_date');" value="<?php echo $end_date ? $end_date->format($df) : ''; ?>" class="text" />
                <a href="javascript: void(0);" onclick="return showCalendar('end_date', '<?php echo $df ?>', 'editFrm', null, true, true)">
                <img src="<?php echo w2PfindImage('calendar.gif'); ?>" alt="" /></a>
            </span>
            <span class="text-nowrap">
                <input type="checkbox" name="showLabels" id="showLabels" value="1" <?php echo (($showLabels == 1) ? 'checked="checked"' : ""); ?> /><label for="showLabels"><?php echo $AppUI->_('Show captions'); ?></label>
            </span>
            <input type="button" class="button btn btn-primary btn-sm" value="<?php echo $AppUI->_('submit'); ?>" onclick='document.editFrm.display_option.value="custom";submitIt();' />
            <?php if ($display_option != 'all') { ?>
            <a href="javascript:scrollNext()">
                <img src="<?php echo w2PfindImage('next.gif'); ?>" alt="<?php echo $AppUI->_('next'); ?>" />
            </a>
            <?php } ?>
        </div>
        <?php if ($a == 'todo') { ?>
        <input type="hidden" name="show_form" value="1" />
        <div class="d-flex flex-wrap justify-content-center gap-3 mt-2">
            <span class="text-nowrap">
                <input type="checkbox" name="showPinned" id="showPinned" <?php echo $showPinned ? 'checked="checked"' : ''; ?> />
                <label for="showPinned"><?php echo $AppUI->_('Pinned Only'); ?></label>
            </span>
            <span class="text-nowrap">
                <input type="checkbox" name="showArcProjs" id="showArcProjs" <?php echo $showArcProjs ? 'checked="checked"' : ''; ?> />
                <label for="showArcProjs"><?php echo $AppUI->_('Archived/Template Projects'); ?></label>
            </span>
            <span class="text-nowrap">
                <input type="checkbox" name="showDynTasks" id="showDynTasks" <?php echo $showDynTasks ? 'checked="checked"' : ''; ?> />
                <label for="showDynTasks"><?php echo $AppUI->_('Dynamic Tasks'); ?></label>
            </span>
            <span class="text-nowrap">
                <input type="checkbox" name="showLowTasks" id="showLowTasks" <?php echo $showLowTasks ? 'checked="checked"' : ''; ?> />
                <label for="showLowTasks"><?php echo $AppUI->_('Low Priority Tasks'); ?></label>
            </span>
        </div>
        <div id="ganttoptions" style="display:none">
            <input type="hidden" name="show_form" value="1" />
            <div class="d-flex flex-wrap gap-3">
                <span>&nbsp;To Do Options:&nbsp;</span>
                <span class="text-nowrap">
                    <input type="checkbox" name="showPinned" id="showPinned" <?php echo $showPinned ? 'checked="checked"' : ''; ?> />
                    <label for="showPinned"><?php echo $AppUI->_('Pinned Only'); ?></label>
                </span>
                <span class="text-nowrap">
                    <input type="checkbox" name="showArcProjs" id="showArcProjs" <?php echo $showArcProjs ? 'checked="checked"' : ''; ?> />
                    <label for="showArcProjs"><?php echo $AppUI->_('Archived Projects'); ?></label>
                </span>
                <span class="text-nowrap">
                    <input type="checkbox" name="showHoldProjs" id="showHoldProjs" <?php echo $showHoldProjs ? 'checked="checked"' : ''; ?> />
                    <label for="showHoldProjs"><?php echo $AppUI->_('Projects on Hold'); ?></label>
                </span>
                <span class="text-nowrap">
                    <input type="checkbox" name="showDynTasks" id="showDynTasks" <?php echo $showDynTasks ? 'checked="checked"' : ''; ?> />
                    <label for="showDynTasks"><?php echo $AppUI->_('Dynamic Tasks'); ?></label>
                </span>
                <span class="text-nowrap">
                    <input type="checkbox" name="showLowTasks" id="showLowTasks" <?php echo $showLowTasks ? 'checked="checked"' : ''; ?> />
                    <label for="showLowTasks"><?php echo $AppUI->_('Low Priority Tasks'); ?></label>
                </span>
            </div>
        </div>
        <?php } ?>
        <div class="text-center mt-2">
            <?php echo "<a href='javascript:showThisMonth()'>" . $AppUI->_('show this month') . "</a> : <a href='javascript:showFullProject()'>" . ($a == 'todo' ? $AppUI->_('show all') : $AppUI->_('show full project')) . "</a>"; ?>
        </div>
    </div>
</form>
</div> <!-- end of div used to show/hide formatting options -->

<div class="d-flex flex-wrap align-items-center gap-3 border rounded p-2 my-2 small w2p-gantt-key">
	<strong>Gantt chart key:</strong>
	<?php if ($showMilestonesOnly != 1) { ?>
		<span class="text-nowrap"><?php echo $AppUI->_('Dynamic Task')?> <img src="<?php echo W2P_BASE_URL;?>/modules/tasks/images/task_dynamic.png" alt=""/></span>
		<span class="text-nowrap"><?php echo $AppUI->_('Task (planned)')?> <img src="<?php echo W2P_BASE_URL;?>/modules/tasks/images/task_planned.png" alt=""/></span>
		<span class="text-nowrap"><?php echo $AppUI->_('Task (in progress)')?> <img src="<?php echo W2P_BASE_URL;?>/modules/tasks/images/task_in_progress.png" alt=""/></span>
		<span class="text-nowrap"><?php echo $AppUI->_('Task (completed)')?> <img src="<?php echo W2P_BASE_URL;?>/modules/tasks/images/task_completed.png" alt=""/></span>
	<?php } ?>
	<?php if ($showNoMilestones != 1) {	?>
		<span class="text-nowrap"><?php echo $AppUI->_('Milestone (planned)')?> <img src="<?php echo W2P_BASE_URL;?>/modules/tasks/images/milestone_planned.png" alt=""/></span>
		<span class="text-nowrap"><?php echo $AppUI->_('Milestone (completed)')?> <img src="<?php echo W2P_BASE_URL;?>/modules/tasks/images/milestone_completed.png" alt=""/></span>
		<span class="text-nowrap"><?php echo $AppUI->_('Milestone (in progress)')?> <img src="<?php echo W2P_BASE_URL;?>/modules/tasks/images/milestone_in_progress.png" alt=""/></span>
		<span class="text-nowrap"><?php echo $AppUI->_('Milestone (overdue)')?> <img src="<?php echo W2P_BASE_URL;?>/modules/tasks/images/milestone_overdue.png" alt=""/></span>
	<?php } ?>
</div>

<div class="std text-center">
            <?php
            if ($a != 'todo') {
                $cnt[0]['N'] = $project->project_task_count;
            } else {
                 $cnt[0]['N'] = ((empty($tasks)) ? 0 : 1);
            }
            if ($cnt[0]['N'] > 0) {
				 $src = ('?m=tasks&a=gantt&suppressHeaders=1&project_id=' . $project_id 
						 . (($display_option == 'all') ? ''
						    : ('&start_date=' . $start_date->format('%Y-%m-%d')
						       . '&end_date=' . $end_date->format('%Y-%m-%d')))
						 . "&width=' + ((navigator.appName=='Netscape'"
						 . "?window.innerWidth:document.body.offsetWidth)*0.95) + '"
						 . '&showLabels=' . $showLabels . '&showWork=' . $showWork
						 . '&showTaskNameOnly=' . $showTaskNameOnly
					     . '&showhgrid=' . $showhgrid . '&showPinned=' . $showPinned
						 . '&showArcProjs=' . $showArcProjs . '&showHoldProjs=' . $showHoldProjs
						 . '&showDynTasks=' . $showDynTasks . '&showLowTasks=' . $showLowTasks
						 . '&caller=' . $a . '&user_id=' . $user_id
						 . '&showNoMilestones=' . $showNoMilestones . '&showMilestonesOnly=' . $showMilestonesOnly
						 . '&addLinksToGantt=' . $addLinksToGantt . '&ganttTaskFilter=' . $ganttTaskFilter
						 . '&monospacefont=' . $monospacefont . '&showWork_days=' . $showWork_days);

                ?>
                <script language="javascript" type="text/javascript"> document.write('<img alt="Please wait while the Gantt chart is generated... (this might take a minute or two)" src="<?php echo htmlspecialchars($src); ?>" />') </script>
                <?php

				 //If we have a problem displaying this we need to display a warning.
				 //Put it at the bottom just in case
				 if (! w2PcheckMem(32*1024*1024)) {
					  echo '<div class="text-danger fw-bold">' . $AppUI->_('invalid memory config') . '</div>';
				 }
            } else {
                echo $AppUI->_('No tasks to display');
            }
            ?>
</div>
