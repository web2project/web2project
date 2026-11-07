<?php
/**
 * Constants that base.php defines at runtime, for PHPStan (see phpstan.neon.dist).
 */
$baseDir = dirname(__DIR__);
defined('W2P_BASE_DIR') || define('W2P_BASE_DIR', $baseDir);
defined('W2P_BASE_URL') || define('W2P_BASE_URL', 'http://localhost');
defined('DP_BASE_DIR') || define('DP_BASE_DIR', $baseDir);
