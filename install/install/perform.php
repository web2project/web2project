<?php
	if (!defined('W2P_BASE_DIR')) {
		die('You should not access this file directly.');
	}

	require_once W2P_BASE_DIR . '/includes/version.php';

	$dbtype = trim( w2PgetParam( $_POST, 'dbtype', 'mysqli' ) );
	$dbhost = trim( w2PgetParam( $_POST, 'dbhost', '' ) );
	$dbname = trim( w2PgetParam( $_POST, 'dbname', '' ) );
	$dbuser = trim( w2PgetParam( $_POST, 'dbuser', '' ) );
	$dbpass = trim( w2PgetParam( $_POST, 'dbpass', '' ) );
	$adminpass = trim( w2PgetParam( $_POST, 'adminpass', 'passwd' ) );
	$adminpass = ($adminpass == '') ? 'passwd' : $adminpass;
	$dbpersist = w2PgetParam( $_POST, 'dbpersist', false );
        
    $system_timezone = trim( w2PgetParam( $_POST, 'system_timezone', '' ) );
    $user_timezone = trim( w2PgetParam( $_POST, 'user_timezone', '' ) );

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
	 'base_url' => W2P_BASE_URL,
	 'adminpass' => $adminpass,
         'system_timezone' => $system_timezone,
         'user_timezone' => $user_timezone
	);
	if (!$manager->testDatabaseCredentials($w2Pconfig)) {
		?>
        <div class="std update">
			<div class="my-2 text-center">
			  	<b class="error">Your database credentials failed.  System installation has stopped.  Please correct them and try again.</b><br /><br />
				  <form action="<?php echo W2P_BASE_URL; ?>/index.php" method="post" name="form" id="form" accept-charset="utf-8">
			  		<input type="hidden" name="step" value="dbcreds" />
			  		<input class="button" type="submit" name="next" value="&laquo; Reset System Credentials" />
					</form>
				</div>
		</div>
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

<div class="std update">
	<h3 class="h6 mt-3 title">Step 3: Create Database &amp; Write Configuration</h3>
	<div class="my-2">
	  	Your database is now being installed and configured.  I would suggest 
	  	going out to smoke a cigarette or get a cup of coffee except that 
	  	cigarettes aren't very healthy and.. oh wait, we're done.
	  </div>
	<?php
		if (count($errorMessages) > 0) { ?>
			<div class="my-2"><b class="error">There were <?php echo count($errorMessages); ?> errors in the installation.</b></div>
			<?php
				foreach ($errorMessages as $message) { 
					?><div class="my-2"><?php echo $message; ?></div><?php
				}
			?>
			<div class="my-2">Note: Errors noting 'Duplicate entry', 'Table already exists', or 'Unknown table' are not likely to be problems.  Sit back and relax.</div>
			<?php
		}
	?>
	<div class="row py-1 border-bottom">
		<div class="col-sm-5 title">Database Installation Feedback:</div>
		<div class="col-sm-7 item">
			<b style="color:<?php echo $dbErr ? 'red' : 'green'; ?>"><?php echo $dbMsg; ?></b>
			<?php if ($dbErr) { ?> <br />
				Please note that errors relating to dropping indexes during upgrades are <b>NORMAL</b> and do not indicate a problem.
			<?php } ?>
		</div>
	</div>
	<div class="row py-1 border-bottom">
		<div class="col-sm-5 title">Config File Creation Feedback:</div>
		<div class="col-sm-7 item"><b style="color:<?php echo $cFileErr ? 'red' : 'green'; ?>"><?php echo $cFileMsg; ?></b></div>
	</div>
	<?php if(($do_cfg || $do_db_cfg) && $cFileErr){ ?>
		<div class="my-2 item">The following Content should go to ./includes/config.php. Create that text file manually and copy the following lines in by hand and save.  This file must be readable by the webserver.</div>
		<div class="my-2 text-center"><textarea class="button" name="dbhost" cols="100" rows="20" title="Content of config.php for manual creation."><?php echo $msg.$config; ?></textarea></div>
	<?php } ?>
	<div class="my-2 text-center item">
			<?php if ($cFileErr) { ?><b style="color: red;">You MUST manually create ./includes/config.php before continuing.</b><br /><?php } ?>
			<b><a href="../index.php?m=system&amp;a=systemconfig">Login and Check the web2project System Environment</a></b>
		</div>
	<div class="my-2 text-center item">
			<p>The Administrator login has been set to <b>admin</b> with the password <?php echo ($adminpass == 'passwd') ? 'of <b>passwd</b>' : 'you set' ?></b>. It is a good idea to change this password when you first log in</p>
		</div>
</div>