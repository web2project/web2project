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

    /**
     * This method is used to map the oldQuery data structure to the Query data
     *   structure. It was generated by using print_r($this) to see the contents.
     */
    protected function _convertFromOldStructure()
    {
        $this->_tables = $this->table_list;
        $this->_fields = $this->query;
        $this->_where  = $this->where;
        $this->_joins  = $this->join;
    }

	/**
     * Prepare the SELECT component of the SQL query
     *
     * @todo quote fields and tables?
     * @todo add ORDER BY
     * @todo add GROUP BY
	 */
	protected function prepareSelect()
    {
        $this->_convertFromOldStructure();

        $fields = count($this->_fields) ? implode(',' , $this->_fields) : '*';
        $tables = implode(',', $this->_tables);
        $where = count($this->_where) ? 'WHERE ' . implode(' AND ' , $this->_where) : '';

        $joins = '';
        if (count($this->join)) {
            foreach ($this->join as $join) {
                $joins .= strtoupper($join['type']) . " JOIN " . $join['table'] . " AS " . $join['alias'] . " ON " . $join['condition'] . " ";
            }
        }

        $sql = "SELECT $fields FROM $tables $joins $where";

        return $sql;
	}
}