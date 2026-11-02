<?php
/**
 * @package web2project\system
 */

class w2p_System_UpgradeManager {
    protected $action = '';

    protected $configDir = '';
    protected $configFile = '';
    protected $uploadDir = '';
    protected $languageDir = '';
    protected $tempDir = '';
    protected $configOptions = array();
    protected $updatesApplied = array();
    protected $_w2Pconfig = '';

    /*
     * Set when a migration statement fails: the upgrade stops there and this
     * holds what is known about the failure (see getFailure()).
     */
    protected $failure = null;
    /* The database version before this run, and the migrations this run will apply. */
    protected $startVersion = 0;
    protected $pendingMigrations = array();

    public function __construct()
    {
        global $w2Pconfig;
        $this->_w2Pconfig = $w2Pconfig;
    }

    public function getActionRequired() {
        if ($this->action == '') {
            $this->_prepareConfiguration();
            if (!file_exists($this->configFile) || filesize($this->configFile) == 0) {
                $this->action = 'install';
            } else {
                require_once $this->configFile;
                if (isset($dPconfig)) {
                    $this->configOptions = $dPconfig;
                    $this->action = 'conversion';
                } elseif (isset($this->_w2Pconfig)) {
                    $this->configOptions = $this->_w2Pconfig;
                    $this->action = 'upgrade';
                } else {
                    /*
                     *  This  case should never be reached because if there is a config.
                     * php file, it should load either the $dPconfig or $w2Pconfig
                     * depending on whether it's an conversion or upgrade respectively.
                     * If we reach here, we have this strange situation of a mostly
                     * "configured" system that doesn't have the configuration values
                     * required.
                     */
                    $this->action = 'install';
                }
            }
        }
        return $this->action;
    }

    public function getConfigDir() {
        return $this->configDir;
    }

    public function getConfigFile() {
        return $this->configFile;
    }

    public function getUploadDir() {
        return $this->uploadDir;
    }

    public function getLanguageDir() {
        return $this->languageDir;
    }

    public function getTempDir() {
        return $this->tempDir;
    }

    public function getConfigOptions() {
        return $this->configOptions;
    }

    protected function _setConfigOptions($dbConfig) {
        $this->configOptions = $dbConfig;
    }

    public function upgradeSystem() {
        /** @var w2p_Core_CAppUI|null $AppUI */
        global $AppUI;
        $AppUI = is_object($AppUI) ? $AppUI : new w2p_Core_CAppUI();

        $allErrors = array();
        set_time_limit(0);
        $version = '';

        /*
         * The migrations in install/sql create and change unprefixed tables, so
         * an installation with a table prefix can't be upgraded (or have worked).
         */
        if (isset($this->configOptions['dbprefix']) && '' !== (string) $this->configOptions['dbprefix']) {
            return array('Table prefixes are not supported. Rename the tables without the \'' .
                $this->configOptions['dbprefix'] . '\' prefix and set $w2Pconfig[\'dbprefix\'] to \'\' in includes/config.php.');
        }

        $dbConn = $this->_openDBConnection();

        if ($dbConn) {
            $currentVersion = $this->_getDatabaseVersion($dbConn);
            $migrations = $this->_getMigrations();
            $allErrors = $this->_runMigrations($migrations, $currentVersion, $dbConn, $AppUI->getVersion());
        } else {
            $allErrors[] = 'Update failed. Database connection was not found.';
        }

        return $allErrors;
    }

    /*
     * Applies the migrations numbered above $currentVersion, in order, and
     * records each one in w2pversion once all of its statements have run. The
     * first statement that fails stops the run: that migration is not recorded,
     * so the database version stays at the last migration that completed, and
     * nothing after it runs. Returns the error messages (empty on success); the
     * details of a failure are in getFailure() and getFailureReport().
     */
    protected function _runMigrations($migrations, $currentVersion, $dbConn, $codeVersion) {
        $this->failure = null;
        $this->startVersion = (int) $currentVersion;
        $this->pendingMigrations = array();
        foreach ($migrations as $update) {
            if ((int) substr($update, 0, 3) > $currentVersion) {
                $this->pendingMigrations[] = $update;
            }
        }

        $lastMigration = end($migrations);
        $serverSqlMode = $this->_sessionSqlMode($dbConn);
        foreach ($this->pendingMigrations as $update) {
            $myIndex = (int) substr($update, 0, 3);
            $version = ($update == $lastMigration) ? $codeVersion : '';

            $this->_setMigrationSqlMode($dbConn, $myIndex, $serverSqlMode);
            $errorMessages = $this->_applySQLUpdates($update, $dbConn);
            if (null !== $this->failure) {
                $this->_setSessionSqlMode($dbConn, $serverSqlMode);
                return $errorMessages;
            }

            $sql = $this->_recordMigration($dbConn, $myIndex, $version);
            if (true !== $sql) {
                $this->_recordFailure($dbConn, $update, $sql, 0, 0, 0,
                    'Every statement in ' . $update . ' ran, but recording it in w2pversion failed.');
                return array($this->getFailureSummary());
            }
            $this->updatesApplied[] = $update;
        }
        $this->_setSessionSqlMode($dbConn, $serverSqlMode);

        return array();
    }

    /**
     * Migrations before this number were written for MySQL 5's default
     * sql_mode, which allowed zero dates: the 1.x and 2.x schemas give date
     * columns a '0000-00-00 00:00:00' default. Under MySQL 8's NO_ZERO_DATE
     * any ALTER TABLE on such a table fails ("Invalid default value"), so
     * upgrading a 2.x site stopped at 035. 056 is where the zero dates are
     * cleaned and the tables rebuilt, so it and everything after run in the
     * server's own mode.
     */
    const STRICT_FROM_MIGRATION = 56;

    /**
     * The sql_mode flags that migrations before STRICT_FROM_MIGRATION run
     * without: the ones MySQL 5 did not set by default.
     *
     * @var string[]
     */
    protected static $legacyOffModes = array('STRICT_TRANS_TABLES', 'STRICT_ALL_TABLES',
        'NO_ZERO_DATE', 'NO_ZERO_IN_DATE', 'ERROR_FOR_DIVISION_BY_ZERO', 'TRADITIONAL');

    /* The connection's sql_mode, or null if it can't be read. */
    protected function _sessionSqlMode($dbConn) {
        $res = $dbConn->Execute('SELECT @@SESSION.sql_mode');
        if ($res && is_object($res) && !$res->EOF) {
            return (string) $res->fields[0];
        }
        return null;
    }

    protected function _setSessionSqlMode($dbConn, $sqlMode) {
        if (null !== $sqlMode && $sqlMode !== $this->_sessionSqlMode($dbConn)) {
            $dbConn->Execute('SET SESSION sql_mode = ' . $dbConn->qstr($sqlMode));
        }
    }

    /*
     * Runs a migration before STRICT_FROM_MIGRATION without the strict and
     * zero-date flags, and a later one in the server's mode.
     */
    protected function _setMigrationSqlMode($dbConn, $migrationNumber, $serverSqlMode) {
        if (null === $serverSqlMode) {
            return;
        }
        $mode = $serverSqlMode;
        if ($migrationNumber < self::STRICT_FROM_MIGRATION) {
            $flags = array_filter(explode(',', strtoupper($serverSqlMode)));
            $mode = implode(',', array_diff($flags, self::$legacyOffModes));
        }
        $this->_setSessionSqlMode($dbConn, $mode);
    }

    /*
     * Adds the w2pversion row for a migration that completed. Returns true, or
     * the last statement tried when none worked (its error is still on the
     * connection).
     *
     * Until 056 rebuilds it, w2pversion's primary key isn't db_version: it is
     * code_revision on installs (0 on every row) and code_version on dotProject
     * conversions ('' on every row but the last migration's). So only one row
     * fit and the recorded version stayed put until 056. Each row now gets its
     * migration number as code_revision, which nothing else uses, and where
     * the key still clashes the clashing row is replaced: the version is the
     * highest db_version, so that row is the one that matters.
     */
    protected function _recordMigration($dbConn, $myIndex, $version) {
        $values = "$myIndex, '" . addslashes($version) . "', now(), now()";
        $attempts = array(
            "INSERT INTO w2pversion (code_revision, db_version, code_version, last_db_update, last_code_update) VALUES ($myIndex, $values)",
            "INSERT INTO w2pversion (db_version, code_version, last_db_update, last_code_update) VALUES ($values)",
        );

        $sql = '';
        foreach ($attempts as $sql) {
            if ($dbConn->Execute($sql)) {
                return true;
            }
            $error = $dbConn->ErrorMsg();
            if (strpos($error, 'Duplicate entry') !== false) {
                $sql = 'REPLACE' . substr($sql, strlen('INSERT'));
                return $dbConn->Execute($sql) ? true : $sql;
            }
            if (strpos($error, 'code_revision') === false) {
                return $sql;
            }
        }
        return $sql;
    }

    /*
     * Null when nothing has failed. Otherwise an array with: migration (file
     * name), statement_number and statement_count (within the file),
     * statements_run (how many before it succeeded), line (in the file),
     * statement (the SQL), error_number and error_message (from the server),
     * note (extra context), start_version and db_version (the recorded
     * version before the run and now), applied (migrations completed this run),
     * not_applied (the failed one and those after it), server_version and
     * sql_mode.
     */
    public function getFailure() {
        return $this->failure;
    }

    public function getUpdatesApplied() {
        return $this->updatesApplied;
    }

    public function convertDotProject() {
        $dpVersion = '';
        set_time_limit(0);

        $allErrors = array();
        $dbConn = $this->_openDBConnection();
        $sql = "SELECT * FROM dpversion ORDER BY db_version DESC";
        $res = $dbConn->Execute($sql);
        if ($res && $res->RecordCount() > 0) {
            $dpVersion = substr($res->fields['code_version'], 0, 5);
        }

        /*
         * Each step stops the conversion if it fails, like a migration does in
         * upgradeSystem(): the steps after it assume it worked.
         *
         * The first case handles 2.0 and falls through, the second 2.0.1, and
         * the default any other version of dotproject, validated for:
         *      2.0.2, 2.0.3, 2.0.4,
         *      2.1-rc1, 2.1-rc2, 2.1, 2.1.1, 2.1.2, 2.1.3, 2.1.4, 2.1.5, 2.1.6, 2.1.7, 2.1.8
         */
        $steps = array('dp21rc1_to_21rc2.sql', 'dp_to_w2p1.sql', 'scrub', 'dp_to_w2p2.sql');
        if ('2.0.1' == $dpVersion) {
            array_unshift($steps, 'dp201_to_202.sql');
        } elseif ('2.0' == $dpVersion) {
            array_unshift($steps, 'dp20_to_201.sql', 'dp201_to_202.sql');
        }

        $this->failure = null;
        $this->startVersion = 0;
        $this->pendingMigrations = array_values(array_diff($steps, array('scrub')));
        foreach ($steps as $step) {
            if ('scrub' == $step) {
                $this->_scrubDotProjectData($dbConn);
                continue;
            }
            $errorMessages = $this->_applySQLUpdates($step, $dbConn);
            if (null !== $this->failure) {
                $this->failure['note'] = trim($this->failure['note'] .
                    ' The dotProject conversion stopped here, so the web2project upgrade did not run.');
                return $errorMessages;
            }
            $this->updatesApplied[] = $step;
        }

        $errorMessages = $this->upgradeSystem();
        $allErrors = array_merge($allErrors, $errorMessages);

        return $allErrors;
    }

    public function createConfigString($dbConfig) {
        $configFile = file_get_contents(W2P_BASE_DIR . '/includes/config-dist.php');
        $configFile = str_replace('[DBTYPE]', $dbConfig['dbtype'], $configFile);
        $configFile = str_replace('[DBCHAR]', 'utf8', $configFile);
        $configFile = str_replace('[DBHOST]', $dbConfig['dbhost'], $configFile);
        $configFile = str_replace('[DBNAME]', $dbConfig['dbname'], $configFile);
        $configFile = str_replace('[DBUSER]', $dbConfig['dbuser'], $configFile);
        $configFile = str_replace('[DBPASS]', $dbConfig['dbpass'], $configFile);
        //TODO: add support for configurable persistent connections
        $configFile = trim($configFile);

        return $configFile;
    }

    public function getMaxFileUpload() {
        $maxfileuploadsize = min($this->_getIniSize(ini_get('upload_max_filesize')), $this->_getIniSize(ini_get('post_max_size')));
        $memory_limit = $this->_getIniSize(ini_get('memory_limit'));
        if ($memory_limit > 0 && $memory_limit < $maxfileuploadsize) $maxfileuploadsize = $memory_limit;
        // Convert back to human readable numbers
        if ($maxfileuploadsize > 1048576) {
            $maxfileuploadsize = (int)($maxfileuploadsize / 1048576) . 'M';
        } else if ($maxfileuploadsize > 1024) {
            $maxfileuploadsize = (int)($maxfileuploadsize / 1024) . 'K';
        }

        return $maxfileuploadsize;
    }

    public function testDatabaseCredentials($w2Pconfig) {
        $result = false;

        $this->_setConfigOptions($w2Pconfig);

        $dbConn = $this->_openDBConnection();
        if ($dbConn->_errorMsg == '') {
            $result = true;
        }

        return $result;
    }

    public function upgradeRequired() {
        $dbConn = $this->_openDBConnection();
        return (count($this->_getMigrations()) > $this->_getDatabaseVersion($dbConn));
    }

    protected function _getIniSize($val) {
       $val = trim($val);
       if (strlen($val <= 1)) return $val;
       $last = substr($val, -1);
       switch($last) {
           case 'k':
           case 'K':
               return (int) $val * 1024;
           case 'm':
           case 'M':
               return (int)   $val * 1048576;
           default:
               return $val;
       }
    }

    protected function _getMigrations() {
        $migrations = array();

        $path = W2P_BASE_DIR.'/install/sql/';
        $dir_handle = @opendir($path) or die("Unable to open $path");

        while ($file = readdir($dir_handle)) {
           $migrationNumber = (int) substr($file, 0, 3);
           if ($migrationNumber > 0) {
             $migrations[$migrationNumber] = $file;
           }
        }
        sort($migrations);
        return $migrations;
    }

    protected function _getDatabaseVersion($dbConn) {

        $sql = "SELECT max(db_version) FROM w2pversion";
        $res = $dbConn->Execute($sql);

        if ($res && $res->RecordCount() > 0) {
            $currentVersion = $res->fields[0];
        } else {
            $currentVersion = 0;
        }

        return $currentVersion;
    }

    protected function _prepareConfiguration() {
        $this->configDir = W2P_BASE_DIR.'/includes';
        $this->configFile = W2P_BASE_DIR.'/includes/config.php';
        $this->uploadDir = W2P_BASE_DIR.'/files';
        $this->languageDir = W2P_BASE_DIR.'/locales/en';
        $this->tempDir = W2P_BASE_DIR.'/files/temp';
    }

    /*
     * Runs one file from install/sql. Stops at the first statement that fails
     * with an error that isn't ignorable (see _isIgnorableError()), records the
     * failure and returns its summary. Returns an empty array when the whole
     * file ran.
     */
    protected function _applySQLUpdates($sqlfile, $dbConn) {
        $path = W2P_BASE_DIR.'/install/sql/'.$sqlfile;
        if (!file_exists($path) || filesize($path) == 0) {
            return array();
        }

        $query = file_get_contents($path);
        if (false === $query) {
            $this->_recordFailure($dbConn, $sqlfile, '', 0, 0, 0,
                'The file ' . $path . ' could not be read, so none of it ran.', array(0, ''));
            return array($this->getFailureSummary());
        }

        return $this->_applySQL($query, $sqlfile, $dbConn);
    }

    protected function _applySQL($query, $name, $dbConn) {
        $statements = array();
        foreach ($this->_splitSQLStatements($query) as $piece) {
            $sql = trim($piece['sql']);
            if ('' !== $sql && '#' !== $sql) {
                $statements[] = array('sql' => $sql, 'line' => $piece['line']);
            }
        }
        $statementCount = count($statements);

        foreach ($statements as $index => $statement) {
            /*
             * While this seems like a good place to use the core classes, it's
             * really not.  With all of the dependencies, it just gets to be a huge
             * pain and ends up loading all kinds of unnecessary stuff.
             */
            $sql = str_replace(
                array('[ADMINPASS]', '[SYSTEM_TIMEZONE]', '[USER_TIMEZONE]'),
                array(
                    isset($this->configOptions['adminpass']) ? $this->configOptions['adminpass'] : '',
                    isset($this->configOptions['system_timezone']) ? $this->configOptions['system_timezone'] : '',
                    isset($this->configOptions['user_timezone']) ? $this->configOptions['user_timezone'] : '',
                ),
                $statement['sql']
            );
            if ($dbConn->Execute($sql)) {
                continue;
            }

            $error = array($this->_errorNumber($dbConn), $dbConn->ErrorMsg());
            if ($this->_isIgnorableError($error[1], $sql)) {
                continue;
            }

            /* The report shows the statement as written, so it never includes the admin password. */
            $this->_recordFailure($dbConn, $name, $statement['sql'], $index + 1, $statementCount,
                $statement['line'], '', $error);
            return array($this->getFailureSummary());
        }

        return array();
    }

    /*
     * Errors that mean the database is already as the statement would leave
     * it: adding a column, key or primary key that is already there, dropping
     * one that is already gone, or inserting a row (INSERT ... VALUES) that is
     * already there. Older migrations repeat each other, so these come up on
     * upgrades from earlier versions. A duplicate row from INSERT ... SELECT is
     * not ignored: there it means part of a copy is missing.
     */
    protected function _isIgnorableError($errorMessage, $sql) {
        $alreadyInPlace = array('Duplicate column name', 'column/key exists', 'Multiple primary key defined',
            'Duplicate key name', 'check that it exists');
        foreach ($alreadyInPlace as $text) {
            if (strpos($errorMessage, $text) !== false) {
                return true;
            }
        }

        return strpos($errorMessage, 'Duplicate entry') !== false
            && preg_match('/^\s*INSERT\b/i', $sql)
            && !preg_match('/\bSELECT\b/i', $sql);
    }

    protected function _errorNumber($dbConn) {
        return method_exists($dbConn, 'ErrorNo') ? (int) $dbConn->ErrorNo() : 0;
    }

    /*
     * Stores the failure. $error is array(number, message); when it is null
     * the connection's last error is used, so call this straight after the
     * failed Execute().
     */
    protected function _recordFailure($dbConn, $migration, $statement, $statementNumber, $statementCount,
            $line, $note, $error = null) {
        if (null === $error) {
            $error = array($this->_errorNumber($dbConn), $dbConn->ErrorMsg());
        }

        $serverVersion = '';
        $sqlMode = '';
        $res = $dbConn->Execute('SELECT VERSION(), @@SESSION.sql_mode');
        if ($res && is_object($res) && !$res->EOF) {
            $serverVersion = (string) $res->fields[0];
            $sqlMode = (string) $res->fields[1];
        }

        $dbVersion = $this->startVersion;
        $res = $dbConn->Execute('SELECT MAX(db_version) FROM w2pversion');
        if ($res && is_object($res) && !$res->EOF) {
            $dbVersion = (int) $res->fields[0];
        }

        $position = array_search($migration, $this->pendingMigrations);
        $notApplied = (false === $position) ? array($migration) : array_slice($this->pendingMigrations, $position);

        $this->failure = array(
            'migration' => $migration,
            'migration_number' => (false === $position) ? 0 : $position + 1,
            'migration_count' => count($this->pendingMigrations),
            'statement_number' => $statementNumber,
            'statement_count' => $statementCount,
            'statements_run' => max(0, $statementNumber - 1),
            'line' => $line,
            'statement' => $statement,
            'error_number' => (int) $error[0],
            'error_message' => (string) $error[1],
            'note' => $note,
            'start_version' => $this->startVersion,
            'db_version' => $dbVersion,
            'applied' => $this->updatesApplied,
            'not_applied' => $notApplied,
            'server_version' => $serverVersion,
            'sql_mode' => $sqlMode,
        );
    }

    /* "none", "a.sql", "a.sql, b.sql" or "a.sql through f.sql (6 migrations)". */
    protected function _describeRun($migrations) {
        $count = count($migrations);
        if (0 == $count) {
            return 'none';
        }
        if ($count <= 2) {
            return implode(', ', $migrations);
        }
        return reset($migrations) . ' through ' . end($migrations) . ' (' . $count . ' migrations)';
    }

    /* One line for the error list: where it stopped and the server's error. */
    public function getFailureSummary() {
        $f = $this->failure;
        if (null === $f) {
            return '';
        }

        $where = $f['migration'];
        if ($f['statement_number'] > 0) {
            $where .= ', statement ' . $f['statement_number'] . ' of ' . $f['statement_count'] . ' (line ' . $f['line'] . ')';
        }
        $error = ('' !== $f['error_message'])
            ? 'MySQL error ' . $f['error_number'] . ': ' . $f['error_message']
            : $f['note'];

        return $where . ': ' . $error . ' The upgrade stopped here.';
    }

    /*
     * Plain text describing the failure in full, for the upgrade pages and for
     * bug reports. Empty when nothing failed. Escape it before putting it in HTML.
     */
    public function getFailureReport() {
        $f = $this->failure;
        if (null === $f) {
            return '';
        }

        $lines = array();
        $stoppedAt = 'The upgrade stopped at ' . $f['migration'];
        if ($f['migration_count'] > 0) {
            $stoppedAt .= ' (' . $f['migration_number'] . ' of ' . $f['migration_count'] . ' to apply in this run)';
        }
        if ($f['statement_number'] > 0) {
            $stoppedAt .= ', statement ' . $f['statement_number'] . ' of ' . $f['statement_count'] .
                ', which starts on line ' . $f['line'] . ' of install/sql/' . $f['migration'];
        }
        $lines[] = $stoppedAt . '.';
        $lines[] = '';

        if ('' !== $f['error_message']) {
            $lines[] = 'MySQL error ' . $f['error_number'] . ': ' . $f['error_message'];
        }
        if ('' !== $f['note']) {
            $lines[] = $f['note'];
        }

        if ('' !== $f['statement']) {
            $statement = $f['statement'];
            $limit = 4000;
            if (strlen($statement) > $limit) {
                $statement = substr($statement, 0, $limit) . "\n... (" . (strlen($statement) - $limit) . ' more characters)';
            }
            $lines[] = '';
            $lines[] = 'Statement:';
            $lines[] = $statement;
        }

        $lines[] = '';
        $lines[] = 'Database version: ' . $f['db_version'] . ' (it was ' . $f['start_version'] . ' before this run). ' .
            $f['migration'] . ' was not recorded as applied.';
        $lines[] = 'Completed in this run: ' . $this->_describeRun($f['applied']);
        $lines[] = 'Not applied: ' . $this->_describeRun($f['not_applied']);
        if ('' !== $f['server_version']) {
            $lines[] = 'Database server: ' . $f['server_version'] . '; sql_mode: ' .
                ('' !== $f['sql_mode'] ? $f['sql_mode'] : '(empty)');
        }

        $lines[] = '';
        $lines[] = 'What to do next:';
        if ($f['statement_number'] == 0 && '' === $f['statement']) {
            $lines[] = '- Make the file readable by the web server and run the upgrade again. It starts at ' . $f['migration'] . '.';
        } elseif ($f['statement_number'] == 0) {
            $lines[] = '- The changes in ' . $f['migration'] . ' are in place, but running the upgrade again would apply it a second time.' .
                ' Fix the problem with w2pversion and add its row by hand, or restore your backup and run the upgrade again.';
        } elseif ($f['statements_run'] > 0) {
            $lines[] = '- ' . $f['statements_run'] . ' earlier statement' . ($f['statements_run'] == 1 ? '' : 's') .
                ' in ' . $f['migration'] . ' had already run, so the database is part-way through this migration.' .
                ' Restore the backup you made before upgrading (for a new installation, empty the database),' .
                ' fix the cause, and run the upgrade again.';
        } else {
            $lines[] = '- Nothing in ' . $f['migration'] . ' had run yet. Fix the cause and run the upgrade again: it starts at ' .
                $f['migration'] . '.';
        }
        $lines[] = '- If you report this, include this whole message.';

        return implode("\n", $lines);
    }

    /*
     * Splits a migration file into statements. A ';' ends a statement only
     * outside quotes and comments, so a ';' in a comment or a string value no
     * longer cuts a statement in two. Comments are dropped (MySQL's versioned
     * comments, /*! ... *\/, are kept because the server runs them), so a
     * piece made only of comments comes back empty and is skipped.
     */
    protected function _splitSQLUpdates($sql) {
        return array_map(function ($statement) {
            return $statement['sql'];
        }, $this->_splitSQLStatements($sql));
    }

    /*
     * As _splitSQLUpdates(), but each piece is array('sql' => ..., 'line' => ...)
     * with the line its first character is on (0 for a piece with no SQL).
     */
    protected function _splitSQLStatements($sql) {
        $statements = array();
        $current = '';
        $start = null;
        $quote = '';
        $length = strlen($sql);
        $lineOf = function ($offset) use ($sql) {
            return (null === $offset) ? 0 : substr_count($sql, "\n", 0, $offset) + 1;
        };

        for ($i = 0; $i < $length; $i++) {
            $char = $sql[$i];
            $next = ($i + 1 < $length) ? $sql[$i + 1] : '';

            if ('' !== $quote) {
                $current .= $char;
                if ('\\' === $char && '`' !== $quote && '' !== $next) {
                    $current .= $next;
                    $i++;
                } elseif ($char === $quote) {
                    if ($next === $quote) {
                        $current .= $next;
                        $i++;
                    } else {
                        $quote = '';
                    }
                }
                continue;
            }

            if ("'" === $char || '"' === $char || '`' === $char) {
                $quote = $char;
                $current .= $char;
                if (null === $start) {
                    $start = $i;
                }
                continue;
            }

            // '-- ' (MySQL needs whitespace after the dashes) and '#' run to the end of the line.
            if (('-' === $char && '-' === $next && ($i + 2 >= $length || ctype_space($sql[$i + 2]))) || '#' === $char) {
                $end = strpos($sql, "\n", $i);
                $i = (false === $end) ? $length : $end - 1;
                continue;
            }

            if ('/' === $char && '*' === $next && ($i + 2 >= $length || '!' !== $sql[$i + 2])) {
                $end = strpos($sql, '*/', $i + 2);
                $i = (false === $end) ? $length : $end + 1;
                $current .= ' ';
                continue;
            }

            if (';' === $char) {
                $statements[] = array('sql' => $current, 'line' => $lineOf($start));
                $current = '';
                $start = null;
                continue;
            }

            $current .= $char;
            if (null === $start && !ctype_space($char)) {
                $start = $i;
            }
        }
        $statements[] = array('sql' => $current, 'line' => $lineOf($start));

        return $statements;
    }

    protected function _openDBConnection() {
        /*
         * While this seems like a good place to use the core classes, it's
         * really not.  With all of the dependencies, it just gets to be a huge
         * pain and ends up loading all kinds of unnecessary stuff.
         */
        $db = false;

        try {
            $db = NewADOConnection($this->configOptions['dbtype']);
            if(!empty($db)) {
              $dbConnection = $db->Connect($this->configOptions['dbhost'], $this->configOptions['dbuser'], $this->configOptions['dbpass']);
              if ($dbConnection) {
                $existing_db = $db->SelectDB($this->configOptions['dbname']);
                if (!$existing_db) {
                  $db->_errorMsg = 'This database user does not have rights to the database.';
                }
              }
            }
        } catch (Exception $exc) {
            echo 'Your database credentials do not work.';
        }
        return $db;
    }

    protected function _scrubDotProjectData($dbConn) {
        /*
         * While this seems like a good place to use the core classes, it's
         * really not.  With all of the dependencies, it just gets to be a huge
         * pain and ends up loading all kinds of unnecessary stuff.
         */
        rmdir(W2P_BASE_DIR.'/db/');
        $recordsUpdated = 0;

        $sql = "SELECT * FROM sysvals WHERE sysval_value like '%|%' ORDER BY sysval_id ASC";
        $res = $dbConn->Execute($sql);
        if ($res->RecordCount() > 0) {
            while (!$res->EOF) {
                $fields = $res->fields;

                $sysvalId = $fields['sysval_id'];
                $sysvalKeyId = $fields['sysval_key_id'];
                $sysvalTitle = $fields['sysval_title'];
                $values = explode("\n", $fields['sysval_value']);
                foreach ($values as $syskey) {
                    $sysvalValId = substr($syskey, 0, strpos($syskey, '|'));
                    $sysvalValue = substr(trim(' '.$syskey.' '), strpos($syskey, '|') + 1);
                    $sql = "INSERT INTO sysvals (sysval_key_id, sysval_title, sysval_value, sysval_value_id) " .
                            "VALUES ($sysvalKeyId, '$sysvalTitle', '$sysvalValue', $sysvalValId)";
                    $dbConn->Execute($sql);
                }
                $recordsUpdated++;
                $sql = "DELETE FROM sysvals WHERE sysval_id = $sysvalId";
                $dbConn->Execute($sql);
                $res->MoveNext();
            }
        }
        return $recordsUpdated;
    }
}