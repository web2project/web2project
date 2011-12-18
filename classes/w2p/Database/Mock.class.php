<?php /* $Id$ $URL$ */

/**
 *	@package    web2project
 *	@subpackage database
 *	@version    $Revision$
 *  @license	Clear BSD
 *  @author     Keith casey
 */

/**
 * Mock database query class
 */

class w2p_Database_Mock extends w2p_Database_Query {

    protected $hash = array();
    protected $result = '';

    public function __construct($prefix = null, $query_db = null) {
        parent::__construct($prefix, $query_db);
    }

    public function stageHash(array $array) {
        $this->hash[] = $array;
    }
    public function loadHash() {
        return array_shift($this->hash);
    }

    public function stageResult($value) {
        $this->result = $value;
    }
    public function loadResult() {
        return $this->result;
    }

    public function loadObject(&$object, $bindAll = false, $strip = true) {
        $hash = $this->loadHash();

        $this->bindHashToObject($hash, $object, null, $strip, $bindAll);
    }

    public function exec() {
        return true;
    }

    public function insertObject($table, &$object, $keyName = null, $verbose = false) {

        parent::insertObject($table, $object, $keyName, $verbose);
        $object->{$keyName} = 1;
    }

    public function updateObject($table, &$object, $keyName, $updateNulls = true) {

        parent::updateObject($table, $object, $keyName, $updateNulls);
    }
}