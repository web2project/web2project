<?php /* $Id: index.php 1515 2010-12-05 07:13:50Z caseydk $ $URL: https://web2project.svn.sourceforge.net/svnroot/web2project/trunk/modules/contacts/index.php $ */
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
$rows = CContact::searchContacts($AppUI, $where);

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
$default_search_string = w2PformSafe(substr($AppUI->getState('ContIdxWhere'), 1, strlen($AppUI->getState('ContIdxWhere'))), true);

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
$titleBlock = new CTitleBlock('Contacts', 'monkeychat-48.png', $m, $m . '.' . $a);
$titleBlock->addCell($a2z);
if ($canEdit) {
	$titleBlock->addCell('<input type="submit" class="button" value="' . $AppUI->_('new contact') . '">', '', '<form action="?m=contacts&a=addedit" method="post" accept-charset="utf-8">', '</form>');
	$titleBlock->addCrumb('?m=contacts&a=csvexport&suppressHeaders=1', 'CSV Download');
	$titleBlock->addCrumb('?m=contacts&a=vcardimport&dialog=0', 'Import vCard');
}
$titleBlock->show();

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
<?php
if (function_exists('styleRenderBoxTop')) {
	echo styleRenderBoxTop();
}
?>
<table width="100%" border="0" cellpadding="1" cellspacing="0" class="contacts">
	<tr>
		<?php for ($z = 0; $z < $carrWidth; $z++) { ?>
			<td valign="top" align="left" width="<?php echo $tdw; ?>%">
				<table width="100%" cellspacing="2" cellpadding="1">
					<?php for ($x = 0, $x_cmp = @count($carr[$z]); $x < $x_cmp; $x++) { ?>
						<tr>
							<td>
								<table width="100%" cellspacing="0" cellpadding="1" class="std">
									<tr>
										<td width="100%" colspan="2">
											<table width="100%" cellspacing="0" cellpadding="1">
												<?php $contactid = $carr[$z][$x]['contact_id']; ?>
												<th style="text-align:left" width="70%">
													<a href="./index.php?m=contacts&a=view&contact_id=<?php echo $contactid; ?>"><strong><?php echo ($carr[$z][$x]['contact_title'] ? $carr[$z][$x]['contact_title'] . ' ' : '') . $carr[$z][$x]['contact_first_name'] . ' ' . $carr[$z][$x]['contact_last_name']; ?></strong></a>
												</th>
												<th style="text-align:right" nowrap="nowrap" width="30%">
                                                    <span>
                                                    <?php
                                                    if ($carr[$z][$x]['user_id']) {
														echo '<a href="./index.php?m=admin&a=viewuser&user_id=' . $carr[$z][$x]['user_id'] . '" style="float: right;">';
                                                        echo w2PtoolTip($m, 'This Contact is also a User, click to view its details.') . w2PshowImage('icons/users.gif') . w2PendTip();
                                                        echo '</a>';
													}
                                                    ?><a href="?m=contacts&a=vcardexport&suppressHeaders=true&contact_id=<?php echo $contactid; ?>" style="float: right;"><?php
                                                        echo w2PtoolTip($m, 'export vCard of this contact') . w2PshowImage('vcard.png') . w2PendTip();
                                                    ?></a>
                                                    <a href="?m=contacts&a=addedit&contact_id=<?php echo $contactid; ?>" style="float: right;"><?php
                                                        echo w2PtoolTip($m, 'edit this contact') . w2PshowImage('icons/pencil.gif') . w2PendTip();
                                                    ?></a>
													<?php
														$projectList = CContact::getProjects($contactid);

                                                        $df = $AppUI->getPref('SHDATEFORMAT');
                                                        $df .= ' ' . $AppUI->getPref('TIMEFORMAT');

                                                        $contact_updatekey   = $carr[$z][$x]['contact_updatekey'];
                                                        $contact_lastupdate  = $carr[$z][$x]['contact_lastupdate'];
                                                        $contact_updateasked = $carr[$z][$x]['contact_updateasked'];
                                                        $last_ask = new w2p_Utilities_Date($contact_updateasked);
                                                        $lastAskFormatted = $last_ask->format($df);
														if (count($projectList) > 0) {
															echo '<a href="" onclick="	window.open(\'./index.php?m=public&a=selector&dialog=1&callback=goProject&table=projects&user_id=' . $carr[$z][$x]['contact_id'] . '\', \'selector\', \'left=50,top=50,height=250,width=400,resizable\');return false;">' . w2PshowImage('projects.png', '', '', $m, 'click to view projects associated with this contact') . '</a>';
														}
														if ($contact_updateasked && (!$contact_lastupdate || $contact_lastupdate == 0) && $contact_updatekey) {
                                                            echo w2PtoolTip('info', 'Waiting for Contact Update Information. (Asked on: ' . $lastAskFormatted . ')') . '<img src="' . w2PfindImage('log-info.gif') . '" style="float: right;">' . w2PendTip();
														} elseif ($contact_updateasked && (!$contact_lastupdate || $contact_lastupdate== 0) && !$contact_updatekey) {
                                                            echo w2PtoolTip('info', 'Waiting for too long! (Asked on ' . $lastAskFormatted . ')') . '<img src="' . w2PfindImage('log-error.gif') . '" style="float: right;">' . w2PendTip();
														} elseif ($contact_updateasked && !$contact_updatekey) {
															$last_ask = new w2p_Utilities_Date($contact_lastupdate);
                                                            echo w2PtoolTip('info', 'Update sucessfully done on: ' . $last_ask->format($df) . '') . '<img src="' . w2PfindImage('log-notice.gif') . '" style="float: right;">' . w2PendTip();
														}
													?>
                                                    </span>
												</th>
											</table>
										</td>
									</tr>
									<tr>
										<?php
											reset($showfields);
											$s = '';
											while (list($key, $val) = each($showfields)) {
												if (mb_strlen($carr[$z][$x][$key]) > 0) {
													if ($val == 'contact_email') {
                                                        $s .= '<td class="hilite" colspan="2">' . w2p_email($carr[$z][$x][$key]) . '</td></tr>';
                                                    } elseif ($val == 'contact_company' && is_numeric($carr[$z][$x][$key])) {
														//Don't do a thing
													} elseif ($val == 'company_name') {
														$s .= '<tr><td width="35%"><strong>' . $AppUI->_('Company') . ':</strong></td><td class="hilite" width="65%">' . $carr[$z][$x][$key] . '</td></tr>';
													} elseif ($val == 'contact_job') {
														$s .= '<tr><td width="35%"><strong>' . $AppUI->_('Job Title') . ':</strong></td><td class="hilite" width="65%">' . $carr[$z][$x][$key] . '</td></tr>';
													} elseif ($val == 'dept_name') {
														$s .= '<tr><td width="35%"><strong>' . $AppUI->_('Department') . ':</strong></td><td class="hilite" width="65%">' . $carr[$z][$x][$key] . '</td></tr>';
													} elseif ($val == 'contact_country' && $carr[$z][$x][$key]) {
														$s .= '<tr><td class="hilite" colspan="2">' . ($countries[$carr[$z][$x][$key]] ? $countries[$carr[$z][$x][$key]] : $carr[$z][$x][$key]) . '<br /></td></tr>';
													} elseif ($val != 'contact_country') {
														$s .= '<tr><td class="hilite" colspan="2">' . $carr[$z][$x][$key] . '<br /></td></tr>';
                                                    } elseif ($val == 'contact_phone') {
                                                        $s .= '<tr><td width="35%"><strong>' . $AppUI->_('Work Phone') . ':</strong></td><td class="hilite" width="65%">' . $carr[$z][$x][$key] . '</td></tr>';
													}
												}
											}
											echo $s;
										?>
									</tr>
								</table>
							</td>
						</tr>
					<?php } ?>
				</table>
			</td>
		<?php } ?>
	</tr>
</table>