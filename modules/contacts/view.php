<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$contact_id = (int) w2PgetParam($_GET, 'contact_id', 0);



$contact = new CContact();
$contact->contact_id = $contact_id;

$canEdit   = $contact->canEdit();
$canRead   = $contact->canView();
$canCreate = $contact->canCreate();
$canAccess = $contact->canAccess();
$canDelete = $contact->canDelete();

if (!$canAccess || !$canRead) {
	$AppUI->redirect(ACCESS_DENIED);
}

$contact->load($contact_id);
if (!$contact) {
	$AppUI->setMsg('Contact');
	$AppUI->setMsg('invalidID', UI_MSG_ERROR, true);
	$AppUI->redirect();
} else {
	$AppUI->savePlace();
}

$tab = $AppUI->processIntState('ContactVwTab', $_GET, 'tab', 0);

$df = $AppUI->getPref('SHDATEFORMAT');
$df .= ' ' . $AppUI->getPref('TIMEFORMAT');


$is_user = $contact->isUser($contact_id);

$countries = w2PgetSysVal('GlobalCountries');

// Get the contact details for company and department
$company_detail = $contact->getCompanyDetails();
$dept_detail = $contact->getDepartmentDetails();

// Get the Contact info (phone, emails, etc) for the contact
$methods = $contact->getContactMethods();
$methodLabels = w2PgetSysVal('ContactMethods');

// setup the title block
$ttl = 'View Contact';
$titleBlock = new w2p_Theme_TitleBlock($ttl, 'monkeychat-48.png', $m, $m . '.' . $a);
$titleBlock->addCrumb('?m=contacts', 'contacts list');
if ($canEdit && $contact_id) {
	$titleBlock->addCrumb('?m=contacts&a=addedit&contact_id='.$contact_id, 'edit this contact');
}
if ($canDelete && $contact_id) {
	$titleBlock->addCrumbDelete('delete contact', $canDelete, $msg);
}
$titleBlock->show();

$htmlHelper = new w2p_Output_HTMLHelper($AppUI);

$last_ask = new w2p_Utilities_Date($contact->contact_updateasked);
$lastupdated = new w2p_Utilities_Date($contact->contact_lastupdate);

?>
<form name="changecontact" action="?m=contacts" method="post" accept-charset="utf-8">
        <input type="hidden" name="dosql" value="do_contact_aed" />
        <input type="hidden" name="del" value="0" />
        <input type="hidden" name="contact_id" value="<?php echo $contact_id; ?>" />
        <input type="hidden" name="contact_owner" value="<?php echo $contact->contact_owner ? $contact->contact_owner : $AppUI->user_id; ?>" />
</form>
<?php if ($canDelete) { ?>
<script language="javascript" type="text/javascript">
function delIt(){
	var form = document.changecontact;
	if(confirm('<?php echo $AppUI->_('contactsDelete', UI_OUTPUT_JS); ?>')) {
		form.del.value = '<?php echo $contact_id; ?>';
		form.submit();
	}
}
</script>
<?php } ?>

<table border="0" cellpadding="4" cellspacing="0" width="100%" class="std view">
	<tr>
		<td valign="top">
			<table border="0" cellpadding="1" cellspacing="1">
				<tr>
					<td align="right" nowrap="nowrap"><?php echo $AppUI->_('First Name'); ?>:</td>
                    <?php

                    // TODO HTMLhelper was confused renamed field name so HTMLhelper is sane...
                    echo $htmlHelper->createCell('contact_firstname', $contact->contact_first_name); ?>
				</tr>
				<tr>
					<td align="right" nowrap="nowrap">&nbsp;&nbsp;<?php echo $AppUI->_('Last Name'); ?>:</td>
                    <?php

                    // TODO HTMLhelper was confused renamed field name so HTMLhelper is sane...
                    echo $htmlHelper->createCell('contact_lastname', $contact->contact_last_name); ?>
				</tr>
				<tr>
					<td align="right" width="100"><?php echo $AppUI->_('Display Name'); ?>: </td>
                    <?php

                    // TODO HTMLhelper was confused renamed field name so HTMLhelper is sane...
                    echo $htmlHelper->createCell('contact_displayname', $contact->contact_display_name); ?>
				</tr>
				<tr>
					<td align="right"><?php echo $AppUI->_('Job Title'); ?>:</td>
                    <?php echo $htmlHelper->createCell('contact_job', $contact->contact_job); ?>
				</tr>
				<tr>
					<td align="right" width="100"><?php echo $AppUI->_('Company'); ?>:</td>
					<td nowrap="nowrap" class="hilite" width="100%">
						<?php
                        $perms = &$AppUI->acl();
                        if ($perms->checkModuleItem('companies', 'access', $contact->contact_company)) { ?>
							<?php echo "<a href='?m=companies&a=view&company_id=" . $contact->contact_company . "'>" . htmlspecialchars($company_detail['company_name'], ENT_QUOTES) . '</a>'; ?>
						<?php } else { ?>
							<?php echo htmlspecialchars($company_detail['company_name'], ENT_QUOTES); ?>
						<?php } ?>
					</td>
				</tr>
				<tr>
					<td align="right" width="100"><?php echo $AppUI->_('Department'); ?>:</td>
                    <?php echo $htmlHelper->createCell('contact_department', $contact->contact_department); ?>
				</tr>
				<tr>
					<td align="right"><?php echo $AppUI->_('Title'); ?>:</td>
                    <?php echo $htmlHelper->createCell('contact_title', $contact->contact_title); ?>
				</tr>
				<tr>
					<td align="right"><?php echo $AppUI->_('Type'); ?>:</td>
                    <?php echo $htmlHelper->createCell('contact_type', $contact->contact_type); ?>
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
					<td align="right"><?php echo $AppUI->_('Birthday'); ?>:</td>
                    <?php echo $htmlHelper->createCell('_date', $contact->contact_birthday); ?>
				</tr>
				<tr>
					<td align="right"><?php echo $AppUI->_('Phone'); ?>:</td>
                    <?php echo $htmlHelper->createCell('contact_phone', $contact->contact_phone); ?>
				</tr>
				<tr>
					<td align="right"><?php echo $AppUI->_('Email'); ?>:</td>
                    <?php echo $htmlHelper->createCell('contact_email', $contact->contact_email); ?>
				</tr>
                <?php
                    $fields = $methods['fields'];
                    foreach ($fields as $key => $field): ?>
                    <tr>
                        <td align="right" width="100" nowrap="nowrap"><?php echo $AppUI->_($methodLabels[$field]); ?>:</td>
                        <?php echo $htmlHelper->createCell('_'.substr($field, 0, strpos($field, '_')), $methods['values'][$key]); ?>
                    </tr>
                <?php endforeach; ?>
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
					<td align="center" nowrap="nowrap">
                        <?php
                            echo ($contact->contact_lastupdate && !($contact->contact_lastupdate == 0)) ? $AppUI->formatTZAwareTime($contact->contact_lastupdate) : '';
                        ?>
                    </td>
				</tr>	
			</table>
		</td>
	</tr>
	<tr>
		<td valign="top" width="50%">
			<table border="0" cellpadding="1" cellspacing="1" class="details" width="100%">
				<?php
					$custom_fields = new w2p_Core_CustomFields($m, $a, $contact->contact_id, 'view');
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
			<?php echo w2p_textarea($contact->contact_notes); ?>
		</td>
	</tr>
	<tr>
		<td>
			<input type="button" value="<?php echo $AppUI->_('back'); ?>" class="button" onclick="javascript:window.location='./index.php?m=contacts';" />
		</td>
	</tr>
</table>