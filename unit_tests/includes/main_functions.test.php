<?php
/**
 * Class for testing main_functions functionality
 *
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to Clear BSD License. Please see the
 *   LICENSE file in root of site for further details
 *
 * @author      D. Keith Casey, Jr.<caseydk@users.sourceforge.net>
 * @category    main_functions
 * @package     web2project
 * @subpackage  unit_tests
 * @license     Clear BSD
 * @link        http://www.web2project.net
 */

class Main_Functions_Test extends PHPUnit_Framework_TestCase
{
	public function testW2PgetParam()
	{
		$params = array('m' => 'projects', 'a' => 'view', 'v' => '<script>alert</script>', 
				'html' => '<div onclick="doSomething()">asdf</div>', '<script>' => 'Something Nasty',
                'key' => array(1,2,3));

        $this->assertEquals('', w2PgetParam($params, 'NotGonnaBeThere'));

        $this->assertEquals('Some Default', w2PgetParam($params, 'NotGonnaBeThere', 'Some Default'));

        //$this->markTestIncomplete("Currently w2PgetParam redirects for tainted names.. what do we do there?");

        $this->assertEquals('alert', w2PgetParam($params, 'v'));

        $this->assertEquals('asdf', w2PgetParam($params, 'html'));

        $this->assertEquals('Some Default', w2PgetParam($params, '<script>', 'Some Default'));

        $this->assertEquals(array(1,2,3), w2PgetParam($params, 'key', array()));

        $this->assertEquals(array(), w2PgetParam($params, 'not-there', array()));
	}

    public function testArrayMerge()
    {
        $array1 = array('a', 'b', 'c', 4 => 'd', 5 => 'e');
        $array2 = array('z', 6 => 'y', 7 => 'x', 4 => 'w', 5 => 'v');
        $newArray = arrayMerge($array1, $array2);

        $this->assertEquals('b', $newArray[1]);        //    Tests no overwrite
        $this->assertEquals('w', $newArray[4]);        //    Tests explicit overwrite
        $this->assertEquals('z', $newArray[0]);        //    Tests conincidental overwrite
    }
    public function testW2PgetConfig()
    {
        global $w2Pconfig;

        $this->assertEquals('web2project.net', w2PgetConfig('site_domain'));
        $this->assertEquals(null, w2PgetConfig('NotGonnaBeThere'));
        $this->assertEquals('Some Default', w2PgetConfig('NotGonnaBeThere', 'Some Default'));
    }

    public function testFilterCurrency()
    {
        $this->assertEquals('123456789', filterCurrency('123456789'));

        $this->assertEquals('1234567.89', filterCurrency('1234567,89'));
        $this->assertEquals('1234567.89', filterCurrency('1.234.567,89'));

        $this->assertEquals('1234567.89', filterCurrency('1234567.89'));
        $this->assertEquals('1234567.89', filterCurrency('1,234,567.89'));
    }

    public function testConvert2days()
    {
        $hours = 1;
        $this->assertEquals(0.125, convert2days($hours, 0));

        $hoursIndicator = 1;
        $hours = 8;
        $this->assertEquals(1, convert2days($hours, $hoursIndicator));

        $dayIndicator = 24;
        $days = 1;
        $this->assertEquals(1, convert2days($days, $dayIndicator));
    }

    public function test__autoload()
    {
        $this->assertTrue(class_exists('CProject'));
        $search = new CSmartSearch();
        $this->assertTrue(class_exists('CSmartSearch'));
    }

    /**
    * Tests the proper creation of a link
    */
    public function test_w2p_url()
    {
        $target = '<a href="http://web2project.net" target="_new">http://web2project.net</a>';
        $linkText = w2p_url('http://web2project.net');
        $this->assertEquals($target, $linkText);

        $target = '';
        $linkText = w2p_url('');
        $this->assertEquals($target, $linkText);

        $target = '<a href="http://web2project.net" target="_new">web2project</a>';
        $linkText = w2p_url('http://web2project.net', 'web2project');
        $this->assertEquals($target, $linkText);
    }

    /**
    * Tests the proper creation of an email link
    */
    public function test_w2p_email()
    {
        $target = '<a href="mailto:test@test.com">test@test.com</a>';
        $linkText = w2p_email('test@test.com');
        $this->assertEquals($target, $linkText);

        $target = '';
        $linkText = w2p_email('');
        $this->assertEquals($target, $linkText);

        $target = '<a href="mailto:test@test.com">web2project</a>';
        $linkText = w2p_email('test@test.com', 'web2project');
        $this->assertEquals($target, $linkText);
    }

    public function test_w2p_check_email()
    {
        $this->assertEquals('tests@web2project.net', w2p_check_email('tests@web2project.net'));
        $this->assertEquals('tests@bugs.web2project.net', w2p_check_email('tests@bugs.web2project.net'));

        $this->assertFalse(w2p_check_email('@web2project.net'));
        $this->assertFalse(w2p_check_email('testsweb2project.net'));
        $this->assertFalse(w2p_check_email('tests@web2project'));
        $this->assertFalse(w2p_check_email('tests@'));
        $this->assertFalse(w2p_check_email('tests@.net'));
    }

    /**
    * Tests the proper creation of an email link
    */
    public function test_w2p_textarea()
    {
        $target = '';
        $linkText = w2p_textarea('');
        $this->assertEquals($target, $linkText);

        $target = 'Have you seen this - <a href="http://web2project.net" target="_blank">http://web2project.net</a> ?';
        $linkText = w2p_textarea('Have you seen this - http://web2project.net ?');
        $this->assertEquals($target, $linkText);

        $target = '<a href="http://web2project.net" target="_blank">http://web2project.net</a> is a fork of <a href="http://dotproject.net" target="_blank">http://dotproject.net</a>';
        $linkText = w2p_textarea('http://web2project.net is a fork of http://dotproject.net');
        $this->assertEquals($target, $linkText);

        $target = '<a href="http://web2project.net" target="_blank">http://web2project.net</a> is a great site';
        $linkText = w2p_textarea('http://web2project.net is a great site');
        $this->assertEquals($target, $linkText);

        $target = 'Please check out <a href="http://web2project.net" target="_blank">http://web2project.net</a>';
        $linkText = w2p_textarea('Please check out http://web2project.net');
        $this->assertEquals($target, $linkText);
    }

    public function test_w2p_pluralize()
    {
        $this->assertEquals('projects',  w2p_pluralize('project'));
        $this->assertEquals('links',     w2p_pluralize('link'));
        $this->assertEquals('companies', w2p_pluralize('company'));
        $this->assertEquals('holidays',  w2p_pluralize('holiday'));
        $this->assertEquals('todos',     w2p_pluralize('todo'));
    }

    public function test_w2Psgn()
    {
        $this->assertEquals(-1, w2Psgn(-56.2));
        $this->assertEquals( 0, w2Psgn(0));
        $this->assertEquals( 1, w2Psgn(0.01));
    }

    public function test_w2pFindTaskComplete()
    {
        $empty   = null;    //we don't care what this value is
        $past    = date('Y-m-d', strtotime('-1 week'));
        $future  = date('Y-m-d', strtotime('+1 week'));

        $this->assertEquals('done',       w2pFindTaskComplete($empty,  $empty,  100));
        $this->assertEquals('',           w2pFindTaskComplete($future, $empty,  0));
        $this->assertEquals('active',     w2pFindTaskComplete($past,   $future, 50));
        $this->assertEquals('notstarted', w2pFindTaskComplete($past,   $future, 0));
        $this->assertEquals('late',       w2pFindTaskComplete($past,   $empty,  0));
    }

    public function test_notifyNewExternalUser()
    {
        // This is for phpUnit 3.6+
        $emailUtility = $this->getMock('w2p_Utilities_Mail', array('Send', 'Body', 'Subject'));
        $emailUtility->expects($this->once())
                     ->method('Subject')
                     ->with($this->stringContains('New Account Created'));
        $emailUtility->expects($this->once())
                     ->method('Body')
                     ->with($this->stringContains('logname'));
        $emailUtility->expects($this->once())
                     ->method('Send');

        notifyNewExternalUser('test@test.com', 'username', 'logname', 'logpwd', $emailUtility);
    }

    public function test_notifyNewUser()
    {
        // This is for phpUnit 3.6+
        $emailUtility = $this->getMock('w2p_Utilities_Mail', array('Send', 'Body', 'Subject'));
        $emailUtility->expects($this->once())
                     ->method('Subject')
                     ->with($this->stringContains('New Account Created'));
        $emailUtility->expects($this->once())
                     ->method('Body')
                     ->with($this->stringContains('username'));
        $emailUtility->expects($this->once())
                     ->method('Send');

        notifyNewUser('test@test.com', 'username', $emailUtility);
    }

    public function test_getAuth()
    {
        $object = getAuth('sql');
        $this->assertInstanceOf('w2p_Authenticators_SQL', $object);

        $object = getAuth('ldap');
        $this->assertInstanceOf('w2p_Authenticators_LDAP', $object);

        $object = getAuth('something else');
        $this->assertInstanceOf('w2p_Authenticators_SQL', $object);
    }
}
