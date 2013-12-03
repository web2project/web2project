<?php

trigger_error("viewuser.php has been deprecated. Just use addedit instead.", E_USER_NOTICE );

$user_id = (int) w2PgetParam($_GET, 'user_id', 0);

header("Location: index.php?m=users&a=view&user_id={$user_id}");