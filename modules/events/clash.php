<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

trigger_error("The clash check has been deprecated. There is no replacement.", E_USER_NOTICE );

header("Location: index.php?m=events");