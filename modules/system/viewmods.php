<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

// TODO: This is only in place to protect existing installs out there.
//   It will go away by v4.0
header("Location: ?m=system&u=modules");