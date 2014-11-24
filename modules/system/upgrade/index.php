<?php
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
.update td {
    font-size: medium;
}
.update img {
    float: left;
}
</style>
<table class="tbl update">
	<tr>
		<td class="title" colspan="2">Step 1: Check System Settings</td>
	</tr>
	<tr>
		<td colspan="2">
			There is a check for (minimal) requirements appended below for
			troubleshooting. Since your system is being upgraded, all requirements
			should be met, but it never hurts checking anyway.
		</td>
	</tr>
	<tr>
		<td class="title" colspan="2">Confirm Requirements</td>
	</tr>
	<tr>
		<td class="item">PHP Version &gt;= <?= MIN_PHP_VERSION; ?></td>
		<td align="left">
			<?php
            if (version_compare(PHP_VERSION, MIN_PHP_VERSION, '<')) {
                echo '<b class="error">'.$failedImg.' ('.PHP_VERSION.'): web2Project requires PHP '.MIN_PHP_VERSION.'+. Please upgrade!</b>';
                $continue = false;
            } else {
                echo '<b class="ok">'.$okImg.'</b> <span class="item">('.PHP_VERSION.')</span>';
            }
            ?>
		</td>
	</tr>
	<tr>
		<td class="item">GD Support (for GANTT Charts)</td>
		<td align="left">
		<?php
            if (!extension_loaded('gd')) {
                echo '<b class="error">'.$failedImg.'</b> <span class="item">GANTT Chart functionality may not work correctly.</span>';
                $continue = false;
            } else {
                echo '<b class="ok">'.$okImg.'</b>';
            }
        ?>
		</td>
	</tr>
	<tr>
		<td class="item">File Uploads</td>
		<td align="left">
		<?php
            if (!ini_get('file_uploads') && is_writable(W2P_BASE_DIR.'/files')) {
                echo '<b class="error">'.$failedImg.'</b> <span class="warning">Upload functionality will not be available, please make the ./files directory writable.</span>';
                $continue = false;
            } else {
                echo '<b class="ok">'.$okImg.'</b>';
            }
        ?>
		</td>
	</tr>
	<tr>
		<td class="title" colspan="2"><br />Check requirements for optional components</td>
	</tr>
	<tr>
		<td class="item">Session Save Path writable?</td>
		<td align="left">
			<?php
                $sspath = ini_get('session.save_path');
                if (! $sspath) {
                    echo '<b class="error">'.$failedImg.'</b> <span class="warning">session.save_path</span> <b class="error">is not set</b>';
                } elseif (is_dir($sspath) && is_writable($sspath)) {
                    echo "<b class='ok'>$okImg</b> <span class='item'>($sspath)</span>";
                } else {
                    echo '<b class="error">'.$failedImg.'</b> <span class="warning">'.$sspath.'</span><b class="error"> not existing or not writable</b>';
                }
            ?>
		</td>
	</tr>
  <tr>
    <td class="item">Server API</td>
    <td align="left">
    <?php
      if (strpos(strtolower(php_sapi_name()), 'cgi') !== false) {
        echo '<b class="error">'.$failedImg.' CGI mode is likely to have problems</b>';
      } else {
        echo '<b class="ok">'.$okImg.'</b> <span class="item">('.php_sapi_name().')</span>';
      }
    ?>
    </td>
  </tr>
	<tr>
		<td colspan="2">&nbsp;</td>
	</tr>
	<tr>
		<td class="item" align="left" colspan="2" style="text-align: center;"><b class="error">Please confirm that your database has been backed up properly before running this process.  You can never have too many backups.</b></td>
	</tr>
	<tr>
	  <td colspan="2" align="center">
		  <form action="<?php echo W2P_BASE_URL; ?>/index.php?m=system&u=upgrade&a=perform" method="post" name="form" id="form" accept-charset="utf-8">
		  	<?php if ($continue) { ?>
		  		<input class="button" type="submit" name="next" value="Perform Upgrade &raquo;" />
		  	<?php } else { ?>
		  		<input class="button" type="button" value="Upgrade Stopped" onClick="alert('The above issues must be fixed before continuing.')" />
		  	<?php } ?>
			</form>
		</td>
	</tr>
</table>
