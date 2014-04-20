<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$contact_id = (int) w2PgetParam($_GET, 'contact_id', 0);

$tab = $AppUI->processIntState('ContactVwTab', $_GET, 'tab', 0);

$contact = new CContact();

if (!$contact->load($contact_id)) {
    $AppUI->redirect(ACCESS_DENIED);
}

$canEdit   = $contact->canEdit();
$canDelete = $contact->canDelete();

$is_user = $contact->isUser($contact_id);

// Get the contact details for company and department
$company_detail = $contact->getCompanyDetails();
$dept_detail = $contact->getDepartmentDetails();

// Get the Contact info (phone, emails, etc) for the contact
$methods = $contact->getContactMethods();
$methodLabels = w2PgetSysVal('ContactMethods');

// setup the title block
$ttl = 'View Contact';
$titleBlock = new w2p_Theme_TitleBlock($ttl, 'icon.png', $m);
$titleBlock->addCrumb('?m=contacts', 'contacts list');
if ($canEdit) {
	$titleBlock->addCrumb('?m=contacts&a=addedit&contact_id='.$contact_id, 'edit this contact');
}
if ($contact->user_id) {
    $titleBlock->addCrumb('?m=users&a=view&user_id='.$contact->user_id, 'view this user');
}
if ($canDelete) {
	$titleBlock->addCrumbDelete('delete contact', $canDelete, $msg);
}
$titleBlock->show();

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
<?php }

include $AppUI->getTheme()->resolveTemplate('contacts/view');