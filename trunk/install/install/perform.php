<?php
	if (!defined('W2P_BASE_DIR')) {
		die('You should not access this file directly.');
	}

	require_once W2P_BASE_DIR . '/lib/adodb/adodb.inc.php';
	require_once W2P_BASE_DIR . '/includes/version.php';

	$AppUI = new InstallerUI; // Fake AppUI class to appease the db_connect utilities.

	$dbtype = trim( w2PgetCleanParam( $_POST, 'dbtype', 'mysql' ) );
	$dbhost = trim( w2PgetCleanParam( $_POST, 'dbhost', '' ) );
	$dbname = trim( w2PgetCleanParam( $_POST, 'dbname', '' ) );
	$dbuser = trim( w2PgetCleanParam( $_POST, 'dbuser', '' ) );
	$dbpass = trim( w2PgetCleanParam( $_POST, 'dbpass', '' ) );
	$dbprefix = trim( w2PgetCleanParam( $_POST, 'dbprefix', '' ) );
	//TODO: add support for database prefixes
	$adminpass = trim( w2PgetCleanParam( $_POST, 'adminpass', 'passwd' ) );
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
	 'base_url' => $baseUrl
	);

	$db = NewADOConnection($dbtype);
	
	if(!empty($db)) {
	  $dbc = $db->Connect($dbhost, $dbuser, $dbpass);
	  if ($dbc) {
	    $existing_db = $db->SelectDB($dbname);
	  }
	} else { 
		$dbc = false;
	}
	
	$dbMsg = '';
	$cFileMsg = 'Not Created';
	$dbErr = false;
	$cFileErr = false;
	
	// Version array for moving from version to version.
	$versionPath = array();
	
	$lastDBUpdate = '';
	$current_version = $w2p_version_major . '.' . $w2p_version_minor;
	$current_version .= isset($w2p_version_patch) ? ('.'.$w2p_version_patch) : '';
	$current_version .= isset($w2p_version_prepatch) ? ('-'.$w2p_version_prepatch) : '';
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
	<tr>
		<td colspan="2">
			<pre><?php
				if ($dbc && ($do_db || $do_db_cfg)) {
					if (! $existing_db) {
						w2pmsg('Creating new Database');
						$db->Execute('CREATE DATABASE '.$dbname);
						$dbError = $db->ErrorNo();
						if ($dbError <> 0 && $dbError <> 1007) {
							$dbErr = true;
							$dbMsg .= 'A Database Error occurred. Database has not been created! The provided database details are probably not correct.<br>'.$db->ErrorMsg().'<br>';
						}
					}

					// For some reason a db->SelectDB call here doesn't work.
					$db->Execute('USE ' . $dbname);
					$db_version = InstallGetVersion($mode, $db);
				
					$code_updated = '';

					$sql = "SELECT * FROM w2pversion";
					$res = $db->Execute($sql);
					if ($res && $res->RecordCount() > 0) {
						w2pmsg('Skipping database install. The database is already installed.');
					} else {
					  w2pmsg('Installing database');
					  InstallLoadSql(W2P_BASE_DIR.'/sql/001_base_install.mysql.sql', null, $adminpass);
					  // After all the updates, find the new version information.
					  $new_version = InstallGetVersion($mode, $db);
					  $lastDBUpdate = $new_version['last_db_update'];
					  $code_updated = $new_version['last_code_update'];										
					}
				
					$dbError = $db->ErrorNo();
					if ($dbError <> 0 && $dbError <> 1007) {
						$dbErr = true;
						$dbMsg .= 'A Database Error occurred. Database has probably not been populated completely!<br>'.$db->ErrorMsg().'<br>';
					}
					if ($dbErr) {
						$dbMsg = 'DB setup incomplete - the following errors occured:<br />'.$dbMsg;
					} else {
						$dbMsg = 'Database successfully setup<br />';
					}
				
					w2pmsg('Updating version information');
					// No matter what occurs we should update the database version in the w2pversion table.
					if (empty($lastDBUpdate)) {
						$lastDBUpdate = $code_updated;
					}
					$sql = "UPDATE w2pversion SET db_version = '$w2p_version_major',
						last_db_update = '$lastDBUpdate', code_version = '$current_version',
						last_code_update = '$code_updated' WHERE 1";
					$db->Execute($sql);
				} else {
					$dbMsg = 'Not Created';
					if (! $dbc) {
						$dbErr=1;
						$dbMsg .= '<br/>No Database Connection available! '  . ($db ? $db->ErrorMsg() : '');
					}
				}

				$config = file_get_contents('../includes/config-dist.php');
				$config = str_replace('[DBTYPE]', $dbtype, $config);
				$config = str_replace('[DBHOST]', $dbhost, $config);
				$config = str_replace('[DBNAME]', $dbname, $config);
				$config = str_replace('[DBUSER]', $dbuser, $config);
				$config = str_replace('[DBPASS]', $dbpass, $config);
				$config = str_replace('[DBPREFIX]', $dbprefix, $config);
				//TODO: add support for configurable persistent connections
			
				$config = trim($config);
			
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
			?></pre>
		</td>
	</tr>
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
		<td class="item" align="center" colspan="2"><br/><b><a href="<?php echo '../index.php?m=system&a=systemconfig'; ?>">Login and Configure the web2project System Environment</a></b></td>
	</tr>
	<tr>
		<td class="item" align="center" colspan="2">
			<p>The Administrator login has been set to <b>admin</b> with the password <?php echo ($adminpass == 'passwd') ? 'of <b>passwd</b>' : 'you set' ?></b>. It is a good idea to change this password when you first log in</p>
		</td>
	</tr>
</table>