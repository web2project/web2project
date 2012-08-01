<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
/*
* This file exists in order to list individual functions which need to be
*   cleaned up, reorganized or eliminated based on usage. Before you touch
*   these, please ensure there are Unit Tests to validate that things work
*   before and after.
*
*
* WARNING: The functions in this file are likely to move to other files as they
*   are updated. Since this file is included within main_functions.php
*   this shouldn't be a problem.
*/


//There is an issue with international UTF characters, when stored in the database an accented letter
//actually takes up two letters per say in the field length, this is a problem with costcodes since
//they are limited in size so saving a costcode as REDACI�N would actually save REDACI� since the accent takes
//two characters, so lets unaccent them, other languages should add to the replacements array too...
function cleanText($text) {
	//This text file is not utf, its iso so we have to decode/encode
	$text = utf8_decode($text);
	$trade = array('�' => 'a', '�' => 'a', '�' => 'a', '�' => 'a', '�' => 'a', '�' => 'A', '�' => 'A', '�' => 'A', '�' => 'A', '�' => 'A', '�' => 'e', '�' => 'e', '�' => 'e', '�' => 'e', '�' => 'E', '�' => 'E', '�' => 'E', '�' => 'E', '�' => 'i', '�' => 'i', '�' => 'i', '�' => 'i', '�' => 'I', '�' => 'I', '�' => 'I', '�' => 'I', '�' => 'o', '�' => 'o', '�' => 'o', '�' => 'o', '�' => 'o', '�' => 'O', '�' => 'O', '�' => 'O', '�' => 'O', '�' => 'O', '�' => 'u', '�' => 'u', '�' => 'u', '�' => 'u', '�' => 'U', '�' => 'U', '�' => 'U', '�' => 'U', '�' => 'N', '�' => 'n');
	$text = strtr($text, $trade);
	$text = utf8_encode($text);

	return $text;
}

function is_task_in_gantt_arr($task) {
    global $gantt_arr;
    $n = count($gantt_arr);
    for ($x = 0; $x < $n; $x++) {
        if ($gantt_arr[$x][0]['task_id'] == $task['task_id']) {
            return true;
        }
    }
    return false;
}

function notifyHR($address, $username, $uaddress, $uusername, $logname, $logpwd, $userid) {
	global $AppUI;
	$mail = new w2p_Utilities_Mail();
	if ($mail->ValidEmail($address)) {
//TODO: why aren't we actually using this $email variable?
        if ($mail->ValidEmail($AppUI->user_email)) {
			$email = $AppUI->user_email;
		} else {
//TODO: this email should be set to something sane
            $email = 'web2project@web2project.net';
		}

		$mail->To($address);
        $emailManager = new w2p_Output_EmailManager($AppUI);
        $body = $emailManager->notifyHR($uusername, $logname, $uaddress, $userid);
		$mail->Subject('New External User Created');
		$mail->Body($body);
		$mail->Send();
	}
}

function notifyNewUserCredentials($address, $username, $logname, $logpwd) {
	global $AppUI, $w2Pconfig;
	$mail = new w2p_Utilities_Mail();
	if ($mail->ValidEmail($address)) {
//TODO: why aren't we actually using this $email variable?
        if ($mail->ValidEmail($AppUI->user_email)) {
			$email = $AppUI->user_email;
		} else {
//TODO: this email should be set to something sane
            $email = "web2project@" . $AppUI->cfg['site_domain'];
		}

		$mail->To($address);
        $emailManager = new w2p_Output_EmailManager($AppUI);
        $body = $emailManager->notifyNewUserCredentials($username, $logname, $logpwd);
		$mail->Subject('New Account Created - web2Project Project Management System');
		$mail->Body($body);
		$mail->Send();
	}
}

function clean_value($str) {
    $bad_values = array("'");
    return str_replace($bad_values, '', $str);
}


function strUTF8Decode($text) {
	global $locale_char_set;
	if (extension_loaded('mbstring')) {
		$encoding = mb_detect_encoding($text.' ');
	}
	if (function_exists('iconv')){
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
function strEzPdf($text) {
    global $locale_char_set;
    if (function_exists('iconv') && function_exists('mb_detect_encoding')) {
        $text = iconv(mb_detect_encoding($text." "), 'UTF-8', $text);
        return $text;
    } else {
        return $text;
    }
}

/*
* 	smart_slice : recursive function used to slice the task array whlie
* 	minimizing the potential number of task dependencies between two sub_arrays
* 	Each sub_array is LENGTH elements long maximum
* 	It is shorter if
* 		- either a dynamic task is between indices LENGTH-3 and LENGTH-1 : in this
* 		  case, the milestone is EXCLUDED from the lower sub_array
* 		- or a milestone a MILESTONE is between indices LENGTH-2 and LENGTH-1 : in
* 		  this case the milestone is INCLUDED in the lower sub_array
*/
function smart_slice( $arr, $showNoMilestones, $printpdfhr, $day_diff ) {
    global $gtask_sliced;

    $length = ($showNoMilestones) ? 26 : 25;
    if ($day_diff < 90) {
        $length = $length - 2;
    } else if ($day_diff >=90 && $day_diff < 1096) {
        $length = $length;
    } else {
        $length++;
    }

    if ( count($arr) > $length ) {
        $found = 0 ;
        for ( $i = $length-3 ; $i<$length ; $i++ ) {
            if ( $arr[$i][0]['task_dynamic'] != 0 ) {
                $found = $i ;
            }
        }
        if ( !$found ) {
            for ( $i = $length-1 ; $i > $length-3 ; $i-- ) {
                if ( $arr[$i][0]['task_milestone'] != 0 ) {
                    $found = $i ;
                }
            }
            if ( !$found ) {
                if ( $arr[$length][0]['task_milestone'] == 0 ) {
                    $cut = $length ;						// No specific task => standard cut
                } else {
                    $cut = $length - 1 ;					// No orphan milestone
                }
            } else {
                $cut = $found + 1 ;						// include found milestone in lower level array
            }
        } else {
            $cut = $found ;									//include found dynamic task in higher level array
        }
        $gtask_sliced[] = array_slice( $arr, 0, $cut );
        $task_sliced[] = smart_slice( array_slice( $arr, $cut ), $showNoMilestones, $printpdfhr, $day_diff );
    } else {
        $gtask_sliced[] = $arr ;
    }
    return $gtask_sliced ;
}

/**
*
* 	END OF GANTT PDF UTILITY FUNCTIONS
*
*/

/*
*  This is a kludgy mess because of how the arraySelectTree function is used..
*    it expects - nay, demands! - that the first element of the subarray is the
*    id and the third is the parent id. In most cases, that is fine.. in this
*    one we're using the existing ACL-respecting functions and it has additional
*    fields in "improper" places.
*/
function temp_filterArrayForSelectTree($projectData) {

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

// The includes/permissions.php file has been ported here because it held a group of public functions for permission checking.
// And that is so it stays on one place only.
// Permission flags used in the DB

define('PERM_DENY', '0');
define('PERM_EDIT', '-1');
define('PERM_READ', '1');

define('PERM_ALL', '-1');

// TODO: getDeny* should return true/false instead of 1/0

function getReadableModule() {
	global $AppUI;
	$perms = &$AppUI->acl();

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
function checkFlag($flag, $perm_type, $old_flag) {
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
function isAllowed($perm_type, $mod, $item_id = 0) {
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

function getPermission($mod, $perm, $item_id = 0) {
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
		$q->addWhere('task_id = ' . (int)$item_id);
		$project_id = $q->loadResult();
		$result = getPermission('projects', $perm, $project_id);
	}
	return $result;
}

function canView($mod, $item_id = 0) {
	return getPermission($mod, 'view', $item_id);
}
function canEdit($mod, $item_id = 0) {
	return getPermission($mod, 'edit', $item_id);
}
function canAdd($mod, $item_id = 0) {
	return getPermission($mod, 'add', $item_id);
}
function canDelete($mod, $item_id = 0) {
	return getPermission($mod, 'delete', $item_id);
}
function canAccess($mod) {
    return getPermission($mod, 'access');
}

function buildTaskTree($task_data, $depth = 0, $projTasks, $all_tasks, $parents, $task_parent, $task_id) {
    $output = '';

	$projTasks[$task_data['task_id']] = $task_data['task_name'];
    $task_data['task_name'] = mb_strlen($task_data[1]) > 45 ? mb_substr($task_data['task_name'], 0, 45) . '...' : $task_data['task_name'];
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

/*
 * Deprecated in favor of buildTaskTree which doesn't use any globals.
 *
 * @deprecated
 */
function constructTaskTree($task_data, $depth = 0) {
	global $projTasks, $all_tasks, $parents, $task_parent_options, $task_parent, $task_id;

    return buildTaskTree($task_data, $depth, $projTasks, $all_tasks, $parents, $task_parent, $task_id);
}
/*
 * Deprecated in favor of buildTaskTree which doesn't use any globals.
 *
 * @deprecated
 */
function constructTaskTree_pd($task_data, $parents, $all_tasks, $depth = 0) {
	global $projTasks, $all_tasks, $task_parent_options, $task_parent, $task_id;

    return buildTaskTree($task_data, $depth, $projTasks, $all_tasks, $parents, $task_parent, $task_id);
}

// from modules/tasks/addedit.php and modules/projectdesigners/vw_actions.php
function build_date_list(&$date_array, $row) {
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
function cal_work_day_conv($val) {
	global $locale_char_set, $AppUI;
	setlocale(LC_TIME, 'en');
	$wk = Date_Calc::getCalendarWeek(null, null, null, '%a', LOCALE_FIRST_DAY);
	setlocale(LC_ALL, $AppUI->user_lang);

	$day_name = $AppUI->_($wk[($val - LOCALE_FIRST_DAY) % 7]);
	$day_name = utf8_encode($day_name);

	return htmlspecialchars($day_name, ENT_COMPAT, $locale_char_set);
}

// from modules/tasks/tasks.class.php
//This kludgy function echos children tasks as threads
function showtask(&$arr, $level = 0, $is_opened = true, $today_view = false, $hideOpenCloseLink = false, $allowRepeat = false) {
	global $AppUI, $query_string, $durnTypes, $userAlloc, $showEditCheckbox;
	global $m, $a, $history_active, $expanded;

	//Check for Tasks Access
	$canAccess = canTaskAccess($arr['task_id']);
	if (!$canAccess) {
		return (false);
	}

    $htmlHelper = new w2p_Output_HTMLHelper($AppUI);
    $htmlHelper->df .= ' ' . $AppUI->getPref('TIMEFORMAT');
    
    // Reformat time strings to take timezones into account
    $startDateStr = $AppUI->formatTZAwareTime($arr['task_start_date'], '%Y-%m-%d %T');
    $endDateStr = $AppUI->formatTZAwareTime($arr['task_end_date'], '%Y-%m-%d %T');

	$show_all_assignees = w2PgetConfig('show_all_task_assignees', false);

	// prepare coloured highlight of task time information
	$class = w2pFindTaskComplete($arr['task_start_date'], $arr['task_end_date'], $arr['task_percent_complete']);

    $jsTaskId = 'project_' . $arr['task_project'] . '_level-' . $level . '-task_' . $arr['task_id'] . '_';
	if ($expanded) {
		$s = '<tr id="' . $jsTaskId . '" class="'.$class.'">';
	} else {
		$s = '<tr id="' . $jsTaskId . '" class="'.$class.'" ' . (($level > 0 && !($m == 'tasks' && $a == 'view')) ? 'style="display:none"' : '') . '>';
	}
	// edit icon
	$s .= '<td align="center">';
	$canEdit = ($arr['task_represents_project']) ? false : true;
	$canViewLog = true;
	if ($canEdit) {
        $s .= '<a href="?m=tasks&a=addedit&task_id=' . $arr['task_id'] . '">' . w2PtoolTip('edit task', 'click to edit this task') . w2PshowImage('icons/pencil.gif', 12, 12) . w2PendTip() . '</a>' ;
	}
	$s .= '</td>';
	// pinned
	$pin_prefix = $arr['task_pinned'] ? '' : 'un';
	$s .= ('<td><a href="?m=tasks&amp;pin=' . ($arr['task_pinned'] ? 0 : 1) . '&amp;task_id=' . $arr['task_id'] . '">' . w2PtoolTip('Pin', 'pin/unpin task') . '<img src="' . w2PfindImage('icons/' . $pin_prefix . 'pin.gif') . '" border="0" alt="" />' . w2PendTip() . '</a></td>');
	// New Log
	if (isset($arr['task_log_problem']) && $arr['task_log_problem'] > 0) {
		$s .= ('<td valign="middle"><a href="?m=tasks&amp;a=view&amp;task_id=' . $arr['task_id'] . '&amp;tab=0&amp;problem=1">' . w2PshowImage('icons/dialog-warning5.png', 16, 16, 'Problem', 'Problem!') . '</a></td>');
	} elseif ($canViewLog && $arr['task_dynamic'] != 1 && 0 == $arr['task_represents_project']) {
		$s .= ('<td align="center"><a href="?m=tasks&amp;a=view&amp;task_id=' . $arr['task_id'] . '&amp;tab=1">' . w2PtoolTip('Add Log', 'create a new log record against this task') . w2PshowImage('edit_add.png') . w2PendTip() . '</a></td>');
	} else {
		$s .= '<td align="center">' . $AppUI->_('-') . '</td>';
	}
	// percent complete and priority
    $s .= $htmlHelper->createCell('task_percent_complete', $arr['task_percent_complete']);
    $s .= $htmlHelper->createCell('task_priority', $arr['task_priority']);
    $s .= $htmlHelper->createCell('user_task_priority', $arr['user_task_priority']);

	// dots
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
        $s .= '<img src="' . $image . '" width="16" height="12"  border="0" alt="" />';
	}
	if ($arr['task_description']) {
		$s .= w2PtoolTip('Task Description', $arr['task_description'], true);
	}

	$open_link = '<a href="javascript: void(0);"><img onclick="expand_collapse(\'' . $jsTaskId . '\', \'tblProjects\',\'\',' . ($level + 1) . ');" id="' . $jsTaskId . '_collapse" src="' . w2PfindImage('icons/collapse.gif') . '" border="0" align="center" ' . (!$expanded ? 'style="display:none"' : '') . ' alt="" /><img onclick="expand_collapse(\'' . $jsTaskId . '\', \'tblProjects\',\'\',' . ($level + 1) . ');" id="' . $jsTaskId . '_expand" src="' . w2PfindImage('icons/expand.gif') . '" border="0" align="center" ' . ($expanded ? 'style="display:none"' : '') . ' alt="" /></a>';
	if (isset($arr['task_nr_of_children']) && $arr['task_nr_of_children']) {
		$is_parent = true;
	} else {
		$is_parent = false;
	}
	if ($arr['task_milestone'] > 0) {
		$s .= '&nbsp;<a href="./index.php?m=tasks&amp;a=view&amp;task_id=' . $arr['task_id'] . '" ><b>' . $arr['task_name'] . '</b></a> <img src="' . w2PfindImage('icons/milestone.gif') . '" border="0" alt="" /></td>';
	} elseif ($arr['task_dynamic'] == '1' || $is_parent) {
		if (!$today_view) {
			$s .= $open_link;
		}
		if ($arr['task_dynamic'] == '1') {
			$s .= '&nbsp;<a href="./index.php?m=tasks&amp;a=view&amp;task_id=' . $arr['task_id'] . '" ><b><i>' . $arr['task_name'] . '</i></b></a>' . w2PendTip() . '</td>';
		} else {
			$s .= '&nbsp;<a href="./index.php?m=tasks&amp;a=view&amp;task_id=' . $arr['task_id'] . '" >' . $arr['task_name'] . '</a>' . w2PendTip() . '</td>';
		}
	} else {
		$s .= '&nbsp;<a href="./index.php?m=tasks&amp;a=view&amp;task_id=' . $arr['task_id'] . '" >' . $arr['task_name'] . '</a></td>';
	}
	if ($arr['task_description']) {
		$s .= w2PendTip();
	}
	if ($today_view) { // Show the project name
		$s .= ('<td width="50%"><a href="./index.php?m=projects&amp;a=view&amp;project_id=' . $arr['task_project'] . '">' . '<span style="padding:2px;background-color:#' . $arr['project_color_identifier'] . ';color:' . bestColor($arr['project_color_identifier']) . '">' . $arr['project_name'] . '</span>' . '</a></td>');
	} else {
        $s .= $htmlHelper->createCell('task_owner', $arr['owner']);
	}
	if (isset($arr['task_assigned_users']) && count($arr['task_assigned_users'])) {
        $assigned_users = $arr['task_assigned_users'];
        $a_u_tmp_array = array();
		if ($show_all_assignees) {
			$s .= '<td align="center" nowrap="nowrap">';
			foreach ($assigned_users as $val) {
				$a_u_tmp_array[] = ('<a href="?m=admin&amp;a=viewuser&amp;user_id=' . $val['user_id'] . '"' . 'title="' . (w2PgetConfig('check_overallocation') ? $AppUI->_('Extent of Assignment') . ':' . $userAlloc[$val['user_id']]['charge'] . '%; ' . $AppUI->_('Free Capacity') . ':' . $userAlloc[$val['user_id']]['freeCapacity'] . '%' : '') . '">' . $val['assignee'] . ' (' . $val['perc_assignment'] . '%)</a>');
			}
			$s .= join(', <br />', $a_u_tmp_array) . '</td>';
		} else {
			$s .= ('<td align="center" nowrap="nowrap">' . '<a href="?m=admin&amp;a=viewuser&amp;user_id=' . $assigned_users[0]['user_id'] . '" title="' . (w2PgetConfig('check_overallocation') ? $AppUI->_('Extent of Assignment') . ':' . $userAlloc[$assigned_users[0]['user_id']]['charge'] . '%; ' . $AppUI->_('Free Capacity') . ':' . $userAlloc[$assigned_users[0]['user_id']]['freeCapacity'] . '%' : '') . '">' . $assigned_users[0]['assignee'] . ' (' . $assigned_users[0]['perc_assignment'] . '%)</a>');
			if ($arr['assignee_count'] > 1) {
				$s .= (' <a href="javascript: void(0);" onclick="toggle_users(' . "'users_" . $arr['task_id'] . "'" . ');" title="' . join(', ', $a_u_tmp_array) . '">(+' . ($arr['assignee_count'] - 1) . ')</a>' . '<span style="display: none" id="users_' . $arr['task_id'] . '">');
				$a_u_tmp_array[] = $assigned_users[0]['assignee'];
				for ($i = 1, $i_cmp = count($assigned_users); $i < $i_cmp; $i++) {
					$a_u_tmp_array[] = $assigned_users[$i]['assignee'];
					$s .= ('<br /><a href="?m=admin&amp;a=viewuser&amp;user_id=' . $assigned_users[$i]['user_id'] . '" title="' . (w2PgetConfig('check_overallocation') ? $AppUI->_('Extent of Assignment') . ':' . $userAlloc[$assigned_users[$i]['user_id']]['charge'] . '%; ' . $AppUI->_('Free Capacity') . ':' . $userAlloc[$assigned_users[$i]['user_id']]['freeCapacity'] . '%' : '') . '">' . $assigned_users[$i]['assignee'] . ' (' . $assigned_users[$i]['perc_assignment'] . '%)</a>');
				}
				$s .= '</span>';
			}
			$s .= '</td>';
		}
	} elseif (!$today_view) {
		// No users asigned to task
		$s .= '<td align="center">-</td>';
	}
	// duration or milestone
    $s .= $htmlHelper->createCell('task_start_datetime', $arr['task_start_date']);
    $s .= $htmlHelper->createCell('task_duration', $arr['task_duration'] . ' ' . mb_substr($AppUI->_($durnTypes[$arr['task_duration_type']]), 0, 1));
    $s .= $htmlHelper->createCell('task_end_datetime', $arr['task_end_date']);
	if ($today_view) {
        $s .= $htmlHelper->createCell('task_due_in', $arr['task_due_in']);
	} elseif ($history_active) {
        $s .= $htmlHelper->createCell('last_update', $arr['last_update']);
	}

	// Assignment checkbox
	if ($showEditCheckbox) {
		$s .= ('<td align="center">' . '<input type="checkbox" name="selected_task[' . $arr['task_id'] . ']" value="' . $arr['task_id'] . '"/></td>');
	}
	$s .= '</tr>'."\n";
	return $s;
}

//This kludgy function echos children tasks as threads on project designer (_pd)
//TODO: modules/projectdesigner/projectdesigner.class.php
function showtask_pd(&$arr, $level = 0, $today_view = false) {
	global $AppUI, $w2Pconfig, $done, $query_string, $durnTypes, $userAlloc, $showEditCheckbox;
	global $task_access, $task_priority, $PROJDESIGN_CONFIG, $m, $expanded;

	//Check for Tasks Access
	$canAccess = canTaskAccess($arr['task_id']);
	if (!$canAccess) {
		return (false);
	}

    $htmlHelper = new w2p_Output_HTMLHelper($AppUI);
    $htmlHelper->df .= ' ' . $AppUI->getPref('TIMEFORMAT');
    $htmlHelper->stageRowData($arr);

	$types = w2Pgetsysval('TaskType');

	
	$perms = &$AppUI->acl();
	$show_all_assignees = $w2Pconfig['show_all_task_assignees'] ? true : false;

	$done[] = $arr['task_id'];

	// prepare coloured highlight of task time information
    $class = w2pFindTaskComplete($arr['task_start_date'], $arr['task_end_date'], $arr['task_percent_complete']);

	$jsTaskId = 'task_proj_' . $arr['task_project'] . '_level-' . $level . '-task_' . $arr['task_id'] . '_';
	if ($expanded) {
		$s = '<tr id="' . $jsTaskId . '" class="'.$class.'" onclick="select_row(\'selected_task\', \'' . $arr['task_id'] . '\', \'frm_tasks\')">'; // edit icon
	} else {
		$s = '<tr id="' . $jsTaskId . '" class="'.$class.'" onclick="select_row(\'selected_task\', \'' . $arr['task_id'] . '\', \'frm_tasks\')" ' . ($level ? 'style="display:none"' : '') . '>'; // edit icon
	}
	$s .= '<td>';
	$canEdit = ($arr['task_represents_project']) ? false : true;
	$canViewLog = true;
	if ($canEdit) {
		$s .= '<a href="?m=tasks&a=addedit&task_id=' . $arr['task_id'] . '">' . w2PtoolTip('edit tasks panel', 'click to edit this task') . w2PshowImage('icons/pencil.gif', 12, 12) . w2PendTip() . '</a>';
	}
	$s .= '</td>';
	// percent complete and priority
    $s .= $htmlHelper->createCell('task_percent_complete', $arr['task_percent_complete']);
    $s .= $htmlHelper->createCell('task_priority', $arr['task_priority']);
    $s .= $htmlHelper->createCell('user_task_priority', $arr['user_task_priority']);

	// access
	$s .= '<td nowrap="nowrap">';
	$s .= mb_substr($task_access[$arr['task_access']], 0, 3);
	$s .= '</td>';
	// type
	$s .= '<td nowrap="nowrap">';
	$s .= mb_substr($types[$arr['task_type']], 0, 3);
	$s .= '</td>';
	// type
	$s .= '<td nowrap="nowrap">';
	$s .= $arr['queue_id'] ? 'Yes' : '';
	$s .= '</td>';
	// inactive
	$s .= '<td nowrap="nowrap">';
	$s .= $arr['task_status'] == '-1' ? 'Yes' : '';
	$s .= '</td>';
	// add log
	$s .= '<td align="center" nowrap="nowrap">';
	if ($arr['task_dynamic'] != 1 && 0 == $arr['task_represents_project']) {
		$s .= '<a href="?m=tasks&a=view&tab=1&project_id=' . $arr['task_project'] . '&task_id=' . $arr['task_id'] . '">' . w2PtoolTip('tasks', 'add work log to this task') . w2PshowImage('edit_add.png') . w2PendTip() . '</a>';
	}
	$s .= '</td>';

	// dots
    $s .= '<td style="width: ' . (($today_view) ? '20%' : '50%') . '" class="data _name">';
	for ($y = 0; $y < $level; $y++) {
		if ($y + 1 == $level) {
			$image = w2PfindImage('corner-dots.gif', $m);
		} else {
			$image = w2PfindImage('shim.gif', $m);
		}
        $s .= '<img src="' . $image . '" width="16" height="12"  border="0" alt="" />';
	}
	// name link
	if ($arr['task_description']) {
		$s .= w2PtoolTip('Task Description', $arr['task_description'], true);
	}
    $jsTaskId = 'task_proj_' . $arr['task_project'] . '_level-' . $level . '-task_' . $arr['task_id'] . '_';
	$open_link = '<a href="javascript: void(0);"><img onclick="expand_collapse(\'' . $jsTaskId . '\', \'tblProjects\',\'\',' . ($level + 1) . ');" id="' . $jsTaskId . '_collapse" src="' . w2PfindImage('icons/collapse.gif', $m) . '" border="0" align="center" ' . (!$expanded ? 'style="display:none"' : '') . ' alt="" /><img onclick="expand_collapse(\'' . $jsTaskId . '\', \'tblProjects\',\'\',' . ($level + 1) . ');" id="' . $jsTaskId . '_expand" src="' . w2PfindImage('icons/expand.gif', $m) . '" border="0" align="center" ' . ($expanded ? 'style="display:none"' : '') . ' alt="" /></a>';
	$taskObj = new CTask;
	$taskObj->load($arr['task_id']);
	if (count($taskObj->getChildren())) {
		$is_parent = true;
	} else {
		$is_parent = false;
	}
	if ($arr['task_milestone'] > 0) {
		$s .= '&nbsp;<a href="./index.php?m=tasks&a=view&task_id=' . $arr['task_id'] . '" ><b>' . $arr['task_name'] . '</b></a> <img src="' . w2PfindImage('icons/milestone.gif', $m) . '" border="0" alt="" /></td>';
	} elseif ($arr['task_dynamic'] == '1' || $is_parent) {
		$s .= $open_link;
		if ($arr['task_dynamic'] == '1') {
			$s .= '&nbsp;<a href="./index.php?m=tasks&a=view&task_id=' . $arr['task_id'] . '" ><b><i>' . $arr['task_name'] . '</i></b></a></td>';
		} else {
			$s .= '&nbsp;<a href="./index.php?m=tasks&a=view&task_id=' . $arr['task_id'] . '" >' . $arr['task_name'] . '</a></td>';
		}
	} else {
		$s .= '&nbsp;<a href="./index.php?m=tasks&a=view&task_id=' . $arr['task_id'] . '" >' . $arr['task_name'] . '</a></td>';
	}
	if ($arr['task_description']) {
		$s .= w2PendTip();
	}
	// task description
	if ($PROJDESIGN_CONFIG['show_task_descriptions']) {
		$s .= '<td align="justified">' . $arr['task_description'] . '</td>';
	}
	// task owner
    $s .= $htmlHelper->createCell('task_owner', $arr['contact_name']);
    $s .= $htmlHelper->createCell('task_start_datetime', $arr['task_start_date']);
	// duration or milestone
    $s .= $htmlHelper->createCell('task_duration', $arr['task_duration'] . ' ' . mb_substr($AppUI->_($durnTypes[$arr['task_duration_type']]), 0, 1));
    $s .= $htmlHelper->createCell('task_end_datetime', $arr['task_end_date']);
	if (isset($arr['task_assigned_users']) && ($assigned_users = $arr['task_assigned_users'])) {
		$a_u_tmp_array = array();
		if ($show_all_assignees) {
			$s .= '<td align="left">';
			foreach ($assigned_users as $val) {
				$aInfo = '<a href="?m=admin&a=viewuser&user_id=' . $val['user_id'] . '"';
				$aInfo .= 'title="' . (w2PgetConfig('check_overallocation') ? $AppUI->_('Extent of Assignment') . ':' . $userAlloc[$val['user_id']]['charge'] . '%; ' . $AppUI->_('Free Capacity') . ':' . $userAlloc[$val['user_id']]['freeCapacity'] . '%' : '') . '">';
				$aInfo .= $val['contact_name'] . ' (' . $val['perc_assignment'] . '%)</a>';
				$a_u_tmp_array[] = $aInfo;
			}
			$s .= join(', ', $a_u_tmp_array);
			$s .= '</td>';
		} else {
			$s .= '<td align="left" nowrap="nowrap">';
			$s .= '<a href="?m=admin&a=viewuser&user_id=' . $assigned_users[0]['user_id'] . '"';
			$s .= 'title="' . (w2PgetConfig('check_overallocation') ? $AppUI->_('Extent of Assignment') . ':' . $userAlloc[$assigned_users[0]['user_id']]['charge'] . '%; ' . $AppUI->_('Free Capacity') . ':' . $userAlloc[$assigned_users[0]['user_id']]['freeCapacity'] . '%' : '') . '">';
			$s .= $assigned_users[0]['contact_name'] . ' (' . $assigned_users[0]['perc_assignment'] . '%)</a>';
			if ($arr['assignee_count'] > 1) {
				$id = $arr['task_id'];
				$s .= '<a href="javascript: void(0);"  onclick="toggle_users(\'users_' . $id . '\');" title="' . join(', ', $a_u_tmp_array) . '">(+' . ($arr['assignee_count'] - 1) . ')</a>';
				$s .= '<span style="display: none" id="users_' . $id . '">';
				$a_u_tmp_array[] = $assigned_users[0]['user_username'];
				for ($i = 1, $i_cmp = count($assigned_users); $i < $i_cmp; $i++) {
					$a_u_tmp_array[] = $assigned_users[$i]['user_username'];
					$s .= '<br /><a href="?m=admin&a=viewuser&user_id=';
					$s .= $assigned_users[$i]['user_id'] . '" title="' . (w2PgetConfig('check_overallocation') ? $AppUI->_('Extent of Assignment') . ':' . $userAlloc[$assigned_users[$i]['user_id']]['charge'] . '%; ' . $AppUI->_('Free Capacity') . ':' . $userAlloc[$assigned_users[$i]['user_id']]['freeCapacity'] . '%' : '') . '">';
					$s .= $assigned_users[$i]['contact_name'] . ' (' . $assigned_users[$i]['perc_assignment'] . '%)</a>';
				}
				$s .= '</span>';
			}
			$s .= '</td>';
		}
	} else {
		// No users asigned to task
		$s .= '<td align="center">-</td>';
	}

	// Assignment checkbox
	if ($showEditCheckbox && 0 == $arr['task_represents_project']) {
		$s .= '<td align="center"><input type="checkbox" onclick="select_box(\'selected_task\', ' . $arr['task_id'] . ',\'project_' . $arr['task_project'] . '_level-' . $level . '-task_' . $arr['task_id'] . '_\',\'frm_tasks\')" onfocus="is_check=true;" onblur="is_check=false;" id="selected_task_' . $arr['task_id'] . '" name="selected_task" value="' . $arr['task_id'] . '"/></td>';
	}
	$s .= '</tr>';

	return $s;
}

//TODO: modules/projectdesigner/projectdesigner.class.php
function showtask_pr(&$arr, $level = 0, $today_view = false) {
	global $AppUI, $w2Pconfig, $done, $query_string, $durnTypes, $userAlloc, $showEditCheckbox;
	global $task_access, $task_priority;

	//Check for Tasks Access
	$canAccess = canTaskAccess($arr['task_id']);
	if (!$canAccess) {
		return (false);
	}

    $htmlHelper = new w2p_Output_HTMLHelper($AppUI);
    $htmlHelper->df .= ' ' . $AppUI->getPref('TIMEFORMAT');

	$perms = &$AppUI->acl();
	$show_all_assignees = $w2Pconfig['show_all_task_assignees'] ? true : false;

	$done[] = $arr['task_id'];

	$s = '<tr>';

	// dots
    $s .= '<td style="width: ' . (($today_view) ? '20%' : '50%') . '" class="data _name">';
	for ($y = 0; $y < $level; $y++) {
		if ($y + 1 == $level) {
			$image = w2PfindImage('corner-dots.gif', $m);
		} else {
			$image = w2PfindImage('shim.gif', $m);
		}
        $s .= '<img src="' . $image . '" width="16" height="12"  border="0" alt="" />';
	}
	// name link
	$alt = mb_strlen($arr['task_description']) > 80 ? mb_substr($arr['task_description'], 0, 80) . '...' : $arr['task_description'];
	// instead of the statement below
	$alt = mb_str_replace('"', "&quot;", $alt);
	$alt = mb_str_replace("\r", ' ', $alt);
	$alt = mb_str_replace("\n", ' ', $alt);

	$open_link = w2PshowImage('collapse.gif');
	if ($arr['task_milestone'] > 0) {
		$s .= '&nbsp;<b>' . $arr["task_name"] . '</b><!--</a>--> <img src="' . w2PfindImage('icons/milestone.gif', $m) . '" border="0" alt="" />';
	} elseif ($arr['task_dynamic'] == '1') {
		$s .= $open_link;
		$s .= '<strong>' . $arr['task_name'] . '</strong>';
	} else {
		$s .= $arr['task_name'];
	}
    $s .= '</td>';

    $s .= $htmlHelper->createCell('task_percent_complete', $arr['task_percent_complete']);
    $s .= $htmlHelper->createCell('task_start_date',       $arr['task_start_date']);
    $s .= $htmlHelper->createCell('task_end_date',         $arr['task_end_date']);
    $s .= $htmlHelper->createCell('last_update',           $arr['last_update']);
    $s .= '</tr>';

	return $s;
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
function showgtask(&$a, $level = 0, $project_id = 0) {
    /* Add tasks to gantt chart */
    global $gantt_arr;
    if (!is_task_in_gantt_arr($a)) {
        $gantt_arr[] = array($a, $level);
    }
}

// from modules/tasks/tasks.class.php
function findchild(&$tarr, $parent, $level = 0) {
	global $shown_tasks;

	$level = $level + 1;
	$n = count($tarr);

	for ($x = 0; $x < $n; $x++) {
		if ($tarr[$x]['task_parent'] == $parent && $tarr[$x]['task_parent'] != $tarr[$x]['task_id']) {
			echo showtask($tarr[$x], $level, true);
			$shown_tasks[$tarr[$x]['task_id']] = $tarr[$x]['task_id'];
			findchild($tarr, $tarr[$x]['task_id'], $level);
		}
	}
}

function findchild_gantt(&$tarr, $parent, $level = 0) {
    global $projects;

    $level = $level + 1;
    $n = count($tarr);

    for ($x = 0; $x < $n; $x++) {
        if ($tarr[$x]['task_parent'] == $parent && $tarr[$x]['task_parent'] != $tarr[$x]['task_id']) {
            showgtask($tarr[$x], $level, $tarr[$x]['project_id']);
            findchild_gantt($tarr, $tarr[$x]['task_id'], $tarr[$x]['project_id'], $level);
        }
    }
}

//TODO: modules/projectdesigner/projectdesigner.class.php
function findchild_pd(&$tarr, $parent, $level = 0) {
	global $projects;

	$level = $level + 1;
	$n = count($tarr);

	for ($x = 0; $x < $n; $x++) {
		if ($tarr[$x]['task_parent'] == $parent && $tarr[$x]['task_parent'] != $tarr[$x]['task_id']) {
			echo showtask_pd($tarr[$x], $level);
			findchild_pd($tarr, $tarr[$x]['task_id'], $level);
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
                                
                                // TODO: In some cases, $arg can be an empty string
                                // which will throw a notice. Don't want to touch it
                                // who knows what will break, it uses eval after all
                                // robertbasic, 2012-02-18
				$sortarr[$i][] = $marray[$j][$arg];
			}
		} else {
			$sortarr[$i] = $arg;
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
function calcEndByStartAndDuration($task) {
	$end_date = new w2p_Utilities_Date($task['task_start_date']);
	$end_date->addSeconds($task['task_duration'] * $task['task_duration_type'] * SEC_HOUR);
	return $end_date->format(FMT_DATETIME_MYSQL);
}

// from modules/tasks/tasks.class.php
function sort_by_item_title($title, $item_name, $item_type, $a = '') {
	global $AppUI, $project_id, $task_id, $min_view, $m;
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
		$s .= '<a href="./index.php?m=calendar&amp;a=day_view';
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
		$s .= '&nbsp;<img src="' . w2PfindImage('arrow-' . (($item_order == SORT_ASC) ? 'up' : 'down') . '.gif') . '" border="0" alt="" />';
	}
	return $s.'</a>';
}

// from modules/tasks/tasks.class.php
/**
 * canTaskAccess()
 * Used to check if a user has task_access to see the task in task list context
 * (This function was optimized to try to use the DB the least possible)
 *
 * @param mixed $task_id
 * @param mixed $task_access
 * @param mixed $task_owner
 * @return true if user has task access to it, or false if he doesn't
 */
function canTaskAccess($task_id, $task_access = 0, $task_owner = 0) {
    //trigger_error("canTaskAccess has been deprecated in v3.0 and will be removed by v4.0. Please use CTask->canAccess() instead.", E_USER_NOTICE);

    global $AppUI;

    $task = new CTask();
    $task->load($task_id);

    return $task->canAccess($AppUI->user_id);
}

// from modules/tasks/tasksperuser_sub.php
function doChildren($list, $N, $id, $uid, $level, $maxlevels, $display_week_hours, $ss, $se) {
	$tmp = '';
	if ($maxlevels == -1 || $level < $maxlevels) {
		for ($c = 0; $c < $N; $c++) {
			$task = $list[$c];
			if (($task->task_parent == $id) and isChildTask($task)) {
				// we have a child, do we have the user as a member?
				if (isMemberOfTask($list, $N, $uid, $task)) {
					$tmp .= displayTask($list, $task, $level, $display_week_hours, $ss, $se, $uid);
					$tmp .= doChildren($list, $N, $task->task_id, $uid, $level + 1, $maxlevels, $display_week_hours, $ss, $se);
				}
			}
		}
	}
	return $tmp;
}

// from modules/reports/tasksperuser.php
function doChildren_r($list, $Lusers, $N, $id, $uid, $level, $maxlevels, $display_week_hours, $ss, $se, $log_all_projects = false) {
	$tmp = "";
	if ($maxlevels == -1 || $level < $maxlevels) {
		for ($c = 0; $c < $N; $c++) {
			$task = $list[$c];
			if (($task->task_parent == $id) and isChildTask($task)) {
				// we have a child, do we have the user as a member?
				if (isMemberOfTask_r($list, $Lusers, $N, $uid, $task)) {
					$tmp .= displayTask_r($list, $task, $level, $display_week_hours, $ss, $se, $log_all_projects, $uid);
					$tmp .= doChildren_r($list, $Lusers, $N, $task->task_id, $uid, $level + 1, $maxlevels, $display_week_hours, $ss, $se, $log_all_projects);
				}
			}
		}
	}
	return $tmp;
}

// from modules/tasks/tasksperuser_sub.php
function isMemberOfTask($list, $N, $user_id, $task) {

	global $user_assigned_tasks;

	if (isset($user_assigned_tasks[$user_id])) {
		if (in_array($task->task_id, $user_assigned_tasks[$user_id])) {
			return true;
		}
	}
	return false;
}

// from modules/reports/tasksperuser.php
function isMemberOfTask_r($list, $Lusers, $N, $user_id, $task) {

	for ($i = 0; $i < $N && $list[$i]->task_id != $task->task_id; $i++)
		;
	$users = $Lusers[$i];

	foreach ($users as $task_user_id => $user_data) {
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
function displayTask($list, $task, $level, $display_week_hours, $fromPeriod, $toPeriod, $user_id) {

	global $AppUI, $df, $durnTypes, $log_userfilter_users, $now, $priority,
			$active_users, $z, $zi, $x, $userAlloc, $projects;
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
	$tmp .= '<td>';

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
	foreach ($users as $key => $row) {
		if ($row['user_id']) {
			$us .= '<a href="?m=admin&a=viewuser&user_id=' . $row[0] . '">' . $sep . $row['contact_name'] . '&nbsp;(' . $row['perc_assignment'] . '%)</a>';
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
function displayTask_r($list, $task, $level, $display_week_hours, $fromPeriod, $toPeriod, $log_all_projects = false, $user_id = 0) {
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
function isChildTask($task) {
	return $task->task_id != $task->task_parent;
}

// from modules/tasks/tasksperuser_sub.php
function weekDates($display_allocated_hours, $fromPeriod, $toPeriod) {
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
function weekDates_r($display_allocated_hours, $fromPeriod, $toPeriod) {
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
	$ew = getEndWeek($e); //intval($e->Format('%U'));

	$row = '';
	for ($i = $sw; $i <= $ew; $i++) {
		$sdf = substr($AppUI->getPref('SHDATEFORMAT'), 3);
		$row .= '<td nowrap="nowrap" bgcolor="#A0A0A0"><font color="black"><b>' . $s->format($sdf) . '</b></font></td>';
		$s->addSeconds(168 * 3600); // + one week
	}
	return $row;
}

// from modules/tasks/tasksperuser_sub.php
function weekCells($display_allocated_hours, $fromPeriod, $toPeriod) {

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
function weekCells_r($display_allocated_hours, $fromPeriod, $toPeriod) {

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
function displayWeeks($list, $task, $level, $fromPeriod, $toPeriod) {

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
function displayWeeks_r($list, $task, $level, $fromPeriod, $toPeriod, $user_id = 0) {

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
function getBeginWeek($d) {
	$dn = (int) $d->Format('%w');
	$dd = new w2p_Utilities_Date($d);
	$dd->subtractSeconds($dn * 24 * 3600);
	return (int) $dd->Format('%U');
}

// from modules/tasks/tasksperuser_sub.php
// from modules/reports/tasksperuser.php
function getEndWeek($d) {

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
function hasChildren($list, $task) {
	foreach ($list as $t) {
		if ($t->task_parent == $task->task_id) {
			return true;
		}
	}
	return false;
}

// from modules/tasks/tasksperuser_sub.php
function getOrphanedTasks($tval) {
    return (sizeof($tval->task_assigned_users) > 0) ? null : $tval;
}

// from modules/tasks/viewgantt.php
function showfiltertask(&$a, $level=0) {
     /* Add tasks to the filter task aray */
     global $filter_task_list, $parents;
     $filter_task_list[] = array($a, $level);
     $parents[$a['task_parent']] = true;
}
// from modules/tasks/viewgantt.php
function findfiltertaskchild(&$tarr, $parent, $level=0) {
     GLOBAL $projects, $filter_task_list;
     $level = $level + 1;
     $n = count($tarr);
     for ($x=0; $x < $n; $x++) {
          if ($tarr[$x]['task_parent'] == $parent && $tarr[$x]['task_parent'] != $tarr[$x]['task_id']){
               showfiltertask($tarr[$x], $level);
               findfiltertaskchild($tarr, $tarr[$x]['task_id'], $level);
          }
     }
}

// from modules/system/roles/roles.class.php
function showRoleRow($role = null) {
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
		$s .= '<td valign="top"><input type="text" size="50" name="role_description" class="text" value="' . $description . '">' . ($id ? '' : '&nbsp;&nbsp;&nbsp;&nbsp;' . arraySelect($roles_arr, 'copy_role_id', 'class="text"', 0, true)) . '</td>';
		$s .= '<td><input type="submit" value="' . $AppUI->_($id ? 'edit' : 'add') . '" class="button" /></td>';
	} else {
		$s .= '<tr><td width="50" valign="top">';
		if ($canEdit) {
			$s .= '<a href="?m=system&u=roles&role_id=' . $id . '">' . w2PshowImage('icons/stock_edit-16.png') . '</a><a href="?m=system&u=roles&a=viewrole&role_id=' . $id . '" title="">' . w2PshowImage('obj/lock.gif') . '</a>';
		}
		if ($canDelete && strpos($name, 'admin') === false) {
			$s .= '<a href=\'javascript:delIt(' . $id . ')\'>' . w2PshowImage('icons/stock_delete-16.png') . '</a>';
		}
		$s .= '</td><td valign="top">' . $name . '</td><td valign="top">' . $AppUI->_($description) . '</td><td valign="top" width="16">&nbsp;</td>';
	}
	$s .= '</tr>';
	return $s;
}

// from modules/system/syskeys/syskeys.class.php
function parseFormatSysval($text, $syskey) {
	$q = new w2p_Database_Query;
	$q->addTable('syskeys');
	$q->addQuery('syskey_type, syskey_sep1, syskey_sep2');
	$q->addWhere('syskey_id = ' . (int)$syskey);
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
function showcodes(&$a) {
	global $AppUI, $company_id;

	$alt = htmlspecialchars($a['billingcode_desc']);
	$s = '
<tr>
	<td width=40>
		<a href="?m=system&amp;a=billingcode&amp;company_id=' . $company_id . '&amp;billingcode_id=' . $a['billingcode_id'] . '" title="' . $AppUI->_('edit') . '">
			<img src="' . w2PfindImage('icons/stock_edit-16.png') . '" border="0" alt="Edit" /></a>';

	if ($a['billingcode_status'] == 0)
		$s .= '<a href="javascript:delIt2(' . $a['billingcode_id'] . ');" title="' . $AppUI->_('delete') . '">
			<img src="' . w2PfindImage('icons/stock_delete-16.png') . '" border="0" alt="Delete" /></a>';

	$s .= '
	</td>
	<td align="left">&nbsp;' . $a['billingcode_name'] . ($a['billingcode_status'] == 1 ? ' (deleted)' : '') . '</td>
	<td nowrap="nowrap" align="center">' . $a['billingcode_value'] . '</td>
	<td nowrap="nowrap">' . $a['billingcode_desc'] . '</td>
</tr>';
	return $s;
}

// from modules/smartsearch/smartsearch.class.php
function highlight($text, $keyval) {
	global $ssearch;

	$txt = $text;
	$keys = array();
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
function recode2regexp_utf8($input) {
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

// from modules/resources/tasks_dosql.addedit.php
/**
 * presave functions are called before the session storage of tab data
 * is destroyed.  It can be used to save this data to be used later in
 * the postsave function.
 */
function resource_presave() {
	global $other_resources;
	// check to see if we are in the post save list or if we need to
	// interrogate the session.
	$other_resources = w2PgetParam($_POST, 'hresource_assign');
}

// from modules/resources/tasks_dosql.addedit.php
/**
 * postsave functions are only called after a succesful save.  They are
 * used to perform database operations after the event.
 */
function resource_postsave() {
	global $other_resources;
	global $obj;
	$task_id = $obj->task_id;
	if (isset($other_resources)) {
		$value = array();
		$reslist = explode(';', $other_resources);
		foreach ($reslist as $res) {
			if ($res) {
				list($resource, $perc) = explode('=', $res);
				$value[] = array($task_id, $resource, $perc);
			}
		}
		// first delete any elements already there, then replace with this
		// list.
		$q = new w2p_Database_Query;
		$q->setDelete('resource_tasks');
		$q->addWhere('task_id = ' . (int)$obj->task_id);
		$q->exec();
		$q->clear();
		if (count($value)) {
			foreach ($value as $v) {
				$q->addTable('resource_tasks');
				$q->addInsert('task_id,resource_id,percent_allocated', $v, true);
				$q->exec();
				$q->clear();
			}
		}
	}
}

// from modules/public/selector.php
function selPermWhere($obj, $idfld, $namefield, $prefix = '') {
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
function showchilddept(&$a, $level = 1) {
	global $buffer, $department;
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
function findchilddept(&$tarr, $parent, $level = 1) {
	$level = $level + 1;
	$n = count($tarr);
	for ($x = 0; $x < $n; $x++) {
		if ($tarr[$x]['dept_parent'] == $parent && $tarr[$x]['dept_parent'] != $tarr[$x]['dept_id']) {
			findchilddept($tarr, $tarr[$x]['dept_id'], $level);
		}
	}
}

//comes from modules/departments/departments.class.php
function addDeptId($dataset, $parent) {
	global $dept_ids;
	foreach ($dataset as $data) {
		if ($data['dept_parent'] == $parent) {
			$dept_ids[] = $data['dept_id'];
			addDeptId($dataset, $data['dept_id']);
		}
	}
}

/*
* Build an SQL to determine an appropriate time slot that will meet
* The requirements for all participants, including the requestor.
* 
* From modules/calendar/clash.php
*/
function clash_process(w2p_Core_CAppUI $AppUI) {
	global $do_include;

	$obj = new CEvent;
	$obj->bind($_SESSION['add_event_post']);
	$attendees = $_SESSION['add_event_attendees'];
	$users = array();
	if (isset($attendees) && $attendees) {
		$users = explode(',', $attendees);
	}
	array_push($users, $obj->event_owner);
	// First remove any duplicates
	$users = array_unique($users);
	// Now remove any null entries, so implode doesn't create a dud SQL
	// Foreach is safer as it works on a copy of the array.
	foreach ($users as $key => $user) {
		if (!$user)
			unset($users[$key]);
	}

	$start_date = new w2p_Utilities_Date($_POST['event_start_date'] . "000000");
	$end_date = new w2p_Utilities_Date($_POST['event_end_date'] . "235959");

	// First find any events in the range requested.
	$event_list = $obj->getEventsInWindow($start_date->format(FMT_DATETIME_MYSQL), $end_date->format(FMT_DATETIME_MYSQL), (int)($_POST['start_time'] / 100), (int)($_POST['end_time'] / 100), $users);
	$event_start_date = new w2p_Utilities_Date($_POST['event_start_date'] . $_POST['start_time']);
	$event_end_date = new w2p_Utilities_Date($_POST['event_end_date'] . $_POST['end_time']);

	if (!$event_list || !count($event_list)) {
		// First available date/time is OK, seed addEdit with the details.
		$obj->event_start_date = $event_start_date->format(FMT_DATETIME_MYSQL);
		$obj->event_end_date = $event_end_date->format(FMT_DATETIME_MYSQL);
		$_SESSION['add_event_post'] = get_object_vars($obj);
		$AppUI->setMsg('No clashes in suggested timespan', UI_MSG_OK);
		$_SESSION['event_is_clash'] = true;
		$_GET['event_id'] = $obj->event_id;
		$do_include = W2P_BASE_DIR . "/modules/calendar/addedit.php";
		return;
	}

	// Now we grab the events, in date order, and compare against the
	// required start and end times.
	// Working in 30 minute increments from the start time, and remembering
	// the end time stipulation, find the first hole in the times.
	// Determine the duration in hours/minutes.
	$start_hour = (int)($_POST['start_time'] / 10000);
	$start_minutes = (int)(($_POST['start_time'] % 10000) / 100);
	$start_time = $start_hour * 60 + $start_minutes;
	$end_hour = (int)($_POST['end_time'] / 10000);
	$end_minutes = (int)(($_POST['end_time'] % 10000) / 100);
	$end_time = ($end_hour * 60 + $end_minutes) - $_POST['duration'];

	// First, build a set of "slots" that give us the duration
	// and start/end times we need
	$first_day = $start_date->format('%E');
	$end_day = $end_date->format('%E');
	$days_between = ($end_day + 1) - $first_day;
	$oneday = new Date_Span(array(1, 0, 0, 0));

	$slots = array();
	$slot_count = 0;
	$first_date = new w2p_Utilities_Date($start_date);
	for ($i = 0; $i < $days_between; $i++) {
		if ($first_date->isWorkingDay()) {
			$slots[$i] = array();
			for ($j = $start_time; $j <= $end_time; $j += 30) {
				$slot_count++;
				$slots[$i][] = array('date' => $first_date->format('%Y-%m-%d'), 'start_time' => $j, 'end_time' => $j + $_POST['duration'], 'committed' => false);
			}
		}
		$first_date->addSpan($oneday);
	}

	// Now process the events list
	foreach ($event_list as $event) {
		$sdate = new w2p_Utilities_Date($event['event_start_date']);
		$edate = new w2p_Utilities_Date($event['event_end_date']);
		$sday = $sdate->format('%E');
		$day_offset = $sday - $first_day;

		// Now find the slots on that day that match
		list($syear, $smonth, $sday, $shour, $sminute, $ssecond) = sscanf($event['event_start_date'], "%4d-%2d-%2d %2d:%2d:%2d");
		list($eyear, $emonth, $eday, $ehour, $eminute, $esecond) = sscanf($event['event_start_date'], "%4d-%2d-%2d %2d:%2d:%2d");
		$start_mins = $shour * 60 + $sminute;
		$end_mins = $ehour * 60 + $eminute;
		if (isset($slots[$day_offset])) {
			foreach ($slots[$day_offset] as $key => $slot) {
				if ($start_mins <= $slot['end_time'] && $end_mins >= $slot['start_time']) {
					$slots[$day_offset][$key]['committed'] = true;
				}
			}
		}
	}

	// Third pass through, find the first uncommitted slot;
	foreach ($slots as $day_offset => $day_slot) {
		foreach ($day_slot as $slot) {
			if (!$slot['committed']) {
				$hour = (int)($slot['start_time'] / 60);
				$min = $slot['start_time'] % 60;
				$ehour = (int)($slot['end_time'] / 60);
				$emin = $slot['end_time'] % 60;
				$obj->event_start_date = $slot['date'] . ' ' . sprintf("%02d:%02d:00", $hour, $min);
				$obj->event_end_date = $slot['date'] . ' ' . sprintf("%02d:%02d:00", $ehour, $emin);
				$_SESSION['add_event_post'] = get_object_vars($obj);
				$AppUI->setMsg('First available time slot', UI_MSG_OK);
				$_SESSION['event_is_clash'] = true;
				$_GET['event_id'] = $obj->event_id;
				$do_include = W2P_BASE_DIR . '/modules/calendar/addedit.php';
				return;
			}
		}
	}
	// If we get here we have found no available slots
	clear_clash();
	$AppUI->setMsg('No times match your parameters', UI_MSG_ALERT);
	$AppUI->redirect();
}

/*
* Cancel the event, but notify attendees of a possible meeting and request
* they might like to contact author regarding the date.
*
* From modules/calendar/clash.php
*/
function clash_mail(w2p_Core_CAppUI $AppUI) {
	$obj = new CEvent;
	if (!$obj->bind($_SESSION['add_event_post'])) {
		$AppUI->setMsg($obj->getError(), UI_MSG_ERROR);
	} else {
		$obj->notify($_SESSION['add_event_post']['event_assigned'], w2PgetParam($_REQUEST, 'event_id', 0) ? false : true, true);
		$AppUI->setMsg('Mail sent', UI_MSG_OK);
	}
	clear_clash();
	$AppUI->redirect();
}

/*
* Even though we end up with a clash, accept the detail.
* 
* From modules/calendar/clash.php
*/
function clash_accept(w2p_Core_CAppUI $AppUI) {
	global $do_redirect;

	$AppUI->setMsg('Event');
	$obj = new CEvent;
	$obj->bind($_SESSION['add_event_post']);
	$GLOBALS['a'] = $_SESSION['add_event_caller'];
	$is_new = ($obj->event_id == 0);
    $result = $obj->store();

    if ($result) {
		if (isset($_SESSION['add_event_attendees']) && $_SESSION['add_event_attendees']){
			$obj->updateAssigned(explode(',', $_SESSION['add_event_attendees']));
        }
		if (isset($_SESSION['add_event_mail']) && $_SESSION['add_event_mail'] == 'on') {
			$obj->notify($_SESSION['add_event_attendees'], !$is_new);
        }
        $AppUI->setMsg('Event Stored', UI_MSG_OK, true);
    } else {
        $AppUI->setMsg($msg, UI_MSG_ERROR);
    }
	clear_clash();
	$AppUI->redirect();
}

//From modules/calendar/clash.php
function clear_clash() {
	unset($_SESSION['add_event_caller']);
	unset($_SESSION['add_event_post']);
	unset($_SESSION['add_event_clash']);
	unset($_SESSION['add_event_attendees']);
	unset($_SESSION['add_event_mail']);
}

// Clash functions.
/*
* Cancel the event, simply clear the event details and return to the previous
* page.
* 
* From modules/calendar/clash.php
*/
function clash_cancel(w2p_Core_CAppUI $AppUI) {
	global $a;
	$a = $_SESSION['add_event_caller'];
	clear_clash();
	$AppUI->setMsg($AppUI->_('Event Cancelled'), UI_MSG_ALERT);
	$AppUI->redirect();
}

// From: modules/files/filefolder.class.php
function getFolderSelectList() {
	global $AppUI;
	$folders = array(0 => '');
	$q = new w2p_Database_Query();
	$q->addTable('file_folders');
	$q->addQuery('file_folder_id, file_folder_name, file_folder_parent');
	$q->addOrder('file_folder_name');
	$folders = arrayMerge(array('0' => array(0, $AppUI->_('Root'), -1)), $q->loadHashList('file_folder_id'));
	return $folders;
}

/*
 * $parent is the parent of the children we want to see
 * $level is increased when we go deeper into the tree, used to display a nice indented tree
 */
// From: modules/files/filefolder.class.php
function getFolders($parent, $level = 0) {
	global $AppUI, $allowed_folders_ary, $denied_folders_ary, $tab, $m, $a, $company_id, $allowed_companies, $project_id, $task_id, $current_uri, $file_types;
	// retrieve all children of $parent

    $file_folder = new CFile_Folder();
    $folders = $file_folder->getFoldersByParent($parent);

	$s = '';
	// display each child
	foreach ($folders as $row) {
		if (array_key_exists($row['file_folder_id'], $allowed_folders_ary) or array_key_exists($parent, $allowed_folders_ary)) {
            $file_count = countFiles($row['file_folder_id']);

            $s .= '<tr><td colspan="20">';
            if ($m == 'files') {
                $s .= '<a href="./index.php?m=' . $m . '&amp;a=' . $a . '&amp;tab=' . $tab . '&folder=' . $row['file_folder_id'] . '" name="ff' . $row['file_folder_id'] . '">';
            }
            $s .= '<img src="' . w2PfindImage('folder5_small.png', 'files') . '" width="16" height="16" style="float: left; border: 0px;" />';
            $s .= $row['file_folder_name'];
            if ($m == 'files') {
                $s .= '</a>';
            }
            if ($file_count > 0) {
                $s .= ' <a href="javascript: void(0);" onClick="expand(\'files_' . $row['file_folder_id'] . '\')" class="has-files">(' . $file_count . ' files) +</a>';
            }
            $s .= '<form name="frm_remove_folder_' . $row['file_folder_id'] . '" action="?m=files" method="post" accept-charset="utf-8">
                    <input type="hidden" name="dosql" value="do_folder_aed" />
                    <input type="hidden" name="del" value="1" />
                    <input type="hidden" name="file_folder_id" value="' . $row['file_folder_id'] . '" />
                    </form>';
            $s .= '<a style="float:left;" href="./index.php?m=files&amp;a=addedit_folder&amp;folder=' . $row['file_folder_id'] . '">' . w2PshowImage('filesaveas.png', '16', '16', 'edit icon', 'edit this folder', 'files') . '</a>' .
                  '<a style="float:left;" href="./index.php?m=files&amp;a=addedit_folder&amp;file_folder_parent=' . $row['file_folder_id'] . '&amp;file_folder_id=0">' . w2PshowImage('edit_add.png', '', '', 'new folder', 'add a new subfolder', 'files') . '</a>' .
                  '<a style="float:right;" href="javascript: void(0);" onclick="if (confirm(\'Are you sure you want to delete this folder?\')) {document.frm_remove_folder_' . $row['file_folder_id'] . '.submit()}">' . w2PshowImage('remove.png', '', '', 'delete icon', 'delete this folder', 'files') . '</a>' .
                  '<a style="float:left;" href="./index.php?m=files&amp;a=addedit&amp;folder=' . $row['file_folder_id'] . '&amp;project_id=' . $project_id . '&amp;file_id=0">' . w2PshowImage('folder_new.png', '', '', 'new file', 'add new file to this folder', 'files') . '</a>';
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
function countFiles($folder) {
	global $AppUI, $company_id, $allowed_companies, $tab;
	global $deny1, $deny2, $project_id, $task_id, $showProject, $file_types;

	$q = new w2p_Database_Query();
	$q->addTable('files');
	$q->addQuery('count(files.file_id)', 'file_in_folder');
	$q->addJoin('projects', 'p', 'p.project_id = file_project');
	$q->addJoin('users', 'u', 'u.user_id = file_owner');
	$q->addJoin('tasks', 't', 't.task_id = file_task');
	$q->addJoin('file_folders', 'ff', 'ff.file_folder_id = file_folder');
	$q->addWhere('file_folder = ' . (int)$folder);
	if (count($deny1) > 0) {
		$q->addWhere('file_project NOT IN (' . implode(',', $deny1) . ')');
	}
	if (count($deny2) > 0) {
		$q->addWhere('file_task NOT IN (' . implode(',', $deny2) . ')');
	}
	if ($project_id) {
		$q->addWhere('file_project = ' . (int)$project_id);
	}
	if ($task_id) {
		$q->addWhere('file_task = ' . (int)$task_id);
	}
	if ($company_id) {
		$q->innerJoin('companies', 'co', 'co.company_id = p.project_company');
		$q->addWhere('company_id = ' . (int)$company_id);
		$q->addWhere('company_id IN (' . $allowed_companies . ')');
	}

	$files_in_folder = $q->loadResult();
	$q->clear();

	return $files_in_folder;
}

// From: modules/files/filefolder.class.php
function displayFiles($AppUI, $folder_id, $task_id, $project_id, $company_id) {
	global $m, $a, $tab, $xpg_min, $xpg_pagesize, $showProject, $file_types, 
            $cfObj, $xpg_totalrecs, $xpg_total_pages, $page, $company_id,
            $allowed_companies, $current_uri, $w2Pconfig, $canEdit, $canRead;

	$df = $AppUI->getPref('SHDATEFORMAT');
	$tf = $AppUI->getPref('TIMEFORMAT');

	// SETUP FOR FILE LIST
	$q = new w2p_Database_Query();
	$q->addQuery('f.*, max(f.file_id) as latest_id, count(f.file_version) as file_versions, 
        round(max(file_version), 2) as file_lastversion, u.user_username as file_owner');
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
		$q->addWhere('file_project = ' . (int)$project_id);
	}
	if ($task_id) {
		$q->addWhere('file_task = ' . (int)$task_id);
	}
	if ($company_id) {
		$q->addWhere('project_company = ' . (int)$company_id);
	}
    $tab = ($m == 'files') ? $tab-1 : -1;
    if ($tab >= 0) {
        $q->addWhere('file_category = ' . (int)$tab);
    }
	$q->setLimit($xpg_pagesize, $xpg_min);
    if ($folder_id > -1) {
        $q->addWhere('file_folder = ' . (int)$folder_id);
    }
	$q->addGroup('file_version_id DESC');
    $q->addOrder('file_project');

	$qv = new w2p_Database_Query();
	$qv->addTable('files');
	$qv->addQuery('file_id, file_version, file_project, file_name, file_task,
		file_description, u.user_username as file_owner, file_size, file_category,
		task_name, file_version_id, file_date as file_datetime, file_checkout, file_co_reason, file_type,
		file_date, cu.user_username as co_user, project_name,
		project_color_identifier, project_owner,
        con.contact_first_name, con.contact_last_name, con.contact_display_name as contact_name,
        co.contact_first_name as co_contact_first_name, co.contact_last_name as co_contact_last_name,
        co.contact_display_name as co_contact_name ');
	$qv->addJoin('projects', 'p', 'p.project_id = file_project');
	$qv->addJoin('users', 'u', 'u.user_id = file_owner');
	$qv->addJoin('contacts', 'con', 'con.contact_id = u.user_contact');
	$qv->addJoin('tasks', 't', 't.task_id = file_task');
	$qv->addJoin('file_folders', 'ff', 'ff.file_folder_id = file_folder');
	if ($project_id) {
		$qv->addWhere('file_project = ' . (int)$project_id);
	}
	if ($task_id) {
		$qv->addWhere('file_task = ' . (int)$task_id);
	}
	if ($company_id) {
		$qv->addWhere('project_company = ' . (int)$company_id);
	}
    if ($tab >= 0) {
        $qv->addWhere('file_category = ' . (int)$tab);
    }
	$qv->leftJoin('users', 'cu', 'cu.user_id = file_checkout');
	$qv->leftJoin('contacts', 'co', 'co.contact_id = cu.user_contact');
	$qv->addWhere('file_folder = ' . (int)$folder_id);

	$files = array();
	$file_versions = array();
    $files = $q->loadList();
    $file_versions = $qv->loadHashList('file_id');
    $q->clear();
    $qv->clear();

	if ($files === array()) {
		return 0;
	}

    $fieldList = array();
    $fieldNames = array();

    $module = new w2p_Core_Module();
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
            'file_owner', 'file_size', 'file_type', 'file_datetime', 'file_checkout_reason');
        $fieldNames = array('File Name', 'Description', 'Version', 'Category',
            'Folder', 'Task Name', 'Owner', 'Size', 'Type', 'Date', 
            'Checkout Reason');

        $module->storeSettings('files', 'index_list', $fieldList, $fieldNames);
    }

    $s  = '<tr>';
    $s .= '<th></th>';
    $s .= '<th>' . $AppUI->_('co') . '</th>';
//TODO: The link below is commented out because this module doesn't support sorting... yet.
    foreach ($fieldNames as $index => $name) {
        $s .= '<th nowrap="nowrap">';
        $s .= $AppUI->_($fieldNames[$index]);
        $s .= '</th>';
    }
    $s .= '<th></th>';
	$s .= '</tr>';

	$fp = -1;
    $htmlHelper = new w2p_Output_HTMLHelper($AppUI);
    $htmlHelper->df .= ' ' . $AppUI->getPref('TIMEFORMAT');

    $file_types = w2PgetSysVal('FileType');
    $customLookups = array('file_category' => $file_types);

	$id = 0;
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
				$s .= '<td colspan="20" style="border: outset 2px #eeeeee;' . $style . '">';
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
        $htmlHelper->stageRowData($row);

        $s .= '<tr>';
 		$s .= '<td nowrap="nowrap" width="20">';
		if ($canEdit && (empty($latest_file['file_checkout']) || ($latest_file['file_checkout'] == 'final' && ($canEdit || $latest_file['project_owner'] == $AppUI->user_id)))) {
			$s .= '<a href="./index.php?m=files&a=addedit&file_id=' . $latest_file['file_id'] . '">' . w2PshowImage('kedit.png', '16', '16', 'edit file', 'edit file', 'files') . '</a>';
        }
        $s .= '</td>';
		$s .= '<td nowrap="nowrap">';
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
                    $file_icon = getIcon($file['file_type']);
                    $hdate = new w2p_Utilities_Date($file['file_date']);

                    foreach ($fieldList as $index => $column) {
                        $hidden_table .= $sub_htmlHelper->createCell($fieldList[$index], $file[$fieldList[$index]], $customLookups);
                    }

                    if ($canEdit && $w2Pconfig['files_show_versions_edit']) {
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
		$hidden_table = '';
	}
	return $s;
}

// From: modules/files/files.class.php
function last_file($file_versions, $file_name, $file_project) {
	$latest = null;

	if (isset($file_versions))
		foreach ($file_versions as $file_version)
			if ($file_version['file_name'] == $file_name && $file_version['file_project'] == $file_project)
				if ($latest == null || $latest['file_version'] < $file_version['file_version'])
					$latest = $file_version;

	return $latest;
}

// From: modules/files/files.class.php
function getIcon($file_type) {
    global $w2Pconfig, $uistyle;
    $result = 'icons/unknown.png';
    $mime = str_replace('/', '-', $file_type);
    $icon = 'gnome-mime-' . $mime;
    if (is_file(W2P_BASE_DIR . '/styles/' . $uistyle . '/images/modules/files/icons/' . $icon . '.png')) {
        $result = 'icons/' . $icon . '.png';
    } else {
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
    }

    return $result;
}

// From: modules/files/files.class.php
function getHelpdeskFolder() {
	$q = new w2p_Database_Query();
	$q->addTable('file_folders', 'ff');
	$q->addQuery('file_folder_id');
	$q->addWhere('ff.file_folder_name = \'Helpdesk\'');
	$ffid = $q->loadResult();
	$q->clear();
	return intval($ffid);
}

// From: modules/files/files.class.php
function file_show_attr() {
	global $AppUI, $obj, $ci, $canAdmin, $projects, $file_project, $file_task, $task_name, $preserve, $file_helpdesk_item;

	if ($ci) {
		$str_out = '<tr><td align="right" nowrap="nowrap">' . $AppUI->_('Minor Revision') . '</td><td><input type="Radio" name="revision_type" value="minor" checked />' . '</td><tr><td align="right" nowrap="nowrap">' . $AppUI->_('Major Revision') . '</td><td><input type="Radio" name="revision_type" value="major" /></td>';
	} else {
		$str_out = '<tr><td align="right" nowrap="nowrap">' . $AppUI->_('Version') . ':</td>';
	}

	$str_out .= '<td align="left">';

	if ($ci || ($canAdmin && $obj->file_checkout == 'final')) {
		$str_out .= '<input type="hidden" name="file_checkout" value="" /><input type="hidden" name="file_co_reason" value="" />';
	}

	if ($ci) {
		$the_value = (strlen($obj->file_version) > 0 ? $obj->file_version + 0.01 : '1');
		$str_out .= '<input type="hidden" name="file_version" value="' . $the_value . '" />';
	} else {
		$the_value = (strlen($obj->file_version) > 0 ? $obj->file_version : '1');
		$str_out .= '<input type="text" name="file_version" maxlength="10" size="5" value="' . $the_value . '" class="text" />';
	}

	$str_out .= '</td>';

	$select_disabled = ' ';
	$onclick_task = ' onclick="popTask()" ';
	if ($ci && $preserve) {
		$select_disabled = ' disabled="disabled" ';
		$onclick_task = ' ';
		// need because when a html is disabled, it's value it's not sent in submit
		$str_out .= '<input type="hidden" name="file_project" value="' . $file_project . '" />';
		$str_out .= '<input type="hidden" name="file_category" value="' . $obj->file_category . '" />';
	}

	// Category
	$str_out .= '<tr><td align="right" nowrap="nowrap">' . $AppUI->_('Category') . ':</td>';
	$str_out .= '<td align="left">' . arraySelect(w2PgetSysVal('FileType'), 'file_category', 'class="text"' . $select_disabled, $obj->file_category, true) . '<td>';

	// ---------------------------------------------------------------------------------

	if ($file_helpdesk_item) {
		$hd_item = new CHelpDeskItem();
		$hd_item->load($file_helpdesk_item);
		//Helpdesk Item
		$str_out .= '<tr><td align="right" nowrap="nowrap">' . $AppUI->_('Helpdesk Item') . ':</td>';
		$str_out .= '<td align="left"><strong>' . $hd_item->item_id . ' - ' . $hd_item->item_title . '</strong></td></tr>';
		// Project
		$str_out .= '<input type="hidden" name="file_project" value="' . $file_project . '" />';

		// Task
		$str_out .= '<input type="hidden" name="file_task" value="0" />';
	} else {
		// Project
		$str_out .= '<tr><td align="right" nowrap="nowrap">' . $AppUI->_('Project') . ':</td>';
		$str_out .= '<td align="left">' . projectSelectWithOptGroup($AppUI->user_id, 'file_project', 'size="1" class="text" style="width:270px"' . $select_disabled, $file_project) . '</td></tr>';

		// ---------------------------------------------------------------------------------

		// Task
		$str_out .= '<tr><td align="right" nowrap="nowrap">' . $AppUI->_('Task') . ':</td><td align="left" colspan="2" valign="top"><input type="hidden" name="file_task" value="' . $file_task . '" /><input type="text" class="text" name="task_name" value="' . $task_name . '" size="40" disabled /><input type="button" class="button" value="' . $AppUI->_('select task') . '..."' . $onclick_task . '/></td></tr>';
	}

	return ($str_out);
}

//TODO: modules/projectdesigner/projectdesigner.class.php
function get_dependencies_pd($task_id) {
	// Pull tasks dependencies
	$q = new w2p_Database_Query;
	$q->addTable('tasks', 't');
	$q->addTable('task_dependencies', 'td');
	$q->addQuery('t.task_id, t.task_name');
	$q->addWhere('td.dependencies_task_id = ' . (int)$task_id);
	$q->addWhere('t.task_id = td.dependencies_req_task_id');
	$taskDep = $q->loadHashList();
}

/** Retrieve tasks with first task_end_dates within given project
 * @param int Project_id
 * @param int SQL-limit to limit the number of returned tasks
 * @return array List of criticalTasks
 */
//TODO: modules/projectdesigner/projectdesigner.class.php
function getCriticalTasksInverted($project_id = null, $limit = 1) {

	if (!$project_id) {
		$result = array();
		$result[0]['task_end_date'] = '0000-00-00 00:00:00';
		return $result;
	} else {
		$q = new w2p_Database_Query();
		$q->addTable('tasks');
		$q->addWhere('task_project = ' . (int)$project_id  . ' AND NOT ISNULL( task_end_date ) AND task_end_date <>  \'0000-00-00 00:00:00\'');
		$q->addOrder('task_start_date ASC');
		$q->setLimit($limit);

		return $q->loadList();
	}
}

//TODO: modules/projectdesigner/projectdesigner.class.php
function get_actual_end_date_pd($task_id, $task) {
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
		$q->addWhere('task_log_task = ' . (int)$task_id);
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
** E.g. this code is used as well in a tab for the admin/viewuser site
**
** @mixed user_id 	userId as filter for tasks/projects that are shown, if nothing is specified,
current viewing user $AppUI->user_id is used.
*/
// From: modules/projects/project.class.php
function projects_list_data($user_id = false) {
	global $AppUI, $addPwOiD, $buffer, $company, $company_id, $company_prefix,
        $deny, $department, $dept_ids, $w2Pconfig, $orderby, $orderdir,
        $tasks_problems, $owner, $projectTypeId, $search_text, $project_type;

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
			$q->addWhere('ut.user_id = ' . (int)$user_id);
		}
		$q->addOrder('task_end_date DESC');
		$q->addGroup('task_project');
		$tasks_users = $q->exec();
		$q->clear();
	}

	// add Projects where the Project Owner is in the given department
	if ($addPwOiD && isset($department)) {
		$owner_ids = array();
		$q->addTable('users');
		$q->addQuery('user_id');
		$q->addJoin('contacts', 'c', 'c.contact_id = user_contact', 'inner');
		$q->addWhere('c.contact_department = ' . (int)$department);
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
	$q->addQuery('pr.project_id, project_status, project_color_identifier,
		project_type, project_name, project_description, project_scheduled_hours as project_duration, project_scheduled_hours,
		project_parent, project_original_parent, project_percent_complete,
		project_color_identifier, project_company, company_id, company_name,
        project_status, project_last_task as critical_task,
        tp.task_log_problem, user_username, project_active');

	$fields = w2p_Core_Module::getSettings('projects', 'index_list');
	unset($fields['department_list']);  // added as an alias below
	foreach ($fields as $field => $text) {
		$q->addQuery($field);
	}
	$q->addQuery('ct.contact_display_name AS owner_name');
	$q->addJoin('users', 'u', 'pr.project_owner = u.user_id');
	$q->addJoin('contacts', 'ct', 'ct.contact_id = u.user_contact');
	$q->addJoin('tasks_problems', 'tp', 'pr.project_id = tp.task_project');
	if ($addProjectsWithAssignedTasks) {
		$q->addJoin('tasks_users', 'tu', 'pr.project_id = tu.task_project');
	}
	if (!isset($department) && $company_id && !$addPwOiD) {
		$q->addWhere('pr.project_company = ' . (int)$company_id);
	}
	if ($project_type > -1) {
		$q->addWhere('pr.project_type = ' . (int)$project_type);
	}
	if (isset($department) && !$addPwOiD) {
		$q->addWhere('project_departments.department_id in ( ' . implode(',', $dept_ids) . ' )');
	}
	if ($user_id && $addProjectsWithAssignedTasks) {
		$q->addWhere('(tu.user_id = ' . (int)$user_id . ' OR pr.project_owner = ' . (int)$user_id . ' )');
	} elseif ($user_id) {
		$q->addWhere('pr.project_owner = ' . (int)$user_id);
	}
	if ($owner > 0) {
		$q->addWhere('pr.project_owner = ' . (int)$owner);
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
	$prj->setAllowedSQL($AppUI->user_id, $q, null, 'pr');
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
	$obj->setAllowedSQL($AppUI->user_id, $q);
	$dpt->setAllowedSQL($AppUI->user_id, $q);
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

// From: modules/projects/project.class.php
function getProjects() {
	global $AppUI;
	$st_projects = array(0 => '');
	$q = new w2p_Database_Query();
	$q->addTable('projects');
	$q->addQuery('project_id, project_name, project_parent');
	$q->addOrder('project_name');
	$st_projects = $q->loadHashList('project_id');
	reset_project_parents($st_projects);
	return $st_projects;
}

// From: modules/projects/project.class.php
function reset_project_parents(&$projects) {
	foreach ($projects as $key => $project) {
		if ($project['project_id'] == $project['project_parent'])
			$projects[$key][2] = '';
	}
}

//This kludgy function echos children projects as threads
// From: modules/projects/project.class.php
function show_st_project(&$a, $level = 0) {
	global $st_projects_arr;
	$st_projects_arr[] = array($a, $level);
}

// From: modules/projects/project.class.php
function find_proj_child(&$tarr, $parent, $level = 0) {
	$level = $level + 1;
	$n = count($tarr);
	for ($x = 0; $x < $n; $x++) {
		if ($tarr[$x]['project_parent'] == $parent && $tarr[$x]['project_parent'] != $tarr[$x]['project_id']) {
			show_st_project($tarr[$x], $level);
			find_proj_child($tarr, $tarr[$x]['project_id'], $level);
		}
	}
}

// From: modules/projects/project.class.php
function getStructuredProjects($original_project_id = 0, $project_status = -1, $active_only = false) {
	global $AppUI, $st_projects_arr;
	$st_projects = array(0 => '');
	$q = new w2p_Database_Query();
	$q->addTable('projects');
	$q->addJoin('companies', '', 'projects.project_company = company_id', 'inner');
	$q->addQuery('DISTINCT(projects.project_id), project_name, project_parent');
	if ($original_project_id) {
		$q->addWhere('project_original_parent = ' . (int)$original_project_id);
	}
	if ($project_status >= 0) {
		$q->addWhere('project_status = ' . (int)$project_status);
	}
	if ($active_only) {
		$q->addWhere('project_active = 1');
	}
	$q->addOrder('project_start_date, project_end_date');

	$obj = new CCompany();
	$obj->setAllowedSQL($AppUI->user_id, $q);
	$dpt = new CDepartment();
	$dpt->setAllowedSQL($AppUI->user_id, $q);
    $q->leftJoin('project_departments', 'pd', 'pd.project_id = projects.project_id' );
    $q->leftJoin('departments', 'd', 'd.dept_id = pd.department_id' );

	$st_projects = $q->loadList();
	$tnums = count($st_projects);
	for ($i = 0; $i < $tnums; $i++) {
		$st_project = $st_projects[$i];
		if (($st_project['project_parent'] == $st_project['project_id'])) {
			show_st_project($st_project);
			find_proj_child($st_projects, $st_project['project_id']);
		}
	}
}

/**
 * getProjectIndex() gets the key nr of a project record within an array of projects finding its primary key within the records so that you can call that array record to get the projects data
 *
 * @param mixed $arraylist array list of project elements to search
 * @param mixed $project_id project id to search for
 * @return int returns the array key of the project record in the array list or false if not found
 */
// From: modules/projects/project.class.php
function getProjectIndex($arraylist, $project_id) {
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
function getDepartmentSelectionList($company_id, $checked_array = array(), $dept_parent = 0, $spaces = 0) {
	global $departments_count, $AppUI;
	$parsed = '';

	if ($departments_count < 6) {
		$departments_count++;
	}

	$depts_list = CDepartment::getDepartmentList($AppUI, $company_id, $dept_parent);

	foreach ($depts_list as $dept_id => $dept_info) {
		$selected = in_array($dept_id, $checked_array) ? ' selected="selected"' : '';

		$parsed .= '<option value="' . $dept_id . '"' . $selected . '>' . str_repeat('&nbsp;', $spaces) . $dept_info['dept_name'] . '</option>';
		$parsed .= getDepartmentSelectionList($company_id, $checked_array, $dept_id, $spaces + 5);
	}

	return $parsed;
}

// From: modules/reports/reports/allocateduserhours.php
function userUsageWeeks() {
	global $task_start_date, $task_end_date, $day_difference, $hours_added, $actual_date, $users, $user_data, $user_usage, $use_assigned_percentage, $user_tasks_counted_in, $task, $start_date, $end_date;

	$task_duration_per_week = $task->getTaskDurationPerWeek($use_assigned_percentage);
	$ted = new w2p_Utilities_Date(Date_Calc::endOfWeek($task_end_date->day, $task_end_date->month, $task_end_date->year));
	$tsd = new w2p_Utilities_Date(Date_Calc::beginOfWeek($task_start_date->day, $task_start_date->month, $task_start_date->year));
	$ed = new w2p_Utilities_Date(Date_Calc::endOfWeek($end_date->day, $end_date->month, $end_date->year));
	$sd = new w2p_Utilities_Date(Date_Calc::beginOfWeek($start_date->day, $start_date->month, $start_date->year));

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
function showWeeks() {
	global $allocated_hours_sum, $end_date, $start_date, $AppUI, $user_list, $user_names, $user_usage, $hideNonWd, $table_header, $table_rows, $df, $working_days_count, $total_hours_capacity, $total_hours_capacity_all;

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
			$actual_date = $sd;
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
function userUsageDays() {
	global $task_start_date, $task_end_date, $day_difference, $hours_added, $actual_date, $users, $user_data, $user_usage, $use_assigned_percentage, $user_tasks_counted_in, $task, $start_date, $end_date;

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
function showDays() {
	global $allocated_hours_sum, $end_date, $start_date, $AppUI, $user_list, $user_names, $user_usage, $hideNonWd, $table_header, $table_rows, $df, $working_days_count, $total_hours_capacity, $total_hours_capacity_all;

	$days_difference = $end_date->dateDiff($start_date);

	$actual_date = $start_date;
	$working_days_count = 0;
	$allocated_hours_sum = 0;

	$table_header = '<tr><th>' . $AppUI->_('User') . '</th>';
	for ($i = 0; $i <= $days_difference; $i++) {
		if (($actual_date->isWorkingDay()) || (!$actual_date->isWorkingDay() && !$hideNonWd)) {
		}
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
			$actual_date = $start_date;
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
function showRow($id = '', $key = 0, $title = '', $value = '') {
  global $canEdit, $sysval_id, $AppUI, $keys;
  global $fixedSysVals;
  $s = '';
  if (($sysval_id == $title) && $canEdit) {
    // edit form
    $s .= '<tr><td><input type="hidden" name="sysval_id" value="' . $title . '" />&nbsp;</td>';
    $s .= '<td valign="top"><a name="'.$title.'"> </a>' . arraySelect($keys, 'sysval_key_id', 'size="1" class="text"', $key) . '</td>';
    $s .= '<td valign="top"><input type="text" name="sysval_title" value="' . w2PformSafe($title) . '" class="text" /></td>';
    $s .= '<td valign="top"><textarea name="sysval_value" class="small" rows="5" cols="40">' . $value . '</textarea></td>';
    $s .= '<td><input type="submit" value="' . $AppUI->_($id ? 'save' : 'add') . '" class="button" /></td><td>&nbsp;</td>';
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
function showRow_keys($id = 0, $name = '', $label = '') {
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
		$s .= '<td><input type="submit" value="' . $AppUI->_($id ? 'edit' : 'add') . '" class="button" /></td>';
		$s .= '<td>&nbsp;</td>';
	} else {
		$s .= '<tr>';
		$s .= '<td width="12">';
		if ($canEdit) {
			$s .= '<a href="?m=system&u=syskeys&a=keys&syskey_id=' . $id . '"><img src="' . w2PfindImage('icons/pencil.gif') . '" alt="edit" border="0"></a>';
			$s .= '</td>' . $CR;
		}
		$s .= '<td>' . $name . '</td>' . $CR;
		$s .= '<td colspan="2">' . $label . '</td>' . $CR;
		$s .= '<td width="16">';
		if ($canEdit) {
			$s .= '<a href="javascript:delIt(' . $id . ')"><img align="absmiddle" src="' . w2PfindImage('icons/trash.gif') . '" width="16" height="16" alt="' . $AppUI->_('delete') . '" border="0"></a>';
		}
		$s .= '</td>' . $CR;
	}
	$s .= '</tr>' . $CR;
	return $s;
}

/*
 *	Authenticator Factory
 *
 */

function &getAuth($auth_mode) {
    switch ($auth_mode) {
        case 'ldap':
            $auth = new w2p_Authenticators_LDAP();
            return $auth;
            break;
        case 'pn':
            $auth = new w2p_Authenticators_PostNuke();
            return $auth;
            break;
        default:
            $auth = new w2p_Authenticators_SQL();
            return $auth;
            break;
    }
}

##
## Returns the best color based on a background color (x is cross-over)
##
function bestColor($bg, $lt = '#ffffff', $dk = '#000000') {
    // cross-over color = x
    $x = 128;
    $r = hexdec(substr($bg, 0, 2));
    $g = hexdec(substr($bg, 2, 2));
    $b = hexdec(substr($bg, 4, 2));

    if ($r < $x && $g < $x || $r < $x && $b < $x || $b < $x && $g < $x) {
        return $lt;
    } else {
        return $dk;
    }
}

##
## returns a select box based on an key,value array where selected is based on key
##
function arraySelect(&$arr, $select_name, $select_attribs, $selected, $translate = false) {
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
function arraySelectTree(&$arr, $select_name, $select_attribs, $selected, $translate = false) {
	global $AppUI;
	reset($arr);

	$children = array();
	// first pass - collect children
	foreach ($arr as $k => $v) {
		$id = $v[0];
		$pt = $v[2];
		$list = isset($children[$pt]) ? $children[$pt] : array();
		array_push($list, $v);
		$children[$pt] = $list;
	}
	$list = tree_recurse($arr[0][2], '', array(), $children);
	return arraySelect($list, $select_name, $select_attribs, $selected, $translate);
}

function tree_recurse($id, $indent, $list, $children) {
	if (isset($children[$id])) {
		foreach ($children[$id] as $v) {
			$id = $v[0];
			$txt = $v[1];
			$pt = $v[2];
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

function projectSelectWithOptGroup($user_id, $select_name, $select_attribs, $selected, $excludeProjWithId = null) {
	global $AppUI;
	$q = new w2p_Database_Query();
	$q->addTable('projects', 'pr');
	$q->addQuery('DISTINCT pr.project_id, co.company_name, project_name');
	if (!empty($excludeProjWithId)) {
		$q->addWhere('pr.project_id <> ' . $excludeProjWithId);
	}
	$proj = new CProject();
	$proj->setAllowedSQL($user_id, $q, null, 'pr');
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
function breadCrumbs(&$arr) {
	global $AppUI;
	$crumbs = array();
	foreach ($arr as $k => $v) {
		$crumbs[] = '<a class="button" href="' . $k . '"><span>' . $AppUI->_($v) . '</span></a>';
	}
	return implode('</td><td align="left" nowrap="nowrap">', $crumbs);
}
##
## generate link for context help -- old version
##
function contextHelp($title, $link = '') {
	return w2PcontextHelp($title, $link);
}

function w2PcontextHelp($title, $link = '') {
	global $AppUI;
	return '<a href="#' . $link . '" onclick="javascript:window.open(\'?m=help&amp;dialog=1&amp;hid=' . $link . '\', \'contexthelp\', \'width=400, height=400, left=50, top=50, scrollbars=yes, resizable=yes\')">' . $AppUI->_($title) . '</a>';
}

function w2PgetUsername($username) {
	return CContact::getContactByUsername($username);
}

function w2PgetUsernameFromID($userId) {
	return CContact::getContactByUserid($userId);
}

function w2PgetUsers() {
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

function w2PgetUsersList($stub = null, $where = null, $orderby = 'contact_first_name, contact_last_name') {
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

	return $q->loadList();
}

function w2PgetUsersHashList($stub = null, $where = null, $orderby = 'contact_first_name, contact_last_name') {
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

	return $q->loadHashList('user_id');
}

##
## displays the configuration array of a module for informational purposes
##
function w2PshowModuleConfig($config) {
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
function w2PfindImage($name, $module = null) {
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
function w2PshowImage($src, $wid = '', $hgt = '', $alt = '', $title = '', $module = null) {
	global $AppUI, $m;

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
	$result .= '<img src="' . $src . '" alt="' . $alt . '" ';
	if ($wid) {
		$result .= ' width="' . $wid . '"';
	}
	if ($hgt) {
		$result .= ' height="' . $hgt . '"';
	}
	$result .= ' border="0" />';
	if (!$alt && !$title) {
		//do nothing
	} elseif ($alt && $title) {
		$result .= w2PendTip();
	} elseif ($alt && !$title) {
		$result .= w2PendTip();
	} elseif (!$alt && $title) {
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

function buildPaginationNav($AppUI, $m, $tab, $xpg_totalrecs, $xpg_pagesize, $page) {
  $xpg_total_pages = ($xpg_totalrecs > $xpg_pagesize) ? ceil($xpg_totalrecs / $xpg_pagesize) : 0;

  $xpg_break = false;
  $xpg_prev_page = $xpg_next_page = 0;

  $s = '<table width="100%" cellspacing="0" cellpadding="0" border="0"><tr>';

  if ($xpg_totalrecs > $xpg_pagesize) {
    $xpg_prev_page = $page - 1;
    $xpg_next_page = $page + 1;
    // left buttoms
    if ($xpg_prev_page > 0) {
      $s .= '<td align="left" width="15%"><a href="./index.php?m=' . $m . '&amp;tab=' . $tab . '&amp;page=1"><img src="' . w2PfindImage('navfirst.gif') . '" border="0" Alt="First Page"></a>&nbsp;&nbsp;';
      $s .= '<a href="./index.php?m=' . $m . '&amp;tab=' . $tab . '&amp;page=' . $xpg_prev_page . '"><img src="' . w2PfindImage('navleft.gif') . '" border="0" Alt="Previous page (' . $xpg_prev_page . ')"></a></td>';
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
      $s .= '<td align="right" width="15%"><a href="./index.php?m=' . $m . '&amp;tab=' . $tab . '&amp;page=' . $xpg_next_page . '"><img src="' . w2PfindImage('navright.gif') . '" border="0" Alt="Next Page (' . $xpg_next_page . ')"></a>&nbsp;&nbsp;';
      $s .= '<a href="./index.php?m=' . $m . '&amp;tab=' . $tab . '&amp;page=' . $xpg_total_pages . '"><img src="' . w2PfindImage('navlast.gif') . '" border="0" Alt="Last Page"></a></td>';
    } else {
      $s .= '<td width="15%">&nbsp;</td></tr>';
    }
  }
  $s .= '</table>';
  return $s;
}

function buildHeaderNavigation($AppUI, $rootTag = '', $innerTag = '', $dividingToken = '', $m = '') {
    $s = '';
    $nav = $AppUI->getMenuModules();
    $perms = $AppUI->acl();

    $s .= ($rootTag != '') ? "<$rootTag id=\"headerNav\">" : '';
    $links = array();
    foreach ($nav as $module) {
        if (canAccess($module['mod_directory'])) {
            $link = ($innerTag != '') ? "<$innerTag>" : '';
            $class = ($m == $module['mod_directory']) ? ' class="module"' : '';
            $link .= '<a href="?m=' . $module['mod_directory'] . '"'.$class.'>' . $AppUI->_($module['mod_ui_name']) . '</a>';
            $link .= ($innerTag != '') ? "</$innerTag>" : '';
            $links[] = $link;
        }
    }
    $s .= implode($dividingToken, $links);
    $s .= ($rootTag != '') ? "</$rootTag>" : '';

    return $s;
}

/**
 * function to return a default value if a variable is not set
 */
function defVal($var, $def) {
	return isset($var) ? $var : $def;
}

#
# add history entries for tracking changes
#
function addHistory($table, $id, $action = 'modify', $description = '', $project_id = 0) {
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

	$q = new w2p_Database_Query;
	$q->addTable('history');
	$q->addInsert('history_action', $action);
	$q->addInsert('history_item', $id);
	$q->addInsert('history_description', $description);
	$q->addInsert('history_user', $AppUI->user_id);
	$q->addInsert('history_date', "'".$q->dbfnNowWithTZ()."'", false, true);
	$q->addInsert('history_project', $project_id);
	$q->addInsert('history_table', $table);
	$q->exec();
	//echo db_error();
}

##
## Looks up a value from the SYSVALS table
##
function w2PgetSysVal($title) {
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
	foreach ($rows as $key => $item) {
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

function w2PuserHasRole($name) {
	global $AppUI;
	$uid = $AppUI->user_id;
	$q = new w2p_Database_Query;
	$q->addTable('roles', 'r');
	$q->addTable('user_roles', 'ur');
	$q->addQuery('r.role_id');
	$q->addWhere('ur.user_id = ' . $uid . ' AND ur.role_id = r.role_id AND r.role_name = \'' . $name . '\'');
	return $q->loadResult();
}

function w2PformatDuration($x) {
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
function w2PsetMicroTime() {
	global $microTimeSet;
	list($usec, $sec) = explode(' ', microtime());
	$microTimeSet = (float)$usec + (float)$sec;
}

function w2PsetExecutionConditions($w2Pconfig) {

	$memoryLimt = ($w2Pconfig['reset_memory_limit'] != '') ? $w2Pconfig['reset_memory_limit'] : '64M';
	ini_set('max_execution_time', 180);
	ini_set('memory_limit', $memoryLimt);
}

/**
 */
function w2PgetMicroDiff() {
	global $microTimeSet;
	$mt = $microTimeSet;
	w2PsetMicroTime();
	return sprintf('%.3f', $microTimeSet - $mt);
}

/**
 * Make text safe to output into double-quote enclosed attirbutes of an HTML tag
 */
function w2PformSafe($txt, $deslash = false) {
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

function formatTime($uts) {
	global $AppUI;
	$date = new w2p_Utilities_Date();
	$date->setDate($uts, DATE_FORMAT_UNIXTIME);
	return $date->format($AppUI->getPref('SHDATEFORMAT'));
}

function file_size($size) {
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
function formatCurrency($number, $format) {
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

function format_backtrace($bt, $file, $line, $msg) {
	echo '<pre>';
	echo 'ERROR: ' . $file . '(' . $line . ') : ' . $msg . "\n";
	echo 'Backtrace:' . "\n";
	foreach ($bt as $level => $frame) {
		echo $level . ' ' . $frame['file'] . ':' . $frame['line'] . ' ' . $frame['function'] . "()\n";
	}
}

function dprint($file, $line, $level, $msg) {
	$max_level = 0;
	$max_level = (int)w2PgetConfig('debug');
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
function findTabModules($module, $file = null) {
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
function findCrumbModules($module, $file = null) {
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

/**
 * @return void
 * @param mixed $var
 * @param char $title
 * @desc Show an estructure (array/object) formatted
 */
function showFVar(&$var, $title = '') {
	echo '<h1>' . $title . '</h1>';
	echo '<pre>';
	print_r($var);
	echo '</pre>';
}

function getUsersArray() {
	return w2PgetUsersHashList();

}

function getUsersCombo($default_user_id = 0, $first_option = 'All users') {
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
function formatHours($hours) {
	global $AppUI;

	$hours = (int)$hours;
	$working_hours = w2PgetConfig('daily_working_hours');

	if ($hours < $working_hours) {
		if ($hours == 1) {
			return '1 ' . $AppUI->_('hour');
		} else {
			return $hours . ' ' . $AppUI->_('hours');
		}
	}

	$hoursPart = $hours % $working_hours;
	$daysPart = (int)($hours / $working_hours);
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
function w2PrequiredFields($requiredFields) {
	global $AppUI, $m;
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
			$buffer .= 'if((foc==false) && (navigator.userAgent.indexOf(\'MSIE\')== -1)) {';
			$buffer .= 'f.' . substr($r, 1, strpos($r, '.', 1) - 1) . '.focus();';
			$buffer .= 'foc=true;}}';
		}
	}
	return $buffer;
}

/**
 * Return the number of bytes represented by a PHP.INI value
 */
function w2PgetBytes($str) {
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
function w2PcheckMem($min = 0, $revert = false) {
	// First of all check if we have the minimum memory requirement.
	$want = w2PgetBytes($GLOBALS['w2Pconfig']['reset_memory_limit']);
	$have = ini_get('memory_limit');
	// Try upping the memory limit based on our config
	ini_set('memory_limit', $GLOBALS['w2Pconfig']['reset_memory_limit']);
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
function w2PHTMLDecode($txt) {
	global $locale_char_set;

	if (!$locale_char_set) {
		$locale_char_set = 'utf-8';
	}

	if (is_object($txt)) {
		foreach (get_object_vars($txt) as $k => $v) {
			$obj->$k = html_entity_decode($v, ENT_COMPAT);
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

function w2PtoolTip($header = '', $tip = '', $raw = false, $id = '') {
	global $AppUI;

    $id = ('' == $id) ? '' : 'id="' . $id . '"';
	if ($raw) {
		$starttip = '<span ' . $id . ' title="&lt;h4&gt;' . nl2br($AppUI->_($header)) . '&lt;/h4&gt; ' . nl2br($AppUI->_($tip)) . '">';
	} else {
		$starttip = '<span ' . $id . ' title="&lt;h4&gt;' . nl2br(ucwords(strtolower($AppUI->_($header)))) . '&lt;/h4&gt; ' . nl2br(strtolower($AppUI->_($tip))) . '">';
	}
	return $starttip;
}

function w2PendTip() {
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
$debug_file = W2P_BASE_DIR . '/files/debug.log';
function w2PwriteDebug($s, $t = '', $f = '?', $l = '?') {
	global $debug, $debug_file;
	if ($debug && ($fp = fopen($debug_file, "at"))) {
		fputs($fp, "Debug message from file [$f], line [$l], at: " . strftime('%H:%S'));
		if ($t) {
			fputs($fp, "\n * * $t * *\n");
		}
		fputs($fp, "\n$s\n\n");
		fclose($fp);
	}
}

function w2p_pluralize($word) {
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


function seconds2HM($sec, $padHours = true) {
    $HM = "";
    // there are 3600 seconds in an hour, so if we
    // divide total seconds by 3600 and throw away
    // the remainder, we've got the number of hours
    $hours = intval(intval($sec) / 3600);
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
    //$seconds = intval($sec % 60);

    // add to $hms, again with a leading 0 if needed
    //$hms .= str_pad($seconds, 2, "0", STR_PAD_LEFT);
    return $HM;
}

function HM2seconds ($HM) {
    list($h, $m) = explode (":", $HM);
    if (intval($h) > 23 && intval($h) < 0) $h = 0;
    if (intval($m) > 59 && intval($m) < 0) $m = 0;
    $seconds = 0;
    $seconds += (intval($h) * 3600);
    $seconds += (intval($m) * 60);
    //$seconds += (intval($s));
    return $seconds;
}

/**
 * Parse the SQL file and get out the timezones from it to use it on the install
 * screen. The SQL file used is: install/sql/mysql/018_add_timezones.sql
 */
function w2PgetTimezonesForInstall() {
    $file = W2P_BASE_DIR . '/install/sql/mysql/018_add_timezones.sql';
    
    $timezones = array();
    
    if(is_file($file) and is_readable($file)) {
        $sql = file_get_contents($file);
        // get it from this kind of a string:
        // (1, 'Timezones', 'Pacific/Auckland', 43200);
        preg_match_all("#\(.*Timezones',\s*'(.*)',.*\);#", $sql, $matchedTimezones);
        
        sort($matchedTimezones[1]);
        
        foreach($matchedTimezones[1] as $timezone) {
            $timezones[$timezone] = $timezone;
        }
    }
    
    return $timezones;
}

//
// New password code based oncode from Mambo Open Source Core
// www.mamboserver.com | mosforge.net
//

function sendNewPass() {
	global $AppUI;

	// ensure no malicous sql gets past
	$checkusername = trim(w2PgetParam($_POST, 'checkusername', ''));
	$checkusername = db_escape($checkusername);
	$confirmEmail = trim(w2PgetParam($_POST, 'checkemail', ''));
	$confirmEmail = strtolower(db_escape($confirmEmail));

	$q = new w2p_Database_Query;
	$q->addTable('users');
	$q->addJoin('contacts', 'con', 'user_contact = contact_id', 'inner');
	$q->addQuery('user_id');
	$q->addWhere('user_username = \'' . $checkusername . '\'');

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
        $q->addWhere("LOWER(contact_email) = '$confirmEmail'");
    }
    /* End Hack */

	if (!($user_id = $q->loadResult()) || !$checkusername || !$confirmEmail) {
		$AppUI->setMsg('Invalid username or email.', UI_MSG_ERROR);
		$AppUI->redirect();
	}

	$newpass = makePass();
	$q->addTable('users');
	$q->addUpdate('user_password', md5($newpass));
	$q->addWhere('user_id=' . $user_id);
	$cur = $q->exec();

	if (!$cur) {
		die('SQL error' . $database->stderr(true));
	} else {
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

function makePass() {
	$makepass = '';
	$salt = 'abchefghjkmnpqrstuvwxyz0123456789';
	srand((double)microtime() * 1000000);
	$i = 0;
	while ($i <= 7) {
		$num = rand() % 33;
		$tmp = substr($salt, $num, 1);
		$makepass = $makepass . $tmp;
		$i++;
	}
	return ($makepass);
}

// from modules/reports/overall.php
function showcompany($company, $restricted = false) {
	global $AppUI, $allpdfdata, $log_start_date, $log_end_date, $log_all;
	$q = new w2p_Database_Query;
	$q->addTable('projects');
	$q->addQuery('project_id, project_name');
	$q->addWhere('project_company = ' . (int)$company);
	$projects = $q->loadHashList();
	$q->clear();

	$q->addTable('companies');
	$q->addQuery('company_name');
	$q->addWhere('company_id = ' . (int)$company);
	$company_name = $q->loadResult();
	$q->clear();

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
		$q->addWhere('project_id = ' . (int)$project);
		$q->addWhere('project_active = 1');
		if (($template_status = w2PgetConfig('template_projects_status_id')) != '') {
			$q->addWhere('project_status <> ' . (int)$template_status);
		}

		if ($log_start_date != 0 && !$log_all) {
			$q->addWhere('task_log_date >=' . $log_start_date);
		}
		if ($log_end_date != 0 && !$log_all) {
			$q->addWhere('task_log_date <=' . $log_end_date);
		}
		if ($restricted) {
			$q->addWhere('task_log_creator = ' . (int)$AppUI->user_id);
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
