<?php /* $Id$ $URL$ */
require_once 'base.php';
require_once W2P_BASE_DIR . '/includes/config.php';

if (!isset($GLOBALS['OS_WIN'])) {
	$GLOBALS['OS_WIN'] = (stristr(PHP_OS, 'WIN') !== false);
}

// tweak for pathname consistence on windows machines
require_once W2P_BASE_DIR . '/includes/main_functions.php';
require_once W2P_BASE_DIR . '/includes/db_adodb.php';

$AppUI = new CAppUI();

if (w2PgetConfig('activate_external_user_creation') != 'true') {
	echo $AppUI->_('You should not access this file directly');
	die();
}

$uistyle = 'web2project';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<title><?php echo $AppUI->_('New User Signup'); ?></title>
		<meta http-equiv="Content-Type" content="text/html;charset=<?php echo 'UTF-8'; ?>" />
		<meta http-equiv="Pragma" content="no-cache" />
        <link rel="stylesheet" type="text/css" href="./style/common.css" media="all" charset="utf-8"/>
		<link rel="stylesheet" type="text/css" href="./style/<?php echo $uistyle; ?>/main.css" media="all" charset="utf-8"/>
		<style type="text/css" media="all">@import "./style/<?php echo $uistyle; ?>/main.css";</style>
		<link rel="shortcut icon" href="./style/<?php echo $uistyle; ?>/favicon.ico" type="image/ico" charset="utf-8"/>
	</head>

	<body bgcolor="#f0f0f0" onload="//document.loginform.username.focus();">
		<?php include 'createuser.php'; ?>

		<?php if ($AppUI->getVersion()) { ?>
			<div align="center">
				<span style="font-size:7pt">Version <?php echo $AppUI->getVersion(); ?></span>
			</div>
		<?php } ?>
		<div align="center">
			<?php
				echo '<span class="error">' . $AppUI->getMsg() . '</span>';
			?>
		</div>
	</body>
</html>