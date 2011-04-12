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