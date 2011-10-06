<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$AppUI->savePlace();

if (!$canAccess) {
	$AppUI->redirect('m=public&a=access_denied');
}

$perms = &$AppUI->acl();

$countries = w2PgetSysVal('GlobalCountries');

// retrieve any state parameters
$searchString = w2PgetParam($_GET, 'search_string', '');
if ($searchString != '') {
	$AppUI->setState('ContIdxWhere', $searchString);
}
$where = $AppUI->getState('ContIdxWhere') ? $AppUI->getState('ContIdxWhere') : '%';
$tab = $AppUI->processIntState('ContactsIdxTab', $_GET, 'tab', 0);
$days = ($tab == 0) ? 30 : 0;

$orderby = 'contact_first_name';

$search_map = array($orderby, 'contact_first_name', 'contact_last_name');

// optional fields shown in the list (could be modified to allow brief and verbose, etc)
$showfields = array('contact_address1' => 'contact_address1', 
	'contact_address2' => 'contact_address2', 'contact_city' => 'contact_city', 
	'contact_state' => 'contact_state', 'contact_zip' => 'contact_zip', 
	'contact_country' => 'contact_country', 'contact_company' => 'contact_company', 
	'company_name' => 'company_name', 'dept_name' => 'dept_name',
    'contact_phone' => 'contact_phone', 'contact_email' => 'contact_email',
    'contact_job'=>'contact_job');
$contactMethods = array('phone_alt', 'phone_mobile', 'phone_fax');
$methodLabels = w2PgetSysVal('ContactMethods');

// assemble the sql statement
$rows = CContact::searchContacts($AppUI, $where, '', $days);

$carr[] = array();
$carrWidth = 4;
$carrHeight = 4;

$rn = count($rows);
$t = ceil($rn / $carrWidth);

if ($rn < ($carrWidth * $carrHeight)) {
	$i = 0;
	for ($y = 0; $y < $carrWidth; $y++) {
		$x = 0;
		while (($x < $carrHeight) && isset($rows[$i]) && ($row = $rows[$i])) {
			$carr[$y][] = $row;
			$x++;
			$i++;
		}
	}
} else {
	$i = 0;
	for ($y = 0; $y <= $carrWidth; $y++) {
		$x = 0;
		while (($x < $t) && isset($rows[$i]) && ($row = $rows[$i])) {
			$carr[$y][] = $row;
			$x++;
			$i++;
		}
	}
}

$tdw = floor(100 / $carrWidth);

/**
 * Contact search form
 */
// Let's remove the first '%' that we previously added to ContIdxWhere
$default_search_string = w2PformSafe(substr($AppUI->getState('ContIdxWhere'), 0, strlen($AppUI->getState('ContIdxWhere'))), true);

$form = '<form action="./index.php" method="get" accept-charset="utf-8">' . $AppUI->_('Search for') . '
           <input type="text" class="text" name="search_string" value="' . $default_search_string . '" />
		   <input type="hidden" name="m" value="contacts" />
		   <input type="submit" value=">" />
		   <a href="./index.php?m=contacts&amp;search_string=0">' . $AppUI->_('Reset search') . '</a>
		 </form>';
// En of contact search form

$a2z = '<table cellpadding="2" cellspacing="1" border="0">';
$a2z .= '<tr>';
$a2z .= '<td width="100%" align="right">' . $AppUI->_('Show') . ': </td>';
$a2z .= '<td><a href="./index.php?m=contacts&where=0">' . $AppUI->_('All') . '</a></td>';

// Pull First Letters
$letters = CContact::getFirstLetters($AppUI->user_id);

for ($c = 65; $c < 91; $c++) {
	$cu = chr($c);
	$cell = !(mb_strpos($letters, $cu) === false) ? '<a href="?m=contacts&search_string=' . $cu . '">' . $cu . '</a>' : '<font color="#999999">' . $cu . '</font>';
	$a2z .= '<td>' . $cell . '</td>';
}
$a2z .= '</tr><tr><td colspan="28">' . $form . '</td></tr></table>';

// setup the title block
$titleBlock = new w2p_Theme_TitleBlock('Contacts', 'monkeychat-48.png', $m, $m . '.' . $a);
$titleBlock->addCell($a2z);
if ($canEdit) {
	$titleBlock->addCell('<input type="submit" class="button" value="' . $AppUI->_('new contact') . '">', '', '<form action="?m=contacts&a=addedit" method="post" accept-charset="utf-8">', '</form>');
	$titleBlock->addCrumb('?m=contacts&a=csvexport&suppressHeaders=1', 'CSV Download');
	$titleBlock->addCrumb('?m=contacts&a=vcardimport&dialog=0', 'Import vCard');
}
$titleBlock->show();

$tabBox = new CTabBox('?m=contacts', W2P_BASE_DIR . '/modules/contacts/', $tab);
$tabBox->add('vw_idx_updated', $AppUI->_('Recently Updated'));
$tabBox->add('vw_idx_contacts', $AppUI->_('All Contacts'));
$tabBox->show();

// TODO: Check to see that the Edit function is separated.
?>
<script language="javascript" type="text/javascript">
	// Callback function for the generic selector
	function goProject( key, val ) {
		var f = document.modProjects;
		if (val != '') {
			f.project_id.value = key;
			f.submit();
		}
	}
</script>
<form action="./index.php" method='get' name="modProjects" accept-charset="utf-8">
  <input type='hidden' name='m' value='projects' />
  <input type='hidden' name='a' value='view' />
  <input type='hidden' name='project_id' />
</form>