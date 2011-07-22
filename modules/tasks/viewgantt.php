<?php /* $Id: viewgantt.php 1506 2010-12-03 05:06:28Z caseydk $ $URL: https://web2project.svn.sourceforge.net/svnroot/web2project/trunk/modules/tasks/viewgantt.php $ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $AppUI, $min_view, $m, $a, $user_id, $tab, $tasks, $cal_sdf;
GLOBAL $sortByName, $project_id, $gantt_map, $currentGanttImgSource, $filter_task_list, $caller;
$AppUI->loadCalendarJS();

$base_url = w2PgetConfig('base_url');
$min_view = defVal($min_view, false);

$project_id = (int) w2PgetParam($_GET, 'project_id', 0);

// sdate and edate passed as unix time stamps
$sdate = w2PgetParam($_POST, 'project_start_date', 0);
$edate = w2PgetParam($_POST, 'project_end_date', 0);


$showWork = w2PgetParam($_POST, 'showWork', '0');
$showWork = (($showWork != '0') ? '1' : $showWork);

$showWork_days = w2PgetParam($_POST, 'showWork_days', '0');
$showWork_days = (($showWork_days != '0') ? '1' : $showWork_days);

$printpdf = w2PgetParam($_POST, 'printpdf', '0');
$printpdf = (($printpdf != '0') ? '1' : $printpdf);

$printpdfhr = w2PgetParam($_POST, 'printpdfhr', '0');
$printpdfhr = (($printpdfhr != '0') ? '1' : $printpdfhr);
		
	
$showPinned = w2PgetParam($_POST, 'showPinned', '0');
if ($showPinned != '0') {
	$showPinned = '1';
}



$showArcProjs = w2PgetParam($_POST, 'showArcProjs', '0');
if ($showArcProjs != '0') {
	$showArcProjs = '1';
}

$showHoldProjs = w2PgetParam($_POST, 'showHoldProjs', '0');
if ($showHoldProjs != '0') {
	$showHoldProjs = '1';
}

$showLowTasks = w2PgetParam($_POST, 'showLowTasks', '0');
if ($showLowTasks != '0') {
	$showLowTasks = '1';
}

if (!isset($_POST['show_form'])) {
	$showDynTasks = '1';
	$showLabels = '1';
} else {
	$showDynTasks = w2PgetParam($_POST, 'showDynTasks', '0');
	if ($showDynTasks != '0') {
		$showDynTasks = '1';
	}	
	//if set GantChart includes user labels as captions of every GantBar
	$showLabels = w2PgetParam($_POST, 'showLabels', '0');
	$showLabels = (($showLabels != '0') ? '1' : $showLabels);
}


/**
  * prepare the array with the tasks to display in the task filter
  * (for the most part this is code harvested from gantt.php)
  *
  */
$filter_task_list = array();
$projectObject = new CProject();
$projects = $projectObject->getAllowedProjects($AppUI->user_id);

$q = new w2p_Database_Query;
$q->addTable('tasks', 't');
$q->addJoin('projects', 'p', 'p.project_id = t.task_project');
$q->addQuery('t.task_id, task_parent, task_name, task_start_date, task_end_date'
             . ', task_duration, task_duration_type, task_priority, task_percent_complete'
             . ', task_order, task_project, task_milestone, project_name, task_dynamic');

$q->addWhere('project_status != 7 AND task_dynamic = 1');
if ($project_id) {
     $q->addWhere('task_project = ' . $project_id);
}
$task =& new CTask;
$task->setAllowedSQL($AppUI->user_id, $q);
$proTasks = $q->loadHashList('task_id');
$q->clear();
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
     $tnums = count($p['tasks']);
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
 * the results of the above bits are stored in $filter_task_list (array)
 * 
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
	$titleBlock = new CTitleBlock('Gantt Chart', 'applet-48.png', $m, $m . '.' . $a);
	$titleBlock->addCrumb('?m=tasks', 'tasks list');
	$titleBlock->addCrumb('?m=projects&a=view&project_id=' . $project_id, 'view this project');
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

    function setDate( frm_name, f_date ) {
        fld_date = eval( 'document.' + frm_name + '.' + f_date );
        fld_real_date = eval( 'document.' + frm_name + '.' + 'project_' + f_date );
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
         document.editFrm.printpdf.value = "0";
         document.editFrm.printpdfhr.value = "0";
        f.submit();
    }

    function showThisMonth() {
        document.editFrm.display_option.value = "this_month";
        document.editFrm.printpdf.value = "0";
        document.editFrm.printpdfhr.value = "0";
        document.editFrm.submit();
    }

    function showFullProject() {
         document.editFrm.display_option.value = "all";
         document.editFrm.printpdf.value = "0";
         document.editFrm.printpdfhr.value = "0";
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

    function printPDFHR() {
         document.editFrm.printpdf.value = "0";
         document.editFrm.printpdfhr.value = "1";
         document.editFrm.submit();
    }

    function submitIt() {
         document.editFrm.printpdf.value = "0";
         document.editFrm.printpdfhr.value = "0";
         document.editFrm.submit();
    }
</script>

<div id="displayOptions"> <!-- start of div used to show/hide formatting options -->

<form name="editFrm" method="post" action="?<?php echo "m=$m&a=$a&tab=$tab&project_id=$project_id"; ?>" accept-charset="utf-8">
    <input type="hidden" name="display_option" value="<?php echo $display_option; ?>" />
	<input type="hidden" name="printpdf" value="<?php echo $printpdf; ?>" />
	<input type="hidden" name="printpdfhr" value="<?php echo $printpdfhr; ?>" />
	<input type="hidden" name="caller" value="<?php echo $a; ?>" />
    <table border="0" cellpadding="4" cellspacing="0" class="std" width="100%">                        
        <tr>
			<input type="hidden" name="show_form" value="1" />
            <td align="left" valign="bottom" nowrap="nowrap" >                
                <table width="100%" border="0" cellpadding="1" cellspacing="1">
                    <tr>
						<td align="center" valign="center" nowrap="nowrap">
                            <input type="checkbox" name="showDynTasks" id="showDynTasks"  value="1" <?php echo $showDynTasks == 1? 'checked="checked"' : ''; ?> />
                            <label for="showDynTasks" ><?php echo $AppUI->_('Dynamic Tasks'); ?></label>
                        </td>
                        <td align="center" valign="center" nowrap="nowrap">
							<input type="checkbox" name="showLabels" id="showLabels" value="1" <?php echo (($showLabels == 1) ? 'checked="checked"' : ""); ?> />
							<label for="showLabels"><?php echo $AppUI->_('Show captions'); ?></label>
						</td>
                        <td align="center" valign="center" nowrap="nowrap">
                            <input type="checkbox" name="showPinned" id="showPinned" value="1" <?php echo $showPinned ? 'checked="checked"' : ''; ?> />
                            <label for="showPinned"><?php echo $AppUI->_('Pinned Only'); ?></label>
                        </td>
                        <td align="center" valign="center" nowrap="nowrap">
                            <input type="checkbox" name="showArcProjs" id="showArcProjs"  value="1" <?php echo $showArcProjs ? 'checked="checked"' : ''; ?> />
                            <label for="showArcProjs"><?php echo $AppUI->_('Archived/Template Projects'); ?></label>
                        </td>                        
                        <td align="center" valign="center" nowrap="nowrap">
                            <input type="checkbox" name="showLowTasks" id="showLowTasks" value="1" <?php echo $showLowTasks ? 'checked="checked"' : ''; ?> />
                            <label for="showLowTasks"><?php echo $AppUI->_('Low Priority Tasks'); ?></label>
                        </td>                        						
                        <td align="center">
							<?php
								if ($other_users) {
									?>
									&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label>  <?php echo $AppUI->_('User')?>  </label>
									<?php
									$selectedUser = w2PgetParam($_POST, 'show_user_todo', $AppUI->user_id);
									$users = $perms->getPermittedUsers('tasks');
									echo arraySelect($users, 'show_user_todo', 'class="text"', $selectedUser);
								}
							?>
						</td>
                    </tr>
                </table>
            </td>
        </tr>                
               
        <tr>	
			<td align="center" valign="bottom">                
                <table width="100%" border="0" cellpadding="1" cellspacing="1">
                    <tr>
						
			<td align="left" valign="center" width="25%">	
				<input type="button" class="button" value="<?php echo ($a == 'todo' ? $AppUI->_('show all') : $AppUI->_('show full project'));?>" onclick='javascript:showFullProject()'  />
				<input type="button" class="button" value="<?php echo $AppUI->_('show this month');?>" onclick='javascript:showThisMonth()'  />				     												           	
			</td> 	
			<td align="center" valign="center" width="50%">                                           
                <a href="javascript:scrollPrev()">
                    <img src="<?php echo w2PfindImage('prev.gif'); ?>" width="16" height="16" alt="<?php echo $AppUI->_('previous'); ?>" border="0">
                </a>
                
				<label><?php echo $AppUI->_('From'); ?>:</label>
                <input type="hidden" name="project_start_date" id="project_start_date" value="<?php echo $start_date ? $start_date->format(FMT_TIMESTAMP_DATE) : ''; ?>" />
                <input type="text" name="start_date" id="start_date" onchange="setDate('editFrm', 'start_date');" value="<?php echo $start_date ? $start_date->format($df) : ''; ?>" class="text" />
                <a href="javascript: void(0);" onclick="return showCalendar('start_date', '<?php echo $df ?>', 'editFrm', null, true)">
                <img src="<?php echo w2PfindImage('calendar.gif'); ?>" width="24" height="12" alt="" border="0" /></a>
				<label><?php echo $AppUI->_('To'); ?>:</label>
                <input type="hidden" name="project_end_date" id="project_end_date" value="<?php echo $end_date ? $end_date->format(FMT_TIMESTAMP_DATE) : ''; ?>" />
                <input type="text" name="end_date" id="end_date" onchange="setDate('editFrm', 'end_date');" value="<?php echo $end_date ? $end_date->format($df) : ''; ?>" class="text" />
                <a href="javascript: void(0);" onclick="return showCalendar('end_date', '<?php echo $df ?>', 'editFrm', null, true)">
                <img src="<?php echo w2PfindImage('calendar.gif'); ?>" width="24" height="12" alt="" border="0" /></a>
                                           
                <a href="javascript:scrollNext()">
                    <img src="<?php echo w2PfindImage('next.gif'); ?>" width="16" height="16" alt="<?php echo $AppUI->_('next'); ?>" border="0" />
                </a>
                     
                <input type="button" class="button" value="<?php echo $AppUI->_('show'); ?>" onclick='document.editFrm.display_option.value="custom";submitIt();'  />           
            </td>                  									          
            	
            
            <td align="right" valign="center" width="25%">
				<input type="button" class="button" value="<?php echo $AppUI->_('Print to PDF');?>" onclick='javascript:printPDFHR()'  />
            </td>  
            </tr>
                </table>
            </td>         
        </tr>
    </table>   
</form>
</div> <!-- end of div used to show/hide formatting options -->

<table cellspacing="0" cellpadding="2" border="1" align="center" bgcolor="white" width="100%">
	<tr><th colspan="9" > Gantt chart key: </th></tr>
	<?php if ($showMilestonesOnly != 1) { ?>
	<tr>
		<td align="right"><?php echo $AppUI->_('Dynamic Task')?>&nbsp;</td>
		<td align="center"><img src="<?php echo W2P_BASE_URL;?>/modules/tasks/images/task_dynamic.png" alt=""/></td>
		<td align="right">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $AppUI->_('Task (planned)')?>&nbsp;</td>
		<td align="center"><img src="<?php echo W2P_BASE_URL;?>/modules/tasks/images/task_planned.png" alt=""/></td>
		<td align="right">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $AppUI->_('Task (in progress)')?>&nbsp;</td>
		<td align="center"><img src="<?php echo W2P_BASE_URL;?>/modules/tasks/images/task_in_progress.png" alt=""/></td>
		<td align="right">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $AppUI->_('Task (completed)')?>&nbsp;</td>
		<td align="center"><img src="<?php echo W2P_BASE_URL;?>/modules/tasks/images/task_completed.png" alt=""/></td>
	</tr>
	<?php } ?>
	<?php if ($showNoMilestones != 1) {	?>
	<tr>
		<td align="right"><?php echo $AppUI->_('Milestone (planned)')?>&nbsp;</td>
		<td align="center"><img src="<?php echo W2P_BASE_URL;?>/modules/tasks/images/milestone_planned.png" alt=""/></td>
		<td align="right">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $AppUI->_('Milestone (completed)')?>&nbsp;</td>
		<td align="center"><img src="<?php echo W2P_BASE_URL;?>/modules/tasks/images/milestone_completed.png" alt=""/></td>
		<td align="right">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $AppUI->_('Milestone (in progress)')?>&nbsp;</td>
		<td align="center"><img src="<?php echo W2P_BASE_URL;?>/modules/tasks/images/milestone_in_progress.png" alt=""/></td>
		<td align="right">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $AppUI->_('Milestone (overdue)')?>&nbsp;</td>
		<td align="center"><img src="<?php echo W2P_BASE_URL;?>/modules/tasks/images/milestone_overdue.png" alt=""/></td>
	</tr>
	<?php } ?>
</table>

<table cellspacing="0" cellpadding="0" border="1" align="center" class="std" width="100%">
    <tr>
        <td valign="top" align="center">
            <?php
            if ($a != 'todo') {
                $q = new w2p_Database_Query;
                $q->addTable('tasks');
                $q->addQuery('COUNT(task_id) AS N');
                $q->addWhere('task_project=' . (int)$project_id);
                $cnt = $q->loadList();
                $q->clear();
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
						 . '&sortByName=' . $sortByName . '&showTaskNameOnly=' . $showTaskNameOnly 
						   . '&showhgrid=' . $showhgrid . '&showPinned=' . $showPinned 
						 . '&showArcProjs=' . $showArcProjs . '&showHoldProjs=' . $showHoldProjs 
						 . '&showDynTasks=' . $showDynTasks . '&showLowTasks=' . $showLowTasks 
						 . '&caller=' . $a . '&user_id=' . $user_id
						   . '&printpdf=' . $printpdf . '&showNoMilestones=' . $showNoMilestones . '&showMilestonesOnly=' . $showMilestonesOnly
						   . '&addLinksToGantt=' . $addLinksToGantt . '&ganttTaskFilter=' . $ganttTaskFilter
						   . '&monospacefont=' . $monospacefont . '&showWork_days=' . $showWork_days);

                ?>
                <script language="javascript" type="text/javascript"> document.write('<img alt="Please wait while the Gantt chart is generated... (this might take a minute or two)" src="<?php echo htmlspecialchars($src); ?>" />') </script>
                <?php

				 //If we have a problem displaying this we need to display a warning.
				 //Put it at the bottom just in case
				 if (! w2PcheckMem(32*1024*1024)) {
					  echo "</td>\n</tr>\n<tr>\n<td>";
					  echo '<span style="color: red; font-weight: bold;">' . $AppUI->_('invalid memory config') . '</span>';
					  echo "\n";
				 }
            } else {
                echo $AppUI->_('No tasks to display');
            }
            ?>
        </td>
    </tr>
	<tr>
		<td>
			<?php
				//POST of all necesary variables to generate gantt in PDF
				$_POST['m'] = 'tasks';
				$_POST['a'] = 'gantt_pdf';
				$_POST['suppressHeaders'] = '1';
				$_POST['start_date'] = $start_date->format('%Y-%m-%d');
				$_POST['end_date'] = $end_date->format('%Y-%m-%d');
				$_POST['display_option'] = $display_option;
				$_POST['showLabels']= $showLabels;
				$_POST['showWork']= $showWork; 
				$_POST['sortByName']= $sortByName;
				$_POST['showTaskNameOnly']= $showTaskNameOnly; 
				$_POST['showhgrid']= $showhgrid;
				$_POST['showPinned']= $showPinned; 
				$_POST['showArcProjs']= $showArcProjs;
				$_POST['showHoldProjs']= $showHoldProjs; 
				$_POST['showDynTasks']= $showDynTasks;
				$_POST['showLowTasks']= $showLowTasks; 
				$_POST['caller']= $a; 
				$_POST['user_id']= $user_id;
				$_POST['printpdfhr']= $printpdfhr;
				$_POST['showPinned']= $showPinned;
				$_POST['showArcProjs']= $showArcProjs;
				$_POST['showHoldProjs']= $showHoldProjs;
				$_POST['showDynTasks']= $showDynTasks;
				$_POST['showLowTasks']= $showLowTasks;

				if ( $printpdf == 1 || $printpdfhr == 1) {
					include 'gantt_pdf.php';
					$_POST['printpdf']= 0; $printpdf = 0;
					$_POST['printpdfhr']= 0; $printpdfhr = 0;
				}
			?>
		</td>
	</tr>
</table>
