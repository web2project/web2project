<?php
trigger_error("m=admin has been deprecated. Please use m=users instead.", E_USER_NOTICE );

$event_id = (int) w2PgetParam($_GET, 'event_id', 0);

header("Location: index.php?m=users");