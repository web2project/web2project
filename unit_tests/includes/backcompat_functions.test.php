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
    
    
    public function test_date_diff() {
        $today       = new DateTime('now');
        $plus7_days  = new DateTime('+7 days');
        $minus1_day  = new DateTime('-1 day');
        $year2010    = new DateTime('2010-01-01');
        $year2030    = new DateTime('2030-01-01');
        $plus5_weeks = new DateTime('+5 weeks');

        $this->assertEquals(0,      date_diff2($today,      $today));
        $this->assertEquals(86400,  date_diff2($minus1_day, $today,      '%s'));
        $this->assertEquals(-10080, date_diff2($plus7_days, $today,      '%i'));
        $this->assertEquals(168,    date_diff2($today,      $plus7_days, '%h'));
        $this->assertEquals(7,      date_diff2($today,      $plus7_days, '%d'));
        $this->assertEquals(-8,     date_diff2($plus7_days, $minus1_day, '%d'));
        $this->assertEquals(7305,   date_diff2($year2010,   $year2030,   '%d')); // don't forget leap days!
        $this->assertEquals(20 ,    date_diff2($year2010,   $year2030,   '%y'));
        $this->assertEquals(-20,    date_diff2($year2030,   $year2010,   '%y'));
    }
    
}