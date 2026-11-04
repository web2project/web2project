<?php
/**
 * Tests for how w2p_System_UpgradeManager runs migrations: splitting files
 * into statements, stopping at the first failure, and reporting it.
 *
 * These use a fake connection, so they need no database or configuration:
 *   phpunit --no-configuration unit_tests/classes/w2p/System/UpgradeManagerMigrationsTest.php
 *
 * LICENSE: This source file is subject to Clear BSD License. Please see the
 *   LICENSE file in root of site for further details
 *
 * @category   w2p_System_UpgradeManager
 * @package    web2project
 * @subpackage unit_tests
 * @license    Clear BSD
 * @link       http://www.web2project.net
 */

if (!defined('W2P_BASE_DIR')) {
    define('W2P_BASE_DIR', dirname(__DIR__, 4));
}
if (!class_exists('w2p_System_UpgradeManager', false)) {
    require_once W2P_BASE_DIR . '/classes/w2p/System/UpgradeManager.class.php';
}

/* A result set with one row, as ADOdb returns for a SELECT. */
class w2p_FakeUpgradeResult
{
    public $fields;
    public $EOF = false;

    public function __construct(array $row)
    {
        $this->fields = $row;
    }
}

/*
 * Records every statement. A statement fails when it contains a key of
 * $failures, with that key's array(number, message). SELECTs for the server
 * details and the version return fixed rows.
 */
class w2p_FakeUpgradeConnection
{
    public $executed = array();
    public $failures = array();
    public $dbVersion = 55;
    /* The session sql_mode; null leaves SELECT/SET @@SESSION.sql_mode unanswered. */
    public $sqlMode = null;
    /* Each statement with the sql_mode it ran under, when $sqlMode is set. */
    public $modes = array();
    private $errorNumber = 0;
    private $errorMessage = '';

    public function Execute($sql)
    {
        $this->executed[] = $sql;
        if (null !== $this->sqlMode) {
            if ('SELECT @@SESSION.sql_mode' === $sql) {
                return new w2p_FakeUpgradeResult(array($this->sqlMode));
            }
            if (preg_match("/^SET SESSION sql_mode = '(.*)'$/", $sql, $match)) {
                $this->sqlMode = $match[1];
                return true;
            }
            $this->modes[] = array($sql, $this->sqlMode);
        }
        if (0 === strpos($sql, 'SELECT VERSION()')) {
            return new w2p_FakeUpgradeResult(array('8.0.36', 'STRICT_TRANS_TABLES,NO_ZERO_DATE'));
        }
        if (0 === strpos($sql, 'SELECT MAX(db_version)')) {
            return new w2p_FakeUpgradeResult(array($this->dbVersion));
        }
        foreach ($this->failures as $text => $error) {
            if (false !== strpos($sql, $text)) {
                list($this->errorNumber, $this->errorMessage) = $error;
                return false;
            }
        }
        if (0 === strpos($sql, 'INSERT INTO w2pversion') || 0 === strpos($sql, 'REPLACE INTO w2pversion')) {
            preg_match('/VALUES \((\d+)/', $sql, $match);
            $this->dbVersion = (int) $match[1];
        }
        $this->errorNumber = 0;
        $this->errorMessage = '';
        return true;
    }

    public function ErrorMsg()
    {
        return $this->errorMessage;
    }

    public function qstr($value)
    {
        return "'" . str_replace("'", "''", $value) . "'";
    }

    public function ErrorNo()
    {
        return $this->errorNumber;
    }
}

/* Exposes the protected steps, and serves migration files from memory. */
class w2p_TestableUpgradeManager extends w2p_System_UpgradeManager
{
    public $files = array();

    public function __construct()
    {
        $this->configOptions = array('adminpass' => 'secret-password', 'system_timezone' => 'UTC', 'user_timezone' => 'UTC');
    }

    public function split($sql)
    {
        return $this->_splitSQLUpdates($sql);
    }

    public function splitWithLines($sql)
    {
        return $this->_splitSQLStatements($sql);
    }

    public function apply($sql, $name, $conn)
    {
        return $this->_applySQL($sql, $name, $conn);
    }

    public function run(array $migrations, $currentVersion, $conn)
    {
        return $this->_runMigrations($migrations, $currentVersion, $conn, '5.0.0');
    }

    protected function _applySQLUpdates($sqlfile, $dbConn)
    {
        return $this->_applySQL($this->files[$sqlfile], $sqlfile, $dbConn);
    }

    /* For convertDotProject(): the connection it opens, and the sql_mode upgradeSystem() was called in. */
    public $conn;
    public $sqlModeAtUpgrade = false;

    protected function _openDBConnection()
    {
        return $this->conn;
    }

    protected function _scrubDotProjectData($dbConn)
    {
        return 0;
    }

    public function upgradeSystem()
    {
        $this->sqlModeAtUpgrade = $this->conn->sqlMode;
        return array();
    }
}

class w2p_System_UpgradeManagerMigrationsTest extends \PHPUnit\Framework\TestCase
{
    private $manager;
    private $conn;

    protected function setUp(): void
    {
        $this->manager = new w2p_TestableUpgradeManager();
        $this->conn = new w2p_FakeUpgradeConnection();
    }

    private function statements($sql)
    {
        return array_values(array_filter(array_map(function ($piece) {
            return trim(preg_replace('/\s+/', ' ', $piece));
        }, $this->manager->split($sql)), 'strlen'));
    }

    /*
     * A ';' inside a comment or a string must not end the statement (migration
     * 071 failed on upgrades because its header comment had one).
     */
    public function testSplitIgnoresSemicolonsInCommentsAndStrings()
    {
        $this->assertEquals(
            array("UPDATE `modules` SET `mod_ui_active` = 0 WHERE `mod_directory` = 'smartsearch'"),
            $this->statements("-- This hides the menu entry; access is still governed\n-- by permissions.\n\nUPDATE `modules` SET `mod_ui_active` = 0 WHERE `mod_directory` = 'smartsearch';\n")
        );
        $this->assertEquals(array('SELECT 1', 'SELECT 2'), $this->statements("# a; b\nSELECT 1; /* c; d */ SELECT 2;"));
        $this->assertEquals(array("INSERT INTO t VALUES ('a;b', 'it''s; fine', 'back\\'slash;')", 'SELECT "x;y"'),
            $this->statements("INSERT INTO t VALUES ('a;b', 'it''s; fine', 'back\\'slash;'); SELECT \"x;y\";"));
        $this->assertEquals(array('SELECT `odd;name` FROM t'), $this->statements('SELECT `odd;name` FROM t;'));
        $this->assertEquals(array('/*!40101 SET NAMES utf8 */', 'SELECT 5--1'), $this->statements("/*!40101 SET NAMES utf8 */;\nSELECT 5--1;"));
    }

    public function testSplitRecordsTheLineEachStatementStartsOn()
    {
        $sql = "-- header; with a semicolon\n\nSELECT 1;\n/* block\n comment */ SELECT\n  'multi\nline';\n\n`t`.x;\n";
        $pieces = array_values(array_filter($this->manager->splitWithLines($sql), function ($piece) {
            return '' !== trim($piece['sql']);
        }));

        $this->assertEquals(array(3, 5, 9), array_map(function ($piece) {
            return $piece['line'];
        }, $pieces));
    }

    public function testApplyStopsAtTheFirstFailingStatement()
    {
        $this->conn->failures = array('`b`' => array(1054, "Unknown column 'b'"));
        $sql = "UPDATE t SET a = 1;\n\nUPDATE t SET `b` = 2;\nUPDATE t SET c = 3;\n";

        $errors = $this->manager->apply($sql, '056_example.sql', $this->conn);

        $this->assertNotContains('UPDATE t SET c = 3', $this->conn->executed);
        $this->assertCount(1, $errors);
        $failure = $this->manager->getFailure();
        $this->assertEquals('056_example.sql', $failure['migration']);
        $this->assertEquals(2, $failure['statement_number']);
        $this->assertEquals(3, $failure['statement_count']);
        $this->assertEquals(1, $failure['statements_run']);
        $this->assertEquals(3, $failure['line']);
        $this->assertEquals(1054, $failure['error_number']);
        $this->assertEquals("Unknown column 'b'", $failure['error_message']);
        $this->assertEquals('UPDATE t SET `b` = 2', $failure['statement']);
        $this->assertEquals('8.0.36', $failure['server_version']);
        $this->assertEquals('STRICT_TRANS_TABLES,NO_ZERO_DATE', $failure['sql_mode']);
    }

    public function testAlreadyInPlaceErrorsDoNotStopTheMigration()
    {
        $this->conn->failures = array(
            'ADD `a`' => array(1060, "Duplicate column name 'a'"),
            "VALUES ('x')" => array(1062, "Duplicate entry 'x' for key 'name'"),
        );
        $sql = "ALTER TABLE t ADD `a` INT;\nINSERT INTO t (name) VALUES ('x');\nUPDATE t SET b = 1;";

        $this->assertEquals(array(), $this->manager->apply($sql, '010_example.sql', $this->conn));
        $this->assertNull($this->manager->getFailure());
        $this->assertContains('UPDATE t SET b = 1', $this->conn->executed);
    }

    /* A duplicate row while copying a table means rows are missing from the copy. */
    public function testDuplicateRowInInsertSelectStops()
    {
        $this->conn->failures = array('SELECT * FROM t' => array(1062, "Duplicate entry '7' for key 'PRIMARY'"));

        $this->manager->apply("INSERT INTO t2 SELECT * FROM t;\nRENAME TABLE t TO old_t;", '056_example.sql', $this->conn);

        $this->assertNotNull($this->manager->getFailure());
        $this->assertNotContains('RENAME TABLE t TO old_t', $this->conn->executed);
    }

    public function testReportShowsTheStatementWithoutTheAdminPassword()
    {
        $this->conn->failures = array('users' => array(1146, "Table 'w2p.users' doesn't exist"));

        $this->manager->apply("UPDATE users SET user_password = MD5('[ADMINPASS]');", '001_base_install.sql', $this->conn);
        $report = $this->manager->getFailureReport();

        $this->assertStringContainsString("MD5('[ADMINPASS]')", $report);
        $this->assertStringNotContainsString('secret-password', $report);
        $this->assertStringContainsString("MySQL error 1146: Table 'w2p.users' doesn't exist", $report);
    }

    public function testRunStopsWithoutRecordingTheFailedMigration()
    {
        $this->manager->files = array(
            '056_a.sql' => 'UPDATE a SET x = 1;',
            '057_b.sql' => "UPDATE b SET x = 1;\nUPDATE b SET `broken` = 1;",
            '058_c.sql' => 'UPDATE c SET x = 1;',
        );
        $this->conn->failures = array('`broken`' => array(1054, "Unknown column 'broken'"));

        $errors = $this->manager->run(array_keys($this->manager->files), 55, $this->conn);

        $this->assertCount(1, $errors);
        $this->assertStringContainsString('057_b.sql, statement 2 of 2 (line 2)', $errors[0]);
        $this->assertEquals(array('056_a.sql'), $this->manager->getUpdatesApplied());
        $this->assertEquals(56, $this->conn->dbVersion);
        $this->assertNotContains('UPDATE c SET x = 1', $this->conn->executed);

        $failure = $this->manager->getFailure();
        $this->assertEquals(56, $failure['db_version']);
        $this->assertEquals(55, $failure['start_version']);
        $this->assertEquals(array('057_b.sql', '058_c.sql'), $failure['not_applied']);
        $this->assertEquals(2, $failure['migration_number']);
        $this->assertEquals(3, $failure['migration_count']);

        $report = $this->manager->getFailureReport();
        $this->assertStringContainsString('Database version: 56 (it was 55 before this run)', $report);
        $this->assertStringContainsString('Completed in this run: 056_a.sql', $report);
        $this->assertStringContainsString('Not applied: 057_b.sql, 058_c.sql', $report);
        $this->assertStringContainsString('1 earlier statement in 057_b.sql had already run', $report);
    }

    public function testRunRecordsEveryMigrationWhenAllSucceed()
    {
        $this->manager->files = array('056_a.sql' => 'UPDATE a SET x = 1;', '057_b.sql' => 'UPDATE b SET x = 1;');

        $this->assertEquals(array(), $this->manager->run(array_keys($this->manager->files), 55, $this->conn));
        $this->assertEquals(array('056_a.sql', '057_b.sql'), $this->manager->getUpdatesApplied());
        $this->assertEquals(57, $this->conn->dbVersion);
        $this->assertSame('', $this->manager->getFailureReport());
    }

    /*
     * Migrations before 056 were written for MySQL 5 and run without the
     * strict and zero-date flags; 056 and later run in the server's mode,
     * which is put back when the run ends.
     */
    public function testMigrationsBefore056RunWithoutStrictModes()
    {
        $serverMode = 'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
        $this->conn->sqlMode = $serverMode;
        $this->conn->dbVersion = 34;
        $this->manager->files = array('035_a.sql' => 'ALTER TABLE a ADD x INT;', '056_b.sql' => 'ALTER TABLE b ADD x INT;');

        $this->assertEquals(array(), $this->manager->run(array_keys($this->manager->files), 34, $this->conn));

        $ran = array();
        foreach ($this->conn->modes as $entry) {
            $ran[$entry[0]] = $entry[1];
        }
        $this->assertSame('NO_ENGINE_SUBSTITUTION', $ran['ALTER TABLE a ADD x INT']);
        $this->assertSame($serverMode, $ran['ALTER TABLE b ADD x INT']);
        $this->assertSame($serverMode, $this->conn->sqlMode);
    }

    public function testServerModeIsRestoredWhenALegacyMigrationFails()
    {
        $serverMode = 'STRICT_TRANS_TABLES,NO_ZERO_DATE';
        $this->conn->sqlMode = $serverMode;
        $this->conn->dbVersion = 34;
        $this->conn->failures = array('ALTER TABLE a' => array(1054, "Unknown column 'y'"));
        $this->manager->files = array('035_a.sql' => 'ALTER TABLE a DROP y;');

        $this->assertNotEquals(array(), $this->manager->run(array_keys($this->manager->files), 34, $this->conn));
        $this->assertSame($serverMode, $this->conn->sqlMode);
    }

    private function dotProjectConversion($serverMode)
    {
        $this->conn->sqlMode = $serverMode;
        $this->conn->failures = array('FROM dpversion' => array(1146, "Table 'dpversion' doesn't exist"));
        $this->manager->conn = $this->conn;
        $this->manager->files = array(
            'dp21rc1_to_21rc2.sql' => 'ALTER TABLE contacts ADD a INT;',
            'dp_to_w2p1.sql' => 'ALTER TABLE contacts ADD b INT;',
            'dp_to_w2p2.sql' => 'ALTER TABLE contacts ADD c INT;',
        );
    }

    /*
     * dotProject's tables come from MySQL 5 and hold zero dates, so the
     * conversion runs like a migration before 056; the upgrade after it starts
     * in the server's mode.
     */
    public function testDotProjectConversionRunsWithoutStrictModes()
    {
        $serverMode = 'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
        $this->dotProjectConversion($serverMode);

        $this->assertEquals(array(), $this->manager->convertDotProject());

        $ran = array();
        foreach ($this->conn->modes as $entry) {
            $ran[$entry[0]] = $entry[1];
        }
        foreach (array('a', 'b', 'c') as $column) {
            $this->assertSame('NO_ENGINE_SUBSTITUTION', $ran['ALTER TABLE contacts ADD ' . $column . ' INT']);
        }
        $this->assertSame($serverMode, $this->manager->sqlModeAtUpgrade);
    }

    public function testServerModeIsRestoredWhenTheConversionFails()
    {
        $serverMode = 'STRICT_TRANS_TABLES,NO_ZERO_DATE';
        $this->dotProjectConversion($serverMode);
        $this->conn->failures['ADD b'] = array(1054, "Unknown column 'x'");

        $this->assertNotEquals(array(), $this->manager->convertDotProject());
        $this->assertSame($serverMode, $this->conn->sqlMode);
        $this->assertFalse($this->manager->sqlModeAtUpgrade);
    }

    /*
     * Before 056, w2pversion's primary key is code_revision or code_version,
     * not db_version, so a plain insert can clash with an earlier row.
     */
    public function testVersionRowReplacesARowWithTheSameKey()
    {
        $this->manager->files = array('003_a.sql' => 'UPDATE a SET x = 1;');
        $this->conn->failures = array('INSERT INTO w2pversion' => array(1062, "Duplicate entry '' for key 'PRIMARY'"));

        $this->assertEquals(array(), $this->manager->run(array('003_a.sql'), 2, $this->conn));
        $this->assertEquals(3, $this->conn->dbVersion);
        $this->assertStringStartsWith('REPLACE INTO w2pversion', end($this->conn->executed));
    }
}
