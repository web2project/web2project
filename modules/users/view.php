<?php
if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly.');
}
// @todo    convert to template
$user_id = (int) w2PgetParam($_GET, 'user_id', 0);

$user = new CUser();

if (!$user->load($user_id)) {
    $AppUI->redirect(ACCESS_DENIED);
}

$canEdit   = $user->canEdit();



$tab = $AppUI->processIntState('UserVwTab', $_GET, 'tab', 0);

$user->loadFull($user_id);

global $addPwT, $company_id, $dept_ids, $department, $min_view, $m, $a;

$utypes = w2PgetSysVal('UserType');

if ($user_id != $AppUI->user_id && (!$perms->checkModuleItem('users', 'view', $user_id) || !$perms->checkModuleItem('users', 'view', $user_id))) {
    $AppUI->redirect(ACCESS_DENIED);
}

$addPwT = $AppUI->processIntState('addProjWithTasks', $_POST, 'add_pwt', 0);

$company_id = $AppUI->getState('UsrProjIdxCompany') !== null ? $AppUI->getState('UsrProjIdxCompany') : $AppUI->user_company;

$company_prefix = 'company_';

if (isset($_POST['department'])) {
    $AppUI->setState('UsrProjIdxDepartment', $_POST['department']);

    //if department is set, ignore the company_id field
    unset($company_id);
}
$department = $AppUI->getState('UsrProjIdxDepartment') !== null ? $AppUI->getState('UsrProjIdxDepartment') : $company_prefix . $AppUI->user_company;

//if $department contains the $company_prefix string that it's requesting a company and not a department.  So, clear the
// $department variable, and populate the $company_id variable.
if (!(strpos($department, $company_prefix) === false)) {
    $company_id = substr($department, strlen($company_prefix));
    $AppUI->setState('UsrProjIdxCompany', $company_id);
    unset($department);
}

$contact = new CContact();
$contact->contact_id = $user->user_contact;
$methods = $contact->getContactMethods();
$methodLabels = w2PgetSysVal('ContactMethods');

$countries = w2PgetSysVal('GlobalCountries');
// setup the title block
$titleBlock = new w2p_Theme_TitleBlock('View User', 'icon.png', $m, "$m.$a");
$titleBlock->addCrumb('?m=users', 'users list');
if ($canEdit || $user_id == $AppUI->user_id) {
    $titleBlock->addCell('<input type="button" class="button  btn btn-small dropdown-toggle" value="' . $AppUI->_('add user') . '" onclick="javascript:window.location=\'./index.php?m=users&a=addedituser\';" />');
    $titleBlock->addCrumb('?m=users&a=addedit&user_id='.$user_id, 'edit this user');
    $titleBlock->addCrumb('?m=contacts&a=addedit&contact_id='.$user->contact_id, 'edit this contact');
    $titleBlock->addCrumb('?m=system&a=addeditpref&user_id='.$user_id, 'edit preferences');
    $titleBlock->addCrumbRight('<div class="crumb"><ul><li><a href="javascript: void(0);" onclick="popChgPwd();return false"><span>' . $AppUI->_('change password') . '</span></a></li></ul></div>');
}
$titleBlock->show();

$htmlHelper = new w2p_Output_HTMLHelper($AppUI);
$htmlHelper->stageRowData((array) $user);
?>
    <script language="javascript" type="text/javascript">
        <?php
            // security improvement:
            // some javascript functions may not appear on client side in case of user not having write permissions
            // else users would be able to arbitrarily run 'bad' functions
            if ($canEdit || $user_id == $AppUI->user_id) {
        ?>
        function popChgPwd() {
            window.open( './index.php?m=public&a=chpwd&dialog=1&user_id=<?php echo $user->user_id; ?>', 'chpwd', 'top=250,left=250,width=350, height=220, scrollbars=no' );
        }
        <?php } ?>
    </script>
    <table class="std view admin">
        <tr>
            <th colspan="2"><?php echo $user->user_username; ?></th>
        </tr>
        <tr valign="top">
            <td width="50%">
                <strong><?php echo $AppUI->_('Details'); ?></strong>
                <table cellspacing="1" cellpadding="2" border="0" width="100%" class="well">
                    <tr>
                        <td align="right" nowrap="nowrap"><?php echo $AppUI->_('User Type'); ?>:</td>
                        <?php echo $htmlHelper->createCell('user_type', $AppUI->_($utypes[$user->user_type])); ?>
                    </tr>
                    <tr>
                        <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Real Name'); ?>:</td>
                        <?php echo $htmlHelper->createCell('contact_displayname', $user->contact_display_name); ?>
                    </tr>
                    <tr>
                        <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Company'); ?>:</td>
                        <?php echo $htmlHelper->createCell('contact_company', $user->contact_company); ?>
                    </tr>
                    <tr>
                        <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Department'); ?>:</td>
                        <?php echo $htmlHelper->createCell('contact_department', $user->contact_department); ?>
                    </tr>
                    <tr>
                        <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Phone'); ?>:</td>
                        <?php echo $htmlHelper->createCell('contact_phone', $user->contact_phone); ?>
                    </tr>
                    <tr>
                        <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Email'); ?>:</td>
                        <?php echo $htmlHelper->createCell('contact_email', $user->contact_email); ?>
                    </tr>
                    <tr valign="top">
                        <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Address'); ?>:</td>
                        <td width="100%">
                            <?php echo $user->contact_address1; ?><br />
                            <?php echo ($user->contact_address2 == '') ? '' : $user->contact_address2.'<br />'; ?>
                            <?php echo $user->contact_city . ', ' . $user->contact_state . ' ' . $user->contact_zip; ?><br />
                            <?php echo isset($countries[$user->contact_country]) ? $countries[$user->contact_country] : $user->contact_country; ?>
                        </td>
                    </tr>
                    <tr>
                        <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Birthday'); ?>:</td>
                        <?php echo $htmlHelper->createCell('_date', $user->contact_birthday); ?>
                    </tr>
                </table>
            </td>
            <td width="50%">
                <strong><?php echo $AppUI->_('Contact Information'); ?></strong>
                <table cellspacing="1" cellpadding="2" border="0" width="100%" class="well">
                    <?php
                    $fields = $methods['fields'];
                    foreach ($fields as $key => $field): ?>
                        <tr>
                            <td align="right" width="100" nowrap="nowrap"><?php echo $AppUI->_($methodLabels[$field]); ?>:</td>
                            <?php echo $htmlHelper->createCell('_'.substr($field, 0, strpos($field, '_')), $methods['values'][$key]); ?>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Calendar Feed'); ?>:</td>
                        <td width="100%">
                            <?php if ($user->feed_token != '') {
                                $calendarFeed = W2P_BASE_URL.'/calendar.php?token='.$user->feed_token.'&amp;ext=.ics';
                                ?>
                                <a href="<?php echo $calendarFeed; ?>">calendar feed</a>
                            <?php } ?>
                            &nbsp;&nbsp;&nbsp;
                            <form name="regenerateToken" action="./index.php?m=users" method="post" accept-charset="utf-8">
                                <input type="hidden" name="user_id" value="<?php echo (int) $user->user_id; ?>" />
                                <input type="hidden" name="dosql" value="do_user_token" />
                                <input type="hidden" name="token" value="<?php echo $user->feed_token; ?>" />
                                <input type="submit" name="regenerate token" value="<?php echo $AppUI->_('regenerate feed url'); ?>" class="button btn btn-primary btn-mini" />
                            </form>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2"><strong><?php echo $AppUI->_('Signature'); ?>:</strong></td>
                    </tr>
                    <tr>
                        <td width="100%" colspan="2">
                            <?php echo w2p_textarea($user->user_signature); ?>&nbsp;
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

<?php
// tabbed information boxes
$min_view = true;
$tabBox = new CTabBox('?m=users&a=view&user_id='.$user_id, '', $tab);
$tabBox->add(W2P_BASE_DIR . '/modules/users/vw_usr_log', 'User Log');
$tabBox->add(W2P_BASE_DIR . '/modules/users/vw_usr_perms', 'Permissions');
$tabBox->add(W2P_BASE_DIR . '/modules/users/vw_usr_roles', 'Roles');
$tabBox->show();