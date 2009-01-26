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
		<form name="instFrm" action="do_install_db.php" method="post">
			<input type="hidden" name="mode" value="install" />
			<table cellspacing="0" cellpadding="3" border="0" class="tbl" width="90%" align="center" style="margin-top: 20px;">
				<tr>
				  <td class="item" colspan="2">Welcome to the web2Project Installer! It 
				  will setup the database for web2Project and create an appropriate config file.
					In some cases a manual installation cannot be avoided.
				  </td>
				</tr>
				<tr>
					<td class="title" colspan="2">Database Settings</td>
				</tr>
				<tr>
					<td class="item">Database Server Type</td>
					<td align="left">
						<select name="dbtype" size="1" style="width:200px;" class="text" disabled="true">
							<option value="mysql" selected="selected">MySQL</option>
						</select>
					</td>
				</tr>
				<tr>
					<td class="item">Database Host Name</td>
					<td align="left">
						<input class="button" type="text" name="dbhost" value="<?php echo $w2Pconfig['dbhost']; ?>" title="The name of the host the database server is installed on" />
					</td>
				</tr>
				<tr>
					<td class="item">Database Name</td>
					<td align="left"><input class="button" type="text" name="dbname" value="<?php echo  $w2Pconfig['dbname']; ?>" title="The name of the database web2project will use and/or install" /></td>
				</tr>
				<tr>
					<td class="item">Database User Name</td>
					<td align="left"><input class="button" type="text" name="dbuser" value="<?php echo $w2Pconfig['dbuser']; ?>" title="The database user that web2project uses for database connection" /></td>
				</tr>
				<tr>
					<td class="item">Database User Password</td>
					<td align="left"><input class="button" type="password" name="dbpass" value="<?php echo $w2Pconfig['dbpass']; ?>" title="The password for the above user." /></td>
				</tr>
				<tr>
					<td class="item">Use Persistent Connection?</td>
					<td align="left"><input type="checkbox" name="dbpersist" value="1" <?php echo ($w2Pconfig['dbpersist']==true) ? 'checked="checked"' : ''; ?> title="Use a persistent Connection to your Database Server." /></td>
				</tr>
				<tr>
					<td class="item">Specify an Admin Password</td>
					<td align="left"><input class="button" type="password" name="adminpass" value="" title="The password for the admin user." /></td>
				</tr>
				<tr>
					<td colspan="2">&nbsp;</td>
				</tr>
				<tr>
					<td align="center" colspan="2">						
						<input class="button" type="submit" name="do_db" value="install db only" title="Try to set up the database with the given information." />
						&nbsp;<input class="button" type="submit" name="do_cfg" value="write config file only" title="Write a config file with the details only." /><br /><br />
						Recommended: &nbsp;<input class="button" type="submit" name="do_db_cfg" value="<?php echo $_POST['mode']; ?> db & write cfg" title="Write config file and setup the database with the given information." />
					</td>
				</tr>
			</table>
		</form>
	</body>
</html>