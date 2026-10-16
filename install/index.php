<?php
require_once '../base.php';
require_once W2P_BASE_DIR . '/includes/main_functions.php';
if (version_compare(PHP_VERSION, MIN_PHP_VERSION, '<')) {
    echo 'web2Project requires PHP ' . MIN_PHP_VERSION . '+. Please upgrade!';
    die();
}

$step = trim( w2PgetParam( $_POST, 'step', '' ) );
$manager = new w2p_System_UpgradeManager();
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta name="Description" content="web2Project Update Manager" />
        <title>web2Project Update Manager</title>
        <link rel="stylesheet" type="text/css" href="../style/common.css" media="all" />
        <link rel="stylesheet" type="text/css" href="../style/bootstrap/css/bootstrap.min.css" media="all" />
        <link rel="stylesheet" type="text/css" href="../style/bootstrap/main.css" media="all" />

        <style>
            .update img {
                float: left;
                margin-right: .25rem;
            }
        </style>

    </head>
    <body class="bg-body-tertiary">
        <main class="container my-4">
        <div class="card std update">
            <div class="card-header fw-bold item">
                Welcome to the web2Project Update Manager!
            </div>
            <div class="card-body">
    <?php
    $action = $manager->getActionRequired();

    switch ($action) {
        case 'install':
            ?>
            <div class="my-2">
                    This system will help you perform each of the required steps to prepare your web2project installation. It's a three step process. First we'll confirm that all the requirements are met, then we'll get the database credentials, then we'll load the system.
            </div>
            <?php if ($step == '') { ?>
            <div class="my-2">
                    When you're ready to begin, simply
                    <form method="post" name="form" id="form" accept-charset="utf-8">
                        <input type="hidden" name="step" value="check" />
                        <input class="button btn btn-primary btn-sm" type="submit" name="next" value="Start <?php echo ucwords($action); ?> &raquo;" />
                    </form>
            </div>
            <?php
            }
            break;
        case 'conversion':
            ?>
            <div class="my-2">
                    This is where the conversion script kicks in. It's a two step process. First we'll confirm that all the
                    requirements are met, then we'll convert your existing data.<br />
                    You shouldn't have to do anything manually except log in at the end.
            </div>
            <?php if ($step == '') { ?>
            <div class="my-2">
                    When you're ready to begin, simply
                    <form method="post" name="form" id="form" accept-charset="utf-8">
                        <input type="hidden" name="step" value="check" />
                        <input class="button btn btn-primary btn-sm" type="submit" name="next" value="Start <?php echo ucwords($action); ?> &raquo;" />
                    </form>
            </div>
            <?php
            }
            break;
        case 'upgrade':
            ?>
            <div class="my-2">
                    The system upgrade is performed through the <strong><a href="../index.php?m=system">System Admin</a></strong> and requires you to be logged in with Admin access. Please click <strong><a href="../index.php?m=system">System Admin</a></strong> to continue.
            </div>
            <?php
            break;
        default:
            ?>
            <div class="my-2">
                    You've attempted to perform an invalid action. Stop that.
            </div>
            <?php
    }

    switch ($action.'/'.$step) {
        case 'install/check':
        case 'install/dbcreds':
        case 'install/perform':
        case 'conversion/check':
        case 'conversion/perform':
            /*
             * Doing something like this is often a security risk. It's not in
             * this case as we know *exactly* what both $action and $step will be
             * if we reach this include.
             */
            include $action.'/'.$step.'.php';
            break;
        default:
            //do nothing
    }
    ?>
            </div>
        </div>
        </main>
    </body>
</html>
