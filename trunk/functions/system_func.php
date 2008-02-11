<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

//fixed system SysVals to prevent their deletion
$fixedSysVals = array('CompanyType', 'EventType', 'FileType', 'GlobalCountries', 'GlobalYesNo', 'ProjectPriority', 'ProjectStatus', 'ProjectType', 'TaskDurationType', 'TaskLogReference', 'TaskPriority', 'TaskStatus', 'TaskType', 'UserType');
?>