<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$AppUI->savePlace();

// retrieve any state parameters
if (isset($_GET['orderby'])) {
	$orderdir = $AppUI->getState('CompIdxOrderDir') ? ($AppUI->getState('CompIdxOrderDir') == 'asc' ? 'desc' : 'asc') : 'desc';
	$AppUI->setState('CompIdxOrderBy', w2PgetParam($_GET, 'orderby', null));
	$AppUI->setState('CompIdxOrderDir', $orderdir);
}
$orderby = $AppUI->getState('CompIdxOrderBy') ? $AppUI->getState('CompIdxOrderBy') : 'company_name';
$orderdir = $AppUI->getState('CompIdxOrderDir') ? $AppUI->getState('CompIdxOrderDir') : 'asc';

if (isset($_REQUEST['owner_filter_id'])) {
	$AppUI->setState('owner_filter_id', w2PgetParam($_REQUEST, 'owner_filter_id', null));
	$owner_filter_id = w2PgetParam($_REQUEST, 'owner_filter_id', null);
} else {
	$owner_filter_id = $AppUI->getState('owner_filter_id');
	if (!isset($owner_filter_id)) {
		$owner_filter_id = 0; //By default show all companies instead of $AppUI->user_id current user.
		$AppUI->setState('owner_filter_id', $owner_filter_id);
	}
}
// load the company types
$types = w2PgetSysVal('CompanyType');

// get any records denied from viewing
$obj = new CCompany();
$deny = $obj->getDeniedRecords($AppUI->user_id);

// Company search by Kist
$search_string = w2PgetParam($_REQUEST, 'search_string', '');
if ($search_string != '') {
	$search_string = $search_string == '-1' ? '' : $search_string;
	$AppUI->setState('search_string', $search_string);
} else {
	$search_string = $AppUI->getState('search_string');
}

// retrieve list of records
$search_string = w2PformSafe($search_string, true);

$perms = &$AppUI->acl();

$baseArray = array(0 => $AppUI->_('All', UI_OUTPUT_RAW));
$allowedArray = $perms->getPermittedUsers('companies');
$owner_list = is_array($allowedArray) ? ($baseArray + $allowedArray) : $baseArray;

$owner_combo = arraySelect($owner_list, 'owner_filter_id', 'class="text" onchange="javascript:document.searchform.submit()"', $owner_filter_id, false);

// setup the title block
$titleBlock = new CTitleBlock('Companies', 'handshake.png', $m, $m . '.' . $a);
$titleBlock->addCell('<form name="searchform" action="?m=companies&amp;search_string=' . $search_string . '" method="post" accept-charset="utf-8">
						<table>
							<tr>
                      			<td>
                                    <strong>' . $AppUI->_('Search') . '</strong>
                                    <input class="text" type="text" name="search_string" value="' . $search_string . '" /><br />
						<a href="index.php?m=companies&search_string=-1">' . $AppUI->_('Reset search') . "</a></td>
								<td valign='top'>
									<strong>" . $AppUI->_('Owner filter') . '</strong> ' . $owner_combo . '
								</td>
							</tr>
						</table>
                      </form>');

$search_string = addslashes($search_string);

if ($canEdit) {
	$titleBlock->addCell('<input type="submit" class="button" value="' . $AppUI->_('new company') . '">', '', '<form action="?m=companies&a=addedit" method="post" accept-charset="utf-8">', '</form>');
}
$titleBlock->show();

$companiesTypeTab = $AppUI->processIntState('CompaniesIdxTab', $_GET, 'tab', 0);
$companiesType = $companiesTypeTab;

$tabBox = new CTabBox('?m=companies', W2P_BASE_DIR . '/modules/companies/', $companiesTypeTab);
if ($tabbed = $tabBox->isTabbed()) {
	$add_na = true;
	if (isset($types[0])) { // They have a Not Applicable entry.
		$add_na = false;
		$types[] = $types[0];
	}
	$types[0] = 'All Companies';
	if ($add_na) {
		$types[] = 'Not Applicable';
	}
}
$type_filter = array();
foreach ($types as $type => $type_name) {
	$type_filter[] = $type;
	$tabBox->add('vw_companies', $type_name);
}

$tabBox->show();