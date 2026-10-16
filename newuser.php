<?php
require_once 'base.php';
require_once W2P_BASE_DIR . '/includes/config.php';
require_once W2P_BASE_DIR . '/includes/main_functions.php';
require_once W2P_BASE_DIR . '/includes/db_adodb.php';

$AppUI = new w2p_Core_CAppUI();

if (w2PgetConfig('activate_external_user_creation') != 'true') {
    echo $AppUI->_('You should not access this file directly');
    die();
}

$uistyle = W2P_UISTYLE;
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta http-equiv="Pragma" content="no-cache" />
        <title><?php echo $AppUI->_('New User Signup'); ?></title>
        <link rel="stylesheet" type="text/css" href="./style/bootstrap/css/bootstrap.min.css" media="all" />
        <link rel="stylesheet" type="text/css" href="./style/<?php echo $uistyle; ?>/main.css" media="all" />
        <link rel="icon" href="./style/<?php echo $uistyle; ?>/favicon.ico" type="image/x-icon" />
    </head>

    <body class="w2p-auth" onload="document.editFrm.user_username.focus();">
        <main class="container py-5">
        <?php include 'createuser.php'; ?>

        <?php if ($AppUI->getVersion()) { ?>
            <div class="text-center small text-body-secondary">
                Version <?php echo $AppUI->getVersion(); ?>
            </div>
        <?php } ?>
        <div class="mx-auto mt-2" style="max-width: 44rem;">
            <?php
                echo $AppUI->getMsg();
            ?>
        </div>
        </main>
    </body>
</html>