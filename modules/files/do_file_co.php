<?php
if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly.');
}
// @todo    refactor to use a core controller

//addfile sql
$file_id = (int) w2PgetParam($_POST, 'file_id', 0);
$coReason = w2PgetParam($_POST, 'file_co_reason', '');

$perms = &$AppUI->acl();
if (!$perms->checkModuleItem('files', 'edit', $file_id)) {
    $AppUI->redirect(ACCESS_DENIED);
}

$obj = new CFile();
if ($file_id) {
    $obj->_message = 'updated';
    $oldObj = new CFile();
    $oldObj->load($file_id);

} else {
    $obj->_message = 'added';
}

if (!$obj->bind($_POST)) {
    $AppUI->setMsg($obj->getError(), UI_MSG_ERROR);
    $AppUI->redirect();
}

if (!ini_get('safe_mode')) {
    set_time_limit(600);
}
ignore_user_abort(1);

$obj->checkout($AppUI->user_id, $file_id, $coReason);

// We now have to display the required page
// Destroy the post stuff, and allow the page to display index.php again.
$a = 'index';
unset($_GET['a']);

$params = 'file_id=' . $file_id;
$session_id = SID;

session_write_close();
// are the params empty
// Fix to handle cookieless sessions
if ($session_id != '') {
    $params .= "&" . $session_id;
}
echo '<script type="text/javascript">
fileloader = window.open("fileviewer.php?' . $params . '", "mywindow",
"location=1,status=1,scrollbars=0,width=20,height=20");
fileloader.moveTo(0,0);
</script>';
