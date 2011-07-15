<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
$perms = &$AppUI->acl();
if (!canView('system')) { // let's see if the user has sys access
	$AppUI->redirect('m=public&a=access_denied');
}

$AppUI->savePlace();

$titleBlock = new CTitleBlock('System Administration', '48_my_computer.png', $m, $m . '.' . $a);
$titleBlock->show();

?>
<table class="std" width="100%" border="0" cellpadding="0" cellspacing="5">
  <tr>
    <td width="42">
      <?php echo w2PshowImage('control-center.png', 42, 42, ''); ?>
    </td>
    <td align="left" class="subtitle">
      <?php echo $AppUI->_('System Status'); ?>
    </td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td align="left">
      <?php
        $system = new CSystem();
        if ('admin@web2project.net' == w2PgetConfig('admin_email', 'admin@web2project.net')) {
            ?>
            <a href="?m=system&a=systemconfig#admin_email"><?php echo $AppUI->_('Set the Admin Email Address'); ?></a> -
            <span class="error"><?php echo $AppUI->_('Please set an email address for your Web2project Administrator.'); ?></span>
            <?php
        }
        echo '<br />';
        if ($system->upgradeRequired()) {
          ?>
          <a href="?m=system&u=upgrade"><?php echo $AppUI->_('Apply System Updates'); ?></a> -
          <span class="error"><?php echo $AppUI->_('Your upgrade is not complete. Please apply the updates immediately.'); ?></span>
          <?php
        } else {
            echo $AppUI->_('All installed update scripts have been executed.');
        }
        echo '<br />';
        $tzName = w2PgetConfig('system_timezone');
        if(strlen($tzName) == 0) {
            $tzName = ini_get('date.timezone');
        }
        if (strlen($tzName) > 0) {
            $time = new DateTimeZone($tzName);
            $x = new DateTime();
            $offset = $time->getOffset($x);
            $offset = ($offset >= 0) ? '+'.$offset/3600 : $offset/3600;
            echo $AppUI->_('Your system has a default timezone of GMT'.$offset.'.');
        } else {
          ?>
          <a href="?m=system&a=systemconfig#system_timezone"><?php echo $AppUI->_('Select a Timezone'); ?></a> -
          <span class="error"><?php echo $AppUI->_('You do not have a default server timezone selected. Please select one immediately.'); ?></span>
          <?php
        }
        echo '<br />';
        $availableVersion = w2PgetConfig('available_version', '');
        if (version_compare($AppUI->getVersion(), $availableVersion, '<')) {
            ?>
            <a href="http://sourceforge.net/projects/web2project/" target="_new"><?php echo $AppUI->_('Upgrade Available!'); ?></a> -
            <span class="error"><?php echo $AppUI->_('Your system should be upgraded to v'.$availableVersion.'.  Please upgrade at your earliest convenience.'); ?></span>
            <?php
        } else {
            echo $AppUI->_('Your system is the latest version available.');
        }
      ?>
    </td>
  </tr>
  <tr>
    <td width="42">
      <?php echo w2PshowImage('rdf2.png', 42, 42, ''); ?>
    </td>
    <td align="left" class="subtitle">
      <?php echo $AppUI->_('Language Support'); ?>
    </td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td align="left">
      <a href="?m=system&a=translate"><?php echo $AppUI->_('Translation Management'); ?></a>
    </td>
  </tr>
  <tr>
    <td>
      <?php echo w2PshowImage('myevo-weather.png', 42, 42, ''); ?>
    </td>
    <td align="left" class="subtitle">
      <?php echo $AppUI->_('Preferences'); ?>
    </td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td align="left">
      <a href="?m=system&a=systemconfig"><?php echo $AppUI->_('System Configuration'); ?></a><br />
      <a href="?m=system&a=addeditpref"><?php echo $AppUI->_('Default User Preferences'); ?></a><br />
      <a href="?m=system&u=syskeys&a=keys"><?php echo $AppUI->_('System Lookup Keys'); ?></a><br />
      <a href="?m=system&u=syskeys"><?php echo $AppUI->_('System Lookup Values'); ?></a><br />
      <a href="?m=system&u=customfields"><?php echo $AppUI->_('Custom Field Editor'); ?></a><br />
      <a href="?m=system&a=billingcode"><?php echo $AppUI->_('Billing Code Table'); ?></a>
    </td>
  </tr>
  <tr>
    <td>
      <?php echo w2PshowImage('power-management.png', 42, 42, ''); ?>
    </td>
    <td align="left" class="subtitle">
      <?php echo $AppUI->_('Modules'); ?>
    </td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td align="left">
      <a href="?m=system&a=viewmods"><?php echo $AppUI->_('View Modules'); ?></a>
    </td>
  </tr>
  <tr>
    <td>
      <?php echo w2PshowImage('main-settings.png', 42, 42, ''); ?>
    </td>
    <td align="left" class="subtitle">
      <?php echo $AppUI->_('Administration'); ?>
    </td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td align="left">
      <a href="?m=system&u=roles"><?php echo $AppUI->_('User Roles'); ?></a><br />
      <a href="?m=system&a=acls_view"><?php echo $AppUI->_('Users Permissions Information'); ?></a><br />
      <a href="?m=system&a=contacts_ldap"><?php echo $AppUI->_('Import Contacts'); ?></a><br />
      <a href="?m=system&a=phpinfo&suppressHeaders=1" target="_blank"><?php echo $AppUI->_('PHP Info'); ?></a>
    </td>
  </tr>
</table>
