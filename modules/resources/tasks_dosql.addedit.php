<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

// Set the pre and post save functions
global $pre_save, $post_save, $other_resources;

$pre_save[] = 'resource_presave';
$post_save[] = 'resource_postsave';
$other_resources = null;
