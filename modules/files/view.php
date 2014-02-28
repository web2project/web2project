<?php
/**
 * This file exists exclusively because it allows the Files module to use the
 *   same url generation that the other modules do. Aka.. it makes our lives
 *   easier and it's one less thing to have to code around.
 */
if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly.');
}

$file_id = (int) w2PgetParam($_GET, 'file_id', 0);

$file = new CFile();

if (!$file->load($file_id)) {
    $AppUI->redirect(ACCESS_DENIED);
}

header("Location: fileviewer.php?file_id=" . $file_id);