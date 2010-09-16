<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$canRead = canView('contacts');
if (!$canRead) {
	$AppUI->redirect("m=public&a=access_denied");
}

// Fields 1 - 5
$text = sprintf("%s", "\"Title\",\"First Name\",\"Middle Name\",\"Last Name\",\"Suffix\",");
// Fields 6 - 10
$text .= sprintf("%s", "\"Company\",\"Department\",\"Job Title\",\"Business Street\",\"Business Street 2\",");
// Fields 11 - 15
$text .= sprintf("%s", "\"Business Street 3\",\"Business City\",\"Business State\",\"Business Postal Code\",\"Business Country\",");
// Fields 16 - 20
$text .= sprintf("%s", "\"Home Street\",\"Home Street 2\",\"Home Street 3\",\"Home City\",\"Home State\",");
// Fields 21 - 25
$text .= sprintf("%s", "\"Home Postal Code\",\"Home Country\",\"Other Street\",\"Other Street 2\",\"Other Street 3\",");
// Fields 26 - 30
$text .= sprintf("%s", "\"Other City\",\"Other State\",\"Other Postal Code\",\"Other Country\",\"Assistant's Phone\",");
// Fields 31 - 35
$text .= sprintf("%s", "\"Business Fax\",\"Business Phone\",\"Business Phone 2\",\"Callback\",\"Car Phone\",");
// Fields 36 - 40
$text .= sprintf("%s", "\"Company Main Phone\",\"Home Fax\",\"Home Phone\",\"Home Phone 2\",\"ISDN\",");
// Fields 41 - 45
$text .= sprintf("%s", "\"Mobile Phone\",\"Other Fax\",\"Other Phone\",\"Pager\",\"Primary Phone\",");
// Fields 46 - 50
$text .= sprintf("%s", "\"Radio Phone\",\"TTY/TDD Phone\",\"Telex\",\"Account\",\"Anniversary\",");
// Fields 51 - 55
$text .= sprintf("%s", "\"Assistant's Name\",\"Billing Information\",\"Birthday\",\"Categories\",\"Children\",");
// Fields 56 - 60
$text .= sprintf("%s", "\"Directory Server\",\"E-mail Address\",\"E-mail Type\",\"E-mail Display Name\",\"E-mail 2 Address\",");
// Fields 61 - 65
$text .= sprintf("%s", "\"E-mail 2 Type\",\"E-mail 2 Display Name\",\"E-mail 3 Address\",\"E-mail 3 Type\",\"E-mail 3 Display Name\",");
// Fields 66 - 70
$text .= sprintf("%s", "\"Gender\",\"Government ID Number\",\"Hobby\",\"Initials\",\"Internet Free Busy\",");
// Fields 71 - 75
$text .= sprintf("%s", "\"Keywords\",\"Language\",\"Location\",\"Manager's Name\",\"Mileage\",");
// Fields 76 - 80
$text .= sprintf("%s", "\"Notes\",\"Office Location\",\"Organizational ID Number\",\"PO Box\",\"Priority\",");
// Fields 81 - 85
$text .= sprintf("%s", "\"Private\",\"Profession\",\"Referred By\",\"Sensitivity\",\"Spouse\",");
// Fields 86 - 90
$text .= sprintf("%s", "\"User 1\",\"User 2\",\"User 3\",\"User 4\",\"Web Page\",");

$custom_fields = w2p_Core_CustomFields::getCustomFieldList('contacts');

foreach ($custom_fields as $f) {
	$text .= sprintf("%s", "\"$f[field_description]\",");
}
$text .= sprintf("%s\r\n", "");

$contactList = CContact::searchContacts($AppUI);
	
foreach ($contactList as $contact) {
	$myContact = new CContact();
    $myContact->contact_id = $contact['contact_id'];
    $contactMethods = $myContact->getContactMethods();
    
    // Fields 1- 10
	$text .= sprintf("\"\",\"%s\",\"\",\"%s\",\"\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",", $contact['contact_first_name'], $contact['contact_last_name'], $contact['company_name'], $contact['dept_name'], $contact['contact_title'], $contact['contact_address1'], $contact['contact_address2']);
	// Fields 11- 20
	$text .= sprintf(",\"%s\",\"%s\",\"%s\",,,,,,,", $contact['contact_city'], $contact['contact_state'], $contact['contact_zip']);
	// Fields 21- 30
	$text .= sprintf(",,,,,,,,,,");
	// Fields 31- 40
	settype($contactMethods['phone_primary'], 'string');
	$text .= sprintf(",\"%s\",,,,,,,,,", $contactMethods['phone_primary']);
	// Fields 41- 50
	settype($contactMethods['phone_mobile'], 'string');
	$text .= sprintf("\"%s\",,,,,,,,\"\",\"0/0/00\",", '' . $contactMethods['phone_mobile']);
	// Fields 51- 60
	if ($contact['contact_type'] != '') {
		$categories = "web2Project; " . $contact['contact_type'];
	} else {
		$categories = "web2Project;";
	}
	$text .= sprintf(",,\"%s\",\"%s\",,,\"%s\",\"%s\",\"%s\",,", $contact['contact_birthday'], $categories, $contactMethods['email_primary'], "SMTP", $contact['contact_first_name'] . " " . $contact['contact_last_name']);
	// Fields 61- 70
	$text .= sprintf(",,,,,\"Unspecified\",,,,,");
	// Fields 71- 80
	$notes = mb_str_replace("\"", "\"\"", $contact['contact_notes']);
	$text .= sprintf("\"\",\"\",\"\",,,\"%s\",,,,\"Normal\",", $notes);
	// Fields 81- 90
	$text .= sprintf("\"False\",,,\"Normal\",,,,,,,");

	$custom_fields = w2p_Core_CustomFields::getCustomFieldByModule($AppUI, 'contacts', $contact['contact_id']);

	if (count($custom_fields) > 0) {
		foreach ($custom_fields as $f) {
			if ($f['value_intvalue']) {
				$text .= sprintf("%s", $f['list_value'].",");
			} else {
				$text .= sprintf("%s", "\"" . mb_str_replace("\r\n", " ", $f['value_charvalue']) . "\",");
			}
		}
	}
	$text .= sprintf("%s\r\n", '');
}

//send http-output in csv format

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
header('Content-Type: text/x-csv');
header('Content-Disposition: attachment; filename="'.$w2Pconfig['company_name'].'Contacts.csv"');
print_r($text);