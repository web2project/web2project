<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

##
##	Companies: View Projects sub-table
##
global $AppUI, $company_id, $pstatus, $w2Pconfig;

$sort = w2PgetParam($_GET, 'sort', 'project_name');
if ($sort == 'project_priority') {
	$sort .= ' DESC';
}

$df = $AppUI->getPref('SHDATEFORMAT');

$projects = CCompany::getProjects($AppUI, $company_id, 1, $sort);

?><table cellpadding="2" cellspacing="1" border="0" width="100%" class="tbl"><?php
if (count($projects) > 0) {
	?>
	<tr>
		<th><a style="color:white" href="index.php?m=companies&a=view&company_id=<?php echo $company_id; ?>&sort=project_priority"><?php echo $AppUI->_('P'); ?></a></th>
		<th><a style="color:white" href="index.php?m=companies&a=view&company_id=<?php echo $company_id; ?>&sort=project_name"><?php echo $AppUI->_('Name'); ?></a></th>
		<th><?php echo $AppUI->_('Owner'); ?></th>
		<th><?php echo $AppUI->_('Started'); ?></th>
		<th><?php echo $AppUI->_('Status'); ?></th>
		<th><?php echo $AppUI->_('Budget'); ?></th>
	</tr>
	<?php
	foreach ($projects as $project) {
		$start_date = new CDate($project['project_start_date']);
		?>
		<tr>
			<td>
				<?php
					if ($project['project_priority'] < 0) {
						echo '<img src="' . w2PfindImage('icons/priority-' . -$project['project_priority'] . '.gif') . '" width=13 height=16>';
					} elseif ($project["project_priority"] > 0) {
						echo '<img src="' . w2PfindImage('icons/priority+' . $project['project_priority'] . '.gif') . '" width=13 height=16>';
					} 
				?>
			</td>
			<td>
				<a href="?m=projects&a=view&project_id=<?php echo $project['project_id']; ?>"><?php echo $project['project_name']; ?></a>
			</td>
			<td nowrap="nowrap"><?php echo $project['contact_first_name']; ?>&nbsp;<?php echo $project['contact_last_name']; ?></td>
			<td nowrap="nowrap"><?php echo $start_date->format($df); ?></td>
			<td nowrap="nowrap"><?php echo $AppUI->_($pstatus[$project['project_status']]); ?></td>
			<td nowrap="nowrap">
                <?php
                    echo $w2Pconfig['currency_symbol'];
                    echo formatCurrency($project['project_target_budget'], $AppUI->getPref('CURRENCYFORM'));
                ?>
            </td>
		</tr>
		<?php
	}
} else {
	?><tr><td colspan="5"><?php echo $AppUI->_('No data available') . '<br />' . $AppUI->getMsg(); ?></td></tr><?php
}
?>
</table>