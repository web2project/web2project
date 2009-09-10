<?php
	if (!defined('W2P_BASE_DIR')) {
		die('You should not access this file directly.');
	}

	require_once W2P_BASE_DIR . '/lib/adodb/adodb.inc.php';
	require_once W2P_BASE_DIR . '/includes/version.php';

	$dbtype = trim( w2PgetCleanParam( $_POST, 'dbtype', 'mysql' ) );
	$dbhost = trim( w2PgetCleanParam( $_POST, 'dbhost', '' ) );
	$dbname = trim( w2PgetCleanParam( $_POST, 'dbname', '' ) );
	$dbuser = trim( w2PgetCleanParam( $_POST, 'dbuser', '' ) );
	$dbpass = trim( w2PgetCleanParam( $_POST, 'dbpass', '' ) );
	$dbprefix = trim( w2PgetCleanParam( $_POST, 'dbprefix', '' ) );
	$adminpass = trim( w2PgetCleanParam( $_POST, 'adminpass', 'passwd' ) );
	$adminpass = ($adminpass == '') ? 'passwd' : $adminpass;
	$dbpersist = w2PgetCleanParam( $_POST, 'dbpersist', false );

	$do_db = isset($_POST['do_db']);
	$do_db_cfg = isset($_POST['do_db_cfg']);
	$do_cfg = isset($_POST['do_cfg']);

	// Create a w2Pconfig array for dependent code
	$w2Pconfig = array(
	 'dbtype' => $dbtype,
	 'dbhost' => $dbhost,
	 'dbname' => $dbname,
	 'dbpass' => $dbpass,
	 'dbuser' => $dbuser,
	 'dbpersist' => $dbpersist,
	 'root_dir' => $baseDir,
	 'base_url' => $baseUrl,
	 'adminpass' => $adminpass
	);
	if (!$manager->testDatabaseCredentials($w2Pconfig)) {
		?>
		<table cellspacing="0" cellpadding="3" border="0" class="tbl" width="90%" align="center" style="margin-top: 20px;">
			<tr>
			  <td colspan="2" align="center">
			  	<b class="error">Your database credentials failed.  System installation has stopped.  Please correct them and try again.</b><br /><br />
				  <form action="<?php echo $baseUrl; ?>/index.php" method="post" name="form" id="form" accept-charset="utf-8">
			  		<input type="hidden" name="step" value="dbcreds" />
			  		<input class="button" type="submit" name="next" value="Reset System Credentials &raquo;" />
					</form>
				</td>
			</tr>
		</table>
		<?php		
		die();
	}

	$dbMsg = 'Not Created';
	$cFileMsg = 'Not Created';
	$dbErr = false;
	$cFileErr = false;
	$errorMessages = array();

	if (($do_db || $do_db_cfg)) {
		$errorMessages = $manager->upgradeSystem();
		if (count($errorMessages) == 0) {
			$dbMsg = 'Created';
		} else {
			$dbMsg = 'Created, some problems have occurred.';
		}
	}
	
	$config = $manager->createConfigString($w2Pconfig);

	if ($do_cfg || $do_db_cfg){
		if ((is_writable(W2P_BASE_DIR.'/includes/config.php')  || !is_file(W2P_BASE_DIR.'/includes/config.php')) && ($fp = @fopen(W2P_BASE_DIR.'/includes/config.php', 'w'))) {
			fputs( $fp, $config, strlen( $config ) );
			fclose( $fp );
			$cFileMsg = 'Config file written successfully'."\n";
		} else {
			$cFileErr = true;
			$cFileMsg = 'Config file could not be written'."\n";
		}
	}
?>

<table cellspacing="0" cellpadding="3" border="0" class="tbl" width="90%" align="center" style="margin-top: 20px;">
	<tr>
		<td class="title" colspan="2">Step 3: Create Database &amp; Write Configuration</td>
	</tr>
	<tr>
	  <td colspan="2">
	  	Your database is now being installed and configured.  I would suggest 
	  	going out to smoke a cigarette or get a cup of coffee except that 
	  	cigarettes aren't very healthy and.. oh wait, we're done.
	  </td>
	</tr>
	<tr><td colspan="2">&nbsp;</td></tr>
	<?php
		if (count($errorMessages) > 0) { ?>
			<tr>
				<td colspan="2"><b class="error">There were <?php echo count($errorMessages); ?> errors in the installation.</b></td>
			</tr>
			<?php
				foreach ($errorMessages as $message) { 
					?><tr><td colspan="2"><?php echo $message; ?></td></tr><?php
				}
			?>
			<tr>
				<td colspan="2">Note: Errors noting 'Duplicate entry', 'Table already exists', or 'Unknown table' are not likely to be problems.  Sit back and relax.</td>
			</tr>
			<?php
		}
	?>
	<tr>
		<td class="title" valign="top">Database Installation Feedback:</td>
		<td class="item">
			<b style="color:<?php echo $dbErr ? 'red' : 'green'; ?>"><?php echo $dbMsg; ?></b>
			<?php if ($dbErr) { ?> <br />
				Please note that errors relating to dropping indexes during upgrades are <b>NORMAL</b> and do not indicate a problem.
			<?php } ?>
		</td>
	<tr>
	<tr>
		<td class="title">Config File Creation Feedback:</td>
		<td class="item" align="left"><b style="color:<?php echo $cFileErr ? 'red' : 'green'; ?>"><?php echo $cFileMsg; ?></b></td>
	</tr>
	<?php if(($do_cfg || $do_db_cfg) && $cFileErr){ ?>
		<tr>
			<td class="item" align="left" colspan="2">The following Content should go to ./includes/config.php. Create that text file manually and copy the following lines in by hand and save.  This file must be readable by the webserver.</td>
		</tr>
		<tr>
			<td align="center" colspan="2"><textarea class="button" name="dbhost" cols="100" rows="20" title="Content of config.php for manual creation." /><?php echo $msg.$config; ?></textarea></td>
		</tr>
	<?php } ?>
	<tr>
		<td class="item" align="center" colspan="2">
			<?php if ($cFileErr) { ?><b style="color: red;">You MUST manually create ./includes/config.php before continuing.</b><br /><?php } ?>
			<b><a href="../index.php?m=system&amp;a=systemconfig">Login and Check the web2project System Environment</a></b>
		</td>
	</tr>
	<tr>
		<td class="item" align="center" colspan="2">
			<p>The Administrator login has been set to <b>admin</b> with the password <?php echo ($adminpass == 'passwd') ? 'of <b>passwd</b>' : 'you set' ?></b>. It is a good idea to change this password when you first log in</p>
		</td>
	</tr>
</table>