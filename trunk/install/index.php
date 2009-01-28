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
	require_once W2P_BASE_DIR . '/includes/main_functions.php';
	require_once W2P_BASE_DIR . '/install/manager.class.php';
	require_once W2P_BASE_DIR . '/install/install.inc.php';

	$step = trim( w2PgetCleanParam( $_POST, 'step', '' ) );
	$manager = new UpgradeManager();
?>
<html>
	<head>
		<title>web2Project Update Manager</title>
		<meta name="Description" content="web2Project Update Manager">
	 	<link rel="stylesheet" type="text/css" href="../style/web2project/main.css">
	</head>
	<body>
		<table cellspacing="0" cellpadding="3" border="0" class="tbl" width="90%" align="center" style="margin-top: 20px;">
			<tr>
			  <td class="item" colspan="2">Welcome to the web2Project Update Manager!</td>
			</tr>
			<?php
			$action = $manager->getActionRequired();
			switch ($action) {
				case 'install':
					?>
					<tr>
						<td colspan="2">This system will help you perform each of the required steps to prepare your web2project installation.</td>
					</tr>
					<?php if ($step == '') { ?>
						<tr>
							<td colspan="2">
								When you're ready to being, simply 
							  <form action="<?php $baseUrl; ?>" method="post" name="form" id="form">
							  	<input type="hidden" name="step" value="check" />
							  	<input class="button" type="submit" name="next" value="Start Installation &raquo;" />
								</form>
							</td>
						</tr>
					<?php
					}
					break;
				case 'convert':
					?>
					<tr>
						<td colspan="2">This is where the conversion script kicks in.</td>
					</tr>
					<?php if ($step == '') { ?>
						<tr>
							<td colspan="2">
								When you're ready to being, simply 
							  <form action="<?php $baseUrl; ?>" method="post" name="form" id="form">
							  	<input type="hidden" name="step" value="check" />
							  	<input class="button" type="submit" name="next" value="Start Conversion &raquo;" />
								</form>
							</td>
						</tr>
					<?php
					}
					break;
				case 'upgrade':
					?>
					<tr>
						<td colspan="2">This is where the upgrade script kicks in.</td>
					</tr>
					<?php
					break;
				default:
					?>
					<tr>
						<td colspan="2">You've attempted to perform an invalid action. Stop that.</td>
					</tr>
					<?php
			}

			switch ($action.'/'.$step) {
				case 'install/check':
				case 'install/dbcreds':
				case 'install/perform':
				case 'convert/check':
				case 'convert/perform':
				case 'upgrade/check':
				case 'upgrade/perform':
					/*
					 *  Doing  something like this is often a security risk.  It's not in
					 * this case as we know *exactly* what both $action and $step will be
					 * if we reach this include.
					 */
					include_once $action.'/'.$step.'.php';
					break;
				default:
					//do nothing
			}
			?>
		</table>
	</body>
</html>