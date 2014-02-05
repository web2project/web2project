<?php
global $post_save, $clashRedirect, $param;
function saveOtherResources(){
	global $AppUI, $obj;
	$assigned=explode(",",$_POST['other_resource']);
	$q = new DBQuery;
	$q->setDelete('event_resources');
	$q->addWhere('event_id = ' . $obj->event_id);
	$q->exec();
	$q->clear();

	if (is_array($assigned) && count($assigned)) {

	 	 	foreach ($assigned as $uid) {
				  $uid=(int)$uid;
				  if ($uid) {
					  $q->addTable('event_resources', 'ue');
					  $q->addInsert('event_id', $obj->event_id);
					  $q->addInsert('resource_id', $uid);
					  $q->exec();
					  $q->clear();
				  }
			  }

	 	 	if ($msg = db_error()) {
				  $AppUI->setMsg($msg, UI_MSG_ERROR);
			  }
	}
}
function checkClashOtherResource($resourceList = null) {
	global $AppUI, $obj;
	if (! isset($resourceList)) {
		return false;
	}
	$resources = explode(',', $resourceList);

	if (! count($resources)) {
		return false;
	}

	$start_date = new w2p_Utilities_Date($AppUI->convertToSystemTZ($obj->event_start_date));
	$end_date = new w2p_Utilities_Date($AppUI->convertToSystemTZ($obj->event_end_date));

	// Now build a query to find matching events.
	$q  = new w2p_Database_Query;
	$q->addTable('events', 'e');
	$q->addQuery('ue.resource_id, ue.resource_name, type.sysval_value');
	$q->addJoin('event_resources', 'e1', 'e1.event_id = e.event_id');
	$q->addJoin('resources', 'ue', 'ue.resource_id = e1.resource_id');
	$q->addJoin('sysvals', 'type', 'ue.resource_type=type.sysval_value_id');
	$q->addWhere("sysval_title = 'ResourceTypes'");
	$q->addWhere("event_start_date <= '" . $end_date->format(FMT_DATETIME_MYSQL) . "'");
	$q->addWhere("event_end_date >= '" . $start_date->format(FMT_DATETIME_MYSQL) . "'");
	$q->addWhere("ue.resource_id IN (" . implode(',', $resources) . ")");
	if (!empty($obj->event_id))
		$q->addWhere('e.event_id != ' . $obj->event_id);
//		$q->addWhere('e.user_id != ' . $AppUI->user_id);

	$result = $q->exec();
	if (! $result) {
		return false;
	}

	$clashes = array();
	while ($row = db_fetch_assoc($result)) {
		$clashes[$row['resource_id']]= $AppUI->_($row['sysval_value']).": ".$row['resource_name'];
	}
	$clash = array_unique($clashes);
	$q->clear();

	if (!empty($clash)) {
		return $clash;
	} else {
		return false;
	}

}
if ($_POST['other_resource'] > '' && ($clash = checkClashOtherResource($_POST['other_resource']))){
	$last_a=$GLOBALS['a'];
	$GLOBALS['a'] = "clashOtherResource";
	$GLOBALS['m'] = "resources";
	$clashRedirect = true;
}else{
	$post_save[]='saveOtherResources';
}
?>
