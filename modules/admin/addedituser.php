<?php

trigger_error("addedituser.php has been deprecated. Just use addedit instead.", E_USER_NOTICE );

$user_id    = (int) w2PgetParam($_GET, 'user_id', 0);
$contact_id = (int) w2PgetParam($_GET, 'contact_id', 0);

header("Location: index.php?m=users&a=addedit&user_id={$user_id}&contact_id={$contact_id}");