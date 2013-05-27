<?php
/* 
* Container for creating prefix-safe queries.  Allows build up of
 * a select statement by adding components one at a time.
 *
 *  Note: This is a clean reimplementation of the original w2p_Database_Query
 *   class which was licensed under a GPL. This uses no code from the original
 *   but is interface-compatible.
 *
 * @package     web2project\database
 */

class w2p_Database_Query extends w2p_Database_oldQuery
{
    protected $_table_prefix;

    protected $_tables = array();
    protected $_fields = array();
    protected $_where  = array();
    protected $_joins  = array();
    protected $_group_by = array();
    protected $_order_by = array();

	/**< Handle to the database connection */
	protected $_db = null;
	/**
	 * Array of db function names
	 * @access private
	 * @var array
	 */
    protected $_db_funcs = array();

	/**
     * w2p_Database_Query constructor
	 *
	 * @param $prefix Database table prefix
	 */
	public function __construct($prefix = '')
    {
		global $db;
        $this->_db = $db;

        $this->_table_prefix = ('' != $prefix) ?
                $prefix : w2PgetConfig('dbprefix', '');

		$this->_db_funcs = array($this->dbfnNow());

		$this->clear();
	}

    public function clear()
    {
        $this->_tables = array();
        $this->_fields = array();
        $this->_where  = array();
        $this->_joins  = array();
        $this->_group_by = array();
        $this->_order_by = array();

        parent::clear();
    }

    /**
     * This method checks to see if the query is in the old structure and - if
     *   so - transforms it to the new structure. Therefore it should be pretty
     *   backwards compatible. It was generated by using print_r($this) to see
     *   the contents.
     *
     */
    protected function _convertFromOldStructure()
    {
        $this->_tables = count($this->table_list) ? $this->table_list : $this->_tables;
        $this->_fields = count($this->query) ? $this->query : $this->_fields;
        $this->_where  = count($this->where) ? $this->where : $this->_where;
        $this->_joins  = count($this->join) ? $this->join : $this->_joins;
        $this->_group_by = count($this->group_by) ? $this->group_by : $this->_group_by;
        $this->_order_by = count($this->order_by) ? $this->order_by : $this->_order_by;
    }

	/**
     * Prepare the SELECT component of the SQL query
     *
     * @todo quote fields and tables?
	 */
	protected function prepareSelect()
    {
        $this->_convertFromOldStructure();

        $where = $this->_buildWhere();
        $joins = $this->_buildJoins();

        $fields = count($this->_fields) ? implode(',' , $this->_fields) : '*';
        $aliases = array();
        foreach($this->_tables as $alias => $table) {
            $aliases[] = "$table AS $alias";
        }
        $tables = implode(',', $aliases);

        $group_by = count($this->_group_by) ? 'GROUP BY ' . implode(',' , $this->_group_by) : '';
        $order_by = count($this->_order_by) ? 'ORDER BY ' . implode(',' , $this->_order_by) : '';

        $sql = "SELECT $fields FROM $tables $joins $where $group_by $order_by";

        return $sql;
	}

    /**
     * Allows you to order query results by a field, can be used multiple times
     *
     * @param type  $field 
     */
    public function addOrder($field = '')
    {
        if('' != $field) {
            $this->_order_by[] = $field;
        }
    }

    /**
     * Allows you to group query results by a field, can be used multiple times
     *
     * @param type  $field 
     */
    public function addGroup($field = '')
    {
        if('' != $field) {
            $this->_group_by[] = $field;
        }
    }

    /**
     * Allows you to filter query results by a field, can be used multiple times
     *
     * @param type  $field 
     */
    public function addWhere($field = '')
    {
        if('' != $field) {
            $this->_where[] = $field;
        }
    }

    /**
     * This combines all the where clauses into a single statement. I don't
     *   like the nested loops but since this array can only be two levels deep,
     *   recursion is probably excessive.
     *
     * @return type 
     */
    protected function _buildWhere()
    {
        $simple = array();

        if (count($this->_where)) {
            foreach($this->_where as $where) {
                if (is_array($where)) {
                    foreach($where as $subwhere) {
                        $simple[] = $subwhere;
                    }
                } else {
                    $simple[] = $where;
                }
            }
        }

        return count($simple) ? ' WHERE ' . implode(' AND ' , $simple) : '';
    }

    protected function _buildJoins()
    {
        $joins = '';
        if (count($this->join)) {
            foreach ($this->join as $join) {
                $join['alias'] = ('' == $join['alias']) ? $join['table'] : $join['alias'];
                $joins .= strtoupper($join['type']) . " JOIN " . $join['table'] . " AS " . $join['alias'] . " ON " . $join['condition'] . " ";
            }
        }

        return $joins;
    }
}