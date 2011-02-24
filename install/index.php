<?php
require_once '../base.php';
require_once W2P_BASE_DIR . '/includes/main_functions.php';
if (version_compare(PHP_VERSION, MIN_PHP_VERSION, '<')) {
    echo 'web2Project requires PHP '.MIN_PHP_VERSION.'+. Please upgrade!';
    die();
}

$step = trim( w2PgetCleanParam( $_POST, 'step', '' ) );
$manager = new w2p_Core_UpgradeManager();
?>
<html>
	<head>
		<title>web2Project Update Manager</title>
		<meta name="Description" content="web2Project Update Manager">
        <link rel="stylesheet" type="text/css" href="../style/common.css" media="all" charset="utf-8"/>
	 	<link rel="stylesheet" type="text/css" href="../style/web2project/main.css" charset="utf-8"/>
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
						<td colspan="2">This system will help you perform each of the 
							required steps to prepare your web2project installation.  It's 
							a three step process.  First we'll confirm that all the 
							requirements are met, then we'll get the database credentials, 
							then we'll load the system.</td>
					</tr>
					<?php if ($step == '') { ?>
						<tr>
							<td colspan="2">
								When you're ready to begin, simply
							  <form action="<?php $baseUrl; ?>" method="post" name="form" id="form" accept-charset="utf-8">
							  	<input type="hidden" name="step" value="check" />
							  	<input class="button" type="submit" name="next" value="Start <?php echo ucwords($action); ?> &raquo;" />
								</form>
							</td>
						</tr>
					<?php
					}
					break;
				case 'conversion':
					?>
					<tr>
						<td colspan="2">This is where the conversion script kicks in.  
							It's a two step process.  First we'll confirm that all the 
							requirements are met, then we'll convert your existing data.<br />
							You shouldn't have to do anything manually except log in at the end.</td>
					</tr>
					<?php if ($step == '') { ?>
						<tr>
							<td colspan="2">
								When you're ready to begin, simply
							  <form action="<?php $baseUrl; ?>" method="post" name="form" id="form" accept-charset="utf-8">
							  	<input type="hidden" name="step" value="check" />
							  	<input class="button" type="submit" name="next" value="Start <?php echo ucwords($action); ?> &raquo;" />
								</form>
							</td>
						</tr>
					<?php
					}
					break;
				case 'upgrade':
          ?>
					<tr>
						<td colspan="2">The system upgrade is performed through the
                        <strong><a href="../index.php?m=system">System Admin</a></strong> and requires you
                        to be logged in with Admin access. Please click <strong><a href="../index.php?m=system">System Admin</a></strong> to continue.</td>
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
				case 'conversion/check':
				case 'conversion/perform':
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
