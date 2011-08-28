<?php
/**
 * Necessary global variables 
 */
global $db;
global $ADODB_FETCH_MODE;
global $w2p_performance_dbtime;
global $w2p_performance_old_dbqueries;
global $AppUI;

require_once '../base.php';
require_once W2P_BASE_DIR . '/includes/config.php';
require_once W2P_BASE_DIR . '/includes/main_functions.php';
require_once W2P_BASE_DIR . '/includes/db_adodb.php';

require_once 'PHPUnit/Framework.php';

/**
 * BackCompat_Functions_Test Class.
 * 
 * Class to test the backcompat_functions include
 * @author D. Keith Casey, Jr. <caseydk@users.sourceforge.net>
 * @package web2project
 * @subpackage unit_tests
 */
class BackCompat_Functions_Test extends PHPUnit_Framework_TestCase {

    public function placeholder() {
        $this->assertEquals(0, 0);
    }
}