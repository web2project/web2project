<?php
if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly.');
}

// Include the file first of all, so that the AJAX methods are printed through xajax below
require W2P_BASE_DIR . '/includes/ajax_functions.php';

$theme = $AppUI->getTheme();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
        <meta name="Version" content="<?php echo $AppUI->getVersion(); ?>" />
        <meta http-equiv="Content-Type" content="text/html;charset=<?php echo isset($locale_char_set) ? $locale_char_set : 'UTF-8'; ?>" />
        <meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
        <title><?php echo @w2PgetConfig('page_title') . ' :: ' . $AppUI->_($m) . ' ' . $AppUI->_($a); ?></title>
        <link rel="stylesheet" type="text/css" href="./style/common.css" media="all" charset="utf-8"/>
        <link rel="stylesheet" type="text/css" href="./style/<?php echo $theme; ?>/main.css" media="all" charset="utf-8"/>
        <link rel="shortcut icon" href="./style/<?php echo $theme; ?>/favicon.ico" type="image/ico" />
        <?php
            if (isset($xajax) && is_object($xajax)) {
                $xajax->printJavascript(W2P_BASE_URL . '/lib/xajax');
            }
        ?>
        <?php $AppUI->getTheme()->loadHeaderJS(); ?>
    </head>

    <body onload="this.focus();">
        <div class="std titlebar">
            <div class="left">
                <a href="http://www.w8.se/" target="_new">
                    <img src="style/<?php echo $theme; ?>/images/wp_icon.gif" />
                </a>
            </div>
            <div class="right">
                <a href="http://www.web2project.net/" target="_new">
                    <?php echo w2PtoolTip('web2Project v. ' . $AppUI->getVersion(), 'click to visit web2Project site', true);?>
                        <img src="style/<?php echo $theme; ?>/images/logo.png" class="banner" align="left" alt="click to visit web2Project site" />
                    <?php echo w2PendTip();?>
                </a>
            </div>
        </div>
        <?php
        if (!$dialog) {
            $perms = &$AppUI->acl(); ?>
            <form name="frm_new" method="get" action="./index.php" accept-charset="utf-8">
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
                ?>
                <div class="header">
                    <div class="left nav">
                        <?php echo $theme->buildHeaderNavigation('', '', ' | '); ?>
                    </div>
                    <div class="right" style="margin: 4px;">
                        <?php
                        if ($AppUI->user_id > 0) {
                            //Do this check in case we are not using any user id, for example for external uses
                            $newItem = array('' => '- New Item -');

                            $items = array('companies' => 'Company', 'projects' => 'Project',
                                'contacts' => 'Contact', 'calendar' => 'Events', 'files' => 'File',
                                'admin' => 'User');
                            foreach ($items as $module => $name) {
                                if (canAdd($module)) {
                                    $newItem[$module] = $name;
                                }
                            }

                            echo arraySelect($newItem, 'm', 'style="font-size:10px; margin-top: -5px;" onchange="f=document.frm_new;mod=f.m.options[f.m.selectedIndex].value;if (mod == \'admin\') document.frm_new.a.value=\'addedituser\';if(mod) f.submit();"', '', true);
                        }
                        ?>
                    </div>
                </div>
                <div>&nbsp;</div>
            </form>
            <div style="margin-top: -10px;">
                <div class="left" style="margin: 5px;">
                    <?php
                        echo $AppUI->_('Welcome') . ' ' . ($AppUI->user_id > 0 ? $AppUI->user_display_name : $outsider);
                        echo '<br />';
                        if ($AppUI->user_id > 0) {
                            echo $AppUI->_('Server time is') . ' ' . $AppUI->getTZAwareTime();
                        }
                    ?>
                </div>
                <?php if ($AppUI->user_id > 0) { ?>
                    <div class="right quicknav">
                        <div class="left" style="margin-top: -3px;">
                        <?php if (canAccess('smartsearch')) { ?>
                            <form name="frm_search" action="?m=smartsearch" method="post" accept-charset="utf-8">
                                <img src="<?php echo w2PfindImage('search.png'); ?>" />&nbsp;<input class="text" size="20" type="text" id="keyword" name="keyword" value="<?php echo $AppUI->_('Global Search') . '...'; ?>" onclick="document.frm_search.keyword.value=''" onblur="document.frm_search.keyword.value='<?php echo $AppUI->_('Global Search') . '...'; ?>'" />
                            </form>
                            <?php } ?>
                        </div>
                        <a class="button" href="javascript: void(0);" onclick="javascript:window.open('?m=help&amp;dialog=1&amp;hid=', 'contexthelp', 'width=800, height=600, left=50, top=50, scrollbars=yes, resizable=yes')"><span><?php echo $AppUI->_('Help'); ?></span></a>
                        <a class="button" href="./index.php?m=users&amp;a=view&amp;user_id=<?php echo $AppUI->user_id; ?>"><span><?php echo $AppUI->_('My Info'); ?></span></a>
                        <?php if (canAccess('tasks')) { ?>
                            <a class="button" href="./index.php?m=tasks&amp;a=todo"><span><b><?php echo $AppUI->_('My Tasks'); ?></b></span></a>
                        <?php } ?>
                        <?php if (canAccess('calendar')) {
                            $now = new w2p_Utilities_Date(); ?>
                            <a class="button" href="./index.php?m=calendar&amp;a=day_view&amp;date=<?php echo $now->format(FMT_TIMESTAMP_DATE); ?>"><span><?php echo $AppUI->_('Today'); ?></span></a>
                        <?php } ?>
                        <a class="button" href="./index.php?logout=-1"><span><?php echo $AppUI->_('Logout'); ?></span></a>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>

        <table width="100%" cellspacing="0" cellpadding="4" border="0">
            <tr>
                <td valign="top" align="left" width="98%">
                    <?php
                        echo $theme->messageHandler();
                        $AppUI->boxTopRendered = false;
                        if ($m == 'help') {
                            echo $theme->styleRenderBoxTop();
                        }
//TODO: Basically this entire file is exactly the same as the other two header.php files in core web2project except for the left header image.. - caseydk 2012-07-01