<?php /* $Id$ $URL$ */

/**
 *	@package web2Project
 *	@subpackage modules
 *	@version $Revision$
 *  // Copyright 2004, Adam Donnison <adam@saki.com.au>
 *  // Released under GNU General Public License version 2 or later
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
			$this->_query->clear();
			$this->_query->addTable('resource_types');
			$this->_query->addQuery('resource_type_id, resource_type_name');
			$this->_query->addOrder('resource_type_name');

			$res = &$this->_query->exec(ADODB_FETCH_ASSOC);
			$typelist = array();
			$typelist[0] = array('resource_type_id' => 0, 'resource_type_name' => 'All Resources');
			while ($row = $this->_query->fetchRow()) {
				$typelist[] = $row;
			}
			$this->_query->clear();
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

		$q = $this->_query;
		$q->addTable('resource_types');
		$q->addWhere('resource_type_id = ' . (int)$this->resource_type);
		$res = &$q->exec(ADODB_FETCH_ASSOC);
		if ($row = $q->fetchRow()) {
			$result = $row['resource_type_name'];
		}

		return $result;
	}

    public function delete(CAppUI $AppUI) {
        $perms = $AppUI->acl();
        $this->_error = array();

        if ($perms->checkModuleItem('resources', 'delete', $this->resource_id)) {
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