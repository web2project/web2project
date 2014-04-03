<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
// @todo    convert to template

$params = get_object_vars($obj);

$pstatus = w2PgetSysVal('ProjectStatus');
$ptype = w2PgetSysVal('ProjectType');

$project_statuses = w2PgetSysVal('ProjectStatus');
$project_types = w2PgetSysVal('ProjectType');
$customLookups = array('project_status' => $pstatus, 'project_type' => $ptype);

$htmlHelper = new w2p_Output_HTMLHelper($AppUI);
$htmlHelper->stageRowData($params);
?>
<td width="50%" valign="top">
    <strong><?php echo $AppUI->_('Details'); ?></strong>
    <table cellspacing="1" cellpadding="2" border="0" width="100%" class="well">
        <tr>
            <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Company'); ?>:</td>
            <?php echo $htmlHelper->createCell('project_company', $obj->project_company); ?>
        </tr>
        <tr>
            <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Short Name'); ?>:</td>
            <?php echo $htmlHelper->createCell('project_shortname', $obj->project_short_name); ?>
        </tr>
        <tr>
            <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Start Date'); ?>:</td>
            <?php echo $htmlHelper->createCell('project_start_date', $obj->project_start_date); ?>
        </tr>
        <tr>
            <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Target End Date'); ?>:</td>
            <?php echo $htmlHelper->createCell('project_end_date', $obj->project_end_date); ?>
        </tr>
        <tr>
            <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Actual End Date'); ?>:</td>
            <?php echo $htmlHelper->createCell('project_end_date_actual', $obj->project_actual_end_date); ?>
        </tr>
        <tr>
            <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Target Budget'); ?>:</td>
            <?php echo $htmlHelper->createCell('project_target_budget', $obj->project_target_budget); ?>
        </tr>
        <tr>
            <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Project Owner'); ?>:</td>
            <?php echo $htmlHelper->createCell('project_owner', $obj->project_owner); ?>
        </tr>
        <tr>
            <td align="right" nowrap="nowrap"><?php echo $AppUI->_('URL'); ?>:</td>
            <?php echo $htmlHelper->createCell('project_url', $obj->project_url); ?>
        </tr>
        <tr>
            <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Staging URL'); ?>:</td>
            <?php echo $htmlHelper->createCell('project_demo_url', $obj->project_demo_url); ?>
        </tr>
        <tr>
            <td colspan="2">
            <?php
              $custom_fields = new w2p_Core_CustomFields('projects', $a, $obj->project_id, 'view');
              $custom_fields->printHTML();
            ?>
            </td>
        </tr>
        <tr>
            <td colspan="2">
            <strong><?php echo $AppUI->_('Description'); ?></strong><br />
            <table cellspacing="0" cellpadding="2" border="0" width="100%">
            <tr>
                <?php echo $htmlHelper->createCell('project_description', $obj->project_description); ?>
            </tr>
            </table>
            </td>
        </tr>
    </table>
</td>
<td width="50%" rowspan="9" valign="top">
    <strong><?php echo $AppUI->_('Summary'); ?></strong><br />
    <table cellspacing="1" cellpadding="2" border="0" width="100%" class="well">
        <tr>
            <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Status'); ?>:</td>
            <?php echo $htmlHelper->createCell('project_status', $obj->project_status, $customLookups); ?>
        </tr>
        <tr>
            <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Priority'); ?>:</td>
            <?php echo $htmlHelper->createCell('project_priority', $obj->project_priority); ?>
        </tr>
        <tr>
            <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Type'); ?>:</td>
            <?php echo $htmlHelper->createCell('project_type', $obj->project_type, $customLookups); ?>
        </tr>
        <tr>
            <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Progress'); ?>:</td>
            <?php echo $htmlHelper->createCell('project_percent_complete', $obj->project_percent_complete); ?>
        </tr>
        <tr>
            <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Worked Hours'); ?>:</td>
            <?php echo $htmlHelper->createCell('project_worked_hours', $obj->project_worked_hours); ?>
        </tr>
        <tr>
            <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Scheduled Hours'); ?>:</td>
            <?php echo $htmlHelper->createCell('project_scheduled_hours', $obj->project_scheduled_hours); ?>
        </tr>
        <?php
        $depts = $obj->getDepartmentList();

        if (count($depts) > 0) { ?>
            <tr>
                <td><strong><?php echo $AppUI->_('Departments'); ?></strong></td>
            </tr>
            <tr>
                <td colspan='3'>
                    <?php
                        foreach ($depts as $dept_id => $dept_info) {
                            echo '<div>' . $dept_info['dept_name'];
                            if ($dept_info['dept_phone'] != '') {
                                echo '( ' . $dept_info['dept_phone'] . ' )';
                            }
                            echo '</div>';
                        }
                    ?>
                </td>
            </tr>
        <?php
        }

        $contacts = $obj->getContactList();
        if (count($contacts)) {
            echo '<tr><td><strong>' . $AppUI->_('Project Contacts') . '</strong></td></tr>';
            echo '<tr><td colspan="3">';
            echo $htmlHelper->renderContactTable('projects', $contacts);
            echo '</td></tr>';
        }
    ?>
    </table>
</td>