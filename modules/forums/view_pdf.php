<?php /* $Id: view_pdf.php 1517 2010-12-05 08:07:54Z caseydk $ $URL: https://web2project.svn.sourceforge.net/svnroot/web2project/trunk/modules/forums/view_pdf.php $ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not call this file directly.');
}

$sort = w2PgetParam($_REQUEST, 'sort', 'asc');
$forum_id = w2PgetParam($_REQUEST, 'forum_id', 0);
$message_id = w2PgetParam($_REQUEST, 'message_id', 0);

$perms = &$AppUI->acl();
if (!$perms->checkModuleItem('forums', 'view', $message_id)) {
	$AppUI->redirect('m=public&a=access_denied');
}

$forum = new CForum();
$forum->loadFull($AppUI, $forum_id);

$messages = $forum->getMessages($AppUI, $forum_id, $message_id, $sort);

// get the prefered date format
$df = $AppUI->getPref('SHDATEFORMAT');
$df .= ' ' . $AppUI->getPref('TIMEFORMAT');

$pdfdata = array();
$pdfhead = array('Date', 'User', 'Message');

foreach ($messages as $row) {
	// Find the parent message - the topic.
	if ($row['message_id'] == $message_id) {
		$topic = $row['message_title'];
	}

    $date = new w2p_Utilities_Date($AppUI->formatTZAwareTime($row['message_date'], '%Y-%m-%d %T'));
	$pdfdata[] = array($date->format($df), $row['contact_display_name'], '<b>' . $row['message_title'] . '</b>' . "\n" . $row['message_body']);
}

$font_dir = W2P_BASE_DIR . '/lib/ezpdf/fonts';
$temp_dir = W2P_BASE_DIR . '/files/temp';
require ($AppUI->getLibraryClass('ezpdf/class.ezpdf'));

$pdf = new Cezpdf($paper = 'A4', $orientation = 'portrait');
$pdf->ezSetCmMargins(1, 1, 1, 1);
$pdf->selectFont($font_dir . '/Helvetica.afm');
$pdf->ezText('Project: ' . $forum->project_name);
$pdf->ezText('Forum: ' . $forum->forum_name);
$pdf->ezText('Topic: ' . $topic);
$pdf->ezText('');
$options = array('showLines' => 1, 'showHeadings' => 1, 'fontSize' => 8,
    'rowGap' => 2, 'colGap' => 5, 'xPos' => 35, 'xOrientation' => 'right', 'width' => '400',
    'cols' => array(0 => array('justification' => 'left', 'width' => 75),
                    1 => array('justification' => 'left', 'width' => 100),
                    2 => array('justification' => 'left', 'width' => 350), ));

$pdf->ezTable($pdfdata, $pdfhead, null, $options);

$pdf->ezStream();