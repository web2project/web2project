<?php
/**
 * Class for testing Query Mock functionality
 *
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to Clear BSD License. Please see the
 *   LICENSE file in root of site for further details
 *
 * @author      D. Keith Casey, Jr.<caseydk@users.sourceforge.net>
 * @category    w2p_Mocks_Query_Test
 * @package     web2project
 * @subpackage  unit_tests
 * @license     Clear BSD
 * @link        http://www.web2project.net
 */

class w2p_Mocks_QueryTest extends CommonSetup
{
    public function testLoadHash()
    {
        $hash1 = array('key1' => 'value1', 'key2' => 'value2');
        $hash2 = array('key3' => 'value3', 'key4' => 'value4');

        $this->mockDB->stageHash($hash1);
        $this->mockDB->stageHash($hash2);

        $this->assertSame($hash1, $this->mockDB->loadHash());
        $this->assertSame($hash2, $this->mockDB->loadHash());
        $this->assertNull($this->mockDB->loadHash());
    }

    public function testLoadResult()
    {
        $this->mockDB->stageResult('value1');
        $this->assertEquals('value1', $this->mockDB->loadResult());

        $this->mockDB->stageResult('value2');
        $this->assertEquals('value2', $this->mockDB->loadResult());

        $this->mockDB->stageResult('value3');
        $this->assertNotEquals('value2', $this->mockDB->loadResult());
    }

    public function testLoadList()
    {
        $this->mockDB->stageList(array('key1' => 'value1', 'key2' => 'value2'));
        $this->mockDB->stageList(array('key3' => 'value3', 'key4' => 'value4'));
        $this->assertEquals(2, count($this->mockDB->loadList()));

        $this->mockDB->stageList(array('key5' => 'value5', 'key6' => 'value6'));
        $this->assertEquals(3, count($this->mockDB->loadList()));

        $list = $this->mockDB->loadList();
        $this->assertEquals('value4', $list[1]['key4']);
    }

    public function testClearList()
    {
        $this->mockDB->stageList(array('key1' => 'value1', 'key2' => 'value2'));
        $this->mockDB->stageList(array('key3' => 'value3', 'key4' => 'value4'));
        $this->assertEquals(2, count($this->mockDB->loadList()));

        $this->mockDB->clearList();
        $this->assertEquals(0, count($this->mockDB->loadList()));
    }

    public function testLoadHashList()
    {
        $this->mockDB->stageHashList(1, array('key1' => 'value1', 'key2' => 'value2'));
        $this->mockDB->stageHashList(5, array('key3' => 'value3', 'key4' => 'value4'));
        $this->assertEquals(2, count($this->mockDB->loadHashList()));

        $this->mockDB->stageHashList(7, array('key5' => 'value5', 'key6' => 'value6'));
        $this->assertEquals(3, count($this->mockDB->loadHashList()));

        $hashlist = $this->mockDB->loadHashList();
        $this->assertEquals('value5', $hashlist[7]['key5']);
    }

    public function testClearHashList()
    {
        $this->mockDB->stageHashList(1, array('key1' => 'value1', 'key2' => 'value2'));
        $this->mockDB->stageHashList(5, array('key3' => 'value3', 'key4' => 'value4'));
        $this->assertEquals(2, count($this->mockDB->loadHashList()));

        $this->mockDB->clearHashList();
        $this->assertEquals(0, count($this->mockDB->loadHashList()));
    }

    public function testLoadObject()
    {
        $hash = array('link_name' => 'web2project homepage', 'link_url' => 'http://web2project.net', 'link_owner' => 1);
        $this->mockDB->stageHash($hash);

        $link = new CLink();
        $this->mockDB->loadObject($link);

        $this->assertEquals('web2project homepage', $link->link_name);
        $this->assertEquals('http://web2project.net', $link->link_url);
    }

    public function testBindHashToObject()
    {
        $subhash = array('link_name' => 'web2project homepage', 'link_url' => 'http://web2project.net', 'link_owner' => 1);
        $hash = array('link_name' => 'Google', 'link_url' => 'http://google.com', 'link_owner' => 1, 'link_category' => $subhash);

        $link = new CLink();

        $this->mockDB->bindHashToObject($hash, $link);
    }
}
