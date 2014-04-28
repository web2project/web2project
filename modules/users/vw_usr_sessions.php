<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
// @todo    convert to template
// @todo    remove database query

global $AppUI, $w2Pconfig, $canEdit, $canDelete, $stub, $where, $orderby;

/*
* Flag value to determine if "logout user" button should show. 
* Could be determined by a configuration value in the future.
*/
$logoutUserFlag = true;

if (isset($_GET['out_user_id']) && $_GET['out_user_id']
        && isset($_GET['out_name']) && $_GET['out_name']
        && $canEdit && $canDelete) {
	$boot_user_id = w2PgetParam($_GET, 'out_user_id', null);
	$boot_user_name = $_GET['out_name'];
	$details = $boot_user_name . ' by ' . $AppUI->user_first_name . ' ' . $AppUI->user_last_name;

	// one session or many?
	if ($_GET['out_session'] && $_GET['out_user_log_id']) {
		$boot_user_session = $_GET['out_session'];
		$boot_user_log_id = w2PgetParam($_GET, 'out_user_log_id', null);
		$boot_query_row = false;
	} else {
		if ($canEdit && $canDelete && $logoutUserFlag) {
			// query for all sessions open for a given user
			$r = new w2p_Database_Query;
			$r->addTable('sessions', 's');
			$r->addQuery('DISTINCT(session_id), user_access_log_id');
			$r->addJoin('user_access_log', 'ual', 'session_user = user_access_log_id');
			$r->addWhere('user_id = ' . (int)$boot_user_id);
			$r->addOrder('user_access_log_id');

			//execute query and fetch results
			$r->exec();
			$boot_query_row = $r->fetchRow();
			if ($boot_query_row) {
				$boot_user_session = $boot_query_row['session_id'];
				$boot_user_log_id = $boot_query_row['user_access_log_id'];
			}
		}
    }

	do {
		if ($boot_user_id == $AppUI->user_id && $boot_user_session == $_COOKIE['PHPSESSID']) {
			$AppUI->resetPlace();
			$AppUI->redirect('logout=-1');
		} else {
			addHistory('login', $boot_user_id, 'logout', $details);
            $session = new w2p_System_Session();
            $session->destroy($boot_user_session, $boot_user_log_id);
		}

		if ($boot_query_row) {
			$boot_query_row = $r->fetchRow();
			if ($boot_query_row) {
				$boot_user_session = $boot_query_row['session_id'];
				$boot_user_log_id = $boot_query_row['user_access_log_id'];
			} else {
				$r->clear();
			}
		}

	} while ($boot_query_row);

	$msg = $boot_user_name . ' logged out by ' . $AppUI->user_first_name . ' ' . $AppUI->user_last_name;
	$AppUI->setMsg($msg, UI_MSG_OK);
	$AppUI->redirect('m=users&tab=3');
}

$rows = __extract_from_vw_usr_sessions($orderby);

$tab = w2PgetParam($_REQUEST, 'tab', 0);

$fieldList = array('user_username', 'contact_last_name', 'company_name', 'date_time_in', 'user_ip');
$fieldNames = array('Login Name', 'Real Name', 'Company', 'Date Time IN', 'Internet Address');
?>
<table class="tbl list">
    <tr>
        <th colspan="2">&nbsp; <?php echo $AppUI->_('sort by'); ?>:&nbsp;</th>
        <?php foreach ($fieldNames as $index => $name) { ?><th>
            <a href="?m=users&orderby=<?php echo $fieldList[$index]; ?>" class="hdr">
                <?php echo $AppUI->_($fieldNames[$index]); ?>
            </a>
        </th><?php } ?>
    </tr>
<?php

$htmlHelper = new w2p_Output_HTMLHelper($AppUI);

foreach ($rows as $row) {
    $htmlHelper->stageRowData($row);

    $s = '<tr>';
	$s .= '<td align="center" nowrap="nowrap">';
	if ($canEdit && $canDelete) {
		$s .= '<input type="button" class="button" value="' . $AppUI->_('logout_session') . '" onclick="javascript:window.location=\'./index.php?m=users&tab=3&out_session=' . $row['session_id'] . '&out_user_log_id=' . $row['user_access_log_id'] . '&out_user_id=' . $row['u_user_id'] . '&out_name=' . addslashes($row['contact_display_name']). '\';"></input>';
	}
	$s .= '</td><td align="center" nowrap="nowrap">';
	if ($canEdit && $canDelete && $logoutUserFlag) {
		$s .= '<input type="button" class=button value="' . $AppUI->_('logout_user') . '" onclick="javascript:window.location=\'./index.php?m=users&tab=3&out_user_id=' . $row['u_user_id'] . '&out_name=' . addslashes($row['contact_display_name']) . '\';"></input>';
	}
	$s .= '</td>';
    $s .= $htmlHelper->createCell('na', $row['user_username']);
    $s .= $htmlHelper->createCell('na', $row['contact_display_name']);
    $s .= $htmlHelper->createCell('contact_company', $row['contact_company']);
    $s .= $htmlHelper->createCell('log_in_datetime', $row['date_time_in']);
    $s .= $htmlHelper->createCell('user_ip', $row['user_ip']);
	$s .= '</tr>';
	echo $s;
}
?>
</table>