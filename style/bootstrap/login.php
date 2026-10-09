<?php
/** @var w2p_Core_CAppUI $AppUI */
if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly');
}

$theme = $AppUI->getTheme();
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="<?php echo isset($locale_char_set) ? $locale_char_set : 'UTF-8'; ?>" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta http-equiv="Pragma" content="no-cache" />
        <meta name="Version" content="<?php echo $AppUI->getVersion(); ?>" />
        <title><?php echo $w2Pconfig['company_name']; ?> :: web2Project Login</title>
        <link rel="stylesheet" type="text/css" href="./style/bootstrap/css/bootstrap.min.css" media="all" />
        <link rel="stylesheet" type="text/css" href="./style/<?php echo $theme; ?>/main.css" media="all" />
        <link rel="icon" href="./style/<?php echo $theme; ?>/favicon.ico" type="image/x-icon" />
    </head>

    <body class="bg-body-tertiary w2p-login">
        <main class="container py-5">
            <div class="card shadow-sm mx-auto" style="max-width: 26rem;">
                <a href="http://www.web2project.net" class="card-img-top"><img src="./style/<?php echo $theme; ?>/images/w2p_logo.jpg" alt="web2Project Home" class="img-fluid rounded-top" /></a>
                <div class="card-body">
                    <h1 class="h5 card-title text-center mb-3"><?php echo $w2Pconfig['company_name']; ?></h1>
                    <form method="post" action="<?php echo $loginFromPage; ?>" name="loginform" accept-charset="utf-8">
                        <input type="hidden" name="lostpass" value="0" />
                        <input type="hidden" name="redirect" value="<?php echo $redirect; ?>" />

                        <div class="form-floating mb-2">
                            <input type="text" class="form-control" id="username" name="username" value="" placeholder="Username" autofocus />
                            <label for="username"><?php echo $AppUI->_('Username'); ?></label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="password" class="form-control" id="password" name="password" value="" placeholder="Password" />
                            <label for="password"><?php echo $AppUI->_('Password'); ?></label>
                        </div>
                        <button type="submit" class="btn btn-primary w-100" name="login" value="login"><?php echo $AppUI->_('login'); ?></button>
                    </form>
                </div>
                <div class="card-footer text-center small">
                    <a href="javascript: void(0);" onclick="f=document.loginform;f.lostpass.value=1;f.submit();"><?php echo $AppUI->_('forgotPassword'); ?></a>
                    <?php if (w2PgetConfig('activate_external_user_creation') == 'true') { ?>
                        &middot; <a href="./newuser.php"><?php echo $AppUI->_('newAccountSignup'); ?></a>
                    <?php } ?>
                    <div class="text-body-secondary">Version <?php echo $AppUI->getVersion(); ?></div>
                </div>
            </div>

            <div class="mx-auto mt-3 text-center" style="max-width: 26rem;">
                <?php
                    echo $AppUI->getTheme()->messageHandler();

                    if (version_compare(PHP_VERSION, MIN_PHP_VERSION, '<')) {
                        echo '<div class="alert alert-warning">WARNING: web2project is NOT SUPPORT for this PHP Version (' . PHP_VERSION . ')</div>';
                    }
                    if (!function_exists('mysqli_connect')) {
                        echo '<div class="alert alert-warning">WARNING: PHP may not be compiled with MySQL support.  This will prevent proper operation of web2Project.  Please check you system setup.</div>';
                    }
                ?>
            </div>
        </main>
    </body>
</html>
