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

// To configure an aditional filter to use in the search string
$additional_filter = '';
// retrieve any state parameters
if (isset($_GET['where'])) {
	$AppUI->setState('ContIdxWhere', $_GET['where']);
}
if (isset($_GET['search_string'])) {
	$AppUI->setState('ContIdxWhere', '%' . $_GET['search_string']);
	// Added the first % in order to find instrings also
	$additional_filter = 'OR contact_first_name like \'%' . $_GET['search_string'] . '%\'
	                      OR contact_last_name  like \'%' . $_GET['search_string'] . '%\'
	                      OR CONCAT(contact_first_name, \' \', contact_last_name)  like \'%' . $_GET['search_string'] . '%\'
						  OR company_name       like \'%' . $_GET['search_string'] . '%\'
						  OR contact_notes      like \'%' . $_GET['search_string'] . '%\'
						  OR contact_email      like \'%' . $_GET['search_string'] . '%\'';
}
$where = $AppUI->getState('ContIdxWhere') ? $AppUI->getState('ContIdxWhere') : '%';

$orderby = 'contact_first_name';

//To Bruce: Clean updatekeys based on datediff to warn about long waiting.
$days_for_update = 5;
$q = new DBQuery;
$q->addTable('contacts');
$q->addUpdate('contact_updatekey', '');
$q->addWhere('(TO_DAYS(NOW()) - TO_DAYS(contact_updateasked) >=' . $days_for_update . ')');
$q->exec();
$q->clear();

require_once $AppUI->getModuleClass('companies');
$company =& new CCompany;
$allowedCompanies = $company->getAllowedSQL($AppUI->user_id);

require_once $AppUI->getModuleClass('departments');
$department =& new CDepartment;
$allowedDepartments = $department->getAllowedSQL($AppUI->user_id);

// Pull First Letters
$let = ":";
$search_map = array($orderby, 'contact_first_name', 'contact_last_name');
foreach ($search_map as $search_name) {
	$q = new DBQuery;
	$q->addTable('contacts');
	$q->addQuery('DISTINCT UPPER(SUBSTRING(' . $search_name . ',1,1)) as L');
	$q->addWhere('contact_private=0 OR (contact_private=1 AND contact_owner=' . $AppUI->user_id . ') OR contact_owner IS NULL OR contact_owner = 0');
	$arr = $q->loadList();
	if (count($allowedCompanies)) {
		$comp_where = implode(' AND ', $allowedCompanies);
		$q->addWhere('( (' . $comp_where . ') OR contact_company = 0 )');
	}
	if (count($allowedDepartments)) {
		$dpt_where = implode(' AND ', $allowedDepartments);
		$q->addWhere('( (' . $dpt_where . ') OR contact_department = 0 )');
	}
	foreach ($arr as $L) {
		$let .= $L['L'];
	}
}

// optional fields shown in the list (could be modified to allow breif and verbose, etc)
$showfields = array( // "test" => "concat(contact_first_name,' ',contact_last_name) as test",    why do we want the name repeated?
	'contact_address1' => 'contact_address1', 'contact_address2' => 'contact_address2', 'contact_city' => 'contact_city', 'contact_state' => 'contact_state', 'contact_zip' => 'contact_zip', 'contact_country' => 'contact_country', 'contact_company' => 'contact_company', 'company_name' => 'company_name', 'dept_name' => 'dept_name', 'contact_phone' => 'contact_phone', 'contact_phone2' => 'contact_phone2', 'contact_mobile' => 'contact_mobile', 'contact_fax' => 'contact_fax', 'contact_email' => 'contact_email');

// assemble the sql statement
$q = new DBQuery;
$q->addQuery('contact_id, contact_order_by');
$q->addQuery($showfields);
$q->addQuery('contact_first_name, contact_last_name, contact_phone, contact_title');
$q->addQuery('contact_updatekey, contact_updateasked, contact_lastupdate');
$q->addQuery('user_id');
$q->addTable('contacts', 'a');
$q->leftJoin('companies', 'b', 'a.contact_company = b.company_id');
$q->leftJoin('departments', '', 'contact_department = dept_id');
$q->leftJoin('users', '', 'contact_id = user_contact');
$q->addWhere('(contact_first_name LIKE \'' . $where . '%\' OR contact_last_name LIKE \'' . $where . '%\' ' . $additional_filter . ')');
$q->addWhere('
	(contact_private=0
		OR (contact_private=1 AND contact_owner=' . $AppUI->user_id . ')
		OR contact_owner IS NULL OR contact_owner = 0
	)');
if (count($allowedCompanies)) {
	$comp_where = implode(' AND ', $allowedCompanies);
	$q->addWhere('( (' . $comp_where . ') OR contact_company = 0 )');
}
if (count($allowedDepartments)) {
	$dpt_where = implode(' AND ', $allowedDepartments);
	$q->addWhere('( (' . $dpt_where . ') OR contact_department = 0 )');
}
$q->addOrder('contact_first_name');
$q->addOrder('contact_last_name');

$carr[] = array();
$carrWidth = 4;
$carrHeight = 4;

$rows = array();
$rows = $q->loadList();
$res = $q->exec();
if (count($rows)) {
	$rn = count($rows);
} else {
	echo db_error();
	$rn = 0;
}
$t = floor($rn / $carrWidth);

if ($rn < ($carrWidth * $carrHeight)) {
	$i = 0;
	for ($y = 0; $y < $carrWidth; $y++) {
		$x = 0;
		while (($x < $carrHeight) && ($row = $rows[$i])) {
			$carr[$y][] = $row;
			$x++;
			$i++;
		}
	}
} else {
	$i = 0;
	for ($y = 0; $y <= $carrWidth; $y++) {
		$x = 0;
		while (($x < $t) && ($row = $rows[$i])) {
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
$default_search_string = w2PformSafe(substr($AppUI->getState('ContIdxWhere'), 1, strlen($AppUI->getState('ContIdxWhere'))), true);

$form = '<form action="./index.php" method="get">' . $AppUI->_('Search for') . '
           <input type="text" class="text" name="search_string" value="' . $default_search_string . '" />
		   <input type="hidden" name="m" value="contacts" />
		   <input type="submit" value=">" />
		   <a href="./index.php?m=contacts&amp;search_string=">' . $AppUI->_('Reset search') . '</a>
		 </form>';
// En of contact search form

$a2z = '<table cellpadding="2" cellspacing="1" border="0">';
$a2z .= '<tr>';
$a2z .= '<td width="100%" align="right">' . $AppUI->_('Show') . ': </td>';
$a2z .= '<td><a href="./index.php?m=contacts&where=0">' . $AppUI->_('All') . '</a></td>';
for ($c = 65; $c < 91; $c++) {
	$cu = chr($c);
	$cell = strpos($let, $cu) > 0 ? '<a href="?m=contacts&where=' . $cu . '">' . $cu . '</a>' : '<font color="#999999">' . $cu . '</font>';
	$a2z .= '<td>' . $cell . '</td>';
}
$a2z .= '</tr><tr><td colspan="28">' . $form . '</td></tr></table>';

// setup the title block

// what purpose is the next line for? Commented out by gregorerhardt, Bug #892912
// $contact_id = $carr[$z][$x]["contact_id"];

$titleBlock = new CTitleBlock('Contacts', 'monkeychat-48.png', $m, $m . '.' . $a);
$titleBlock->addCell($a2z);
if ($canEdit) {
	$titleBlock->addCell('<input type="submit" class="button" value="' . $AppUI->_('new contact') . '">', '', '<form action="?m=contacts&a=addedit" method="post">', '</form>');
	$titleBlock->addCrumb('?m=contacts&a=csvexport&suppressHeaders=1', 'CSV Download');
	$titleBlock->addCrumb('?m=contacts&a=vcardimport&dialog=0', 'Import vCard');
}
$titleBlock->show();

// TODO: Check to see that the Edit function is separated.


?>
<script language="javascript">
// Callback function for the generic selector
function goProject( key, val ) {
	var f = document.modProjects;
	if (val != '') {
		f.project_id.value = key;
		f.submit();
        }
}
</script>
<form action="./index.php" method='get' name="modProjects">
  <input type='hidden' name='m' value='projects' />
  <input type='hidden' name='a' value='view' />
  <input type='hidden' name='project_id' />
</form>
<?php
if (function_exists('styleRenderBoxTop')) {
	echo styleRenderBoxTop();
}
?>
<table width="100%" border="0" cellpadding="1" cellspacing="0" class="contacts">
<tr>
<?php
for ($z = 0; $z < $carrWidth; $z++) {
?>
	<td valign="top" align="left" width="<?php echo $tdw; ?>%">
		<table width="100%" cellspacing="2" cellpadding="1">
	<?php
	for ($x = 0, $x_cmp = @count($carr[$z]); $x < $x_cmp; $x++) {
?>
		<tr>
		<td>
		<table width="100%" cellspacing="0" cellpadding="1" class="std">
		<tr>
            <?php $contactid = $carr[$z][$x]['contact_id']; ?>
			<th style="text-align:left" nowrap="nowrap" width="50%">
				<a href="./index.php?m=contacts&a=view&contact_id=<?php echo $contactid; ?>"><strong><?php echo ($carr[$z][$x]['contact_title'] ? $carr[$z][$x]['contact_title'] . ' ' : '') . $carr[$z][$x]['contact_first_name'] . ' ' . $carr[$z][$x]['contact_last_name']; ?></strong></a>
			</th>
			<th style="text-align:right" nowrap="nowrap" width="50%">
                <?php if ($carr[$z][$x]['user_id']) {
			echo '<a href="./index.php?m=admin&a=viewuser&user_id=' . $carr[$z][$x]['user_id'] . '">' . w2PshowImage('icons/users.gif', '', '', $m, 'This Contact is also a User, click to view its details.') . '</a>';
		}
?>
				<a href="?m=contacts&a=vcardexport&suppressHeaders=true&contact_id=<?php echo $contactid; ?>" ><?php echo w2PshowImage('vcard.png', '', '', $m, 'export vCard of this contact'); ?></a>
                <a href="?m=contacts&a=addedit&contact_id=<?php echo $contactid; ?>"><?php echo w2PshowImage('icons/pencil.gif', '', '', $m, 'edit this contact'); ?></a>
<?php
		$q->clear();
		$q = new DBQuery;
		$q->addTable('projects');
		$q->addQuery('count(project_id)');
		$q->addWhere('project_contacts like \'' . $carr[$z][$x]['contact_id'] . ',%\' or project_contacts like \'%,' . $carr[$z][$x]['contact_id'] . ',%\' or project_contacts like \'%,' . $carr[$z][$x]['contact_id'] . '\' or project_contacts like \'' . $carr[$z][$x]['contact_id'] . '\'');

		$res = $q->exec();
		$projects_contact = $q->fetchRow();
		$q->clear();
		if ($projects_contact[0] > 0)
			echo '<a href="" onclick="	window.open(\'./index.php?m=public&a=selector&dialog=1&callback=goProject&table=projects&user_id=' . $carr[$z][$x]['contact_id'] . '\', \'selector\', \'left=50,top=50,height=250,width=400,resizable\');return false;">' . w2PshowImage('projects.png', '', '', $m, 'click to view projects associated with this contact') . '</a>';
		if ($carr[$z][$x]['contact_updateasked'] && (!$carr[$z][$x]['contact_lastupdate'] || $carr[$z][$x]['contact_lastupdate'] == 0) && $carr[$z][$x]['contact_updatekey']) {
			$last_ask = new CDate($carr[$z][$x]['contact_updateasked']);
			$df = $AppUI->getPref('SHDATEFORMAT');
			$df .= ' ' . $AppUI->getPref('TIMEFORMAT');
			echo w2PshowImage('log-info.gif', null, null, 'info', 'Waiting for Contact Update Information. (Asked on: ' . $last_ask->format($df) . ')');
		} elseif ($carr[$z][$x]['contact_updateasked'] && (!$carr[$z][$x]['contact_lastupdate'] || $carr[$z][$x]['contact_lastupdate'] == 0) && !$carr[$z][$x]['contact_updatekey']) {
			$last_ask = new CDate($carr[$z][$x]['contact_updateasked']);
			$df = $AppUI->getPref('SHDATEFORMAT');
			$df .= ' ' . $AppUI->getPref('TIMEFORMAT');
			echo w2PshowImage('log-error.gif', null, null, 'info', 'Waiting for too long! (Asked on ' . $last_ask->format($df) . ')');
		} elseif ($carr[$z][$x]['contact_lastupdate'] && !$carr[$z][$x]['contact_updatekey']) {
			$last_ask = new CDate($carr[$z][$x]['contact_lastupdate']);
			$df = $AppUI->getPref('SHDATEFORMAT');
			$df .= ' ' . $AppUI->getPref('TIMEFORMAT');
			echo w2PshowImage('log-notice.gif', null, null, 'info', 'Update sucessfully done on: ' . $last_ask->format($df) . '');
		} else {
		}
?>
			</th>
		</tr>
		<tr>
			<?php
		reset($showfields);
		$s = '';
		while (list($key, $val) = each($showfields)) {
			if (strlen($carr[$z][$x][$key]) > 0) {
				if ($val == 'contact_email') {
					$s .= '<tr><td class="hilite" colspan="2"><a href="mailto:' . $carr[$z][$x][$key] . '" class="mailto">' . $carr[$z][$x][$key] . '</a></td></tr>';
				} elseif ($val == 'contact_company' && is_numeric($carr[$z][$x][$key])) {
					//Don't do a thing
				} elseif ($val == 'company_name') {
					$s .= '<tr><td width="35%"><strong>' . $AppUI->_('Company') . ':</strong></td><td class="hilite" width="65%">' . $carr[$z][$x][$key] . '</td></tr>';
				} elseif ($val == 'dept_name') {
					$s .= '<tr><td width="35%"><strong>' . $AppUI->_('Department') . ':</strong></td><td class="hilite" width="65%">' . $carr[$z][$x][$key] . '</td></tr>';
				} elseif ($val == 'contact_phone') {
					$s .= '<tr><td width="35%"><strong>' . $AppUI->_('Work Phone') . ':</strong></td><td class="hilite" width="65%">' . $carr[$z][$x][$key] . '</td></tr>';
				} elseif ($val == 'contact_phone2') {
					$s .= '<tr><td width="35%"><strong>' . $AppUI->_('Home Phone') . ':</strong></td><td class="hilite" width="65%">' . $carr[$z][$x][$key] . '</td></tr>';
				} elseif ($val == 'contact_mobile') {
					$s .= '<tr><td width="35%"><strong>' . $AppUI->_('Mobile Phone') . ':</strong></td><td class="hilite" width="65%">' . $carr[$z][$x][$key] . '</td></tr>';
				} elseif ($val == 'contact_fax') {
					$s .= '<tr><td width="35%"><strong>' . $AppUI->_('Fax') . ':</strong></td><td class="hilite" width="65%">' . $carr[$z][$x][$key] . '</td></tr>';
				} elseif ($val == 'contact_country' && $carr[$z][$x][$key]) {
					$s .= '<tr><td class="hilite" colspan="2">' . ($countries[$carr[$z][$x][$key]] ? $countries[$carr[$z][$x][$key]] : $carr[$z][$x][$key]) . '<br /></td></tr>';
				} elseif ($val != 'contact_country') {
					$s .= '<tr><td class="hilite" colspan="2">' . $carr[$z][$x][$key] . '<br /></td></tr>';
				}
			}
		}
		echo $s;
?>
		</table>
		</td>
		</tr>
	<?php } ?>
		</table>
	</td>
<?php } ?>
</tr>
</table>