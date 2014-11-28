<?php
if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly');
}

$theme = new style_web2project($AppUI);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
       "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
        <title><?php echo @w2PgetConfig('page_title'); ?></title>
        <meta http-equiv="Content-Type" content="text/html;charset=<?php echo isset($locale_char_set) ? $locale_char_set : 'UTF-8'; ?>" />
        <title><?php echo $w2Pconfig['company_name']; ?> :: web2Project Lost Password Recovery</title>
        <meta http-equiv="Pragma" content="no-cache" />
        <meta name="Version" content="<?php echo $AppUI->getVersion(); ?>" />
        <link rel="stylesheet" type="text/css" href="./style/login.css" media="all" charset="utf-8"/>
        <link rel="stylesheet" type="text/css" href="./style/common.css" media="all" charset="utf-8"/>
        <link rel="stylesheet" type="text/css" href="./style/<?php echo $uistyle; ?>/main.css" media="all" charset="utf-8"/>
        <style type="text/css" media="all">@import "./style/<?php echo $uistyle; ?>/main.css";</style>
        <link rel="shortcut icon" href="./style/<?php echo $uistyle; ?>/favicon.ico" type="image/ico" />
    </head>

    <body bgcolor="#f0f0f0" onload="document.lostpassform.checkusername.focus();">
        <div class="container">
            <div class="login">
                <h1><?php echo $w2Pconfig['company_name']; ?></h1>
                <form method="post" action="<?php echo $loginFromPage; ?>" name="loginform" accept-charset="utf-8">
                    <input type="hidden" name="action" value="sendpass" />
                    <input type="hidden" name="redirect" value="<?php echo isset($redirect) ? $redirect : ''; ?>" />

                    <p><input type="text" name="checkusername" value="" placeholder="Username"></p>
                    <p><input type="text" name="checkemail" value="" placeholder="Email"></p>
                    <p class="submit"><input type="submit" name="commit" value="Send Password"></p>
                </form>
            </div>

            <?php if (w2PgetConfig('activate_external_user_creation') == 'true') { ?>
                <div class="login-help">
                    <p><a href="javascript: void(0);" onclick="javascript:window.location='./newuser.php'"><?php echo $AppUI->_('newAccountSignup'); ?></a></p>
                </div>
            <?php } ?>
        </div>

        <div align="center">
            <?php
                echo '<span class="error">' . $AppUI->getMsg() . '</span>';
                $msg = '';
                $msg .= (version_compare(PHP_VERSION, MIN_PHP_VERSION, '<')) ? '<br /><span class="warning">WARNING: web2project is NOT supported for this PHP Version (' . PHP_VERSION . ')</span>' : '';
                $msg .= function_exists('mysql_pconnect') ? '' : '<br /><span class="warning">WARNING: PHP may not be compiled with MySQL support.  This will prevent proper operation of web2Project.  Please check your system setup.</span>';
                echo $msg;
            ?>
        </div>
    </body>
</html>