<?php /* $Id$ $URL$ */
$uistyle = 'web2project';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<title><?php echo 'New User Signup'; ?></title>
		<meta http-equiv="Content-Type" content="text/html;charset=<?php echo 'UTF-8'; ?>" />
		<meta http-equiv="Pragma" content="no-cache" />
		<link rel="stylesheet" type="text/css" href="./style/<?php echo $uistyle; ?>/main.css" media="all" />
		<style type="text/css" media="all">@import "./style/<?php echo $uistyle; ?>/main.css";</style>
		<link rel="shortcut icon" href="./style/<?php echo $uistyle; ?>/images/favicon.ico" type="image/ico" />
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
				$msg = '';
				$msg .= phpversion() < '4.1' ? '<br /><span class="warning">WARNING: web2project is NOT SUPPORT for this PHP Version (' . phpversion() . ')</span>' : '';
				$msg .= function_exists('mysql_pconnect') ? '' : '<br /><span class="warning">WARNING: PHP may not be compiled with MySQL support.  This will prevent proper operation of web2Project.  Please check you system setup.</span>';
				echo $msg;
			?>
		</div>
	</body>
</html>