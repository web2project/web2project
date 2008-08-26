<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly');
}
?>
<script language="javascript">
function submitIt(){
    var form = document.editFrm;
   if (form.user_username.value.length < <?php echo w2PgetConfig('username_min_len'); ?>) {
        alert("<?php echo $AppUI->_('Username size is invalid, should be greater than', UI_OUTPUT_JS); ?>" + ' ' + <?php echo w2PgetConfig('username_min_len'); ?>);
        form.user_username.focus();
    } else if (form.user_password.value.length < <?php echo w2PgetConfig('password_min_len'); ?>) {
        alert("<?php echo $AppUI->_('Password size is invalid, should be greater than', UI_OUTPUT_JS); ?>" + ' ' + <?php echo w2PgetConfig('password_min_len'); ?>);
        form.user_password.focus();
    } else if (form.user_password.value !=  form.password_check.value) {
        alert("<?php echo $AppUI->_('Password confirmation failed', UI_OUTPUT_JS); ?>");
        form.user_password.focus();
    } else if (form.contact_first_name.value.length < 1) {
        alert("<?php echo $AppUI->_('First name is invalid', UI_OUTPUT_JS); ?>");
        form.contact_first_name.focus();
    } else if (form.contact_last_name.value.length < 1) {
        alert("<?php echo $AppUI->_('Last name is invalid', UI_OUTPUT_JS); ?>");
        form.contact_last_name.focus();
    } else if (form.contact_email.value.length < 4) {
        alert("<?php echo $AppUI->_('Email is invalid', UI_OUTPUT_JS); ?>");
        form.contact_email.focus();
	} else if (form.spam_check.value.length < 1 ) {
        alert("<?php echo $AppUI->_('Anti Spam Security is invalid', UI_OUTPUT_JS); ?>");
    } else {
        form.submit();
    }
}
</script>
<table width="100%" cellspacing="0" cellpadding="0" border="0">
<tbody><tr>
	<td width="508"><a href="http://www.web2project.net"><img border="0" alt="web2Project Home" src="../style/<?php echo $uistyle; ?>/w2p_logo.jpg"/></a></td>
	<td style="background: url(../style/<?php echo $uistyle; ?>/logo_bkgd.jpg)">&nbsp;</td>
</tr>
</tbody>
</table>
<table width="100%" cellspacing="0" cellpadding="0" border="0">
<tbody>
	<tr>
	<td valign="top" style="background: url(../style/<?php echo $uistyle; ?>/nav_shadow.jpg)" align="left">
		<img width="1" height="13" border="0" src="../style/<?php echo $uistyle; ?>/nav_shadow.jpg"/>
	</td>
</tr>
</tbody>
</table>

<table align="center" border="0" width="75%" cellpadding="0" cellspacing="0" class="">
<tr>
	<td style="padding-top:10px;padding-bottom:10px;" align="left" valign="top" class="txt"><h1>Welcome to web2Project Installer!</h1>
	This interface will guide you through the installation of web2Project on your server.<br /><br />
	Below you must provide the necessary information required to interact with the Database that you should have previously created.<br />
	Please check any Fatal Errors, which prevent the install process, and fix them before installing.<br />
	The Warnings section provides you information regarding portions of the product that may not work properly due to the lack of system capabilities. 
	</td>
</tr>
</table>
<form name="setupFrm" action="./do_setup.php" method="post">
	<input type="hidden" name="cid" value="<?php echo $cid ?>" />

<table class="text" style="background-color:#FFFFFF;" align="center" border="0" width="75%" cellpadding="0" cellspacing="0">	
<tr>
    <td class="item" align="center" colspan="3"><b>Database Connection Settings</b></td>
</tr>
<tr>
    <td width="10">&nbsp;</td>
	<td>&nbsp;</td>
</tr>
<tr>
    <td width="10">&nbsp;</td>
    <td>Database Server Type</td>
    <td align="left">
	<select name="dbtype" size="1" style="width:227px;" class="text">
		<option value="mysql" selected="selected">MySQL</option>
	</select>
	</td>
</tr>
<tr>
    <td width="10">&nbsp;</td>
	<td>Database Host Name</td>
	<td align="left"><input class="text" maxlength="255" size="40" type="text" name="dbhost" value="<?php echo $w2Pconfig['dbhost']; ?>" title="The Name of the Host the Database Server is installed on" /></td>
</tr>
<tr>
    <td width="10">&nbsp;</td>
	<td>Database Name</td>
	<td align="left"><input class="text" maxlength="255" size="40" type="text" name="dbname" value="<?php echo  $w2Pconfig['dbname']; ?>" title="The Name of the Database web2Project will use and/or install" /></td>
</tr>
<tr>
    <td width="10">&nbsp;</td>
	<td>Database User Name</td>
	<td align="left"><input class="text" maxlength="255" size="40" type="text" name="dbuser" value="<?php echo $w2Pconfig['dbuser']; ?>" title="The Database User that web2Project uses for Database Connection" /></td>
</tr>
<tr>
    <td width="10">&nbsp;</td>
	<td>Database User Password</td>
	<td align="left"><input class="text" maxlength="255" size="40" type="password" name="dbpass" value="<?php echo $w2Pconfig['dbpass']; ?>" title="The Password according to the above User." /></td>
</tr>
<tr>
    <td width="10">&nbsp;</td>
	<td>Table Prefix</td>
	<td align="left"><input class="text" maxlength="255" size="40" type="text" name="dbprefix" value="<?php echo $w2Pconfig['dbprefix']; ?>" title="Prefix to be used when naming web2Projects Database Tables" /></td>
</tr>
<tr>
    <td width="10">&nbsp;</td>
	<td>Use Persistent Connection?</td>
	<td align="left"><input type="checkbox" name="dbpersist" value="1" <?php echo ($w2Pconfig['dbpersist']==true) ? 'checked="checked"' : ''; ?> title="Use a persistent Connection to your Database Server." /></td>
</tr>
<tr>
    <td width="10">&nbsp;</td>
	<td>&nbsp;</td>
</tr>
<tr>
    <td width="10">&nbsp;</td>
    <td colspan="2" align="right">
        <input type="button" value="<?php echo $AppUI->_('Install!'); ?>" onclick="submitIt()" class="button" />&nbsp;&nbsp;&nbsp;
    </td>
</tr>
<tr>
    <td width="10">&nbsp;</td>
	<td>&nbsp;</td>
</tr>
</table>