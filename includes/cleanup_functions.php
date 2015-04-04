<?php
/**
* This file exists in order to list individual functions which need to be
*   cleaned up, reorganized or eliminated based on usage. Before you touch
*   these, please ensure there are Unit Tests to validate that things work
*   before and after.
* @todo/TODO: Every single function in this file.
*
* WARNING: The functions in this file are likely to move to other files as they
*   are updated. Since this file is included within main_functions.php
*   this shouldn't be a problem.
*/

function is_task_in_gantt_arr($task)
{
    global $gantt_arr;
    $n = count($gantt_arr);
    for ($x = 0; $x < $n; $x++) {
        if ($gantt_arr[$x][0]['task_id'] == $task['task_id']) {
            return true;
        }
    }

    return false;
}

function notifyHR($address, $notUsed, $uaddress, $uusername, $logname, $notUsed2, $userid)
{
    global $AppUI;
    $emailManager = new w2p_Output_EmailManager($AppUI);
    $body = $emailManager->notifyHR($uusername, $logname, $uaddress, $userid);

    $mail = new w2p_Utilities_Mail();
    $mail->To($address);
    $mail->Subject('New External User Created');
    $mail->Body($body);
    return $mail->Send();
}

function notifyNewUserCredentials($address, $username, $logname, $logpwd)
{
    global $AppUI;
    $emailManager = new w2p_Output_EmailManager($AppUI);
    $body = $emailManager->notifyNewUserCredentials($username, $logname, $logpwd);

    $mail = new w2p_Utilities_Mail();
    $mail->To($address);
    $mail->Subject('New Account Created - web2Project Project Management System');
    $mail->Body($body);
    return $mail->Send();
}

function clean_value($str)
{
    $bad_values = array("'");

    return str_replace($bad_values, '', $str);
}


function strUTF8Decode($text)
{
    if (extension_loaded('mbstring')) {
        $encoding = mb_detect_encoding($text.' ');
    }
    if (function_exists('iconv')) {
        $text = mb_convert_encoding($text, 'UTF-8', $encoding);
        //iconv($encoding, 'UTF-8', $text);
    } elseif (function_exists('utf8_decode')) {
        $text = utf8_decode($text);
    }
    // mb functions don't seam to work well here for some reason as the output gets corrupted.
    // iconv is doing the job just fine though
    return $text;
}

/**
* utility functions for the preparation of task data for GANTT PDF
*
* @todo some of these functions are not needed, need to trim this down
*
*/
// PYS : utf_8 decoding as suggested in Vbulletin #3987
function strEzPdf($text)
{
    if (function_exists('iconv') && function_exists('mb_detect_encoding')) {
        $text = iconv(mb_detect_encoding($text." "), 'UTF-8', $text);
    }

    return $text;
}

function dumb_slice( $gantt_arr, $length = 25 )
{
    $sliced_array = array();

    $pages = (int) count($gantt_arr) / $length;

    for ( $i = 0; $i <= $pages; $i++ ) {
        $sliced_array[] = array_slice($gantt_arr, $i * $length, $length);
    }

    return $sliced_array;
}

/**
*
* 	END OF GANTT PDF UTILITY FUNCTIONS
*
*/

/**
*  This is a kludgy mess because of how the arraySelectTree function is used..
*    it expects - nay, demands! - that the first element of the subarray is the
*    id and the third is the parent id. In most cases, that is fine.. in this
*    one we're using the existing ACL-respecting functions and it has additional
*    fields in "improper" places.
*/
function temp_filterArrayForSelectTree($projectData)
{
    unset($projectData['project_id']);
    unset($projectData['project_color_identifier']);
    unset($projectData['project_name']);
    unset($projectData['project_start_date']);
    unset($projectData['project_end_date']);
    unset($projectData['project_company']);
    unset($projectData['project_parent']);

    unset($projectData[1]);
    unset($projectData[3]);
    unset($projectData[4]);
    unset($projectData[5]);
    $projectData[6] = ($projectData[0] == $projectData[6]) ? '' : $projectData[6];

    return array_values($projectData);
}

function getReadableModule()
{
    $q = new w2p_Database_Query;
    $q->addTable('modules');
    $q->addQuery('mod_directory');
    $q->addWhere('mod_active = 1');
    $q->addOrder('mod_ui_order');
    $modules = $q->loadColumn();
    foreach ($modules as $mod) {
        if (canAccess($mod)) {
            return $mod;
        }
    }

    return null;
}

/**
 * This function is used to check permissions.
 */
function checkFlag($flag, $perm_type, $old_flag)
{
    if ($old_flag) {
        return (($flag == PERM_DENY) || // permission denied
            ($perm_type == PERM_EDIT && $flag == PERM_READ) // we ask for editing, but are only allowed to read
            ) ? 0 : 1;
    } else {
        if ($perm_type == PERM_READ) {
            return ($flag != PERM_DENY) ? 1 : 0;
        } else {
            // => $perm_type == PERM_EDIT
            return ($flag == $perm_type) ? 1 : 0;
        }
    }
}

/**
 * This function checks certain permissions for
 * a given module and optionally an item_id.
 *
 * $perm_type can be PERM_READ or PERM_EDIT
 */
function isAllowed($perm_type, $mod, $item_id = 0)
{
    $invert = false;
    switch ($perm_type) {
        case PERM_READ:
            $perm_type = 'view';
            break;
        case PERM_EDIT:
            $perm_type = 'edit';
            break;
        case PERM_ALL:
            $perm_type = 'edit';
            break;
        case PERM_DENY:
            $perm_type = 'view';
            $invert = true;
            break;
    }
    $allowed = getPermission($mod, $perm_type, $item_id);
    if ($invert) {
        return !$allowed;
    }

    return $allowed;
}

function getPermission($mod, $perm, $item_id = 0)
{
    // First check if the module is readable, i.e. has view permission.
    $perms = &$GLOBALS['AppUI']->acl();
    $result = $perms->checkModule($mod, $perm);
    // If we have access then we need to ensure we are not denied access to the particular
    // item.
    if ($result && $item_id) {
        if ($perms->checkModuleItemDenied($mod, $perm, $item_id)) {
            $result = false;
        }
    }
    // If denied we need to check if we are allowed the task.  This can be done
    // a lot better in PHPGACL, but is here for compatibility.
    if ($mod == 'tasks' && !$result && $item_id > 0) {
        $q = new w2p_Database_Query;
        $q->addTable('tasks');
        $q->addQuery('task_project');
        $q->addWhere('task_id = ' . (int) $item_id);
        $project_id = $q->loadResult();
        $result = getPermission('projects', $perm, $project_id);
    }

    return $result;
}

function canView($mod, $item_id = 0)
{
    return getPermission($mod, 'view', $item_id);
}
function canEdit($mod, $item_id = 0)
{
    return getPermission($mod, 'edit', $item_id);
}
function canAdd($mod, $item_id = 0)
{
    return getPermission($mod, 'add', $item_id);
}
function canDelete($mod, $item_id = 0)
{
    return getPermission($mod, 'delete', $item_id);
}
function canAccess($mod)
{
    return getPermission($mod, 'access');
}

function buildTaskTree($task_data, $depth = 0, $projTasks, $all_tasks, $parents, $task_parent, $task_id)
{
    $output = '';

    $projTasks[$task_data['task_id']] = $task_data['task_name'];
    $task_data['task_name'] = mb_strlen($task_data['task_name']) > 45 ? mb_substr($task_data['task_name'], 0, 45) . '...' : $task_data['task_name'];
    $selected = $task_data['task_id'] == $task_parent ? ' selected="selected"' : '';

    $output .= '<option value="' . $task_data['task_id'] . '"' . $selected . '>' . str_repeat('&nbsp;', $depth * 3) . w2PFormSafe($task_data['task_name']) . '</option>';

    if (isset($parents[$task_data['task_id']])) {
        foreach ($parents[$task_data['task_id']] as $child_task) {
            if ($child_task != $task_id) {
                $output .= buildTaskTree($all_tasks[$child_task], ($depth + 1), $projTasks, $all_tasks, $parents, $task_parent, $task_id);
            }
        }
    }

    return $output;
}

// from modules/tasks/addedit.php and modules/projectdesigners/vw_actions.php
function build_date_list(&$date_array, $row)
{
    global $project;
    // if this task_dynamic is not tracked, set end date to proj start date
    if (!in_array($row['task_dynamic'], CTask::$tracked_dynamics)) {
        $date = new w2p_Utilities_Date($project->project_start_date);
    } elseif ($row['task_milestone'] == 0) {
        $date = new w2p_Utilities_Date($row['task_end_date']);
    } else {
        $date = new w2p_Utilities_Date($row['task_start_date']);
    }
    $sdate = $date->format('%d/%m/%Y');
    $shour = $date->format('%H');
    $smin = $date->format('%M');

    $date_array[$row['task_id']] = array($row['task_name'], $sdate, $shour, $smin);
}

// from modules/tasks/ae_dates.php
function cal_work_day_conv($val)
{
    global $locale_char_set, $AppUI;
    setlocale(LC_TIME, 'en');
    $wk = Date_Calc::getCalendarWeek(null, null, null, '%a', LOCALE_FIRST_DAY);
    setlocale(LC_ALL, $AppUI->user_lang);

    $day_name = $AppUI->_($wk[($val - LOCALE_FIRST_DAY) % 7]);
    $day_name = utf8_encode($day_name);

    return htmlspecialchars($day_name, ENT_COMPAT, $locale_char_set);
}

function __extract_from_showtask(&$arr, $level, $today_view, $listTable, $fields = array())
{
    return '';
}

/**
 * @param $arr
 * @param $level
 * @param $today_view
 * @param $s
 * @param $m
 * @param $jsTaskId
 * @param $expanded
 * @return array
 */
function __extract_from_showtask2($arr, $level, $today_view, $s, $m, $jsTaskId, $expanded)
{
    $s .= '<td style="width: ' . (($today_view) ? '50%' : '90%') . '" class="data _name">';
    //level
    if ($level == -1) {
        $s .= '...';
    }
    for ($y = 0; $y < $level; $y++) {
        if ($y + 1 == $level) {
            $image = w2PfindImage('corner-dots.gif', $m);
        } else {
            $image = w2PfindImage('shim.gif', $m);
        }
        $s .= '<img src="' . $image . '" width="16" height="12"  border="0" alt=""/>';
    }
    if ($arr['task_description'] && !$arr['task_milestone']) {
        $s .= w2PtoolTip('Task Description', substr($arr['task_description'], 0, 1000), true);
    }

    if (isset($arr['task_nr_of_children']) && $arr['task_nr_of_children']) {
        $is_parent = true;
    } else {
        $is_parent = false;
    }
    if ($arr['task_milestone'] > 0) {
        $s .= '&nbsp;<a href="./index.php?m=tasks&amp;a=view&amp;task_id=' . $arr['task_id'] . '" ><b>' . $arr['task_name'] . '</b></a>&nbsp;<img src="' . w2PfindImage('icons/milestone.gif') . '" />';
    } elseif ($arr['task_dynamic'] == '1' || $is_parent) {
        $open_link = '<a href="javascript: void(0);"><img onclick="expand_collapse(\'' . $jsTaskId . '\', \'tblProjects\',\'\',' . ($level++) . ');" id="' . $jsTaskId . '_collapse" src="' . w2PfindImage('icons/collapse.gif') . '" class="center" ' . (!$expanded ? 'style="display:none"' : '') . ' /><img onclick="expand_collapse(\'' . $jsTaskId . '\', \'tblProjects\',\'\',' . ($level++) . ');" id="' . $jsTaskId . '_expand" src="' . w2PfindImage('icons/expand.gif') . '" class="center" ' . ($expanded ? 'style="display:none"' : '') . ' /></a>';
        $s .= $open_link;

        if ($arr['task_dynamic'] == '1') {
            $s .= '&nbsp;<a href="./index.php?m=tasks&amp;a=view&amp;task_id=' . $arr['task_id'] . '" ><b><i>' . $arr['task_name'] . '</i></b></a>';
        } else {
            $s .= '&nbsp;<a href="./index.php?m=tasks&amp;a=view&amp;task_id=' . $arr['task_id'] . '" >' . $arr['task_name'] . '</a>';
        }
    } else {
        $s .= '&nbsp;<a href="./index.php?m=tasks&amp;a=view&amp;task_id=' . $arr['task_id'] . '" >' . $arr['task_name'] . '</a>';
    }
    if ($arr['task_description'] && !$arr['task_milestone']) {
        $s .= w2PendTip();
    }
    $s .= '</td>';

    return $s;
}

function showtask_new(&$arr, $level = 0, $today_view = false, $listTable = null, $fields = array())
{
    return __extract_from_showtask($arr, $level, $today_view, $listTable, $fields);
}

/*
 * 	gantt_arr [ project_id ] [ 0 ]  is a task "object" : 	task['task_id'], task['task_access'], task['task_owner'], task['task_name'], task['project_name']
 * 															task['task_start_date'], task['task_end_date'], task['task_percent_complete'], ['task_milestone']
 * 	gantt_arr [ project_id ] [ 1 ] 	is the level
 *
 * 	project_id is "optional": a 0 value means we re not handling projects
 *
 *	 adds a bidimensional array:
 * 		-1st level: composed of integer project_id
 * 		-2nd level: composed of an array of two items: task "object", integer level
 */
function showgtask(&$a, $level = 0, $notUsed = 0)
{
    /* Add tasks to gantt chart */
    global $gantt_arr;
    if (!is_task_in_gantt_arr($a)) {
        $gantt_arr[] = array($a, $level);
    }
}

function findchild_new(&$tarr, $parent, $level = 0)
{
    global $shown_tasks;

    $level++;
    $n = count($tarr);

    for ($x = 0; $x < $n; $x++) {
        if ($tarr[$x]['task_parent'] == $parent && $tarr[$x]['task_parent'] != $tarr[$x]['task_id']) {
            echo showtask_new($tarr[$x], $level, true);
            $shown_tasks[$tarr[$x]['task_id']] = $tarr[$x]['task_id'];
            findchild_new($tarr, $tarr[$x]['task_id'], $level);
        }
    }
}

function findchild_gantt(&$tarr, $parent, $level = 0)
{
    $level++;
    $n = count($tarr);

    for ($x = 0; $x < $n; $x++) {
        if ($tarr[$x]['task_parent'] == $parent && $tarr[$x]['task_parent'] != $tarr[$x]['task_id']) {
            showgtask($tarr[$x], $level, $tarr[$x]['project_id']);
            findchild_gantt($tarr, $tarr[$x]['task_id'], $level);
        }
    }
}

// from modules/tasks/tasks.class.php
function array_csort() { //coded by Ichier2003

    $args = func_get_args();
    $marray = array_shift($args);

    if (empty($marray)) {
        return array();
    }

    $i = 0;
    $msortline = 'return(array_multisort(';
    $sortarr = array();
    foreach ($args as $arg) {
        $i++;
        if (is_string($arg)) {
            for ($j = 0, $j_cmp = count($marray); $j < $j_cmp; $j++) {

                /* we have to calculate the end_date via start_date+duration for
                ** end='0000-00-00 00:00:00' before sorting, see mantis #1509:

                ** Task definition writes the following to the DB:
                ** A without start date: start = end = NULL
                ** B with start date and empty end date: start = startdate,
                end = '0000-00-00 00:00:00'
                ** C start + end date: start= startdate, end = end date

                ** A the end_date for the middle task (B) is ('dynamically') calculated on display
                ** via start_date+duration, it may be that the order gets wrong due to the fact
                ** that sorting has taken place _before_.
                */
                if ($marray[$j]['task_end_date'] == '0000-00-00 00:00:00') {
                    $marray[$j]['task_end_date'] = calcEndByStartAndDuration($marray[$j]);
                }

                if ('' == $arg) { continue; }

                $sortarr[$i][] = $marray[$j][$arg];
            }
        } else {
            $sortarr[$i] = $arg;
        }
        if (!is_array($sortarr[$i])) {
            continue;
        }
        $msortline .= '$sortarr[' . $i . '],';
    }
    $msortline .= '$marray));';

    eval($msortline);

    return $marray;
}

// from modules/tasks/tasks.class.php
/*
** Calc End Date via Startdate + Duration
** @param array task	A DB row from the earlier fetched tasklist
** @return string	Return calculated end date in MySQL-TIMESTAMP format
*/
function calcEndByStartAndDuration($task)
{
    $end_date = new w2p_Utilities_Date($task['task_start_date']);
    $end_date->addSeconds($task['task_duration'] * $task['task_duration_type'] * 3600);

    return $end_date->format(FMT_DATETIME_MYSQL);
}

// from modules/tasks/tasks.class.php
function sort_by_item_title($title, $item_name, $item_type, $a = '')
{
    global $AppUI, $project_id, $task_id, $m;
    global $task_sort_item1, $task_sort_type1, $task_sort_order1;
    global $task_sort_item2, $task_sort_type2, $task_sort_order2;

    if ($task_sort_item2 == $item_name) {
        $item_order = $task_sort_order2;
    }
    if ($task_sort_item1 == $item_name) {
        $item_order = $task_sort_order1;
    }

    $s = '';

    if (isset($item_order)) {
        $show_icon = true;
    } else {
        $show_icon = false;
        $item_order = SORT_DESC;
    }

    /* flip the sort order for the link */
    $item_order = ($item_order == SORT_ASC) ? SORT_DESC : SORT_ASC;
    if ($m == 'tasks') {
        $s .= '<a href="./index.php?m=tasks' . (($task_id > 0) ? ('&amp;a=view&amp;task_id=' . $task_id) : $a);
    } elseif ($m == 'calendar') {
        $s .= '<a href="./index.php?m=events&amp;a=day_view';
    } else {
        $s .= '<a href="./index.php?m=projects&amp;bypass=1' . (($project_id > 0) ? ('&amp;a=view&amp;project_id=' . $project_id) : '');
    }
    $s .= '&amp;task_sort_item1=' . $item_name;
    $s .= '&amp;task_sort_type1=' . $item_type;
    $s .= '&amp;task_sort_order1=' . $item_order;
    if ($task_sort_item1 == $item_name) {
        $s .= '&amp;task_sort_item2=' . $task_sort_item2;
        $s .= '&amp;task_sort_type2=' . $task_sort_type2;
        $s .= '&amp;task_sort_order2=' . $task_sort_order2;
    } else {
        $s .= '&amp;task_sort_item2=' . $task_sort_item1;
        $s .= '&amp;task_sort_type2=' . $task_sort_type1;
        $s .= '&amp;task_sort_order2=' . $task_sort_order1;
    }
    $s .= '" class="hdr">' . $AppUI->_($title);
    if ($show_icon) {
        $s .= '&nbsp;<img src="' . w2PfindImage('arrow-' . (($item_order == SORT_ASC) ? 'up' : 'down') . '.gif') . '" />';
    }

    return $s.'</a>';
}

// from modules/tasks/tasksperuser_sub.php
function doChildren($list, $N, $id, $uid, $level, $maxlevels, $display_week_hours, $ss, $se)
{
    $tmp = '';
    if ($maxlevels == -1 || $level < $maxlevels) {
        for ($c = 0; $c < $N; $c++) {
            $task = $list[$c];
            if (($task->task_parent == $id) and isChildTask($task)) {
                // we have a child, do we have the user as a member?
                if (isMemberOfTask($list, $N, $uid, $task)) {
                    $tmp .= displayTask($list, $task, $level, $display_week_hours, $ss, $se, $uid);
                    $tmp .= doChildren($list, $N, $task->task_id, $uid, $level++, $maxlevels, $display_week_hours, $ss, $se);
                }
            }
        }
    }

    return $tmp;
}

// from modules/reports/tasksperuser.php
function doChildren_r($list, $Lusers, $N, $id, $uid, $level, $maxlevels, $display_week_hours, $ss, $se, $log_all_projects = false)
{
    $tmp = "";
    if ($maxlevels == -1 || $level < $maxlevels) {
        for ($c = 0; $c < $N; $c++) {
            $task = $list[$c];
            if (($task->task_parent == $id) and isChildTask($task)) {
                // we have a child, do we have the user as a member?
                if (isMemberOfTask_r($list, $Lusers, $N, $uid, $task)) {
                    $tmp .= displayTask_r($list, $task, $level, $display_week_hours, $ss, $se, $log_all_projects, $uid);
                    $tmp .= doChildren_r($list, $Lusers, $N, $task->task_id, $uid, $level++, $maxlevels, $display_week_hours, $ss, $se, $log_all_projects);
                }
            }
        }
    }

    return $tmp;
}

// from modules/tasks/tasksperuser_sub.php
function isMemberOfTask($notUsed, $notUsed2, $user_id, $task)
{
    global $user_assigned_tasks;

    if (isset($user_assigned_tasks[$user_id])) {
        if (in_array($task->task_id, $user_assigned_tasks[$user_id])) {
            return true;
        }
    }

    return false;
}

// from modules/reports/tasksperuser.php
function isMemberOfTask_r($list, $Lusers, $N, $user_id, $task)
{
    for ($i = 0; $i < $N && $list[$i]->task_id != $task->task_id; $i++)
        ;
    $users = $Lusers[$i];

    foreach ($users as $task_user_id => $notUsed) {
        if ($task_user_id == $user_id) {
            return true;
        }
    }

    // check child tasks if any

    for ($c = 0; $c < $N; $c++) {
        $ntask = $list[$c];
        if (($ntask->task_parent == $task->task_id) and isChildTask($ntask)) {
            // we have a child task
            if (isMemberOfTask_r($list, $Lusers, $N, $user_id, $ntask)) {
                return true;
            }
        }
    }

    return false;
}

// from modules/tasks/tasksperuser_sub.php
function displayTask($list, $task, $level, $display_week_hours, $fromPeriod, $toPeriod, $user_id)
{
    global $AppUI, $durnTypes, $active_users, $zi, $projects;
    //if the user has no permission to the project don't show the tasks
    if (!(key_exists($task->task_project, $projects))) {
        return;
    }

    $htmlHelper = new w2p_Output_HTMLHelper($AppUI);

    $zi++;
    $users = $task->task_assigned_users;
    $task->userPriority = $task->getUserSpecificTaskPriority($user_id);
    $project = $task->getProject();
    $tmp = '<tr>';
    $tmp .= '<td align="center" nowrap="nowrap">';
    $tmp .= '<input type="checkbox" name="selected_task[' . $task->task_id . ']" value="' . $task->task_id . '" />';
    $tmp .= '</td>';
    $tmp .= $htmlHelper->createCell('user_priority', $task->userPriority);
    $tmp .= '<td class="_name">';

    for ($i = 0; $i < $level; $i++) {
        $tmp .= '&#160';
    }

    if ($task->task_milestone == true) {
        $tmp .= '<b>';
    }
    if ($level >= 1) {
        $tmp .= '<img src="' . w2PfindImage('corner-dots.gif') . '" width="16" height="12" alt="" style="float: left;">';
    }
    $tmp .= '<a href="?m=tasks&a=view&task_id=' . $task->task_id . '">' . $task->task_name . '</a>';
    if ($task->task_milestone == true) {
        $tmp .= '</b>';
    }
    if ($task->task_priority < 0) {
        $tmp .= '&nbsp;(<img src="' . w2PfindImage('icons/priority-' . -$task->task_priority . '.gif') . '" width="13" height="16" alt="" />)';
    } elseif ($task->task_priority > 0) {
        $tmp .= '&nbsp;(<img src="' . w2PfindImage('icons/priority+' . $task->task_priority . '.gif') . '" width="13" height="16" alt="" />)';
    }
    $tmp .= '</td>';
    $tmp .= '<td align="left">';
    $tmp .= '<a href="?m=projects&a=view&project_id=' . $task->task_project . '" style="background-color:#' . $project['project_color_identifier'] . '; color:' . bestColor($project['project_color_identifier']) . '">' . $project['project_name'] . '</a>';
    $tmp .= '</td>';
    $tmp .= $htmlHelper->createCell('task_duration', $task->task_duration . ' ' . mb_substr($AppUI->_($durnTypes[$task->task_duration_type]), 0, 1));
    $tmp .= $htmlHelper->createCell('task_start_date', $task->task_start_date);
    $tmp .= $htmlHelper->createCell('task_end_date', $task->task_end_date);
    if ($display_week_hours) {
        $tmp .= displayWeeks($list, $task, $level, $fromPeriod, $toPeriod);
    }
    $tmp .= '<td>';
    $sep = $us = '';
    foreach ($users as $notUsed => $row) {
        if ($row['user_id']) {
            $us .= '<a href="?m=users&a=view&user_id=' . $row[0] . '">' . $sep . $row['contact_name'] . '&nbsp;(' . $row['perc_assignment'] . '%)</a>';
            $sep = ', ';
        }
    }
    $tmp .= $us;
    $tmp .= '</td>';

    // create the list of possible assignees
    $size = (count($active_users) > 5) ? 5 : 3;
    $tmp .= '<td valign="top" align="center" nowrap="nowrap">';
    $tmp .= '<select name="add_users" style="width:200px" size="'.$size.'" class="text" multiple="multiple" ondblclick="javascript:chAssignment(' . $user_id . ', 0, false)">';
    foreach ($active_users as $id => $name) {
        $tmp .= '<option value="' . $id . '">' . $name . '</option>';
    }
    $tmp .= '</select>';
    $tmp .= '</td>';

    $tmp .= '</tr>';

    return $tmp;
}

// from modules/reports/tasksperuser.php
function displayTask_r($list, $task, $level, $display_week_hours, $fromPeriod, $toPeriod, $log_all_projects = false, $user_id = 0)
{
    global $AppUI;

    $htmlHelper = new w2p_Output_HTMLHelper($AppUI);

    $tmp = '';
    $tmp .= '<tr><td align="left" nowrap="nowrap">&#160&#160&#160';
    for ($i = 0; $i < $level; $i++) {
        $tmp .= '&#160&#160&#160';
    }
    if ($level == 0) {
        $tmp .= '<b>';
    } elseif ($level == 1) {
        $tmp .= '<i>';
    }
    $tmp .= $task->task_name;
    if ($level == 0) {
        $tmp .= '</b>';
    } elseif ($level == 1) {
        $tmp .= '</i>';
    }
    $tmp .= '&#160&#160&#160</td>';
    if ($log_all_projects) {
        //Show project name when we are logging all projects
        $project = $task->getProject();
        $tmp .= '<td nowrap="nowrap">';
        if (!isChildTask($task)) {
            //However only show the name on parent tasks and not the children to make it a bit cleaner
            $tmp .= $project['project_name'];
        }
        $tmp .= '</td>';
    }

    $tmp .= $htmlHelper->createCell('task_start_date', $task->task_start_date);
    $tmp .= $htmlHelper->createCell('task_end_date', $task->task_end_date);

    if ($display_week_hours) {
        $tmp .= displayWeeks_r($list, $task, $level, $fromPeriod, $toPeriod, $user_id);
    }
    $tmp .= "</tr>\n";

    return $tmp;
}

// from modules/tasks/tasksperuser_sub.php
function isChildTask($task)
{
    return $task->task_id != $task->task_parent;
}

// from modules/tasks/tasksperuser_sub.php
function weekDates($display_allocated_hours, $fromPeriod, $toPeriod)
{
    if ($fromPeriod == -1) {
        return '';
    }
    if (!$display_allocated_hours) {
        return '';
    }

    $s = new w2p_Utilities_Date($fromPeriod);
    $e = new w2p_Utilities_Date($toPeriod);
    $sw = getBeginWeek($s);
    $dw = ceil($e->dateDiff($s) / 7);
    $ew = $sw + $dw;
    $row = '';
    for ($i = $sw; $i <= $ew; $i++) {
        $wn = $s->getWeekofYear() % 52;
        $wn = ($wn != 0) ? $wn : 52;

        $row .= '<th title="' . $s->getYear() . '" nowrap="nowrap">' . $wn . '</th>';
        $s->addSeconds(168 * 3600); // + one week
    }

    return $row;
}

// from modules/reports/tasksperuser.php
function weekDates_r($display_allocated_hours, $fromPeriod, $toPeriod)
{
    global $AppUI;

    if ($fromPeriod == -1) {
        return '';
    }
    if (!$display_allocated_hours) {
        return '';
    }

    $s = new w2p_Utilities_Date($fromPeriod);
    $e = new w2p_Utilities_Date($toPeriod);
    $sw = getBeginWeek($s);
    $ew = getEndWeek($e);

    $row = '';
    for ($i = $sw; $i <= $ew; $i++) {
        $sdf = substr($AppUI->getPref('SHDATEFORMAT'), 3);
        $row .= '<td nowrap="nowrap" bgcolor="#A0A0A0"><font color="black"><b>' . $s->format($sdf) . '</b></font></td>';
        $s->addSeconds(168 * 3600); // + one week
    }

    return $row;
}

// from modules/tasks/tasksperuser_sub.php
function weekCells($display_allocated_hours, $fromPeriod, $toPeriod)
{
    if ($fromPeriod == -1) {
        return 0;
    }
    if (!$display_allocated_hours) {
        return 0;
    }

    $s = new w2p_Utilities_Date($fromPeriod);
    $e = new w2p_Utilities_Date($toPeriod);
    $sw = getBeginWeek($s);
    $dw = ceil($e->dateDiff($s) / 7);
    $ew = $sw + $dw;

    return $ew - $sw + 1;
}

// from modules/reports/tasksperuser.php
function weekCells_r($display_allocated_hours, $fromPeriod, $toPeriod)
{
    if ($fromPeriod == -1) {
        return 0;
    }
    if (!$display_allocated_hours) {
        return 0;
    }

    $s = new w2p_Utilities_Date($fromPeriod);
    $e = new w2p_Utilities_Date($toPeriod);
    $sw = getBeginWeek($s);
    $ew = getEndWeek($e);

    return $ew - $sw + 1;
}

// from modules/tasks/tasksperuser_sub.php
// Look for a user when he/she has been allocated
// to this task and when. Report this in weeks
// This function is called within 'displayTask()'
function displayWeeks($list, $task, $level, $fromPeriod, $toPeriod)
{
    if ($fromPeriod == -1) {
        return '';
    }

    $s = new w2p_Utilities_Date($fromPeriod);
    $e = new w2p_Utilities_Date($toPeriod);
    $sw = getBeginWeek($s);
    $dw = ceil($e->dateDiff($s) / 7);
    $ew = $sw + $dw;

    $st = new w2p_Utilities_Date($task->task_start_date);
    $et = new w2p_Utilities_Date($task->task_end_date);
    $stw = getBeginWeek($st);
    $dtw = ceil($et->dateDiff($st) / 7);
    $etw = $stw + $dtw;

    $row = '';
    for ($i = $sw; $i <= $ew; $i++) {
        if ($i >= $stw and $i < $etw) {
            $color = 'blue';
            if ($level == 0 and hasChildren($list, $task)) {
                $color = '#C0C0FF';
            } elseif ($level == 1 and hasChildren($list, $task)) {
                $color = '#9090FF';
            }
            $row .= '<td  nowrap="nowrap" bgcolor="' . $color . '">';
        } else {
            $row .= '<td nowrap="nowrap">';
        }
        $row .= '&#160&#160</td>';
    }

    return $row;
}

// Look for a user when he/she has been allocated
// to this task and when. Report this in weeks
// This function is called within 'displayTask_r()'
// from modules/reports/tasksperuser.php
function displayWeeks_r($list, $task, $level, $fromPeriod, $toPeriod, $user_id = 0)
{
    if ($fromPeriod == -1) {
        return '';
    }
    $s = new w2p_Utilities_Date($fromPeriod);
    $e = new w2p_Utilities_Date($toPeriod);
    $sw = getBeginWeek($s);
    $ew = getEndWeek($e);

    $st = new w2p_Utilities_Date($task->task_start_date);
    $et = new w2p_Utilities_Date($task->task_end_date);
    $stw = getBeginWeek($st);
    $etw = getEndWeek($et);

    $row = '';
    for ($i = $sw; $i <= $ew; $i++) {
        $assignment = '';

        if ($i >= $stw and $i < $etw) {
            $color = '#0000FF';
            if ($level == 0 and hasChildren($list, $task)) {
                $color = '#C0C0FF';
            } elseif ($level == 1 and hasChildren($list, $task)) {
                $color = '#9090FF';
            }

            if ($user_id) {
                $users = $task->getAssignedUsers($task->task_id);
                $assignment = ($users[$user_id]['perc_assignment']) ? $users[$user_id]['perc_assignment'].'%' : '';
            }
        } else {
            $color = '#FFFFFF';
        }
        $row .= '<td bgcolor="' . $color . '" class="center">';
        $row .= '<font color="'.bestColor($color).'">';
        $row .= $assignment;
        $row .= '</font>';
        $row .= '</td>';
    }

    return $row;
}

// from modules/tasks/tasksperuser_sub.php
// from modules/reports/tasksperuser.php
function getBeginWeek($d)
{
    $dn = (int) $d->Format('%w');
    $dd = new w2p_Utilities_Date($d);
    $dd->subtractSeconds($dn * 24 * 3600);

    return (int) $dd->Format('%U');
}

// from modules/tasks/tasksperuser_sub.php
// from modules/reports/tasksperuser.php
function getEndWeek($d)
{
    $dn = (int) $d->Format('%w');
    if ($dn > 0) {
        $dn = 7 - $dn;
    }
    $dd = new w2p_Utilities_Date($d);
    $dd->addSeconds($dn * 24 * 3600);

    return (int) $dd->Format('%U');
}

// from modules/tasks/tasksperuser_sub.php
// from modules/reports/tasksperuser.php
function hasChildren($list, $task)
{
    foreach ($list as $t) {
        if ($t->task_parent == $task->task_id) {
            return true;
        }
    }

    return false;
}

// from modules/tasks/tasksperuser_sub.php
function getOrphanedTasks($tval)
{
    return (sizeof($tval->task_assigned_users) > 0) ? null : $tval;
}

// from modules/tasks/viewgantt.php
function showfiltertask(&$a, $level=0)
{
     /* Add tasks to the filter task aray */
     global $filter_task_list, $parents;
     $filter_task_list[] = array($a, $level);
     $parents[$a['task_parent']] = true;
}
// from modules/tasks/viewgantt.php
function findfiltertaskchild(&$tarr, $parent, $level=0)
{
     $level++;
     $n = count($tarr);
     for ($x=0; $x < $n; $x++) {
          if ($tarr[$x]['task_parent'] == $parent && $tarr[$x]['task_parent'] != $tarr[$x]['task_id']) {
               showfiltertask($tarr[$x], $level);
               findfiltertaskchild($tarr, $tarr[$x]['task_id'], $level);
          }
     }
}

// from modules/system/roles/roles.class.php
function showRoleRow($role = null)
{
    global $canEdit, $canDelete, $role_id, $AppUI, $roles;

    $id = $role['id'];
    $name = $role['value'];
    $description = $role['name'];

    if (!$id) {
        $roles_arr = array(0 => '(' . $AppUI->_('Copy Role') . '...)');
        foreach ($roles as $role) {
            $roles_arr[$role['id']] = $role['name'];
        }
    }

    $s = '';
    if (($role_id == $id || $id == 0) && $canEdit) {
        // edit form
        $s .= '<form name="roleFrm" method="post" action="?m=system&u=roles" accept-charset="utf-8">';
        $s .= '<input type="hidden" name="dosql" value="do_role_aed" />';
        $s .= '<input type="hidden" name="del" value="0" />';
        $s .= '<input type="hidden" name="role_id" value="' . $id . '" />';
        $s .= '<tr><td>&nbsp;</td>';
        $s .= '<td valign="top"><input type="text" size="20" name="role_name" value="' . $name . '" class="text" /></td>';
        $s .= '<td valign="top"><input type="text" size="50" name="role_description" class="text" value="' . $description . '">' . ($id ? '' : '&nbsp;&nbsp;&nbsp;&nbsp;' . arraySelect($roles_arr, 'copy_role_id', 'class="text"', 0, true));
        $s .= '<input type="submit" value="' . $AppUI->_($id ? 'save' : 'add') . '" class="button btn btn-primary btn-mini right" /></td>';
    } else {
        $s .= '<tr><td width="50" valign="top">';
        if ($canEdit) {
            $s .= '<a href="?m=system&u=roles&role_id=' . $id . '">' . w2PshowImage('icons/stock_edit-16.png') . '</a><a href="?m=system&u=roles&a=viewrole&role_id=' . $id . '" title="">' . w2PshowImage('obj/lock.gif') . '</a>';
        }
        if ($canDelete && strpos($name, 'admin') === false) {
            $s .= '<a href=\'javascript:delIt(' . $id . ')\'>' . w2PshowImage('icons/stock_delete-16.png') . '</a>';
        }
        $s .= '</td><td valign="top">' . $name . '</td><td valign="top">' . $AppUI->_($description) . '</td>';
    }
    $s .= '</tr>';

    return $s;
}

// from modules/system/syskeys/syskeys.class.php
function parseFormatSysval($text, $syskey)
{
    $q = new w2p_Database_Query;
    $q->addTable('syskeys');
    $q->addQuery('syskey_type, syskey_sep1, syskey_sep2');
    $q->addWhere('syskey_id = ' . (int) $syskey);
    $q->exec();
    $row = $q->fetchRow();
    $q->clear();
    // type 0 = list
    $sep1 = $row['syskey_sep1']; // item separator
    $sep2 = $row['syskey_sep2']; // alias separator

    // A bit of magic to handle newlines and returns as separators
    // Missing sep1 is treated as a newline.
    if (!isset($sep1) || empty($sep1)) {
        $sep1 = "\n";
    }
    if ($sep1 == "\\n") {
        $sep1 = "\n";
    }
    if ($sep1 == "\\r") {
        $sep1 = "\r";
    }

    $temp = explode($sep1, $text);
    $arr = array();
    // We use trim() to make sure a numeric that has spaces
    // is properly treated as a numeric
    foreach ($temp as $item) {
        if ($item) {
            $sep2 = empty($sep2) ? "\n" : $sep2;
            $temp2 = explode($sep2, $item);
            if (isset($temp2[1])) {
                $arr[mb_trim($temp2[0])] = mb_trim($temp2[1]);
            } else {
                $arr[mb_trim($temp2[0])] = mb_trim($temp2[0]);
            }
        }
    }

    return $arr;
}

// from modules/system/billingcode.php
function showcodes(&$a)
{
    global $AppUI, $company_id;

    $s = '
<tr>
    <td width=40>
        <a href="?m=system&amp;a=billingcode&amp;company_id=' . $company_id . '&amp;billingcode_id=' . $a['billingcode_id'] . '" title="' . $AppUI->_('edit') . '">
            <img src="' . w2PfindImage('icons/stock_edit-16.png') . '" alt="Edit" /></a>';

    if ($a['billingcode_status'] == 0)
        $s .= '<a href="javascript:delIt2(' . $a['billingcode_id'] . ');" title="' . $AppUI->_('delete') . '">
            <img src="' . w2PfindImage('icons/stock_delete-16.png') . '" alt="Delete" /></a>';

    $s .= '
    </td>
    <td align="left">&nbsp;' . $a['billingcode_name'] . ($a['billingcode_status'] == 1 ? ' (deleted)' : '') . '</td>
    <td nowrap="nowrap" align="center">' . $a['billingcode_value'] . '</td>
    <td nowrap="nowrap">' . $a['billingcode_desc'] . '</td>
</tr>';

    return $s;
}

// from modules/smartsearch/smartsearch.class.php
function highlight($text, $keyval)
{
    global $ssearch;

    $txt = $text;
    $keys = (!is_array($keyval)) ? array($keyval) : $keyval;

    foreach ($keys as $key_idx => $key) {
        if (mb_strlen($key) > 0) {
            $key = stripslashes($key);
            $metacharacters = array('\\', '(', ')', '$', '[', '*', '+', '|', '.', '^', '?');
            $metareplacement = array('\\\\', '\(', '\)', '\$', '\[', '\*', '\+', '\|', '\.', '\^', '\?');
            $key = mb_str_replace($metacharacters, $metareplacement, $key);
            if (isset($ssearch['ignore_specchar']) && ($ssearch['ignore_specchar'] == 'on')) {
                if ($ssearch['ignore_case'] == 'on') {
                    $txt = preg_replace('/'.recode2regexp_utf8($key).'/i', '<span class="highlight' . $key_idx . '" >\\0</span>', $txt);
                } else {
                    $txt = preg_replace('/'.(recode2regexp_utf8($key)).'/', '<span class="highlight' . $key_idx . '" >\\0</span>', $txt);
                }
            } elseif (!isset($ssearch['ignore_specchar']) || ($ssearch['ignore_specchar'] == '')) {
                if ($ssearch['ignore_case'] == 'on') {
                    $txt = preg_replace('/'.$key.'/i', '<span class="highlight' . $key_idx . '" >\\0</span>', $txt);
                } else {
                    $txt = preg_replace('/'.$key.'/', '<span class="highlight' . $key_idx . '" >\\0</span>', $txt);
                }
            } else {
                $txt = preg_replace('/'.$key.'/i', '<span class="highlight:' . $key_idx . '" >\\0</span>', $txt);
            }
        }
    }

    return $txt;
}

// from modules/smartsearch/smartsearch.class.php
function recode2regexp_utf8($input)
{
    $result = '';
    for ($i = 0, $i_cmp = mb_strlen($input); $i < $i_cmp; ++$i)
        switch ($input[$i]) {
            case 'A':
            case 'a':
                $result .= '(a|A!|A�|A?|A�)';
                break;
            case 'C':
            case 'c':
                $result .= '(c|�?|�O)';
                break;
            case 'D':
            case 'd':
                $result .= '(d|�?|Ď)';
                break;
            case 'E':
            case 'e':
                $result .= '(e|A�|ě|A�|Ě)';
                break;
            case 'I':
            case 'i':
                $result .= '(i|A�|A?)';
                break;
            case 'L':
            case 'l':
                $result .= '(l|�o|�3|�1|�1)';
                break;
            case 'N':
            case 'n':
                $result .= '(n|A^|A�)';
                break;
            case 'O':
            case 'o':
                $result .= '(o|A3|A�|A�|A�)';
                break;
            case 'R':
            case 'r':
                $result .= '(r|A�|A�|A�|A~)';
                break;
            case 'S':
            case 's':
                $result .= '(s|A!|A�)';
                break;
            case 'T':
            case 't':
                $result .= '(t|AY|A�)';
                break;
            case 'U':
            case 'u':
                $result .= '(u|Ao|A�|A�|A�)';
                break;
            case 'Y':
            case 'y':
                $result .= '(y|A1|A?)';
                break;
            case 'Z':
            case 'z':
                $result .= '(z|A3|A1)';
                break;
            default:
                $result .= $input[$i];
        }

    return $result;
}

// from modules/public/selector.php
function selPermWhere($obj, $idfld, $namefield, $prefix = '')
{
    global $AppUI;

    $allowed = $obj->getAllowedRecords($AppUI->user_id, $idfld . ', ' . $namefield, '', '', '', $prefix);
    if (count($allowed)) {
        return ' ' . $idfld . ' IN (' . implode(',', array_keys($allowed)) . ') ';
    } else {
        return null;
    }
}

//comes from modules/departments/departments.class.php
//writes out a single <option> element for display of departments
function showchilddept(&$a, $level = 1)
{
    global $department;
    $s = '<option value="' . $a['dept_id'] . '"' . (isset($department) && $department == $a['dept_id'] ? 'selected="selected"' : '') . '>';

    for ($y = 0; $y < $level; $y++) {
        if ($y + 1 == $level) {
            $s .= '';
        } else {
            $s .= '&nbsp;&nbsp;';
        }
    }

    $s .= '&nbsp;&nbsp;' . $a['dept_name'] . '</option>';

    return $s;
}

//comes from modules/departments/departments.class.php
//recursive function to display children departments.
function findchilddept(&$tarr, $parent, $level = 1)
{
    $level++;
    $n = count($tarr);
    for ($x = 0; $x < $n; $x++) {
        if ($tarr[$x]['dept_parent'] == $parent && $tarr[$x]['dept_parent'] != $tarr[$x]['dept_id']) {
            findchilddept($tarr, $tarr[$x]['dept_id'], $level);
        }
    }
}

//comes from modules/departments/departments.class.php
function addDeptId($dataset, $parent)
{
    global $dept_ids;
    foreach ($dataset as $data) {
        if ($data['dept_parent'] == $parent) {
            $dept_ids[] = $data['dept_id'];
            addDeptId($dataset, $data['dept_id']);
        }
    }
}

// From: modules/files/filefolder.class.php
function getFolderSelectList()
{
    global $AppUI;

    $q = new w2p_Database_Query();
    $q->addTable('file_folders');
    $q->addQuery('file_folder_id, file_folder_name, file_folder_parent');
    $q->addOrder('file_folder_name');
    $folderList = $q->loadHashList('file_folder_id');

    $folders = array(0 => 'Root');
    foreach($folderList as $folder => $data) {
        $folders[$folder] = $data['file_folder_name'];
    }

    return $folders;
}

/*
 * $parent is the parent of the children we want to see
 * $level is increased when we go deeper into the tree, used to display a nice indented tree
 */
// From: modules/files/filefolder.class.php
function getFolders($parent)
{
    global $AppUI, $allowed_folders_ary, $tab, $m, $a, $company_id, $project_id, $task_id;
    // retrieve all children of $parent

    $file_folder = new CFile_Folder();
    $folders = $file_folder->getFoldersByParent($parent);

    $s = '';
    // display each child
    foreach ($folders as $row) {
        if (array_key_exists($row['file_folder_id'], $allowed_folders_ary) or array_key_exists($parent, $allowed_folders_ary)) {
            $file_count = countFiles($row['file_folder_id']);

            $s .= '<tr><td colspan="20">';
            $s .= '<ul>';
            $s .= '<li><a href="./index.php?m=files&amp;a=addedit_folder&amp;file_folder_parent=' . $row['file_folder_id'] . '&amp;file_folder_id=0">' . w2PshowImage('edit_add.png', '', '', 'new folder', 'add a new subfolder', 'files') . '</a></li>';
            $s .= '<li><a href="./index.php?m=files&amp;a=addedit&amp;folder=' . $row['file_folder_id'] . '&amp;project_id=' . $project_id . '&amp;file_id=0">' . w2PshowImage('folder_new.png', '', '', 'new file', 'add new file to this folder', 'files') . '</a></li>';
            $s .= '<li><a href="./index.php?m=files&amp;a=addedit_folder&amp;folder=' . $row['file_folder_id'] . '">' . w2PshowImage('filesaveas.png', '', '', 'edit icon', 'edit this folder', 'files') . '</a></li>';
            if ($m == 'files') {
                $s .= '<li class="info-text"><a href="./index.php?m=' . $m . '&amp;a=' . $a . '&amp;tab=' . $tab . '&folder=' . $row['file_folder_id'] . '" name="ff' . $row['file_folder_id'] . '">';
            }
            $s .= w2PshowImage('folder5_small.png', '22', '22', '', '', 'files');
            $s .= $row['file_folder_name'];
            if ($m == 'files') {
                $s .= '</a></li>';
            }
            if ($file_count > 0) {
                $s .= '<li class="info-text"><a href="javascript: void(0);" onClick="expand(\'files_' . $row['file_folder_id'] . '\')" class="has-files">(' . $file_count . ' files) +</a></li>';
            }
            $s .= '<form name="frm_remove_folder_' . $row['file_folder_id'] . '" action="?m=files" method="post" accept-charset="utf-8">
                    <input type="hidden" name="dosql" value="do_folder_aed" />
                    <input type="hidden" name="del" value="1" />
                    <input type="hidden" name="file_folder_id" value="' . $row['file_folder_id'] . '" />
                    </form>';

            $s .= '</ul>';
            $s .= '<a class="small-delete" href="javascript: void(0);" onclick="if (confirm(\'Are you sure you want to delete this folder?\')) {document.frm_remove_folder_' . $row['file_folder_id'] . '.submit()}">' . w2PshowImage('remove.png', '', '', 'delete icon', 'delete this folder', 'files') . '</a>';
            $s .= '</td></tr>';
            if ($file_count > 0) {
                $s .= '<div class="files-list" id="files_' . $row['file_folder_id'] . '" style="display: none;">';
                $s .= displayFiles($AppUI, $row['file_folder_id'], $task_id, $project_id, $company_id);
                $s .= "</div>";
            }
        }
    }

    return $s;
}

// From: modules/files/filefolder.class.php
function countFiles($folder)
{
    global $company_id, $allowed_companies;
    global $deny1, $deny2, $project_id, $task_id;

    $q = new w2p_Database_Query();
    $q->addTable('files');
    $q->addQuery('count(files.file_id)');
    $q->addJoin('projects', 'p', 'p.project_id = file_project');
    $q->addJoin('users', 'u', 'u.user_id = file_owner');
    $q->addJoin('tasks', 't', 't.task_id = file_task');
    $q->addJoin('file_folders', 'ff', 'ff.file_folder_id = file_folder');
    $q->addWhere('file_folder = ' . (int) $folder);
    if (count($deny1) > 0) {
        $q->addWhere('file_project NOT IN (' . implode(',', $deny1) . ')');
    }
    if (count($deny2) > 0) {
        $q->addWhere('file_task NOT IN (' . implode(',', $deny2) . ')');
    }
    if ($project_id) {
        $q->addWhere('file_project = ' . (int) $project_id);
    }
    if ($task_id) {
        $q->addWhere('file_task = ' . (int) $task_id);
    }
    if ($company_id) {
        $q->innerJoin('companies', 'co', 'co.company_id = p.project_company');
        $q->addWhere('company_id = ' . (int) $company_id);
        $q->addWhere('company_id IN (' . $allowed_companies . ')');
    }

    $files_in_folder = $q->loadResult();
    $q->clear();

    return $files_in_folder;
}

// From: modules/files/filefolder.class.php
function displayFiles($AppUI, $folder_id, $task_id, $project_id, $company_id)
{
    global $m, $tab, $xpg_min, $xpg_pagesize, $showProject, $file_types,
            $company_id, $current_uri, $canEdit;

    // SETUP FOR FILE LIST
    $q = new w2p_Database_Query();
    $q->addQuery('f.*, max(f.file_id) as latest_id, count(f.file_version) as file_versions, round(max(file_version), 2) as file_lastversion, file_owner, user_id');
    $q->addQuery('ff.*, max(file_version) as file_version, f.file_date as file_datetime');
    $q->addTable('files', 'f');
    $q->addJoin('file_folders', 'ff', 'ff.file_folder_id = file_folder');
    $q->addJoin('projects', 'p', 'p.project_id = file_project');
    $q->addJoin('tasks', 't', 't.task_id = file_task');
    $q->addJoin('users', 'u', 'u.user_id = file_owner');
    $q->leftJoin('project_departments', 'project_departments', 'p.project_id = project_departments.project_id OR project_departments.project_id IS NULL');
    $q->leftJoin('departments', 'departments', 'departments.dept_id = project_departments.department_id OR dept_id IS NULL');

    //TODO: apply permissions properly
    $project = new CProject();
    $deny1 = $project->getDeniedRecords($AppUI->user_id);
    if (count($deny1) > 0) {
        $q->addWhere('file_project NOT IN (' . implode(',', $deny1) . ')');
    }
    //TODO: apply permissions properly
    $task = new CTask();
    $deny2 = $task->getDeniedRecords($AppUI->user_id);
    if (count($deny2) > 0) {
        $q->addWhere('file_task NOT IN (' . implode(',', $deny2) . ')');
    }

    if ($project_id) {
        $q->addWhere('file_project = ' . (int) $project_id);
    }
    if ($task_id) {
        $q->addWhere('file_task = ' . (int) $task_id);
    }
    if ($company_id) {
        $q->addWhere('project_company = ' . (int) $company_id);
    }
    //$tab = ($m == 'files') ? $tab-1 : -1;
    $temp_tab = ($m == 'files') ? $tab - 1 : -1;
    if (($temp_tab >= 0) and ((count($file_types) - 1) > $temp_tab)) {
    //if ($tab >= 0) {
        $q->addWhere('file_category = ' . (int) $temp_tab);
    }
    $q->setLimit($xpg_pagesize, $xpg_min);
    if ($folder_id > -1) {
        $q->addWhere('file_folder = ' . (int) $folder_id);
    }
    $q->addGroup('file_version_id DESC');
    $q->addOrder('project_name ASC, file_parent ASC, file_id DESC');

    $qv = new w2p_Database_Query();
    $qv->addTable('files');
    $qv->addQuery('file_id, file_version, file_project, file_name, file_task,
        file_description, file_owner, file_size, file_category,
        task_name, file_version_id, file_date as file_datetime, file_checkout, file_co_reason, file_type,
        file_date, cu.user_username as co_user, project_name,
        project_color_identifier, project_owner, u.user_id,
        con.contact_first_name, con.contact_last_name, con.contact_display_name as contact_name,
        co.contact_first_name as co_contact_first_name, co.contact_last_name as co_contact_last_name,
        co.contact_display_name as co_contact_name ');
    $qv->addJoin('projects', 'p', 'p.project_id = file_project');
    $qv->addJoin('users', 'u', 'u.user_id = file_owner');
    $qv->addJoin('contacts', 'con', 'con.contact_id = u.user_contact');
    $qv->addJoin('tasks', 't', 't.task_id = file_task');
    $qv->addJoin('file_folders', 'ff', 'ff.file_folder_id = file_folder');
    if ($project_id) {
        $qv->addWhere('file_project = ' . (int) $project_id);
    }
    if ($task_id) {
        $qv->addWhere('file_task = ' . (int) $task_id);
    }
    if ($company_id) {
        $qv->addWhere('project_company = ' . (int) $company_id);
    }
    if (($temp_tab >= 0) and ((count($file_types) - 1) > $temp_tab)) {
    //if ($tab >= 0) {
        $qv->addWhere('file_category = ' . (int) $temp_tab);
    }
    $qv->leftJoin('users', 'cu', 'cu.user_id = file_checkout');
    $qv->leftJoin('contacts', 'co', 'co.contact_id = cu.user_contact');
    if ($folder_id > -1) {
        $qv->addWhere('file_folder = ' . (int) $folder_id);
    }

    $files = $q->loadList();
    $file_versions = $qv->loadHashList('file_id');

    $module = new w2p_System_Module();
    $fields = $module->loadSettings('files', 'index_list');

    if (count($fields) > 0) {
        $fieldList = array_keys($fields);
        $fieldNames = array_values($fields);
    } else {
        // TODO: This is only in place to provide an pre-upgrade-safe
        //   state for versions earlier than v3.0
        //   At some point at/after v4.0, this should be deprecated
        $fieldList = array('file_name', 'file_description',
            'file_version', 'file_category', 'file_folder', 'file_task',
            'file_owner', 'file_datetime');
        $fieldNames = array('File Name', 'Description', 'Version', 'Category',
            'Folder', 'Task Name', 'Owner', 'Date',);

        $module->storeSettings('files', 'index_list', $fieldList, $fieldNames);
    }

    $s  = '<tr>';
    $s .= '<th></th>';
    $s .= '<th>' . $AppUI->_('co') . '</th>';
    foreach ($fieldNames as $index => $name) {
        $s .= '<th>' . $AppUI->_($fieldNames[$index]) . '</th>';
    }
    $s .= '<th></th>';
    $s .= '</tr>';

    $fp = -1;
    $htmlHelper = new w2p_Output_HTMLHelper($AppUI);
    $htmlHelper->df .= ' ' . $AppUI->getPref('TIMEFORMAT');

    $file_types = w2PgetSysVal('FileType');
    $customLookups = array('file_category' => $file_types);

    foreach ($files as $row) {
        $latest_file = $file_versions[$row['latest_id']];

        if ($fp != $latest_file['file_project']) {
            if (!$latest_file['file_project']) {
                $latest_file['project_name'] = $AppUI->_('Not attached to a project');
                $latest_file['project_color_identifier'] = 'f4efe3';
            }
            if ($showProject) {
                $style = 'background-color:#' . $latest_file['project_color_identifier'] . ';color:' . bestColor($latest_file['project_color_identifier']);
                $s .= '<tr>';
                $s .= '<td colspan="20" style="text-align: left; border: outset 2px #eeeeee;' . $style . '">';
                if ($latest_file['file_project'] > 0) {
                    $href = './index.php?m=projects&a=view&project_id=' . $latest_file['file_project'];
                } else {
                    $href = './index.php?m=projects';
                }
                $s .= '<a href="' . $href . '">';
                $s .= '<span style="' . $style . '">' . $latest_file['project_name'] . '</span></a>';
                $s .= '</td></tr>';
            }
        }
        $fp = $latest_file['file_project'];
        $row['file_datetime'] = $latest_file['file_datetime'];
        $row['file_id'] = $latest_file['file_id'];
        $htmlHelper->stageRowData($row);

        $s .= '<tr>';
        $s .= '<td class="data">';
        if ($canEdit && (empty($latest_file['file_checkout']) || ($latest_file['file_checkout'] == 'final' && ($canEdit || $latest_file['project_owner'] == $AppUI->user_id)))) {
            $s .= '<a href="./index.php?m=files&a=addedit&file_id=' . $latest_file['file_id'] . '">' . w2PshowImage('kedit.png', '16', '16', 'edit file', 'edit file', 'files') . '</a>';
        }
        $s .= '</td>';
        $s .= '<td class="data">';
        if ($canEdit && empty($latest_file['file_checkout'])) {
            $s .= '<a href="?m=files&a=co&file_id=' . $latest_file['file_id'] . '">' . w2PshowImage('up.png', '16', '16', 'checkout', 'checkout file', 'files') . '</a>';
        } else {
            if ($latest_file['file_checkout'] == $AppUI->user_id) {
                $s .= '<a href="?m=files&a=addedit&ci=1&file_id=' . $latest_file['file_id'] . '">' . w2PshowImage('down.png', '16', '16', 'checkin', 'checkin file', 'files') . '</a>';
            } else {
                if ($latest_file['file_checkout'] == 'final') {
                    $s .= 'final';
                } else {
                    $s .= $latest_file['co_contact_name'] . '<br>(' . $latest_file['co_user'] . ')';
                }
            }
        }

        $version_link = '';
        $hidden_table = '';
        if ($row['file_versions'] > 1) {
            $version_link = '&nbsp<a href="javascript: void(0);" onClick="expand(\'versions_' . $latest_file['file_id'] . '\'); ">(' . $row['file_versions'] . ')</a>';
            $hidden_table = '<tr><td colspan="20">
                <table style="display: none" id="versions_' . $latest_file['file_id'] . '" class="tbl list">
                <tr>';
            foreach ($fieldNames as $index => $name) {
                $hidden_table .= '<th nowrap="nowrap">';
                $hidden_table .= $AppUI->_($fieldNames[$index]);
                $hidden_table .= '</th>';
            }
            $hidden_table .= '</tr>';

            $sub_htmlHelper = new w2p_Output_HTMLHelper($AppUI);
            $sub_htmlHelper->df .= ' ' . $AppUI->getPref('TIMEFORMAT');

            foreach ($file_versions as $file) {
                $sub_htmlHelper->stageRowData($file);

                if ($file['file_version_id'] == $latest_file['file_version_id']) {
                    foreach ($fieldList as $index => $column) {
                        $hidden_table .= $sub_htmlHelper->createCell($fieldList[$index], $file[$fieldList[$index]], $customLookups);
                    }

                    if ($canEdit && w2PgetConfig('files_show_versions_edit')) {
                        $hidden_table .= '<a href="./index.php?m=files&a=addedit&file_id=' . $file['file_id'] . '">' . w2PshowImage('kedit.png', '16', '16', 'edit file', 'edit file', 'files') . "</a>";
                    }
                    $hidden_table .= '</td><tr>';
                }
            }
            $hidden_table .= '</table>';
        }
        $s .= '</td>';

        foreach ($fieldList as $index => $column) {
            $cell = $htmlHelper->createCell($fieldList[$index], $row[$fieldList[$index]], $customLookups);
            if ('file_version' == $fieldList[$index]) {
                $cell = str_replace('</td>', $version_link.'</td>', $cell);
            }
            $s .= $cell;
        }

        $s .= '<td>';
        $s .= '<form name="frm_remove_file_' . $latest_file['file_id'] . '" action="?m=files" method="post" accept-charset="utf-8">
            <input type="hidden" name="dosql" value="do_file_aed" />
            <input type="hidden" name="del" value="1" />
            <input type="hidden" name="file_id" value="' . $latest_file['file_id'] . '" />
            <input type="hidden" name="redirect" value="' . $current_uri . '" />
            </form>';
        $s .= '<a href="javascript: void(0);" onclick="if (confirm(\'' . $AppUI->_('Are you sure you want to delete this file?') . '\')) {document.frm_remove_file_' . $latest_file['file_id'] . '.submit()}">' . w2PshowImage('remove.png', '16', '16', 'delete file', 'delete file', 'files') . '</a>';
        $s .= '</td>';
        $s .= '</tr>';
        $s .= $hidden_table;
    }
    if (0 == count($files)) {
        $s .= '<tr><td colspan="' . (count($fieldNames) + 3 ) . '">' . $AppUI->_('No data available') . '</td></tr>';
    }

    return $s;
}

// From: modules/files/files.class.php
function last_file($file_versions, $file_name, $file_project)
{
    $latest = null;

    if (isset($file_versions))
        foreach ($file_versions as $file_version)
            if ($file_version['file_name'] == $file_name && $file_version['file_project'] == $file_project)
                if ($latest == null || $latest['file_version'] < $file_version['file_version'])
                    $latest = $file_version;

    return $latest;
}

// From: modules/files/files.class.php
function getIcon($file_type)
{
    global $uistyle;

    $mime = str_replace('/', '-', $file_type);
    $icon = 'gnome-mime-' . $mime;
    if (is_file(W2P_BASE_DIR . '/styles/' . $uistyle . '/images/modules/files/icons/' . $icon . '.png')) {
        $result = 'icons/' . $icon . '.png';
    } else {
        $result = __extract_from_files_index_table($file_type);
    }

    return $result;
}

/**
 * @param $file_type
 * @return string
 */
function __extract_from_files_index_table($file_type)
{
    $mime = explode('/', $file_type);
    switch ($mime[0]) {
        case 'audio':
            $result = 'icons/wav.png';
            break;
        case 'image':
            $result = 'icons/image.png';
            break;
        case 'text':
            $result = 'icons/text.png';
            break;
        case 'video':
            $result = 'icons/video.png';
            break;
        case 'application':
            switch ($mime[1]) {
                case 'vnd.ms-excel':
                    $result = 'icons/spreadsheet.png';
                    break;
                case 'vnd.ms-powerpoint':
                    $result = 'icons/quicktime.png';
                    break;
                case 'octet-stream':
                    $result = 'icons/source_c.png';
                    break;
                default:
                    $result = 'icons/documents.png';
            }
            break;
    }

    return $result;
}

// From: modules/files/files.class.php
function getHelpdeskFolder()
{
    $q = new w2p_Database_Query();
    $q->addTable('file_folders', 'ff');
    $q->addQuery('file_folder_id');
    $q->addWhere('ff.file_folder_name = \'Helpdesk\'');
    $ffid = $q->loadResult();

    return (int) $ffid;
}

// From: modules/files/files.class.php
function file_show_attr($AppUI, $form)
{
    global $object, $ci, $canAdmin, $file_project, $file_task, $task_name, $preserve;

    if ($ci) {
        $str_out  = '<p>' . $form->addLabel('Minor Revision') . '<input type="Radio" name="revision_type" value="minor" checked /></p>';
        $str_out .= '<p>' . $form->addLabel('Major Revision') . '<input type="Radio" name="revision_type" value="major" />';
    } else {
        $str_out = '<p>' . $form->addLabel('Version');
    }

    if ($ci) {
        $the_value = (strlen($object->file_version) > 0 ? $object->file_version + 0.01 : '1');
        $str_out .= '<input type="hidden" name="file_version" value="' . $the_value . '" />';
    } else {
        $the_value = (strlen($object->file_version) > 0 ? $object->file_version : '1');
        $str_out .= '<input type="text" name="file_version" maxlength="10" size="5" value="' . $the_value . '" class="text" />';
    }

    if ($ci || ($canAdmin && $object->file_checkout == 'final')) {
        $str_out .= '<input type="hidden" name="file_checkout" value="" /><input type="hidden" name="file_co_reason" value="" />';
    }

    $str_out .= '</p>';

    $select_disabled = ' ';
    $onclick_task = ' onclick="popTask()" ';
    if ($ci && $preserve) {
        $select_disabled = ' disabled="disabled" ';
        $onclick_task = ' ';
        // need because when a html is disabled, it's value it's not sent in submit
        $str_out .= '<input type="hidden" name="file_project" value="' . $file_project . '" />';
        $str_out .= '<input type="hidden" name="file_category" value="' . $object->file_category . '" />';
    }

    // Category
    $str_out .= '<p>' . $form->addLabel('Category');
    $str_out .= arraySelect(w2PgetSysVal('FileType'), 'file_category', 'class="text"' . $select_disabled, $object->file_category, true) . '</p>';

    // ---------------------------------------------------------------------------------

    $str_out .= '<p>' . $form->addLabel('Project');
    $str_out .= projectSelectWithOptGroup($AppUI->user_id, 'file_project', 'size="1" class="text"' . $select_disabled, $file_project) . '</p>';

    // ---------------------------------------------------------------------------------

    // Task
    $str_out .= '<p>' . $form->addLabel('Task');
    $str_out .= '<input type="hidden" name="file_task" value="' . $file_task . '" /><input type="text" class="text" name="task_name" value="' . $task_name . '" size="40" disabled /><input type="button" class="button btn btn-primary btn-mini" value="' . $AppUI->_('select task') . '..."' . $onclick_task . '/></p>';

    return ($str_out);
}

/** Retrieve tasks with first task_end_dates within given project
 * @param int Project_id
 * @param int SQL-limit to limit the number of returned tasks
 * @return array List of criticalTasks
 */
//TODO: modules/projectdesigner/projectdesigner.class.php
function getCriticalTasksInverted($project_id = null, $limit = 1)
{
    if (!$project_id) {
        $result = array();
        $result[0]['task_end_date'] = '0000-00-00 00:00:00';

        return $result;
    } else {
        $q = new w2p_Database_Query();
        $q->addTable('tasks');
        $q->addWhere('task_project = ' . (int) $project_id  . ' AND NOT ISNULL( task_end_date ) AND task_end_date <>  \'0000-00-00 00:00:00\'');
        $q->addOrder('task_start_date ASC');
        $q->setLimit($limit);

        return $q->loadList();
    }
}

//TODO: modules/projectdesigner/projectdesigner.class.php
function get_actual_end_date_pd($task_id, $task)
{
    global $AppUI;

    $q = new w2p_Database_Query();
    $mods = $AppUI->getActiveModules();

    if (!empty($mods['history']) && canView('history')) {
        $q->addQuery('MAX(history_date) as actual_end_date');
        $q->addTable('history');
        $q->addWhere('history_table=\'tasks\' AND history_item=' . $task_id);
    } else {
        $q->addQuery('MAX(task_log_date) AS actual_end_date');
        $q->addTable('task_log');
        $q->addWhere('task_log_task = ' . (int) $task_id);
    }

    $task_log_end_date = $q->loadResult();

    $edate = $task_log_end_date;

    $edate = ($edate > $task->task_end_date || $task->task_percent_complete == 100) ? $edate : $task->task_end_date;

    return $edate;
}

/* The next lines of code have resided in projects/index.php before
** and have been moved into this 'encapsulated' function
** for reusability of that central code.
**
** @date 20060225
** @responsible gregorerhardt
**
** E.g. this code is used as well in a tab for the admin/view site
**
** @mixed user_id 	userId as filter for tasks/projects that are shown, if nothing is specified,
current viewing user $AppUI->user_id is used.
*/
// From: modules/projects/project.class.php
function projects_list_data($user_id = false)
{
    global $AppUI, $addPwOiD, $buffer, $company, $company_id, $company_prefix,
        $deny, $department, $dept_ids, $orderby, $orderdir,
        $tasks_problems, $owner, $search_text, $project_type;

    $addProjectsWithAssignedTasks = $AppUI->getState('addProjWithTasks') ? $AppUI->getState('addProjWithTasks') : 0;

    // get any records denied from viewing
    $obj = new CProject();
    $deny = $obj->getDeniedRecords($AppUI->user_id);

    // Let's delete temproary tables
    $q = new w2p_Database_Query;
    $q->setDelete('tasks_problems');
    $q->exec();
    $q->clear();

    $q->setDelete('tasks_users');
    $q->exec();
    $q->clear();

    // support task problem logs
    $q->addInsertSelect('tasks_problems');
    $q->addTable('tasks');
    $q->addQuery('task_project, task_log_problem');
    $q->addJoin('task_log', 'tl', 'tl.task_log_task = task_id', 'inner');
    $q->addWhere('task_log_problem = 1');
    $q->addGroup('task_project');
    $tasks_problems = $q->exec();
    $q->clear();

    if ($addProjectsWithAssignedTasks) {
        // support users tasks
        $q->addInsertSelect('tasks_users');
        $q->addTable('tasks');
        $q->addQuery('task_project');
        $q->addQuery('ut.user_id');
        $q->addJoin('user_tasks', 'ut', 'ut.task_id = tasks.task_id');
        if ($user_id) {
            $q->addWhere('ut.user_id = ' . (int) $user_id);
        }
        $q->addOrder('task_end_date DESC');
        $q->addGroup('task_project');
        $q->exec();
        $q->clear();
    }

    // add Projects where the Project Owner is in the given department
    if ($addPwOiD && isset($department)) {
        $q->addTable('users');
        $q->addQuery('user_id');
        $q->addJoin('contacts', 'c', 'c.contact_id = user_contact', 'inner');
        $q->addWhere('c.contact_department = ' . (int) $department);
        $owner_ids = $q->loadColumn();
        $q->clear();
    }

    if (isset($department)) {
        //If a department is specified, we want to display projects from the department, and all departments under that, so we need to build that list of departments
        $dept_ids = array();
        $q->addTable('departments');
        $q->addQuery('dept_id, dept_parent');
        $q->addOrder('dept_parent,dept_name');
        $rows = $q->loadList();
        addDeptId($rows, $department);
        $dept_ids[] = isset($department->dept_id) ? $department->dept_id : 0;
        $dept_ids[] = ($department > 0) ? $department : 0;
    }
    $q->clear();

    // retrieve list of records
    // modified for speed
    // by Pablo Roca (pabloroca@mvps.org)
    // 16 August 2003
    // get the list of permitted companies
    $obj = new CCompany();
    $companies = $obj->getAllowedRecords($AppUI->user_id, 'companies.company_id,companies.company_name', 'companies.company_name');
    if (count($companies) == 0) {
        $companies = array();
    }

    $q->addTable('projects', 'pr');
    $q->addQuery('pr.*, project_scheduled_hours as project_duration,
        project_actual_end_date as project_end_actual,
        company_id, company_name, project_last_task as critical_task,
        tp.task_log_problem, user_username, task_log_problem, u.user_id');

    $fields = w2p_System_Module::getSettings('projects', 'index_list');
    unset($fields['department_list']);  // added as an alias below
    foreach ($fields as $field => $notUsed) {
        $q->addQuery($field);
    }
    $q->addQuery('ct.contact_display_name AS owner_name');
    $q->addJoin('companies', 'c', 'c.company_id = pr.project_company');
    $q->addJoin('users', 'u', 'pr.project_owner = u.user_id');
    $q->addJoin('contacts', 'ct', 'ct.contact_id = u.user_contact');
    $q->addJoin('tasks_problems', 'tp', 'pr.project_id = tp.task_project');
    if ($addProjectsWithAssignedTasks) {
        $q->addJoin('tasks_users', 'tu', 'pr.project_id = tu.task_project');
    }
    if (!isset($department) && $company_id > 0 && !$addPwOiD) {
        $q->addWhere('pr.project_company = ' . (int) $company_id);
    }
    if ($project_type > -1) {
        $q->addWhere('pr.project_type = ' . (int) $project_type);
    }
    if (isset($department) && !$addPwOiD) {
        $q->addWhere('project_departments.department_id in ( ' . implode(',', $dept_ids) . ' )');
    }
    if ($user_id && $addProjectsWithAssignedTasks) {
        $q->addWhere('(tu.user_id = ' . (int) $user_id . ' OR pr.project_owner = ' . (int) $user_id . ' )');
    } elseif ($user_id) {
        $q->addWhere('pr.project_owner = ' . (int) $user_id);
    }
    if ($owner > 0) {
        $q->addWhere('pr.project_owner = ' . (int) $owner);
    }
    if (mb_trim($search_text)) {
        $q->addWhere('pr.project_name LIKE \'%' . $search_text . '%\' OR pr.project_description LIKE \'%' . $search_text . '%\'');
    }
    // Show Projects where the Project Owner is in the given department
    if ($addPwOiD && !empty($owner_ids)) {
        $q->addWhere('pr.project_owner IN (' . implode(',', $owner_ids) . ')');
    }
    $orderby = ('project_company' == $orderby) ? 'company_name' : $orderby;
    $q->addGroup('pr.project_id');
    $q->addOrder($orderby . ' ' .$orderdir);
    $prj = new CProject();
    $q = $prj->setAllowedSQL($AppUI->user_id, $q, null, 'pr');
    $dpt = new CDepartment();
    $projects = $q->loadList();

    // get the list of permitted companies
    $companies = arrayMerge(array('0' => $AppUI->_('All')), $companies);
    $company_array = $companies;

    //get list of all departments, filtered by the list of permitted companies.
    $q->clear();
    $q->addTable('companies');
    $q->addQuery('company_id, company_name, dep.*');
    $q->addJoin('departments', 'dep', 'companies.company_id = dep.dept_company');
    $q->addOrder('company_name,dept_parent,dept_name');
    $q = $obj->setAllowedSQL($AppUI->user_id, $q);
    $q = $dpt->setAllowedSQL($AppUI->user_id, $q);
    $rows = $q->loadList();

    //display the select list
    $buffer = '<select name="department" id="department" onChange="document.pickCompany.submit()" class="text" style="width: 200px;">';
    $company = '';

    foreach ($company_array as $key => $c_name) {
        $buffer .= '<option value="' . $company_prefix . $key . '" style="font-weight:bold;"' . ($company_id == $key ? 'selected="selected"' : '') . '>' . $c_name . '</option>' . "\n";
        foreach ($rows as $row) {
            if ($row['dept_parent'] == 0) {
                if ($key == $row['company_id']) {
                    if ($row['dept_parent'] != null) {
                        findchilddept($rows, $row['dept_id']);
                    }
                }
            }
        }
    }
    $buffer .= '</select>';

    return $projects;
}

/**
 * getProjectIndex() gets the key nr of a project record within an array of projects finding its primary key within the records so that you can call that array record to get the projects data
 *
 * @param mixed $arraylist array list of project elements to search
 * @param mixed $project_id project id to search for
 * @return int returns the array key of the project record in the array list or false if not found
 */
// From: modules/projects/project.class.php
function getProjectIndex($arraylist, $project_id)
{
    $result = false;
    foreach ($arraylist as $key => $data) {
        if ($data['project_id'] == $project_id) {
            return $key;
        }
    }

    return $result;
}

/**
 * getDepartmentSelectionList() returns a tree of departments in <option> tags (originally used on the addedit interface to display the departments of a project)
 *
 * @param mixed $company_id the id of the company we are searching departments
 * @param mixed $checked_array an array with the ids of the departments that should be selected on the list
 * @param integer $dept_parent used when to determine the starting level on the tree, or by recursion
 * @param integer $spaces used by recursion to add spaces to form the visual tree on the <select> element
 * @return string returns the html <option> elements
 */
// From: modules/projects/project.class.php
function getDepartmentSelectionList($company_id, $checked_array = array(), $dept_parent = 0, $spaces = 0)
{
    global $departments_count, $AppUI;
    $parsed = '';

    if ($AppUI->isActiveModule('departments') && canView('departments')) {
        $department = new CDepartment();
        $depts_list = $department->departments($company_id, $dept_parent);

        foreach ($depts_list as $dept_id => $dept_info) {
            $selected = in_array($dept_id, $checked_array) ? ' selected="selected"' : '';

            $parsed .= '<option value="' . $dept_id . '"' . $selected . '>' . str_repeat('&nbsp;', $spaces) . $dept_info['dept_name'] . '</option>';
            $parsed .= getDepartmentSelectionList($company_id, $checked_array, $dept_id, $spaces + 5);
        }
    }

    return $parsed;
}

// From: modules/reports/reports/allocateduserhours.php
function userUsageWeeks()
{
    global $task_start_date, $task_end_date, $hours_added, $actual_date, $users, $user_data, $user_usage, $use_assigned_percentage, $user_tasks_counted_in, $task, $start_date, $end_date;

    $task_duration_per_week = $task->getTaskDurationPerWeek($use_assigned_percentage);
    $ted = new w2p_Utilities_Date(Date_Calc::endOfWeek($task_end_date->day, $task_end_date->month, $task_end_date->year));
    $tsd = new w2p_Utilities_Date(Date_Calc::beginOfWeek($task_start_date->day, $task_start_date->month, $task_start_date->year));

    $week_difference = $end_date->workingDaysInSpan($start_date) / count(explode(',', w2PgetConfig('cal_working_days')));

    $actual_date = $start_date;

    for ($i = 0; $i <= $week_difference; $i++) {
        if (!$actual_date->before($tsd) && !$actual_date->after($ted)) {
            $awoy = $actual_date->year . Date_Calc::weekOfYear($actual_date->day, $actual_date->month, $actual_date->year);
            foreach ($users as $user_id => $user_data) {
                if (!isset($user_usage[$user_id][$awoy])) {
                    $user_usage[$user_id][$awoy] = 0;
                }
                $percentage_assigned = $use_assigned_percentage ? ($user_data['perc_assignment'] / 100) : 1;
                $hours_added = $task_duration_per_week * $percentage_assigned;
                $user_usage[$user_id][$awoy] += $hours_added;
                if ($user_usage[$user_id][$awoy] < 0.005) {
                    //We want to show at least 0.01 even when the assigned time is very small so we know
                    //that at that time the user has a running task
                    $user_usage[$user_id][$awoy] += 0.006;
                    $hours_added += 0.006;
                }

                // Let's register the tasks counted in for calculation
                if (!array_key_exists($user_id, $user_tasks_counted_in)) {
                    $user_tasks_counted_in[$user_id] = array();
                }

                if (!array_key_exists($task->task_project, $user_tasks_counted_in[$user_id])) {
                    $user_tasks_counted_in[$user_id][$task->task_project] = array();
                }

                if (!array_key_exists($task->task_id, $user_tasks_counted_in[$user_id][$task->task_project])) {
                    $user_tasks_counted_in[$user_id][$task->task_project][$task->task_id] = 0;
                }
                // We add it up
                $user_tasks_counted_in[$user_id][$task->task_project][$task->task_id] += $hours_added;
            }
        }
        $actual_date->addSeconds(168 * 3600); // + one week
    }
}

// From: modules/reports/reports/allocateduserhours.php
function showWeeks()
{
    global $allocated_hours_sum, $end_date, $start_date, $AppUI, $user_list, $user_names, $user_usage, $table_header, $table_rows, $working_days_count, $total_hours_capacity, $total_hours_capacity_all;

    $working_days_count = 0;
    $allocated_hours_sum = 0;

    $ed = new w2p_Utilities_Date(Date_Calc::endOfWeek($end_date->day, $end_date->month, $end_date->year));
    $sd = new w2p_Utilities_Date(Date_Calc::beginOfWeek($start_date->day, $start_date->month, $start_date->year));

    $week_difference = ceil($ed->workingDaysInSpan($sd) / count(explode(',', w2PgetConfig('cal_working_days'))));

    $actual_date = $sd;

    $table_header = '<tr><th>' . $AppUI->_('User') . '</th>';
    for ($i = 0; $i < $week_difference; $i++) {
        $actual_date->addSeconds(168 * 3600); // + one week
        $working_days_count = $working_days_count + count(explode(',', w2PgetConfig('cal_working_days')));
    }
    $table_header .= '<th nowrap="nowrap" colspan="2">' . $AppUI->_('Allocated') . '</th></tr>';

    $table_rows = '';

    foreach ($user_list as $user_id => $user_data) {
        $user_names[$user_id] = $user_data['contact_first_name'] . ' ' . $user_data['contact_last_name'];
        if (isset($user_usage[$user_id])) {
            $table_rows .= '<tr><td nowrap="nowrap">(' . $user_data['user_username'] . ') ' . $user_data['contact_first_name'] . ' ' . $user_data['contact_last_name'] . '</td>';
            $array_sum = array_sum($user_usage[$user_id]);

            $average_user_usage = number_format(($array_sum / ($week_difference * count(explode(',', w2PgetConfig('cal_working_days'))) * w2PgetConfig('daily_working_hours'))) * 100, 2);
            $allocated_hours_sum += $array_sum;

            $bar_color = 'blue';
            if ($average_user_usage > 100) {
                $bar_color = 'red';
                $average_user_usage = 100;
            }
            $table_rows .= '<td ><div align="left">' . round($array_sum, 2) . ' ' . $AppUI->_('hours') . '</td> <td align="right"> ' . $average_user_usage;
            $table_rows .= '%</div>';
            $table_rows .= '<div align="left" style="height:2px;width:' . $average_user_usage . '%; background-color:' . $bar_color . '">&nbsp;</div></td>';
            $table_rows .= '</tr>';
        }
    }
    $total_hours_capacity = $working_days_count / 2 * w2PgetConfig('daily_working_hours') * count($user_usage);
    $total_hours_capacity_all = $working_days_count / 2 * w2PgetConfig('daily_working_hours') * count($user_list);
}

// From: modules/reports/reports/allocateduserhours.php
function userUsageDays()
{
    global $day_difference, $hours_added, $actual_date, $users, $user_data, $user_usage, $use_assigned_percentage, $user_tasks_counted_in, $task, $start_date, $end_date;

    $task_duration_per_day = $task->getTaskDurationPerDay($use_assigned_percentage);

    for ($i = 0; $i <= $day_difference; $i++) {
        if (!$actual_date->before($start_date) && !$actual_date->after($end_date) && $actual_date->isWorkingDay()) {

            foreach ($users as $user_id => $user_data) {
                if (!isset($user_usage[$user_id][$actual_date->format('%Y%m%d')])) {
                    $user_usage[$user_id][$actual_date->format('%Y%m%d')] = 0;
                }
                $percentage_assigned = $use_assigned_percentage ? ($user_data['perc_assignment'] / 100) : 1;
                $hours_added = $task_duration_per_day * $percentage_assigned;
                $user_usage[$user_id][$actual_date->format('%Y%m%d')] += $hours_added;
                if ($user_usage[$user_id][$actual_date->format('%Y%m%d')] < 0.005) {
                    //We want to show at least 0.01 even when the assigned time is very small so we know
                    //that at that time the user has a running task
                    $user_usage[$user_id][$actual_date->format('%Y%m%d')] += 0.006;
                    $hours_added += 0.006;
                }

                // Let's register the tasks counted in for calculation
                if (!array_key_exists($user_id, $user_tasks_counted_in)) {
                    $user_tasks_counted_in[$user_id] = array();
                }

                if (!array_key_exists($task->task_project, $user_tasks_counted_in[$user_id])) {
                    $user_tasks_counted_in[$user_id][$task->task_project] = array();
                }

                if (!array_key_exists($task->task_id, $user_tasks_counted_in[$user_id][$task->task_project])) {
                    $user_tasks_counted_in[$user_id][$task->task_project][$task->task_id] = 0;
                }
                // We add it up
                $user_tasks_counted_in[$user_id][$task->task_project][$task->task_id] += $hours_added;
            }
        }
        $actual_date->addDays(1);
    }
}

// From: modules/reports/reports/allocateduserhours.php
function showDays()
{
    global $allocated_hours_sum, $end_date, $start_date, $AppUI, $user_list, $user_names, $user_usage, $hideNonWd, $table_header, $table_rows, $working_days_count, $total_hours_capacity, $total_hours_capacity_all;

    $days_difference = $end_date->dateDiff($start_date);

    $actual_date = $start_date;
    $working_days_count = 0;
    $allocated_hours_sum = 0;

    $table_header = '<tr><th>' . $AppUI->_('User') . '</th>';
    for ($i = 0; $i <= $days_difference; $i++) {
        if ($actual_date->isWorkingDay()) {
            $working_days_count++;
        }
        $actual_date->addDays(1);
    }
    $table_header .= '<th nowrap="nowrap" colspan="2">' . $AppUI->_('Allocated') . '</th></tr>';

    $table_rows = '';

    foreach ($user_list as $user_id => $user_data) {
        $user_names[$user_id] = $user_data['contact_first_name'] . ' ' . $user_data['contact_last_name'];
        if (isset($user_usage[$user_id])) {
            $table_rows .= '<tr><td nowrap="nowrap">(' . $user_data['user_username'] . ') ' . $user_data['contact_first_name'] . ' ' . $user_data['contact_last_name'] . '</td>';
            $array_sum = array_sum($user_usage[$user_id]);
            $average_user_usage = number_format(($array_sum / ($working_days_count * w2PgetConfig('daily_working_hours'))) * 100, 2);
            $allocated_hours_sum += $array_sum;

            $bar_color = 'blue';
            if ($average_user_usage > 100) {
                $bar_color = 'red';
                $average_user_usage = 100;
            }
            $table_rows .= '<td ><div align="left">' . round($array_sum, 2) . ' ' . $AppUI->_('hours') . '</td> <td align="right"> ' . $average_user_usage;
            $table_rows .= '%</div>';
            $table_rows .= '<div align="left" style="height:2px;width:' . $average_user_usage . '%; background-color:' . $bar_color . '">&nbsp;</div></td>';
            $table_rows .= '</tr>';

        }
    }
    $total_hours_capacity = $working_days_count * w2PgetConfig('daily_working_hours') * count($user_usage);
    $total_hours_capacity_all = $working_days_count * w2PgetConfig('daily_working_hours') * count($user_list);
}

// From: modules/system/syskeys/index.php
function showRow($id = '', $key = 0, $title = '', $value = '')
{
  global $canEdit, $sysval_id, $AppUI, $keys;
  global $fixedSysVals;
  $s = '';
  if (($sysval_id == $title) && $canEdit) {
    // edit form
    $s .= '<tr><td><input type="hidden" name="sysval_id" value="' . $title . '" />&nbsp;</td>';
    $s .= '<td valign="top"><a name="'.$title.'"> </a>' . arraySelect($keys, 'sysval_key_id', 'size="1" class="text"', $key) . '</td>';
    $s .= '<td valign="top"><input type="text" name="sysval_title" value="' . w2PformSafe($title) . '" class="text" /></td>';
    $s .= '<td valign="top"><textarea name="sysval_value" class="small" rows="5" cols="40">' . $value . '</textarea></td>';
    $s .= '<td><input type="submit" value="' . $AppUI->_($id ? 'save' : 'add') . '" class="button btn btn-primary btn-mini" /></td><td>&nbsp;</td>';
  } else {
    $s = '<tr><td width="12" valign="top">';
    if ($canEdit) {
      $s .= '<a href="?m=system&u=syskeys&sysval_id=' . $title . '#'.$title.'" title="' . $AppUI->_('edit') . '">' . w2PshowImage('icons/stock_edit-16.png', 16, 16, '') . '</a></td>';
    }
    $s .= '<td valign="top">' . $keys[$key] . '</td>';
    $s .= '<td valign="top">' . w2PformSafe($title) . '</td>';
    $s .= '<td valign="top" colspan="2">' . $value . '</td>';
    $s .= '<td valign="top" width="16">';
    if ($canEdit && !in_array($title, $fixedSysVals)) {
      $s .= '<a href="javascript:delIt(\'' . $title . '\')" title="' . $AppUI->_('delete') . '">' . w2PshowImage('icons/stock_delete-16.png', 16, 16, '') . '</a>';
    }
    $s .= '</td>';
  }
  $s .= '</tr>';

  return $s;
}

// From: modules/system/syskeys/keys.php
function showRow_keys($id = 0, $name = '', $label = '')
{
    global $canEdit, $syskey_id, $CR, $AppUI;
    $s = '';
    if ($syskey_id == $id && $canEdit) {
        $s .= '<form name="sysKeyFrm" method="post" action="?m=system&u=syskeys&a=do_syskey_aed" accept-charset="utf-8">';
        $s .= '<input type="hidden" name="del" value="0" />';
        $s .= '<input type="hidden" name="syskey_id" value="' . $id . '" />';
        $s .= '<tr>';
        $s .= '<td>&nbsp;</td>';
        $s .= '<td><input type="text" name="syskey_name" value="' . $name . '" class="text" /></td>';
        $s .= '<td><textarea name="syskey_label" class="small" rows="2" cols="40">' . $label . '</textarea></td>';
        $s .= '<td><input type="submit" value="' . $AppUI->_($id ? 'edit' : 'add') . '" class="button btn btn-primary btn-mini" /></td>';
        $s .= '<td>&nbsp;</td>';
    } else {
        $s .= '<tr>';
        $s .= '<td width="12">';
        if ($canEdit) {
            $s .= '<a href="?m=system&u=syskeys&a=keys&syskey_id=' . $id . '"><img src="' . w2PfindImage('icons/pencil.gif') . '" alt="edit" /></a>';
            $s .= '</td>' . $CR;
        }
        $s .= '<td>' . $name . '</td>' . $CR;
        $s .= '<td colspan="2">' . $label . '</td>' . $CR;
        $s .= '<td width="16">';
        if ($canEdit) {
            $s .= '<a href="javascript:delIt(' . $id . ')"><img align="absmiddle" src="' . w2PfindImage('icons/trash.gif') . '" alt="' . $AppUI->_('delete') . '" /></a>';
        }
        $s .= '</td>' . $CR;
    }
    $s .= '</tr>' . $CR;

    return $s;
}

##
## Returns the best color based on a background color (x is cross-over)
##
function bestColor($bg, $lt = '#ffffff', $dk = '#000000')
{
    // cross-over color = x
    $x = 128;
    $r = hexdec(substr($bg, 0, 2));
    $g = hexdec(substr($bg, 2, 2));
    $b = hexdec(substr($bg, 4, 2));

    $y = 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
    if ($y < $x) {
        return $lt;
    } else {
        return $dk;
    }
}

##
## returns a select box based on an key,value array where selected is based on key
##
function arraySelect(&$arr, $select_name, $select_attribs, $selected, $translate = false)
{
    global $AppUI;
    if (!is_array($arr)) {
        dprint(__file__, __line__, 0, 'arraySelect called with no array');

        return '';
    }
    reset($arr);
    $s = '<select id="' . $select_name . '" name="' . $select_name . '" ' . $select_attribs . '>';
    // if we are dealing with multiple selected itens for multiple kind of listboxes
    // we need to count them so that only those get selected.
    if (is_array($selected)) {
        $multiple = count($selected);
    }
    $did_selected = 0;
    foreach ($arr as $k => $v) {
        if ($translate) {
            $v = $AppUI->_($v);
        }
        if (is_array($selected)) {
            $s .= '<option value="' . $k . '"' . ((in_array($k, $selected) && !$did_selected) ? ' selected="selected"' : '') . '>' . $v . '</option>';
            if (in_array($k, $selected)) {
                // We found a match. Lets decrease the $multiples yet to be found
                $multiple--;
            }
            if (!$multiple) {
                // As soon as we found $multiple nr of matches we make sure no other gets selected by computer mistake by using the $did_selected lock
                $did_selected = 1;
            }
        } else {
            $s .= '<option value="' . $k . '"' . ((($k == $selected && strcmp($k, $selected) == 0) && !$did_selected) ? ' selected="selected"' : '') . '>' . $v . '</option>';
            if (($k == $selected && strcmp($k, $selected) == 0)) {
                // As soon as we find a match we make sure no other gets selected by computer mistake by using the $did_selected lock
                $did_selected = 1;
            }
        }
    }
    $s .= '</select>';

    return $s;
}

##
## returns a select box based on an key,value array where selected is based on key
##
function arraySelectTree(&$arr, $select_name, $select_attribs, $selected, $translate = false)
{
    reset($arr);

    $children = array();
    // first pass - collect children
    foreach ($arr as $notUsed => $v) {
        $pt = $v[2];
        $list = isset($children[$pt]) ? $children[$pt] : array();
        array_push($list, $v);
        $children[$pt] = $list;
    }
    $list = tree_recurse($arr[0][2], '', array(), $children);

    return arraySelect($list, $select_name, $select_attribs, $selected, $translate);
}

function tree_recurse($id, $indent, $list, $children)
{
    if (isset($children[$id])) {
        foreach ($children[$id] as $v) {
            $id = $v[0];
            $txt = $v[1];
            $list[$id] = $indent . ' ' . $txt;
            $list = tree_recurse($id, $indent . '--', $list, $children);
        }
    }

    return $list;
}

/**
 **	Provide Projects Selectbox sorted by Companies
 **	@author gregorerhardt with special thanks to original author aramis
 **	@param 	int 		userID
 **	@param 	string 	HTML select box name identifier
 **	@param	string	HTML attributes
 **	@param	int			Proejct ID for preselection
 **	@param 	int			Project ID which will be excluded from the list
 **									(e.g. in the tasks import list exclude the project to import into)
 **	@return	string 	HTML selectbox

 */

function projectSelectWithOptGroup($user_id, $select_name, $select_attribs, $selected, $excludeProjWithId = null)
{
    global $AppUI;
    $q = new w2p_Database_Query();
    $q->addTable('projects', 'pr');
    $q->addQuery('DISTINCT pr.project_id, co.company_name, project_name');
    $q->addJoin('companies', 'co', 'co.company_id = pr.project_company');
    if (!empty($excludeProjWithId)) {
        $q->addWhere('pr.project_id <> ' . $excludeProjWithId);
    }
    $proj = new CProject();
    $q = $proj->setAllowedSQL($user_id, $q, null, 'pr');
    $q->addOrder('co.company_name, project_name');
    $projects = $q->loadList();
    $s = '<select name="' . $select_name . '" ' . $select_attribs . '>';
    $s .= '<option value="0" ' . ($selected == 0 ? 'selected="selected"' : '') . ' >' . $AppUI->_('None') . '</option>';
    $current_company = '';
    foreach ($projects as $p) {
        if ($p['company_name'] != $current_company) {
            $current_company = $p['company_name'];
            $s .= '<optgroup label="' . $current_company . '" >' . $current_company . '</optgroup>';
        }
        $s .= '<option value="' . $p['project_id'] . '" ' . ($selected == $p['project_id'] ? 'selected="selected"' : '') . '>&nbsp;&nbsp;&nbsp;' . $p['project_name'] . '</option>';
    }
    $s .= '</select>';

    return $s;
}

##
## breadCrumbs - show a separated list of crumbs
## array is in the form url => title
##
function breadCrumbs(&$arr)
{
    global $AppUI;
    $crumbs = array();
    foreach ($arr as $k => $v) {
        $crumbs[] = '<a class="button" href="' . $k . '"><span>' . $AppUI->_($v) . '</span></a>';
    }

    return implode('</td><td align="left" nowrap="nowrap">', $crumbs);
}

function w2PgetUsers()
{
    global $AppUI;

    $q = new w2p_Database_Query;
    $q->addTable('users');
    $q->addQuery('user_id, concat_ws(\' \', contact_first_name, contact_last_name) as name');
    $q->addJoin('contacts', 'con', 'con.contact_id = user_contact', 'inner');
    $q->addOrder('contact_first_name,contact_last_name');

    $obj = new CCompany();
    $companies = $obj->getAllowedSQL($AppUI->user_id, 'company_id');
    $q->addJoin('companies', 'com', 'company_id = contact_company');
    if ($companies) {
        $q->addWhere('(' . implode(' OR ', $companies) . ' OR contact_company=\'\' OR contact_company IS NULL OR contact_company = 0)');
    }

    if ($AppUI->isActiveModule('departments')) {
        $dpt = new CDepartment();
        $depts = $dpt->getAllowedSQL($AppUI->user_id, 'dept_id');
        $q->addJoin('departments', 'dep', 'dept_id = contact_department');
        if ($depts) {
            $q->addWhere('(' . implode(' OR ', $depts) . ' OR contact_department=0)');
        }
    }

    return $q->loadHashList();
}

function getUsers($stub = null, $where = null, $orderby = 'contact_first_name, contact_last_name')
{
    global $AppUI;
    $q = new w2p_Database_Query;
    $q->addTable('users');
    $q->addQuery('DISTINCT(user_id), user_username, contact_last_name, contact_first_name,
         company_name, contact_company, dept_id, dept_name, contact_display_name,
         contact_display_name as contact_name, contact_email, user_type');
    $q->addJoin('contacts', 'con', 'con.contact_id = user_contact', 'inner');
    if ($stub) {
        $q->addWhere('(UPPER(user_username) LIKE \'' . $stub . '%\' or UPPER(contact_first_name) LIKE \'' . $stub . '%\' OR UPPER(contact_last_name) LIKE \'' . $stub . '%\')');
    } elseif ($where) {
        $where = $q->quote('%' . $where . '%');
        $q->addWhere('(UPPER(user_username) LIKE ' . $where . ' OR UPPER(contact_first_name) LIKE ' . $where . ' OR UPPER(contact_last_name) LIKE ' . $where . ')');
    }

    $q->addGroup('user_id');
    $q->addOrder($orderby);

    // get CCompany() to filter by company
    $obj = new CCompany();
    $companies = $obj->getAllowedSQL($AppUI->user_id, 'company_id');
    $q->addJoin('companies', 'com', 'company_id = contact_company');
    if ($companies) {
        $q->addWhere('(' . implode(' OR ', $companies) . ' OR contact_company=\'\' OR contact_company IS NULL OR contact_company = 0)');
    }
    $dpt = new CDepartment();
    $depts = $dpt->getAllowedSQL($AppUI->user_id, 'dept_id');
    $q->addJoin('departments', 'dep', 'dept_id = contact_department');
    if ($depts) {
        $q->addWhere('(' . implode(' OR ', $depts) . ' OR contact_department=0)');
    }

    return $q;
}

function w2PgetUsersList($stub = null, $where = null, $orderby = 'contact_first_name, contact_last_name')
{
    $q = getUsers($stub, $where, $orderby);
    return $q->loadList();
}

function w2PgetUsersHashList($stub = null, $where = null, $orderby = 'contact_first_name, contact_last_name')
{
    $q = getUsers($stub, $where, $orderby);
    return $q->loadHashList('user_id');
}

##
## displays the configuration array of a module for informational purposes
##
function w2PshowModuleConfig($config)
{
    global $AppUI;
    $s = '<table cellspacing="2" cellpadding="2" border="0" class="std" width="50%">';
    $s .= '<tr><th colspan="2">' . $AppUI->_('Module Configuration') . '</th></tr>';
    foreach ($config as $k => $v) {
        $s .= '<tr><td width="50%">' . $AppUI->_($k) . '</td><td width="50%" class="hilite">' . $AppUI->_($v) . '</td></tr>';
    }
    $s .= '</table>';

    return ($s);
}

/**
 *	Function to recussively find an image in a number of places
 *	@param string The name of the image
 *	@param string Optional name of the current module
 */
function w2PfindImage($name, $module = null)
{
    // uistyle must be declared globally
    global $uistyle;
    if ($module && file_exists(W2P_BASE_DIR . '/modules/' . $module . '/images/' . $name)) {
        return './modules/' . $module . '/images/' . $name;
    } elseif ($module && file_exists(W2P_BASE_DIR . '/style/' . $uistyle . '/images/modules/' . $module . '/' . $name)) {
        return './style/' . $uistyle . '/images/modules/' . $module . '/' . $name;
    } elseif (file_exists(W2P_BASE_DIR . '/style/' . $uistyle . '/images/icons/' . $name)) {
        return './style/' . $uistyle . '/images/icons/' . $name;
    } elseif (file_exists(W2P_BASE_DIR . '/style/' . $uistyle . '/images/obj/' . $name)) {
        return './style/' . $uistyle . '/images/obj/' . $name;
    } elseif (file_exists(W2P_BASE_DIR . '/style/' . $uistyle . '/images/' . $name)) {
        return './style/' . $uistyle . '/images/' . $name;
    } elseif ($module && file_exists(W2P_BASE_DIR . '/style/' . w2PgetConfig('host_style') . '/images/modules/' . $module . '/' . $name)) {
        return './style/' . w2PgetConfig('host_style') . '/images/modules/' . $module . '/' . $name;
    } elseif (file_exists(W2P_BASE_DIR . '/style/' . w2PgetConfig('host_style') . '/images/icons/' . $name)) {
        return './style/' . w2PgetConfig('host_style') . '/images/icons/' . $name;
    } elseif (file_exists(W2P_BASE_DIR . '/style/' . w2PgetConfig('host_style') . '/images/obj/' . $name)) {
        return './style/' . w2PgetConfig('host_style') . '/images/obj/' . $name;
    } elseif (file_exists(W2P_BASE_DIR . '/style/web2project/images/obj/' . $name)) {
        return './style/web2project/images/obj/' . $name;
    } else {
        return './style/web2project/images/' . $name;
    }
}

/**
 *	Workaround removed due to problems in Opera and other issues
 *	with IE6.
 *	Workaround to display png images with alpha-transparency in IE6.0
 *	@param string The name of the image
 *	@param string The image width
 *	@param string The image height
 *	@param string The alt text for the image
 */
function w2PshowImage($src, $notUsed = '', $notUsed2 = '', $alt = '', $title = '', $module = null)
{
    global $m;

    if ($src == '') {
        return '';
    } elseif ($module) {
        $src = w2PfindImage($src, $module);
    } else {
        $src = w2PfindImage($src, $m);
    }

    if (!$alt && !$title) {
        $result = '';
    } elseif ($alt && $title) {
        $result = w2PtoolTip($alt, $title);
    } elseif ($alt && !$title) {
        $result = w2PtoolTip($m, $alt);
    } elseif (!$alt && $title) {
        $result = w2PtoolTip($m, $title);
    }
    $result .= '<img src="' . $src . '" alt="' . $alt . '" />';
    if ($alt || $title) {
        $result .= w2PendTip();
    }

    return $result;
}

// ****************************************************************************
// Page numbering variables
// Pablo Roca (pabloroca@Xmvps.org) (Remove the X)
// 19 August 2003
//
// $tab             - file category
// $page            - actual page to show
// $xpg_pagesize    - max rows per page
// $xpg_min         - initial record in the SELECT LIMIT
// $xpg_totalrecs   - total rows selected
// $xpg_sqlrecs     - total rows from SELECT LIMIT
// $xpg_total_pages - total pages
// $xpg_next_page   - next pagenumber
// $xpg_prev_page   - previous pagenumber
// $xpg_break       - stop showing page numbered list?
// $xpg_sqlcount    - SELECT for the COUNT total
// $xpg_sqlquery    - SELECT for the SELECT LIMIT
// $xpg_result      - pointer to results from SELECT LIMIT

function buildPaginationNav($AppUI, $m, $tab, $xpg_totalrecs, $xpg_pagesize, $page)
{
  $xpg_total_pages = ($xpg_totalrecs > $xpg_pagesize) ? ceil($xpg_totalrecs / $xpg_pagesize) : 0;

  $xpg_break = false;

  $s = '<table width="100%" cellspacing="0" cellpadding="0" border="0"><tr>';

  if ($xpg_totalrecs > $xpg_pagesize) {
    $xpg_prev_page = $page - 1;
    $xpg_next_page = $page + 1;
    // left buttoms
    if ($xpg_prev_page > 0) {
      $s .= '<td align="left" width="15%"><a href="./index.php?m=' . $m . '&amp;tab=' . $tab . '&amp;page=1"><img src="' . w2PfindImage('navfirst.gif') . '" alt="First Page"></a>&nbsp;&nbsp;';
      $s .= '<a href="./index.php?m=' . $m . '&amp;tab=' . $tab . '&amp;page=' . $xpg_prev_page . '"><img src="' . w2PfindImage('navleft.gif') . '" alt="Previous page (' . $xpg_prev_page . ')"></a></td>';
    } else {
      $s .= '<td width="15%">&nbsp;</td>';
    }

    // central text (files, total pages, ...)
    $s .= '<td align="center" width="70%">';
    $s .= $xpg_totalrecs . ' ' . $AppUI->_('Record(s)') . ' ' . $xpg_total_pages . ' ' . $AppUI->_('Page(s)');

    // Page numbered list, up to 30 pages
    $s .= ' [ ';

    for ($n = $page > 16 ? $page - 16 : 1; $n <= $xpg_total_pages; $n++) {
      if ($n == $page) {
        $s .= '<b>' . $n . '</b></a>';
      } else {
        $s .= '<a href="./index.php?m=' . $m . '&amp;tab=' . $tab . '&amp;page=' . $n . '">' . $n . '</a>';
      }
      if ($n >= 30 + $page - 15) {
        $xpg_break = true;
        break;
      } elseif ($n < $xpg_total_pages) {
        $s .= ' | ';
      }
    }

    if (!isset($xpg_break)) { // are we supposed to break ?
      if ($n == $page) {
        $s .= '<' . $n . '</a>';
      } else {
        $s .= '<a href="./index.php?m=' . $m . '&amp;tab=' . $tab . '&amp;page=' . $xpg_total_pages . '">' . $n . '</a>';
      }
    }
    $s .= ' ] ';
    $s .= '</td>';
    // right buttoms
    if ($xpg_next_page <= $xpg_total_pages) {
      $s .= '<td align="right" width="15%"><a href="./index.php?m=' . $m . '&amp;tab=' . $tab . '&amp;page=' . $xpg_next_page . '"><img src="' . w2PfindImage('navright.gif') . '" alt="Next Page (' . $xpg_next_page . ')"></a>&nbsp;&nbsp;';
      $s .= '<a href="./index.php?m=' . $m . '&amp;tab=' . $tab . '&amp;page=' . $xpg_total_pages . '"><img src="' . w2PfindImage('navlast.gif') . '" alt="Last Page"></a></td>';
    } else {
      $s .= '<td width="15%">&nbsp;</td></tr>';
    }
  }
  $s .= '</table>';

  return $s;
}

/**
 * function to return a default value if a variable is not set
 */
function defVal($var, $def)
{
    return isset($var) ? $var : $def;
}

function addHistory($table, $id, $action = 'modify', $description = '', $project_id = 0)
{
    global $AppUI;
    /*
    * TODO:
    * 1) description should be something like:
    * 		command(arg1, arg2...)
    *  The command should be as module_action
    *  for example:
    * 		forums_new('Forum Name', 'URL')
    *
    * This way, the history module will be able to display descriptions
    * using locale definitions:
    * 		"forums_new" -> "New forum '%s' was created" -> "Se ha creado un nuevo foro llamado '%s'"
    *
    * 2) project_id and module_id should be provided in order to filter history entries
    *
    */
    if (!$AppUI->isActiveModule('history')) {
        return;
    }
    if (is_null($action)) {
        $action = 'delete';
    }

    $q = new w2p_Database_Query;
    $q->addTable('history');
    $q->addInsert('history_action', $action);
    $q->addInsert('history_item', (int) $id);
    $q->addInsert('history_description', $description);
    $q->addInsert('history_user', (int) $AppUI->user_id);
    $q->addInsert('history_date', "'".$q->dbfnNowWithTZ()."'", false, true);
    $q->addInsert('history_project', (int) $project_id);
    $q->addInsert('history_table', $table);
    $q->exec();
    //echo db_error();
}

function w2PgetSysVal($title)
{
    $q = new w2p_Database_Query;
    $q->addTable('sysvals');
    $q->addQuery('sysval_value_id, sysval_value');
    $q->addWhere("sysval_title = '$title'");
    $q->addOrder('sysval_value_id ASC');
    $rows = $q->loadList();

    $arr = array();
    // We use trim() to make sure a numeric that has spaces
    // is properly treated as a numeric
    $key_sort = SORT_NUMERIC;
    foreach ($rows as $notUsed => $item) {
        if ($item) {
            $arr[trim($item['sysval_value_id'])] = trim($item['sysval_value']);
            if (!is_numeric(trim($item['sysval_value_id']))) {
                $key_sort = SORT_REGULAR;
            }
        }
    }
    ksort($arr, $key_sort);

    return $arr;
}

function w2PuserHasRole($name)
{
    global $AppUI;
    $uid = $AppUI->user_id;
    $q = new w2p_Database_Query;
    $q->addTable('roles', 'r');
    $q->addTable('user_roles', 'ur');
    $q->addQuery('r.role_id');
    $q->addWhere('ur.user_id = ' . $uid . ' AND ur.role_id = r.role_id AND r.role_name = \'' . $name . '\'');

    return $q->loadResult();
}

function w2PformatDuration($x)
{
    global $AppUI;

    $dur_day = floor($x / w2PgetConfig('daily_working_hours'));
    $dur_hour = $x - $dur_day * w2PgetConfig('daily_working_hours');
    $str = '';
    if ($dur_day > 1) {
        $str .= $dur_day . ' ' . $AppUI->_('days') . ' ';
    } elseif ($dur_day == 1) {
        $str .= $dur_day . ' ' . $AppUI->_('day') . ' ';
    }

    if ($dur_hour > 1) {
        $str .= $dur_hour . ' ' . $AppUI->_('hours');
    } elseif ($dur_hour > 0 and $dur_hour <= 1) {
        $str .= $dur_hour . ' ' . $AppUI->_('hour');
    }

    if ($str == '') {
        $str = $AppUI->_('n/a');
    }

    return $str;

}

/**
 */
function w2PsetMicroTime()
{
    global $microTimeSet;
    list($usec, $sec) = explode(' ', microtime());
    $microTimeSet = (float) $usec + (float) $sec;
}

function w2PsetExecutionConditions()
{
    $memoryLimt = (w2PgetConfig('reset_memory_limit') != '') ? w2PgetConfig('reset_memory_limit') : '64M';
    ini_set('max_execution_time', 180);
    ini_set('memory_limit', $memoryLimt);
}

/**
 */
function w2PgetMicroDiff()
{
    global $microTimeSet;
    $mt = $microTimeSet;
    w2PsetMicroTime();

    return sprintf('%.3f', $microTimeSet - $mt);
}

/**
 * Make text safe to output into double-quote enclosed attirbutes of an HTML tag
 */
function w2PformSafe($txt, $deslash = false)
{
    global $locale_char_set;

    if (!$locale_char_set) {
        $locale_char_set = 'utf-8';
    }

    if (is_object($txt)) {
        foreach (get_object_vars($txt) as $k => $v) {
            if ($deslash) {
                $obj->$k = htmlspecialchars(stripslashes($v), ENT_COMPAT, $locale_char_set);
            } else {
                $obj->$k = htmlspecialchars($v, ENT_COMPAT, $locale_char_set);
            }
        }
    } elseif (is_array($txt)) {
        foreach ($txt as $k => $v) {
            if ($deslash) {
                $txt[$k] = htmlspecialchars(stripslashes($v), ENT_COMPAT, $locale_char_set);
            } else {
                $txt[$k] = htmlspecialchars($v, ENT_COMPAT, $locale_char_set);
            }
        }
    } else {
        if ($deslash) {
            $txt = htmlspecialchars(stripslashes($txt), ENT_COMPAT, $locale_char_set);
        } else {
            $txt = htmlspecialchars($txt, ENT_COMPAT, $locale_char_set);
        }
    }

    return $txt;
}

function formatTime($uts)
{
    global $AppUI;
    $date = new w2p_Utilities_Date();
    $date->setDate($uts, DATE_FORMAT_UNIXTIME);

    return $date->format($AppUI->getPref('SHDATEFORMAT'));
}

function file_size($size)
{
    $size = (int) $size;
    if ($size > 1024 * 1024 * 1024)
        return round($size / 1024 / 1024 / 1024, 2) . ' Gb';
    if ($size > 1024 * 1024)
        return round($size / 1024 / 1024, 2) . ' Mb';
    if ($size > 1024)
        return round($size / 1024, 2) . ' Kb';
    return $size . ' B';
}

/**
 * This function is necessary because Windows likes to
 * write their own standards.  Nothing that depends on locales
 * can be trusted in Windows.
 */
function formatCurrency($number, $format)
{
    global $AppUI, $locale_char_set;

    if (!$format) {
        $format = $AppUI->getPref('CURRENCYFORM');
    }
    // If the requested locale doesn't work, don't fail,
    // revert to the system default.
    if ($locale_char_set != 'utf-8' || !setlocale(LC_MONETARY, $format . '.UTF8')) {
        if (!setlocale(LC_MONETARY, $format)) {
            setlocale(LC_MONETARY, '');
        }
    }

    // Even money_format can't be trusted in Windows. It simply does not work on systems that don't have strfmon capabilities. Use number_format as fallback.
    return function_exists('money_format') ? money_format('%i', $number) : number_format($number, 2);
}

function format_backtrace($bt, $file, $line, $msg)
{
    trigger_error('ERROR: ' . $file . '(' . $line . ') : ' . $msg, E_USER_WARNING);
    trigger_error('Backtrace:', E_USER_WARNING);
    foreach ($bt as $level => $frame) {
        trigger_error($level . ' ' . $frame['file'] . ':' . $frame['line'] . ' ' . $frame['function'] . "()", E_USER_WARNING);
    }
}

function dprint($file, $line, $level, $msg)
{
    $max_level = (int) w2PgetConfig('debug');
    $display_debug = w2PgetConfig('display_debug', false);
    if ($level <= $max_level) {
        error_log($file . '(' . $line . '): ' . $msg);
        if ($display_debug) {
            echo $file . '(' . $line . '): ' . $msg . ' <br />';
        }
        if ($level == 0 && $max_level > 0) {
            format_backtrace(debug_backtrace(), $file, $line, $msg);
        }
    }
}

/**
 * Return a list of modules that are associated with tabs for this
 * page.  This can be used to find post handlers, for instance.
 */
function findTabModules($module, $file = null)
{
    $modlist = array();
    if (!isset($_SESSION['all_tabs']) || !isset($_SESSION['all_tabs'][$module])) {
        return $modlist;
    }

    if (isset($file)) {
        if (isset($_SESSION['all_tabs'][$module][$file]) && is_array($_SESSION['all_tabs'][$module][$file])) {
            $tabs_array = &$_SESSION['all_tabs'][$module][$file];
        } else {
            return $modlist;
        }
    } else {
        $tabs_array = &$_SESSION['all_tabs'][$module];
    }
    foreach ($tabs_array as $tab) {
        if (isset($tab['module'])) {
            $modlist[] = $tab['module'];
        }
    }

    return array_unique($modlist);
}

/**
 * Return a list of modules that are associated with crumbs for this
 * page.  This can be used to find post handlers, for instance.
 */
function findCrumbModules($module, $file = null)
{
    $modlist = array();
    if (!isset($_SESSION['all_crumbs']) || !isset($_SESSION['all_crumbs'][$module])) {
        return $modlist;
    }

    if (isset($file)) {
        if (isset($_SESSION['all_crumbs'][$module][$file]) && is_array($_SESSION['all_crumbs'][$module][$file])) {
            $crumbs_array = &$_SESSION['all_crumbs'][$module][$file];
        } else {
            return $modlist;
        }
    } else {
        $crumbs_array = &$_SESSION['all_crumbs'][$module];
    }
    foreach ($crumbs_array as $crumb) {
        if (isset($crumb['module'])) {
            $modlist[] = $crumb['module'];
        }
    }

    return array_unique($modlist);
}

function getUsersArray()
{
    return w2PgetUsersHashList();

}

function getUsersCombo($default_user_id = 0, $first_option = 'All users')
{
    global $AppUI;

    $parsed = '<select name="user_id" class="text">';
    if ($first_option != '') {
        $parsed .= '<option value="0" ' . (!$default_user_id ? 'selected="selected"' : '') . '>' . $AppUI->_($first_option) . '</option>';
    }
    foreach (getUsersArray() as $user_id => $user) {
        $selected = $user_id == $default_user_id ? ' selected="selected"' : '';
        $parsed .= '<option value="' . $user_id . '"' . $selected . '>' . $user['contact_first_name'] . ' ' . $user['contact_last_name'] . '</option>';
    }
    $parsed .= '</select>';

    return $parsed;
}

/**
 * Function to format hours into useful numbers.
 * Supplied by GrahamJB.
 */
function formatHours($hours)
{
    global $AppUI;

    $hours = (int) $hours;
    $working_hours = w2PgetConfig('daily_working_hours');

    if ($hours < $working_hours) {
        if ($hours == 1) {
            return '1 ' . $AppUI->_('hour');
        } else {
            return $hours . ' ' . $AppUI->_('hours');
        }
    }

    $hoursPart = $hours % $working_hours;
    $daysPart = (int) ($hours / $working_hours);
    if ($hoursPart == 0) {
        if ($daysPart == 1) {
            return '1 ' . $AppUI->_('day');
        } else {
            return $daysPart . ' ' . $AppUI->_('days');
        }
    }

    if ($daysPart == 1) {
        return '1 ' . $AppUI->_('day') . ' ' . $hoursPart . ' ' . $AppUI->_('hr');
    } else {
        return $daysPart . ' ' . $AppUI->_('days') . ' ' . $hoursPart . ' ' . $AppUI->_('hr');
    }
}

/*
** Create the Required Fields (From Sysvals) JavaScript Code
** For instance implemented in projects and tasks addedit.php
** @param array required field array from SysVals
*/
function w2PrequiredFields($requiredFields)
{
    global $AppUI;
    $buffer = 'var foc=false;';

    if (!empty($requiredFields)) {
        foreach ($requiredFields as $rf => $comparator) {
            $buffer .= 'if (' . $rf . html_entity_decode($comparator, ENT_QUOTES) . ') {';
            $buffer .= 'msg += "\n' . $AppUI->_('required_field_' . $rf, UI_OUTPUT_JS) . '";';

            /* MSIE cannot handle the focus command for some disabled or hidden fields like the start/end date fields
            ** Another workaround would be to check whether the field is disabled,
            ** but then one would for instance need to use end_date instead of project_end_date in the projects addedit site.
            ** As this cannot be guaranteed since these fields are grabbed from a user-specifiable
            ** System Value it's IMHO more safe to disable the focus for MSIE.
            */
            $r = strstr($rf, '.');
            $buffer .= 'if ((foc==false) && (navigator.userAgent.indexOf(\'MSIE\')== -1)) {';
            $buffer .= 'f.' . substr($r, 1, strpos($r, '.', 1) - 1) . '.focus();';
            $buffer .= 'foc=true;}}';
        }
    }

    return $buffer;
}

/**
 * Return the number of bytes represented by a PHP.INI value
 */
function w2PgetBytes($str)
{
    $val = $str;
    if (preg_match('/^([0-9]+)([kmg])?$/i', $str, $match)) {
        if (!empty($match[2])) {
            switch (strtolower($match[2])) {
                case 'k':
                    $val = $match[1] * 1024;
                    break;
                case 'm':
                    $val = $match[1] * 1024 * 1024;
                    break;
                case 'g':
                    $val = $match[1] * 1024 * 1024 * 1024;
                    break;
            }
        }
    }

    return $val;
}

/**
 * Check for a memory limit, if we can't generate it then we fail.
 * @param int $min minimum amount of memory needed
 * @param bool $revert revert back to original config after test.
 * @return bool true if we have the minimum amount of RAM and if we can modify RAM
 */
function w2PcheckMem($min = 0, $revert = false)
{
    // First of all check if we have the minimum memory requirement.
    $want = w2PgetBytes(w2PgetConfig('reset_memory_limit'));
    $have = ini_get('memory_limit');
    // Try upping the memory limit based on our config
    ini_set('memory_limit', w2PgetConfig('reset_memory_limit'));
    $now = w2PgetBytes(ini_get('memory_limit'));
    // Revert, if necessary, back to the original after testing.
    if ($revert) {
        ini_set('memory_limit', $have);
    }
    if ($now < $want || $now < $min) {
        return false;
    } else {
        return true;
    }
}

/**
 * decode HTML entities
 */
function w2PHTMLDecode($txt)
{
    global $locale_char_set;

    if (!$locale_char_set) {
        $locale_char_set = 'utf-8';
    }

    if (is_object($txt)) {
        foreach (get_object_vars($txt) as $k => $v) {
            $txt->$k = html_entity_decode($v, ENT_COMPAT);
        }
    } else {
        if (is_array($txt)) {
            foreach ($txt as $k => $v) {
                $txt[$k] = (is_array($v)) ? $v : html_entity_decode($v, ENT_COMPAT);
            }
        } else {
            $txt = html_entity_decode($txt, ENT_COMPAT);
        }
    }

    return $txt;
}

function w2PtoolTip($header = '', $tip = '', $raw = false, $id = '')
{
    global $AppUI;

    $id = ('' == $id) ? '' : 'id="' . $id . '"';
    if ($raw) {
        $starttip = '<span ' . $id . ' title="&lt;h4&gt;' . nl2br($AppUI->_($header)) . '&lt;/h4&gt; ' . nl2br($AppUI->_($tip)) . '">';
    } else {
        $starttip = '<span ' . $id . ' title="&lt;h4&gt;' . nl2br(ucwords(strtolower($AppUI->_($header)))) . '&lt;/h4&gt; ' . nl2br(strtolower($AppUI->_($tip))) . '">';
    }

    return $starttip;
}

function w2PendTip()
{
    $endtip = '</span>';

    return $endtip;
}

/**
 *    Write debugging to debug.log file
 *
 *    @param string $s the debug message
 *    @param string $t the header of the message
 *    @param string $f the script filename
 *    @param string $l the script line
 *    @access public
 */
function w2PwriteDebug($s, $t = '', $f = '?', $l = '?')
{
    global $debug;

    $debug_file = W2P_BASE_DIR . '/files/debug.log';
    if ($debug && ($fp = fopen($debug_file, "at"))) {
        fputs($fp, "Debug message from file [$f], line [$l], at: " . strftime('%H:%S'));
        if ($t) {
            fputs($fp, "\n * * $t * *\n");
        }
        fputs($fp, "\n$s\n\n");
        fclose($fp);
    }
}

function w2p_pluralize($word)
{
    $rules= array(
        '/(matr|vert|ind)(ix|ex)$/i' => '\1ices', # matrix, vertex, index
        '/(ss|sh|ch|x|z)$/i' => '\1es', # sibilant rule (no ending e)
        '/([^aeiou])o$/i' => '\1oes', # -oes rule
        '/([^aeiou]|qu)y$/i' => '\1ies', # -ies rule
        '/sis$/i' => 'ses', # synopsis, diagnosis
        '/(m|l)ouse$/i' => '\1ice', # mouse, louse
        '/(t|i)um$/i' => '\1a', # datum, medium
        '/([li])fe?$/i' => '\1ves', # knife, life, shelf
        '/(octop|vir|syllab)us$/i' => '\1i', # octopus, virus, syllabus
        '/(ax|test)is$/i' => '\1es', # axis, testis
        '/([a-rt-z])$/i' => '\1s' # not ending in s
    );
    $irregulars = array(
        'bus' => 'busses',
        'child' => 'children',
        'equipment' => 'equipment',
        'fish' => 'fish',
        'information' => 'information',
        'man' => 'men',
        'money' => 'money',
        'moose' => 'moose',
        'news' => 'news',
        'person' => 'people',
        'quiz' => 'quizzes',
        'rice' => 'rice',
        'series' => 'series',
        'sheep' => 'sheep',
        'species' => 'species',
        'todo' => 'todos'
    );
    if (isset($irregulars[$word])) {
        return $irregulars[$word];
    }
    foreach ($rules as $regex => $replace) {
        $word = preg_replace($regex, $replace, $word, 1, $count);
        if ($count) {
            return $word;
        }
    }

    return $word;
}


function seconds2HM($sec, $padHours = true)
{
    $HM = "";
    // there are 3600 seconds in an hour, so if we
    // divide total seconds by 3600 and throw away
    // the remainder, we've got the number of hours
    $hours = (int) ($sec / 3600);
    // with the remaining seconds divide them by 60
    // and then round the floating number to get the precise minute
    $minutes = intval(round(($sec - ($hours * 3600)) / 60) ,0);

    if (intval($hours) == 0 && intval($minutes) < 0) {
        $HM .= '-0:';
    } else {
        // add to $hms, with a leading 0 if asked for
        $HM .= ($padHours) ? str_pad($hours, 2, "0", STR_PAD_LEFT). ':' : $hours. ':';
    }
    if (intval($hours) < 0 || intval($minutes) < 0) {
        $minutes = $minutes * (-1);
    }
    $HM .= str_pad($minutes, 2, "0", STR_PAD_LEFT);

    return $HM;
}

function HM2seconds($HM)
{
    list($h, $m) = explode (":", $HM);
    if (intval($h) > 23 && intval($h) < 0) $h = 0;
    if (intval($m) > 59 && intval($m) < 0) $m = 0;
    $seconds = 0;
    $seconds += (intval($h) * 3600);
    $seconds += (intval($m) * 60);

    return $seconds;
}

/**
 * Parse the SQL file and get out the timezones from it to use it on the install
 * screen. The SQL file used is: install/sql/mysql/018_add_timezones.sql
 */
function w2PgetTimezonesForInstall()
{
    $file = W2P_BASE_DIR . '/install/sql/018_add_timezones.sql';

    $timezones = array();

    if (is_file($file) and is_readable($file)) {
        $sql = file_get_contents($file);
        // get it from this kind of a string:
        // (1, 'Timezones', 'Pacific/Auckland', 43200);
        preg_match_all("#\(.*Timezones',\s*'(.*)',.*\);#", $sql, $matchedTimezones);

        sort($matchedTimezones[1]);

        foreach ($matchedTimezones[1] as $timezone) {
            $timezones[$timezone] = $timezone;
        }
    }

    return $timezones;
}

//
// New password code based oncode from Mambo Open Source Core
// www.mamboserver.com | mosforge.net
//

function sendNewPass()
{
    global $AppUI;

    // ensure no malicous sql gets past
    $checkusername = preg_replace("/[^A-Za-z0-9]/", "", w2PgetParam($_POST, 'checkusername', ''));
    $confirmEmail = trim(w2PgetParam($_POST, 'checkemail', ''));
    $confirmEmail = strtolower(db_escape($confirmEmail));

    $q = new w2p_Database_Query;
    $q->addTable('users');
    $q->addJoin('contacts', 'con', 'user_contact = contact_id', 'inner');
    $q->addQuery('user_id');
    $q->addWhere("user_username = '$checkusername'");

    /* Begin Hack */
    /*
     * This is a particularly annoying hack but I don't know of a better
     *   way to resolve #457. In v2.0, there was a refactoring to allow for
     *   muliple contact methods which resulted in the contact_email being
     *   removed from the contacts table. If the user is upgrading from
     *   v1.x and they try to log in before applying the database, crash.
     *   Info: http://bugs.web2project.net/view.php?id=457
     */
    $qTest = new w2p_Database_Query();
    $qTest->addTable('w2pversion');
    $qTest->addQuery('max(db_version)');
    $dbVersion = $qTest->loadResult();
    if ($dbVersion >= 21 && $dbVersion < 26) {
        $q->leftJoin('contacts_methods', 'cm', 'cm.contact_id = con.contact_id');
        $q->addWhere("cm.method_value = '$confirmEmail'");
    } else {
        $q->addWhere("LOWER(user_email) = '$confirmEmail'");
    }
    /* End Hack */

    $user_id = $q->loadResult();
    if (!$user_id) {
        $AppUI->setMsg('Invalid username or email.', UI_MSG_ERROR);
        $AppUI->redirect();
    }

    $auth = new w2p_Authenticators_SQL();
    $newpass = $auth->createNewPassword();
    $hashed  = $auth->hashPassword($newpass);

    $q->addTable('users');
    $q->addUpdate('user_password', $hashed);
    $q->addWhere('user_id=' . $user_id);
    $cur = $q->exec();

    if ($cur) {
        $emailManager = new w2p_Output_EmailManager($AppUI);
        $body = $emailManager->notifyPasswordReset($checkusername, $newpass);

        $m = new w2p_Utilities_Mail; // create the mail
        $m->To($confirmEmail);
        $subject = $_sitename . ' :: ' . $AppUI->_('sendpass4', UI_OUTPUT_RAW) . ' - ' . $checkusername;
        $m->Subject($subject);
        $m->Body($body, isset($GLOBALS['locale_char_set']) ? $GLOBALS['locale_char_set'] : ''); // set the body
        $m->Send(); // send the mail

        $AppUI->setMsg('New User Password created and emailed to you');
        $AppUI->redirect();
    }
}

// from modules/reports/overall.php
function showcompany($company_id, $restricted = false)
{
    global $AppUI, $allpdfdata, $log_start_date, $log_end_date, $log_all;
    $q = new w2p_Database_Query;
    $q->addTable('projects');
    $q->addQuery('project_id, project_name');
    $q->addWhere('project_company = ' . (int) $company_id);
    $projects = $q->loadHashList();
    $q->clear();

    $company = new CCompany();
    $company->load($company_id);
    $company_name = $company->company_name;

    $table = '<h2>Company: ' . $company_name . '</h2>
        <table cellspacing="1" cellpadding="4" border="0" class="tbl">';
    $project_row = '
        <tr>
                <th>' . $AppUI->_('Project') . '</th>';

    $pdfth[] = $AppUI->_('Project');
    $project_row .= '<th>' . $AppUI->_('Total') . '</th></tr>';
    $pdfth[] = $AppUI->_('Total');
    $pdfdata[] = $pdfth;

    $hours = 0.0;
    $table .= $project_row;

    foreach ($projects as $project => $name) {
        $pdfproject = array();
        $pdfproject[] = $name;
        $project_hours = 0;
        $project_row = '<tr><td>' . $name . '</td>';

        $q->addTable('projects');
        $q->addTable('tasks');
        $q->addTable('task_log');
        $q->addQuery('task_log_costcode, SUM(task_log_hours) as hours');
        $q->addWhere('project_id = ' . (int) $project);
        $q->addWhere('project_active = 1');
        if (($template_status = w2PgetConfig('template_projects_status_id')) != '') {
            $q->addWhere('project_status <> ' . (int) $template_status);
        }

        if ($log_start_date != 0 && !$log_all) {
            $q->addWhere('task_log_date >=' . $log_start_date);
        }
        if ($log_end_date != 0 && !$log_all) {
            $q->addWhere('task_log_date <=' . $log_end_date);
        }
        if ($restricted) {
            $q->addWhere('task_log_creator = ' . (int) $AppUI->user_id);
        }

        $q->addWhere('project_id = task_project');
        $q->addWhere('task_id = task_log_task');
        $q->addGroup('project_id');

        $task_logs = $q->loadHashList();
        $q->clear();

        foreach ($task_logs as $task_log) {
            $project_hours += $task_log;
        }
        $project_row .= '<td style="text-align:right;">' . sprintf('%.2f', round($project_hours, 2)) . '</td></tr>';
        $pdfproject[] = round($project_hours, 2);
        $hours += $project_hours;
        if ($project_hours > 0) {
            $table .= $project_row;
            $pdfdata[] = $pdfproject;
        }
    }

    if ($hours > 0) {
        $pdfdata[] = array($AppUI->_('Total'), round($hours, 2));
        $allpdfdata[$company_name] = $pdfdata;
        echo $table;
        echo '<tr><td>' . $AppUI->_('Total') . '</td><td style="text-align:right;">' . sprintf('%.2f', round($hours, 2)) . '</td></tr></table>';
    }

    return $hours;
}

/**
 * Sub-function to collect events within a period
 * @param Date the starting date of the period
 * @param Date the ending date of the period
 * @param array by-ref an array of links to append new items to
 * @param int the length to truncate entries by
 * @author Andrew Eddie <eddieajau@users.sourceforge.net>
 */
function getEventLinks($startPeriod, $endPeriod, $links, $notUsed = null, $minical = false)
{
    global $event_filter;
    $events = CEvent::getEventsForPeriod($startPeriod, $endPeriod, $event_filter);
    $cwd = explode(',', w2PgetConfig('cal_working_days'));

    // assemble the links for the events
    foreach ($events as $row) {
        $start = new w2p_Utilities_Date($row['event_start_date']);
        $end = new w2p_Utilities_Date($row['event_end_date']);
        $date = $start;

        for ($i = 0, $i_cmp = $start->dateDiff($end); $i <= $i_cmp; $i++) {
            // the link
            // optionally do not show events on non-working days
            if (($row['event_cwd'] && in_array($date->getDayOfWeek(), $cwd)) || !$row['event_cwd']) {
                if ($minical) {
                    $link = array();
                } else {
                    $url = '?m=events&a=view&event_id=' . $row['event_id'];
                    $link['href'] = '';
                    $link['alt'] = '';
                    $link['text'] = w2PtoolTip($row['event_name'], getEventTooltip($row['event_id']), true) . w2PshowImage('modules/events/event' . $row['event_type'] . '.png', 16, 16, '', '', 'calendar') . '</a>&nbsp;' . '<a href="' . $url . '"><span class="event">' . $row['event_name'] . '</span></a>' . w2PendTip();
                }
                $links[$date->format(FMT_TIMESTAMP_DATE)][] = $link;
            }
            $date = $date->getNextDay();
        }
    }

    return $links;
}

function getEventTooltip($event_id)
{
    global $AppUI;

    if (!$event_id) {
        return '';
    }

    $df = $AppUI->getPref('SHDATEFORMAT');
    $tf = $AppUI->getPref('TIMEFORMAT');

    // load the record data

    $event = new CEvent();
    $event->loadFull($event_id);

    // load the event types
    $types = w2PgetSysVal('EventType');

    // load the event recurs types
    $recurs = array('Never', 'Hourly', 'Daily', 'Weekly', 'Bi-Weekly', 'Every Month', 'Quarterly', 'Every 6 months', 'Every Year');

    $obj = new CEvent();
    $obj->event_id = $event_id;
    $assigned = $obj->getAssigned();

    if ($event->event_project) {
        $event_project = $event->project_name;
        $event_company = $event->company_name;
    }

    $tt = '<table class="tool-tip">';
    $tt .= '<tr>';
    $tt .= '	<td valign="top" width="40%">';
    $tt .= '		<strong>' . $AppUI->_('Details') . '</strong>';
    $tt .= '		<table cellspacing="3" cellpadding="2" width="100%">';
    $tt .= '		<tr>';
    $tt .= '			<td class="tip-label">' . $AppUI->_('Type') . '</td>';
    $tt .= '			<td>' . $AppUI->_($types[$event->event_type]) . '</td>';
    $tt .= '		</tr>	';
    if ($event->event_project) {
        $tt .= '		<tr>';
        $tt .= '			<td class="tip-label">' . $AppUI->_('Company') . '</td>';
        $tt .= '			<td>' . $event_company . '</td>';
        $tt .= '		</tr>';
        $tt .= '		<tr>';
        $tt .= '			<td class="tip-label">' . $AppUI->_('Project') . '</td>';
        $tt .= '			<td>' . $event_project . '</td>';
        $tt .= '		</tr>';
    }
    $tt .= '		<tr>';
    $tt .= '			<td class="tip-label">' . $AppUI->_('Starts') . '</td>';
    $tt .= '			<td>' . $AppUI->formatTZAwareTime($event->event_start_date, $df . ' ' . $tf) . '</td>';
    $tt .= '		</tr>';
    $tt .= '		<tr>';
    $tt .= '			<td class="tip-label">' . $AppUI->_('Ends') . '</td>';
    $tt .= '			<td>' . $AppUI->formatTZAwareTime($event->event_end_date, $df . ' ' . $tf) . '</td>';
    $tt .= '		</tr>';
    $tt .= '		<tr>';
    $tt .= '			<td class="tip-label">' . $AppUI->_('Recurs') . '</td>';
    $tt .= '			<td>' . $AppUI->_($recurs[$event->event_recurs]) . ($event->event_recurs ? ' (' . $event->event_times_recuring . '&nbsp;' . $AppUI->_('times') . ')' : '') . '</td>';
    $tt .= '		</tr>';
    $tt .= '		<tr>';
    $tt .= '			<td class="tip-label">' . $AppUI->_('Attendees') . '</td>';
    $tt .= '			<td>';
    $tt .= implode('<br />', $assigned);
    $tt .= '		</tr>';
    $tt .= '		</table>';
    $tt .= '	</td>';
    $tt .= '	<td width="60%" valign="top">';
    $tt .= '		<strong>' . $AppUI->_('Note') . '</strong>';
    $tt .= '		<table cellspacing="0" cellpadding="2" border="0" width="100%">';
    $tt .= '		<tr>';
    $tt .= '			<td class="tip-label description">';
    $tt .= '				' . mb_str_replace(chr(10), "<br />", $event->event_description) . '&nbsp;';
    $tt .= '			</td>';
    $tt .= '		</tr>';
    $tt .= '		</table>';
    $tt .= '	</td>';
    $tt .= '</tr>';
    $tt .= '</table>';

    return $tt;
}

/**
 * Sub-function to collect tasks within a period
 *
 * @param Date the starting date of the period
 * @param Date the ending date of the period
 * @param array by-ref an array of links to append new items to
 * @param int the length to truncate entries by
 * @param int the company id to filter by
 * @author Andrew Eddie <eddieajau@users.sourceforge.net>
 */
function getTaskLinks($startPeriod, $endPeriod, $links, $strMaxLen, $company_id = 0, $minical = false, $userid=0)
{
    global $a, $AppUI;
    $tasks = CTask::getTasksForPeriod($startPeriod, $endPeriod, $company_id, $userid);
    $tf = $AppUI->getPref('TIMEFORMAT');
    //subtract one second so we don't have to compare the start dates for exact matches with the startPeriod which is 00:00 of a given day.
    $startPeriod->subtractSeconds(1);

    $link = array();

    // assemble the links for the tasks
    foreach ($tasks as $row) {
        // the link
        $link['task'] = true;

        if (!$minical) {
            $link['href'] = '?m=tasks&a=view&task_id=' . $row['task_id'];
            // the link text
            if (mb_strlen($row['task_name']) > $strMaxLen) {
                $row['short_name'] = mb_substr($row['task_name'], 0, $strMaxLen) . '...';
            } else {
                $row['short_name'] = $row['task_name'];
            }

            $link['text'] = '<span style="color:' . bestColor($row['color']) . ';background-color:#' . $row['color'] . '">' . $row['short_name'] . ($row['task_milestone'] ? '&nbsp;' . w2PshowImage('icons/milestone.gif') : '') . '</span>';
        }

        // determine which day(s) to display the task
        $start = new w2p_Utilities_Date($AppUI->formatTZAwareTime($row['task_start_date'], '%Y-%m-%d %T'));
        $end = $row['task_end_date'] ? new w2p_Utilities_Date($AppUI->formatTZAwareTime($row['task_end_date'], '%Y-%m-%d %T')) : null;

        // First we test if the Tasks Starts and Ends are on the same day, if so we don't need to go any further.
        if (($start->after($startPeriod)) && ($end && $end->after($startPeriod) && $end->before($endPeriod) && !($start->dateDiff($end)))) {
            if ($minical) {
                $temp = array('task' => true);
            } else {
                $temp = $link;
                if ($a != 'day_view') {
                    $temp['text'] = w2PtoolTip($row['task_name'], getTaskTooltip($row['task_id']), true) . w2PshowImage('block-start-16.png') . $start->format($tf) . ' ' . $temp['text'] . ' ' . $end->format($tf) . w2PshowImage('block-end-16.png') . w2PendTip();
                    $temp['text'].= '<a href="?m=tasks&amp;a=view&amp;task_id=' . $row['task_id'] . '&amp;tab=1&amp;date=' . $AppUI->formatTZAwareTime($row['task_end_date'], '%Y%m%d'). '">' . w2PtoolTip('Add Log', 'create a new log record against this task') . w2PshowImage('edit_add.png') . w2PendTip() . '</a>';
                }
            }
            $links[$end->format(FMT_TIMESTAMP_DATE)][] = $temp;
        } else {
            // If they aren't, we will now need to see if the Tasks Start date is between the requested period
            if ($start->after($startPeriod) && $start->before($endPeriod)) {
                if ($minical) {
                    $temp = array('task' => true);
                } else {
                    $temp = $link;
                    if ($a != 'day_view') {
                        $temp['text'] = w2PtoolTip($row['task_name'], getTaskTooltip($row['task_id']), true) . w2PshowImage('block-start-16.png') . $start->format($tf) . ' ' . $temp['text'] . w2PendTip();
                        $temp['text'].= '<a href="?m=tasks&amp;a=view&amp;task_id=' . $row['task_id'] . '&amp;tab=1&amp;date=' . $AppUI->formatTZAwareTime($row['task_start_date'], '%Y%m%d'). '">' . w2PtoolTip('Add Log', 'create a new log record against this task') . w2PshowImage('edit_add.png') . w2PendTip() . '</a>';
                    }
                }
                $links[$start->format(FMT_TIMESTAMP_DATE)][] = $temp;
            }
            // And now the Tasks End date is checked if it is between the requested period too.
            if ($end && $end->after($startPeriod) && $end->before($endPeriod) && $start->before($end)) {
                if ($minical) {
                    $temp = array('task' => true);
                } else {
                    $temp = $link;
                    if ($a != 'day_view') {
                        $temp['text'] = w2PtoolTip($row['task_name'], getTaskTooltip($row['task_id']), true) . ' ' . $temp['text'] . ' ' . $end->format($tf) . w2PshowImage('block-end-16.png') . w2PendTip();
                        $temp['text'].= '<a href="?m=tasks&amp;a=view&amp;task_id=' . $row['task_id'] . '&amp;tab=1&amp;date=' . $AppUI->formatTZAwareTime($row['task_end_date'], '%Y%m%d'). '">' . w2PtoolTip('Add Log', 'create a new log record against this task') . w2PshowImage('edit_add.png') . w2PendTip() . '</a>';
                    }
                }
                $links[$end->format(FMT_TIMESTAMP_DATE)][] = $temp;
            }
        }
    }

    return $links;
}

function getTaskTooltip($task_id)
{
    global $AppUI;

    if (!$task_id) {
        return '';
    }

    $df = $AppUI->getPref('SHDATEFORMAT');
    $tf = $AppUI->getPref('TIMEFORMAT');

    $task = new CTask();

    // load the record data
    $task->load($task_id);

    // load the event types
    $types = w2PgetSysVal('TaskType');

    $assignees = $task->assignees($task_id);
    $assigned = array();
    foreach ($assignees as $user) {
        $assigned[] = $user['contact_name'] . ' ' . $user['perc_assignment'] . '%';
    }

    $start_date = (int) $task->task_start_date ? new w2p_Utilities_Date($AppUI->formatTZAwareTime($task->task_start_date, '%Y-%m-%d %T')) : null;
    $end_date = (int) $task->task_end_date ? new w2p_Utilities_Date($AppUI->formatTZAwareTime($task->task_end_date, '%Y-%m-%d %T')) : null;

    // load the record data
    $project = new CProject();
    $project->load($task->task_project);
    $task_project = $project->project_name;

    $company = new CCompany();
    $company->load($project->project_company);
    $task_company = $company->company_name;

    $tt = '<table class="tool-tip">';
    $tt .= '<tr>';
    $tt .= '	<td valign="top" width="40%">';
    $tt .= '		<strong>' . $AppUI->_('Details') . '</strong>';
    $tt .= '		<table cellspacing="3" cellpadding="2" width="100%">';
    $tt .= '		<tr>';
    $tt .= '			<td class="tip-label">' . $AppUI->_('Company') . '</td>';
    $tt .= '			<td>' . $task_company . '</td>';
    $tt .= '		</tr>';
    $tt .= '		<tr>';
    $tt .= '			<td class="tip-label">' . $AppUI->_('Project') . '</td>';
    $tt .= '			<td>' . $task_project . '</td>';
    $tt .= '		</tr>';
    $tt .= '		<tr>';
    $tt .= '			<td class="tip-label">' . $AppUI->_('Type') . '</td>';
    $tt .= '			<td>' . $AppUI->_($types[$task->task_type]) . '</td>';
    $tt .= '		</tr>	';
    $tt .= '		<tr>';
    $tt .= '			<td class="tip-label">' . $AppUI->_('Progress') . '</td>';
    $tt .= '			<td>' . sprintf("%.1f%%", $task->task_percent_complete) . '</td>';
    $tt .= '		</tr>	';
    $tt .= '		<tr>';
    $tt .= '			<td class="tip-label">' . $AppUI->_('Starts') . '</td>';
    $tt .= '			<td>' . ($start_date ? $start_date->format($df . ' ' . $tf) : '-') . '</td>';
    $tt .= '		</tr>';
    $tt .= '		<tr>';
    $tt .= '			<td class="tip-label">' . $AppUI->_('Ends') . '</td>';
    $tt .= '			<td>' . ($end_date ? $end_date->format($df . ' ' . $tf) : '-') . '</td>';
    $tt .= '		</tr>';
    $tt .= '		<tr>';
    $tt .= '			<td class="tip-label">' . $AppUI->_('Assignees') . '</td>';
    $tt .= '			<td>';
    $tt .= implode('<br />', $assigned);
    $tt .= '		</tr>';
    $tt .= '		</table>';
    $tt .= '	</td>';
    $tt .= '	<td width="60%" valign="top">';
    $tt .= '		<strong>' . $AppUI->_('Description') . '</strong>';
    $tt .= '		<table cellspacing="0" cellpadding="2" border="0" width="100%">';
    $tt .= '		<tr>';
    $tt .= '			<td class="tip-label description">';
    $tt .= '				' . $task->task_description;
    $tt .= '			</td>';
    $tt .= '		</tr>';
    $tt .= '		</table>';
    $tt .= '	</td>';
    $tt .= '</tr>';
    $tt .= '</table>';

    return $tt;
}

/**
 * @param $perms
 * @param $user_id
 * @param $module
 * @param $action
 *
 * @return array
 */
function getPermissions($perms, $user_id, $module, $action)
{
    $q = new w2p_Database_Query;
    $q->addTable($perms->_db_acl_prefix . 'permissions', 'gp');
    $q->addQuery('gp.*');
    $q->addWhere('user_id = ' . $user_id);
    if ('all' != $module) {
        $q->addWhere("module = '$module'");
    }
    if ('all' != $action) {
        $q->addWhere("action = '$action'");
    }

    $q->addOrder('user_name');
    $q->addOrder('module');
    $q->addOrder('action');
    $q->addOrder('item_id');
    $q->addOrder('acl_id');
    $permissions = $q->loadList();

    return $permissions;
}

/**
 * @param $row
 *
 * @return array
 */
function getPermissionField($row)
{
    $q = new w2p_Database_Query;
    $q->addTable('modules');
    $q->addQuery('permissions_item_field,permissions_item_label');
    $q->addWhere('mod_directory = \'' . $row['module'] . '\'');
    $field = $q->loadHash();

    return $field;
}

/**
 * @param $row
 * @param $field
 *
 * @return Value
 */
function getPermissionItem($row, $field)
{
    $q = new w2p_Database_Query;
    $q->addTable($row['module']);
    $q->addQuery($field['permissions_item_label']);
    $q->addWhere($field['permissions_item_field'] . ' = \'' . $row['item_id'] . '\'');
    $item = $q->loadResult();

    return $item;
}

/**
 * @param $user_id
 *
 * @return array
 */
function getPreferences($user_id)
{
    $q = new w2p_Database_Query;
    $q->addTable('user_preferences');
    $q->addQuery('pref_name, pref_value');
    $q->addWhere('pref_user = ' . (int) $user_id);
    $prefs = $q->loadHashList();

    return $prefs;
}

/**
 * @param $obj
 *
 * @return array
 */
function getTaskLogContacts($obj)
{
    $q = new w2p_Database_Query();
    $q->addTable('task_contacts', 'tc');
    $q->addJoin('contacts', 'c', 'c.contact_id = tc.contact_id', 'inner');
    $q->addWhere('tc.task_id = ' . (int) $obj->task_id);
    $q->addQuery('tc.contact_id');
    $q->addQuery('c.contact_first_name, c.contact_last_name');
    $req = & $q->exec();

    return $req;
}

/**
 * @param $obj
 *
 * @return array
 */
function getContactsfromProjects($obj)
{
    $q = new w2p_Database_Query();
    $q->addTable('project_contacts', 'pc');
    $q->addJoin('contacts', 'c', 'c.contact_id = pc.contact_id', 'inner');
    $q->addWhere('pc.project_id = ' . (int) $obj->task_project);
    $q->addQuery('pc.contact_id');
    $q->addQuery('c.contact_first_name, c.contact_last_name');
    $req = & $q->exec();

    return $req;
}

/**
 * @param $project_id
 * @param $AppUI
 *
 * @return Associative
 */
function __extract_from_tasks_viewgantt($project_id, $AppUI)
{
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
    $task = new CTask;
    $q = $task->setAllowedSQL($AppUI->user_id, $q);
    $proTasks = $q->loadHashList('task_id');

    return $proTasks;
}

/**
 * @param $user_id
 * @param $showArcProjs
 * @param $showLowTasks
 * @param $showInProgress
 * @param $showHoldProjs
 * @param $showDynTasks
 * @param $showPinned
 * @param $showEmptyDate
 * @param $task_type
 * @param $allowedTasks
 * @param $allowedProjects
 *
 * @return Array
 */
function __extract_from_todo($user_id, $showArcProjs, $showLowTasks, $showInProgress, $showHoldProjs, $showDynTasks, $showPinned, $showEmptyDate, $task_type, $allowedTasks, $allowedProjects)
{
// query my sub-tasks (ignoring task parents)

    $q = new w2p_Database_Query;
    $q->addQuery('distinct(ta.task_id), ta.*, ta.task_start_date as task_start_datetime, ta.task_end_date as task_end_datetime');
    $q->addQuery('project_name, pr.project_id, project_color_identifier');
    $q->addQuery('tp.task_pinned');
    $q->addQuery('ut.user_task_priority');
    $dateDiffString = $q->dbfnDateDiff('ta.task_end_date', $q->dbfnNow()) . ' AS task_due_in';
    $q->addQuery($dateDiffString);

    $q->addTable('projects', 'pr');
    $q->addTable('tasks', 'ta');
    $q->addTable('user_tasks', 'ut');
    $q->leftJoin('user_task_pin', 'tp', 'tp.task_id = ta.task_id and tp.user_id = ' . (int) $user_id);

    $q->addWhere('ut.task_id = ta.task_id');
    $q->addWhere('ut.user_id = ' . (int) $user_id);
    $q->addWhere('( ta.task_percent_complete < 100 or ta.task_percent_complete is null)');

    $q->addWhere('ta.task_status = 0');
    $q->addWhere('pr.project_id = ta.task_project');
    if (!$showArcProjs) {
        $q->addWhere('project_active = 1');
        if (($template_status = w2PgetConfig('template_projects_status_id')) != '') {
            $q->addWhere('project_status <> ' . (int) $template_status);
        }
    }
    if (!$showLowTasks) {
        $q->addWhere('task_priority >= 0');
    }
    if ($showInProgress) {
        $q->addWhere('project_status = 3');
    }
    if (!$showHoldProjs) {
        if (($on_hold_status = w2PgetConfig('on_hold_projects_status_id')) != '') {
            $q->addWhere('project_status <> ' . (int) $on_hold_status);
        }
    }
    if (!$showDynTasks) {
        $q->addWhere('task_dynamic <> 1');
    }
    if ($showPinned) {
        $q->addWhere('task_pinned = 1');
    }
    if (!$showEmptyDate) {
        $q->addWhere('ta.task_start_date <> \'\' AND ta.task_start_date <> \'0000-00-00 00:00:00\'');
    }
    if ($task_type != '') {
        $q->addWhere('ta.task_type = ' . (int) $task_type);
    }

    if (count($allowedTasks)) {
        $q->addWhere($allowedTasks);
    }

    if (count($allowedProjects)) {
        $q->addWhere($allowedProjects);
    }

    $q->addOrder('task_end_date, task_start_date, task_priority');
    $tasks = $q->loadList();

    return $tasks;
}

/**
 * @param $use_period
 * @param $ss
 * @param $se
 * @param $log_userfilter
 * @param $project_id
 * @param $company_id
 * @param $proj
 * @param $AppUI
 *
 * @return mixed
 */
function __extract_from_tasksperuser($use_period, $ss, $se, $log_userfilter, $project_id, $company_id, $proj, $AppUI, $all_proj_status='on')
{
    $q = new w2p_Database_Query;
    $q->addTable('tasks', 't');
    $q->addQuery('t.*');
    $q->addJoin('projects', 'pr', 'pr.project_id = t.task_project', 'inner');
    $q->addWhere('pr.project_active = 1');
    if ('off'==$all_proj_status) {
		 $q->addWhere('pr.project_status = 3 ');
		}
		else {
			
		  if (($template_status = w2PgetConfig('template_projects_status_id')) != '') {
        $q->addWhere('pr.project_status <> ' . (int) $template_status);
    }
	}

    if ('on' == $use_period) {
        $q->addWhere('(( task_start_date >= ' . $ss . ' AND task_start_date <= ' . $se . ' ) OR ' . '  ( task_end_date <= ' . $se . ' AND task_end_date >= ' . $ss . ' ))');
    }
    $q->addWhere('(task_percent_complete < 100)');

    $q->addJoin('user_tasks', 'ut', 'ut.task_id = t.task_id');
    if ($log_userfilter > -1) {
        $q->addWhere('ut.user_id = ' . $log_userfilter);
    }

    if ($project_id != 'all') {
        $q->addWhere('t.task_project=' . (int) $project_id);
    }

    if ($company_id != 'all') {
        $q->addWhere('pr.project_company = ' . (int) $company_id);
    }

    $q->addOrder('task_project');
    $q->addOrder('task_end_date');
    $q->addOrder('task_start_date');
    $q = $proj->setAllowedSQL($AppUI->user_id, $q, null, 'pr');

    $task_list_hash = $q->loadHashList('task_id');

    return $task_list_hash;
}

/**
 * @return String
 */
function __extract_from_tasks1()
{
//subquery the parent state
    $sq = new w2p_Database_Query;
    $sq->addTable('tasks', 'stasks');
    $sq->addQuery('COUNT(stasks.task_id)');
    $sq->addWhere('stasks.task_id <> tasks.task_id AND stasks.task_parent = tasks.task_id');
    $subquery = $sq->prepare();

    return $subquery;
}

/**
 * @param $userFilter
 * @param $AppUI
 * @param $proj
 *
 * @return Array
 */
function __extract_from_listtasks($userFilter, $AppUI, $proj)
{
    $q = new w2p_Database_Query();
    $q->addQuery('t.task_id, t.task_name');
    $q->addTable('tasks', 't');

    if ($userFilter) {
        $q->addJoin('user_tasks', 'ut', 'ut.task_id = t.task_id');
        $q->addWhere('ut.user_id = ' . (int) $AppUI->user_id);
    }
    if ($proj != 0) {
        $q->addWhere('task_project = ' . (int) $proj);
    }
    $tasks = $q->loadList();

    return $tasks;
}

/**
 * @param $selected
 * @param $task_priority
 */
function __extract_from_tasks_todo($selected, $task_priority)
{
    $q = new w2p_Database_Query;
    foreach ($selected as $key => $val) {
        if ($task_priority == 'c') {
            // mark task as completed
            $q->addTable('tasks');
            $q->addUpdate('task_percent_complete', '100');
            $q->addWhere('task_id=' . (int) $val);
        } else {
            if ($task_priority == 'd') {
                // delete task
                $q->setDelete('tasks');
                $q->addWhere('task_id=' . (int) $val);
            } else
                if ($task_priority > -2 && $task_priority < 2) {
                    // set priority
                    $q->addTable('tasks');
                    $q->addUpdate('task_priority', $task_priority);
                    $q->addWhere('task_id=' . (int) $val);
                }
        }
        $q->exec();
        echo db_error();
        $q->clear();
    }
}

/**
 * @return w2p_Database_Query
 */
function __extract_from_syskeys_index1()
{
// pull all the key types
    $q = new w2p_Database_Query;
    $q->addTable('syskeys');
    $q->addQuery('syskey_id,syskey_name');
    $q->addOrder('syskey_name');
    $keys = arrayMerge(array(0 => '- Select Type -'), $q->loadHashList());

    return $keys;
}

/**
 * @return array
 */
function __extract_from_syskeys_index2()
{
    $q = new w2p_Database_Query;
    $q->addTable('syskeys');
    $q->addTable('sysvals');
    $q->addQuery('DISTINCT sysval_title, sysval_key_id, syskeys.*');
    $q->addWhere('sysval_key_id = syskey_id');
    $q->addOrder('sysval_title');
    $q->addOrder('sysval_id');

    return $q->loadList();
}

/**
 * @return Array
 */
function __extract_from_syskeys_index3()
{
    $q = new w2p_Database_Query;
    $q->addTable('sysvals');
    $q->addTable('syskeys');
    $q->addQuery('sysval_title, sysval_value_id, sysval_value, syskey_sep1, syskey_sep2');
    $q->addWhere('sysval_key_id = syskey_id');
    $q->addOrder('sysval_title');
    $q->addOrder('sysval_id');
    $vals = $q->loadList();

    return $vals;
}

/**
 * @return Array
 */
function __extract_from_syskeys_syskey()
{
    $q = new w2p_Database_Query;
    $q->addTable('syskeys');
    $q->addQuery('*');
    $q->addOrder('syskey_name');
    $keys = $q->loadList();

    return $keys;
}

function __extract_from_systemconfig_aed()
{
// set all checkboxes to false
// overwrite the true/enabled/checked checkboxes later
    $q = new w2p_Database_Query;
    $q->addTable('config');
    $q->addUpdate('config_value', 'false');
    $q->addWhere("config_type = 'checkbox'");
    $q->loadResult();
}

/**
 * @param $hidden_modules
 *
 * @return Array
 */
function __extract_from_modules_index($hidden_modules)
{
    $q = new w2p_Database_Query;
    $q->addQuery('*');
    $q->addTable('modules');
    foreach ($hidden_modules as $no_show) {
        $q->addWhere('mod_directory <> \'' . $no_show . '\'');
    }
    $q->addOrder('mod_ui_order');
    $modules = $q->loadList();

    return $modules;
}

/**
 * @param $module
 * @param $mod_data
 *
 * @return Value
 */
function __extract_from_role_perms($module, $mod_data)
{
    $q = new w2p_Database_Query();
    $q->addTable($module['permissions_item_table']);
    $q->addQuery($module['permissions_item_label']);
    $q->addWhere($module['permissions_item_field'] . '=' . $mod_data['name']);
    $data = $q->loadResult();
    $q->clear();

    return $data;
}

/**
 * @param $deps
 *
 * @return Associative
 */
function __extract_from_ae_depend1($deps)
{
    $q = new w2p_Database_Query;
    $q->addTable('tasks');
    $q->addQuery('task_id, task_name');
    $q->addWhere('task_id IN (' . $deps . ')');
    $taskDep = $q->loadHashList();

    return $taskDep;
}

/**
 * @param $task_id
 *
 * @return Associative
 */
function __extract_from_ae_depend2($task_id)
{
    if (0 == (int) $task_id)
    {
        return array();
    }

    $q = new w2p_Database_Query;
    $q->addTable('tasks', 't');
    $q->addTable('task_dependencies', 'td');
    $q->addQuery('t.task_id, t.task_name');
    $q->addWhere('td.dependencies_task_id = ' . (int) $task_id);
    $q->addWhere('t.task_id = td.dependencies_req_task_id');
    $taskDep = $q->loadHashList();

    return $taskDep;
}

/**
 * @param $user_id
 * @param $showArcProjs
 * @param $showLowTasks
 * @param $showHoldProjs
 * @param $showDynTasks
 * @param $showPinned
 * @param $task
 * @param $AppUI
 *
 * @return array
 */
function __extract_from_tasks_gantt1($user_id, $showArcProjs, $showLowTasks, $showHoldProjs, $showDynTasks, $showPinned, $task, $AppUI)
{
    $q = new w2p_Database_Query;
    $q->addQuery('t.*');
    $q->addQuery('project_name, project_id, project_color_identifier');
    $q->addQuery('tp.task_pinned');
    $q->addTable('tasks', 't');
    $q->innerJoin('projects', 'pr', 'pr.project_id = t.task_project');
    $q->innerJoin('user_tasks', 'ut', 'ut.task_id = t.task_id AND ut.user_id = ' . (int) $user_id);
    $q->leftJoin('user_task_pin', 'tp', 'tp.task_id = t.task_id and tp.user_id = ' . (int) $user_id);
    $q->addWhere('(t.task_percent_complete < 100 OR t.task_percent_complete IS NULL)');
    $q->addWhere('t.task_status = 0');
    if (!$showArcProjs) {
        $q->addWhere('pr.project_active = 1');
        if (($template_status = w2PgetConfig('template_projects_status_id')) != '') {
            $q->addWhere('pr.project_status <> ' . (int) $template_status);
        }
    }
    if (!$showLowTasks) {
        $q->addWhere('task_priority >= 0');
    }
    if (!$showHoldProjs) {
        $q->addWhere('project_active = 1');
    }
    if (!$showDynTasks) {
        $q->addWhere('task_dynamic <> 1');
    }
    if ($showPinned) {
        $q->addWhere('task_pinned = 1');
    }

    $q->addGroup('t.task_id');
    $q->addOrder('t.task_start_date, t.task_end_date, t.task_priority');
    // get any specifically denied tasks
    $q = $task->setAllowedSQL($AppUI->user_id, $q);
    $proTasks = $q->loadHashList('task_id');

    return $proTasks;
}

/**
 * @param $showNoMilestones
 * @param $showMilestonesOnly
 * @param $ganttTaskFilter
 * @param $where
 * @param $project_id
 * @param $f
 * @param $AppUI
 * @param $task
 *
 * @return array
 */
function __extract_from_tasks_gantt2($showNoMilestones, $showMilestonesOnly, $ganttTaskFilter, $where, $project_id, $f, $AppUI, $task)
{
// pull tasks
    $q = new w2p_Database_Query;
    $q->addTable('tasks', 't');
    $q->addQuery('t.task_id, task_parent, task_name, task_start_date, task_end_date,' .
    ' task_duration, task_duration_type, task_priority, task_percent_complete,' .
    ' task_hours_worked, task_order, task_project, task_milestone, task_access,' .
    ' task_owner, project_name, project_color_identifier, task_dynamic');
    $q->addJoin('projects', 'p', 'project_id = t.task_project', 'inner');

    // don't add milestones if box is checked//////////////////////////////////////////////////////////
    if ($showNoMilestones) {
        $q->addWhere('task_milestone != 1');
    }
    if ($showMilestonesOnly) {
        $q->addWhere('task_milestone = 1');
    }
    if ($ganttTaskFilter) {
        $q->addWhere($where);
    }
    if ($project_id) {
        $q->addWhere('task_project = ' . (int) $project_id);
    }

    switch ($f) {
        case 'all':
            $q->addWhere('task_status > -1');
            break;
        case 'myproj':
            $q->addWhere('task_status > -1');
            $q->addWhere('project_owner = ' . (int) $AppUI->user_id);
            break;
        case 'mycomp':
            $q->addWhere('task_status > -1');
            $q->addWhere('project_company = ' . (int) $AppUI->user_company);
            break;
        case 'myinact':
            $q->innerJoin('user_tasks', 'ut', 'ut.task_id = t.task_id');
            $q->addWhere('task_project = p.project_id');
            $q->addWhere('ut.user_id = ' . (int) $AppUI->user_id);
            break;
        default:
            $q->innerJoin('user_tasks', 'ut', 'ut.task_id = t.task_id');
            $q->addWhere('task_status > -1');
            $q->addWhere('task_project = p.project_id');
            $q->addWhere('ut.user_id = ' . (int) $AppUI->user_id);
            break;
    }
    $q->addOrder('t.task_start_date, t.task_end_date, t.task_priority');
    // get any specifically denied tasks

    $q = $task->setAllowedSQL($AppUI->user_id, $q);
    $proTasks = $q->loadHashList('task_id');

    return $proTasks;
}

/**
 * @param $a
 *
 * @return array
 */
function __extract_from_gantt_pdf($a)
{
    $q = new w2p_Database_Query;
    $q->addTable('tasks', 't');
    $q->addJoin('user_tasks', 'u', 't.task_id = u.task_id');
    $q->addQuery('ROUND(SUM(t.task_duration*u.perc_assignment/100),2) AS wh');
    $q->addWhere('t.task_duration_type = 24');
    $q->addWhere('t.task_id = ' . (int) $a['task_id']);
    $wh = $q->loadResult();

    return $wh;
}

/**
 * @param $a
 *
 * @return array
 */
function __extract_from_gantt_pdf2($a)
{
    $q = new w2p_Database_Query;
    $q->addTable('tasks', 't');
    $q->addJoin('user_tasks', 'u', 't.task_id = u.task_id');
    $q->addQuery('ROUND(SUM(t.task_duration*u.perc_assignment/100),2) AS wh');
    $q->addWhere('t.task_duration_type = 1');
    $q->addWhere('t.task_id = ' . (int) $a['task_id']);
    $wh2 = $q->loadResult();

    return $wh2;
}

/**
 * @param $user_id
 * @param $showArcProjs
 * @param $showLowTasks
 * @param $showHoldProjs
 * @param $showDynTasks
 * @param $showPinned
 * @param $task
 * @param $AppUI
 *
 * @return array
 */
function __extract_from_gantt_pdf3($user_id, $showArcProjs, $showLowTasks, $showHoldProjs, $showDynTasks, $showPinned, $task, $AppUI)
{
    $q = new w2p_Database_Query;
    $q->addQuery('t.*');
    $q->addQuery('project_name, project_id, project_color_identifier');
    $q->addQuery('tp.task_pinned');
    $q->addTable('tasks', 't');
    $q->innerJoin('projects', 'pr', 'pr.project_id = t.task_project');
    $q->leftJoin('user_tasks', 'ut', 'ut.task_id = t.task_id AND ut.user_id = ' . (int) $user_id);
    $q->leftJoin('user_task_pin', 'tp', 'tp.task_id = t.task_id and tp.user_id = ' . (int) $user_id);
    $q->addWhere('(t.task_percent_complete < 100 OR t.task_percent_complete IS NULL)');
    $q->addWhere('t.task_status = 0');
    if (!$showArcProjs) {
        $q->addWhere('pr.project_active = 1');
        if (($template_status = w2PgetConfig('template_projects_status_id')) != '') {
            $q->addWhere('pr.project_status <> ' . (int) $template_status);
        }
    }
    if (!$showLowTasks) {
        $q->addWhere('task_priority >= 0');
    }
    if (!$showHoldProjs) {
        $q->addWhere('project_active = 1');
    }
    if (!$showDynTasks) {
        $q->addWhere('task_dynamic <> 1');
    }
    if ($showPinned) {
        $q->addWhere('task_pinned = 1');
    }

    $q->addGroup('t.task_id');
    $q->addOrder('t.task_end_date, t.task_priority DESC');
    $q = $task->setAllowedSQL($AppUI->user_id, $q);
    $proTasks = $q->loadHashList('task_id');

    return $proTasks;
}


/**
 * @param $project_id
 * @param $f
 * @param $AppUI
 * @param $task
 *
 * @return array
 */
function __extract_from_gantt_pdf4($project_id, $f, $AppUI, $task)
{
// pull tasks
    $q = new w2p_Database_Query();
    $q->addTable('tasks', 't');
    $q->addQuery('t.task_id, task_parent, task_name, task_start_date, task_end_date,' .
    ' task_duration, task_duration_type, task_priority, task_percent_complete,' .
    ' task_hours_worked, task_order, task_project, task_milestone, task_access,' .
    ' task_owner, project_name, project_color_identifier, task_dynamic');
    $q->addJoin('projects', 'p', 'project_id = t.task_project', 'inner');
    $q->addOrder('p.project_id, t.task_end_date');

    if ($project_id) {
        $q->addWhere('task_project = ' . (int) $project_id);
    }

    switch ($f) {
        case 'all':
            $q->addWhere('task_status > -1');
            break;
        case 'myproj':
            $q->addWhere('task_status > -1');
            $q->addWhere('project_owner = ' . (int) $AppUI->user_id);
            break;
        case 'mycomp':
            $q->addWhere('task_status > -1');
            $q->addWhere('project_company = ' . (int) $AppUI->user_company);
            break;
        case 'myinact':
            $q->innerJoin('user_tasks', 'ut', 'ut.task_id = t.task_id');

            $q->addWhere('ut.user_id = ' . $AppUI->user_id);
            break;
        default:
            $q->innerJoin('user_tasks', 'ut', 'ut.task_id = t.task_id');


            $q->addWhere('ut.user_id = ' . (int) $AppUI->user_id);
            break;
    }
    $q = $task->setAllowedSQL($AppUI->user_id, $q);
    $proTasks = $q->loadHashList('task_id');

    return $proTasks;
}

/**
 * @param $department
 *
 * @return array
 */
function __extract_from_projects_gantt($department)
{
    $q = new w2p_Database_Query;
    $q->addTable('users');
    $q->addQuery('user_id');
    $q->addJoin('contacts', 'c', 'c.contact_id = user_contact', 'inner');
    $q->addWhere('c.contact_department = ' . (int) $department);
    $owner_ids = $q->loadColumn();

    return $owner_ids;
}

/**
 * @param $department
 * @param $addPwOiD
 * @param $project_type
 * @param $owner
 * @param $statusFilter
 * @param $company_id
 * @param $owner_ids
 * @param $showInactive
 * @param $AppUI
 * @param $pjobj
 *
 * @return mixed
 */
function __extract_from_projects_gantt2($department, $addPwOiD, $project_type, $owner, $statusFilter, $company_id, $owner_ids, $showInactive, $AppUI, $pjobj)
{
// pull valid projects and their percent complete information
    $q = new w2p_Database_Query;
    $q->addTable('projects', 'pr');
    $q->addQuery('DISTINCT pr.project_id, project_color_identifier, project_name, project_start_date, project_end_date,
                max(t1.task_end_date) AS project_actual_end_date, project_percent_complete,
                project_status, project_active');
    $q->addJoin('tasks', 't1', 'pr.project_id = t1.task_project');
    $q->addJoin('companies', 'c1', 'pr.project_company = c1.company_id');
    if ($department > 0 && !$addPwOiD) {
        $q->addWhere('project_departments.department_id = ' . (int) $department);
    }
    if ($project_type > -1) {
        $q->addWhere('pr.project_type = ' . (int) $project_type);
    }
    if ($owner > 0) {
        $q->addWhere('pr.project_owner = ' . (int) $owner);
    }
    if ($statusFilter > -1) {
        $q->addWhere('pr.project_status = ' . (int) $statusFilter);
    }
    if (!($department > 0) && $company_id > 0 && !$addPwOiD) {
        $q->addWhere('pr.project_company = ' . (int) $company_id);
    }
// Show Projects where the Project Owner is in the given department
    if ($addPwOiD && !empty($owner_ids)) {
        $q->addWhere('pr.project_owner IN (' . implode(',', $owner_ids) . ')');
    }

    if ($showInactive != '1') {
        $q->addWhere('pr.project_active = 1');
        if (($template_status = w2PgetConfig('template_projects_status_id')) != '') {
            $q->addWhere('pr.project_status <> ' . $template_status);
        }
    }
    $search_text = $AppUI->getState('projsearchtext') !== null ? $AppUI->getState('projsearchtext') : '';
    if (mb_trim($search_text)) {
        $q->addWhere('pr.project_name LIKE \'%' . $search_text . '%\' OR pr.project_description LIKE \'%' . $search_text . '%\'');
    }
    $q = $pjobj->setAllowedSQL($AppUI->user_id, $q, null, 'pr');
    $q->addGroup('pr.project_id');
    $q->addOrder('pr.project_name, task_end_date DESC');

    $projects = $q->loadList();

    return $projects;
}

/**
 * @param $department
 * @param $company_id
 * @param $original_project_id
 * @param $pjobj
 * @param $AppUI
 *
 * @return array
 */
function __extract_from_subprojects_gantt($department, $company_id, $original_project_id, $pjobj, $AppUI)
{
// pull valid projects and their percent complete information
// GJB: Note that we have to special case duration type 24 and this refers to the hours in a day, NOT 24 hours

    $q = new w2p_Database_Query;
    $q->addTable('projects', 'pr');
    $q->addQuery('DISTINCT pr.project_id, project_color_identifier, project_name, project_start_date, project_end_date,
                max(t1.task_end_date) AS project_actual_end_date, project_percent_complete, project_status, project_active');
    $q->addJoin('tasks', 't1', 'pr.project_id = t1.task_project');
    $q->addJoin('companies', 'c1', 'pr.project_company = c1.company_id');
    if ($department > 0) {
        $q->addWhere('project_departments.department_id = ' . (int) $department);
    }

    if (!($department > 0) && $company_id != 0) {
        $q->addWhere('project_company = ' . (int) $company_id);
    }

    $q->addWhere('project_original_parent = ' . (int) $original_project_id);

    $q = $pjobj->setAllowedSQL($AppUI->user_id, $q, null, 'pr');
    $q->addGroup('pr.project_id');
    $q->addOrder('project_start_date, project_end_date, project_name');

    $projects = $q->loadHashList('project_id');

    return $projects;
}

/**
 * @param $original_project_id
 * @param $task
 * @param $AppUI
 *
 * @return array
 */
function __extract_from_subprojects_gantt2($original_project_id, $task, $AppUI)
{
// insert tasks into Gantt Chart
    // select for tasks for each project
    // pull tasks
    $q = new w2p_Database_Query;
    $q->addTable('tasks', 't');
    $q->addQuery('t.task_id, task_parent, task_name, task_start_date, task_end_date, task_duration, task_duration_type, task_priority, task_percent_complete, task_order, task_project, task_milestone, project_id, project_name, task_dynamic');
    $q->addJoin('projects', 'p', 'project_id = t.task_project');
    $q->addOrder('project_id, task_start_date');
    $q->addWhere('project_original_parent = ' . (int) $original_project_id);
    $q = $task->setAllowedSQL($AppUI->user_id, $q);
    $proTasks = $q->loadHashList('task_id');

    return $proTasks;
}

/**
 * @param $AppUI
 *
 * @return array
 */
function __extract_from_projectdesigner1($AppUI)
{
//Lets load the users panel viewing options
    $q = new w2p_Database_Query;
    $q->addTable('project_designer_options', 'pdo');
    $q->addQuery('pdo.*');
    $q->addWhere('pdo.pd_option_user = ' . (int) $AppUI->user_id);
    $view_options = $q->loadList();

    return $view_options;
}

/**
 * @return array
 */
function __extract_from_projectdesigner2()
{
    $q = new w2p_Database_Query;
    $q->addTable('projects');
    $q->addQuery('projects.project_id, company_name');
    $q->addJoin('companies', 'co', 'co.company_id = project_company');
    $idx_companies = $q->loadHashList();

    return $idx_companies;
}

/**
 * @param $controller
 */
function __extract_from_contact_controller($controller)
{
    $updatekey = $controller->object->getUpdateKey();
    $notifyasked = w2PgetParam($_POST, 'contact_updateask', 0);
    if ($notifyasked && !strlen($updatekey)) {
        $rnow = new w2p_Utilities_Date();
        $controller->object->contact_updatekey = MD5($rnow->format(FMT_DATEISO));
        $controller->object->contact_updateasked = $rnow->format(FMT_DATETIME_MYSQL);
        $controller->object->contact_lastupdate = '';
        $controller->object->store();
        $controller->object->notify();
    }

    return $controller;
}

/**
 * @param $row
 *
 * @return Array
 */
function __extract_from_vw_usr($row)
{
    $q = new w2p_Database_Query;
    $q->addTable('user_access_log', 'ual');
    $q->addQuery('user_access_log_id, ( unix_timestamp( \'' . $q->dbfnNowWithTZ() . '\' ) - unix_timestamp( date_time_in ) ) / 3600 as 		hours, ( unix_timestamp( \'' . $q->dbfnNowWithTZ() . '\' ) - unix_timestamp( date_time_last_action ) ) / 3600 as idle, if(isnull(date_time_out) or date_time_out =\'0000-00-00 00:00:00\',\'1\',\'0\') as online');
    $q->addWhere('user_id = ' . (int) $row['user_id']);
    $q->addOrder('user_access_log_id DESC');
    $q->setLimit(1);
    $user_logs = $q->loadList();

    return $user_logs;
}

/**
 * @param $module
 * @param $mod_data
 *
 * @return Value
 */
function __extract_from_vw_usr_perms($module, $mod_data)
{
    $q = new w2p_Database_Query();
    $q->addTable($module['permissions_item_table']);
    $q->addQuery($module['permissions_item_label']);
    $q->addWhere($module['permissions_item_field'] . '=' . $mod_data['value']);
    $data = $q->loadResult();

    return $data;
}

/**
 * @param $orderby
 *
 * @return Array
 */
function __extract_from_vw_usr_sessions($orderby)
{
    $q = new w2p_Database_Query;
    $q->addTable('sessions', 's');
    $q->addQuery('DISTINCT(session_id), user_access_log_id, u.user_id, u.user_id as u_user_id,
    user_username, contact_last_name, contact_display_name, contact_first_name,
    company_name, contact_company, date_time_in, user_ip');

    $q->addJoin('user_access_log', 'ual', 'session_user = user_access_log_id');
    $q->addJoin('users', 'u', 'ual.user_id = u.user_id');
    $q->addJoin('contacts', 'con', 'u.user_contact = contact_id');
    $q->addJoin('companies', 'com', 'contact_company = company_id');
    $q->addOrder($orderby);
    $rows = $q->loadList();

    return $rows;
}

/**
 * @param $row
 *
 * @return array
 */
function __extract_from_forums_view_messages($row)
{
    $q = new w2p_Database_Query;
    $q->addTable('forum_messages');
    $q->addTable('users');
    $q->addQuery('DISTINCT contact_first_name, contact_last_name, contact_display_name as contact_name, user_username, contact_email');
    $q->addJoin('contacts', 'con', 'contact_id = user_contact', 'inner');
    $q->addWhere('users.user_id = ' . (int) $row['message_editor']);
    $editor = $q->loadList();

    return $editor;
}

// Now we need to update the forum visits with the new messages so they don't show again.
/**
 * @param $AppUI
 * @param $forum_id
 * @param $msg_id
 * @param $date
 */
function __extract_from_forums_view_messages2($AppUI, $forum_id, $msg_id, $date)
{
    $q = new w2p_Database_Query;
    $q->addTable('forum_visits');
    $q->addInsert('visit_user', $AppUI->user_id);
    $q->addInsert('visit_forum', $forum_id);
    $q->addInsert('visit_message', $msg_id);
    $q->addInsert('visit_date', $date->getDate());
    $q->exec();
}

/**
 * @param $AppUI
 * @param $forum_id
 * @param $f
 * @param $orderby
 * @param $orderdir
 *
 * @return Array
 */
function __extract_from_forums_view_topics($AppUI, $forum_id, $f, $orderby, $orderdir)
{
//Pull All Messages
    $q = new w2p_Database_Query;
    $q->addTable('forum_messages', 'fm1');
    $q->addQuery('fm1.*, u.*, fm1.message_title as message_name, fm1.message_forum as forum_id');
    $q->addQuery('COUNT(distinct fm2.message_id) AS replies');
    $q->addQuery('MAX(fm2.message_date) AS latest_reply');
    $q->addQuery('user_username, contact_first_name, contact_last_name, contact_display_name as contact_name, watch_user');
    $q->addQuery('count(distinct v1.visit_message) as reply_visits');
    $q->addQuery('v1.visit_user');
    $q->leftJoin('users', 'u', 'fm1.message_author = u.user_id');
    $q->leftJoin('contacts', 'con', 'contact_id = user_contact');
    $q->leftJoin('forum_messages', 'fm2', 'fm1.message_id = fm2.message_parent');
    $q->leftJoin('forum_watch', 'fw', 'watch_user = ' . (int) $AppUI->user_id . ' AND watch_topic = fm1.message_id');
    $q->leftJoin('forum_visits', 'v1', 'v1.visit_user = ' . (int) $AppUI->user_id . ' AND v1.visit_message = fm1.message_id');
    $q->addWhere('fm1.message_forum = ' . (int) $forum_id);
    $q->addWhere('fm1.message_parent < 1');

    switch ($f) {
        case 1:
            $q->addWhere('watch_user IS NOT NULL');
            break;
        case 2:
            $q->addWhere('(NOW() < DATE_ADD(fm2.message_date, INTERVAL 30 DAY) OR NOW() < DATE_ADD(fm1.message_date, INTERVAL 30 DAY))');
            break;
    }
    $q->addGroup('fm1.message_id, fm1.message_parent');
    $q->addOrder($orderby . ' ' . $orderdir);
    $items = $q->loadList();

    return $items;
}

/**
 * @param $row
 *
 * @return Array
 */
function __extract_from_tasks2($row)
{
    $q = new w2p_Database_Query;
    $q->addQuery('ut.user_id,	u.user_username, ut.user_task_priority');
    $q->addQuery('ut.perc_assignment');
    $q->addQuery('contact_display_name AS assignee, contact_email');
    $q->addTable('user_tasks', 'ut');
    $q->addJoin('users', 'u', 'u.user_id = ut.user_id', 'inner');
    $q->addJoin('contacts', 'c', 'u.user_contact = c.contact_id', 'inner');
    $q->addWhere('ut.task_id = ' . (int) $row['task_id']);
    $q->addOrder('perc_assignment desc, contact_first_name, contact_last_name');
    $assigned_users = $q->loadList();

    return $assigned_users;
}

/**
 * @return array
 */
function __extract_from_vw_actions()
{
    $q = new w2p_Database_Query;
    $q->addTable('projects');
    $q->addQuery('projects.project_id, company_name');
    $q->addJoin('companies', 'co', 'co.company_id = project_company');

    return $q->loadHashList();
}

/**
 * @param $f
 * @param $q
 * @param $user_id
 * @param $task_id
 * @param $AppUI
 * @return string
 */
function __extract_from_tasks3($f, $q, $user_id, $task_id, $AppUI)
{
    $f = (($f) ? $f : '');
    if ($task_id) {
        //if we are on a task context make sure we show ALL the children tasks
        $f = 'deepchildren';
    }

    switch ($f) {
        case 'myfinished7days':
            $q->addWhere('ut.user_id = ' . (int) $user_id);
        case 'allfinished7days':
            $q->addTable('user_tasks');
            $q->addWhere('user_tasks.user_id = ' . (int) $user_id);
            $q->addWhere('user_tasks.task_id = tasks.task_id');

            $q->addWhere('task_percent_complete = 100');
            //TODO: use date class to construct date.
            $q->addWhere('task_end_date >= \'' . date('Y-m-d 00:00:00', mktime(0, 0, 0, date('m'), date('d') - 7, date('Y'))) . '\'');
            break;
        case 'children':
            $q->addWhere('task_parent = ' . (int) $task_id);
            $q->addWhere('tasks.task_id <> ' . $task_id);
            break;
        case 'deepchildren':
            $taskobj = new CTask;
            $taskobj->load((int) $task_id);
            $deepchildren = $taskobj->getDeepChildren();
            $q->addWhere('tasks.task_id IN (' . implode(',', $deepchildren) . ')');
            $q->addWhere('tasks.task_id <> ' . $task_id);
            break;
        case 'myproj':
            $q->addWhere('project_owner = ' . (int) $user_id);
            break;
        case 'mycomp':
            if (!$AppUI->user_company) {
                $AppUI->user_company = 0;
            }
            $q->addWhere('project_company = ' . (int) $AppUI->user_company);
            break;
        case 'myunfinished':
            $q->addTable('user_tasks');
            $q->addWhere('user_tasks.user_id = ' . (int) $user_id);
            $q->addWhere('user_tasks.task_id = tasks.task_id');
            $q->addWhere('(task_percent_complete < 100 OR task_end_date = \'\')');
            break;
        case 'allunfinished':
            $q->addWhere('(task_percent_complete < 100 OR task_end_date = \'\')');
            break;
        case 'unassigned':
            $q->leftJoin('user_tasks', 'ut_empty', 'tasks.task_id = ut_empty.task_id');
            $q->addWhere('ut_empty.task_id IS NULL');
            break;
        case 'taskcreated':
            $q->addWhere('task_creator = ' . (int) $user_id);
            break;
        case 'taskowned':
            $q->addWhere('task_owner = ' . (int) $user_id);
            break;
        case 'all':
            //break;
        default:
            if ($user_id) {
                $q->addTable('user_tasks');
                $q->addWhere('user_tasks.user_id = ' . (int) $user_id);
                $q->addWhere('user_tasks.task_id = tasks.task_id');
            }
            break;
    }

    return $q;
}

/**
 * @param $min_view
 * @param $currentTabId
 * @param $project_id
 * @param $currentTabName
 * @param $AppUI
 * @return int
 */
function __extract_from_tasks($min_view, $currentTabId, $project_id, $currentTabName, $AppUI)
{
//TODO: This whole structure is hard-coded based on the TaskStatus SelectList.
    $task_status = 0;
    if ($min_view && isset($_GET['task_status'])) {
        $task_status = (int)w2PgetParam($_GET, 'task_status', null);
        return $task_status;
    } elseif ($currentTabId == 1 && $project_id) {
        $task_status = -1;
        return $task_status;
    } elseif ($currentTabId > 1 && $project_id) {
        $task_status = $currentTabId - 1;
        return $task_status;
    } elseif (!$currentTabName) {
        // If we aren't tabbed we are in the tasks list.
        $task_status = (int)$AppUI->getState('inactive');
        return $task_status;
    }
    return $task_status;
}

/**
 * @param $where_list
 * @param $project_id
 * @param $task_id
 * @return Array
 */
function __extract_from_tasks4($where_list, $project_id, $task_id)
{
    $q = new w2p_Database_Query;
    $q->addTable('projects', 'p');
    $q->addQuery('company_name, p.project_id, project_color_identifier, project_name, project_percent_complete, project_task_count');
    $q->addJoin('companies', 'com', 'company_id = project_company', 'inner');
    $q->addJoin('tasks', 't1', 'p.project_id = t1.task_project', 'inner');
    $q->leftJoin('project_departments', 'project_departments', 'p.project_id = project_departments.project_id OR project_departments.project_id IS NULL');
    $q->leftJoin('departments', 'departments', 'departments.dept_id = project_departments.department_id OR dept_id IS NULL');
    $q->addWhere($where_list . (($where_list) ? ' AND ' : '') . 't1.task_id = t1.task_parent');
    $q->addGroup('p.project_id');
    if (!$project_id && !$task_id) {
        $q->addOrder('project_name');
    }
    if ($project_id > 0) {
        $q->addWhere('p.project_id = ' . $project_id);
    }

    $projects = $q->loadList(-1, 'project_id');
    return $projects;
}

/**
 * @param $q
 * @param $subquery
 */
function __extract_from_tasks5($q, $subquery)
{
    $q->addQuery('tasks.task_id, task_parent, task_name');
    $q->addQuery('task_start_date, task_end_date, task_dynamic');
    $q->addQuery('task_pinned, pin.user_id as pin_user');
    $q->addQuery('ut.user_task_priority');
    $q->addQuery('task_priority, task_percent_complete');
    $q->addQuery('task_duration, task_duration_type');
    $q->addQuery('task_project, task_represents_project');
    $q->addQuery('task_description, task_owner, task_status');
    $q->addQuery('usernames.user_username, usernames.user_id');
    $q->addQuery('assignees.user_username as assignee_username');
    $q->addQuery('count(distinct assignees.user_id) as assignee_count');
    $q->addQuery('co.contact_first_name, co.contact_last_name');
    $q->addQuery('contact_display_name AS contact_name');
    $q->addQuery('contact_display_name AS owner');
    $q->addQuery('task_milestone');
    $q->addQuery('count(distinct f.file_task) as file_count');
    $q->addQuery('tlog.task_log_problem');
    $q->addQuery('task_access');
    $q->addQuery('(' . $subquery . ') AS task_nr_of_children');
    $q->addTable('tasks');

    return $q;
}

/**
 * @param $AppUI
 * @param $task_id
 */
function __extract_from_tasks_pinning($AppUI, $task_id)
{
    if (isset($_GET['pin'])) {
        $pin = (int)w2PgetParam($_GET, 'pin', 0);

        $task = new CTask();
        // load the record data
        if (1 == $pin) {
            $result = $task->pinTask($AppUI->user_id, $task_id);
        }
        if (-1 == $pin) {
            $result = $task->unpinTask($AppUI->user_id, $task_id);
        }

        if (!$result) {
            $AppUI->setMsg('Pinning ', UI_MSG_ERROR, true);
        }
		$task->load($task_id);
        $AppUI->redirect('m=projects&a=view&project_id='.$task->task_project, -1);
    }
}

/**
 * Check if start date exists, if not try giving it the end date. If the end date does not exist then set it for
 *   today. This avoids jpgraphs internal errors that render the gantt completely useless
 *
 * @param $row
 * @return array
 */
function __extract_from_projects_gantt3($row)
{
    if ($row['task_start_date'] == '0000-00-00 00:00:00') {
        if ($row['task_end_date'] == '0000-00-00 00:00:00') {
            $todaydate = new w2p_Utilities_Date();
            $row['task_start_date'] = $todaydate->format(FMT_TIMESTAMP_DATE);
        } else {
            $row['task_start_date'] = $row['task_end_date'];
        }
    }
    return $row['task_start_date'];
}

/**
 * Check if end date exists, if not try giving it the start date. If the start date does not exist then set it for
 *   today. This avoids jpgraphs internal errors that render the gantt completely useless
 *
 * @param $row
 * @return array
 */
function __extract_from_projects_gantt4($row)
{
    if ($row['task_end_date'] == '0000-00-00 00:00:00') {
        if ($row['task_duration']) {
            $date = new w2p_Utilities_Date($row['task_start_date']);
            $date->addDuration($row['task_duration'], $row['task_duration_type']);
        } else {
            $date = new w2p_Utilities_Date();
        }

        $row['task_end_date'] = $date->format(FMT_TIMESTAMP_DATE);
    }

    return $row['task_end_date'];
}

/**
 * @param $s
 * @param $style
 * @param $row
 * @param $editor
 * @param $AppUI
 * @param $bbparser
 * @return array
 */
function __extract_from_view_messages1($s, $style, $row, $editor, $AppUI, $bbparser)
{
    $s .= "<tr>";

    $s .= '<td valign="top" style="' . $style . '" >';
    $s .= '<a href="mailto:' . $row['contact_email'] . '">';
    $s .= $row['contact_name'] . '</a>';
    if (sizeof($editor) > 0) {
        $s .= '<br/>&nbsp;<br/>' . $AppUI->_('last edited by');
        $s .= ':<br/><a href="mailto:' . $editor[0]['contact_email'] . '">';
        $s .= '<font size="1">' . $editor[0]['contact_name'] . '</font></a>';
    }
    $s .= '<a name="' . $row['message_id'] . '" href="javascript: void(0);" onclick="toggle(' . $row['message_id'] . ')">';
    $s .= '<span size="2"><strong>' . $row['message_title'] . '</strong></span></a>';
    $s .= '<div class="message" id="' . $row['message_id'] . '" style="display: none">';
    $row['message_body'] = $bbparser->qparse($row['message_body']);
    $s .= $row['message_body'];
    $s .= '</div></td>';

    $s .= '</tr>';
    return $s;
}

/**
 * @param $s
 * @param $style
 * @param $AppUI
 * @param $row
 * @param $df
 * @param $tf
 * @param $editor
 * @param $side
 * @param $bbparser
 * @param $first
 * @param $messages
 * @return array
 */
function __extract_from_view_messages3($s, $style, $AppUI, $row, $df, $tf, $editor, $side, $bbparser, $first, $messages)
{
    $s .= '<tr>';

    $s .= '<td valign="top" style="' . $style . '">';
    $s .= $AppUI->formatTZAwareTime($row['message_date'], $df . ' ' . $tf) . ' - ';
    $s .= '<a href="mailto:' . $row['contact_email'] . '">' . $row['contact_name'] . '</a>';
    $s .= '<br />';
    if (sizeof($editor) > 0) {
        $s .= '<br/>&nbsp;<br/>' . $AppUI->_('last edited by');
        $s .= ':<br/><a href="mailto:' . $editor[0]['contact_email'] . '">';
        $s .= '<font size="1">' . $editor[0]['contact_name'] . '</font></a>';
    }
    $s .= '<a href="javascript: void(0);" onclick="toggle(' . $row['message_id'] . ')">';
    $s .= '<span size="2"><strong>' . $row['message_title'] . '</strong></span></a>';
    $side .= '<div class="message" id="' . $row['message_id'] . '" style="display: none">';
    $side .= $row['message_body'];
    $side .= '</div>';
    $row['message_body'] = $bbparser->qparse($row['message_body']);
    $s .= '</td>';
    if ($first) {
        $s .= '<td rowspan="' . count($messages) . '" valign="top">';
        echo $s;
        $s = '';
    }
    $s .= '</tr>';
    return array($s, $side);
}


/**
 * @param $s
 * @param $style
 * @param $row
 * @param $hideEmail
 * @param $editor
 * @param $AppUI
 * @param $new_messages
 * @param $bbparser
 * @param $m
 * @param $df
 * @param $tf
 * @param $canEdit
 * @param $canAdminEdit
 * @param $canDelete
 * @return array
 */
function __extract_from_view_messages4($s, $style, $row, $hideEmail, $editor, $AppUI, $new_messages, $bbparser, $m, $df, $tf, $canEdit, $canAdminEdit, $canDelete)
{
    $s .= '<tr>';

    $s .= '<td valign="top" style="' . $style . '" nowrap="nowrap">';
    $s .= '<a href="?m=users&a=view&user_id=' . $row['message_author'] . '">';
    $s .= $row['contact_name'];
    $s .= '</a>';
    if (!$hideEmail) {
        $s .= '&nbsp;';
        $s .= '<a href="mailto:' . $row['contact_email'] . '">';
        $s .= '<img src="' . w2PfindImage('email.gif') . '" alt="email" />';
        $s .= '</a>';
    }

    if (sizeof($editor) > 0) {
        $s .= '<br/>&nbsp;<br/>' . $AppUI->_('last edited by');
        $s .= ':<br/>';
        if (!$hideEmail) {
            $s .= '<a href="mailto:' . $editor[0]['contact_email'] . '">';
        }
        $s .= $editor[0]['contact_name'];
        if (!$hideEmail) {
            $s .= '</a>';
        }
    }
    if ($row['visit_user'] != $AppUI->user_id) {
        $s .= '<br />&nbsp;' . w2PshowImage('icons/stock_new_small.png');
        $new_messages[] = $row['message_id'];
    }
    $s .= '</td>';
    $s .= '<td valign="top" style="' . $style . '">';
    $s .= '<strong>' . $row['message_title'] . '</strong><hr size=1>';
    $row['message_body'] = $bbparser->qparse($row['message_body']);
    $row['message_body'] = nl2br($row['message_body']);
    $s .= $row['message_body'];
    $s .= '</td>';

    $s .= '</tr><tr>';

    $s .= '<td valign="top" style="' . $style . '" nowrap="nowrap">';
    $s .= '<img src="' . w2PfindImage('icons/posticon.gif', $m) . '" alt="date posted" />' . $AppUI->formatTZAwareTime($row['message_date'], $df . ' ' . $tf) . '</td>';
    $s .= '<td valign="top" align="right" style="' . $style . '">';

    // in some weird permission cases
    // it can happen that the table gets opened but never closed,
    // or the other way around, thus breaking the layout
    // introducing these variables to help us out with proper
    // table tag opening and closing.
    $tableOpened = false;
    $tableClosed = false;
    //the following users are allowed to edit/delete a forum message: 1. the forum creator  2. a superuser with read-write access to 'all' 3. the message author
    if ($canEdit || $AppUI->user_id == $row['forum_moderated'] || $AppUI->user_id == $row['message_author'] || $canAdminEdit) {
        $tableOpened = true;
        $s .= '<table cellspacing="0" cellpadding="0" border="0"><tr>';
        // edit message
        $s .= '<td><a href="./index.php?m=forums&a=viewer&post_message=1&forum_id=' . $row['message_forum'] . '&message_parent=' . $row['message_parent'] . '&message_id=' . $row["message_id"] . '" title="' . $AppUI->_('Edit') . ' ' . $AppUI->_('Message') . '">';
        $s .= w2PshowImage('icons/stock_edit-16.png', '16', '16');
        $s .= '</td>';
    }
    if ($canDelete || $AppUI->user_id == $row['forum_moderated'] || $AppUI->user_id == $row['message_author'] || $canAdminEdit) {
        $tableClosed = true;
        if (!$tableOpened) {
            $s .= '<table cellspacing="0" cellpadding="0" border="0"><tr>';
        }
        // delete message
        $s .= '<td><a href="javascript:delIt(' . $row['message_id'] . ')" title="' . $AppUI->_('delete') . '">';
        $s .= w2PshowImage('icons/stock_delete-16.png', '16', '16');
        $s .= '</a>';
        $s .= '</td></tr></table>';
    }

    if ($tableOpened and !$tableClosed) {
        $s .= '</tr></table>';
    }

    $s .= '</td>';
    $s .= '</tr>';
    return array($s, $new_messages);
}
