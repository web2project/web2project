<?php /* $Id$ $URL$ */
$dialog = w2PgetParam($_GET, 'dialog', 0);
if ($dialog) {
	$page_title = '';
} else {
	$page_title = ($w2Pconfig['page_title'] == 'web2Project') ? $w2Pconfig['page_title'] . '&nbsp;' . $AppUI->getVersion() : $w2Pconfig['page_title'];
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
        <meta name="Description" content="web2Project WebbPlatsen Redmond Style" />
        <meta name="Version" content="<?php echo $AppUI->getVersion(); ?>" />
        <meta http-equiv="Content-Type" content="text/html;charset=<?php echo isset($locale_char_set) ? $locale_char_set : 'UTF-8'; ?>" />
        <meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
        <title><?php echo @w2PgetConfig('page_title'); ?></title>
        <link rel="stylesheet" type="text/css" href="./style/common.css" media="all" charset="utf-8"/>
        <link rel="stylesheet" type="text/css" href="./style/<?php echo $uistyle; ?>/main.css" media="all" charset="utf-8"/>
        <style type="text/css" media="all">@import "./style/<?php echo $uistyle; ?>/main.css";</style>
        <link rel="shortcut icon" href="./style/<?php echo $uistyle; ?>/favicon.ico" type="image/ico" />
        <?php
            if (isset($xajax) && is_object($xajax)) {
                $xajax->printJavascript(w2PgetConfig('base_url') . '/lib/xajax');
            }
        ?>
        <?php $AppUI->loadHeaderJS(); ?>
    </head>

    <body onload="this.focus();">
        <table width="100%" cellpadding="0" cellspacing="0" border="0">
            <tr>
                <td>
                    <table class="banner" width='100%' cellpadding="3" cellspacing="0" border="0" background="style/<?php echo $uistyle; ?>/images/titlegrad.jpg">
                    <tr>
                        <th align="left"><strong><a href='http://www.w8.se/' <?php if ($dialog)
                        echo "target='_blank'"; ?>><img src="style/<?php echo $uistyle; ?>/images/wp_icon.gif" border="0" /></a></strong></th>
                        <th align="right" width='95'><?php
                        echo '<a href="http://www.web2project.net/">' . w2PtoolTip('web2Project v. ' . $AppUI->getVersion(), 'click to visit web2Project site', true) . '<img src="style/' . $uistyle . '/images/title.png" border="0" alt="click to visit web2Project site"  /></a>';?>
                        </th>
                    </tr>
                    </table>
                </td>
            </tr><?php
            if (!$dialog) {
                $perms = &$AppUI->acl(); ?>
                <tr>
                    <td align="left">
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
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tbody>
                                    <tr class="nav">
                                        <td>
                                            <?php echo buildHeaderNavigation($AppUI, '', '', ' | '); ?>
                                        </td>
                                        <?php
                                            if ($AppUI->user_id > 0) {
                                                //Do this check in case we are not using any user id, for example for external uses
                                                echo '<td nowrap="nowrap" align="right">';
                                                $newItem = array('' => '- New Item -');
                                                if (canAdd('companies')) {
                                                    $newItem['companies'] = 'Company';
                                                }
                                                if (canAdd('contacts')) {
                                                    $newItem['contacts'] = 'Contact';
                                                }
                                                if (canAdd('calendar')) {
                                                    $newItem['calendar'] = 'Event';
                                                }
                                                if (canAdd('files')) {
                                                    $newItem['files'] = 'File';
                                                }
                                                if (canAdd('projects')) {
                                                    $newItem['projects'] = 'Project';
                                                }
                                                if (canAdd('admin')) {
                                                    $newItem['admin'] = 'User';
                                                }
                                                echo arraySelect($newItem, 'm', 'style="font-size:10px" onchange="f=document.frm_new;mod=f.m.options[f.m.selectedIndex].value;if (mod == \'admin\') document.frm_new.a.value=\'addedituser\';if(mod) f.submit();"', '', true);
                                                echo '</td>';
                                            }
                                        ?>
                                    </tr>
                                </tbody>
                            </table>
                        </form>
                    </td>
                </tr>
                <tr>
                    <td>
                        <table cellspacing="0" cellpadding="3" border="0" width="100%">
                            <tr>
                                <td width="75%">
                                    <table cellspacing="0" cellpadding="3" border="0" width="100%">
                                    <tr>
                                        <td>
                                            <?php
                                                echo $AppUI->_('Welcome') . ' ' . ($AppUI->user_id > 0 ? $AppUI->user_first_name . ' ' . $AppUI->user_last_name : $outsider);
                                                echo '<br />';
                                                echo $AppUI->_('Server time is') . ' ' . $AppUI->getTZAwareTime();
                                            ?>
                                        </td>
                                    </tr>
                                    </table>
                                </td>
                                <?php if ($AppUI->user_id > 0) { ?>
                                    <td width="170" valign="middle" nowrap="nowrap"><table><tr><td><form name="frm_search" action="?m=smartsearch" method="post" accept-charset="utf-8">
                                        <?php if (canAccess('smartsearch')) { ?>
                                            <img src="<?php echo w2PfindImage('search.png'); ?>" style="border: 0;" alt="" />&nbsp;<input class="text" size="20" type="text" id="keyword" name="keyword" value="<?php echo $AppUI->_('Global Search') . '...'; ?>" onclick="document.frm_search.keyword.value=''" onblur="document.frm_search.keyword.value='<?php echo $AppUI->_('Global Search') . '...'; ?>'" />
                                        <?php } else {
                                        echo '&nbsp;';
                                        } ?></form></td></tr></table>
                                    </td>
                                    <td width="275" nowrap="nowrap">
                                        <table cellspacing="0" cellpadding="3" border="0" width="100%">
                                            <tr>
                                                <td nowrap="nowrap" align="right">
                                                    <input type="button" class="button" value="<?php echo $AppUI->_('Help'); ?>" onclick="javascript:window.open('?m=help&amp;dialog=1&amp;hid=', 'contexthelp', 'width=800, height=600, left=50, top=50, scrollbars=yes, resizable=yes')" />
                                                </td>
                                                <td nowrap="nowrap" align="right">
                                                    <input type="button" class="button" value="<?php echo $AppUI->_('My Info'); ?>" onclick="javascript:window.location='./index.php?m=admin&amp;a=viewuser&amp;user_id=<?php echo $AppUI->user_id; ?>'" />
                                                </td>
                                                <?php
                                                if (canAccess('tasks')) {
                                                    ?>
                                                    <td nowrap="nowrap" align="right">
                                                        <input type="button" class="button" value="<?php echo $AppUI->_('Todo'); ?>" onclick="javascript:window.location='./index.php?m=tasks&amp;a=todo'" />
                                                    </td>
                                                    <?php
                                                }
                                                if (canAccess('calendar')) {
                                                    $now = new CDate();
                                                    ?>
                                                    <td nowrap="nowrap" align="right">
                                                        <input type="button" class="button" value="<?php echo $AppUI->_('Today'); ?>" onclick="javascript:window.location='./index.php?m=calendar&amp;a=day_view&amp;date=<?php echo $now->format(FMT_TIMESTAMP_DATE); ?>'" />
                                                    </td><?php
                                                } ?>
                                                <td nowrap="nowrap" align="right">
                                                    <input type="button" class="button" value="<?php echo $AppUI->_('Logout'); ?>" onclick="javascript:window.location='./index.php?logout=-1'" />
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                <?php } ?>
                            </tr>
                        </table>
                    </td>
                </tr>
            <?php } // END showMenu ?>
        </table>

        <table width="100%" cellspacing="0" cellpadding="4" border="0">
            <tr>
                <td valign="top" align="left" width="98%">
                    <?php
                        echo $AppUI->getMsg();
                        $AppUI->boxTopRendered = false;
                        if ($m == 'help' && function_exists('styleRenderBoxTop')) {
                            echo styleRenderBoxTop();
                        }