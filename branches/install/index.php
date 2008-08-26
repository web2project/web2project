<?php /* $Id$ $URL$ */
/*
All files in this work are now covered by the following copyright notice.
Please note that included libraries in the lib directory may have their own license.
Copyright (c) 2007-2008 The web2Project Development Team <developers@web2project.net>
Copyright (c) 2003-2005 The dotProject Development Team <core-developers@dotproject.net>

    This file is part of web2Project.

    web2Project is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    web2Project is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with web2Project; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

The full text of the GPL is in the COPYING file.
*/

require_once '../base.php';
include_once W2P_BASE_DIR . '/includes/config.php';
$uistyle = 'web2project';

if (!isset($GLOBALS['OS_WIN'])) {
	$GLOBALS['OS_WIN'] = (stristr(PHP_OS, 'WIN') !== false);
}

// tweak for pathname consistence on windows machines
require_once W2P_BASE_DIR . '/includes/main_functions.php';
require_once W2P_BASE_DIR . '/includes/db_adodb.php';
require_once W2P_BASE_DIR . '/classes/query.class.php';
require_once W2P_BASE_DIR . '/classes/ui.class.php';
$AppUI = new CAppUI();
include_once W2P_BASE_DIR . '/classes/w2p.class.php';
require_once W2P_BASE_DIR . '/classes/date.class.php';

require_once W2P_BASE_DIR . '/style/' . $uistyle . '/overrides.php';

require_once W2P_BASE_DIR.'/install/setup.inc.php';
require_once W2P_BASE_DIR.'/lib/adodb/adodb.inc.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title><?php echo 'web2Project Setup'; ?></title>
	<meta name="Description" content="<?php echo 'web2Project Setup'; ?>" />
	<meta http-equiv="Content-Type" content="text/html;charset=<?php echo 'UTF-8'; ?>" />
	<meta http-equiv="Pragma" content="no-cache" />
	<link rel="stylesheet" type="text/css" href="../style/<?php echo $uistyle; ?>/main.css" media="all" />
	<style type="text/css" media="all">@import "../style/<?php echo $uistyle; ?>/main.css";</style>
	<link rel="shortcut icon" href="../style/<?php echo $uistyle; ?>/images/favicon.ico" type="image/ico" />
	<?php $AppUI->loadHeaderJS(); ?>
	<script type="text/javascript" src="../js/base.js"></script>
</head>

<body bgcolor="#f0f0f0" onload="//document.loginform.username.focus();">
<?php
include 'setup.php';
$mode = w2PcheckUpgrade();
?>
<?php
if ($mode == 'upgrade') {
?>
<tr>
	<td class='title' colspan='2'><p class='error'>It would appear that you already have a web2Project installation. The installer will attempt to upgrade your system, however it is a good idea to take a full backup first!</p></td>
<?php
}
?>
</table>
<?php
include_once('setup_check.php');
?>
</body>
</html>