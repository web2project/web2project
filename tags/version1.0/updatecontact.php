<?php /* $Id$ $URL$ */
require_once 'base.php';
require_once W2P_BASE_DIR . '/includes/config.php';

if (!isset($GLOBALS['OS_WIN'])) {
	$GLOBALS['OS_WIN'] = (stristr(PHP_OS, "WIN") !== false);
}

// tweak for pathname consistence on windows machines
require_once W2P_BASE_DIR . '/includes/main_functions.php';
require_once W2P_BASE_DIR . '/includes/db_adodb.php';
require_once W2P_BASE_DIR . '/classes/query.class.php';
require_once W2P_BASE_DIR . '/classes/ui.class.php';
$AppUI = new CAppUI();
require_once ($AppUI->getSystemClass('date'));
require_once ($AppUI->getModuleClass('contacts'));

$updatekey = w2PgetParam($_GET, 'updatekey', 0);
$q = new DBQuery;
$contact_id = CContact::getContactByUpdatekey($updatekey);

$company_id = intval(w2PgetParam($_REQUEST, 'company_id', 0));
$company_name = w2PgetParam($_REQUEST, 'company_name', null);

// check permissions for this record

if (!$contact_id) {
	echo ($AppUI->_('You are not authorized to use this page. If you should be authorized please contact') . ' ' . $w2Pconfig['company_name'] . ' ' . $AppUI->_('to give you another valid link, thank you.'));
	exit;
}

// load the record data
$msg = '';
$row = new CContact();

if (!$row->load($contact_id) && $contact_id > 0) {
	$AppUI->setMsg('Contact');
	$AppUI->setMsg('invalidID', UI_MSG_ERROR, true);
	$AppUI->redirect();
} else {
	if ($row->contact_private && $row->contact_owner != $AppUI->user_id && $row->contact_owner && $contact_id != 0) {
		// check only owner can edit
		$AppUI->redirect('m=public&a=access_denied');
	}
}
$df = $AppUI->getPref('SHDATEFORMAT');
$df .= ' ' . $AppUI->getPref('TIMEFORMAT');

// setup the title block
$ttl = $contact_id > 0 ? 'Edit Contact' : 'Add Contact';
$company_detail = $row->getCompanyDetails();
$dept_detail = $row->getDepartmentDetails();
if ($contact_id == 0 && $company_id > 0) {
	$company_detail['company_id'] = $company_id;
	$company_detail['company_name'] = $company_name;
	echo $company_name;
}

$uistyle = 'web2project';
$outsider = $row->contact_first_name . ' ' . $row->contact_last_name;
require W2P_BASE_DIR . '/style/' . $uistyle . '/overrides.php';
require W2P_BASE_DIR . '/style/' . $uistyle . '/header.php';

if (function_exists('styleRenderBoxTop')) {
	echo styleRenderBoxTop();
}
?>

<script language="javascript">
function submitIt() {
	var form = document.changecontact;
	if (form.contact_last_name.value.length < 1) {
		alert( "<?php echo $AppUI->_('contactsValidName', UI_OUTPUT_JS); ?>" );
		form.contact_last_name.focus();
	} else if (form.contact_order_by.value.length < 1) {
		alert( "<?php echo $AppUI->_('contactsOrderBy', UI_OUTPUT_JS); ?>" );
		form.contact_order_by.focus();
	} else {
		form.submit();
	}
}

function orderByName( x ){
	var form = document.changecontact;
	if (x == 'name') {
		form.contact_order_by.value = form.contact_last_name.value + ", " + form.contact_first_name.value;
	} else {
		form.contact_order_by.value = form.contact_company_name.value;
	}
}
</script>

<form name="changecontact" action="do_updatecontact.php" method="post">
	<input type="hidden" name="contact_project" value="0" />
	<input type="hidden" name="contact_unique_update" value="<?php echo uniqid(''); ?>" />
	<input type="hidden" name="updatekey" value="<?php echo $updatekey; ?>" />
	<input type="hidden" name="contact_id" value="<?php echo $contact_id; ?>" />
	<input type="hidden" name="contact_owner" value="<?php echo $row->contact_owner ? $row->contact_owner : $AppUI->user_id; ?>" />
	<input type="hidden" name="contact_company" value="<?php echo $row->contact_company ? $row->contact_company : 0; ?>" />
	<input type="hidden" name="contact_department" value="<?php echo $row->contact_department ? $row->contact_department : 0; ?>" />
	<input type="hidden" class="text" size="25" name="contact_order_by" value="<?php echo $row->contact_order_by; ?>" maxlength="50" />

	<table border="0" cellpadding="4" cellspacing="0" width="100%" class="std">
		<tr>
			<td colspan="2">
				<table border="0" cellpadding="1" cellspacing="1">
					<tr>
						<td nowrap="nowrap">
							<strong><?php echo $AppUI->_('Please Edit Your Contact Information Below:'); ?></strong>
						</td>
						<td>
							<input type="hidden" class="text" size="25" name="contact_first_name" value="<?php echo $row->contact_first_name; ?>" maxlength="50" />
						</td>
					</tr>
					<tr>
						<td>
							<input type="hidden" class="text" size="25" name="contact_last_name" value="<?php echo $row->contact_last_name; ?>" maxlength="50" <?php if ($contact_id == 0) { ?> onblur="orderByName('name')"<?php } ?> />
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td valign="top" width="50%">
				<table border="0" cellpadding="1" cellspacing="1" class="details" width="100%">
					<tr>
						<td align="right" width="100"><?php echo $AppUI->_('Job Title'); ?>:</td>
						<td nowrap="nowrap">
							<input type="text" class="text" name="contact_job" value="<?php echo $row->contact_job; ?>" maxlength="100" size="25" />
						</td>
					</tr>
					<tr>
						<td align="right"><?php echo $AppUI->_('Title'); ?>:</td>
						<td><input type="text" class="text" name="contact_title" value="<?php echo $row->contact_title; ?>" maxlength="50" size="25" /></td>
					</tr>
					<tr>
						<td align="right"><?php echo $AppUI->_('Type'); ?>:</td>
						<td><input type="text" class="text" name="contact_type" value="<?php echo $row->contact_type; ?>" maxlength="50" size="25" /></td>
					</tr>
					<tr>
						<td align="right" width="100"><?php echo $AppUI->_('Address'); ?>1:</td>
						<td><input type="text" class="text" name="contact_address1" value="<?php echo $row->contact_address1; ?>" maxlength="60" size="25" /></td>
					</tr>
					<tr>
						<td align="right"><?php echo $AppUI->_('Address'); ?>2:</td>
						<td><input type="text" class="text" name="contact_address2" value="<?php echo $row->contact_address2; ?>" maxlength="60" size="25" /></td>
					</tr>
					<tr>
						<td align="right"><?php echo $AppUI->_('City'); ?>:</td>
						<td><input type="text" class="text" name="contact_city" value="<?php echo $row->contact_city; ?>" maxlength="30" size="25" /></td>
					</tr>
					<tr>
						<td align="right"><?php echo $AppUI->_('State'); ?>:</td>
						<td><input type="text" class="text" name="contact_state" value="<?php echo $row->contact_state; ?>" maxlength="30" size="25" /></td>
					</tr>
					<tr>
						<td align="right"><?php echo $AppUI->_('Postcode') . ' / ' . $AppUI->_('Zip'); ?>:</td>
						<td><input type="text" class="text" name="contact_zip" value="<?php echo $row->contact_zip; ?>" maxlength="11" size="25" /></td>
					</tr>
					<tr>
						<td align="right"><?php echo $AppUI->_('Country'); ?>:</td>
						<td>
							<?php
								$countries = array('' => $AppUI->_('(Select a Country)')) + w2PgetSysVal('GlobalCountries');
								echo arraySelect($countries, 'contact_country', 'size="1" class="text"', $row->contact_country ? $row->contact_country : 0);
							?>
						</td>
					</tr>
					<tr>
						<td align="right" width="100"><?php echo $AppUI->_('Work Phone'); ?>:</td>
						<td>
							<input type="text" class="text" name="contact_phone" value="<?php echo $row->contact_phone; ?>" maxlength="30" size="25" />
						</td>
					</tr>
					<tr>
						<td align="right"><?php echo $AppUI->_('Home Phone'); ?>:</td>
						<td>
							<input type="text" class="text" name="contact_phone2" value="<?php echo $row->contact_phone2; ?>" maxlength="30" size="25" />
						</td>
					</tr>
					<tr>
						<td align="right"><?php echo $AppUI->_('Fax'); ?>:</td>
						<td>
							<input type="text" class="text" name="contact_fax" value="<?php echo $row->contact_fax; ?>" maxlength="30" size="25" />
						</td>
					</tr>
					<tr>
						<td align="right"><?php echo $AppUI->_('Mobile Phone'); ?>:</td>
						<td>
							<input type="text" class="text" name="contact_mobile" value="<?php echo $row->contact_mobile; ?>" maxlength="30" size="25" />
						</td>
					</tr>
					<tr>
						<td align="right" width="100"><?php echo $AppUI->_('Email'); ?>:</td>
						<td nowrap="nowrap">
							<input type="text" class="text" name="contact_email" value="<?php echo $row->contact_email; ?>" maxlength="255" size="25" />
						</td>
					</tr>
					<tr>
						<td align="right"><?php echo $AppUI->_('Email'); ?>2:</td>
						<td>
							<input type="text" class="text" name="contact_email2" value="<?php echo $row->contact_email2; ?>" maxlength="255" size="25" />
						</td>
					</tr>
					<tr>
						<td align="right"><?php echo $AppUI->_('Home Page'); ?>2:</td>
						<td>
							<input type="text" class="text" name="contact_url" value="<?php echo $row->contact_url; ?>" maxlength="255" size="25" />
						</td>
					</tr>
					<tr>
						<td align="right">Jabber:</td>
						<td>
							<input type="text" class="text" name="contact_jabber" value="<?php echo $row->contact_jabber; ?>" maxlength="255" size="25" />
						</td>
					</tr>
					<tr>
						<td align="right">ICQ:</td>
						<td>
							<input type="text" class="text" name="contact_icq" value="<?php echo $row->contact_icq; ?>" maxlength="20" size="25" />
						</td>
					</tr>
					<tr>
						<td align="right">AOL:</td>
						<td>
							<input type="text" class="text" name="contact_aol" value="<?php echo $row->contact_aol; ?>" maxlength="20" size="25" />
						</td>
					</tr>
					<tr>
						<td align="right">MSN:</td>
						<td>
							<input type="text" class="text" name="contact_msn" value="<?php echo $row->contact_msn; ?>" maxlength="255" size="25" />
						</td>
					</tr>
					<tr>
						<td align="right">Yahoo:</td>
						<td>
							<input type="text" class="text" name="contact_yahoo" value="<?php echo $row->contact_yahoo; ?>" maxlength="255" size="25" />
						</td>
					</tr>
					</tr>
					<tr>
						<td align="right"><?php echo $AppUI->_('Birthday'); ?>:</td>
						<td nowrap="nowrap">
							<input type="text" class="text" name="contact_birthday" value="<?php echo substr($row->contact_birthday, 0, 10); ?>" maxlength="10" size="25" />(<?php echo $AppUI->_('yyyy-mm-dd'); ?>)
						</td>
					</tr>
					<tr>
						<td align="right" colspan="3">
							<?php
								require_once W2P_BASE_DIR . '/classes/CustomFields.class.php';
								$custom_fields = new CustomFields('contacts', 'addedit', $row->contact_id, "edit", 1);
								$custom_fields->printHTML();
							?>
						</td>
					</tr>
				</table>
			</td>
			<td valign="top" width="50%">
				<strong><?php echo $AppUI->_('Contact Notes'); ?></strong><br />
				<textarea class="textarea" name="contact_notes" rows="20" cols="40"><?php echo $row->contact_notes; ?></textarea></td>
			</td>
		</tr>
		<tr>
			<td colspan="2"></td>
			<td align="right">
				<input type="button" value="<?php echo $AppUI->_('submit'); ?>" class="button" onclick="submitIt()" />
			</td>
		</tr>
	</table>
</form>
<?php
if (function_exists('styleRenderBoxTop')) {
	echo styleRenderBoxBottom();
}
?>