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
    protected $_having = array();
    protected $_joins  = array();
    protected $_group_by = array();
    protected $_order_by = array();
    protected $_limit   = 0;
    protected $_offset  = 0;

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
        $this->_limit  = 0;

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
        $this->_having  = count($this->having) ? $this->having : $this->_having;
        $this->_joins  = count($this->join) ? $this->join : $this->_joins;
        $this->_group_by = count($this->group_by) ? $this->group_by : $this->_group_by;
        $this->_order_by = count($this->order_by) ? $this->order_by : $this->_order_by;
    }

    public function prepare($clear = false)
    {
        $this->_convertFromOldStructure();

        return parent::prepare($clear);
    }

	/**
     * Prepare the SELECT component of the SQL query
     *
     * @todo quote fields and tables?
	 */
	protected function prepareSelect()
    {
        $tables = $this->_buildTable();
        $fields = $this->_buildQuery();
        $where = $this->_buildWhere();
        $joins = $this->_buildJoins();
        $limit = $this->_buildLimit();
        $order = $this->_buildOrder();
        $group_by = $this->_buildGroup();

        $sql = "SELECT $fields FROM ($tables) $joins $where $group_by $order $limit";

        return $sql;
	}

    protected function prepareInsertSelect()
    {
        return $this->prepareInsert();
    }

	/**
     * Prepare the DELETE component of the SQL query
     *
     * @todo quote fields and tables?
	 */
    protected function prepareDelete()
    {
        $table = $this->_buildTable(true);
        $where = $this->_buildWhere();
        $limit = $this->_buildLimit();

        $sql = "DELETE FROM $table $where $limit";

        return $sql;
    }

    protected function prepareInsert()
    {
        $table = $this->_buildTable(true);

        $fieldList = array_keys($this->value_list);
        $fields = implode(',', $fieldList);
        $fieldValues = array_values($this->value_list);
        $values = implode(',', $fieldValues);

        $sql = "INSERT INTO $table ($fields) VALUES ($values)";

        return $sql;
    }

    protected function prepareReplace()
    {
        //NOTE: This functions the same as prepareInsert..
        $table = $this->_buildTable(true);

        $fieldList = array_keys($this->value_list);
        $fields = implode(',', $fieldList);
        $fieldValues = array_values($this->value_list);
        $values = implode(',', $fieldValues);

        $sql = "REPLACE INTO $table ($fields) VALUES ($values)";

        return $sql;
    }

    protected function prepareUpdate()
    {
        $table = $this->_buildTable(true);
        $where = $this->_buildWhere();

        foreach($this->update_list as $field => $value) {
            $fieldValues[] = "$field = $value";
        }
        $field_values = implode(',', $fieldValues);
        
        $sql = "UPDATE $table SET $field_values $where";

        return $sql;
    }

	/**
     * Adds a table to the query
     *
	 * @param	$name	Name of table, without prefix
	 * @param	$alias	Alias for use in query/where/group clauses
	 */
	public function addTable($table, $alias = '')
    {
        $alias = ('' == $alias) ? $table : $alias;
        $this->_tables[$alias] = $table;
	}

    public function addQuery($field)
    {
        if('' != $field) {
            $this->_fields[] = $field;
        }
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
     * Sets a result limit on the query
     *
     * @param type $limit
     */
    public function setLimit($limit, $offset = 0)
    {
        if ((int) $limit > 0) {
            $this->_limit = (int) $limit;
        }

        $this->_offset = max(0, (int) $offset);
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
     * Allows you to filter query results by a aggregate field, can be used multiple times
     *
     * @param type  $field
     */
    public function addHaving($field = '')
    {
        if('' != $field) {
            $this->_having[] = $field;
        }
    }

	/** Add a JOIN
	 *
	 * Adds a join to the query.  This only implements a left join by default
     *   but you can either specify others or use one of the shorthand methods
     *   below.
	 *
	 * @param	$table	Name of table (without prefix)
	 * @param	$alias	Alias to use instead of table name (required).
	 * @param	$condition	Join condition (e.g. 'a.id = b.other_id')
	 *				or array of join fieldnames, e.g. array('id', 'name);
	 *				Both are correctly converted into a join clause.
	 */
    public function addJoin($table, $alias, $condition, $type = 'left')
    {
        $this->join[] = array('table' => $table, 'alias' => $alias,
                            'condition' => $condition, 'type' => $type);
    }

	/**
     * Works the same as addJoin except defaults to an INNER join.
	 */
    public function innerJoin($table, $alias, $join)
    {
        $this->addJoin($table, $alias, $join, 'inner');
    }

	/**
     * Works the same as addJoin except defaults to a LEFT join.
	 */
    public function leftJoin($table, $alias, $join)
    {
        $this->addJoin($table, $alias, $join, 'left');
    }

	/**
     * Works the same as addJoin except defaults to a RIGHT join.
	 */
    public function rightJoin($table, $alias, $join)
    {
        $this->addJoin($table, $alias, $join, 'right');
    }

    protected function _buildQuery()
    {
        $simple = array();

        if (count($this->_fields)) {
            foreach($this->_fields as $field) {
                if (is_array($field)) {
                    foreach($field as $subfield) {
                        $simple[] = $subfield;
                    }
                } else {
                    $simple[] = $field;
                }
            }
        }

        return count($simple) ? implode(',' , $simple) : '*';
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

    /**
     * This combines all the having clauses into a single statement. I don't
     *   like the nested loops but since this array can only be two levels deep,
     *   recursion is probably excessive.
     * And yes, it looks the same as _buildWhere() because it basically is.
     *
     * @return type
     */
    protected function _buildHaving()
    {
        $simple = array();

        if (count($this->_having)) {
            foreach($this->_having as $having) {
                if (is_array($having)) {
                    foreach($having as $subhaving) {
                        $simple[] = $subhaving;
                    }
                } else {
                    $simple[] = $having;
                }
            }
        }

        return count($simple) ? ' HAVING ' . implode(' AND ' , $simple) : '';
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

    protected function _buildLimit()
    {
        return ($this->_limit) ?
            ' LIMIT ' . (int) $this->_limit . ', ' . (int) $this->_offset : '';
    }

    protected function _buildOrder()
    {
        return count($this->_order_by) ? 'ORDER BY ' . implode(',' , $this->_order_by) : '';
    }

    protected function _buildGroup()
    {
        return count($this->_group_by) ? 'GROUP BY ' . implode(',' , $this->_group_by) : '';
    }

    protected function _buildTable($first_table = false)
    {
        if ($first_table) {
            $tables = array_shift($this->_tables);
        } else {
            $aliases = array();
            foreach($this->_tables as $alias => $table) {
                $aliases[] = "($table AS $alias)";
            }
            $tables = implode(',', $aliases);
        }

        return $tables;
    }

    public function dbfnNowWithTZ()
    {
        $df = 'Y-m-d H:i:s';
        $defaultTZ = 'Europe/London';
        $systemTZ = new DateTimeZone($defaultTZ);
        $ts = new DateTime();
        $ts->setTimezone($systemTZ);

        return $ts->format($df);
    }

	/**
     * Generates the token representing the 'now' datetime
	 */
	public function dbfnNow()
    {
        return 'NOW()';
	}

	/**
     * Add a date difference clause and name the result
	 *
	 * @param	$date1			This is the starting date
	 * @param	$date2			This is the ending date
	 */
	public function dbfnDateDiff($date1 = '', $date2 = '')
    {
		$date1 = ($date1 == '') ? $this->dbfnNow() : $date1;
		$date2 = ($date2 == '') ? $this->dbfnNow() : $date2;

        return 'DATEDIFF(' . $date1 . ', ' . $date2 . ')';
	}

	/** Adds a given unit interval to a date
	 *
	 * @param	$date			This is the date we want to add to
	 * @param	$interval		This is how much units we will be adding to the date
	 * @param	$unit			This is the type of unit we are adding to the date
	 */
	public function dbfnDateAdd($date, $interval = 0, $unit = 'DAY')
    {
		$date = ($date == '') ? $this->dbfnNow() : $date;

        return 'DATE_ADD(' . $date . ', INTERVAL ' . $interval . ' ' . $unit . ')';
	}

    /**
     * This is simply a wrapper for the adodb FetchRow method and doesn't add
     *   any new real functionality. I have no clue why you'd do this at the
     *   moment..
     *
     * @return  array
     */
    public function fetchRow()
    {
        return $this->_query_id->FetchRow();
    }

    /**
     * This gives you the first value from a single row
     *
     * @return Value of the row column
     */
    public function loadResult()
    {
        $result = false;

        if($this->exec()) {
            $row = $this->fetchRow();
            $result = ($row) ? $row[0] : $result;
        }

        return $result;
    }

    /**
     * Load database results as an array of associative arrays
     *
     * @param   $maxrows        Maximum number of rows to return
     * @param   $index          Can be used to set the keys of the resulting arrays, useful to find records by primary key
     * @return                  array of associative arrays containing row field values
     */
    public function loadList($maxrows = -1, $index = -1)
    {
        $results = array();
        if ($this->exec()) {
            $actualRows = $this->_query_id->NumRows();
            $upperLimit = ($maxrows > 0) ? min($maxrows, $actualRows) : $actualRows;
            $rows = $this->_query_id->GetAll($upperLimit);
            foreach ($rows as $row) {
                if (-1 == $index) {
                    $results[] = $row;
                } else {
                    $results[$row[$index]] = $row;
                }
            }
        }
        return $results;
    }

    /**
     * This is simply a wrapper for the adodb qstr method and doesn't add
     *   any new real functionality. I have no clue why you'd do this at the
     *   moment..
     *
     * @param   $string         The string to be quoted
     * @return  $string         The quoted string
     */
    public function quote($string)
    {
        return $this->_db->qstr($string);
    }

    /**
     * EVERYTHING BELOW HERE IS DEPRECATED.
     *
     * Previously it was all in the oldQuery class but since it uses protected functions from this class, it doesn't make sense.
     */

    /**
     * @deprecated
     */
    public function make_where_clause($where_clause) {
        trigger_error("make_order_clause has been deprecated in v3.0.", E_USER_NOTICE );
        $this->_convertFromOldStructure();

        return $this->_buildWhere();
    }

    /**
     * @deprecated
     */
    public function make_order_clause($order_clause) {
        trigger_error("make_order_clause has been deprecated in v3.0.", E_USER_NOTICE );
        $this->_convertFromOldStructure();

        return $this->_buildOrder();
    }

    /**
     * @deprecated
     */
    public function make_group_clause($group_clause) {
        trigger_error("make_group_clause has been deprecated in v3.0.", E_USER_NOTICE );
        $this->_convertFromOldStructure();

        return $this->_buildGroup();
    }

    /**
     * @deprecated
     */
    public function setPageLimit($page = 0, $pagesize = 0) {
        trigger_error(__FUNCTION__ . " has been deprecated in v3.0.", E_USER_NOTICE );
        if ($page == 0) {
            global $tpl;
            $page = $tpl->page;
        }

        if ($pagesize == 0) {
            $pagesize = w2PgetConfig('page_size');
        }

        $this->setLimit($pagesize, ($page - 1) * $pagesize);
    }

    /**
     * @deprecated
     */
    public function make_limit_clause($limit, $offset) {
        trigger_error(__FUNCTION__ . " has been deprecated in v3.0.", E_USER_NOTICE );

        $this->setLimit($limit, $offset);

        return $this->_buildLimit();
    }

    /**
     * @deprecated
     */
    public function make_having_clause($having_clause) {
        trigger_error(__FUNCTION__ . " has been deprecated in v3.0.", E_USER_NOTICE );

        if (is_array($having_clause)) {
            foreach($having_clause as $having) {
                $this->addHaving($having);
            }
        }
        if (is_string($having_clause)) {
            $this->addHaving($having_clause);
        }

        return $this->_buildHaving();
    }

    /** Create a join condition based upon supplied fields.
     *
     * @param	$join_clause	Either string or array of subclauses.
     * @return SQL JOIN condition as a string.
     */
    public function make_join($join_clause) {
        trigger_error(__FUNCTION__ . " has been deprecated in v3.0.", E_USER_NOTICE );

        if (is_array($join_clause)) {
            foreach($join_clause as $join) {
                //$this->addHaving($having);
                $this->addJoin($join['table'], $join['alias'], $join['condition'], $join['type']);
            }
        }

        return $this->_buildJoins();
    }

    /**
     * @deprecated
     */
    public function quote_db($string) {
        trigger_error(__FUNCTION__ . " has been deprecated in v3.0.", E_USER_NOTICE );

        return $this->quote($string);
    }
}