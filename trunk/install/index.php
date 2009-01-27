<?php
/*
	Copyright (c) 2007-2009 The web2Project Development Team <w2p-developers@web2project.net>
	Copyright (c) 2003-2005 The dotProject Development Team <core-developers@dotproject.net>

  This file is part of web2project.

  web2project is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  dotProject is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with dotProject; if not, write to the Free Software
  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

	The full text of the GPL is in the COPYING file.
*/

	require_once '../base.php';
?>
<html>
	<head>
		<title>web2Project Installer</title>
		<meta name="Description" content="web2Project Installer">
	 	<link rel="stylesheet" type="text/css" href="../style/web2project/main.css">
	</head>
	<body>
		<table cellspacing="0" cellpadding="3" border="0" class="tbl" width="90%" align="center" style="margin-top: 20px;">
			<tr>
			  <td class="item" colspan="2">Welcome to the web2Project Installer! It 
			  will setup the database for web2Project and create an appropriate config file.
				In some cases a manual installation cannot be avoided.
			  </td>
			</tr>
			<tr>
				<td colspan="2">&nbsp;</td>
			</tr>
			<tr>
				<td class="title" colspan="2">There is an initial Check for (minimal) 
				requirements appended below for troubleshooting. At minimum, a database 
				and corresponding database connection must be available.  In addition 
				../includes/config.php should be writable for the webserver.</td>
			</tr>
		</table>
		<br />
		<?php include_once('vw_idx_check.php'); ?>
	</body>
</html>