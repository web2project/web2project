<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $w2Pconfig, $canEdit, $canDelete, $stub, $where, $orderby;

/*
* Flag value to determine if "logout user" button should show. 
* Could be determined by a configuration value in the future.
*/
$logoutUserFlag = true;

if ($_GET['out_user_id'] && $_GET['out_name'] && $canEdit && $canDelete) {
	$boot_user_id = w2PgetParam($_GET, 'out_user_id', null);
	$boot_user_name = $_GET['out_name'];
	$details = $boot_user_name . ' by ' . $AppUI->user_first_name . ' ' . $AppUI->user_last_name;

	// one session or many?
	if ($_GET['out_session'] && $_GET['out_user_log_id']) {
		$boot_user_session = $_GET['out_session'];
		$boot_user_log_id = w2PgetParam($_GET, 'out_user_log_id', null);
		$boot_query_row = false;
	} else
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

	do {
		if ($boot_user_id == $AppUI->user_id && $boot_user_session == $_COOKIE['PHPSESSID']) {
			$AppUI->resetPlace();
			$AppUI->redirect('logout=-1');
		} else {
			addHistory('login', $boot_user_id, 'logout', $details);
			w2PsessionDestroy($boot_user_session, $boot_user_log_id);
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
	$AppUI->redirect('m=admin&tab=3');
}

$q = new w2p_Database_Query;
$q->addTable('sessions', 's');
$q->addQuery('DISTINCT(session_id), user_access_log_id, u.user_id as u_user_id, user_username, contact_last_name, contact_first_name, company_name, contact_company, date_time_in, user_ip');

$q->addJoin('user_access_log', 'ual', 'session_user = user_access_log_id');
$q->addJoin('users', 'u', 'ual.user_id = u.user_id');
$q->addJoin('contacts', 'con', 'u.user_contact = contact_id');
$q->addJoin('companies', 'com', 'contact_company = company_id');
$q->addOrder($orderby);
$rows = $q->loadList();
$q->clear();

$tab = w2PgetParam($_REQUEST, 'tab', 0);

?>

<table cellpadding="2" cellspacing="1" border="0" width="100%" class="tbl">
    <tr>
        <th colspan="2">&nbsp; <?php echo $AppUI->_('sort by'); ?>:&nbsp;</th>
        <?php
        $fieldList = array('user_username', 'contact_last_name', 'company_name', 'date_time_in', 'user_ip');
        $fieldNames = array('Login Name', 'Real Name', 'Company', 'Date Time IN', 'Internet Address');
        foreach ($fieldNames as $index => $name) {
            ?><th nowrap="nowrap">
                <a href="?m=admin&orderby=<?php echo $fieldList[$index]; ?>" class="hdr">
                    <?php echo $AppUI->_($fieldNames[$index]); ?>
                </a>
            </th><?php
        }
        ?>
    </tr>
<?php
foreach ($rows as $row) {
	$s = '<tr>';
	$s .= '<td align="center" nowrap="nowrap">';
	if ($canEdit && $canDelete) {
		$s .= '<input type="button" class="button" value="' . $AppUI->_('logout_session') . '" onclick="javascript:window.location=\'./index.php?m=admin&tab=3&out_session=' . $row['session_id'] . '&out_user_log_id=' . $row['user_access_log_id'] . '&out_user_id=' . $row['u_user_id'] . '&out_name=' . $row['contact_first_name'] . '%20' . $row['contact_last_name'] . '\';"></input>';
	}
	$s .= '</td><td align="center" nowrap="nowrap">';
	if ($canEdit && $canDelete && $logoutUserFlag) {
		$s .= '<input type="button" class=button value="' . $AppUI->_('logout_user') . '" onclick="javascript:window.location=\'./index.php?m=admin&tab=3&out_user_id=' . $row['u_user_id'] . '&out_name=' . $row['contact_first_name'] . '%20' . $row['contact_last_name'] . '\';"></input>';
	}
	$s .= '</td><td><a href="./index.php?m=admin&a=viewuser&user_id=' . $row['u_user_id'] . '">' . $row['user_username'] . '</a></td><td>';
	if ($row['contact_first_name'] || $row['contact_last_name']) {
		$s .= ($row['contact_first_name'] . ' ' . $row['contact_last_name']);
	} else {
		$s .= ('<span style="font-style: italic">unknown</span>');
	}
	$s .= '</td><td><a href="./index.php?m=companies&a=view&company_id=' . $row['contact_company'] . '">' . $row['company_name'] . '</a></td>';
	$s .= '<td>' . $row['date_time_in'] . '</td><td>' . $row['user_ip'] . '</td></tr>';
	echo $s;
}
?>
</table>