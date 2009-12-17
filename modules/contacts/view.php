<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$contact_id = intval(w2PgetParam($_GET, 'contact_id', 0));
$AppUI->savePlace();

//check permissions for this record
$perms = &$AppUI->acl();
$canRead = $perms->checkModuleItem($m, 'view', $contact_id);

if (!$canRead) {
	$AppUI->redirect('m=public&a=access_denied');
}

$tab = $AppUI->processIntState('ContactVwTab', $_GET, 'tab', 0);

$df = $AppUI->getPref('SHDATEFORMAT');
$df .= ' ' . $AppUI->getPref('TIMEFORMAT');

// load the record data
$msg = '';
$contact = new CContact();
$canDelete = $contact->canDelete($msg, $contact_id);
$is_user = $contact->isUser($contact_id);

$canEdit = $perms->checkModuleItem($m, 'edit', $contact_id);

if (!$contact->load($contact_id) && $contact_id > 0) {
	$AppUI->setMsg('Contact');
	$AppUI->setMsg('invalidID', UI_MSG_ERROR, true);
	$AppUI->redirect();
} elseif ($contact->contact_private && $contact->contact_owner != $AppUI->user_id && $contact->contact_owner && $contact_id != 0) {
	// check only owner can edit
	$AppUI->redirect('m=public&a=access_denied');
}

$countries = w2PgetSysVal('GlobalCountries');

// Get the contact details for company and department
$company_detail = $contact->getCompanyDetails();
$dept_detail = $contact->getDepartmentDetails();

// setup the title block
$ttl = 'View Contact';
$titleBlock = new CTitleBlock($ttl, 'monkeychat-48.png', $m, $m . '.' . $a);
$titleBlock->addCrumb('?m=contacts', 'contacts list');
if ($canEdit && $contact_id) {
	$titleBlock->addCrumb('?m=contacts&a=addedit&contact_id='.$contact_id, 'edit this contact');
}
if ($canDelete && $contact_id) {
	$titleBlock->addCrumbDelete('delete contact', $canDelete, $msg);
}
$titleBlock->show();

$last_ask = new CDate($contact->contact_updateasked);
$lastupdated = new CDate($contact->contact_lastupdate);

?>
<form name="changecontact" action="?m=contacts" method="post" accept-charset="utf-8">
        <input type="hidden" name="dosql" value="do_contact_aed" />
        <input type="hidden" name="del" value="0" />
        <input type="hidden" name="contact_id" value="<?php echo $contact_id; ?>" />
        <input type="hidden" name="contact_owner" value="<?php echo $contact->contact_owner ? $contact->contact_owner : $AppUI->user_id; ?>" />
</form>
<script language="JavaScript">
function delIt(){
        var form = document.changecontact;
        if(confirm( '<?php echo $AppUI->_('contactsDelete', UI_OUTPUT_JS); ?>' )) {
                form.del.value = '<?php echo $contact_id; ?>';
                form.submit();
        }
}
</script>

<table border="0" cellpadding="4" cellspacing="0" width="100%" class="std">
	<tr>
		<td valign="top">
			<table border="0" cellpadding="1" cellspacing="1">
				<tr>
					<td align="right"><?php echo $AppUI->_('First Name'); ?>:</td>
					<td class="hilite" width="100%"><?php echo $contact->contact_first_name; ?></td>
				</tr>
				<tr>
					<td align="right">&nbsp;&nbsp;<?php echo $AppUI->_('Last Name'); ?>:</td>
					<td class="hilite" width="100%"><?php echo $contact->contact_last_name; ?></td>
				</tr>
				<tr>
					<td align="right" width="100"><?php echo $AppUI->_('Display Name'); ?>: </td>
					<td class="hilite" width="100%"><?php echo $contact->contact_order_by; ?></td>
				</tr>
				<tr>
					<td align="right"><?php echo $AppUI->_('Job Title'); ?>:</td>
					<td class="hilite" width="100%"><?php echo $contact->contact_job; ?></td>
				</tr>
				<tr>
					<td align="right" width="100"><?php echo $AppUI->_('Company'); ?>:</td>
					<td nowrap="nowrap" class="hilite" width="100%">
						<?php if ($perms->checkModuleItem('companies', 'access', $contact->contact_company)) { ?>
							<?php echo "<a href='?m=companies&a=view&company_id=" . $contact->contact_company . "'>" . htmlspecialchars($company_detail['company_name'], ENT_QUOTES) . '</a>'; ?>
						<?php } else { ?>
							<?php echo htmlspecialchars($company_detail['company_name'], ENT_QUOTES); ?>
						<?php } ?>
					</td>
				</tr>
				<tr>
					<td align="right" width="100"><?php echo $AppUI->_('Department'); ?>:</td>
					<td nowrap="nowrap" class="hilite" width="100%"><?php echo $dept_detail['dept_name']; ?></td>
				</tr>
				<tr>
					<td align="right"><?php echo $AppUI->_('Title'); ?>:</td>
					<td class="hilite" width="100%"><?php echo $contact->contact_title; ?></td>
				</tr>
				<tr>
					<td align="right"><?php echo $AppUI->_('Type'); ?>:</td>
					<td class="hilite" width="100%"><?php echo $contact->contact_type; ?></td>
				</tr>
				<tr>
					<td align="right" valign="top" width="100"><?php echo $AppUI->_('Address'); ?>:</td>
					<td class="hilite" width="100%">
						<?php echo $contact->contact_address1; ?><br />
		        <?php echo $contact->contact_address2; ?><br />
		        <?php echo $contact->contact_city . ', ' . $contact->contact_state . ' ' . $contact->contact_zip; ?><br />
		        <?php echo isset($countries[$contact->contact_country]) ? $countries[$contact->contact_country] : $contact->contact_country; ?>
		     </td>
				</tr>
				<tr>
					<td align="right" width="100"><?php echo $AppUI->_('Map Address'); ?>:</td>
					<td class="hilite" width="100%"><a target="_blank" href="http://maps.google.com/maps?q=<?php echo $contact->contact_address1; ?>+<?php echo $contact->contact_address2; ?>+<?php echo $contact->contact_city; ?>+<?php echo $contact->contact_state; ?>+<?php echo $contact->contact_zip; ?>+<?php echo $contact->contact_country; ?>"><?php echo w2PshowImage('googlemaps.gif', 55, 22, 'Find It on Google'); ?></a></td>
				</tr>
			</table>
		</td>
		<td>
			<table border="0" cellpadding="1" cellspacing="1">
				<tr>
					<td align="right" width="100"><?php echo $AppUI->_('Work Phone'); ?>:</td>
					<td class="hilite" width="100%"><?php echo $contact->contact_phone; ?></td>
				</tr>
				<tr>
					<td align="right"><?php echo $AppUI->_('Home Phone'); ?>:</td>
					<td class="hilite" width="100%"><?php echo $contact->contact_phone2; ?></td>
				</tr>
				<tr>
					<td align="right"><?php echo $AppUI->_('Fax'); ?>:</td>
					<td class="hilite" width="100%"><?php echo $contact->contact_fax; ?></td>
				</tr>
				<tr>
					<td align="right"><?php echo $AppUI->_('Mobile Phone'); ?>:</td>
					<td class="hilite" width="100%"><?php echo $contact->contact_mobile; ?></td>
				</tr>
				<tr>
					<td align="right" width="100"><?php echo $AppUI->_('Email'); ?>:</td>
					<td nowrap="nowrap" class="hilite" width="100%"><a href="mailto:<?php echo $contact->contact_email; ?>"><?php echo $contact->contact_email; ?></a></td>
				</tr>
				<tr>
					<td align="right"><?php echo $AppUI->_('Email'); ?>2:</td>
					<td nowrap="nowrap" class="hilite" width="100%"><a href="mailto:<?php echo $contact->contact_email2; ?>"><?php echo $contact->contact_email2; ?></a></td>
				</tr>
				<tr>
					<td align="right"><?php echo $AppUI->_('Homepage'); ?>:</td>
					<td nowrap="nowrap" class="hilite" width="100%"><a href="<?php echo $contact->contact_url; ?>"><?php echo $contact->contact_url; ?></a></td>
				</tr>
				<tr>
					<td align="right">Jabber:</td>
					<td class="hilite" width="100%"><?php echo $contact->contact_jabber; ?></td>
				</tr>
				<tr>
					<td align="right">ICQ:</td>
					<td class="hilite" width="100%"><?php echo $contact->contact_icq; ?></td>
				</tr>
				<tr>
					<td align="right">AOL:</td>
					<td class="hilite" width="100%"><a href="aim:<?php echo $contact->contact_aol; ?>"><?php echo $contact->contact_aol; ?></a></td>
				</tr>
				<tr>
					<td align="right">MSN:</td>
					<td class="hilite" width="100%"><?php echo $contact->contact_msn; ?></td>
				</tr>
				<tr>
					<td align="right">Yahoo:</td>
					<td class="hilite" width="100%"><a href="ymsgr:sendIM?<?php echo $contact->contact_yahoo; ?>"><?php echo $contact->contact_yahoo; ?></a></td>
				</tr>
				<tr>
					<td align="right">Skype:</td>
					<td class="hilite" width="100%"><a href="skype:<?php echo $contact->contact_skype; ?>"><?php echo $contact->contact_skype; ?></a></td>
				</tr>
				<tr>
					<td align="right">Google:</td>
					<td class="hilite" width="100%"><a href="google:<?php echo $contact->contact_google; ?>"><?php echo $contact->contact_google; ?></a></td>
				</tr>
				<tr>
					<td align="right"><?php echo $AppUI->_('Birthday'); ?>:</td>
					<td nowrap="nowrap" class="hilite" width="100%"><?php echo substr($contact->contact_birthday, 0, 10); ?></td>
				</tr>
			</table>
		</td>
		<td valign="top" align="right">
			<table border="0" cellpadding="1" cellspacing="1">
				<th colspan="2">
					<strong><?php echo $AppUI->_('Contact Update Info'); ?></strong>
				</th>
				<tr>
					<td align="right" width="100" nowrap="nowrap"><?php echo $AppUI->_('Waiting Update'); ?>?:</td>
					<td align="center">
						<input type="checkbox" value="1" name="contact_updateasked" disabled="disabled" <?php echo $contact->contact_updatekey ? 'checked="checked"' : ''; ?> />
					</td>
				</tr>	
				<tr>
					<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Last Update Requested'); ?>:</td>
					<td align="center" nowrap="nowrap"><?php echo $contact->contact_updateasked ? $last_ask->format($df) : ''; ?></td>
				</tr>	
				<tr>
				<tr>
					<td align="right" width="100" nowrap="nowrap"><?php echo $AppUI->_('Last Updated'); ?>:</td>
					<td align="center" nowrap="nowrap"><?php echo ($contact->contact_lastupdate && !($contact->contact_lastupdate == 0)) ? $lastupdated->format($df) : ''; ?></td>
				</tr>	
			</table>
		</td>
	</tr>
	<tr>
		<td valign="top" width="50%">
			<table border="0" cellpadding="1" cellspacing="1" class="details" width="100%">
				<?php
					$custom_fields = new CustomFields($m, $a, $contact->contact_id, 'view');
					if ($custom_fields->count()) { ?>
							<th colspan="2">
								<strong><?php echo $AppUI->_('Contacts Custom Fields'); ?></strong>
							</th>
							<tr>
								<td colspan="2">
									<?php
										$custom_fields->printHTML();
									?>
								</td>
							</tr>
					<?php
					}
				?>
			</table>
		</td>
		<td valign="top" width="50%">
			<strong><?php echo $AppUI->_('Contact Notes'); ?></strong><br />
			<?php echo w2p_textarea(nl2br($contact->contact_notes)); ?>
		</td>
	</tr>
	<tr>
		<td>
			<input type="button" value="<?php echo $AppUI->_('back'); ?>" class="button" onclick="javascript:window.location='./index.php?m=contacts';" />
		</td>
	</tr>
</table>
<?php
  // tabbed information boxes
  $tabBox = new CTabBox('?m=contacts&a=' . $a . '&contact_id=' . $contact_id, '', $tab);
  //$tabBox->add(W2P_BASE_DIR . '/modules/departments/vw_contacts', 'Contacts');
  // include auto-tabs with 'view' explicitly instead of $a, because this view is also included in the main index site
  $tabBox->show();