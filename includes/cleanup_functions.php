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

//This kludgy function echos children tasks as threads
function showgtask(&$a, $level = 0, $project_id = 0) {
    /* Add tasks to gantt chart */
    global $gantt_arr;
    if ($project_id) {
        $gantt_arr[$project_id][] = array($a, $level);
    } else {
        $gantt_arr[] = array($a, $level);
    }
}

function findgchild(&$tarr, $parent, $level = 0) {
    global $projects;
    $level = $level + 1;
    $n = count($tarr);
    for ($x = 0; $x < $n; $x++) {
        if ($tarr[$x]['task_parent'] == $parent && $tarr[$x]['task_parent'] != $tarr[$x]['task_id']) {
            showgtask($tarr[$x], $level, $tarr[$x]['project_id']);
            findgchild($tarr, $tarr[$x]['task_id'], $level, $tarr[$x]['project_id']);
        }
    }
}

function notifyNewExternalUser($address, $username, $logname, $logpwd) {
	global $AppUI;
	$mail = new w2p_Utilities_Mail();
	if ($mail->ValidEmail($address)) {
		if ($mail->ValidEmail($AppUI->user_email)) {
			$email = $AppUI->user_email;
		} else {
			$email = 'web2project@web2project.net';
		}

		$mail->To($address);
		$mail->Subject('New Account Created');
		$mail->Body('You have signed up for a new account on ' . w2PgetConfig('company_name') . ".\n\n" . "Once the administrator approves your request, you will receive an email with confirmation.\n" . "Your login information are below for your own record:\n\n" . 'Username:	' . $logname . "\n" . 'Password:	' . $logpwd . "\n\n" . "You may login at the following URL: " . W2P_BASE_URL . "\n\n" . "Thank you very much.\n\n" . 'The ' . w2PgetConfig('company_name') . " Support Staff.\n\n" . '****PLEASE KEEP THIS EMAIL FOR YOUR RECORDS****');
		$mail->Send();
	}
}

function notifyHR($address, $username, $uaddress, $uusername, $logname, $logpwd, $userid) {
	global $AppUI;
	$mail = new w2p_Utilities_Mail();
	if ($mail->ValidEmail($address)) {
		if ($mail->ValidEmail($AppUI->user_email)) {
			$email = $AppUI->user_email;
		} else {
			$email = 'web2project@web2project.net';
		}

		$mail->To($address);
		$mail->Subject('New External User Created');
		$mail->Body('A new user has signed up on ' . w2PgetConfig('company_name') . ". Please go through the user details below:\n" . 'Name:	' . $uusername . "\n" . 'Username:	' . $logname . "\n" . 'Email:	' . $uaddress . "\n\n" . 'You may check this account at the following URL: ' . W2P_BASE_URL . '/index.php?m=admin&a=viewuser&user_id=' . $userid . "\n\n" . "Thank you very much.\n\n" . 'The ' . w2PgetConfig('company_name') . " Taskforce.\n\n" . '****PLEASE KEEP THIS EMAIL FOR YOUR RECORDS****');
		$mail->Send();
	}
}

function notifyNewUser($address, $username) {
	global $AppUI;
	$mail = new w2p_Utilities_Mail();
	if ($mail->ValidEmail($address)) {
		if ($mail->ValidEmail($AppUI->user_email)) {
			$email = $AppUI->user_email;
		} else {
			return false;
		}

		$mail->To($address);
        $emailManager = new w2p_Output_EmailManager();
        $body = $emailManager->getNotifyNewUser($username);
        $mail->Subject('New Account Created');
		$mail->Body($body);
		$mail->Send();
	}
}

function notifyNewUserCredentials($address, $username, $logname, $logpwd) {
	global $AppUI, $w2Pconfig;
	$mail = new w2p_Utilities_Mail();
	if ($mail->ValidEmail($address)) {
		if ($mail->ValidEmail($AppUI->user_email)) {
			$email = $AppUI->user_email;
		} else {
			$email = "web2project@" . $AppUI->cfg['site_domain'];
		}

		$mail->To($address);
		$mail->Subject('New Account Created - web2Project Project Management System');
		$mail->Body($username . ",\n\n" . "An access account has been created for you in our web2Project project management system.\n\n" . "You can access it here at " . w2PgetConfig('base_url') . "\n\n" . "Your username is: " . $logname . "\n" . "Your password is: " . $logpwd . "\n\n" .
			"This account will allow you to see and interact with projects. If you have any questions please contact us.");
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
/*
* 	Convert string char (ref : Vbulletin #3987)
*/
function strJpGraph($text) {
    global $locale_char_set;
    trigger_error("The strJpGraph function has been deprecated and will be removed in v3.0.", E_USER_NOTICE );
    if ( $locale_char_set=='utf-8' && function_exists("utf8_decode") ) {
        return utf8_decode($text);
    } else {
        return $text;
    }
}
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

function atoi($a) {
	return $a + 0;
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

// from modules/tasks/addedit.php
function getSpaces($amount) {
	if ($amount == 0) {
		return '';
	}
	return str_repeat('&nbsp;', $amount);
}

// from modules/tasks/addedit.php
function constructTaskTree($task_data, $depth = 0) {
	global $projTasks, $all_tasks, $parents, $task_parent_options, $task_parent, $task_id;

	$projTasks[$task_data['task_id']] = $task_data['task_name'];

	$selected = $task_data['task_id'] == $task_parent ? 'selected="selected"' : '';
	$task_data['task_name'] = mb_strlen($task_data[1]) > 45 ? mb_substr($task_data['task_name'], 0, 45) . '...' : $task_data['task_name'];

	$task_parent_options .= '<option value="' . $task_data['task_id'] . '" ' . $selected . '>' . getSpaces($depth * 3) . w2PFormSafe($task_data['task_name']) . '</option>';

	if (isset($parents[$task_data['task_id']])) {
		foreach ($parents[$task_data['task_id']] as $child_task) {
			if ($child_task != $task_id) {
				constructTaskTree($all_tasks[$child_task], ($depth + 1));
			}
		}
	}
}

// from modules/tasks/addedit.php
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
	$canAccess = canTaskAccess($arr['task_id'], $arr['task_access'], $arr['task_owner']);
	if (!$canAccess) {
		return (false);
	}

	$now = new w2p_Utilities_Date();
	$tf = $AppUI->getPref('TIMEFORMAT');
	$df = $AppUI->getPref('SHDATEFORMAT');
	$fdf = $df . ' ' . $tf;
	$show_all_assignees = w2PgetConfig('show_all_task_assignees', false);

	$start_date = intval($arr['task_start_date']) ? new w2p_Utilities_Date($AppUI->formatTZAwareTime($arr['task_start_date'], '%Y-%m-%d %T')) : null;
	$end_date = intval($arr['task_end_date']) ? new w2p_Utilities_Date($AppUI->formatTZAwareTime($arr['task_end_date'], '%Y-%m-%d %T')) : null;
	$last_update = isset($arr['last_update']) && intval($arr['last_update']) ? new w2p_Utilities_Date( $AppUI->formatTZAwareTime($arr['last_update'], '%Y-%m-%d %T')) : null;

	// prepare coloured highlight of task time information
	$sign = 1;
	$style = '';
	if ($start_date) {
		if (!$end_date) {
			/*
			** end date calc has been moved to calcEndByStartAndDuration()-function
			** called from array_csort and tasks.php
			** perhaps this fallback if-clause could be deleted in the future,
			** didn't want to remove it shortly before the 2.0.2
			*/
			$end_date = new w2p_Utilities_Date('0000-00-00 00:00:00');
		}

		if ($now->after($start_date) && $arr['task_percent_complete'] == 0) {
			$style = 'background-color:#ffeebb';
		} elseif ($now->after($start_date) && $arr['task_percent_complete'] < 100) {
			$style = 'background-color:#e6eedd';
		}

		if ($now->after($end_date)) {
			$sign = -1;
			$style = 'background-color:#cc6666;color:#ffffff';
		}
		if ($arr['task_percent_complete'] == 100) {
			$style = 'background-color:#aaddaa; color:#00000';
		}

		$days = $now->dateDiff($end_date) * $sign;
	}

    $jsTaskId = 'project_' . $arr['task_project'] . '_level-' . $level . '-task_' . $arr['task_id'] . '_';
	if ($expanded) {
		$s = '<tr id="' . $jsTaskId . '" >';
	} else {
		$s = '<tr id="' . $jsTaskId . '" ' . (($level > 0 && !($m == 'tasks' && $a == 'view')) ? 'style="display:none"' : '') . '>';
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
	if ($arr['task_log_problem'] > 0) {
		$s .= ('<td valign="middle"><a href="?m=tasks&amp;a=view&amp;task_id=' . $arr['task_id'] . '&amp;tab=0&amp;problem=1">' . w2PshowImage('icons/dialog-warning5.png', 16, 16, 'Problem', 'Problem!') . '</a></td>');
	} elseif ($canViewLog && $arr['task_dynamic'] != 1 && 0 == $arr['task_represents_project']) {
		$s .= ('<td align="center"><a href="?m=tasks&amp;a=view&amp;task_id=' . $arr['task_id'] . '&amp;tab=1">' . w2PtoolTip('Add Log', 'create a new log record against this task') . w2PshowImage('edit_add.png') . w2PendTip() . '</a></td>');
	} else {
		$s .= '<td align="center">' . $AppUI->_('-') . '</td>';
	}
	// percent complete and priority
	$s .= ('<td align="right">' . (int) $arr['task_percent_complete'] . '%</td><td align="center" nowrap="nowrap">');
	if ($arr['task_priority'] < 0) {
		$s .= '<img src="' . w2PfindImage('icons/priority-' . -$arr['task_priority'] . '.gif') . '" alt="" />';
	} elseif ($arr['task_priority'] > 0) {
		$s .= '<img src="' . w2PfindImage('icons/priority+' . $arr['task_priority'] . '.gif') . '" alt="" />';
	}
	$s .= '</td><td align="center" nowrap="nowrap">';
	if (isset($arr['user_task_priority'])) {
		if ($arr['user_task_priority'] < 0) {
			$s .= '<img src="' . w2PfindImage('icons/priority-' . -$arr['user_task_priority'] . '.gif') . '" alt="" />';
		} elseif ($arr['task_priority'] > 0) {
			$s .= '<img src="' . w2PfindImage('icons/priority+' . $arr['user_task_priority'] . '.gif') . '" alt="" />';
		}
	}
	$s .= '</td>';

	// dots
	$s .= '<td width="' . (($today_view) ? '50%' : '90%') . '">';
	//level
	if ($level == -1) {
		$s .= '...';
	}
	for ($y = 0; $y < $level; $y++) {
		if ($y + 1 == $level) {
			$s .= '<img src="' . w2PfindImage('corner-dots.gif') . '" width="16" height="12" border="0" alt="">';
		} else {
			$s .= '';
		}
	}
	if ($arr['task_description']) {
		$s .= w2PtoolTip('Task Description', $arr['task_description'], true);
	}

	$open_link = '<a href="javascript: void(0);"><img onclick="expand_collapse(\'' . $jsTaskId . '\', \'tblProjects\',\'\',' . ($level + 1) . ');" id="' . $jsTaskId . '_collapse" src="' . w2PfindImage('icons/collapse.gif') . '" border="0" align="center" ' . (!$expanded ? 'style="display:none"' : '') . ' alt="" /><img onclick="expand_collapse(\'' . $jsTaskId . '\', \'tblProjects\',\'\',' . ($level + 1) . ');" id="' . $jsTaskId . '_expand" src="' . w2PfindImage('icons/expand.gif') . '" border="0" align="center" ' . ($expanded ? 'style="display:none"' : '') . ' alt="" /></a>';
	if ($arr['task_nr_of_children']) {
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
		$s .= ('<td nowrap="nowrap" align="center">' . '<a href="?m=admin&amp;a=viewuser&amp;user_id=' . $arr['user_id'] . '">' . $arr['owner'] . '</a>' . '</td>');
	}

	if (count($arr['task_assigned_users'])) {
        $assigned_users = $arr['task_assigned_users'];
        $a_u_tmp_array = array();
		if ($show_all_assignees) {
			$s .= '<td align="center">';
			foreach ($assigned_users as $val) {
				$a_u_tmp_array[] = ('<a href="?m=admin&amp;a=viewuser&amp;user_id=' . $val['user_id'] . '"' . 'title="' . (w2PgetConfig('check_overallocation') ? $AppUI->_('Extent of Assignment') . ':' . $userAlloc[$val['user_id']]['charge'] . '%; ' . $AppUI->_('Free Capacity') . ':' . $userAlloc[$val['user_id']]['freeCapacity'] . '%' : '') . '">' . $val['assignee'] . ' (' . $val['perc_assignment'] . '%)</a>');
			}
			$s .= join(', ', $a_u_tmp_array) . '</td>';
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
    $s .= '<td nowrap="nowrap" align="center" style="' . $style . '">' . ($start_date ? $start_date->format($fdf) : '-') . '</td>';
    $s .= '<td align="right" nowrap="nowrap" style="' . $style . '">' . $arr['task_duration'] . ' ' . mb_substr($AppUI->_($durnTypes[$arr['task_duration_type']]), 0, 1) . '</td>';
    $s .= '<td nowrap="nowrap" align="center" style="' . $style . '">' . ($end_date ? $end_date->format($fdf) : '-') . '</td>';
	if ($today_view) {
		$s .= ('<td nowrap="nowrap" align="center" style="' . $style . '">' . $arr['task_due_in'] . '</td>');
	} elseif ($history_active) {
		$s .= ('<td nowrap="nowrap" align="center" style="' . $style . '">' . ($last_update ? $last_update->format($fdf) : '-') . '</td>');
	}

	// Assignment checkbox
	if ($showEditCheckbox) {
		$s .= ('<td align="center">' . '<input type="checkbox" name="selected_task[' . $arr['task_id'] . ']" value="' . $arr['task_id'] . '"/></td>');
	}
	$s .= '</tr>'."\n";
	echo $s;
}
// from modules/tasks/tasks.class.php
function findchild(&$tarr, $parent, $level = 0) {
	global $shown_tasks;

	$level = $level + 1;
	$n = count($tarr);

	for ($x = 0; $x < $n; $x++) {
		if ($tarr[$x]['task_parent'] == $parent && $tarr[$x]['task_parent'] != $tarr[$x]['task_id']) {
			showtask($tarr[$x], $level, true);
			$shown_tasks[$tarr[$x]['task_id']] = $tarr[$x]['task_id'];
			findchild($tarr, $tarr[$x]['task_id'], $level);
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
	echo $s.'</a>';
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
function canTaskAccess($task_id, $task_access, $task_owner) {
	global $AppUI;
	$q = new w2p_Database_Query;

	if (!$task_id || !isset($task_access)) {
		return false;
	}

	//if for some weird reason we have tasks without an owner, lets make them visible at least for admins, or else we take the risk of having phantom tasks.
	if (!$task_owner) {
		$task_owner = $AppUI->user_id;
	}

	$user_id = $AppUI->user_id;
	// Let's see if this user has admin privileges, if so return true
	if ($AppUI->user_is_admin) {
		return true;
	}

	switch ($task_access) {
		case 0:
			// public
			$retval = true;
			break;
		case 1:
			// protected
			$q->addTable('users');
			$q->addQuery('user_company');
			$q->addWhere('user_id=' . (int)$user_id . ' OR user_id=' . (int)$task_owner);
			$user_owner_companies = $q->loadColumn();
			$q->clear();
			$company_match = true;
			foreach ($user_owner_companies as $current_company) {
				$company_match = $company_match && ((!(isset($last_company))) || $last_company == $current_company);
				$last_company = $current_company;
			}

		case 2:
			// participant
			$company_match = ((isset($company_match)) ? $company_match : true);
			$q->addTable('user_tasks');
			$q->addQuery('COUNT(task_id)');
			$q->addWhere('user_id=' . (int)$user_id . ' AND task_id=' . (int)$task_id);
			$count = $q->loadResult();
			$q->clear();
			$retval = (($company_match && $count > 0) || ($count > 0) || $task_owner == $user_id);
			break;
		case 3:
			// private
			$retval = ($task_owner == $user_id);
			break;
		default:
			$retval = false;
			break;
	}

	return $retval;
}