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

//
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

function findgchild(&$tarr, $parent, $level = 0) {
    global $projects;
    $level = $level + 1;
    $n = count($tarr);
    for ($x = 0; $x < $n; $x++) {
        if ($tarr[$x]['task_parent'] == $parent && $tarr[$x]['task_parent'] != $tarr[$x]['task_id']) {
            showgtask($tarr[$x], $level, $tarr[$x]['project_id']);
            findgchild($tarr, $tarr[$x]['task_id'], $tarr[$x]['project_id'], $level);
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

// from modules/tasks/addedit.php and modules/projectdesigners/vw_actions.php
function getSpaces($amount) {
	if ($amount == 0) {
		return '';
	}
	return str_repeat('&nbsp;', $amount);
}

// from modules/tasks/addedit.php and modules/projectdesigners/vw_actions.php
function constructTaskTree($task_data, $depth = 0) {
	global $projTasks, $all_tasks, $parents, $task_parent_options, $task_parent, $task_id;

	$projTasks[$task_data['task_id']] = $task_data['task_name'];
    $task_data['task_name'] = mb_strlen($task_data[1]) > 45 ? mb_substr($task_data['task_name'], 0, 45) . '...' : $task_data['task_name'];
	$selected = $task_data['task_id'] == $task_parent ? 'selected="selected"' : '';

	//$task_parent_options .= '<option value="' . $task_data['task_id'] . '" ' . $selected . '>' . getSpaces($depth * 3) . w2PFormSafe($task_data['task_name']) . '</option>';

	if (isset($parents[$task_data['task_id']])) {
		foreach ($parents[$task_data['task_id']] as $child_task) {
			if ($child_task != $task_id) {
				constructTaskTree($all_tasks[$child_task], ($depth + 1));
			}
		}
	}
}
function constructTaskTree_pd($task_data, $parents, $all_tasks, $depth = 0) {
	global $projTasks, $all_tasks, $task_parent_options, $task_parent, $task_id;

	$projTasks[$task_data['task_id']] = $task_data['task_name'];
	$task_data['task_name'] = mb_strlen($task_data[1]) > 45 ? mb_substr($task_data['task_name'], 0, 45) . "..." : $task_data['task_name'];
	$task_parent_options .= '<option value="' . $task_data['task_id'] . '" >' . getSpaces($depth * 3) . w2PFormSafe($task_data['task_name']) . '</option>';

	if (isset($parents[$task_data['task_id']])) {
		foreach ($parents[$task_data['task_id']] as $child_task) {
			if ($child_task != $task_id)
				constructTaskTree_pd($all_tasks[$child_task], $parents, $all_tasks, ($depth + 1));
		}
	}
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
		} elseif ($arr['user_task_priority'] > 0) {
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

	$zi++;
	$users = $task->task_assigned_users;
	$task->userPriority = $task->getUserSpecificTaskPriority($user_id);
	$project = $task->getProject();
	$tmp = '<tr>';
	$tmp .= '<td align="center" nowrap="nowrap">';
	$tmp .= '<input type="checkbox" name="selected_task[' . $task->task_id . ']" value="' . $task->task_id . '" />';
	$tmp .= '</td>';
	$tmp .= '<td align="center" nowrap="nowrap">';
	if ($task->userPriority < 0) {
		$tmp .= '<img src="' . w2PfindImage('icons/priority-' . -$task->userPriority . '.gif') . '" width="13" height="16" alt="">';
	} elseif ($task->userPriority > 0) {
		$tmp .= '<img src="' . w2PfindImage('icons/priority+' . $task->userPriority . '.gif') . '" width="13" height="16" alt="">';
	}
	$tmp .= '</td>';
	$tmp .= '<td>';

	for ($i = 0; $i < $level; $i++) {
		$tmp .= '&#160';
	}

	if ($task->task_milestone == true) {
		$tmp .= '<b>';
	}
	if ($level >= 1) {
		$tmp .= w2PshowImage('corner-dots.gif', 16, 12, 'Subtask', '', 'tasks') . '&nbsp;';
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
	$tmp .= '<td align="right" nowrap="nowrap">';
	$tmp .= $task->task_duration . '&nbsp;' . mb_substr($AppUI->_($durnTypes[$task->task_duration_type]),0,1);
	$tmp .= '</td>';
	$tmp .= '<td align="center" nowrap="nowrap">';
	$dt = new w2p_Utilities_Date($AppUI->formatTZAwareTime($task->task_start_date, '%Y-%m-%d %T'));
	$tmp .= $dt->format($df);
	$tmp .= '&#160&#160&#160</td>';
	$tmp .= '<td align="right" nowrap="nowrap">';
	$ed = new w2p_Utilities_Date($AppUI->formatTZAwareTime($task->task_end_date, '%Y-%m-%d %T'));
	$dt = $now->dateDiff($ed);
	$sgn = $now->compare($ed, $now);
	$tmp .= ($dt * $sgn);
	$tmp .= '</td>';
	if ($display_week_hours) {
		$tmp .= displayWeeks($list, $task, $level, $fromPeriod, $toPeriod);
	}
	$tmp .= '<td>';
	$sep = $us = '';
	foreach ($users as $key => $row) {
		if ($row['user_id']) {
			$us .= '<a href="?m=admin&a=viewuser&user_id=' . $row[0] . '">' . $sep . $row['contact_first_name'] . '&nbsp;' . $row['contact_last_name'] . '&nbsp;(' . $row['perc_assignment'] . '%)</a>';
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
	$df = $AppUI->getPref('SHDATEFORMAT');

	$tmp .= '<td nowrap="nowrap">';
	$dt = new w2p_Utilities_Date($task->task_start_date);
	$tmp .= $dt->format($df);
	$tmp .= '&#160&#160&#160</td>';
	$tmp .= '<td nowrap="nowrap">';
	$dt = new w2p_Utilities_Date($task->task_end_date);
	$tmp .= $dt->format($df);
	$tmp .= '</td>';
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
			} else {
				if ($level == 1 and hasChildren($list, $task)) {
					$color = '#9090FF';
				}
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
	echo $s;
}

// from modules/smartsearch/smartsearch.class.php
function highlight($text, $keyval) {
	global $ssearch;

	$txt = $text;
	$hicolor = array('#FFFF66', '#ADD8E6', '#90EE8A', '#FF99FF', '#FFA500', '#ADFF2F', '#00FFFF', '#FF69B4');
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
					$txt = preg_replace('/'.recode2regexp_utf8($key).'/i', '<span style="background:' . $hicolor[$key_idx] . '" >\\0</span>', $txt);
				} else {
					$txt = preg_replace('/'.(recode2regexp_utf8($key)).'/', '<span style="background:' . $hicolor[$key_idx] . '" >\\0</span>', $txt);
				}
			} elseif (!isset($ssearch['ignore_specchar']) || ($ssearch['ignore_specchar'] == '')) {
				if ($ssearch['ignore_case'] == 'on') {
					$txt = preg_replace('/'.$key.'/i', '<span style="background:' . $hicolor[$key_idx] . '" >\\0</span>', $txt);
				} else {
					$txt = preg_replace('/'.$key.'/', '<span style="background:' . $hicolor[$key_idx] . '" >\\0</span>', $txt);
				}
			} else {
				$txt = preg_replace('/'.$key.'/i', '<span style="background:' . $hicolor[$key_idx] . '" >\\0</span>', $txt);
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

// from modules/public/contact_selector.php
function remove_invalid($arr) {
	$result = array();
	foreach ($arr as $val) {
		if (!empty($val) && mb_trim($val) !== '') {
			$result[] = $val;
		}
	}
	return $result;
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
	$buffer .= $s;

	//	echo $s;
}

//comes from modules/departments/departments.class.php
//recursive function to display children departments.
function findchilddept(&$tarr, $parent, $level = 1) {
	$level = $level + 1;
	$n = count($tarr);
	for ($x = 0; $x < $n; $x++) {
		if ($tarr[$x]['dept_parent'] == $parent && $tarr[$x]['dept_parent'] != $tarr[$x]['dept_id']) {
			showchilddept($tarr[$x], $level);
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
function clash_process(CAppUI $AppUI) {
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
function clash_mail(CAppUI $AppUI) {
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
function clash_accept(CAppUI $AppUI) {
	global $do_redirect;

	$AppUI->setMsg('Event');
	$obj = new CEvent;
	$obj->bind($_SESSION['add_event_post']);
	$GLOBALS['a'] = $_SESSION['add_event_caller'];
	$is_new = ($obj->event_id == 0);
    $result = $obj->store($AppUI);

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
function clash_cancel(CAppUI $AppUI) {
	global $a;
	$a = $_SESSION['add_event_caller'];
	clear_clash();
	$AppUI->setMsg($AppUI->_('Event Cancelled'), UI_MSG_ALERT);
	$AppUI->redirect();
}

// function renamed to avoid naming clash
// From:  modules/companies/vw_depts.php
function showchilddept_comp(&$a, $level = 0) {
	global $AppUI;
	$s = '
	<td>
		<a href="./index.php?m=departments&amp;a=addedit&amp;dept_id=' . $a["dept_id"] . '" title="' . $AppUI->_('edit') . '">
			' . w2PshowImage('icons/stock_edit-16.png', 16, 16, '') . '
	</td>
	<td>';

	for ($y = 0; $y < $level; $y++) {
		if ($y + 1 == $level) {
			$s .= '<img src="' . w2PfindImage('corner-dots.gif') . '" width="16" height="12" border="0" alt="">';
		} else {
			$s .= '<img src="' . w2PfindImage('shim.gif') . '" width="16" height="12" border="0" alt="">';
		}
	}

	$s .= '<a href="./index.php?m=departments&a=view&dept_id=' . $a['dept_id'] . '">' . $a['dept_name'] . '</a>';
	$s .= '</td>';
	$s .= '<td align="center">' . ($a['dept_users'] ? $a['dept_users'] : '') . '</td>';

	echo '<tr>' . $s . '</tr>';
}

// function renamed to avoid naming clash
// From:  modules/companies/vw_depts.php
function findchilddept_comp(&$tarr, $parent, $level = 0) {
	$level = $level + 1;
	$n = count($tarr);
	for ($x = 0; $x < $n; $x++) {
		if ($tarr[$x]['dept_parent'] == $parent && $tarr[$x]['dept_parent'] != $tarr[$x]['dept_id']) {
			showchilddept_comp($tarr[$x], $level);
			findchilddept_comp($tarr, $tarr[$x]['dept_id'], $level);
		}
	}
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

    $file_folder = new CFileFolder();
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
		// call this function again to display this
		// child's children
		// getFolders *always* returns true, so there's no point in checking it
		//$s .= getFolders($row['file_folder_id'], $level + 1).'</li></ul>';
	}
	/*
	 *  getFolders  would *alway* return true and would echo the results.  It
	 * makes more sense to simply return the results.  Then the calling code can
	 * echo it, capture it for parsing, or whatever else needs to be done.  There
	 * should be less inadvertent actions as a result.
	 */
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
	$q->addQuery('f.*, max(f.file_id) as latest_id, count(f.file_version) as file_versions, round(max(file_version), 2) as file_lastversion');
	$q->addQuery('ff.*');
	$q->addTable('files', 'f');
	$q->addJoin('file_folders', 'ff', 'ff.file_folder_id = file_folder');
	$q->addJoin('projects', 'p', 'p.project_id = file_project');
	$q->addJoin('tasks', 't', 't.task_id = file_task');
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
	$q->setLimit($xpg_pagesize, $xpg_min);
	$q->addWhere('file_folder = ' . (int)$folder_id);
	$q->addGroup('file_version_id DESC');

	$qv = new w2p_Database_Query();
	$qv->addTable('files');
	$qv->addQuery('file_id, file_version, file_project, file_name, file_task,
		file_description, u.user_username as file_owner, file_size, file_category,
		task_name, file_version_id,  file_checkout, file_co_reason, file_type,
		file_date, cu.user_username as co_user, project_name,
		project_color_identifier, project_owner, con.contact_first_name,
		con.contact_last_name, co.contact_first_name as co_contact_first_name,
		co.contact_last_name as co_contact_last_name ');
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

	$s = '
		<table width="100%" border="0" cellpadding="2" cellspacing="1" class="tbl">
		<tr>
			<th nowrap="nowrap">' . $AppUI->_('File Name') . '</th>
			<th>' . $AppUI->_('Description') . '</th>
			<th>' . $AppUI->_('Versions') . '</th>
		    <th>' . $AppUI->_('Category') . '</th>
			<th nowrap="nowrap">' . $AppUI->_('Task Name') . '</th>
			<th>' . $AppUI->_('Owner') . '</th>
			<th>' . $AppUI->_('Size') . '</th>
			<th>' . $AppUI->_('Type') . '</a></th>
			<th>' . $AppUI->_('Date') . '</th>
	    	<th nowrap="nowrap">' . $AppUI->_('co Reason') . '</th>
	    	<th>' . $AppUI->_('co') . '</th>
			<th nowrap="nowrap" width="5%"></th>
			<th nowrap="nowrap" width="1"></th>
		</tr>';

	$fp = -1;
	$file_date = new w2p_Utilities_Date();

	$id = 0;
	foreach ($files as $row) {
		$latest_file = $file_versions[$row['latest_id']];
		$file_date = new w2p_Utilities_Date($latest_file['file_date']);

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

		$s .= '<tr>
				<td nowrap="8%">
                    <form name="frm_remove_file_' . $latest_file['file_id'] . '" action="?m=files" method="post" accept-charset="utf-8">
                        <input type="hidden" name="dosql" value="do_file_aed" />
                        <input type="hidden" name="del" value="1" />
                        <input type="hidden" name="file_id" value="' . $latest_file['file_id'] . '" />
                        <input type="hidden" name="redirect" value="' . $current_uri . '" />
                    </form>
                    <form name="frm_duplicate_file_' . $latest_file['file_id'] . '" action="?m=files" method="post" accept-charset="utf-8">
                        <input type="hidden" name="dosql" value="do_file_aed" />
                        <input type="hidden" name="duplicate" value="1" />
                        <input type="hidden" name="file_id" value="' . $latest_file['file_id'] . '" />
                        <input type="hidden" name="redirect" value="' . $current_uri . '" />
                    </form>
                ';
        $junkFile = new CFile(); // TODO: This is just to get getIcon included..
		$file_icon = getIcon($row['file_type']);
		$s .= '<a href="./fileviewer.php?file_id=' . $latest_file['file_id'] . '"><img border="0" width="16" heigth="16" src="' . w2PfindImage($file_icon, 'files') . '" alt="" />&nbsp;' . $latest_file['file_name'] . '</a></td>';
		$s .= '<td width="20%">' . w2p_textarea($latest_file['file_description']) . '</td><td width="5%" nowrap="nowrap" align="right">';
		$hidden_table = '';
		$s .= $row['file_lastversion'];
		if ($row['file_versions'] > 1) {
			$s .= ' <a href="javascript: void(0);" onClick="expand(\'versions_' . $latest_file['file_id'] . '\'); ">(' . $row['file_versions'] . ')</a>';
			$hidden_table = '<tr><td colspan="20">
							<table style="display: none" id="versions_' . $latest_file['file_id'] . '" width="100%" border="0" cellpadding="2" cellspacing="1" class="tbl">
							<tr>
							        <th nowrap="nowrap">' . $AppUI->_('File Name') . '</th>
							        <th>' . $AppUI->_('Description') . '</th>
							        <th>' . $AppUI->_('Versions') . '</th>
							        <th>' . $AppUI->_('Category') . '</th>
									<th>' . $AppUI->_('Folder') . '</th>
							        <th>' . $AppUI->_('Task Name') . '</th>
							        <th>' . $AppUI->_('Owner') . '</th>
							        <th>' . $AppUI->_('Size') . '</th>
							        <th>' . $AppUI->_('Type') . '</a></th>
							        <th>' . $AppUI->_('Date') . '</th>
							</tr>';
			foreach ($file_versions as $file) {
				if ($file['file_version_id'] == $latest_file['file_version_id']) {
					$file_icon = getIcon($file['file_type']);
					$hdate = new w2p_Utilities_Date($file['file_date']);
					$hidden_table .= '<tr><td nowrap="8%"><a href="./fileviewer.php?file_id=' . $file['file_id'] . '" title="' . $file['file_description'] . '">' . '<img border="0" width="16" heigth="16" src="' . w2PfindImage($file_icon, 'files') . '" alt="" />&nbsp;' . $file['file_name'] . '
					  </a></td>
					  <td width="20%">' . $file['file_description'] . '</td>
					  <td width="5%" nowrap="nowrap" align="right">' . $file['file_version'] . '</td>
					  <td nowrap="nowrap" align="left">' . $file_types[$file['file_category']] . '</td>
					  <td nowrap="nowrap" align="left">' . (($file['file_folder_name'] != '') ? '<a href="' . W2P_BASE_URL . '/index.php?m=files&tab=' . (count($file_types) + 1) . '&folder=' . $file['file_folder_id'] . '">' . w2PshowImage('folder5_small.png', '16', '16', 'folder icon', 'show only this folder', 'files') . $file['file_folder_name'] . '</a>' : 'Root') . '</td>
					  <td nowrap="nowrap" align="left"><a href="./index.php?m=tasks&a=view&task_id=' . $file['file_task'] . '">' . $file['task_name'] . '</a></td>
					  <td nowrap="nowrap">' . $file['contact_first_name'] . ' ' . $file['contact_last_name'] . '</td>
					  <td width="5%" nowrap="nowrap" align="right">' . file_size(intval($file['file_size'])) . '</td>
					  <td nowrap="nowrap">' . $file['file_type'] . '</td>
					  <td width="5%" nowrap="nowrap" align="center">' . $AppUI->formatTZAwareTime($file['file_date'], $df . ' ' . $tf) . '</td>';
					if ($canEdit && $w2Pconfig['files_show_versions_edit']) {
						$hidden_table .= '<a href="./index.php?m=files&a=addedit&file_id=' . $file['file_id'] . '">' . w2PshowImage('kedit.png', '16', '16', 'edit file', 'edit file', 'files') . "</a>";
					}
					$hidden_table .= '</td><tr>';
				}
			}
			$hidden_table .= '</table>';
		}
		$s .= '</td>
				<td width="10%" nowrap="nowrap" align="left">' . $file_types[$file['file_category']] . '</td>
				<td nowrap="nowrap" align="left"><a href="./index.php?m=tasks&a=view&task_id=' . $latest_file['file_task'] . '">' . $latest_file['task_name'] . '</a></td>
				<td nowrap="nowrap">' . $latest_file['contact_first_name'] . ' ' . $latest_file['contact_last_name'] . '</td>
				<td width="5%" nowrap="nowrap" align="right">' . intval($latest_file['file_size'] / 1024) . ' kb</td>
				<td nowrap="nowrap">' . $latest_file['file_type'] . '</td>
				<td nowrap="nowrap" align="center">' . $AppUI->formatTZAwareTime($latest_file['file_date'], $df . ' ' . $tf) . '</td>
				<td width="10%">' . $latest_file['file_co_reason'] . '</td>
				<td nowrap="nowrap">';
        if (empty($row['file_checkout'])) {
        	$s .= '<a href="?m=files&a=co&file_id=' . $latest_file['file_id'] . '">' . w2PshowImage('up.png', '16', '16', 'checkout', 'checkout file', 'files') . '</a>';
        } elseif ($row['file_checkout'] == $AppUI->user_id) {
            $s .= '<a href="?m=files&a=addedit&ci=1&file_id=' . $latest_file['file_id'] . '">' . w2PshowImage('down.png', '16', '16', 'checkin', 'checkin file', 'files') . '</a>';
        } else {
			if ($latest_file['file_checkout'] == 'final') {
				$s .= 'final';
			} else {
				$s .= $latest_file['co_contact_first_name'] . ' ' . $latest_file['co_contact_last_name'] . '<br>(' . $latest_file['co_user'] . ')';
			}
		}
		$s .= '</td><td nowrap="nowrap" width="50">';
		if ($canEdit && (empty($latest_file['file_checkout']) || ($latest_file['file_checkout'] == 'final' && ($canEdit || $latest_file['project_owner'] == $AppUI->user_id)))) {
			$s .= '<a style="float: left;" href="./index.php?m=files&a=addedit&file_id=' . $latest_file['file_id'] . '">' . w2PshowImage('kedit.png', '16', '16', 'edit file', 'edit file', 'files') . '</a>';
			$s .= '<a style="float: left;" href="javascript: void(0);" onclick="document.frm_duplicate_file_' . $latest_file['file_id'] . '.submit()">' . w2PshowImage('duplicate.png', '16', '16', 'duplicate file', 'duplicate file', 'files') . '</a>';
			$s .= '<a style="float: left;" href="javascript: void(0);" onclick="if (confirm(\'Are you sure you want to delete this file?\')) {document.frm_remove_file_' . $latest_file['file_id'] . '.submit()}">' . w2PshowImage('remove.png', '16', '16', 'delete file', 'delete file', 'files') . '</a>';
		}
        $s .= '</td>';
		$s .= '<td nowrap="nowrap" align="center" width="1">';
		if ($canEdit && (empty($latest_file['file_checkout']) || ($latest_file['file_checkout'] == 'final' && ($canEdit || $latest_file['project_owner'] == $AppUI->user_id)))) {
			$bulk_op = 'onchange="(this.checked) ? addBulkComponent(' . $latest_file['file_id'] . ') : removeBulkComponent(' . $latest_file['file_id'] . ')"';
			$s .= '<input type="checkbox" ' . $bulk_op . ' name="chk_sel_file_' . $latest_file['file_id'] . '" />';
		}
		$s .= '</td></tr>';
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
	$result = '';
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
		}
		if ($mime[0] == 'application') {
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
		}
	}

	if ($result == '') {
		switch ($obj->$file_category) {
			default: // no idea what's going on
				$result = 'icons/unknown.png';
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

//This kludgy function echos children tasks as threads on project designer (_pd)
//TODO: modules/projectdesigner/projectdesigner.class.php
function showtask_pd(&$a, $level = 0, $today_view = false) {
	global $AppUI, $w2Pconfig, $done, $query_string, $durnTypes, $userAlloc, $showEditCheckbox;
	global $task_access, $task_priority, $PROJDESIGN_CONFIG, $m, $expanded;

	$types = w2Pgetsysval('TaskType');

	$now = new w2p_Utilities_Date();
	$tf = $AppUI->getPref('TIMEFORMAT');
	$df = $AppUI->getPref('SHDATEFORMAT');
	$fdf = $df . ' ' . $tf;
	$perms = &$AppUI->acl();
	$show_all_assignees = $w2Pconfig['show_all_task_assignees'] ? true : false;

	$done[] = $a['task_id'];

	$start_date = intval($a['task_start_date']) ? new w2p_Utilities_Date($AppUI->formatTZAwareTime($a['task_start_date'], '%Y-%m-%d %T')) : null;
	$end_date = intval($a['task_end_date']) ? new w2p_Utilities_Date($AppUI->formatTZAwareTime($a['task_end_date'], '%Y-%m-%d %T')) : null;
	$last_update = isset($a['last_update']) && intval($a['last_update']) ? new w2p_Utilities_Date( $AppUI->formatTZAwareTime($a['last_update'], '%Y-%m-%d %T')) : null;

	// prepare coloured highlight of task time information
	$sign = 1;
	$style = '';
	if ($start_date) {
		if (!$end_date) {
			$end_date = new w2p_Utilities_Date('0000-00-00 00:00:00');
		}

		if ($now->after($start_date) && $a['task_percent_complete'] == 0) {
			$style = 'background-color:#ffeebb';
		} elseif ($now->after($start_date) && $a['task_percent_complete'] < 100) {
			$style = 'background-color:#e6eedd';
		}

		if ($now->after($end_date)) {
			$sign = -1;
			$style = 'background-color:#cc6666;color:#ffffff';
		}
		if ($a['task_percent_complete'] == 100) {
			$style = 'background-color:#aaddaa; color:#00000';
		}

		$days = $now->dateDiff($end_date) * $sign;
	}

	$jsTaskId = 'task_proj_' . $a['task_project'] . '_level-' . $level . '-task_' . $a['task_id'] . '_';
	if ($expanded) {
		$s = '<tr id="' . $jsTaskId . '" onmouseover="highlight_tds(this, true, ' . $a['task_id'] . ')" onmouseout="highlight_tds(this, false, ' . $a['task_id'] . ')" onclick="select_box(\'selected_task\', \'' . $a['task_id'] . '\', \'' . $jsTaskId . '\',\'frm_tasks\')">'; // edit icon
	} else {
		$s = '<tr id="' . $jsTaskId . '" onmouseover="highlight_tds(this, true, ' . $a['task_id'] . ')" onmouseout="highlight_tds(this, false, ' . $a['task_id'] . ')" onclick="select_box(\'selected_task\', \'' . $a['task_id'] . '\', \'' . $jsTaskId . '\',\'frm_tasks\')" ' . ($level ? 'style="display:none"' : '') . '>'; // edit icon
	}
	$s .= '<td>';
	$canEdit = ($a['task_represents_project']) ? false : true;
	$canViewLog = true;
	if ($canEdit) {
		$s .= '<a href="?m=tasks&a=addedit&task_id=' . $a['task_id'] . '">' . w2PtoolTip('edit tasks panel', 'click to edit this task') . w2PshowImage('icons/pencil.gif', 12, 12) . w2PendTip() . '</a>';
	}
	$s .= '</td>';
	// percent complete
	$s .= '<td align="right">' . (int) $a['task_percent_complete'] . '%</td>';
	// priority
	$s .= '<td align="center" nowrap="nowrap">';
	if ($a['task_priority'] < 0) {
		$s .= '<img src="' . w2PfindImage('icons/priority-' . -$a['task_priority'] . '.gif') . '" width="13" height="16" alt="" />';
	} elseif ($a['task_priority'] > 0) {
		$s .= '<img src="' . w2PfindImage('icons/priority+' . $a['task_priority'] . '.gif') . '" width="13" height="16" alt="" />';
	}
	$s .= '</td><td align="center" nowrap="nowrap">';
	if ($a['user_task_priority'] < 0) {
		$s .= '<img src="' . w2PfindImage('icons/priority-' . -$a['user_task_priority'] . '.gif') . '" alt="" />';
	} elseif ($a['user_task_priority'] > 0) {
		$s .= '<img src="' . w2PfindImage('icons/priority+' . $a['user_task_priority'] . '.gif') . '" alt="" />';
	}
	$s .= '</td>';

	// access
	$s .= '<td nowrap="nowrap">';
	$s .= mb_substr($task_access[$a['task_access']], 0, 3);
	$s .= '</td>';
	// type
	$s .= '<td nowrap="nowrap">';
	$s .= mb_substr($types[$a['task_type']], 0, 3);
	$s .= '</td>';
	// type
	$s .= '<td nowrap="nowrap">';
	$s .= $a['queue_id'] ? 'Yes' : '';
	$s .= '</td>';
	// inactive
	$s .= '<td nowrap="nowrap">';
	$s .= $a['task_status'] == '-1' ? 'Yes' : '';
	$s .= '</td>';
	// add log
	$s .= '<td align="center" nowrap="nowrap">';
	if ($a['task_dynamic'] != 1 && 0 == $a['task_represents_project']) {
		$s .= '<a href="?m=tasks&a=view&tab=1&project_id=' . $a['task_project'] . '&task_id=' . $a['task_id'] . '">' . w2PtoolTip('tasks', 'add work log to this task') . w2PshowImage('edit_add.png') . w2PendTip() . '</a>';
	}
	$s .= '</td>';
	// dots
	if ($today_view) {
		$s .= '<td>';
	} else {
		$s .= '<td width="20%">';
	}
	for ($y = 0; $y < $level; $y++) {
		if ($y + 1 == $level) {
			$s .= '<img src="' . w2PfindImage('corner-dots.gif', $m) . '" width="16" height="12" border="0" alt="" />';
		} else {
			$s .= '<img src="' . w2PfindImage('shim.gif', $m) . '" width="16" height="12"  border="0" alt="" />';
		}
	}
	// name link
	if ($a['task_description']) {
		$s .= w2PtoolTip('Task Description', $a['task_description'], true);
	}
    $jsTaskId = 'task_proj_' . $a['task_project'] . '_level-' . $level . '-task_' . $a['task_id'] . '_';
	$open_link = '<a href="javascript: void(0);"><img onclick="expand_collapse(\'' . $jsTaskId . '\', \'tblProjects\',\'\',' . ($level + 1) . ');" id="' . $jsTaskId . '_collapse" src="' . w2PfindImage('icons/collapse.gif', $m) . '" border="0" align="center" ' . (!$expanded ? 'style="display:none"' : '') . ' alt="" /><img onclick="expand_collapse(\'' . $jsTaskId . '\', \'tblProjects\',\'\',' . ($level + 1) . ');" id="' . $jsTaskId . '_expand" src="' . w2PfindImage('icons/expand.gif', $m) . '" border="0" align="center" ' . ($expanded ? 'style="display:none"' : '') . ' alt="" /></a>';
	$taskObj = new CTask;
	$taskObj->load($a['task_id']);
	if (count($taskObj->getChildren())) {
		$is_parent = true;
	} else {
		$is_parent = false;
	}
	if ($a['task_milestone'] > 0) {
		$s .= '&nbsp;<a href="./index.php?m=tasks&a=view&task_id=' . $a['task_id'] . '" ><b>' . $a['task_name'] . '</b></a> <img src="' . w2PfindImage('icons/milestone.gif', $m) . '" border="0" alt="" /></td>';
	} elseif ($a['task_dynamic'] == '1' || $is_parent) {
		$s .= $open_link;
		if ($a['task_dynamic'] == '1') {
			$s .= '&nbsp;<a href="./index.php?m=tasks&a=view&task_id=' . $a['task_id'] . '" ><b><i>' . $a['task_name'] . '</i></b></a></td>';
		} else {
			$s .= '&nbsp;<a href="./index.php?m=tasks&a=view&task_id=' . $a['task_id'] . '" >' . $a['task_name'] . '</a></td>';
		}
	} else {
		$s .= '&nbsp;<a href="./index.php?m=tasks&a=view&task_id=' . $a['task_id'] . '" >' . $a['task_name'] . '</a></td>';
	}
	if ($a['task_description']) {
		$s .= w2PendTip();
	}
	// task description
	if ($PROJDESIGN_CONFIG['show_task_descriptions']) {
		$s .= '<td align="justified">' . $a['task_description'] . '</td>';
	}
	// task owner
	$s .= '<td align="left">' . '<a href="?m=admin&a=viewuser&user_id=' . $a['user_id'] . '">' . $a['contact_first_name'] . ' ' . $a['contact_last_name'] . '</a></td>';
	$s .= '<td id="ignore_td_' . $a['task_id'] . '" nowrap="nowrap" align="center" style="' . $style . '">' . ($start_date ? $start_date->format($df . ' ' . $tf) : '-') . '</td>';
	// duration or milestone
	$s .= '<td id="ignore_td_' . $a['task_id'] . '" align="right" nowrap="nowrap" style="' . $style . '">';
	$s .= $a['task_duration'] . ' ' . mb_substr($AppUI->_($durnTypes[$a['task_duration_type']]), 0, 1);
	$s .= '</td>';
	$s .= '<td id="ignore_td_' . $a['task_id'] . '" nowrap="nowrap" align="center" style="' . $style . '">' . ($end_date ? $end_date->format($df . ' ' . $tf) : '-') . '</td>';
	if (isset($a['task_assigned_users']) && ($assigned_users = $a['task_assigned_users'])) {
		$a_u_tmp_array = array();
		if ($show_all_assignees) {
			$s .= '<td align="left">';
			foreach ($assigned_users as $val) {
				$aInfo = '<a href="?m=admin&a=viewuser&user_id=' . $val['user_id'] . '"';
				$aInfo .= 'title="' . (w2PgetConfig('check_overallocation') ? $AppUI->_('Extent of Assignment') . ':' . $userAlloc[$val['user_id']]['charge'] . '%; ' . $AppUI->_('Free Capacity') . ':' . $userAlloc[$val['user_id']]['freeCapacity'] . '%' : '') . '">';
				$aInfo .= $val['contact_first_name'] . ' ' . $val['contact_last_name'] . ' (' . $val['perc_assignment'] . '%)</a>';
				$a_u_tmp_array[] = $aInfo;
			}
			$s .= join(', ', $a_u_tmp_array);
			$s .= '</td>';
		} else {
			$s .= '<td align="left" nowrap="nowrap">';
			$s .= '<a href="?m=admin&a=viewuser&user_id=' . $assigned_users[0]['user_id'] . '"';
			$s .= 'title="' . (w2PgetConfig('check_overallocation') ? $AppUI->_('Extent of Assignment') . ':' . $userAlloc[$assigned_users[0]['user_id']]['charge'] . '%; ' . $AppUI->_('Free Capacity') . ':' . $userAlloc[$assigned_users[0]['user_id']]['freeCapacity'] . '%' : '') . '">';
			$s .= $assigned_users[0]['contact_first_name'] . ' ' . $assigned_users[0]['contact_last_name'] . ' (' . $assigned_users[0]['perc_assignment'] . '%)</a>';
			if ($a['assignee_count'] > 1) {
				$id = $a['task_id'];
				$s .= '<a href="javascript: void(0);"  onclick="toggle_users(\'users_' . $id . '\');" title="' . join(', ', $a_u_tmp_array) . '">(+' . ($a['assignee_count'] - 1) . ')</a>';
				$s .= '<span style="display: none" id="users_' . $id . '">';
				$a_u_tmp_array[] = $assigned_users[0]['user_username'];
				for ($i = 1, $i_cmp = count($assigned_users); $i < $i_cmp; $i++) {
					$a_u_tmp_array[] = $assigned_users[$i]['user_username'];
					$s .= '<br /><a href="?m=admin&a=viewuser&user_id=';
					$s .= $assigned_users[$i]['user_id'] . '" title="' . (w2PgetConfig('check_overallocation') ? $AppUI->_('Extent of Assignment') . ':' . $userAlloc[$assigned_users[$i]['user_id']]['charge'] . '%; ' . $AppUI->_('Free Capacity') . ':' . $userAlloc[$assigned_users[$i]['user_id']]['freeCapacity'] . '%' : '') . '">';
					$s .= $assigned_users[$i]['contact_first_name'] . ' ' . $assigned_users[$i]['contact_last_name'] . ' (' . $assigned_users[$i]['perc_assignment'] . '%)</a>';
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
	if ($showEditCheckbox && 0 == $a['task_represents_project']) {
		$s .= '<td align="center"><input type="checkbox" onclick="select_box(\'selected_task\', ' . $a['task_id'] . ',\'project_' . $a['task_project'] . '_level-' . $level . '-task_' . $a['task_id'] . '_\',\'frm_tasks\')" onfocus="is_check=true;" onblur="is_check=false;" id="selected_task_' . $a['task_id'] . '" name="selected_task[' . $a['task_id'] . ']" value="' . $a['task_id'] . '"/></td>';
	}
	$s .= '</tr>';
	echo $s;
}

//TODO: modules/projectdesigner/projectdesigner.class.php
function findchild_pd(&$tarr, $parent, $level = 0) {
	global $projects;

	$level = $level + 1;
	$n = count($tarr);

	for ($x = 0; $x < $n; $x++) {
		if ($tarr[$x]['task_parent'] == $parent && $tarr[$x]['task_parent'] != $tarr[$x]['task_id']) {
			showtask_pd($tarr[$x], $level);
			findchild_pd($tarr, $tarr[$x]['task_id'], $level);
		}
	}
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

//TODO: modules/projectdesigner/projectdesigner.class.php
function showtask_pr(&$a, $level = 0, $today_view = false) {
	global $AppUI, $w2Pconfig, $done, $query_string, $durnTypes, $userAlloc, $showEditCheckbox;
	global $task_access, $task_priority;

	$types = w2Pgetsysval('TaskType');

	$now = new w2p_Utilities_Date();
	$tf = $AppUI->getPref('TIMEFORMAT');
	$df = $AppUI->getPref('SHDATEFORMAT');
	$fdf = $df . ' ' . $tf;
	$perms = &$AppUI->acl();
	$show_all_assignees = $w2Pconfig['show_all_task_assignees'] ? true : false;

	$done[] = $a['task_id'];

	$start_date = intval($a['task_start_date']) ? new w2p_Utilities_Date($a['task_start_date']) : null;
	$end_date = intval($a['task_end_date']) ? new w2p_Utilities_Date($a['task_end_date']) : null;
	$last_update = isset($a['last_update']) && intval($a['last_update']) ? new w2p_Utilities_Date($a['last_update']) : null;

	// prepare coloured highlight of task time information
	$sign = 1;
	$style = '';
	if ($start_date) {
		if (!$end_date) {
			$end_date = new w2p_Utilities_Date('0000-00-00 00:00:00');
		}

		$days = $now->dateDiff($end_date) * $sign;
	}

	$s = '<tr>';

	// dots
	$s .= '<td nowrap width="20%">';
	for ($y = 0; $y < $level; $y++) {
		if ($y + 1 == $level) {
			$s .= '<img src="' . w2PfindImage('corner-dots.gif', $m) . '" width="16" height="12" border="0" alt="" />';
		} else {
			$s .= '<img src="' . w2PfindImage('shim.gif', $m) . '" width="16" height="12"  border="0" alt="" />';
		}
	}
	// name link
	$alt = mb_strlen($a['task_description']) > 80 ? mb_substr($a['task_description'], 0, 80) . '...' : $a['task_description'];
	// instead of the statement below
	$alt = mb_str_replace('"', "&quot;", $alt);
	$alt = mb_str_replace("\r", ' ', $alt);
	$alt = mb_str_replace("\n", ' ', $alt);

	$open_link = w2PshowImage('collapse.gif');
	if ($a['task_milestone'] > 0) {
		$s .= '&nbsp;<b>' . $a["task_name"] . '</b><!--</a>--> <img src="' . w2PfindImage('icons/milestone.gif', $m) . '" border="0" alt="" /></td>';
	} elseif ($a['task_dynamic'] == '1') {
		$s .= $open_link;
		$s .= '<strong>' . $a['task_name'] . '</strong>';
	} else {
		$s .= $a['task_name'];
	}
	// percent complete
	$s .= '<td align="right">' . (int) $a['task_percent_complete'] . '%</td>';
	$s .= '<td nowrap="nowrap" align="center" style="' . $style . '">' . ($start_date ? $start_date->format($df . ' ' . $tf) : '-') . '</td>';
	$s .= '</td>';
	$s .= '<td nowrap="nowrap" align="center" style="' . $style . '">' . ($end_date ? $end_date->format($df . ' ' . $tf) : '-') . '</td>';
	$s .= '</td>';
	$s .= '<td nowrap="nowrap" align="center" style="' . $style . '">' . ($last_update ? $last_update->format($df . ' ' . $tf) : '-') . '</td>';
	echo $s;
}

//TODO: modules/projectdesigner/projectdesigner.class.php
function findchild_pr(&$tarr, $parent, $level = 0) {
	global $projects;

	$level = $level + 1;
	$n = count($tarr);

	for ($x = 0; $x < $n; $x++) {
		if ($tarr[$x]['task_parent'] == $parent && $tarr[$x]['task_parent'] != $tarr[$x]['task_id']) {
			showtask_pr($tarr[$x], $level);
			findchild_pr($tarr, $tarr[$x]['task_id'], $level);
		}
	}
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
function taskstyle_pd($task) {
	$now = new w2p_Utilities_Date();
	$start_date = intval($task['task_start_date']) ? new w2p_Utilities_Date($task['task_start_date']) : null;
	$end_date = intval($task['task_end_date']) ? new w2p_Utilities_Date($task['task_end_date']) : null;

	if ($start_date && !$end_date) {
		$end_date = $start_date;
		$end_date->addSeconds($task['task_duration'] * $task['task_duration_type'] * SEC_HOUR);
	} else
		if (!$start_date) {
			return '';
		}

	$style = 'class=';
	if ($task['task_percent_complete'] == 0) {
		$style .= (($now->before($start_date)) ? '"task_future"' : '"task_notstarted"');
	} else
		if ($task['task_percent_complete'] == 100) {
			$t = new CTask();
			$t->load($task['task_id']);
			$actual_end_date = new w2p_Utilities_Date(get_actual_end_date_pd($t->task_id, $t));
			$style .= (($actual_end_date->after($end_date)) ? '"task_late"' : '"task_done"');
		} else {
			$style .= (($now->after($end_date)) ? '"task_overdue"' : '"task_started"');
		}
		return $style;
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
		project_type, project_name, project_description, project_scheduled_hours as project_duration,
		project_parent, project_original_parent, project_percent_complete,
		project_color_identifier, project_company,
        company_name, project_status, project_last_task as critical_task,
        tp.task_log_problem, user_username, project_active');

	$fields = w2p_Core_Module::getSettings('projects', 'index_list');
	unset($fields['department_list']);  // added as an alias below
	foreach ($fields as $field => $text) {
		$q->addQuery($field);
	}
	$q->addQuery('CONCAT(ct.contact_first_name, \' \', ct.contact_last_name) AS owner_name');
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
						showchilddept($row);
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