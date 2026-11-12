<?php
/**
 * Builds (or rebuilds) the unit tests' database as a fresh install.
 *
 *   php unit_tests/build-test-db.php
 *
 * The database name, host and login come from includes/config.php and the
 * W2P_TEST_* environment variables (see test-db.php); by default it is the
 * application database's name plus "_test". It is dropped and created again,
 * then every migration in install/sql runs through the installer's upgrade
 * manager, so it matches what the installer produces. The admin password is
 * "passwd", which the tests log in with.
 *
 * It refuses to touch the application's own database.
 *
 * @package    web2project
 * @subpackage unit_tests
 * @license    Clear BSD
 */

if ('cli' !== PHP_SAPI) {
    die('Run this from the command line.');
}

require_once __DIR__ . '/../base.php';
require_once W2P_BASE_DIR . '/includes/main_functions.php';
require_once W2P_BASE_DIR . '/includes/config.php';
require_once __DIR__ . '/test-db.php';

$appConfig = $w2Pconfig;
$test = w2p_unit_test_db_config($w2Pconfig);
if (null !== ($problem = w2p_unit_test_db_problem($appConfig, $test))) {
    fwrite(STDERR, $problem . "\n");
    exit(1);
}

// The values the installer asks for; the tests were written against these.
$test['adminpass'] = 'passwd';
$test['system_timezone'] = 'UTC';
$test['user_timezone'] = 'America/Chicago';

echo 'Test database: ' . $test['dbname'] . ' on ' . $test['dbhost'] . ' (user ' . $test['dbuser'] . ")\n";

mysqli_report(MYSQLI_REPORT_OFF);
$link = @new mysqli($test['dbhost'], $test['dbuser'], $test['dbpass']);
if ($link->connect_errno) {
    fwrite(STDERR, 'Connecting failed: ' . $link->connect_error . "\n");
    exit(1);
}
$name = '`' . str_replace('`', '``', $test['dbname']) . '`';
if (!$link->query('DROP DATABASE IF EXISTS ' . $name) ||
        !$link->query('CREATE DATABASE ' . $name . ' CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci')) {
    fwrite(STDERR, 'Creating the database failed: ' . $link->error . "\n" .
        "Create it as a MySQL administrator, then run this again:\n" .
        '  CREATE DATABASE ' . $name . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\n" .
        '  GRANT ALL PRIVILEGES ON ' . $name . ".* TO '" . $test['dbuser'] . "'@'" .
        ('localhost' === $test['dbhost'] || '127.0.0.1' === $test['dbhost'] ? 'localhost' : '%') . "';\n");
    exit(1);
}
$link->close();

$w2Pconfig = $test;
$manager = new w2p_System_UpgradeManager();
if (!$manager->testDatabaseCredentials($test)) {
    fwrite(STDERR, "The upgrade manager couldn't connect to the new database.\n");
    exit(1);
}
$errors = $manager->upgradeSystem();
if (count($errors)) {
    fwrite(STDERR, "Installing failed:\n" . implode("\n", $errors) . "\n" . $manager->getFailureReport() . "\n");
    exit(1);
}

echo 'Installed ' . count($manager->getUpdatesApplied()) . " migrations. Run the tests from unit_tests/:\n";
echo "  ../vendor/bin/phpunit\n";
