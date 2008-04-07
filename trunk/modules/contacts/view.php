<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$contact_id = intval(w2PgetParam($_GET, 'contact_id', 0));
$AppUI->savePlace();

//check permissions for this record
$perms = &$AppUI->acl();
$canRead = $perms->checkModuleItem($m, 'view', $contact_id);
$canAddProjects = $perms->checkModule('projects', 'add');
if (!$canRead) {
	$AppUI->redirect('m=public&a=access_denied');
}
$df = $AppUI->getPref('SHDATEFORMAT');
$df .= ' ' . $AppUI->getPref('TIMEFORMAT');

// load the record data
$msg = '';
$row = new CContact();
$canDelete = $row->canDelete($msg, $contact_id);
$is_user = $row->isUser($contact_id);

$canEdit = $perms->checkModuleItem($m, 'edit', $contact_id);

if (!$row->load($contact_id) && $contact_id > 0) {
	$AppUI->setMsg('Contact');
	$AppUI->setMsg('invalidID', UI_MSG_ERROR, true);
	$AppUI->redirect();
} elseif ($row->contact_private && $row->contact_owner != $AppUI->user_id && $row->contact_owner && $contact_id != 0) {
	// check only owner can edit
	$AppUI->redirect('m=public&a=access_denied');
}

$countries = w2PgetSysVal('GlobalCountries');

// Get the contact details for company and department
$company_detail = $row->getCompanyDetails();
$dept_detail = $row->getDepartmentDetails();

// setup the title block
$ttl = 'View Contact';
$titleBlock = new CTitleBlock($ttl, 'monkeychat-48.png', $m, $m . '.' . $a);
$titleBlock->addCrumb('?m=contacts', 'contacts list');
if ($canEdit && $contact_id) {
	$titleBlock->addCrumb('?m=contacts&a=addedit&contact_id='.$contact_id, 'edit');
}
if ($canAddProjects && $contact_id) {
	$titleBlock->addCell('<input type="submit" class="button" value="' . $AppUI->_('new project') . '" />', '', '<form action="?m=projects&a=addedit&company_id=' . $row->contact_company . '&contact_id=' . $contact_id . '" method="post">', '</form>');
}
if ($canDelete && $contact_id) {
	$titleBlock->addCrumbDelete('delete contact', $canDelete, $msg);
}
$titleBlock->show();
?>
<form name="changecontact" action="?m=contacts" method="post">
        <input type="hidden" name="dosql" value="do_contact_aed" />
        <input type="hidden" name="del" value="0" />
        <input type="hidden" name="contact_id" value="<?php echo $contact_id; ?>" />
        <input type="hidden" name="contact_owner" value="<?php echo $row->contact_owner ? $row->contact_owner : $AppUI->user_id; ?>" />
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
	<td colspan="2">
		<table border="0" cellpadding="1" cellspacing="1">
		<tr>
			<td align="right"><?php echo $AppUI->_('First Name'); ?>:</td>
			<td><?php echo $row->contact_first_name; ?></td>
		</tr>
		<tr>
			<td align="right">&nbsp;&nbsp;<?php echo $AppUI->_('Last Name'); ?>:</td>
			<td><?php echo $row->contact_last_name; ?></td>
		</tr>
		<tr>
			<td align="right" width="100"><?php echo $AppUI->_('Display Name'); ?>: </td>
			<td><?php echo $row->contact_order_by; ?></td>
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
				<input type="checkbox" value="1" name="contact_updateasked" disabled="disabled" <?php echo $row->contact_updatekey ? 'checked="checked"' : ''; ?> />
			</td>
		</tr>	
		<tr>
<?php
$last_ask = new CDate($row->contact_updateasked);
?>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Last Update Requested'); ?>:</td>
			<td align="center" nowrap="nowrap"><?php echo $row->contact_updateasked ? $last_ask->format($df) : ''; ?></td>
		</tr>	
		<tr>
		<tr>
<?php
$lastupdated = new CDate($row->contact_lastupdate);
?>
			<td align="right" width="100" nowrap="nowrap"><?php echo $AppUI->_('Last Updated'); ?>:</td>
			<td align="center" nowrap="nowrap"><?php echo ($row->contact_lastupdate && !($row->contact_lastupdate == 0)) ? $lastupdated->format($df) : ''; ?></td>
		</tr>	
		</table>
	</td>
</tr>
<tr>
	<td valign="top" width="50%">
		<table border="0" cellpadding="1" cellspacing="1" class="details" width="100%">
		<tr>
			<td align="right"><?php echo $AppUI->_('Job Title'); ?>:</td>
			<td><?php echo $row->contact_job; ?></td>
		</tr>
		<tr>
			<td align="right" width="100"><?php echo $AppUI->_('Company'); ?>:</td>
			<?php if ($perms->checkModuleItem('companies', 'access', $row->contact_company)) { ?>
            			<td nowrap="nowrap"> <?php echo "<a href='?m=companies&a=view&company_id=" . $row->contact_company . "'>" . htmlspecialchars($company_detail['company_name'], ENT_QUOTES) . '</a>'; ?></td>
			<?php } else { ?>
						<td nowrap="nowrap"><?php echo htmlspecialchars($company_detail['company_name'], ENT_QUOTES); ?></td>
			<?php } ?>
		</tr>
		<tr>
			<td align="right" width="100"><?php echo $AppUI->_('Department'); ?>:</td>
			<td nowrap="nowrap"><?php echo $dept_detail['dept_name']; ?></td>
		</tr>
		<tr>
			<td align="right"><?php echo $AppUI->_('Title'); ?>:</td>
			<td><?php echo $row->contact_title; ?></td>
		</tr>
		<tr>
			<td align="right"><?php echo $AppUI->_('Type'); ?>:</td>
			<td><?php echo $row->contact_type; ?></td>
		</tr>
		<tr>
			<td align="right" valign="top" width="100"><?php echo $AppUI->_('Address'); ?>:</td>
			<td>
                    <?php echo $row->contact_address1; ?><br />
			        <?php echo $row->contact_address2; ?><br />
			        <?php echo $row->contact_city . ', ' . $row->contact_state . ' ' . $row->contact_zip; ?><br />
			        <?php echo ($countries[$row->contact_country] ? $countries[$row->contact_country] : $row->contact_country); ?>
			        
           </td>
		</tr>
		<tr>
			<td align="right" width="100"><?php echo $AppUI->_('Map Address'); ?>:</td>
			<td><a target="_blank" href="http://maps.google.com/maps?q=<?php echo $row->contact_address1; ?>+<?php echo $row->contact_address2; ?>+<?php echo $row->contact_city; ?>+<?php echo $row->contact_state; ?>+<?php echo $row->contact_zip; ?>+<?php echo $row->contact_country; ?>"><?php echo w2PshowImage('googlemaps.gif', 55, 22, 'Find It on Google'); ?></a></td>
		</tr>
		<tr>
			<td align="right" width="100"><?php echo $AppUI->_('Work Phone'); ?>:</td>
			<td><?php echo $row->contact_phone; ?></td>
		</tr>
		<tr>
			<td align="right"><?php echo $AppUI->_('Home Phone'); ?>:</td>
			<td><?php echo $row->contact_phone2; ?></td>
		</tr>
		<tr>
			<td align="right"><?php echo $AppUI->_('Fax'); ?>:</td>
			<td><?php echo $row->contact_fax; ?></td>
		</tr>
		<tr>
			<td align="right"><?php echo $AppUI->_('Mobile Phone'); ?>:</td>
			<td><?php echo $row->contact_mobile; ?></td>
		</tr>
		<tr>
			<td align="right" width="100"><?php echo $AppUI->_('Email'); ?>:</td>
			<td nowrap="nowrap"><a href="mailto:<?php echo $row->contact_email; ?>"><?php echo $row->contact_email; ?></a></td>
		</tr>
		<tr>
			<td align="right"><?php echo $AppUI->_('Email'); ?>2:</td>
			<td nowrap="nowrap"><a href="mailto:<?php echo $row->contact_email2; ?>"><?php echo $row->contact_email2; ?></a></td>
		</tr>
		<tr>
			<td align="right"><?php echo $AppUI->_('Homepage'); ?>:</td>
			<td nowrap="nowrap"><a href="<?php echo $row->contact_url; ?>"><?php echo $row->contact_url; ?></a></td>
		</tr>
		<tr>
			<td align="right">Jabber:</td>
			<td><?php echo $row->contact_jabber; ?></td>
		</tr>
		<tr>
			<td align="right">ICQ:</td>
			<td><?php echo $row->contact_icq; ?></td>
		</tr>
		<tr>
			<td align="right">AOL:</td>
			<td><a href="aim:<?php echo $row->contact_aol; ?>"><?php echo $row->contact_aol; ?></a></td>
		</tr>
		<tr>
			<td align="right">MSN:</td>
			<td><?php echo $row->contact_msn; ?></td>
		</tr>
		<tr>
			<td align="right">Yahoo:</td>
			<td><a href="ymsgr:sendIM?<?php echo $row->contact_yahoo; ?>"><?php echo $row->contact_yahoo; ?></a></td>
		</tr>
		<tr>
			<td align="right">Skype:</td>
			<td><a href="skype:<?php echo $row->contact_skype; ?>"><?php echo $row->contact_skype; ?></a></td>
		</tr>
		<tr>
			<td align="right">Google:</td>
			<td><a href="google:<?php echo $row->contact_google; ?>"><?php echo $row->contact_google; ?></a></td>
		</tr>
		<tr>
			<td align="right"><?php echo $AppUI->_('Birthday'); ?>:</td>
			<td nowrap="nowrap"><?php echo substr($row->contact_birthday, 0, 10); ?></td>
		</tr>		
<?php
require_once ($AppUI->getSystemClass('CustomFields'));
$custom_fields = new CustomFields($m, $a, $row->contact_id, 'view');
if ($custom_fields->count()) {
?>
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
		<?php echo nl2br($row->contact_notes); ?>
	</td>
</tr>
<tr>
	<td>
		<input type="button" value="<?php echo $AppUI->_('back'); ?>" class="button" onclick="javascript:window.location='./index.php?m=contacts';" />
	</td>
</tr>
</form>
</table>