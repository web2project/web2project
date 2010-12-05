<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

// get GETPARAMETER for contact_id
$contact_id = (int) w2PgetParam($_GET, 'contact_id', 0);

$canRead = canView('contacts');

if (!$canRead) {
	$AppUI->redirect('m=public&a=access_denied');
}

if ($contact_id) {

	$contact = new CContact();
	$contact->loadFull($AppUI, $contact_id);
    $contactMethods = $contact->getContactMethods();
	
	// include PEAR vCard class
	require_once ($AppUI->getLibraryClass('PEAR/Contact_Vcard_Build'));

	// instantiate a builder object
	// (defaults to version 3.0)
	$vcard = new Contact_Vcard_Build();

	// set a formatted name
	$vcard->setFormattedName($contact->contact_first_name . ' ' . $contact->contact_last_name);

	// set the structured name parts
	$vcard->setName($contact->contact_last_name, $contact->contact_first_name, $contact->contact_type, $contact->contact_title, '');

	// set the source of the vCard
	$vcard->setSource($w2Pconfig['company_name'] . ' ' . $w2Pconfig['page_title'] . ': ' . $w2Pconfig['site_domain']);

	// set the birthday of the contact
	$vcard->setBirthday($contact->contact_birthday);

	// set a note of the contact
	$contact->contact_notes = mb_str_replace("\r", ' ', $contact->contact_notes);
	$vcard->setNote($contact->contact_notes);

	// add an organization
	$vcard->addOrganization($contact->company_name);

	// add dp company id
	$vcard->setUniqueID($contact->contact_company);

	// add a phone number
	$vcard->addTelephone($contact->contact_phone);
	$vcard->addParam('TYPE', 'PF');

	// add a phone number
	$vcard->addTelephone($contactMethods['phone_alt']);

	// add a mobile phone number
	$vcard->addTelephone($contactMethods['phone_mobile']);
	$vcard->addParam('TYPE', 'car');

	// add a work email.  note that we add the value
	// first and the param after -- Contact_Vcard_Build
	// is smart enough to add the param in the correct
	// place.
	$vcard->addEmail($contact->contact_email);
	$vcard->addParam('TYPE', 'PF');

	// add a home/preferred email
	$vcard->addEmail($contactMethods['email_alt']);

	// add an address
	$vcard->addAddress('', $contact->contact_address2, $contact->contact_address1, $contact->contact_city, $contact->contact_state, $contact->contact_zip, $contact->contact_country);

	// get back the vCard
	$text = $vcard->fetch();

	//send http-output with this vCard

	// BEGIN extra headers to resolve IE caching bug (JRP 9 Feb 2003)
	// [http://bugs.php.net/bug.php?id=16173]
	header('Pragma: ');
	header('Cache-Control: ');
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
	header('Cache-Control: no-store, no-cache, must-revalidate'); //HTTP/1.1
	header('Cache-Control: post-check=0, pre-check=0', false);
	// END extra headers to resolve IE caching bug

	header('MIME-Version: 1.0');
	header('Content-Type: text/x-vcard');
	header('Content-Disposition: attachment; filename=' . $contact->contact_first_name . $contact->contact_last_name . '.vcf');
	print_r($text);
} else {
	$AppUI->setMsg('contactIdError', UI_MSG_ERROR);
	$AppUI->redirect();
}