<?php
/** @var w2p_Core_CAppUI $AppUI */
if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly');
}

$theme = new style_bootstrap($AppUI);
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="<?php echo isset($locale_char_set) ? $locale_char_set : 'UTF-8'; ?>" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta http-equiv="Pragma" content="no-cache" />
        <meta name="Version" content="<?php echo $AppUI->getVersion(); ?>" />
        <title><?php echo $w2Pconfig['company_name']; ?> :: web2Project Lost Password Recovery</title>
        <link rel="stylesheet" type="text/css" href="./style/bootstrap/css/bootstrap.min.css" media="all" />
        <link rel="stylesheet" type="text/css" href="./style/<?php echo $theme; ?>/main.css" media="all" />
        <link rel="icon" href="./style/<?php echo $theme; ?>/favicon.ico" type="image/x-icon" />
    </head>

    <body class="w2p-auth">
        <main class="w2p-auth-main">
            <a href="http://www.web2project.net" class="w2p-auth-logo"><img src="./style/<?php echo $theme; ?>/images/logo.png" alt="web2Project Home" width="123" height="28" /></a>
            <div class="w2p-auth-card">
                <h1 class="w2p-auth-title"><?php echo $w2Pconfig['company_name']; ?></h1>
                <form method="post" action="<?php echo $loginFromPage; ?>" name="lostpassform" accept-charset="utf-8">
                    <input type="hidden" name="lostpass" value="1" />
                    <input type="hidden" name="redirect" value="<?php echo isset($redirect) ? $redirect : ''; ?>" />

                    <div class="mb-3">
                        <label for="checkusername" class="form-label"><?php echo $AppUI->_('Username'); ?></label>
                        <input type="text" class="form-control" id="checkusername" name="checkusername" value="" autofocus />
                    </div>
                    <div class="mb-4">
                        <label for="checkemail" class="form-label"><?php echo $AppUI->_('Email'); ?></label>
                        <input type="email" class="form-control" id="checkemail" name="checkemail" maxlength="255" />
                    </div>
                    <button type="submit" class="btn btn-primary w-100" name="sendpass" value="Send Password"><?php echo $AppUI->_('Send Password'); ?></button>
                </form>
                <div class="w2p-auth-footer">
                    <a class="w2p-auth-link" href="./index.php"><?php echo $AppUI->_('login'); ?></a>
                    <?php if (w2PgetConfig('activate_external_user_creation') == 'true') { ?>
                        &middot; <a class="w2p-auth-link" href="./newuser.php"><?php echo $AppUI->_('newAccountSignup'); ?></a>
                    <?php } ?>
                </div>
            </div>

            <div class="w2p-auth-messages">
                <?php
                    echo $theme->messageHandler();

                    if (version_compare(PHP_VERSION, MIN_PHP_VERSION, '<')) {
                        echo '<div class="alert alert-warning">WARNING: web2project is NOT supported for this PHP Version (' . PHP_VERSION . ')</div>';
                    }
                    if (!function_exists('mysqli_connect')) {
                        echo '<div class="alert alert-warning">WARNING: PHP may not be compiled with MySQL support.  This will prevent proper operation of web2Project.  Please check your system setup.</div>';
                    }
                ?>
            </div>
        </main>
    </body>
</html>
