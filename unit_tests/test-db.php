<?php
/**
 * Which database the unit tests use.
 *
 * The tests create, change and delete rows, run the upgrade manager
 * (UpgradeManagerTest) and expect a fresh install's data (roles, default
 * preferences, the config table's site name). So they never run against the
 * database in includes/config.php; they use a separate one:
 *
 *   W2P_TEST_DBNAME  default: <dbname from includes/config.php>_test
 *   W2P_TEST_DBHOST  default: dbhost from includes/config.php
 *   W2P_TEST_DBUSER  default: dbuser from includes/config.php
 *   W2P_TEST_DBPASS  default: dbpass from includes/config.php
 *
 * Build (or rebuild) it with: php unit_tests/build-test-db.php
 *
 * @package    web2project
 * @subpackage unit_tests
 * @license    Clear BSD
 */

/**
 * Returns $config with the database settings switched to the test database.
 *
 * @param array $config the application's $w2Pconfig
 *
 * @return array
 */
function w2p_unit_test_db_config(array $config)
{
    $env = function ($name) {
        $value = getenv($name);
        return (false === $value || '' === $value) ? null : $value;
    };

    $test = $config;
    $test['dbhost'] = $env('W2P_TEST_DBHOST') ?? ($config['dbhost'] ?? 'localhost');
    $test['dbname'] = $env('W2P_TEST_DBNAME') ?? (($config['dbname'] ?? 'web2project') . '_test');
    $test['dbuser'] = $env('W2P_TEST_DBUSER') ?? ($config['dbuser'] ?? '');
    $test['dbpass'] = $env('W2P_TEST_DBPASS') ?? ($config['dbpass'] ?? '');
    $test['dbpersist'] = false;

    return $test;
}

/**
 * Null when $test can be used for the tests, otherwise the reason it can't.
 *
 * @param array $app  the application's $w2Pconfig
 * @param array $test the result of w2p_unit_test_db_config()
 *
 * @return string|null
 */
function w2p_unit_test_db_problem(array $app, array $test)
{
    if (strtolower((string) ($app['dbname'] ?? '')) === strtolower((string) $test['dbname'])
            && strtolower((string) ($app['dbhost'] ?? '')) === strtolower((string) $test['dbhost'])) {
        return 'The test database is the application database (' . $test['dbname'] . ' on ' . $test['dbhost'] .
            '). The tests change and delete data, so set W2P_TEST_DBNAME to another database.';
    }

    return null;
}
