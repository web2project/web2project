<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

##
##	Companies: View Archived Projects sub-table
##

global $AppUI, $company_id;

$projects = CCompany::getProjects($AppUI, $company_id, 0);

?><table cellpadding="2" cellspacing="1" border="0" width="100%" class="tbl"><?php

if (count($projects) > 0) {
	?><tr><th><?php echo $AppUI->_('Name'); ?></th><th><?php echo $AppUI->_('Owner'); ?></th></tr><?php
	foreach ($projects as $project) {
		?>
		<tr>
			<td>
				<a href="?m=projects&a=view&project_id=<?php echo $project['project_id']; ?>"><?php echo $project['project_name']; ?></a>
			</td>
			<td><?php echo $project['contact_first_name']; ?>&nbsp;<?php echo $project['contact_last_name']; ?></td>
		</tr>
		<?php
	}
} else {
	?><tr><td colspan="5"><?php echo $AppUI->_('No data available') . '<br />' . $AppUI->getMsg(); ?></td></tr><?php
}
?>
</table>