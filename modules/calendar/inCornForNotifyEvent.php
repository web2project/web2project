#!/usr/bin/php
<?php
$base_file='../../base.php';
if (file_exists($base_file) && filesize($base_file)>0){
	require $base_file;
}else{
	echo "don't load base file";
	die();
}

$config_file=W2P_BASE_DIR . '/includes/config.php';
if (file_exists($config_file) && filesize($config_file)>0){
	require $config_file;
}else{
	echo "don't load config file";
	die();
}
$mysqli=new mysqli($w2Pconfig['dbhost'],$w2Pconfig['dbuser'],$w2Pconfig['dbpass'],$w2Pconfig['dbname']);
$mysqli->query('SET NAMES utf8');
if ($mysqli->connect_errno) {
	echo "Failed to connect to MySQL: " . $mysqli->connect_error;
}
$res=$mysqli->query('
	SELECT  event_id, event_title, event_start_date, event_description, event_remind, contact_email
	FROM events
	LEFT JOIN user_events USING (event_id)
	LEFT JOIN users USING (user_id)
	LEFT JOIN contacts ON user_contact=contact_id
	WHERE event_remind>0
	');
$events=array();
while ($row=$res->fetch_assoc()){
	if (strtotime($row['event_start_date'])-time()<$row['event_remind']){
		if (!isset($events[$row['event_id']])){
			$text=str_replace("\n","<br>", $row['event_description']);
			$events[$row['event_id']]['subject']= "[web2project] Напоминание о событии";
			$events[$row['event_id']]['message']=
				'
				<html>
				<head>
				 <title>'.$row['event_title'].'</title>
				</head>
				<body>
				<b>'.$row['event_title'].'</b><br><br>
				'.$text.'
				</body>
				</html>
			';
			$events[$row['event_id']]['headers']= "MIME-Version: 1.0\r\n";
			$events[$row['event_id']]['headers'].= "Content-type: text/html; charset=UTF-8\r\n";
			$events[$row['event_id']]['headers'].= "From: no-reply@".$w2Pconfig['mailServer']."\r\n";
		}
		$events[$row['event_id']]['to'][]=$row['contact_email'];
	}
}
$readyEvent=array();
foreach ($events as $eventId=>$eventData){
	$to=implode(', ', $eventData['to']);
	if (mail($to, $eventData['subject'], $eventData['message'], $eventData['headers'])){
		$readyEvent[]=$eventId;
	}
}
if (!empty($readyEvent)){
	$sql=
		"
		UPDATE events
		SET event_remind=0
		WHERE event_id in (".implode(', ',$readyEvent).");";
	$mysqli->query($sql);
}
?>
