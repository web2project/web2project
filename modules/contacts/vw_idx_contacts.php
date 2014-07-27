<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
// @todo    convert to template

global $AppUI, $tab, $m;

$days = ($tab == 0) ? 30 : 0;
$searchString = w2PgetParam($_POST, 'search_string', '');
if ('' == $searchString) {
    $searchString = ((0 < $tab) && ($tab < 27)) ? chr(64 + $tab) : '';
    $searchString = (0 == $tab) ? '' : $searchString;
} else {
    $AppUI->setState('ContactsIdxTab', 27);
}

$AppUI->setState('ContIdxWhere', $searchString);

$where = $AppUI->getState('ContIdxWhere') ? $AppUI->getState('ContIdxWhere') : '%';

$contact = new CContact();
$rows = $contact->search($where, $days);

$countries = w2PgetSysVal('GlobalCountries');

unset($carr);
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

$df = $AppUI->getPref('SHDATEFORMAT');
$df .= ' ' . $AppUI->getPref('TIMEFORMAT');

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

?>
<table width="100%" border="0" cellpadding="1" cellspacing="0" class="contacts">
	<tr>
		<?php for ($z = 0; $z < $carrWidth; $z++) { ?>
			<td valign="top" align="left" width="<?php echo $tdw; ?>%">
                            <?php
                            if (!isset($carr[$z])){
                                continue;
                            }
                            ?>
				<table width="100%" cellspacing="2" cellpadding="1" class="contacts-column">
					<?php for ($x = 0, $x_cmp = count($carr[$z]); $x < $x_cmp; $x++) { ?>
						<tr>
							<td>
								<table class="std contact-info">
									<tr>
										<td width="100%" colspan="2">
											<table width="100%" cellspacing="0" cellpadding="1">
												<?php $contactid = $carr[$z][$x]['contact_id']; ?>
												<th style="text-align:left" width="70%">
													<a href="./index.php?m=contacts&a=view&contact_id=<?php echo $contactid; ?>"><strong><?php echo ($carr[$z][$x]['contact_title'] ? $carr[$z][$x]['contact_title'] . ' ' : '') . $carr[$z][$x]['contact_first_name'] . ' ' . $carr[$z][$x]['contact_last_name']; ?></strong></a>
												</th>
												<th style="text-align:right" nowrap="nowrap" width="30%">
                                                    <?php
                                                    if ($carr[$z][$x]['user_id']) {
														echo '<a href="./index.php?m=users&a=view&user_id=' . $carr[$z][$x]['user_id'] . '" style="float: right;">';
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

                                                        $contact_updatekey   = $carr[$z][$x]['contact_updatekey'];
                                                        $contact_lastupdate  = $carr[$z][$x]['contact_lastupdate'];
                                                        $contact_updateasked = $carr[$z][$x]['contact_updateasked'];
                                                        $last_ask = new w2p_Utilities_Date($contact_updateasked);
                                                        $lastAskFormatted = $last_ask->format($df);
														if (count($projectList) > 0) {
															echo '<a href="" onclick="	window.open(\'./index.php?m=public&a=selector&dialog=1&callback=goProject&table=projects&user_id=' . $carr[$z][$x]['contact_id'] . '\', \'selector\', \'left=50,top=50,height=250,width=400,resizable\');return false;">' . w2PshowImage('projects.png', '', '', $m, 'click to view projects associated with this contact') . '</a>';
														}
														if ($contact_updateasked && (!$contact_lastupdate || $contact_lastupdate == 0) && $contact_updatekey) {
                                                            echo w2PtoolTip('info', 'Waiting for Contact Update Information. (Asked on: ' . $lastAskFormatted . ')') . '<img src="' . w2PfindImage('log-info.gif') . '">' . w2PendTip();
														} elseif ($contact_updateasked && (!$contact_lastupdate || $contact_lastupdate== 0) && !$contact_updatekey) {
                                                            echo w2PtoolTip('info', 'Waiting for too long! (Asked on ' . $lastAskFormatted . ')') . '<img src="' . w2PfindImage('log-error.gif') . '">' . w2PendTip();
														} elseif ($contact_updateasked && !$contact_updatekey) {
															$last_ask = new w2p_Utilities_Date($contact_lastupdate);
                                                            echo w2PtoolTip('info', 'Update sucessfully done on: ' . $last_ask->format($df) . '') . '<img src="' . w2PfindImage('log-notice.gif') . '">' . w2PendTip();
														}
													?>
												</th>
											</table>
										</td>
									</tr>
									<tr>
										<?php
											reset($showfields);
											$s = '';
                                            foreach($showfields as $key => $val) {
												if (isset($carr[$z][$x][$key]) && mb_strlen($carr[$z][$x][$key]) > 0) {
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