<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Class for testing Date functionality
 *
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version. Please see the LICENSE file in root of site
 * for further details
 *
 * @category    Date
 * @package     web2project
 * @subpackage  unit_tests
 * @author      D. Keith Casey, Jr.
 * @copyright   2007-2010 The web2Project Development Team <w2p-developers@web2project.net>
 * @link        http://www.web2project.net
 */

/**
 * Necessary global variables
 */
global $AppUI;
global $_DATE_TIMEZONE_DATA;
global $w2Pconfig;

require_once '../base.php';
require_once W2P_BASE_DIR . '/includes/main_functions.php';

/*
 * Need this to test actions that require permissions.
 */
$AppUI  = new CAppUI;
$_POST['login'] = 'login';
$_REQUEST['login'] = 'sql';
$AppUI->login('admin', 'passwd');

require_once 'PHPUnit/Framework.php';

/**
 * DateTest Class.
 *
 * Class to test the date include
 * @author D. Keith Casey, Jr.
 * @package web2project
 * @subpackage unit_tests
 */
class Date_Test extends PHPUnit_Framework_TestCase
{

    /**
     * Stores old working days value while tests being run
     *
     * @param string
     * @access private
     */
    private $old_working_days;

    /**
     * Stores old day start value while tests being run
     *
     * @param int
     * @access private
     */
    private $old_cal_day_start;

    /**
     * Stores old say end value while test being run
     *
     * @param int
     * @access private
     */
    private $old_cal_day_end;

    /**
     * Save our global settings before running tests
     */
    protected function setUp()
    {
        global $w2Pconfig;

        parent::setUp();

        // Save old working days, day start and end
        $this->old_working_days             = $w2Pconfig['cal_working_days'];
        $this->old_cal_day_start            = $w2Pconfig['cal_day_start'];
        $this->old_cal_day_end              = $w2Pconfig['cal_day_end'];
        $this->old_dwh                      = $w2Pconfig['daily_working_hours'];
        $w2Pconfig['cal_working_days']      = '1,2,3,4,5';
        $w2Pconfig['cal_day_start']         = 9;
        $w2Pconfig['cal_day_end']           = 17;
        $w2Pconfig['daily_working_hours']   = 8;
    }

    /**
     * Restore our global settings after running tests
     */
    protected function tearDown()
    {
        global $w2Pconfig;

        parent::tearDown();

        // Restore old working days, day start and end
        $w2Pconfig['cal_working_days']      = $this->old_working_days;
        $w2Pconfig['cal_day_start']         = $this->old_cal_day_start;
        $w2Pconfig['cal_day_end']           = $this->old_cal_day_end;
        $w2Pconfig['daily_working_hours']   = $this->old_dwh;
    }

    /**
     * Tests constructor without arguments
     */
    public function testConstructorNoDateTimeNoTz()
    {
        $date       = new w2p_Utilities_Date();
        $datetime   = new DateTime();
        $timezone   = new DateTimeZone($datetime->getTimezone()->getName());

        $this->assertInstanceOf('w2p_Utilities_Date', $date);
        $this->assertEquals($datetime->format('Y'),   $date->year);
        $this->assertEquals($datetime->format('m'),   $date->month);
        $this->assertEquals($datetime->format('d'),   $date->day);
        $this->assertEquals($datetime->format('H'),   $date->hour);
        $this->assertEquals($datetime->format('i'),   $date->minute);
        $this->assertEquals($datetime->format('s'),   $date->second);

        $this->assertEquals($datetime->getOffset(),   $date->tz['offset']/1000);
        $this->assertEquals($timezone->getName(),     $date->tz['id']);
    }

    /**
     * Tests constructor with a datetime, but no timezone
     */
    public function testConstructorDateTimeNoTz()
    {
        $date       = new w2p_Utilities_Date('2010-08-07 11:00:00');
        $datetime   = new DateTime('2010-08-07 11:00:00');
        $timezone   = new DateTimeZone($datetime->getTimezone()->getName());

        $this->assertInstanceOf('w2p_Utilities_Date',     $date);
        $this->assertEquals($datetime->format('Y'),       $date->year);
        $this->assertEquals($datetime->format('m'),       $date->month);
        $this->assertEquals($datetime->format('d'),       $date->day);
        $this->assertEquals($datetime->format('H'),       $date->hour);
        $this->assertEquals($datetime->format('i'),       $date->minute);
        $this->assertEquals($datetime->format('s'),       $date->second);

        $this->assertEquals($timezone->getName(),         $date->tz['id']);
    }

    /**
     * Tests constructor with a datetime and timezone
     */
    public function testConstructorDateTimeTz()
    {
        $date       = new w2p_Utilities_Date('2010-08-07 11:00:00', 'America/Halifax');
        $datetime   = new DateTime('2010-08-07 11:00:00', new DateTimeZone('America/Halifax'));
        $timezone   = new DateTimeZone($datetime->getTimezone()->getName());

        $this->assertInstanceOf('w2p_Utilities_Date', $date);
        $this->assertEquals($datetime->format('Y'),   $date->year);
        $this->assertEquals($datetime->format('m'),   $date->month);
        $this->assertEquals($datetime->format('d'),   $date->day);
        $this->assertEquals($datetime->format('H'),   $date->hour);
        $this->assertEquals($datetime->format('i'),   $date->minute);
        $this->assertEquals($datetime->format('s'),   $date->second);

        $this->assertEquals($timezone->getName(),     $date->tz['id']);
    }

    /**
     * Tests constructor with an invalid datetime
     */
    public function testConstructorInvalidDateTime()
    {
        $date       = new w2p_Utilities_Date('2010-35-35 28:65:85');
        $datetime   = new DateTime();
        $timezone   = new DateTimeZone($datetime->getTimezone()->getName());

        $this->assertInstanceOf('w2p_Utilities_Date', $date);
        $this->assertEquals(2010,                     $date->year);
        $this->assertEquals(35,                       $date->month);
        $this->assertEquals(35,                       $date->day);
        $this->assertEquals(28,                       $date->hour);
        $this->assertEquals(65,                       $date->minute);
        $this->assertEquals(85,                       $date->second);

        $this->assertEquals($timezone->getName(),     $date->tz['id']);
    }

    /**
     * Tests constructor with an invalid timezone
     *
     * expectedException PHPUnit_Framework_Error
     */
    public function testConstructorInvalidTimezone()
    {
        $date = new w2p_Utilities_Date('2010-08-07 22:10:27', 'Halifax');
        $datetime = new DateTime('2010-08-07 22:10:27');

        $this->assertInstanceOf('w2p_Utilities_Date', $date);
        $this->assertEquals($datetime->format('Y'),   $date->year);
        $this->assertEquals($datetime->format('m'),   $date->month);
        $this->assertEquals($datetime->format('d'),   $date->day);
        $this->assertEquals($datetime->format('H'),   $date->hour);
        $this->assertEquals($datetime->format('i'),   $date->minute);
        $this->assertEquals($datetime->format('s'),   $date->second);
        $this->assertEquals('Halifax',                $date->tz['id']);
        $this->assertFalse(isset($data->tz['offset']));
        $this->assertFalse(isset($data->tz['longname']));
        $this->assertFalse(isset($data->tz['shortname']));
        $this->assertFalse(isset($data->tz['dstlongname']));
        $this->assertFalse(isset($data->tz['dstshortname']));
        $this->assertFalse(isset($data->tz['hasdst']));
    }

    /**
     * Tests compare function when days are greater and don't convert timezone
     */
    public function testCompareDayGreaterNoConvertTz()
    {
        $date1 = new w2p_Utilities_Date('2010-08-07 00:00:00');
        $date2 = new w2p_Utilities_Date('2010-08-06 00:00:00');

        $this->assertEquals(1, $date1->compare($date1, $date2));
    }

    /**
     * Tests compare function when days are the same, hours are greater
     * and don't convert timezone
     */
    public function testCompareHourGreaterNoConvertTz()
    {
        $date1 = new w2p_Utilities_Date('2010-08-07 02:00:00');
        $date2 = new w2p_Utilities_Date('2010-08-07 01:00:00');

        $this->assertEquals(1, $date1->compare($date1, $date2));
    }

    /**
     * Tests compare function when days and hours are the same, minutes are
     * greater and don't convert timezone
     */
    public function testCompareMinuteGreaterNotConvertTz()
    {
        $date1 = new w2p_Utilities_Date('2010-08-07 01:01:00');
        $date2 = new w2p_Utilities_Date('2010-08-07 01:00:00');

        $this->assertEquals(1, $date1->compare($date1, $date2));
    }

    /**
     * Tests compare function when days, hours and minutes are the same,
     * seconds are greater and don't convert timezone
     */
    public function testCompareSecondGreaterNoConvertTz()
    {
        $date1 = new w2p_Utilities_Date('2010-08-07 01:01:01');
        $date2 = new w2p_Utilities_Date('2010-08-07 01:01:00');

        $this->assertEquals(1, $date1->compare($date1, $date2));
    }

    /**
     * Tests compare function when days are lesser and don't convert timezone
     */
    public function testCompareDayLesserNoConvertTz()
    {
        $date1 = new w2p_Utilities_Date('2010-08-06 00:00:00');
        $date2 = new w2p_Utilities_Date('2010-08-07 00:00:00');

        $this->assertEquals(-1, $date1->compare($date1, $date2));
    }

    /**
     * Tests compare function when days are the same, hours are lesser
     * and don't convert timezone
     */
    public function testCompareHourLesserNoConvertTz()
    {
        $date1 = new w2p_Utilities_Date('2010-08-07 01:00:00');
        $date2 = new w2p_Utilities_Date('2010-08-07 02:00:00');

        $this->assertEquals(-1, $date1->compare($date1, $date2));
    }

    /**
     * Tests compare function when days and hours are the same, minutes are
     * lesser and don't convert timezone
     */
    public function testCompareMinuteLesserNotConvertTz()
    {
        $date1 = new w2p_Utilities_Date('2010-08-07 01:00:00');
        $date2 = new w2p_Utilities_Date('2010-08-07 01:01:00');

        $this->assertEquals(-1, $date1->compare($date1, $date2));
    }

    /**
     * Tests compare function when days, hours and minutes are the same,
     * seconds are lesser and don't convert timezone
     */
    public function testCompareSecondLesserNoConvertTz()
    {
        $date1 = new w2p_Utilities_Date('2010-08-07 01:01:00');
        $date2 = new w2p_Utilities_Date('2010-08-07 01:01:01');

        $this->assertEquals(-1, $date1->compare($date1, $date2));
    }

    /**
     * Tests compare function when both dates are equal, don't convert timezones
     */
    public function testCompareEqualNoConvertTz()
    {
        $date1 = new w2p_Utilities_Date('2010-08-07 00:00:00');
        $date2 = new w2p_Utilities_Date('2010-08-07 00:00:00');

       $this->assertEquals(0, $date1->compare($date1, $date2));
    }

    /**
     * Tests compare function when days are greater and convert timezone
     */
    public function testCompareDayGreaterConvertTz()
    {
        $date1 = new w2p_Utilities_Date('2010-08-07 00:00:00', 'America/Halifax');
        $date2 = new w2p_Utilities_Date('2010-08-06 00:00:00', 'America/Chicago');

        $this->assertEquals(1, $date1->compare($date1, $date2, true));
    }

    /**
     * Tests compare function when days are the same, hours are greater
     * and convert timezone
     */
    public function testCompareHourGreaterConvertTz()
    {
        $date1 = new w2p_Utilities_Date('2010-08-07 00:00:00', 'America/Halifax');
        $date2 = new w2p_Utilities_Date('2010-08-06 21:00:00', 'America/Chicago');

        $this->assertEquals(1, $date1->compare($date1, $date2, true));
    }

    /**
     * Tests compare function when days and hours are the same, minutes are
     * greater and convert timezone
     */
    public function testCompareMinuteGreaterConvertTz()
    {
        $date1 = new w2p_Utilities_Date('2010-08-07 01:01:00', 'America/Halifax');
        $date2 = new w2p_Utilities_Date('2010-08-06 23:00:00', 'America/Chicago');

        $this->assertEquals(1, $date1->compare($date1, $date2, true));
    }

    /**
     * Tests compare function when days, hours and minutes are the same,
     * seconds are greater and convert timezone
     */
    public function testCompareSecondGreaterConvertTz()
    {
        $date1 = new w2p_Utilities_Date('2010-08-07 01:01:01');
        $date2 = new w2p_Utilities_Date('2010-08-06 23:01:00');

        $this->assertEquals(1, $date1->compare($date1, $date2, true));
    }

    /**
     * Tests compare function when days are lesser and convert timezone
     */
    public function testCompareDayLesserConvertTz()
    {
        $date1 = new w2p_Utilities_Date('2010-08-06 00:00:00', 'America/Halifax');
        $date2 = new w2p_Utilities_Date('2010-08-07 00:00:00', 'America/Chicago');

        $this->assertEquals(-1, $date1->compare($date1, $date2, true));
    }

    /**
     * Tests compare function when days are the same, hours are lesser
     * and convert timezone
     */
    public function testCompareHourLesserConvertTz()
    {
        $date1 = new w2p_Utilities_Date('2010-08-07 01:00:00', 'America/Halifax');
        $date2 = new w2p_Utilities_Date('2010-08-07 02:00:00', 'America/Chicago');

        $this->assertEquals(-1, $date1->compare($date1, $date2, true));
    }

    /**
     * Tests compare function when days and hours are the same, minutes are
     * lesser and convert timezone
     */
    public function testCompareMinuteLesserConvertTz()
    {
        $date1 = new w2p_Utilities_Date('2010-08-07 01:00:00', 'America/Halifax');
        $date2 = new w2p_Utilities_Date('2010-08-06 23:01:00', 'America/Chicago');

        $this->assertEquals(-1, $date1->compare($date1, $date2, true));
    }

    /**
     * Tests compare function when days, hours and minutes are the same,
     * seconds are lesser and convert timezone
     */
    public function testCompareSecondLesserConvertTz()
    {
        $date1 = new w2p_Utilities_Date('2010-08-07 01:01:00', 'America/Halifax');
        $date2 = new w2p_Utilities_Date('2010-08-06 23:01:01', 'America/Chicago');

        $this->assertEquals(-1, $date1->compare($date1, $date2, true));
    }

    /**
     * Tests compare function when both dates are equal, convert timezones
     */
    public function testCompareEqualConvertTz()
    {
        $date1 = new w2p_Utilities_Date('2010-08-07 00:00:00', 'America/Halifax');
        $date2 = new w2p_Utilities_Date('2010-08-06 22:00:00', 'America/Chicago');

        $this->assertEquals(0, $date1->compare($date1, $date2, true));
    }

    /**
     * Tests addDays function with a full positive day
     */
    public function testAddDaysPositiveFullDay()
    {
        $date = new w2p_Utilities_Date('2010-08-08 00:00:00');
        $date->addDays(3);

        $this->assertEquals('2010-08-11 00:00:00', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests addDays function with a full negative day
     */
    public function testAddDaysNegativeFullDay()
    {
        $date = new w2p_Utilities_Date('2010-08-08 00:00:00');
        $date->addDays(-3);

        $this->assertEquals('2010-08-05 00:00:00', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests addDays function with partial positive day
     */
    public function testAddDaysPositivePartialDay()
    {
        $date = new w2p_Utilities_Date('2010-08-08 00:00:00');
        $date->addDays(2.5);

        $this->assertEquals('2010-08-10 12:00:00', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Test addDays function with partial negative day
     */
    public function testAddDaysNegativePartialDay()
    {
        $date = new w2p_Utilities_Date('2010-08-08 00:00:00');
        $date->addDays(-2.5);

        $this->assertEquals('2010-08-05 12:00:00', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests addDays function with partial positive day spanning over midnight
     */
    public function testAddDaysPostivePartialDayAcrossDay()
    {
        $date = new w2p_Utilities_Date('2010-08-08 14:00:00');
        $date->addDays(2.5);

        $this->assertEquals('2010-08-11 02:00:00', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests addDays function when the days being added spans the end of a month
     */
    public function testAddDaysAcrossMonth()
    {
        $date = new w2p_Utilities_Date('2010-08-31 00:00:00');
        $date->addDays(2);

        $this->assertEquals('2010-09-02 00:00:00', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests addDays function when the days being added spans the end of a year
     */
    public function testAddDaysAcrossYear()
    {
        $date = new w2p_Utilities_Date('2010-12-31 00:00:00');
        $date->addDays(2);

        $this->assertEquals('2011-01-02 00:00:00', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests addMonths function with a positive full month
     */
    public function testAddMonthsPositiveFullMonth()
    {
        $date = new w2p_Utilities_Date('2010-08-08 00:00:00');
        $date->addMonths(2);

        $this->assertEquals('2010-10-08 00:00:00', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests addMonths function with a negative full month
     */
    public function testAddMonthsNegativeFullMonth()
    {
        $date = new w2p_Utilities_Date('2010-08-08 00:00:00');
        $date->addMonths(-2);

        $this->assertEquals('2010-06-08 00:00:00', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests addMonths function with a positive partial month
     */
    public function testAddMonthsPositivePartialMonth()
    {
        $date = new w2p_Utilities_Date('2010-08-08 00:00:00');
        $date->addMonths(2.5);

        $this->assertEquals('2010-10-08 00:00:00', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests addMonths function with a negative partial month
     */
    public function testAddMonthsNegativePartialMonth()
    {
        $date = new w2p_Utilities_Date('2010-08-08 00:00:00');
        $date->addMonths(-2.5);

        $this->assertEquals('2010-06-08 00:00:00', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests addMonths when the number of months spans a year
     */
    public function testAddMonthsPositiveAcrossYear()
    {
        $date = new w2p_Utilities_Date('2010-12-01 00:00:00');
        $date->addMonths(1);

        $this->assertEquals('2011-01-01 00:00:00', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests addMonths when the number of months spans a year
     */
    public function testAddMonthsNegativeAcrossYear()
    {
        $date = new w2p_Utilities_Date('2010-01-01 00:00:00');
        $date->addMonths(-1);

        $this->assertEquals('2009-12-01 00:00:00', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests dateDiff when not passing an object
     */
    public function testDateDiffNotObject()
    {
        $date = new w2p_Utilities_Date('2010-08-11 00:00:00');

        $this->assertFalse($date->dateDiff(1));
    }

    /**
     * Tests dateDiff when the date being compared against is in the future and
     * is a full day
     */
    public function testDateDiffFutureFullDay()
    {
       $date        = new w2p_Utilities_Date('2010-08-11 00:00:00');
       $date_diff   = $date->dateDiff(new w2p_Utilities_Date('2010-08-13 00:00:00'));

       $this->assertEquals(2, $date_diff);
    }

    /**
     * Tests dateDiff when the date being compared against is in the past and
     * is a full day
     */
    public function testDateDiffPastFullDay()
    {
        $date       = new w2p_Utilities_Date('2010-08-11 00:00:00');
        $date_diff  = $date->dateDiff(new w2p_Utilities_Date('2010-08-07 00:00:00'));

        $this->assertEquals(4, $date_diff);
    }

    /**
     * Tests dateDiff when teh date being compared against is in the future and
     * is a partial day
     */
    public function testDateDiffFuturePartialDay()
    {
        $date       = new w2p_Utilities_Date('2010-08-11 00:00:00');
        $date_diff  = $date->dateDiff(new w2p_Utilities_Date('2010-08-13 12:00:00'));

        $this->assertEquals(2, $date_diff);
    }

    /**
     * Tests dateDiff when the date being compared against is in the past and is
     * a partial day
     */
    public function testDateDiffPastPartialDay()
    {
        $date       = new w2p_Utilities_Date('2010-08-11 00:00:00');
        $date_diff  = $date->dateDiff(new w2p_Utilities_Date('2010-08-07 06:00:00'));

        $this->assertEquals(4, $date_diff);
    }

    /**
     * Tests setTime when hour is set and minute and second are not
     */
    public function testSetTimeHourNoMinuteNoSecond()
    {
        $date = new w2p_Utilities_Date('2010-08-11 00:00:00');
        $date->setTime(12);

        $this->assertEquals('2010-08-11 12:00:00', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests setTime when hour and minute is set and second is not
     */
    public function testSetTimeHourMinuteNoSecond()
    {
        $date = new w2p_Utilities_Date('2010-08-11 00:00:00');
        $date->setTime(12, 12);

        $this->assertEquals('2010-08-11 12:12:00', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests setTime when hour, minute and second are set
     */
    public function testSetTimeHourMinuteSecond()
    {
        $date = new w2p_Utilities_Date('2010-08-11 00:00:00');
        $date->setTime(12, 12, 12);

        $this->assertEquals('2010-08-11 12:12:12', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests setTime with invalid hour
     */
    public function testSetTimeInvalidHour()
    {
        $date = new w2p_Utilities_Date('2010-08-11 00:00:00');
        $date->setTime(25);

        $this->assertEquals('2010-08-11 00:00:00', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests setTime with invalid minute
     */
    public function testSetTimeInvalidMinute()
    {
        $date = new w2p_Utilities_Date('2010-08-11 00:00:00');
        $date->setTime(12, 61);

        $this->assertEquals('2010-08-11 12:00:00', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests setTime with invalid second
     */
    public function testSetTimeInvalidSecond()
    {
        $date = new w2p_Utilities_Date('2010-08-11 00:00:00');
        $date->setTime(12, 12, 61);

        $this->assertEquals('2010-08-11 12:12:00', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests isWorkingDay with a proper working day
     */
    public function testIsWorkingDayYes()
    {
        $date = new w2p_Utilities_Date('2010-08-11 00:00:00');

        $this->assertTrue($date->isWorkingDay());
    }

    /**
     * Tests isWorkingDay with a non working day
     */
    public function testIsWorkingDayNo()
    {
        $date = new w2p_Utilities_Date('2010-08-08 00:00:00');

        $this->assertFalse($date->isWorkingDay());
    }

    /**
     * Tests isWorkingDay with a proper working day, and cal_working_days
     * is null
     */
    public function testIsWorkingDayNullWorkingDaysYes()
    {
        global $w2Pconfig;

        $w2Pconfig['cal_working_days']  = null;

        $date = new w2p_Utilities_Date('2010-08-10 00:00:00');

        $this->assertTrue($date->isWorkingDay());
    }

    /**
     * Tests isWorkingDay with a non working day, and call_working_days
     * is null
     */
    public function testIsWorkingDayNullWorkingDaysNo()
    {
        global $w2Pconfig;

        $w2Pconfig['cal_working_days']  = null;

        $date = new w2p_Utilities_Date('2010-08-07 00:00:00');

        $this->assertFalse($date->isWorkingDay());
    }

    /**
     * Tests calcDuration with positive change on same day
     */
    public function testCalcDurationIntraDayPositive()
    {
        $date = new w2p_Utilities_Date('2010-09-03 11:00:00');

        $this->assertEquals(1, $date->calcDuration(new w2p_Utilities_Date('2010-09-03 12:00:00')));
    }

    /**
     * Tests calcDuration with positive change across a day
     */
    public function testCalcDurationAcrossDayPostive()
    {
        $date = new w2p_Utilities_Date('2010-09-02 16:00:00');

        $this->assertEquals(2, $date->calcDuration(new w2p_Utilities_Date('2010-09-03 10:00:00')));
    }

    /**
     * Tests calcDuration with positive change across multiple days
     */
    public function testCalcDurationAcrossMultipleDaysPositive()
    {
       $date = new w2p_Utilities_Date('2010-09-01 15:00:00');

       $this->assertEquals(11, $date->calcDuration(new w2p_Utilities_Date('2010-09-03 10:00:00')));
    }

    /**
     * Tests calcDuration with positive change across non-working days
     */
    public function testCalcDurationAcrossNonWorkingDaysPositive()
    {
        $date = new w2p_Utilities_Date('2010-09-03 15:00:00');

        $this->assertEquals(3, $date->calcDuration(new w2p_Utilities_Date('2010-09-06 10:00:00')));
    }

    /**
     * Tests calcDuration with negative change on same day
     */
    public function testCalcDurationIntraDayNegative()
    {
        $date = new w2p_Utilities_Date('2010-09-03 12:00:00');

        $this->assertEquals(-1, $date->calcDuration(new w2p_Utilities_Date('2010-09-03 11:00:00')));
    }

    /**
     * Tests calcDuration with negative change across a day
     */
    public function testCalcDurationAcrossDayNegative()
    {
        $date = new w2p_Utilities_Date('2010-09-03 10:00:00');

        $this->assertEquals(-2, $date->calcDuration(new w2p_Utilities_Date('2010-09-02 16:00:00')));
    }

    /**
     * Tests getAMPM when it is AM
     */
    public function testGetAMPMAM()
    {
        $date = new w2p_Utilities_Date('2010-08-19 10:00:00');

        $this->assertEquals('am', $date->getAMPM());
    }

    /**
     * Tests getAMPM when it is PM
     */
    public function testGetAMPMPM()
    {
        $date = new w2p_Utilities_Date('2010-08-19 13:00:00');

        $this->assertEquals('pm', $date->getAMPM());
    }

    /**
     * Tests next_working_day when not a working day, and not preserving
     * hours
     */
    public function testNextWorkingDayNotWorkingDayNoPreserveHours()
    {
        $date = new w2p_Utilities_Date('2010-08-07 00:00:00');
        $date->next_working_day();

        $this->assertEquals('2010-08-09 09:00:00', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests next_working_day when not a working day and preserving hours
     */
    public function testNextWorkingDayNotWorkingDayPreserveHours()
    {
        $date = new w2p_Utilities_Date('2010-08-07 10:00:00');
        $date->next_working_day(true);

        $this->assertEquals('2010-08-09 10:00:00', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests next_working_day when its past end of working day and not
     * preserving hours
     */
    public function testNextWorkingDayPastEndOfDayNoPreserveHours()
    {
        $date = new w2p_Utilities_Date('2010-08-24 18:00:00');
        $date->next_working_day();

        $this->assertEquals('2010-08-25 09:00:00', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests next_working_day when its past end of working day and preserving
     * hours
     */
    public function testNextWorkingDayPastEndOfDayPreserveHours()
    {
        $date = new w2p_Utilities_Date('2010-08-24 18:00:00');
        $date->next_working_day(true);

        $this->assertEquals('2010-08-25 18:00:00', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests next_working_day when its exactly the end of working day and not
     * preserving hours
     */
    public function testNextWorkingDayEndOfDayNoPreserveHours()
    {
        $date = new w2p_Utilities_Date('2010-08-24 17:00:00');
        $date->next_working_day();

        $this->assertEquals('2010-08-25 09:00:00', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests next_working_day when it is a working day
     */
    public function testNextWorkingDayIsWorkingDay()
    {
        $date = new w2p_Utilities_Date('2010-08-24 13:00:00');
        $date->next_working_day();

        $this->assertEquals('2010-08-24 13:00:00', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests prev_working_day when not a working day and not preserving hours
     */
    public function testPrevWorkingDayNotWorkingDayNoPreserveHours()
    {
        $date = new w2p_Utilities_Date('2010-08-07 00:00:00');
        $date->prev_working_day();

        $this->assertEquals('2010-08-06 17:00:00', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests prev_working_day when not a working day and preserving hours
     */
    public function testPrevWorkingDayNotWorkingDayPreserveHours()
    {
        $date = new w2p_Utilities_Date('2010-08-07 00:00:00');
        $date->prev_working_day(true);

        $this->assertEquals('2010-08-06 00:00:00', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests prev_working_day when before start of day and not preserving hours
     */
    public function testPrevWorkingDayBeforeStartOfDayNoPreserveHours()
    {
        $date = new w2p_Utilities_Date('2010-08-07 00:00:00');
        $date->prev_working_day();

        $this->assertEquals('2010-08-06 17:00:00', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests prev_working_day when before start of day and preserving hours
     */
    public function testPrevWorkingDayBeforeStartOfDayPreserveHours()
    {
        $date = new w2p_Utilities_Date('2010-08-07 00:00:00');
        $date->prev_working_day(true);

        $this->assertEquals('2010-08-06 00:00:00', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests prev_working_day when it is the start of day and not preserving hours
     */
    public function testPrevWorkingDayStartOfDayNoPreserveHours()
    {
        $date = new w2p_Utilities_Date('2010-08-07 09:00:00');
        $date->prev_working_day();

        $this->assertEquals('2010-08-06 17:00:00', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests prev_working_day when it is the start of day and preserving hours
     */
    public function testPrevWorkingDayStartOfDayPreserveHours()
    {
        $date = new w2p_Utilities_Date('2010-08-07 09:00:00');
        $date->prev_working_day(true);

        $this->assertEquals('2010-08-06 09:00:00', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests prev_working_day when it is a working day
     */
    public function testPrevWorkingDayIsWorkingDay()
    {
        $date = new w2p_Utilities_Date('2010-08-24 13:00:00');
        $date->prev_working_day();

        $this->assertEquals('2010-08-24 13:00:00', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests addDuration with positive full days
     */
    public function testAddDurationPositiveDurationFullDayDuration()
    {
        $date = new w2p_Utilities_Date('2010-08-30 10:00:00');
        $date->addDuration(1, 24);

        $this->assertEquals('2010-08-31 10:00:00', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests addDuration with positive full days across a month
     */
    public function testAddDurationPositiverDurationFullDayDurationAcrossMonth()
    {
        $date = new w2p_Utilities_Date('2010-08-31 10:00:00');
        $date->addDuration(1, 24);

        $this->assertEquals('2010-09-01 10:00:00', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests addDuration with negative full days
     */
    public function testAddDurationNegativeDurationFullDayDuration()
    {
        $date = new w2p_Utilities_Date('2010-08-31 10:00:00');
        $date->addDuration(-1, 24);

        $this->assertEquals('2010-08-30 10:00:00', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests addDuration with negative full days across a month
     */
    public function testAddDurationNegativeDurationFullDayDurationAcrossMonth()
    {
        $date = new w2p_Utilities_Date('2010-09-01 10:00:00');
        $date->addDuration(-1, 24);

        $this->assertEquals('2010-08-31 10:00:00', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests addDuration with positive hour
     */
    public function testAddDurationPositiveHourDuration()
    {
        $date = new w2p_Utilities_Date('2010-08-30 10:00:00');
        $date->addDuration(1);

        $this->assertEquals('2010-08-30 11:00:00', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests addDuration with positive hour across a day
     */
    public function testAddDurationPositiveHourDurationAcrossDay()
    {
        $date = new w2p_Utilities_Date('2010-08-30 16:00:00');
        $date->addDuration(2);

        $this->assertEquals('2010-08-31 10:00:00', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests addDuration with positive hour on non working day
     */
    public function testAddDurationPositiveHourDurationNonWorkingDay()
    {
        $date = new w2p_Utilities_Date('2010-08-28 10:00:00');
        $date->addDuration(1);

        $this->assertEquals('2010-08-30 10:00:00', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests addDuration with positive hour when adding mulitple days
     */
    public function testAddDurationPositiveHourDurationMultipleDays()
    {
        $date = new w2p_Utilities_Date('2010-08-30 10:00:00');
        $date->addDuration(17);

        $this->assertEquals('2010-09-01 11:00:00', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests add duration with positive hour when it's friday afternoon(next
     * day is not a working day)
     */
    public function testAddDurationPositiveHourDurationFridayAfternoon()
    {
        $date = new w2p_Utilities_Date('2010-08-27 16:00:00');
        $date->addDuration(2);

        $this->assertEquals('2010-08-30 10:00:00', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests addDuration with negative hour
     */
    public function testAddDurationNegativeHourDuration()
    {
        $date = new w2p_Utilities_Date('2010-08-30 10:00:00');
        $date->addDuration(-1);

        $this->assertEquals('2010-08-30 09:00:00', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests addDuration with negative hour across a day
     */
    public function testAddDurationNegativeHourDurationAcrossDay()
    {
        $date = new w2p_Utilities_Date('2010-08-31 10:00:00');
        $date->addDuration(-2);

        $this->assertEquals('2010-08-30 16:00:00', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests addDuration with negative hour on non working day
     */
    public function testAddDurationNegativeHourDurationNonWorkingDay()
    {
        $date = new w2p_Utilities_Date('2010-08-28 10:00:00');
        $date->addDuration(-1);

        $this->assertEquals('2010-08-27 16:00:00', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests addDuration with negative hour spanning multiple days
     */
    public function testAddDurationNegativeHourDurationMultipleDays()
    {
        $date = new w2p_Utilities_Date('2010-09-01 11:00:00');
        $date->addDuration(-17);

        $this->assertEquals('2010-08-30 10:00:00', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests addDuration with negative hour when it's Monday morning (prev day
     * is not a working day)
     */
    public function testAddDurationNegativeHourDurationMondayMorning()
    {
        $date = new w2p_Utilities_Date('2010-08-30 10:00:00');
        $date->addDuration(-2);

        $this->assertEquals('2010-08-27 16:00:00', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests addDuration with an invalid duration type (valid are 1(hours), or
     * 24(days))
     */
    public function testAddDurationInvalidDurationType()
    {
        $date = new w2p_Utilities_Date('2010-08-30 10:00:00');
        $date->addDuration(1, 17);

        $this->assertEquals('2010-08-30 10:00:00', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests calcDuration with negative change across multiple days
     */
    public function testCalcDurationAcrossMultipleDaysNegative()
    {
        $date = new w2p_Utilities_Date('2010-09-03 10:00:00');

        $this->assertEquals(-11, $date->calcDuration(new w2p_Utilities_Date('2010-09-01 15:00:00')));
    }

    /**
     * Tests calcDuration with negative change across non-working days
     */
    public function testCalcDurationAcrossNonWorkingDaysNegative()
    {
        $date = new w2p_Utilities_Date('2010-09-06 10:00:00');

        $this->assertEquals(-3, $date->calcDuration(new w2p_Utilities_Date('2010-09-03 15:00:00')));
    }

    /**
     * Tests workingDaysInSpan on same day
     */
    public function testWorkingDaysInSpanSameDay()
    {
        $date = new w2p_Utilities_Date('2010-09-14 10:00:00');

        $this->assertEquals(1, $date->workingDaysInSpan(new w2p_Utilities_Date('2010-09-14 12:00:00')));
    }

    /**
     * Tests workingDaysInSpan with multiple positive days
     */
    public function testWorkingDaysInSpanMultiDaysPositive()
    {
        $date = new w2p_Utilities_Date('2010-09-14 10:00:00');

        $this->assertEquals(3, $date->workingDaysInSpan(new w2p_Utilities_Date('2010-09-16 12:00:00')));
    }

    /**
     * Tests workingDaysInSpan with multiple negative days
     */
    public function testWorkingDaysInSpanMultiDaysNegative()
    {
        $date = new w2p_Utilities_Date('2010-09-14 10:00:00');

        $this->assertEquals(2, $date->workingDaysInSpan(new w2p_Utilities_Date('2010-09-12 10:00:00')));
    }

    /**
     * Test workingDaysInSpan with multiple positive days including non
     * working days
     */
    public function testWorkingDaysInSpanMultiDaysPositiveWithNonWorking()
    {
        $date = new w2p_Utilities_Date('2010-09-14 10:00:00');

        $this->assertEquals(5, $date->workingDaysInSpan(new w2p_Utilities_Date('2010-09-20 10:00:00')));
    }

    /**
     * Test workingDaysInSpan with multiple negative days including non
     * working days
     */
    public function testWorkingDaysInSpanMultiDaysNegativeWithNonWorking()
    {
        $date = new w2p_Utilities_Date('2010-09-14 10:00:00');
        $this->assertEquals(3, $date->workingDaysInSpan(new w2p_Utilities_Date('2010-09-10 10:00:00')));
    }

    /**
     * Tests Duplicate
     */
    public function testDuplicate()
    {
        $date = new w2p_Utilities_Date('2010-09-14 10:00:00');
        $date2 = $date->duplicate();

        $this->assertEquals($date, $date2);
    }

    /**
     * Test Duplicate after changing one of the properties
     */
    public function testDuplicateDifferent()
    {
        $date = new w2p_Utilities_Date('2010-09-14 10:00:00');
        $date2 = $date->duplicate();

        $date->minute = 15;

        $this->assertNotEquals($date, $date2);
    }

    /**
     * Tests calcFinish when adding an hour on same day
     */
    public function testCalcFinishSameDayHours()
    {
        $date   = new w2p_Utilities_Date('2010-09-15 10:00:00');
        $finish = $date->calcFinish(2, 1);

        $this->assertEquals('2010-09-15 12:00:00', $finish->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests calcFinish when the minute is > 38 so should be rounded to 45
     */
    public function testCalcFinish45()
    {
        $date   = new w2p_Utilities_Date('2010-09-15 10:39:00');
        $finish = $date->calcFinish(1, 1);

        $this->assertEquals('2010-09-15 11:45:00', $finish->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests calcFinish when the minute is > 23 so should be rounded to 30
     */
    public function testCalcFinish30()
    {
        $date   = new w2p_Utilities_Date('2010-09-15 10:24:00');
        $finish = $date->calcFinish(1, 1);

        $this->assertEquals('2010-09-15 11:30:00', $finish->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests calcFinish when the minute is > 8 so should be rounded to 15
     */
    public function testCalcFinish15()
    {
        $date   = new w2p_Utilities_Date('2010-09-15 10:09:00');
        $finish = $date->calcFinish(1, 1);

        $this->assertEquals('2010-09-15 11:15:00', $finish->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests calcFinish when the minute is < 9 so should be rounded to 0
     */
    public function testCalcFinish00()
    {
        $date   = new w2p_Utilities_Date('2010-09-15 10:08:00');
        $finish = $date->calcFinish(1, 1);

        $this->assertEquals('2010-09-15 11:00:00', $finish->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests calcFinish on a non working day
     */
    public function testCalcFinishNonWorkingDay()
    {
        $date   = new w2p_Utilities_Date('2010-09-18 10:00:00');
        $finish = $date->calcFinish(1, 1);

        $this->assertEquals('2010-09-20 11:00:00', $finish->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests calcFinish across a Day
     */
    public function testCalcFinishAcrossDayHoursOnLastDay()
    {
        $date   = new w2p_Utilities_Date('2010-09-20 16:00:00');
        $finish = $date->calcFinish(2, 1);

        $this->assertEquals('2010-09-21 10:00:00', $finish->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Test calcFinish Across multiple days when it ends at end of day
     * (no hours to add to last day)
     */
    public function testCalcFinishAcrossMultipleDaysNoHoursLastDay()
    {
        $date   = new w2p_Utilities_Date('2010-09-20 16:00:00');
        $finish = $date->calcFinish(16, 1);

        $this->assertEquals('2010-09-22 16:00:00', $finish->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests adding a single day, with day duration, when the time is equal to
     * day start
     */
    public function testCalcFinishAddDayStartDayDuration()
    {
        $date   = new w2p_Utilities_Date('2010-09-20 09:00:00');
        $finish = $date->calcFinish(1, 24);

        $this->assertEquals('2010-09-20 17:00:00', $finish->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests calcFinish, with day duration
     */
    public function testCalcFinishAddDaysDayDuration()
    {
        $date   = new w2p_Utilities_Date('2010-09-20 10:00:00');
        $finish = $date->calcFinish(2, 24);

        $this->assertEquals('2010-09-22 10:00:00', $finish->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Test calcFinish with day duration across non working days
     */
    public function testCalcFinishAddDaysDayDurationAcrossNonWorkingDays()
    {
        $date   = new w2p_Utilities_Date('2010-09-17 10:00:00');
        $finish = $date->calcFinish(2, 24);

        $this->assertEquals('2010-09-21 10:00:00', $finish->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests converting between timezones
     */
	public function testConvertTZ()
	{
		$myDate1 = new w2p_Utilities_Date('', 'US/Eastern');
		$myDate2 = new w2p_Utilities_Date('', 'CST');
		$myDate2->convertTZ('EST');

		//This tweaks the test data in case the +1 is across the day change.
		$tmpHour = ($myDate1->hour+1 >=24) ? $myDate1->hour+1-24 : $myDate1->hour+1;
		$this->assertEquals($tmpHour, $myDate2->hour);
		$this->assertEquals($myDate1->minute, $myDate2->minute);

		$myDate2->convertTZ('PST');
		$tmpHour = ($myDate1->hour-2 < 0) ? $myDate1->hour-2+24 : $myDate1->hour-2;
		$this->assertEquals($tmpHour, $myDate2->hour);
    }

    /**
     * Tests setting the timezone of a date object
     */
    public function testSetTZ()
    {
        $date = new w2p_Utilities_Date('', 'US/Atlantic');
        $this->assertEquals(new w2p_Utilities_Date('', 'US/Atlantic'), $date);

        $date->setTZ('US/Eastern');
        $this->assertEquals(new w2p_Utilities_Date('', 'US/Eastern'), $date);
    }

    /**
     * Tests adding seconds
     */
    public function testAddSecondsPositive()
    {
        $date = new w2p_Utilities_Date('2010-09-21 09:00:00');
        $date->addSeconds(59);

        $this->assertEquals('2010-09-21 09:00:59', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests adding negative seconds
     */
    public function testAddSecondsNegative()
    {
        $date = new w2p_Utilities_Date('2010-09-21 09:00:00');
        $date->addSeconds(-59);

        $this->assertEquals('2010-09-21 08:59:01', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Test adding seconds across a minute
     */
    public function testAddSecondsAcrossMinute()
    {
        $date = new w2p_Utilities_Date('2010-09-21 09:00:00');
        $date->addSeconds(65);

        $this->assertEquals('2010-09-21 09:01:05', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests adding seconds across an hour
     */
    public function testAddSecondsAcrossHour()
    {
        $date = new w2p_Utilities_Date('2010-09-21 09:59:00');
        $date->addSeconds(65);

        $this->assertEquals('2010-09-21 10:00:05', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests adding seconds across a day
     */
    public function testAddSecondsAcrossDay()
    {
        $date = new w2p_Utilities_Date('2010-09-21 23:59:00');
        $date->addSeconds(65);

        $this->assertEquals('2010-09-22 00:00:05', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests adding seconds across a year
     */
    public function testAddSecondsAcrossYear()
    {
        $date = new w2p_Utilities_Date('2010-12-31 23:59:00');
        $date->addSeconds(65);

        $this->assertEquals('2011-01-01 00:00:05', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests after when the date is after
     */
    public function testAfterIsAfter()
    {
        $date1 = new w2p_Utilities_Date('2010-11-04 11:00:00');
        $date2 = new w2p_Utilities_Date('2010-11-04 10:00:00');

        $this->assertTrue($date1->after($date2));
    }

    /**
     * Tests after when the date is before
     */
    public function testAfterIsBefore()
    {
        $date1 = new w2p_Utilities_Date('2010-11-04 11:00:00');
        $date2 = new w2p_Utilities_Date('2010-11-04 12:00:00');

        $this->assertFalse($date1->after($date2));
    }

    /**
     * Tests after then the dates are equal
     */
    public function testAfterIsSame()
    {
        $date1 = new w2p_Utilities_Date('2010-11-04 11:00:00');
        $date2 = new w2p_Utilities_Date('2010-11-04 11:00:00');

        $this->assertFalse($date1->after($date2));
    }

    /**
     * Tests before when the date is before
     */
    public function testBeforeIsBefore()
    {
        $date1 = new w2p_Utilities_Date('2010-11-04 10:00:00');
        $date2 = new w2p_Utilities_Date('2010-11-04 11:00:00');

        $this->assertTrue($date1->before($date2));
    }

    /**
     * Tests before when the date is after
     */
    public function testBeforeIsAfter()
    {
        $date1 = new w2p_Utilities_Date('2010-11-04 11:00:00');
        $date2 = new w2p_Utilities_Date('2010-11-04 10:00:00');

        $this->assertFalse($date1->before($date2));
    }

    /**
     * Tests before when the dates are equal
     */
    public function testBeforeIsSame()
    {
        $date1 = new w2p_Utilities_Date('2010-11-04 11:00:00');
        $date2 = new w2p_Utilities_Date('2010-11-04 11:00:00');

        $this->assertFalse($date1->before($date2));
    }

    /**
     * Tests getDate with ISO format
     */
    public function testGetDateIso()
    {
        $date = new w2p_Utilities_Date('2010-11-05 11:00:00');

        $this->assertEquals('2010-11-05 11:00:00', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests getDate with TIMESTAMP format
     */
    public function testGetDateTimestamp()
    {
        $date = new w2p_Utilities_Date('2010-11-05 11:00:00');

        $this->assertEquals('20101105110000', $date->getDate(DATE_FORMAT_TIMESTAMP));
    }

    /**
     * Tests getDate with UNIXTIME format
     */
    public function testGetDateUnixtime()
    {
        $date = new w2p_Utilities_Date('2010-11-05 11:00:00');

        $this->assertEquals(1288954800, $date->getDate(DATE_FORMAT_UNIXTIME));
    }

    /**
     * Tests getDate with an ivalid format
     */
    public function testGetDateInvalidFormat()
    {
        $date = new w2p_Utilities_Date('2010-11-05 11:00:00');

        $this->assertNull($date->getDate(DATE_FORMAT_INVALID));
    }

    /**
     * Tests getDay
     */
    public function testGetDay()
    {
        $date = new w2p_Utilities_Date('2010-11-05 11:00:00');

        $this->assertEquals(5, $date->getDay());
    }

    /**
     * Tests getDay with invalid day, this *should* break
     */
    public function testGetDayInvalidDay()
    {
        $date = new w2p_Utilities_Date('2010-11-34 11:00:00');

        $this->assertEquals(34, $date->getDay());
    }

    /**
     * Tests getDaysInMonth
     */
    public function testGetDaysInMonth()
    {
        $date = new w2p_Utilities_Date('2010-11-05 11:00:00');

        $this->assertEquals(30, $date->getDaysInMonth());

        $date->setMonth(12);

        $this->assertEquals(31, $date->getDaysInMonth());
    }

    /**
     * Tests getHour
     */
    public function testGetHour()
    {
        $date = new w2p_Utilities_Date('2010-11-06 11:00:00');

        $this->assertEquals(11, $date->getHour());
    }

    /**
     * Test getHour when the hour is greater then 12
     */
    public function testGetHourGreaterThenTwelve()
    {
        $date = new w2p_Utilities_Date('2010-11-06 15:00:00');

        $this->assertEquals(15, $date->getHour());
    }

    /**
     * Tests getHour with an invalid Hour, this should break...
     */
    public function testGetHourInvalidHour()
    {
        $date = new w2p_Utilities_Date('2010-11-06 25:00:00');

        $this->assertEquals(25, $date->getHour());
    }

    /**
     * Tests getMinute
     */
    public function testGetMinute()
    {
        $date = new w2p_Utilities_Date('2010-11-06 11:21:00');
        $this->assertEquals(21, $date->getMinute());
    }

    /**
     * Tests getMinute with Invalid minutes, this should break...
     */
    public function testGetMinuteInvalidMinute()
    {
        $date = new w2p_Utilities_Date('2010-11-06 11:65:00');

        $this->assertEquals(65, $date->getMinute());
    }

    /**
     * Tests getMonth
     */
    public function testGetMonth()
    {
        $date = new w2p_Utilities_Date('2010-11-06 11:00:00');

        $this->assertEquals(11, $date->getMonth());
    }

    /**
     * Tests getMonth with an invalid month
     */
    public function testGetMonthInvalidMonth()
    {
        $date = new w2p_Utilities_Date('2010-14-06 11:00:00');

        $this->assertEquals(14, $date->getMonth());
    }

    /**
     * Tests getWeekOfYear
     */
    public function testGetWeekOfYear()
    {
        $date = new w2p_Utilities_Date('2010-11-06 11:00:00');

        $this->assertEquals(45, $date->getWeekOfYear());
    }

    /**
     * Tests getWeekOfYear with invalid date, this should break...
     */
    public function testGetWeekOfYearInvalidDate()
    {
        $date = new w2p_Utilities_Date('2010-14-11 11:00:00');

        $this->assertEquals(2, $date->getWeekOfYear());
    }

    /**
     * Tests getYear
     */
    public function testGetYear()
    {
        $date = new w2p_Utilities_Date('2010-11-06 11:00:00');

        $this->assertEquals(2010, $date->getYear());
    }

    /**
     * Tests getYear with a year before the epoch
     */
    public function testGetYearBeforeEpoch()
    {
        $date = new w2p_Utilities_Date('1950-11-06 11:00:00');

        $this->assertEquals(1950, $date->getYear());
    }

    /**
     * Tests SetDay
     */
    public function testSetDay()
    {
        $date = new w2p_Utilities_Date('2010-11-07 11:00:00');
        $date->setDay(12);

        $this->assertEquals('2010-11-12 11:00:00', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests setDay with a 0 day
     */
    public function testSetDayZero()
    {
        $date = new w2p_Utilities_Date('2010-11-07 11:00:00');
        $date->setDay(0);

        $this->assertEquals('2010-11-01 11:00:00', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests setDay with a negative day
     */
    public function testSetDayNegative()
    {
        $date = new w2p_Utilities_Date('2010-11-07 11:00:00');
        $date->setDay(-1);

        $this->assertEquals('2010-11-01 11:00:00', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests setDay with a day that is too high
     */
    public function testSetDayHigh()
    {
        $date = new w2p_Utilities_Date('2010-11-07 11:00:00');
        $date->setDay(32);

        $this->assertEquals('2010-11-01 11:00:00', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Test setMonth
     */
    public function testSetMonth()
    {
        $date = new w2p_Utilities_Date('2010-11-07 11:00:00');
        $date->setMonth(9);

        $this->assertEquals('2010-09-07 11:00:00', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests setMonth with a zero month
     */
    public function testSetMonthZero()
    {
        $date = new w2p_Utilities_Date('2010-11-07 11:00:00');
        $date->setMonth(0);

        $this->assertEquals('2010-01-07 11:00:00', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests setMonth with a negative month
     */
    public function testSetMonthNegative()
    {
        $date = new w2p_Utilities_Date('2010-11-07 11:00:00');
        $date->setMonth(-2);

        $this->assertEquals('2010-01-07 11:00:00', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests setMonth with month that is above 12
     */
    public function testSetMonthHigh()
    {
        $date = new w2p_Utilities_Date('2010-11-07 11:00:00');
        $date->setMonth(14);

        $this->assertEquals('2010-01-07 11:00:00', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests subtractSeconds
     */
    public function testSubtractSeconds()
    {
        $date = new w2p_Utilities_Date('2010-11-07 11:00:00');
        $date->subtractSeconds(45);

        $this->assertEquals('2010-11-07 10:59:15', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests subtractSeconds with a negative number, this appears to NOT work
     * as expected
     */
    public function testSubtractSecondsNegative()
    {
        $date = new w2p_Utilities_Date('2010-11-07 11:00:00');
        $date->subtractSeconds(-45);

        $this->assertEquals('2010-11-07 11:00:00', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests subtractSeconds when crossing over a minute
     */
    public function testSubtractSecondsOverAMinute()
    {
        $date = new w2p_Utilities_Date('2010-11-07 11:00:00');
        $date->subtractSeconds(75);

        $this->assertEquals('2010-11-07 10:58:45', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests subtractSeconds when crossing over a day
     */
    public function testSubtractSecondsAcrossDay()
    {
        $date = new w2p_Utilities_Date('2010-11-07 00:00:10');
        $date->subtractSeconds(11);

        $this->assertEquals('2010-11-06 23:59:59', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests subtractSeconds when crossing over a year
     */
    public function testSubtractSecondsAcrossYear()
    {
        $date = new w2p_Utilities_Date('2011-01-01 00:00:10');
        $date->subtractSeconds(11);

        $this->assertEquals('2010-12-31 23:59:59', $date->getDate(DATE_FORMAT_ISO));
    }
}
