<?php
/**
 * Class for testing AppUI functionality
 *
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to Clear BSD License. Please see the
 *   LICENSE file in root of site for further details
 *
 * @author      D. Keith Casey, Jr.<caseydk@users.sourceforge.net>
 * @category    w2p_Core_CAppUI
 * @package     web2project
 * @subpackage  unit_tests
 * @license     Clear BSD
 * @link        http://www.web2project.net
 */

class w2p_Core_CAppUITest extends CommonSetup
{
    public function setUp()
    {
        parent::setUp();

        $this->_AppUI->user_prefs['TIMEZONE'] = 'CST';
    }
    /**
     * Tests the attributes of a new AppUI object
     */
    public function testNewAppUIAttributes()
    {
        global $w2Pconfig;

        $AppUI = $this->_AppUI;

        $this->assertInstanceOf('w2p_Core_CAppUI',          $AppUI);
        $this->assertObjectHasAttribute('state',            $AppUI);
        $this->assertObjectHasAttribute('user_id',          $AppUI);
        $this->assertObjectHasAttribute('user_first_name',  $AppUI);
        $this->assertObjectHasAttribute('user_last_name',   $AppUI);
        $this->assertObjectHasAttribute('user_company',     $AppUI);
        $this->assertObjectHasAttribute('user_department',  $AppUI);
        $this->assertObjectHasAttribute('user_email',       $AppUI);
        $this->assertObjectHasAttribute('user_type',        $AppUI);
        $this->assertObjectHasAttribute('user_prefs',       $AppUI);
        $this->assertObjectHasAttribute('day_selected',     $AppUI);
        $this->assertObjectHasAttribute('user_locale',      $AppUI);
        $this->assertObjectHasAttribute('user_lang',        $AppUI);
        $this->assertObjectHasAttribute('base_locale',      $AppUI);
        $this->assertObjectHasAttribute('msg',              $AppUI);
        $this->assertObjectHasAttribute('msgNo',            $AppUI);
        $this->assertObjectHasAttribute('defaultRedirect',  $AppUI);
        $this->assertObjectHasAttribute('cfg',              $AppUI);
        $this->assertObjectHasAttribute('version_major',    $AppUI);
        $this->assertObjectHasAttribute('version_minor',    $AppUI);
        $this->assertObjectHasAttribute('version_patch',    $AppUI);
        $this->assertObjectHasAttribute('version_string',   $AppUI);
        $this->assertObjectHasAttribute('last_insert_id',   $AppUI);
        $this->assertObjectHasAttribute('user_style',       $AppUI);
        $this->assertObjectHasAttribute('long_date_format', $AppUI);
        $this->assertObjectHasAttribute('objStore',         $AppUI);
        $this->assertObjectHasAttribute('project_id',       $AppUI);
    }

    /**
     * Tests the value of the attributes of a new AppUI object
     */
    public function testNewAppUIAttributeValues()
    {
        global $w2Pconfig;

        $AppUI = new w2p_Core_CAppUI();

        $user_lang = array (
            0 => 'en_US.ISO8859-15',
            1 => 'en_US',
            2 => 'en_US',
            3 => 'en',
        );

        $cfg = array(
            'locale_warn' => '',
        );

        $this->assertEquals(array(),    $AppUI->state);
        $this->assertEquals(-1,         $AppUI->user_id);
        $this->assertEquals('',         $AppUI->user_first_name);
        $this->assertEquals('',         $AppUI->user_last_name);
        $this->assertEquals(0,          $AppUI->user_company);
        $this->assertEquals(0,          $AppUI->user_department);
        $this->assertEquals('',         $AppUI->user_email);
        $this->assertEquals(0,          $AppUI->user_type);
        $this->assertEquals(array(),    $AppUI->user_prefs);
        $this->assertEquals('',         $AppUI->day_selected);
        $this->assertEquals('en',       $AppUI->user_locale);
        $this->assertEquals($user_lang, $AppUI->user_lang);
        $this->assertEquals('en',       $AppUI->base_locale);
        $this->assertEquals('',         $AppUI->msg);
        $this->assertEquals('',         $AppUI->msgNo);
        $this->assertEquals('',         $AppUI->defaultRedirect);
        $this->assertEquals($cfg,       $AppUI->cfg);
        $this->assertEquals('',         $AppUI->version_major);
        $this->assertEquals('',         $AppUI->version_minor);
        $this->assertEquals('',         $AppUI->version_patch);
        $this->assertEquals('',         $AppUI->version_string);
        $this->assertEquals('',         $AppUI->last_insert_id);
        $this->assertEquals('',         $AppUI->user_style);
        $this->assertEquals(0,          $AppUI->user_is_admin);
        $this->assertEquals('',         $AppUI->long_date_format);
        $this->assertEquals('',         $this->readAttribute($AppUI, 'objStore'));
        $this->assertEquals(0,          $AppUI->project_id);
    }


    /**
     * Test the translation function
     */
    public function test__()
    {
        $AppUI = $this->_AppUI;
        global $w2Pconfig;

        $w2Pconfig['locale_warn'] = false;
        $this->assertEquals('Company',        $AppUI->__('Company'));
        $this->assertEquals('NoGonnaBeThere', $AppUI->__('NoGonnaBeThere'));

        /* Turn on 'untranslatable' warning */
        $w2Pconfig['locale_warn'] = true;
        $this->assertEquals('Projects^', $AppUI->__('Projects'));
        $this->assertEquals('Add File^', $AppUI->__('Add File'));
    }

    /**
     * Tests getting a preference
     */
    public function testGetPref()
    {
        $AppUI = $this->_AppUI;

        $this->assertEquals('en', $AppUI->getPref('LOCALE'));
        $this->assertEquals('',   $AppUI->getPref('NotGonnaBeThere'));
    }

    /**
     * Tests setting a preference
     */
    public function testSetPref()
    {
        $AppUI = $this->_AppUI;

        $this->assertEquals('en',     $AppUI->getPref('LOCALE'));
        $AppUI->setPref('AddingThis', 'Monkey');
        $this->assertEquals('Monkey', $AppUI->getPref('AddingThis'));
    }

    /**
     * Test setting the global state
     */
    public function testSetState()
    {
        $AppUI = $this->_AppUI;

        $AppUI->setState('testSetState',    'someValue');
        $this->assertEquals('someValue',    $AppUI->getState('testSetState'));
        $AppUI->setState('testSetState',    'anotherValue');
        $this->assertEquals('anotherValue', $AppUI->getState('testSetState'));
    }

    /**
     * Tests processing the tab state
     */
    public function testProcessTabState()
    {
        $AppUI = $this->_AppUI;

        $myArray = array('existingKey' => 13, 'existingKey2' => 42);

        $AppUI->processIntState('testProcessState', null,     'existingKey', 9);
        $this->assertEquals(9, $AppUI->getState('testProcessState'));

        $AppUI->processIntState('testProcessState', $myArray, 'existingKey', 9);
        $this->assertEquals(13, $AppUI->getState('testProcessState'));

        $AppUI->processIntState('testProcessNull', $myArray,  'missingKey',  14);
        $this->assertEquals(14, $AppUI->getState('testProcessNull'));

        $AppUI->processIntState('testProcessState', $myArray, 'missingKey',  79);
        $this->assertEquals(13, $AppUI->getState('testProcessState'));
    }

    /**
     * Tests saving the place(url)
     *
     * @expectedException PHPUnit_Framework_Error
     */
    public function testSavePlace()
    {
        $AppUI = $this->_AppUI;

        $_SERVER['QUERY_STRING'] = 'testUrl';
        $AppUI->savePlace();
        $this->assertEquals('testUrl', $AppUI->getPlace());

        $AppUI->savePlace('?m=projects&amp;a=view&amp;project_id=1');
        $this->assertEquals('?m=projects&amp;a=view&amp;project_id=1', $AppUI->getPlace());
    }

    /**
     * Tests reseting the place(url)
     *
     * @expectedException PHPUnit_Framework_Error
     */
    public function testResetPlace()
    {
        $AppUI = $this->_AppUI;

        $_SERVER['QUERY_STRING'] = 'testUrl';
        $AppUI->savePlace();
        $this->assertEquals('testUrl', $AppUI->getPlace());
        $AppUI->resetPlace();
        $this->assertEquals('', $AppUI->getPlace());
    }

    /**
     * Tests restoring an object from the global scope
     */
    public function testHoldRestoreObject()
    {
        $AppUI = $this->_AppUI;

      $this->assertNull($AppUI->restoreObject());
      $myArray = array('one' => 'something', 2 => 'another');
      $AppUI->holdObject($myArray);

      $result = $AppUI->restoreObject();
      $this->AssertEquals(2, count($result));
      $this->assertArrayHasKey('one', $result);
      $this->assertArrayHasKey(2, $result);
      $this->assertNull($AppUI->restoreObject());
    }

    /**
     * Tests setting a message
     */
    public function testSetMsg()
    {
        $AppUI = $this->_AppUI;
        global $w2Pconfig;

      $w2Pconfig['locale_warn'] = false;

      $msg = 'This is a test';
      $AppUI->setMsg($msg, 0, false);
      $this->AssertEquals($msg, $AppUI->msg);
      $AppUI->setMsg($msg, 0, true);
      $this->AssertEquals($msg.' '.$msg, $AppUI->msg);
      $AppUI->setMsg($msg, 0, false);
      $this->AssertEquals($msg, $AppUI->msg);

      $myArray = array('one' => 'First Message', 'two' => 'Second Message');
      $AppUI->setMsg($myArray, 0, false);
      $this->AssertEquals('First Message<br />Second Message', $AppUI->msg);

      $AppUI->setMsg($msg, 0, false);
      $this->AssertEquals($msg, $AppUI->msg);
    }

    /**
     * Tests getting an ajax module file for a module
     */
    public function testGetModuleAjaxValid()
    {
        $AppUI = $this->_AppUI;

        $this->assertEquals(W2P_BASE_DIR . '/modules/tasks/tasks.ajax.php', $AppUI->getModuleAjax('tasks'));
    }

    /**
     * Tests getting an ajax class for a module when no name is passed
     */
    public function testGetModuleAjaxNoName()
    {
        $AppUI = $this->_AppUI;

        $this->assertNull($AppUI->getModuleAjax());
    }

    /**
     * Tests that the proper version number is reported
     */
    public function testGetVersion()
    {
        $AppUI = $this->_AppUI;

        include W2P_BASE_DIR . '/includes/version.php';

        $version = $w2p_version_major . '.' . $w2p_version_minor;
        if (isset($w2p_version_patch)) {
            $version .= '.' . $w2p_version_patch;
        }
        if (isset($w2p_version_prepatch)) {
            $version .= '-' . $w2p_version_prepatch;
        }

        $this->assertEquals($version, $AppUI->getVersion());
    }

    /**
     * Tests getting the time, taking into account the timezone
     */
    public function testGetTZAwareTime()
    {
        $AppUI = $this->_AppUI;

        $timezone     = $AppUI->getPref('TIMEZONE');
        $datetimezone = new DateTimeZone($timezone);
        $datetime     = new DateTime('now', $datetimezone);
        $df           = $AppUI->getPref('FULLDATEFORMAT');

        $this->assertEquals($datetime->format($df), $AppUI->getTZAwareTime());
    }

    /**
     * Tests converting to system timezone(GMT) with no parameters
     */
    public function testConvertToSystemTZNoParams()
    {
        $AppUI = $this->_AppUI;

        $timezone     = $AppUI->getPref('TIMEZONE');
        $datetimezone = new DateTimeZone($timezone);
        $datetime     = new DateTime('now', $datetimezone);
        $datetime->setTimezone(new DateTimeZone('UTC'));

        $this->assertEquals($datetime->format('Y-m-d H:i:s'), $AppUI->convertToSystemTZ());
    }

    /**
     * Tests converting to system timezone(GMT) with a date passed
     */
    public function testConvertToSystemTZWithDateTime()
    {
        $AppUI = $this->_AppUI;

        $timezone     = $AppUI->getPref('TIMEZONE');
        $datetimezone = new DateTimeZone($timezone);
        $datetime     = new DateTime('2011-01-01 10:00:00', $datetimezone);
        $datetime->setTimezone(new DateTimeZone('UTC'));

        $this->assertEquals($datetime->format('Y-m-d H:i:s'), $AppUI->convertToSystemTZ('2011-01-01 10:00:00'));
    }

    /**
     * Tests converting to system timezone(GMT) with a format passed
     */
    public function testConvertToSystemTZWithFormat()
    {
        $AppUI = $this->_AppUI;

        $timezone     = $AppUI->getPref('TIMEZONE');
        $datetimezone = new DateTimeZone($timezone);
        $datetime     = new DateTime('now', $datetimezone);
        $datetime->setTimeZone(new DateTimeZone('UTC'));

        $this->assertEquals($datetime->format('d/m/Y h:i:s a'), $AppUI->convertToSystemTZ('', 'd/m/Y h:i:s a'));
    }

    /**
     * Tests converting to system timezone(GMT) with a date and format passed
     */
    public function testConvertToSystemTZWithDateTimeAndFormat()
    {
        $AppUI = $this->_AppUI;

        $timezone     = $AppUI->getPref('TIMEZONE');
        $datetimezone = new DateTimeZone($timezone);
        $datetime     = new DateTime('2011-01-1 10:00:00', $datetimezone);
        $datetime->setTimezone(new DateTimeZone('UTC'));

        $this->assertEquals($datetime->format('d/m/Y h:i:s a'), $AppUI->convertToSystemTZ('2011-01-01 10:00:00', 'd/m/Y h:i:s a'));
    }

    /**
     * Tests formatting timezone aware with no params
     */
    public function testFormatTZAwareTimeNoParams()
    {
        $AppUI = $this->_AppUI;

        $timezone     = $AppUI->getPref('TIMEZONE');
        $datetimezone = new DateTimeZone($timezone);
        $datetime     = new DateTime('now', $datetimezone);

        $this->assertEquals($datetime->format($AppUI->getPref('FULLDATEFORMAT')), $AppUI->formatTZAwareTime());
    }

    /**
     * Tests formatting timezone aware with a date
     */
    public function testFormatTZAwareTimeWithDateTime()
    {
        $AppUI = $this->_AppUI;

        $timezone     = new DateTimezone($AppUI->getPref('TIMEZONE'));
        $datetimezone = new DateTimeZone('UTC');
        $datetime     = new DateTime('2011-01-01 10:00:00', $datetimezone);
        $datetime->setTimeZone($timezone);

        $this->assertEquals($datetime->format($AppUI->getPref('FULLDATEFORMAT')), $AppUI->formatTZAwareTime('2011-01-01 10:00:00'));
    }

    /**
     * Tests formatting timezone aware with a format
     */
    public function testFormatTZAwareTimeWithFormat()
    {
        $AppUI = $this->_AppUI;

        $timezone     = $AppUI->getPref('TIMEZONE');
        $datetimezone = new DateTimeZone($timezone);
        $datetime     = new DateTime('now', $datetimezone);

        $this->assertEquals($datetime->format('d/m/Y h:i:s a'), $AppUI->formatTZAwareTime('', '%d/%m/%Y %I:%M:%S %p'));
    }

    /**
     * Tests formatting timezone aware with a date and format
     */
    public function testFormatTZAwareTimeWithDateTimeAndFormat()
    {
        $AppUI = $this->_AppUI;

        $timezone     = new DateTimeZone($AppUI->getPref('TIMEZONE'));
        $datetimezone = new DateTimeZone('UTC');
        $datetime     = new DateTime('2011-01-1 10:00:00', $datetimezone);
        $datetime->setTimeZone($timezone);

        $this->assertEquals($datetime->format('d/m/Y h:i:s a'), $AppUI->formatTZAwareTime('2011-01-01 10:00:00', '%d/%m/%Y %I:%M:%S %p'));
    }

    /**
     * Tests checking a users style selection when it is valid
     */
    public function testCheckStyleValidStyle()
    {
        $AppUI = $this->_AppUI;

        $AppUI->setPref('UISTYLE', 'wps-redmond');
        $AppUI->setStyle();

        $this->assertEquals('wps-redmond', $AppUI->getPref('UISTYLE'));
    }

    /**
     * Tests checking a users style selection when it is invalid
     */
    public function testCheckStyleInvalidStyle()
    {
        $AppUI = $this->_AppUI;

        $AppUI->setPref('UISTYLE', 'trevors-style');
        $AppUI->setStyle();

        $this->assertEquals('web2project', $AppUI->getPref('UISTYLE'));
    }

    public function testGetModuleAjax()
    {
        $this->assertEquals(W2P_BASE_DIR . '/modules/monkey/monkey.ajax.php', $this->_AppUI->getModuleAjax('monkey'));
    }

    public function testConvertToSystemTZ()
    {
        $this->_AppUI->user_prefs['TIMEZONE'] = 'CST';
        $time = $this->_AppUI->convertToSystemTZ('2010-05-12 14:34:56');
        $this->assertEquals('2010-05-12 19:34:56', $time);

        $this->_AppUI->user_prefs['TIMEZONE'] = 'BST';
        $time = $this->_AppUI->convertToSystemTZ('2010-05-12 09:34:56');
        $this->assertEquals('2010-05-12 08:34:56', $time);
    }

    public function testFormatTZAwareTime()
    {
        $this->_AppUI->user_prefs['TIMEZONE'] = 'CST';
        $this->_AppUI->user_prefs['FULLDATEFORMAT'] = 'd/m/Y h:i a';
        $time = $this->_AppUI->formatTZAwareTime('2010-05-12 14:34:56');
        $this->assertEquals('12/05/2010 09:34 am', $time);

        $this->_AppUI->user_prefs['TIMEZONE'] = 'BST';
        $this->_AppUI->user_prefs['FULLDATEFORMAT'] = 'd/m/Y h:i a';
        $time = $this->_AppUI->formatTZAwareTime('2010-05-12 09:34:56');
        $this->assertEquals('12/05/2010 10:34 am', $time);
    }

    public function testGetState()
    {
        $this->_AppUI->setState('animal', 'monkey');
        $this->assertEquals('monkey', $this->_AppUI->getState('animal'));

        $this->assertEquals('', $this->_AppUI->getState('not-set', ''));
        $this->assertEquals('texas', $this->_AppUI->getState('republic', 'texas'));
    }

    /**
     * @todo Implement testDoLogin().
     */
    public function testLoginRequired()
    {
        $this->_AppUI->user_id = 1;
        $this->assertFalse($this->_AppUI->doLogin());

        $this->_AppUI->user_id = -1;
        $this->assertTrue($this->_AppUI->doLogin());
    }
}
