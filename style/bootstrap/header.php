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

    <body class="<?php echo $w2pDialog ? 'w2p-dialog' : ($AppUI->user_id > 0 ? 'w2p-has-sidebar' : 'w2p-no-sidebar'); ?>">
        <?php if (!$w2pDialog) { ?>
        <script>try { if ('collapsed' === localStorage.getItem('w2p.sidebar')) { document.body.classList.add('w2p-sidebar-collapsed'); } } catch (e) {}</script>
        <header class="w2p-header">
            <div class="w2p-header-left">
                <?php if ($AppUI->user_id > 0) { ?>
                    <button type="button" class="w2p-sidebar-toggle" data-w2p-sidebar-toggle aria-controls="w2p-sidebar" aria-label="<?php echo $AppUI->_('Toggle navigation'); ?>">
                        <i class="bi bi-list" aria-hidden="true"></i>
                    </button>
                <?php } ?>
                <a class="w2p-brand" href="./index.php">
                    <img src="./style/<?php echo $theme; ?>/images/logo.png" alt="web2Project" width="123" height="28" />
                </a>
            </div>
            <?php if ($AppUI->user_id > 0) { ?>
                <?php if (canAccess('smartsearch')) { ?>
                    <form name="frm_search" action="?m=smartsearch" method="post" accept-charset="utf-8" class="w2p-header-search" role="search">
                        <i class="bi bi-search" aria-hidden="true"></i>
                        <input type="text" id="keyword" name="keyword" placeholder="<?php echo $AppUI->_('Global Search') . '...'; ?>" aria-label="<?php echo $AppUI->_('Global Search'); ?>" />
                    </form>
                <?php } ?>
                <div class="w2p-header-right">
                    <?php
                    // links for the "New Item" menu, with the same context the old select passed on
                    $newItemContext = '';
                    if (isset($company_id)) {
                        $newItemContext .= '&amp;company_id=' . (int) $company_id;
                    }
                    if (isset($task_id)) {
                        $newItemContext .= '&amp;task_parent=' . (int) $task_id;
                    }
                    if (isset($file_id)) {
                        $newItemContext .= '&amp;file_id=' . (int) $file_id;
                    }
                    $newItems = array();
                    $items = array('companies' => 'Company', 'projects' => 'Project',
                        'contacts' => 'Contact', 'events' => 'Events', 'files' => 'File',
                        'users' => 'User');
                    foreach ($items as $module => $name) {
                        if (canAdd($module)) {
                            $newItems[$module] = $name;
                        }
                    }
                    if (count($newItems)) { ?>
                        <div class="dropdown">
                            <button type="button" class="w2p-header-action w2p-new-item dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-plus-lg" aria-hidden="true"></i><span><?php echo $AppUI->_('New Item'); ?></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <?php foreach ($newItems as $module => $name) { ?>
                                    <li><a class="dropdown-item" href="./index.php?m=<?php echo $module; ?>&amp;a=addedit<?php echo $newItemContext; ?>"><i class="bi <?php echo $theme->moduleIcon($module); ?>" aria-hidden="true"></i> <?php echo $AppUI->_($name); ?></a></li>
                                <?php } ?>
                            </ul>
                        </div>
                    <?php } ?>
                    <?php if (canAccess('events')) {
                        $now = new w2p_Utilities_Date(); ?>
                        <a class="w2p-header-action" href="./index.php?m=events&amp;a=day_view&amp;date=<?php echo $now->format(FMT_TIMESTAMP_DATE); ?>" aria-label="<?php echo $AppUI->_('Today'); ?>" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="<?php echo $AppUI->_('Today'); ?>">
                            <i class="bi bi-calendar-event" aria-hidden="true"></i>
                        </a>
                    <?php } ?>
                    <?php if (canAccess('tasks')) { ?>
                        <a class="w2p-header-action" href="./index.php?m=tasks&amp;a=todo" aria-label="<?php echo $AppUI->_('My Tasks'); ?>" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="<?php echo $AppUI->_('My Tasks'); ?>">
                            <i class="bi bi-check2-square" aria-hidden="true"></i>
                        </a>
                    <?php } ?>
                    <a class="w2p-header-action" href="javascript: void(0);" onclick="javascript:window.open('?m=help&amp;dialog=1&amp;hid=', 'contexthelp', 'width=800, height=300, left=50, top=50, scrollbars=yes, resizable=yes')" aria-label="<?php echo $AppUI->_('Help'); ?>" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="<?php echo $AppUI->_('Help'); ?>">
                        <i class="bi bi-question-circle" aria-hidden="true"></i>
                    </a>
                    <?php
                    $userName = trim($AppUI->user_display_name ?: ($AppUI->user_first_name . ' ' . $AppUI->user_last_name));
                    $initials = '';
                    foreach (preg_split('/\s+/', $userName) as $part) {
                        if ('' != $part && strlen($initials) < 2) {
                            $initials .= mb_strtoupper(mb_substr($part, 0, 1));
                        }
                    }
                    ?>
                    <div class="dropdown">
                        <button type="button" class="w2p-user" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="w2p-avatar" aria-hidden="true"><?php echo w2PformSafe($initials ?: '?'); ?></span>
                            <span class="w2p-user-name"><?php echo w2PformSafe($userName); ?></span>
                            <i class="bi bi-chevron-down" aria-hidden="true"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li class="dropdown-header"><?php echo w2PformSafe($userName); ?></li>
                            <li><a class="dropdown-item" href="./index.php?m=users&amp;a=view&amp;user_id=<?php echo $AppUI->user_id; ?>"><i class="bi bi-person" aria-hidden="true"></i> <?php echo $AppUI->_('My Info'); ?></a></li>
                            <li><a class="dropdown-item" href="./index.php?m=system&amp;a=addeditpref&amp;user_id=<?php echo $AppUI->user_id; ?>"><i class="bi bi-sliders" aria-hidden="true"></i> <?php echo $AppUI->_('Preferences'); ?></a></li>
                            <?php if (canAccess('tasks')) { ?>
                                <li class="w2p-narrow-only"><a class="dropdown-item" href="./index.php?m=tasks&amp;a=todo"><i class="bi bi-check2-square" aria-hidden="true"></i> <?php echo $AppUI->_('My Tasks'); ?></a></li>
                            <?php } ?>
                            <?php if (canAccess('events')) { ?>
                                <li class="w2p-narrow-only"><a class="dropdown-item" href="./index.php?m=events&amp;a=day_view&amp;date=<?php echo $now->format(FMT_TIMESTAMP_DATE); ?>"><i class="bi bi-calendar-event" aria-hidden="true"></i> <?php echo $AppUI->_('Today'); ?></a></li>
                            <?php } ?>
                            <li class="w2p-narrow-only"><a class="dropdown-item" href="javascript: void(0);" onclick="javascript:window.open('?m=help&amp;dialog=1&amp;hid=', 'contexthelp', 'width=800, height=300, left=50, top=50, scrollbars=yes, resizable=yes')"><i class="bi bi-question-circle" aria-hidden="true"></i> <?php echo $AppUI->_('Help'); ?></a></li>
                            <li><hr class="dropdown-divider" /></li>
                            <li><form method="post" action="./index.php" class="m-0" accept-charset="utf-8"><button type="submit" name="logout" value="-1" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right" aria-hidden="true"></i> <?php echo $AppUI->_('Logout'); ?></button></form></li>
                        </ul>
                    </div>
                </div>
            <?php } ?>
        </header>
        <?php if ($AppUI->user_id > 0) { ?>
            <aside class="w2p-sidebar" id="w2p-sidebar" aria-label="<?php echo $AppUI->_('Modules'); ?>">
                <?php echo $theme->buildHeaderNavigation(); ?>
            </aside>
            <div class="w2p-sidebar-backdrop" data-w2p-sidebar-toggle></div>
        <?php } ?>
        <?php } ?>

        <main class="w2p-main" id="w2p-main">
            <?php
                echo $theme->messageHandler();
                $AppUI->boxTopRendered = false;
                if ($m == 'help') {
                    echo $theme->styleRenderBoxTop();
                }
