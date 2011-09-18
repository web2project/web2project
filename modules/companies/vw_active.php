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

?>
<a name="projects-company_view"> </a>
<table width="100%" border="0" cellpadding="2" cellspacing="1" class="tbl">
    <tr>
        <?php
        $fieldList = array();
        $fieldNames = array();
        $fields = w2p_Core_Module::getSettings('projects', 'company_view');
        if (count($fields) > 0) {
            foreach ($fields as $field => $text) {
                $fieldList[] = $field;
                $fieldNames[] = $text;
            }
        } else {
            // TODO: This is only in place to provide an pre-upgrade-safe 
            //   state for versions earlier than v3.0
            //   At some point at/after v4.0, this should be deprecated
            $fieldList = array('project_priority', 'project_name', 'user_username',
                'project_start_date', 'project_status', 'project_target_budget');
            $fieldNames = array('P', 'Name', 'Owner', 'Started', 'Status', 
                'Budget');
        }

        foreach ($fieldNames as $index => $name) {
            ?><th nowrap="nowrap">
                <a href="?m=companies&a=view&company_id=<?php echo $company_id; ?>&sort=<?php echo $fieldList[$index]; ?>#projects-company_view" class="hdr">
                    <?php echo $AppUI->_($fieldNames[$index]); ?>
                </a>
            </th><?php
        }
        ?>
    </tr>
<?php
if (count($projects) > 0) {
	foreach ($projects as $project) {
		$start_date = new w2p_Utilities_Date($project['project_start_date']);
		?>
		<tr>
			<td>
				<?php
					if ($project['project_priority'] < 0) {
						echo '<img src="' . w2PfindImage('icons/priority-' . -$project['project_priority'] . '.gif') . '" width="13" height="16" alt="">';
					} elseif ($project["project_priority"] > 0) {
						echo '<img src="' . w2PfindImage('icons/priority+' . $project['project_priority'] . '.gif') . '" width="13" height="16" alt="">';
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