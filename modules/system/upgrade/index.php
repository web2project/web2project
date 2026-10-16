<?php
/** @var w2p_Core_CAppUI $AppUI */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
$perms = $AppUI->acl();

// let's see if the user has sys access
if (!canEdit('system')) {
	$AppUI->redirect(ACCESS_DENIED);
}

$titleBlock = new w2p_Theme_TitleBlock($AppUI->_('Upgrade System'), 'control-center.png', $m);
$titleBlock->addCrumb('?m=system', 'system admin');
$titleBlock->show();

$failedImg = w2PshowImage('log-error.gif', 16, 16, $AppUI->_('Failed'));
$okImg = w2PshowImage('log-notice.gif', 16, 16, $AppUI->_('OK'));

$continue = true;
?>
<style>
.update {
    font-size: medium;
}
.update img {
    float: left;
}
</style>
<div class="std update">
	<h3 class="h6 mt-3 title">Step 1: Check System Settings</h3>
	<div class="my-2">
			There is a check for (minimal) requirements appended below for
			troubleshooting. Since your system is being upgraded, all requirements
			should be met, but it never hurts checking anyway.
		</div>
	<h3 class="h6 mt-3 title">Confirm Requirements</h3>
	<div class="row py-1 border-bottom">
	    <div class="col-sm-5 item">PHP Version &gt;= <?= MIN_PHP_VERSION; ?></div>
	    <div class="col-sm-7">
			<?php
			if (version_compare(PHP_VERSION, MIN_PHP_VERSION, '<')) {
                echo '<b class="error">'.$failedImg.' ('.PHP_VERSION.'): web2Project requires PHP '.MIN_PHP_VERSION.'+. Please upgrade!</b>';
				$continue = false;
			} else {
				echo '<b class="ok">'.$okImg.'</b> <span class="item">('.PHP_VERSION.')</span>';
			}
			?>
		</div>
	</div>
	<div class="row py-1 border-bottom">
	    <div class="col-sm-5 item">GD Support (for GANTT Charts)</div>
	    <div class="col-sm-7">
		<?php
			if (!extension_loaded('gd')) {
				echo '<b class="error">'.$failedImg.'</b> <span class="item">GANTT Chart functionality may not work correctly.</span>';
				$continue = false;
			} else {
				echo '<b class="ok">'.$okImg.'</b>';
			}
		?>
		</div>
	</div>
	<div class="row py-1 border-bottom">
	    <div class="col-sm-5 item">File Uploads</div>
	    <div class="col-sm-7">
		<?php
			if (!ini_get('file_uploads') && is_writable(W2P_BASE_DIR.'/files')) {
				echo '<b class="error">'.$failedImg.'</b> <span class="warning">Upload functionality will not be available, please make the ./files directory writable.</span>';
				$continue = false;
			} else {
				echo '<b class="ok">'.$okImg.'</b>';
			}
		?>
		</div>
	</div>
	<h3 class="h6 mt-3 title">Check requirements for optional components</h3>
	<div class="row py-1 border-bottom">
	    <div class="col-sm-5 item">Session Save Path writable?</div>
	    <div class="col-sm-7">
			<?php
				$sspath = ini_get('session.save_path');
				if (! $sspath) {
					echo '<b class="error">'.$failedImg.'</b> <span class="warning">session.save_path</span> <b class="error">is not set</b>';
				} else if (is_dir($sspath) && is_writable($sspath)) {
					echo "<b class='ok'>$okImg</b> <span class='item'>($sspath)</span>";
				} else {
					echo '<b class="error">'.$failedImg.'</b> <span class="warning">'.$sspath.'</span><b class="error"> not existing or not writable</b>';
				}
			?>
		</div>
	</div>
  <div class="row py-1 border-bottom">
      <div class="col-sm-5 item">Server API</div>
      <div class="col-sm-7">
    <?php
      if (strpos(strtolower(php_sapi_name()), 'cgi') !== false) {
        echo '<b class="error">'.$failedImg.' CGI mode is likely to have problems</b>';
      } else {
        echo '<b class="ok">'.$okImg.'</b> <span class="item">('.php_sapi_name().')</span>';
      }
    ?>
    </div>
  </div>
	<div class="my-2 text-center item"><b class="error">Please confirm that your database has been backed up properly before running this process.  You can never have too many backups.</b></div>
	<div class="my-2 text-center">
		  <form action="<?php echo W2P_BASE_URL; ?>/index.php?m=system&u=upgrade&a=perform" method="post" name="form" id="form" accept-charset="utf-8">
		  	<?php if ($continue) { ?>
		  		<input class="button" type="submit" name="next" value="Perform Upgrade &raquo;" />
		  	<?php } else { ?>
		  		<input class="button" type="button" value="Upgrade Stopped" onClick="alert('The above issues must be fixed before continuing.')" />
		  	<?php } ?>
			</form>
		</div>
</div>