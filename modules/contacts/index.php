<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$tab = $AppUI->processIntState('ContactsIdxTab', $_GET, 'tab', 0);
$days = ($tab == 0) ? 30 : 0;

$contact = new CContact();
$canCreate = $contact->canCreate();
$canAccess = $contact->canAccess();

if (!$canAccess) {
	$AppUI->redirect(ACCESS_DENIED);
}

$countries = w2PgetSysVal('GlobalCountries');

// retrieve any state parameters
$searchString = w2PgetParam($_GET, 'search_string', '');
if ('' == $searchString) {
    $searchString = ((0 < $tab) && ($tab < 27)) ? chr(64 + $tab) : '';
    $searchString = (0 == $tab) ? '' : $searchString;
} else {
    $tab = 27;
    $AppUI->setState('ContactsIdxTab', $tab);
}

$AppUI->setState('ContIdxWhere', $searchString);
$where = $AppUI->getState('ContIdxWhere') ? $AppUI->getState('ContIdxWhere') : '%';

$rows = CContact::searchContacts($AppUI, $where, '', $days);

/**
 * Contact search form
 */
$form = '<form action="./index.php" method="get" accept-charset="utf-8">' . $AppUI->_('Search for') . '
           <input type="text" class="text" name="search_string" value="' . $searchString . '" />
		   <input type="hidden" name="m" value="contacts" />
		   <input type="submit" value=">" />
		   <a href="./index.php?m=contacts&amp;tab=0">' . $AppUI->_('Reset search') . '</a>
		 </form>';
// En of contact search form

$a2z = '<table cellpadding="2" cellspacing="1" border="0">';
$a2z .= '<tr><td>' . $form . '</td></tr></table>';

// setup the title block
$titleBlock = new w2p_Theme_TitleBlock('Contacts', 'monkeychat-48.png', $m, $m . '.' . $a);
$titleBlock->addCell($a2z);
if ($canCreate) {
	$titleBlock->addCell('<input type="submit" class="button" value="' . $AppUI->_('new contact') . '">', '', '<form action="?m=contacts&a=addedit" method="post" accept-charset="utf-8">', '</form>');
	$titleBlock->addCrumb('?m=contacts&a=csvexport&suppressHeaders=1', 'CSV Download');
	$titleBlock->addCrumb('?m=contacts&a=vcardimport&dialog=0', 'Import vCard');
}
$titleBlock->show();

$tabBox = new CTabBox('?m=contacts', W2P_BASE_DIR . '/modules/contacts/', $tab);
$tabBox->add('vw_idx_contacts', $AppUI->_('Recently Updated'));
for ($c = 65; $c < 91; $c++) {
    $tabBox->add('vw_idx_contacts', $AppUI->_(chr($c)));
}
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