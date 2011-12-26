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

class w2p_Mocks_Query extends w2p_Database_Query {

    protected $hash = array();
    protected $result = '';
    protected $list = array();
    protected $hashlist = array();

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

    public function stageList(array $array) {
        $this->list[] = $array;
    }
    public function loadList($maxrows = -1, $index = -1) {
        return $this->list;
    }

    public function stageHashList($index, array $array) {
        $this->hashlist[$index] = $array;
    }
    public function loadHashList($index = null) {
        return $this->hashlist;
    }

    public function loadObject(&$object, $bindAll = false, $strip = true) {
        $hash = $this->loadHash();

        $this->bindHashToObject($hash, $object, null, $strip, $bindAll);
    }

    public function bindHashToObject($hash, &$obj, $prefix = null, $checkSlashes = true, $bindAll = false) {
        foreach (get_object_vars($obj) as $k => $v) {
            if (isset($hash[$k])) {
                if (is_array(w2PHTMLDecode($hash[$k]))) {
                    $obj->$k = w2PHTMLDecode($hash[$k]);
                } else {
                    $obj->$k = ($checkSlashes && get_magic_quotes_gpc()) ? stripslashes(w2PHTMLDecode($hash[$k])) : w2PHTMLDecode($hash[$k]);
                }
            }
        }
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