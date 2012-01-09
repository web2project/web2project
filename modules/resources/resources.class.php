<?php /* $Id$ $URL$ */

/**
 *	@package web2Project
 *	@subpackage modules
 *	@version $Revision$
 *
 */

class CResource extends w2p_Core_BaseObject {
	public $resource_id = null;
	public $resource_key = null;
	public $resource_name = null;
	public $resource_type = null;
	public $resource_max_allocation = null;
	public $resource_note = null;

	public function __construct() {
        parent::__construct('resources', 'resource_id');
	}

	public function &loadTypes() {
		// If we have loaded the resource types before then we don't need to
		// load them again.
		if (isset($_SESSION['resource_type_list'])) {
			$typelist = &$_SESSION['resource_type_list'];
		} else {
			$q = $this->_getQuery();
			$q->addTable('resource_types');
			$q->addQuery('resource_type_id, resource_type_name');
			$q->addOrder('resource_type_name');

			$res = &$q->exec(ADODB_FETCH_ASSOC);
			$typelist = array();
			$typelist[0] = array('resource_type_id' => 0, 'resource_type_name' => 'All Resources');
			while ($row = $q->fetchRow()) {
				$typelist[] = $row;
			}
			$_SESSION['resource_type_list'] = &$typelist;
		}
		return $typelist;
	}

	public function typeSelect() {
		$typelist = &$this->loadTypes();
		$result = array();
		foreach ($typelist as $type) {
			$result[$type['resource_type_id']] = $type['resource_type_name'];
		}
		return $result;
	}

	public function getTypeName() {
		$result = 'All Resources';

		$q = $this->_getQuery();
		$q->addTable('resource_types');
		$q->addWhere('resource_type_id = ' . (int)$this->resource_type);
		$res = &$q->exec(ADODB_FETCH_ASSOC);
		if ($row = $q->fetchRow()) {
			$result = $row['resource_type_name'];
		}

		return $result;
	}

    public function store(w2p_Core_CAppUI $AppUI = null) {
        $stored = false;

        $this->_error = $this->check();

        if (count($this->error)) {
            return $this->_error;
        }

        $q = $this->_getQuery();
        if ($this->resource_id && $this->_perms->checkModuleItem($this->_tbl_module, 'edit', $this->{$this->_tbl_key})) {

            if (($msg = parent::store())) {
                return $msg;
            }
            $stored = true;
        }
        if (0 == $this->resource_id && $this->_perms->checkModuleItem($this->_tbl_module, 'add')) {
            if (($msg = parent::store())) {
                return $msg;
            }
            $stored = true;
        }
        return $stored;
    }

    public function delete(w2p_Core_CAppUI $AppUI = null) {
        if ($this->_perms->checkModuleItem($this->_tbl_module, 'delete', $this->{$this->_tbl_key})) {
            if ($msg = parent::delete()) {
                return $msg;
            }
            return true;
        }
        return false;
    }

    public function hook_search() {
        $search['table'] = 'resources';
        $search['table_module'] = 'resources';
        $search['table_key'] = 'resource_id';
        $search['table_link'] = 'index.php?m=resources&a=view&resource_id='; // first part of link
        $search['table_title'] = 'Resources';
        $search['table_orderby'] = 'resource_name';
        $search['search_fields'] = array('resource_name', 'resource_key','resource_note');
        $search['display_fields'] = $search['search_fields'];

        return $search;
    }
}
