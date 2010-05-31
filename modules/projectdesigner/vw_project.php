<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
?>
	<td width="50%" valign="top">
		<strong><?php echo $AppUI->_('Details'); ?></strong>
		<table cellspacing="1" cellpadding="2" border="0" width="100%">
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Company'); ?>:</td>
        	<td class="hilite" width="100%"> <?php echo "<a href='?m=companies&a=view&company_id=" . $obj->project_company . "'>" . htmlspecialchars($obj->company_name, ENT_QUOTES) . '</a>'; ?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Short Name'); ?>:</td>
			<td class="hilite"><?php echo htmlspecialchars($obj->project_short_name, ENT_QUOTES); ?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Start Date'); ?>:</td>
			<td class="hilite"><?php echo $start_date ? $start_date->format($df) : '-'; ?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Target End Date'); ?>:</td>
			<td class="hilite"><?php echo $end_date ? $end_date->format($df) : '-'; ?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Actual End Date'); ?>:</td>
			<td class="hilite">
        <?php if ($project_id > 0) { ?>
          <?php echo $actual_end_date ? '<a href="?m=tasks&a=view&task_id=' . $criticalTasks[0]['task_id'] . '">' : ''; ?>
          <?php echo $actual_end_date ? '<span ' . $style . '>' . $actual_end_date->format($df) . '</span>' : '-'; ?>
          <?php echo $actual_end_date ? '</a>' : ''; ?>
        <?php } else {
        	echo $AppUI->_('Dynamically calculated');
        } ?>
      </td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Target Budget'); ?>:</td>
			<td class="hilite"><?php echo $w2Pconfig['currency_symbol'] ?><?php echo $obj->project_target_budget; ?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Project Owner'); ?>:</td>
			<td class="hilite"><?php echo $obj->user_name; ?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('URL'); ?>:</td>
      <td class="hilite"><?php echo w2p_url($obj->project_url); ?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Staging URL'); ?>:</td>
      <td class="hilite"><?php echo w2p_url($obj->project_demo_url); ?></td>
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
				<td class="hilite">
					<?php echo w2p_textarea($obj->project_description); ?>&nbsp;
				</td>
			</tr>
			</table>
			</td>
		</tr>
		</table>
	</td>
	<td width="50%" rowspan="9" valign="top">
		<strong><?php echo $AppUI->_('Summary'); ?></strong><br />
		<table cellspacing="1" cellpadding="2" border="0" width="100%">
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Status'); ?>:</td>
			<td class="hilite" width="100%"><?php echo $AppUI->_($pstatus[$obj->project_status]); ?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Priority'); ?>:</td>
			<td class="hilite" width="100%" style="background-color:<?php echo $projectPriorityColor[$obj->project_priority] ?>"><?php echo $AppUI->_($projectPriority[$obj->project_priority]); ?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Type'); ?>:</td>
			<td class="hilite" width="100%"><?php echo $AppUI->_($ptype[$obj->project_type]); ?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Progress'); ?>:</td>
			<td class="hilite" width="100%"><?php printf("%.1f%%", $obj->project_percent_complete); ?></td>
		</tr>
<!--		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Active'); ?>:</td>
			<td class="hilite" width="100%"><?php echo $obj->project_active ? $AppUI->_('Yes') : $AppUI->_('No'); ?></td>
		</tr>-->
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Worked Hours'); ?>:</td>
			<td class="hilite" width="100%"><?php echo $worked_hours ?></td>
		</tr>	
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Scheduled Hours'); ?>:</td>
			<td class="hilite" width="100%"><?php echo $total_hours ?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Project Hours'); ?>:</td>
			<td class="hilite" width="100%"><?php echo $total_project_hours ?></td>
		</tr>				
        <?php
        $depts = CProject::getDepartments($AppUI, $obj->project_id);

        if (count($depts) > 0) { ?>
            <tr>
                <td><strong><?php echo $AppUI->_('Departments'); ?></strong></td>
            </tr>
            <tr>
                <td colspan='3' class="hilite">
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

        $contacts = CProject::getContacts($AppUI, $obj->project_id);
        if (count($contacts)) {
            echo '<tr><td><strong>' . $AppUI->_('Project Contacts') . '</strong></td></tr>';
            echo '<tr><td colspan="3" class="hilite">';
            echo w2p_Output_HTMLHelper::renderContactList($AppUI, $contacts);
            echo '</td></tr>';
        }
        ?>
        </table>
    </td>