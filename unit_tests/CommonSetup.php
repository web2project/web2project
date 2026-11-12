<?php

/*
 * All of the module tests go through a common process of setting up the globals,
 *   doing the includes, logging in and setting the base configuration.
 *   Unfortunately, not all of it can immediately go into a common setUp
 *   method but a bunch of it can.
 */

/**
 * Necessary global variables
 */
global $db;
global $ADODB_FETCH_MODE;
global $w2p_performance_dbtime;
global $w2p_performance_old_dbqueries;
global $AppUI;

require_once '../base.php';
require_once W2P_BASE_DIR . '/includes/main_functions.php';
require_once W2P_BASE_DIR . '/includes/config.php';

/*
 * The tests change and delete data and expect a fresh install, so they run on
 * their own database, never the one in includes/config.php. See test-db.php.
 */
require_once __DIR__ . '/test-db.php';
$appConfig = $w2Pconfig;
$w2Pconfig = w2p_unit_test_db_config($w2Pconfig);
$testDbProblem = w2p_unit_test_db_problem($appConfig, $w2Pconfig);
if (null === $testDbProblem) {
    try {
        mysqli_report(MYSQLI_REPORT_OFF);
        $check = @new mysqli($w2Pconfig['dbhost'], $w2Pconfig['dbuser'], $w2Pconfig['dbpass'], $w2Pconfig['dbname']);
        if ($check->connect_errno) {
            $testDbProblem = $check->connect_error;
        } else {
            $check->close();
        }
    } catch (Throwable $e) {
        $testDbProblem = $e->getMessage();
    }
    if (null !== $testDbProblem) {
        $testDbProblem = "Can't use the test database " . $w2Pconfig['dbname'] . ' on ' . $w2Pconfig['dbhost'] .
            ': ' . $testDbProblem . "\nBuild it with: php unit_tests/build-test-db.php (see unit_tests/test-db.php).";
    }
}
if (null !== $testDbProblem) {
    fwrite(STDERR, $testDbProblem . "\n");
    exit(1);
}
unset($appConfig, $testDbProblem, $check);

require_once W2P_BASE_DIR . '/includes/db_adodb.php';

/*
 * Need this to not get the annoying timezone warnings in tests.
 */
$defaultTZ = w2PgetConfig('system_timezone', 'UTC');
$defaultTZ = ('' == $defaultTZ) ? 'UTC' : $defaultTZ;
date_default_timezone_set($defaultTZ);

/*
 * Need this to test actions that require permissions.
 */
$AppUI  = new w2p_Core_CAppUI();
$_POST['login'] = 'login';
$_REQUEST['login'] = 'sql';
$AppUI->login('admin', 'passwd');
$AppUI->user_email = 'something@something.com';

/*
 * Load the translations as index.php does, so tests that check translated
 * text (e.g. mail bodies) don't depend on an earlier test having loaded them.
 */
$AppUI->setUserLocale();
$AppUI->loadLocale();

class CommonSetup extends \PHPUnit\Framework\TestCase
{
    protected $backupGlobals = false;
    protected $obj = null;
    protected $post_data = array();
    protected $mockDB = null;
    protected $_AppUI = null;

    protected function setUp(): void
    {
        parent::setUp();

        global $AppUI;
        $this->_AppUI  = $AppUI;
        $this->_AppUI->restoreObject();

        $this->mockDB = new w2p_Mocks_Query();
    }

    protected function tearDown(): void
    {
        unset($this->obj, $this->post_data);

        parent::tearDown();
    }

    /**
     * Tests the Attributes of a new object.
     *
     * This method is not prefixed with 'test' because it is a helper and should not be tested on its own.
     */
    public function objectPropertiesTest($classname, $fieldCount, $removeFields = array())
    {
        $params = get_object_vars($this->obj);
        foreach ($removeFields as $key => $value) {
            unset($params[$value]);
        }

        $this->assertInstanceOf($classname,     $this->obj);
        $this->assertEquals($fieldCount,        count($params));

        foreach ($params as $key => $value) {
            $this->assertNull($this->obj->{$key}, "$classname::$key did not pass validation");
        }
    }
}
