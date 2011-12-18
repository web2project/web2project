<?php /* $Id$ $URL$ */

/**
 *	@package    web2project
 *	@subpackage unit_tests
 *	@version    $Revision$
 *  @license	Clear BSD
 *  @author     Keith casey
 */

class w2p_Database_Mock_Test extends PHPUnit_Framework_TestCase {

    /**
     * An AppUI object for validation
     *
     * @param CAppUI
     * @access private
     */
    private $mockDB;

    /**
     * Create an AppUI before running tests
     */
    protected function setUp()
    {
        $this->mockDB = new w2p_Database_Mock();
    }

    public function testLoadHash() {
        $hash1 = array('key1' => 'value1', 'key2' => 'value2');
        $hash2 = array('key3' => 'value3', 'key4' => 'value4');

        $this->mockDB->stageHash($hash1);
        $this->mockDB->stageHash($hash2);

        $this->assertSame($hash1, $this->mockDB->loadHash());
        $this->assertSame($hash2, $this->mockDB->loadHash());
        $this->assertNull($this->mockDB->loadHash());
    }

    public function testLoadResult() {
        $this->mockDB->stageResult('value1');
        $this->assertEquals('value1', $this->mockDB->loadResult());

        $this->mockDB->stageResult('value2');
        $this->assertEquals('value2', $this->mockDB->loadResult());

        $this->mockDB->stageResult('value3');
        $this->assertNotEquals('value2', $this->mockDB->loadResult());
    }

    public function testLoadList() {
        $this->mockDB->stageList(array('key1' => 'value1', 'key2' => 'value2'));
        $this->mockDB->stageList(array('key3' => 'value3', 'key4' => 'value4'));
        $this->assertEquals(2, count($this->mockDB->loadList()));

        $this->mockDB->stageList(array('key5' => 'value5', 'key6' => 'value6'));
        $this->assertEquals(3, count($this->mockDB->loadList()));

        $list = $this->mockDB->loadList();
        $this->assertEquals('value4', $list[1]['key4']);
    }

//    public function loadObject(&$object, $bindAll = false, $strip = true) {
//        $hash = $this->loadHash();
//
//        $this->bindHashToObject($hash, $object, null, $strip, $bindAll);
//    }
//
//    public function insertObject($table, &$object, $keyName = null, $verbose = false) {
//
//        parent::insertObject($table, $object, $keyName, $verbose);
//        $object->{$keyName} = 1;
//    }
//
//    public function updateObject($table, &$object, $keyName, $updateNulls = true) {
//
//        parent::updateObject($table, $object, $keyName, $updateNulls);
//    }
}