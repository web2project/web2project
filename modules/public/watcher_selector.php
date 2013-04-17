<?php
//if (!defined('W2P_BASE_DIR')){
//  die('You should not access this file directly.');
//}

$show_all = w2PgetParam($_REQUEST, 'show_all', 0);
$company_id = w2PgetParam($_REQUEST, 'company_id', 0);
$call_back = w2PgetParam($_GET, 'call_back', null);
$watchers_submited = w2PgetParam($_POST, 'watchers_submited', 0);
$selected_watchers_id = w2PgetParam($_GET, 'selected_watchers_id', '');
if (w2PgetParam($_POST, 'selected_watchers_id')) {
	$selected_watchers_id = w2PgetParam($_POST, 'selected_watchers_id');
}
?>
<script language="javascript" type="text/javascript">
function removeFromArray(arr, value) {
	for (var i = 0, i_cmp = arr.length; i < i_cmp; i++) {
		if (arr[i] == value) {
			return arr.splice(0,i).concat(arr.splice(i + 1));
		}
	}
	return arr;
}

function insertIntoArray(arr, value) {
	for (var i = 0, i_cmp = arr.length; i < i_cmp; i++) {
		if (arr[i] == value) {
			return arr;
		}
	}
	return arr.concat(value);
}

function setWatcherIDs(method, querystring) {
	var URL = 'index.php?m=public&a=watcher_selector';
	var field = document.getElementsByName('watcher_id[]');
	var selected_watchers_id = document.frmWatcherSelect.selected_watchers_id;
	if (selected_watchers_id.value.length == 0) {
		var tmp = new Array();
	} else {
		var tmp = selected_watchers_id.value.split(',');
	}

	if (method == 'GET' && querystring){
		URL += '&' + querystring;
	}

	for (var i = 0, i_cmp = field.length; i < i_cmp; i++) {
		if (field[i].checked) {
			tmp = insertIntoArray(tmp,field[i].value);
		} else {
			tmp = removeFromArray(tmp,field[i].value);
		}
	}
	selected_watchers_id.value = tmp.join(',');

	if (method == 'GET') {
		URL +=  '&selected_watchers_id=' + selected_watchers_id.value;
		return URL;
	} else {
		return selected_watchers_id;
	}
}
</script>
<?php

if ($watchers_submited == 1) {
	$call_back_string = !is_null($call_back) ? "window.opener.$call_back('$selected_watchers_id');" : '';
?>
<script language="javascript" type="text/javascript">
	<?php echo $call_back_string ?>
	self.close();
</script>
<?php
}

// Remove any empty elements
$watchers_id = array_filter(explode(',', $selected_watchers_id));
$selected_watchers_id = implode(',', $watchers_id);

/*
 * The code should filter the users so that only users allowed to access the forum would be selectable.
 * But that would required a massive number of queries (several per user), so the code doesn't do that.
 * Instead it relies that the user won't select users that are not allowed to access the forum, since
 * that test will be done when attempting to access the forum or messages. So selecting an user that
 * can't access the forum means that the selection is meaningless.
 */
$perms = &$AppUI->acl();

$company_name = '';
$where = null;
$q = new w2p_Database_Query;
$q->addTable('users');
$q->addQuery('DISTINCT(user_id), user_username, contact_last_name, contact_first_name,
 	      company_name, contact_company, dept_id, dept_name, contact_display_name,
              contact_display_name as contact_name, contact_email, user_type');
$q->addJoin('contacts', 'con', 'con.contact_id = user_contact', 'inner');
if (strlen($selected_watchers_id) > 0 && !$show_all && !$company_id) {
	$where = 'user_id IN(' . $selected_watchers_id . ')';
} elseif (!$company_id && !$show_all) {
	$where = '(contact_company IS NULL OR contact_company = 0)';
	$company_name = $AppUI->_('No Company');
} elseif ($show_all) {
	$company_name = $AppUI->_('Allowed Companies');
} else {
	// Contacts for this company only
	$where = 'contact_company = ' . (int)$company_id;
}
$q->addWhere($where);
$q->addGroup('user_id');
$q->addOrder('company_name, contact_company, dept_name, contact_department, contact_first_name, contact_last_name');

// get CCompany() to filter by company
$cobj = new CCompany();
$companies_sql = $cobj->getAllowedSQL($AppUI->user_id, 'company_id');
$q->addJoin('companies', 'com', 'company_id = contact_company');
if ($companies_sql) {
	$q->addWhere('(' . implode(' OR ', $companies_sql) . ' OR contact_company=\'\' OR contact_company IS NULL OR contact_company = 0)');
}
$dobj = new CDepartment();
$depts_sql = $dobj->getAllowedSQL($AppUI->user_id, 'dept_id');
$q->addJoin('departments', 'dep', 'dept_id = contact_department');
if ($depts_sql) {
	$q->addWhere('(' . implode(' OR ', $depts_sql) . ' OR contact_department=0)');
}

$temp_user_list = $q->loadList();

// Strip inactive users
$valid_user_list = array();
foreach ($temp_user_list as $row) {
	if ($perms->isUserPermitted($row['user_id'])) {
		$valid_user_list[$row['user_id']] = $row;
	}
}

$companies = $cobj->getAllowedRecords($AppUI->user_id, 'company_id, company_name', 'company_name');

?>

<form action="index.php?m=public&a=watcher_selector&dialog=1&<?php if (!is_null($call_back)) echo 'call_back=' . $call_back . '&'; ?>company_id=<?php echo $company_id ?>" method="post" name="frmWatcherSelect" accept-charset="utf-8">
	<?php
		$actual_department = '';
		$actual_company = '';
		$companies_names = array(0 => $AppUI->_('Select a company')) + $companies;
		echo $AppUI->_('Users from company') . ': ' . arraySelect($companies_names, 'company_id', 'onchange="document.frmWatcherSelect.watchers_submited.value=0; setWatcherIDs(); document.frmWatcherSelect.submit();"', $company_id);
	?>
	<br /><br />
	<?php
		if (function_exists('styleRenderBoxTop')) {
			echo styleRenderBoxTop();
		}
	?>
	<table width="100%" class="std">
		<tr>
			<td>
				<h4><a href="javascript: void(0);" onclick="window.location.href=setWatcherIDs('GET','dialog=1&<?php if (!is_null($call_back)) echo 'call_back=' . $call_back . '&'; ?>show_all=1');"><?php echo $AppUI->_('View all allowed companies'); ?></a></h4>
				<hr />
				<h2><?php if (count($valid_user_list)) { echo $AppUI->_('Users from') . ' ' . $company_name; } ?></h2>
				<?php
					foreach ($valid_user_list as $user_id => $user_data) {
						// Skip the default admin
						if ($user_id > 1) {
							if (!$user_data['company_name']) {
								$user_company = $user_data['contact_company'];
							} else {
								$user_company = $user_data['company_name'];
							}
							if ($user_company && $user_company != $actual_company) {
								echo '<h4>' . $user_company . '</h4>';
								$actual_company = $user_company;
							}
							$user_department = $user_data['dept_name'] ? $user_data['dept_name'] : $user_data['dept_id'];
							if ($user_department && $user_department != $actual_department) {
								echo '<h5>' . $user_department . '</h5>';
								$actual_department = $user_department;
							}
							$checked = in_array($user_id, $watchers_id) ? 'checked="checked"' : '';
							echo '<input type="checkbox" name="watcher_id[]" id="watcher_' . $user_id . '" value="' . $user_id . '" ' . $checked . ' />';
							echo '<label for="watcher_' . $user_id . '">' . $user_data['contact_first_name'] . ' ' . $user_data['contact_last_name'] . '</label>';
							echo '<br />';
						}
					}
				?>
				<hr />
				<input name="watchers_submited" type="hidden" value="1" />
				<input name="selected_watchers_id" type="hidden" value="<?php echo $selected_watchers_id; ?>" />
				<input type="submit" value="<?php echo $AppUI->_('Continue'); ?>" onclick="setWatcherIDs();" class="button" />
			</td>
		</tr>
	</table>
</form>
