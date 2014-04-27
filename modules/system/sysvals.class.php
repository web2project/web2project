<?php
/**
 * @package     web2project\modules\core
 */

class CSystem_SysVal extends w2p_Core_BaseObject
{
	public $sysval_id = null;
	public $sysval_key_id = null;
	public $sysval_title = null;
	public $sysval_value_id = null;
	public $sysval_value = null;

    public function isValid()
    {
        $baseErrorMsg = get_class($this) . '::store-check failed - ';

        if ($this->sysval_key_id == 0) {
            $this->_error['sysval_key_id'] = $baseErrorMsg . 'key type is not set';
		}
		if (!$this->sysval_title) {
			$this->_error['sysval_title'] = $baseErrorMsg . 'key title is not set';
		}

        return (count($this->_error)) ? false : true;
    }

	public function __construct($key = null, $title = null, $value = null)
    {
        parent::__construct('sysvals', 'sysval_id', 'system');
		$this->sysval_key_id = $key;
		$this->sysval_title = $title;
		$this->sysval_value = $value;
	}

    /**
     * NOTE: This function is a simplified version from the w2p_Core_BaseObject
     *   because that version of the function applies filtering that kills our
     *   required fields which legitimately have <'s and >'s.
     */
	public function bind($hash, $prefix = null, $checkSlashes = true, $bindAll = false)
	{
		if (!is_array($hash)) {
			$this->_error = get_class($this) . '::bind failed.';
			return false;
		} else {
			$q = $this->_getQuery();
			$q->bindHashToObject($hash, $this, $prefix, $checkSlashes, $bindAll);
			return true;
		}
	}

	public function store($unused = null)
    {
        $this->w2PTrimAll();

		$values = parseFormatSysval($this->sysval_value, $this->sysval_key_id);
		//lets delete the old values
		$q = $this->_getQuery();
		if ($this->sysval_key_id && $this->sysval_title) {
			$q->setDelete('sysvals');
			$q->addWhere('sysval_key_id = ' . (int)$this->sysval_key_id);
			$q->addWhere('sysval_title = \'' . $this->sysval_title . '\'');
			if (!$q->exec()) {
                $this->_error['store'] = get_class($this) . '::store failed: ' . db_error();
                return false;
			}
		}
        $q->clear();
		foreach ($values as $key => $value) {
			$q->addTable('sysvals');
			$q->addInsert('sysval_key_id', $this->sysval_key_id);
			$q->addInsert('sysval_title', $this->sysval_title);
			$q->addInsert('sysval_value_id', $key);
			$q->addInsert('sysval_value', $value);
			if (!$q->exec()) {
				$this->_error['store'] = get_class($this) . '::store failed: ' . db_error();
                return false;
			}
			$q->clear();
		}
		return true;
	}

    public function delete($unused = null)
    {
        $result = false;

		if ($this->sysval_title) {
            $q = $this->_getQuery();
			$q->setDelete('sysvals');
			$q->addWhere('sysval_title = \'' . $this->sysval_title . '\'');

            $result = $q->exec();
            if(!$result) {
                $this->_errors[] = db_error();
            }
		}
		return $result;
	}
}