<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Class for testing AppUI functionality
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
global $db;
global $ADODB_FETCH_MODE;
global $w2p_performance_dbtime;
global $w2p_performance_old_dbqueries;
global $AppUI;
global $w2Pconfig;

require_once '../base.php';
require_once W2P_BASE_DIR . '/includes/config.php';
require_once W2P_BASE_DIR . '/includes/main_functions.php';
require_once W2P_BASE_DIR . '/includes/db_adodb.php';

/*
 * Need this to test actions that require permissions.
 */
$AppUI  = new CAppUI;
$_POST['login'] = 'login';
$_REQUEST['login'] = 'sql';
$AppUI->login('admin', 'passwd');

require_once W2P_BASE_DIR . '/includes/session.php';
require_once 'PHPUnit/Framework.php';

/**
 * DateTest Class.
 *
 * Class to test the date include
 * @author D. Keith Casey, Jr.
 * @package web2project
 * @subpackage unit_tests
 */
class CAppUI_Test extends PHPUnit_Framework_TestCase
{

    /**
     * Tests the attributes of a new AppUI object
     */
    public function testNewAppUIAttributes()
    {
        global $w2pconfig;

        $AppUI = new CAppUI;

        $this->assertType('CAppUI',                         $AppUI);
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
        $this->assertObjectHasAttribute('user_is_admin',    $AppUI);
        $this->assertObjectHasAttribute('long_date_format', $AppUI);
        $this->assertObjectHasAttribute('objStore',         $AppUI);
        $this->assertObjectHasAttribute('project_id',       $AppUI);
    }

    /**
     * Test the translation function
     */
	public function test__()
	{
		global $AppUI, $w2Pconfig;

		$w2Pconfig['locale_warn'] = false;
		$this->assertEquals('Company',        $AppUI->__('Company'));
		$this->assertEquals('NoGonnaBeThere', $AppUI->__('NoGonnaBeThere'));

		/* Turn on 'untranslatable' warning */
		$w2Pconfig['locale_warn'] = true;
		$this->assertEquals('Projects^', $AppUI->__('Projects'));
		$this->assertEquals('Add File^', $AppUI->__('Add File'));

		/* Change to another language and reload tranlations */
		$AppUI->user_locale = 'es';
		require W2P_BASE_DIR . '/locales/core.php';
		$this->assertEquals('Proyectos',      $AppUI->__('Projects'));
		$this->assertEquals('Ciudad',         $AppUI->__('City'));
		$this->assertEquals('StillNotThere^', $AppUI->__('StillNotThere'));

		/* Change back to English and reload tranlations */
		$AppUI->user_locale = 'en';
		require W2P_BASE_DIR . '/locales/core.php';
		$this->assertEquals('Projects',        $AppUI->__('Projects'));
		$this->assertEquals('NoGonnaBeThere^', $AppUI->__('NoGonnaBeThere'));
	}

    /**
     * Tests getting a preference
     */
	public function testGetPref()
	{
		global $AppUI;

		$this->assertEquals('en', $AppUI->getPref('LOCALE'));
		$this->assertEquals('',   $AppUI->getPref('NotGonnaBeThere'));
	}

    /**
     * Tests setting a preference
     */
	public function testSetPref()
	{
		global $AppUI;

		$this->assertEquals('en',     $AppUI->getPref('LOCALE'));
		$AppUI->setPref('AddingThis', 'Monkey');
		$this->assertEquals('Monkey', $AppUI->getPref('AddingThis'));
	}

    /**
     * Test setting the global state
     */
    public function testSetState()
    {
        global $AppUI;

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
        global $AppUI;
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
     */
	public function testSavePlace()
	{
		global $AppUI;

		$_SERVER['QUERY_STRING'] = 'testUrl';
		$AppUI->savePlace();
		$this->assertEquals('testUrl', $AppUI->getPlace());

		$AppUI->savePlace('?m=projects&amp;a=view&amp;project_id=1');
		$this->assertEquals('?m=projects&amp;a=view&amp;project_id=1', $AppUI->getPlace());
	}

    /**
     * Tests reseting the place(url)
     */
	public function testResetPlace()
	{
		global $AppUI;

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
	  global $AppUI;

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
	  global $AppUI;

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
}
