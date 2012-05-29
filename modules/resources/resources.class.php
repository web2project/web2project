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

    /*
     * This is only here for backwards compatibility
     *
     * @deprecated
     */
	public function &loadTypes() {
		trigger_error("CResource->loadTypes() has been deprecated in v3.0 and will be removed in v4.0. Please use w2PgetSysVal('ResourceTypes') instead.", E_USER_NOTICE);

        return $this->typeSelect();
	}

    /*
     * This is only here for backwards compatibility
     *
     * @deprecated
     */
	public function typeSelect() {
        trigger_error("CResource->typeSelect() has been deprecated in v3.0 and will be removed in v4.0. Please use w2PgetSysVal('ResourceTypes') instead.", E_USER_NOTICE);

        $typelist = w2PgetSysVal('ResourceTypes');
        if (!count($typelist)) {
            $this->convertTypes();
            $typelist = w2PgetSysVal('ResourceTypes');
        }

		return $typelist;
	}

    /*
     * This is only here for backwards compatibility
     *
     * @deprecated
     */
	public function getTypeName() {
        trigger_error("CResource->getTypeName() has been deprecated in v3.0 and will be removed in v4.0. Please use w2PgetSysVal('ResourceTypes') instead.", E_USER_NOTICE);

        $typelist = $this->typeSelect();
        return $typelist[$this->resource_type];
	}

    public function store() {
        $stored = false;

        $this->_error = $this->check();

        if (count($this->error)) {
            return $this->_error;
        }

        $q = $this->_getQuery();
        if ($this->{$this->_tbl_key} && $this->canEdit()) {
            if (($msg = parent::store())) {
                return $msg;
            }
            $stored = true;
        }
        if (0 == $this->{$this->_tbl_key} && $this->canCreate()) {
            if (($msg = parent::store())) {
                return $msg;
            }
            $stored = true;
        }
        return $stored;
    }

    public function delete() {
        if ($this->canDelete()) {
            if ($msg = parent::delete()) {
                return $msg;
            }
            return true;
        }
        return false;
    }

    /*
     * This method should only be run once to upgrade the module from v1.0.1 to
     *   v1.1.0 which happened around the web2project v3.0 release.
     */
    public function convertTypes()
    {
        $q = $this->_getQuery();
        $q->addTable('resource_types');
        $q->addQuery('*');
        $types = $q->loadList();

        $resourceTypes = array();
        foreach($types as $type) {
            $resourceTypes[$type['resource_type_id']] = $type['resource_type_name'];
        }

        foreach ($resourceTypes as $id => $type) {
            $q->addTable('sysvals');
            $q->addInsert('sysval_key_id', 1);
            $q->addInsert('sysval_title', 'ResourceTypes');
            $q->addInsert('sysval_value', $type);
            $q->addInsert('sysval_value_id', $id);
            $q->exec();
            $q->clear();
        }

        // This removes the dead table.
        $q->dropTable('resource_types');
        $result = $q->exec();
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
