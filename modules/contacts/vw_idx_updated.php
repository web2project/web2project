<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $carr, $carrWidth, $carrHeight, $showfields, $contactMethods, $methodLabels, $tdw;

?>
<table width="100%" border="0" cellpadding="1" cellspacing="0" class="contacts list">
	<tr>
		<?php for ($z = 0; $z < $carrWidth; $z++) { ?>
			<td valign="top" align="left" width="<?php echo $tdw; ?>%">
                            <?php
                            if (!isset($carr[$z])){
                                continue;
                            }
                            ?>
				<table width="100%" cellspacing="2" cellpadding="1">
					<?php for ($x = 0, $x_cmp = count($carr[$z]); $x < $x_cmp; $x++) { ?>
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