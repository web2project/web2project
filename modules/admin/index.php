<?php
trigger_error("m=admin has been deprecated. Please use m=users instead.", E_USER_NOTICE );

header("Location: index.php?m=users");