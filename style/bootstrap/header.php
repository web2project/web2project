<?php
/** @var w2p_Core_CAppUI $AppUI */
if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly.');
}

// Include the file first of all, so that the AJAX methods are printed through xajax below
require W2P_BASE_DIR . '/includes/ajax_functions.php';

$theme = $AppUI->getTheme();
$charset = isset($locale_char_set) ? $locale_char_set : 'UTF-8';
// popup windows (selectors, help, calendars) only get the page content
$w2pDialog = (1 == (int) w2PgetParam($_GET, 'dialog', 0));
?>
<!DOCTYPE html>
<html lang="<?php echo substr($AppUI->user_locale ?: 'en', 0, 2); ?>">
    <head>
        <meta charset="<?php echo $charset; ?>" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta name="Version" content="<?php echo $AppUI->getVersion(); ?>" />
        <title><?php echo @w2PgetConfig('page_title') . ' :: ' . $AppUI->_($m) . ' ' . $AppUI->_($a); ?></title>
        <link rel="stylesheet" type="text/css" href="./style/bootstrap/css/bootstrap.min.css" media="all" />
        <link rel="stylesheet" type="text/css" href="./style/<?php echo $theme; ?>/main.css" media="all" />
        <link rel="icon" href="./style/<?php echo $theme; ?>/favicon.ico" type="image/x-icon" />
        <?php
            if (isset($xajax) && is_object($xajax)) {
                $xajax->printJavascript(W2P_BASE_URL . '/lib/xajax');
            }
        ?>
        <?php $theme->loadHeaderJS(); ?>
    </head>

    <body<?php echo $w2pDialog ? ' class="w2p-dialog"' : ''; ?>>
        <?php if (!$w2pDialog) { ?>
        <header class="w2p-header mb-3">
            <nav class="navbar navbar-expand-lg bg-body-tertiary border-bottom">
                <div class="container-fluid">
                    <a class="navbar-brand" href="http://www.web2project.net/" target="_blank" rel="noopener" title="web2Project v. <?php echo $AppUI->getVersion(); ?>">
                        <img src="./style/<?php echo $theme; ?>/images/logo.png" alt="web2Project" width="123" height="28" />
                    </a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#w2pNavbar" aria-controls="w2pNavbar" aria-expanded="false" aria-label="<?php echo $AppUI->_('Toggle navigation'); ?>">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="w2pNavbar">
                        <?php echo $theme->buildHeaderNavigation(); ?>
                    </div>
                </div>
            </nav>
            <?php if ($AppUI->user_id > 0) { ?>
                <div class="container-fluid d-flex flex-wrap align-items-center gap-2 py-2 border-bottom w2p-toolbar">
                    <form name="frm_new" method="get" action="./index.php" accept-charset="utf-8" class="d-flex">
                        <input type="hidden" name="a" value="addedit" />
                        <?php
                        //build URI string
                        if (isset($company_id)) {
                            echo '<input type="hidden" name="company_id" value="' . $company_id . '" />';
                        }
                        if (isset($task_id)) {
                            echo '<input type="hidden" name="task_parent" value="' . $task_id . '" />';
                        }
                        if (isset($file_id)) {
                            echo '<input type="hidden" name="file_id" value="' . $file_id . '" />';
                        }

                        $newItem = array('' => '- New Item -');
                        $items = array('companies' => 'Company', 'projects' => 'Project',
                            'contacts' => 'Contact', 'events' => 'Events', 'files' => 'File',
                            'users' => 'User');
                        foreach ($items as $module => $name) {
                            if (canAdd($module)) {
                                $newItem[$module] = $name;
                            }
                        }
                        echo arraySelect($newItem, 'm', 'class="form-select form-select-sm" aria-label="' . $AppUI->_('New Item') . '" onchange="f=document.frm_new;mod=f.m.options[f.m.selectedIndex].value;if (mod == \'admin\') document.frm_new.a.value=\'addedituser\';if(mod) f.submit();"', '', true);
                        ?>
                    </form>
                    <?php if (canAccess('smartsearch')) { ?>
                        <form name="frm_search" action="?m=smartsearch" method="post" accept-charset="utf-8" class="d-flex" role="search">
                            <input class="form-control form-control-sm" type="text" id="keyword" name="keyword" placeholder="<?php echo $AppUI->_('Global Search') . '...'; ?>" aria-label="<?php echo $AppUI->_('Global Search'); ?>" />
                        </form>
                    <?php } ?>
                    <div class="ms-auto d-flex flex-wrap gap-1 quicknav">
                        <a class="btn btn-sm btn-outline-secondary" href="javascript: void(0);" onclick="javascript:window.open('?m=help&amp;dialog=1&amp;hid=', 'contexthelp', 'width=800, height=300, left=50, top=50, scrollbars=yes, resizable=yes')"><?php echo $AppUI->_('Help'); ?></a>
                        <a class="btn btn-sm btn-outline-secondary" href="./index.php?m=users&amp;a=view&amp;user_id=<?php echo $AppUI->user_id; ?>"><?php echo $AppUI->_('My Info'); ?></a>
                        <?php if (canAccess('tasks')) { ?>
                            <a class="btn btn-sm btn-outline-primary" href="./index.php?m=tasks&amp;a=todo"><?php echo $AppUI->_('My Tasks'); ?></a>
                        <?php } ?>
                        <?php if (canAccess('events')) {
                            $now = new w2p_Utilities_Date(); ?>
                            <a class="btn btn-sm btn-outline-secondary" href="./index.php?m=events&amp;a=day_view&amp;date=<?php echo $now->format(FMT_TIMESTAMP_DATE); ?>"><?php echo $AppUI->_('Today'); ?></a>
                        <?php } ?>
                        <a class="btn btn-sm btn-outline-danger" href="./index.php?logout=-1"><?php echo $AppUI->_('Logout'); ?></a>
                    </div>
                </div>
            <?php } ?>
        </header>
        <?php } ?>

        <main class="container-fluid w2p-main<?php echo $w2pDialog ? ' pt-2' : ''; ?>" id="w2p-main">
            <?php
                echo $theme->messageHandler();
                $AppUI->boxTopRendered = false;
                if ($m == 'help') {
                    echo $theme->styleRenderBoxTop();
                }
