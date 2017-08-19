<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly');
}

$theme = $AppUI->getTheme();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
        <title><?php echo $w2Pconfig['page_title']; ?></title>
        <meta http-equiv="Content-Type" content="text/html;charset=<?php echo isset($locale_char_set) ? $locale_char_set : 'UTF-8'; ?>" />
        <title><?php echo $w2Pconfig['company_name']; ?> :: web2Project Login</title>
        <meta http-equiv="Pragma" content="no-cache" />
        <meta name="Version" content="<?php echo $AppUI->getVersion(); ?>" />
        <link rel="stylesheet" type="text/css" href="./style/login.css" media="all" charset="utf-8"/>
        <link rel="stylesheet" type="text/css" href="./style/common.css" media="all" charset="utf-8"/>
        <link rel="stylesheet" type="text/css" href="./style/<?php echo $uistyle; ?>/main.css" media="all" charset="utf-8"/>
        <style type="text/css" media="all">@import "./style/<?php echo $uistyle; ?>/main.css";</style>
        <link rel="shortcut icon" href="./style/<?php echo $uistyle; ?>/favicon.ico" type="image/ico" />
    </head>

    <body bgcolor="#f0f0f0" onload="document.loginform.username.focus();">
        <table width="100%" cellspacing="0" cellpadding="0" border="0">
            <tbody>
                <tr>
                    <td width="508"><a href="http://www.web2project.net"><img src="./style/<?php echo $uistyle; ?>/images/w2p_logo.jpg" alt="web2Project Home" /></a></td>
                    <td style="background:url(./style/<?php echo $uistyle; ?>/images/logo_bkgd.jpg)">&nbsp;</td>
                </tr>
            </tbody>
        </table>
        <table width="100%" cellspacing="0" cellpadding="0" border="0">
            <tbody>
                <tr>
                    <td width="100%" valign="top" align="left" style="background: transparent url(./style/<?php echo $uistyle; ?>/images/nav_shadow.jpg) repeat-x scroll 0%;">
                        <img src="./style/<?php echo $uistyle; ?>/images/nav_shadow.jpg" />
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="container">
            <div class="login">
                <h1><?php echo $w2Pconfig['company_name']; ?></h1>
                <form method="post" action="<?php echo $loginFromPage; ?>" name="loginform" accept-charset="utf-8">
                    <input type="hidden" name="login" value="<?php echo time(); ?>" />
                    <input type="hidden" name="lostpass" value="0" />
                    <input type="hidden" name="redirect" value="<?php echo $redirect; ?>" />

                    <p><input type="text" name="username" value="" placeholder="Username"></p>
                    <p><input type="password" name="password" value="" placeholder="Password"></p>
                    <p class="submit"><input type="submit" name="login" value="login"></p>
                </form>
            </div>
            <div class="login-help">
                <p><a href="javascript: void(0);" onclick="f=document.loginform;f.lostpass.value=1;f.submit();"><?php echo $AppUI->_('forgotPassword'); ?></a></p>
            </div>

            <div class="login-help">
                <?php if (w2PgetConfig('activate_external_user_creation') == 'true') { ?>
                <p><a href="javascript: void(0);" onclick="javascript:window.location='./newuser.php'"><?php echo $AppUI->_('newAccountSignup'); ?></a></p>
                <?php } ?>
                <p><span style="font-size:7pt">Version <?php echo $AppUI->getVersion(); ?></span></p>
            </div>
        </div>

        <div align="center">
            <?php
                echo '<span class="error">' . $AppUI->getMsg() . '</span>';

                $msg = '';
                $msg .= (version_compare(PHP_VERSION, MIN_PHP_VERSION, '<')) ? '<br /><span class="warning">WARNING: web2project is NOT SUPPORT for this PHP Version (' . PHP_VERSION . ')</span>' : '';
                $msg .= function_exists('mysqli_connect') ? '' : '<br /><span class="warning">WARNING: PHP may not be compiled with MySQL support.  This will prevent proper operation of web2Project.  Please check you system setup.</span>';
                echo $msg;
            ?>
        </div>
    </body>
</html>