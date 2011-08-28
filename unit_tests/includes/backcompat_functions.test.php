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
        $this->assertEquals(86400,  date_diff2($minus1_day, $today,      'S'));
        $this->assertEquals(-10080, date_diff2($plus7_days, $today,      'I'));
        $this->assertEquals(168,    date_diff2($today,      $plus7_days, 'H'));
        $this->assertEquals(7,      date_diff2($today,      $plus7_days, 'D'));
        $this->assertEquals(1,      date_diff2($today,      $plus7_days, 'W'));
        $this->assertEquals(-8,     date_diff2($plus7_days, $minus1_day, 'D'));
        $this->assertEquals(7305,   date_diff2($year2010,   $year2030,   'D')); // don't forget leap days!
        $this->assertEquals(20 ,    date_diff2($year2010,   $year2030,   'Y'));
        $this->assertEquals(-20,    date_diff2($year2030,   $year2010,   'Y'));
    }

    public function test_DateInterval_constructor() {

        $interval = new DateInterval2('P2Y4D6H8M');
        $this->assertEquals(
                array('y' => 2, 'm' => 0, 'd' => 4, 'h' => 6, 'i' => 8, 
                    's' => 0, 'invert' => 0, 'days' => 0), get_object_vars($interval));

        $interval = new DateInterval2('P3W6H8M');
        $this->assertEquals(
                array('y' => 0, 'm' => 0, 'd' => 21, 'h' => 6, 'i' => 8, 
                    's' => 0, 'invert' => 0, 'days' => 0), get_object_vars($interval));

        $interval = new DateInterval2('P1Y1D');
        $this->assertEquals(
                array('y' => 1, 'm' => 0, 'd' => 1, 'h' => 0, 'i' => 0, 
                    's' => 0, 'invert' => 0, 'days' => 0), get_object_vars($interval));

        $interval = new DateInterval2('P1Y1M37D500M');
        $this->assertEquals(
                array('y' => 1, 'm' => 1, 'd' => 37, 'h' => 0, 'i' => 500, 
                    's' => 0, 'invert' => 0, 'days' => 0), get_object_vars($interval));
        //TODO: test days & invert
    }
}