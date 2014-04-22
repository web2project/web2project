<?php

/**
 * @package     web2project\mocks
 * @author      D. Keith Casey, Jr. <caseydk@users.sourceforge.net>
 */

class w2p_Mocks_Query extends w2p_Database_Query
{
    protected $hash = array();
    protected $result = '';
    protected $list = array();
    protected $hashlist = array();

    public function stageHash(array $array)
    {
        $this->hash[] = $array;
    }
    public function loadHash()
    {
        return array_shift($this->hash);
    }
    public function clearHash()
    {
        $this->hash = array();
    }

    public function stageResult($value)
    {
        $this->result = $value;
    }
    public function loadResult()
    {
        return $this->result;
    }

    public function stageList(array $array)
    {
        $this->list[] = $array;
    }
    public function loadList($maxrows = -1, $index = -1)
    {
        return $this->list;
    }
    public function clearList()
    {
        $this->list = array();
    }

    public function stageHashList($index, $value)
    {
        $this->hashlist[$index] = $value;
    }
    public function loadHashList($index = null)
    {
        return $this->hashlist;
    }
    public function clearHashList()
    {
        $this->hashlist = array();
    }

    public function loadObject(&$object, $bindAll = false, $strip = true)
    {
        $hash = $this->loadHash();

        $this->bindHashToObject($hash, $object, null, $strip, $bindAll);
    }

    public function bindHashToObject($hash, &$obj, $prefix = null, $notUsed = true, $bindAll = false)
    {
        foreach (get_object_vars($obj) as $k => $notUsed2) {
            if (isset($hash[$k])) {
                if (is_array(w2PHTMLDecode($hash[$k]))) {
                    $obj->$k = w2PHTMLDecode($hash[$k]);
                } else {
                    $obj->$k = w2PHTMLDecode($hash[$k]);
                }
            }
        }
    }


    public function exec()
    {
        return true;
    }

    public function insertObject($table, &$object, $keyName = null, $verbose = false)
    {
        $object->{$keyName} = 1;
        return true;
    }

    public function updateObject($table, &$object, $keyName, $updateNulls = true)
    {
        return true;
    }
}