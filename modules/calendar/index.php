<?php
trigger_error("m=calendar has been deprecated. Please use m=events instead.", E_USER_NOTICE );

header("Location: index.php?m=events");