<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly');
}

/* Copyright 2003,2004 Adam Donnison <adam@saki.com.au>

This file is part of the collected works of Adam Donnison.

This is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

require_once W2P_BASE_DIR . '/lib/adodb/adodb.inc.php';

define('QUERY_STYLE_ASSOC', ADODB_FETCH_ASSOC);
define('QUERY_STYLE_NUM', ADODB_FETCH_NUM);
define('QUERY_STYLE_BOTH', ADODB_FETCH_BOTH);

/**
 * Database query class
 *
 * Container for creating prefix-safe queries.  Allows build up of
 * a select statement by adding components one at a time.
 *
 * @version	$Rev$
 * @package	web2Project
 * @access	public
 * @author	Adam Donnison <adam@saki.com.au>
 * @license	GPL version 2 or later.
 * @copyright	(c) 2003 Adam Donnison
 */
class DBQuery {
	/**< Contains the query after it has been built. */
	public $query;
	/**< Array of tables to be queried */
	public $table_list;
	/**< WHERE component of the query */
	public $where;
	/**< ORDER BY component of the query */
	public $order_by;
	/**< GROUP BY component of the query */
	public $group_by;
	/**< HAVING component of the query */
	public $having;
	/**< LIMIT component of the query */
	public $limit;
	/**< offset of the LIMIT component */
	public $offset;
	/**< JOIN component of the query */
	public $join;
	/**< Query type eg. 'select', 'update' */
	public $type;
	/**< Array of fields->values to update */
	public $update_list;
	/**< Array of values used in INSERT or REPLACE statements */
	public $value_list;
	/**< Name of the table to create */
	public $create_table;
	/**< Array containing information about the table definition */
	public $create_definition;
	/**< Boolean to count rows in query */
	public $include_count = false;
	/**< Internal string, table prefix, prepended to all queries */
	public $_table_prefix;
	/**< Handle to the query result */
	public $_query_id = null;
	/**< Use the old style of fetch mode with ADODB */
	public $_old_style = null;
	/**< Handle to the database connection */
	public $_db = null;

	/**
	 * Array of db function names
	 * @access private
	 * @var array
	 */
	private $_db_funcs;

	/** DBQuery constructor
	 *
	 * @param $prefix Database table prefix - will be appended to all web2project table names
	 * @param $query_db Database type
	 */
	public function DBQuery($prefix = null, $query_db = null) {
		global $db;

		if (isset($prefix)) {
			$this->_table_prefix = $prefix;
		} else {
			$this->_table_prefix = w2PgetConfig('dbprefix', '');
		}
		$this->_db = isset($query_db) ? $query_db : $db;
		$this->_db_funcs = array($this->dbfnNow());

		$this->clear();
	}

	/** Clear the current query and all set options
	 */
	public function clear() {
		global $ADODB_FETCH_MODE;
		if (isset($this->_old_style)) {
			$ADODB_FETCH_MODE = $this->_old_style;
			$this->_old_style = null;
		}
		$this->type = 'select';
		$this->query = null;
		$this->table_list = null;
		$this->where = null;
		$this->order_by = null;
		$this->group_by = null;
		$this->limit = null;
		$this->offset = -1;
		$this->join = null;
		$this->value_list = null;
		$this->update_list = null;
		$this->create_table = null;
		$this->create_definition = null;
		if ($this->_query_id) {
			$this->_query_id->Close();
		}
		$this->_query_id = null;
	}

	public function clearQuery() {
		if ($this->_query_id) {
			$this->_query_id->Close();
		}
		$this->_query_id = null;
	}

	/** Get database specific SQL used to concatenate strings.
	 * @return String containing SQL to concatenate supplied strings
	 */
	public function concat() {
		$arr = func_get_args();
		$conc_str = call_user_func_array(array(&$this->_db, 'Concat'), $arr);
		return $conc_str;
	}

	/** Get database specific SQL used to check for null values.
	 *
	 * Calls the ADODB IfNull method
	 * @return String containing SQL to check for null field value
	 */
	public function ifNull($field, $nullReplacementValue) {
		return $this->_db->IfNull($field, $nullReplacementValue);
	}

	/** Add item to an internal associative array
	 *
	 * Used internally with DBQuery
	 *
	 * @param	$varname	Name of variable to add/create
	 * @param	$name	Data to add
	 * @param	$id	Index to use in array.
	 */
	public function addMap($varname, $name, $id) {
		if (!isset($this->$varname)) {
			$this->$varname = array();
		}
		if (isset($id)) {
			$this->{$varname}[$id] = $name;
		} else {
			$this->{$varname}[] = $name;
		}
	}

	/** Add a table to the query
	 *
	 * A table is normally addressed by an
	 * alias.  If you don't supply the alias chances are your code will
	 * break.  You can add as many tables as are needed for the query.
	 * E.g. addTable('something', 'a') will result in an SQL statement
	 * of {PREFIX}table as a.
	 * Where {PREFIX} is the system defined table prefix.
	 *
	 * @param	$name	Name of table, without prefix.
	 * @param	$id	Alias for use in query/where/group clauses.
	 */
	public function addTable($name, $id = null) {
		$this->addMap('table_list', $name, ($id ? $id : $name));
	}

	/** Add a clause to an internal array
	 *
	 * Checks to see variable exists first.
	 * then pushes the new data onto the end of the array.
	 * @param $clause the type of clause to add
	 * @param $value the clause value
	 * @param $check_array defaults to true, iterates through each element in $value and adds them seperately to the clause
	 */
	public function addClause($clause, $value, $check_array = true) {
		//dprint(__file__, __line__, 8, "Adding '$value' to $clause clause");
		if (!isset($this->$clause)) {
			$this->$clause = array();
		}
		if ($check_array && is_array($value)) {
			foreach ($value as $v) {
				array_push($this->$clause, $v);
			}
		} else {
			array_push($this->$clause, $value);
		}
	}

	/** Add the select part (fields, functions) to the query
	 *
	 * E.g. '*', or 'a.*'
	 * or 'a.field, b.field', etc.  You can call this multiple times
	 * and it will correctly format a combined query.
	 *
	 * @param	$query	Query string to use.
	 */
	public function addQuery($query) {
		$this->addClause('query', $query);
	}

	/** Insert a value into the database
	 * @param $field The field to insert the value into
	 * @param $value The specified value
	 * @param $set Defaults to false. If true will check to see if the fields or values supplied are comma delimited strings instead of arrays
	 * @param $func Defaults to false. If true will not use quotation marks around the value - to be used when the value being inserted includes a function
	 */
	public function addInsert($field, $value = null, $set = false, $func = false) {
		if (is_array($field) && $value == null) {
			foreach ($field as $f => $v) {
				$this->addMap('value_list', $f, $v);
			}
		} elseif ($set) {
			if (is_array($field)) {
				$fields = $field;
			} else {
				$fields = explode(',', $field);
			}

			if (is_array($value)) {
				$values = $value;
			} else {
				$values = explode(',', $value);
			}

			for ($i = 0, $i_cmp = count($fields); $i < $i_cmp; $i++) {
				$this->addMap('value_list', $this->quote($values[$i]), $fields[$i]);
			}
		} else
			if (!$func) {
				$this->addMap('value_list', $this->quote($value), $field);
			} else {
				$this->addMap('value_list', $value, $field);
			}
			$this->type = 'insert';
	}

	public function addInsertSelect($table) {
		$this->create_table = $table;
		$this->type = 'insert_select';
	}

	// implemented addReplace() on top of addInsert()
	/** Insert a value into the database, to replace an existing row.
	 * @param $field The field to insert the value into
	 * @param $value The specified value
	 * @param $set Defaults to false. If true will check to see if the fields or values supplied are comma delimited strings instead of arrays
	 * @param $func Defaults to false. If true will not use quotation marks around the value - to be used when the value being inserted includes a function
	 */
	public function addReplace($field, $value, $set = false, $func = false) {
		$this->addInsert($field, $value, $set, $func);
		$this->type = 'replace';
	}

	/** Update a database value
	 * @param $field The field to update
	 * @param $value The value to set $field to
	 * @param $set Defaults to false. If true will check to see if the fields or values supplied are comma delimited strings instead of arrays
	 * @param $func Defaults to false. If true will not use quotation marks around the value - to be used when the value being updated includes a function
	 */
	public function addUpdate($field, $value = null, $set = false, $func = false) {
		if (is_array($field) && $value == null) {
			foreach ($field as $f => $v) {
				$this->addMap('update_list', $f, $v);
			}
		} elseif ($set) {
			if (is_array($field)) {
				$fields = $field;
			} else {
				$fields = explode(',', $field);
			}

			if (is_array($value)) {
				$values = $value;
			} else {
				$values = explode(',', $value);
			}

			for ($i = 0, $i_cmp = count($fields); $i < $i_cmp; $i++) {
				$this->addMap('update_list', $values[$i], $fields[$i]);
			}
		} else {
			if (!$func) {
				$this->addMap('update_list', $this->quote($value), $field);
			} else {
				$this->addMap('update_list', $value, $field);
			}
		}
		$this->type = 'update';
	}

	/** Create a database table
	 * @param $table the name of the table to create
	 */
	public function createTable($table, $def = null) {
		$this->type = 'createPermanent';
		$this->create_table = $table;
		if ($def) {
			$this->create_definition = $def;
		}
	}

	public function createDatabase($database) {
		$dict = NewDataDictionary($this->_db, w2PgetConfig('dbtype'));
		$dict->CreateDatabase($database);
	}

	public function DDcreateTable($table, $def, $opts) {
		$dict = NewDataDictionary($this->_db, w2PgetConfig('dbtype'));
		$query_array = $dict->ChangeTableSQL(w2PgetConfig('dbprefix') . $table, $def, $opts);
		//returns 0 - failed, 1 - executed with errors, 2 - success
		return $dict->ExecuteSQLArray($query_array);
	}

	public function DDcreateIndex($name, $table, $cols, $opts) {
		$dict = NewDataDictionary($this->_db, w2PgetConfig('dbtype'));
		$query_array = $dict->CreateIndexSQL($name, $table, $cols, $opts);
		//returns 0 - failed, 1 - executed with errors, 2 - success
		return $dict->ExecuteSQLArray($query_array);
	}

	/** Create a temporary database table
	 * @param $table the name of the temporary table to create.
	 */
	public function createTemp($table) {
		$this->type = 'createTemporary';
		$this->create_table = $table;
	}

	/** Drop a table from the database
	 *
	 * Use dropTemp() to drop temporary tables
	 * @param $table the name of the table to drop.
	 */
	public function dropTable($table) {
		$this->type = 'drop';
		$this->create_table = $table;
	}

	/** Drop a temporary table from the database
	 * @param $table the name of the temporary table to drop
	 */
	public function dropTemp($table) {
		$this->type = 'drop';
		$this->create_table = $table;
	}

	/** Alter a database table
	 * @param $table the name of the table to alter
	 */
	public function alterTable($table) {
		$this->create_table = $table;
		$this->type = 'alter';
	}

	/** Add a field definition for usage with table creation/alteration
	 * @param $name The name of the field
	 * @param $type The type of field to create
	 */
	public function addField($name, $type) {
		if (!is_array($this->create_definition)) {
			$this->create_definition = array();
		}
		$this->create_definition[] = array('action' => 'ADD', 'type' => '', 'spec' => $name . ' ' . $type);
	}

	/**
	 * Alter a field definition for usage with table alteration
	 * @param $name The name of the field
	 * @param $type The type of the field
	 */
	public function alterField($name, $type) {
		if (!is_array($this->create_definition)) {
			$this->create_definition = array();
		}
		$this->create_definition[] = array('action' => 'CHANGE', 'type' => '', 'spec' => $name . ' ' . $name . ' ' . $type);
	}

	/** Drop a field from table definition or from an existing table
	 * @param $name The name of the field to drop
	 */
	public function dropField($name) {
		if (!is_array($this->create_definition)) {
			$this->create_definition = array();
		}
		$this->create_definition[] = array('action' => 'DROP', 'type' => '', 'spec' => $name);
	}

	/** Add an index. Fields should be separated by commas to create a multi-field index
	 */
	public function addIndex($name, $type) {
		if (!is_array($this->create_definition)) {
			$this->create_definition = array();
		}
		$this->create_definition[] = array('action' => 'ADD', 'type' => 'INDEX', 'spec' => '(' . $name . ') ' . $type);
	}

	/** Add a primary key attribute. Fields should be separated by commas to create a multi-field primary key
	 */
	public function addPrimary($name) {
		if (!is_array($this->create_definition)) {
			$this->create_definition = array();
		}
		$this->create_definition[] = array('action' => 'ADD', 'type' => 'PRIMARY KEY', 'spec' => '(' . $name . ')');
	}

	/** Drop an index
	 */
	public function dropIndex($name) {
		if (!is_array($this->create_definition)) {
			$this->create_definition = array();
		}
		$this->create_definition[] = array('action' => 'DROP', 'type' => 'INDEX', 'spec' => $name);
	}

	/** Remove a primary key attribute from a field
	 */
	public function dropPrimary() {
		if (!is_array($this->create_definition)) {
			$this->create_definition = array();
		}
		$this->create_definition[] = array('action' => 'DROP', 'type' => 'PRIMARY KEY', 'spec' => '');
	}

	/** Set a table creation definition from supplied array
	 * @param $def Array containing table definition
	 */
	public function createDefinition($def) {
		$this->create_definition = $def;
	}

	public function setDelete($table) {
		$this->type = 'delete';
		$this->addMap('table_list', $table, null);
	}

	/** Add a WHERE sub clause
	 *
	 * The where clause can be built up one
	 * part at a time and the resultant query will put in the 'and'
	 * between each component.
	 *
	 * Make sure you use table aliases.
	 *
	 * @param	$query	Where subclause to use, not including WHERE keyword
	 */
	public function addWhere($query) {
		if (isset($query)) {
			$this->addClause('where', $query);
		}
	}

	/** Add a JOIN condition
	 *
	 * Add a join condition to the query.  This only implements
	 * left join, however most other joins are either synonymns or
	 * can be emulated with where clauses.
	 *
	 * @param	$table	Name of table (without prefix)
	 * @param	$alias	Alias to use instead of table name (required).
	 * @param	$join	Join condition (e.g. 'a.id = b.other_id')
	 *				or array of join fieldnames, e.g. array('id', 'name);
	 *				Both are correctly converted into a join clause.
	 */
	public function addJoin($table, $alias, $join, $type = 'left') {
		$var = array('table' => $table, 'alias' => $alias, 'condition' => $join, 'type' => $type);

		$this->addClause('join', $var, false);
	}

	/** Add a left join condition
	 *
	 * Helper method to add a left join
	 * @see addJoin()
	 * @param $table Name of table (without prefix)
	 * @param $alias Alias to use instead of table name
	 * @param $join Join condition
	 */
	public function leftJoin($table, $alias, $join) {
		$this->addJoin($table, $alias, $join, 'left');
	}

	/** Add a right join condition
	 *
	 * Helper method to add a right join
	 * @see addJoin()
	 * @param $table Name of table (without prefix)
	 * @param $alias Alias to use instead of table name
	 * @param $join Join condition
	 */
	public function rightJoin($table, $alias, $join) {
		$this->addJoin($table, $alias, $join, 'right');
	}

	/** Add an inner join condition
	 *
	 * Helper method to add an inner join
	 * @see addJoin()
	 * @param $table Name of table (without prefix)
	 * @param $alias Alias to use instead of table name
	 * @param $join Join condition
	 */
	public function innerJoin($table, $alias, $join) {
		$this->addJoin($table, $alias, $join, 'inner');
	}

	/** Add an ORDER BY clause
	 *
	 * Again, only the fieldname is required, and
	 * it should include an alias if a table has been added.
	 * May be called multiple times.
	 *
	 * @param	$order	Order by field.
	 */
	public function addOrder($order) {
		if (isset($order)) {
			$this->addClause('order_by', $order);
		}
	}

	/** Add a GROUP BY clause
	 *
	 * Only the fieldname is required.
	 * May be called multiple times.  Use table aliases as required.
	 *
	 * @param	$group	Field name to group by.
	 */
	public function addGroup($group) {
		$this->addClause('group_by', $group);
	}

	/** Add a HAVING sub clause
	 *
	 * The having clause can be built up one
	 * part at a time and the resultant query will put in the 'and'
	 * between each component.
	 *
	 * Remember:
	 * "the SQL standard requires that HAVING must reference only columns in the
	 * GROUP BY clause or columns used in aggregate functions"
	 *
	 * @param	$query	HAVING subclause to use, not including HAVING keyword
	 */
	public function addHaving($query) {
		$this->addClause('having', $query);
	}

	/** Generates the token representing the 'now' datetime
	 *
	 * The 'now' datetime is represented just a bit differently from database
	 * engine to engine.  Therefore this method checks to see what database is
	 * being used and returns the string to calculate the value.  It does *not*
	 * calculate the value itself.
	 */
	public function dbfnNow() {
		$dbType = strtolower(trim(w2PgetConfig('dbtype')));

		switch ($dbType) {
			case 'oci8':
			case 'oracle':
				return 'current_date';
            case 'mysql':
			default:
				return 'NOW()';
		}
	}

	/** Add a date difference clause and name the result
	 *
	 * Each database engine represents date math just a little bit differently.
	 * Therefore, this method checks to see what database is being used and adds a
	 * date difference appropriately.
	 *
	 * @param	$date1			This is the starting date
	 * @param	$date2			This is the ending date
	 */
	public function dbfnDateDiff($date1 = '', $date2 = '') {
		$dbType = strtolower(trim(w2PgetConfig('dbtype')));

		$date1 = ($date1 == '') ? $this->dbfnNow() : $date1;
		$date2 = ($date2 == '') ? $this->dbfnNow() : $date2;

		switch ($dbType) {
			case 'oci8':
			case 'oracle':
				return $date1 . ' - ' . $date2;
            case 'mysql':
			default:
				return 'DATEDIFF(' . $date1 . ', ' . $date2 . ')';
		}
	}

	/** Adds a given unit interval to a date
	 *
	 * @param	$date			This is the date we want to add to
	 * @param	$interval		This is how much units we will be adding to the date
	 * @param	$unit			This is the type of unit we are adding to the date
	 */
	public function dbfnDateAdd($date, $interval = 0, $unit = 'DAY') {
		$dbType = strtolower(trim(w2PgetConfig('dbtype')));

		$date = ($date == '') ? $this->dbfnNow() : $date;

		switch ($dbType) {
			case 'oci8':
			case 'oracle':
				return '(' . $date . ' + interval \'' . $unit . '\' ' . $interval . ')';
            case 'mysql':
			default:
				return 'DATE_ADD(' . $date . ', INTERVAL ' . $interval . ' ' . $unit . ')';
		}
	}

	/** Set a row limit on the query
	 *
	 * Set a limit on the query.  This is done in a database-independent
	 * fashion.
	 *
	 * @param	$limit	Number of rows to limit.
	 * @param	$start	First row to start extraction(row offset).
	 */
	public function setLimit($limit, $start = -1) {
		$this->limit = $limit;
		$this->offset = $start;
	}

	/**
	 * Set include count feature, grabs the count of rows that
	 * would have been returned had no limit been set.
	 */
	public function includeCount() {
		$this->include_count = true;
	}
	/** Set a limit on the query based on pagination.
	 *
	 * @param $page     the current page
	 * @param $pagesize the size of pages
	 */
	public function setPageLimit($page = 0, $pagesize = 0) {
		if ($page == 0) {
			global $tpl;
			$page = $tpl->page;
		}

		if ($pagesize == 0) {
			$pagesize = w2PgetConfig('page_size');
		}

		$this->setLimit($pagesize, ($page - 1) * $pagesize);
	}

	/** Prepare query for execution
	 * @param $clear Boolean, Clear the query after it has been executed
	 * @return String containing the SQL statement
	 */
	public function prepare($clear = false) {
		switch ($this->type) {
			case 'select':
				$q = $this->prepareSelect();
				break;
			case 'update':
				$q = $this->prepareUpdate();
				break;
			case 'insert':
				$q = $this->prepareInsert();
				break;
			case 'insert_select':
				$s = $this->prepareSelect();
				$q = 'INSERT INTO ' . $this->_table_prefix . $this->create_table;
				$q .= ' ' . $s;
				break;
			case 'replace':
				$q = $this->prepareReplace();
				break;
			case 'delete':
				$q = $this->prepareDelete();
				break;
			case 'createTemporary': // Create a temporary table
				$s = $this->prepareSelect();
				$q = 'CREATE TEMPORARY TABLE ' . $this->_table_prefix . $this->create_table;
				if (!empty($this->create_definition)) {
					$q .= ' ' . $this->create_definition;
                }
				$q .= ' ' . $s;
				break;
			case 'alter':
				$q = $this->prepareAlter();
				break;
			case 'createPermanent': // Create a permanent table
				$q = 'CREATE TABLE ' . $this->_table_prefix . $this->create_table;
				if (!empty($this->create_definition)) {
					$q .= ' ' . $this->create_definition;
				}
				break;
			case 'drop':
				$q = 'DROP TABLE IF EXISTS ' . $this->_table_prefix . $this->create_table;
				break;
		}
		if ($clear) {
			$this->clear();
		}
		return $q;
		dprint(__file__, __line__, 2, $q);
	}

	/** Prepare the SELECT component of the SQL query
	 */
	public function prepareSelect() {
		switch (strtolower(trim(w2PgetConfig('dbtype')))) {
			case 'oci8':
			case 'oracle':
				$q = 'SELECT ';
				if ($this->include_count) {
					//$q .= 'SQL_CALC_FOUND_ROWS ';
				}
				if (isset($this->query)) {
					if (is_array($this->query)) {
						$inselect = false;
						$q .= implode(',', $this->query);
					} else {
						$q .= $this->query;
					}
				} else {
					$q .= '*';
				}
				$q .= ' FROM ';
				if (isset($this->table_list)) {
					if (is_array($this->table_list)) {
						$intable = false;
						foreach ($this->table_list as $table_id => $table) {
							if ($intable) {
								$q .= ',';
							} else {
								$intable = true;
							}
							$q .= $this->_table_prefix . $table;
							if (!is_numeric($table_id)) {
								$q .= ' ' . $table_id;
							}
						}
					} else {
						$q .= $this->_table_prefix . $this->table_list;
					}
				} else {
					return false;
				}
				$q .= $this->make_join($this->join);
				$q .= $this->make_where_clause($this->where);
				$q .= $this->make_group_clause($this->group_by);
				$q .= $this->make_having_clause($this->having);
				$q .= $this->make_order_clause($this->order_by);
				$q .= $this->make_limit_clause($this->limit, $this->offset);
				return $q;
				break;
			default:
				//mySQL
				$q = 'SELECT ';
				if ($this->include_count) {
					$q .= 'SQL_CALC_FOUND_ROWS ';
				}
				if (isset($this->query)) {
					if (is_array($this->query)) {
						$inselect = false;
						$q .= implode(',', $this->query);
					} else {
						$q .= $this->query;
					}
				} else {
					$q .= '*';
				}
				$q .= ' FROM (';
				if (isset($this->table_list)) {
					if (is_array($this->table_list)) {
						$intable = false;
						/* added brackets for MySQL > 5.0.12 compatibility
						 ** patch #1358907 submitted to sf.net on 2005-11-17 04:12 by ilgiz
						 */
						$q .= '(';
						foreach ($this->table_list as $table_id => $table) {
							if ($intable) {
								$q .= ',';
							} else {
								$intable = true;
							}
							$q .= $this->quote_db($this->_table_prefix . $table);
							if (!is_numeric($table_id)) {
								$q .= ' AS ' . $table_id;
							}
						}
						/* added brackets for MySQL > 5.0.12 compatibility
						 ** patch #1358907 submitted to sf.net on 2005-11-17 04:12 by ilgiz
						 */
						$q .= ')';
					} else {
						$q .= $this->_table_prefix . $this->table_list;
					}
					$q .= ')';
				} else {
					return false;
				}
				$q .= $this->make_join($this->join);
				$q .= $this->make_where_clause($this->where);
				$q .= $this->make_group_clause($this->group_by);
				$q .= $this->make_having_clause($this->having);
				$q .= $this->make_order_clause($this->order_by);
				$q .= $this->make_limit_clause($this->limit, $this->offset);
				return $q;
		}
	}

	/** Prepare the UPDATE component of the SQL query
	 */
	public function prepareUpdate() {
		// You can only update one table, so we get the table detail
		switch (strtolower(trim(w2PgetConfig('dbtype')))) {
			case 'oci8':
			case 'oracle':
				$q = 'UPDATE ';
				if (isset($this->table_list)) {
					if (is_array($this->table_list)) {
						reset($this->table_list);
						// Grab the first record
						list($key, $table) = each($this->table_list);
					} else {
						$table = $this->table_list;
					}
				} else {
					return false;
				}
				$q .= $this->_table_prefix . $table;

				$q .= ' SET ';
				$sets = '';
				foreach ($this->update_list as $field => $value) {
					if ($sets) {
						$sets .= ', ';
					}
					$sets .= $field . ' = ' . $this->quote($value);
				}
				$q .= $sets;
				$q .= $this->make_where_clause($this->where);
				return $q;
				break;
			default:
				//mySQL
				$q = 'UPDATE ';
				if (isset($this->table_list)) {
					if (is_array($this->table_list)) {
						reset($this->table_list);
						// Grab the first record
						list($key, $table) = each($this->table_list);
					} else {
						$table = $this->table_list;
					}
				} else {
					return false;
				}
				$q .= $this->quote_db($this->_table_prefix . $table);

				$q .= ' SET ';
				$sets = '';
				foreach ($this->update_list as $field => $value) {
					if ($sets) {
						$sets .= ', ';
					}
					$sets .= $this->quote_db($field) . ' = ' . $value;
				}
				$q .= $sets;
				$q .= $this->make_where_clause($this->where);
				return $q;
		}
	}

	/** Prepare the INSERT component of the SQL query
	 */
	public function prepareInsert() {
		switch (strtolower(trim(w2PgetConfig('dbtype')))) {
			case 'oci8':
			case 'oracle':
				$q = 'INSERT INTO ';
				if (isset($this->table_list)) {
					if (is_array($this->table_list)) {
						reset($this->table_list);
						// Grab the first record
						list($key, $table) = each($this->table_list);
					} else {
						$table = $this->table_list;
					}
				} else {
					return false;
				}
				$q .= $this->_table_prefix . $table;

                $quotedFieldnames = array_map(array($this, 'quote_db'), array_keys($this->value_list));
                $fieldlist = implode(',', $quotedFieldnames);
                $valuelist = implode(',', $this->value_list);

				$q .= '(' . $fieldlist . ') VALUES (' . $valuelist . ')';
				return $q;
				break;
			default:
				//mySQL
				$q = 'INSERT INTO ';
				if (isset($this->table_list)) {
					if (is_array($this->table_list)) {
						reset($this->table_list);
						// Grab the first record
						list($key, $table) = each($this->table_list);
					} else {
						$table = $this->table_list;
					}
				} else {
					return false;
				}
				$q .= $this->quote_db($this->_table_prefix . $table);

                $quotedFieldnames = array_map(array($this, 'quote_db'), array_keys($this->value_list));
                $fieldlist = implode(',', $quotedFieldnames);
                $valuelist = implode(',', $this->value_list);

				$q .= '(' . $fieldlist . ') VALUES (' . $valuelist . ')';
				return $q;
		}
	}

	/** Prepare the INSERT component of the SQL query
	 */
	public function prepareInsertSelect() {
		$q = 'INSERT INTO ';
		if (isset($this->table_list)) {
			if (is_array($this->table_list)) {
				reset($this->table_list);
				// Grab the first record
				list($key, $table) = each($this->table_list);
			} else {
				$table = $this->table_list;
			}
		} else {
			return false;
		}
		$q .= $this->quote_db($this->_table_prefix . $table);

        $quotedFieldnames = array_map(array($this, 'quote_db'), array_keys($this->value_list));
        $fieldlist = implode(',', $quotedFieldnames);
        $valuelist = implode(',', $this->value_list);

		$q .= '(' . $fieldlist . ') VALUES (' . $valuelist . ')';
		return $q;
	}

	/** Prepare the REPLACE component of the SQL query
	 */
	public function prepareReplace() {
		switch (strtolower(trim(w2PgetConfig('dbtype')))) {
			case 'oci8':
			case 'oracle':
				$q = 'REPLACE INTO ';
				if (isset($this->table_list)) {
					if (is_array($this->table_list)) {
						reset($this->table_list);
						// Grab the first record
						list($key, $table) = each($this->table_list);
					} else {
						$table = $this->table_list;
					}
				} else {
					return false;
				}
				$q .= $this->_table_prefix . $table;

                $quotedFieldnames = array_map(array($this, 'quote_db'), array_keys($this->value_list));
                $fieldlist = implode(',', $quotedFieldnames);
                $valuelist = implode(',', $this->value_list);

				$q .= '(' . $fieldlist . ') VALUES (' . $valuelist . ')';
				return $q;
				break;
			default:
				//mySQL
				$q = 'REPLACE INTO ';
				if (isset($this->table_list)) {
					if (is_array($this->table_list)) {
						reset($this->table_list);
						// Grab the first record
						list($key, $table) = each($this->table_list);
					} else {
						$table = $this->table_list;
					}
				} else {
					return false;
				}
				$q .= $this->quote_db($this->_table_prefix . $table);

                $quotedFieldnames = array_map(array($this, 'quote_db'), array_keys($this->value_list));
                $fieldlist = implode(',', $quotedFieldnames);
                $valuelist = implode(',', $this->value_list);


				$q .= '(' . $fieldlist . ') VALUES (' . $valuelist . ')';
				return $q;
		}
	}

	/** Prepare the DELETE component of the SQL query
	 */
	public function prepareDelete() {
		switch (strtolower(trim(w2PgetConfig('dbtype')))) {
			case 'oci8':
			case 'oracle':
				$q = 'DELETE FROM ';
				if (isset($this->table_list)) {
					if (is_array($this->table_list)) {
						// Grab the first record
						list($key, $table) = each($this->table_list);
					} else {
						$table = $this->table_list;
					}
				} else {
					return false;
				}
				$q .= $this->_table_prefix . $table;
				$q .= $this->make_where_clause($this->where);
				return $q;
				break;
			default:
				//mySQL
				$q = 'DELETE FROM ';
				if (isset($this->table_list)) {
					if (is_array($this->table_list)) {
						// Grab the first record
						list($key, $table) = each($this->table_list);
					} else {
						$table = $this->table_list;
					}
				} else {
					return false;
				}
				$q .= $this->quote_db($this->_table_prefix . $table);
				$q .= $this->make_where_clause($this->where);
				return $q;
		}
	}

	/** Prepare the ALTER component of the SQL query
	 * @todo add ALTER DROP/CHANGE/MODIFY/IMPORT/DISCARD/.. definitions: http://dev.mysql.com/doc/mysql/en/alter-table.html
	 */
	public function prepareAlter() {
		$q = 'ALTER TABLE ' . $this->quote_db($this->_table_prefix . $this->create_table) . ' ';
		if (isset($this->create_definition)) {
			if (is_array($this->create_definition)) {
				$first = true;
				foreach ($this->create_definition as $def) {
					if ($first) {
						$first = false;
					} else {
						$q .= ', ';
					}
					$q .= $def['action'] . ' ' . $def['type'] . ' ' . $def['spec'];
				}
			} else {
				$q .= 'ADD ' . $this->create_definition;
			}
		}
		return $q;
	}

	/** Execute the query
	 *
	 * Execute the query and return a handle.  Supplants the db_exec query
	 * @param $style ADODB fetch style. Can be ADODB_FETCH_BOTH, ADODB_FETCH_NUM or ADODB_FETCH_ASSOC
	 * @param $debug Defaults to false. If true, debug output includes explanation of query
	 * @return Handle to the query result
	 */
	public function &exec($style = ADODB_FETCH_BOTH, $debug = false) {
		global $ADODB_FETCH_MODE, $w2p_performance_dbtime, $w2p_performance_dbqueries;

		if (W2P_PERFORMANCE_DEBUG) {
			$startTime = array_sum(explode(' ', microtime()));
		}
		if (!isset($this->_old_style)) {
			$this->_old_style = $ADODB_FETCH_MODE;
		}
		$ADODB_FETCH_MODE = $style;
		$this->clearQuery();

		if ($q = $this->prepare()) {
			if ($debug) {
				// Before running the query, explain the query and return the details.
				$qid = $this->_db->Execute('EXPLAIN ' . $q);
				if ($qid) {
					$res = array();
					while ($row = $this->fetchRow()) {
						$res[] = $row;
					}
					dprint(__file__, __line__, 0, 'QUERY DEBUG: ' . var_export($res, true));
					$qid->Close();
				}
			}
			$this->_query_id = $this->_db->_Execute($q);
			if (!$this->_query_id) {
				$error = $this->_db->ErrorMsg();
				dprint(__file__, __line__, 0, "query failed($q)" . ' - error was: <span style="color:red">' . $error . '</span>');
				return $this->_query_id;
			}
			if (W2P_PERFORMANCE_DEBUG) {
				++$w2p_performance_dbqueries;
				$w2p_performance_dbtime += array_sum(explode(' ', microtime())) - $startTime;
			}
			return $this->_query_id;
		} else {
			if (W2P_PERFORMANCE_DEBUG) {
				++$w2p_performance_dbqueries;
				$w2p_performance_dbtime += array_sum(explode(' ', microtime())) - $startTime;
			}
			return $this->_query_id;
		}
	}

	/** Fetch the first row of the results
	 * @return First row as array
	 */
	public function fetchRow() {
		if (!$this->_query_id) {
			return false;
		}
		return $this->_query_id->FetchRow();
	}

	/** Load database results as an array of associative arrays
	 *
	 * Replaces the db_loadList() function
	 * @param $maxrows Maximum number of rows to return
	 * @param $index Can be used to set the keys of the resulting arrays, useful to find records by primary key.
	 * @return Array of associative arrays containing row field values
	 */
	public function loadList($maxrows = -1, $index = -1) {
		global $AppUI;

		if (!$this->exec(ADODB_FETCH_ASSOC)) {
			$AppUI->setMsg($this->_db->ErrorMsg(), UI_MSG_ERROR);
			$this->clear();
			return false;
		}

		$list = array();
		$list = $this->_query_id->GetArray($maxrows);
		if ($index != -1) {
			$indexed_list = array();
			foreach ($list as $record) {
				$indexed_list[$record[$index]] = $record;
			}
			unset($list);
			$list = array();
			$list = $indexed_list;
		}
		$this->clear();
		return $list;
	}

	/** Load database results as an associative array, using the supplied field name as the array's keys
	 *
	 * Replaces the db_loadHashList() function
	 * @param $index Defaults to null, the field to use for array keys
	 * @return Associative array of rows, keyed with the field indicated by the $index parameter
	 */
	public function loadHashList($index = null) {
		if (!$this->exec(ADODB_FETCH_ASSOC)) {
			exit($this->_db->ErrorMsg());
		}
		$hashlist = array();
		$keys = null;

		while ($hash = $this->fetchRow()) {
			if ($index) {
				$hashlist[$hash[$index]] = $hash;
				//Lets add the hash fields in numerial keys:
				//This is so that the arraySelectList works correctly with the results of DBQueries loadHashList method
				$key = 0;
				foreach ($hash as $field) {
					$hashlist[$hash[$index]][$key] = $field;
					$key++;
				}
			} else {
				// If we are using fetch mode of ASSOC, then we don't
				// have an array index we can use, so we need to get one
				if (!$keys) {
					$keys = array_keys($hash);
				}
				$hashlist[$hash[$keys[0]]] = $hash[$keys[1]];
			}
		}
		$this->clear();
		return $hashlist;
	}

	/** Load a single result row as an associative array
	 * @return Associative array of field names to values
	 */
	public function loadHash() {
		if (!$this->exec(ADODB_FETCH_ASSOC)) {
			exit($this->_db->ErrorMsg());
		}
		$hash = $this->fetchRow();
		$this->clear();
		return $hash;
	}

	/** Load database results as an associative array
	 *
	 * @note To devs: is this functionally different to loadHashList() ?
	 * @param $index Field index to use for naming the array keys.
	 * @return Associative array containing result rows
	 */
	public function loadArrayList($index = 0) {
		if (!$this->exec(ADODB_FETCH_NUM)) {
			exit($this->_db->ErrorMsg());
		}
		$hashlist = array();
		$keys = null;
		while ($hash = $this->fetchRow()) {
			$hashlist[$hash[$index]] = $hash;
		}
		$this->clear();
		return $hashlist;
	}

	/** Load an indexed array containing the first column of results only
	 * @return Indexed array of first column values
	 */
	public function loadColumn() {
		if (!$this->exec(ADODB_FETCH_NUM)) {
			die($this->_db->ErrorMsg());
		}
		$result = array();
		while ($row = $this->fetchRow()) {
			$result[] = $row[0];
		}
		$this->clear();
		return $result;
	}

	/** Load database results into a CW2pObject based object
	 * @param &$object Reference to the object to propagate with database results
	 * @param $bindAll Defaults to false, Bind every field returned to the referenced object
	 * @param $strip Defaults to true
	 * @return True on success.
	 */
	public function loadObject(&$object, $bindAll = false, $strip = true) {
		if (!$this->exec(ADODB_FETCH_NUM)) {
			die($this->_db->ErrorMsg());
		}
		if ($object != null) {
			$hash = $this->loadHash();
			$this->clear();
			if (!$hash) {
				return false;
			}
			$this->bindHashToObject($hash, $object, null, $strip, $bindAll);
			return true;
		} else {
			if ($object = $this->_query_id->FetchNextObject(false)) {
				$this->clear();
				return true;
			} else {
				$object = null;
				return false;
			}
		}
	}

	/** Bind a hash to an object
	 *
	 * Takes the hash/associative array specified by $hash and turns the fields into instance properties of $obj
	 * @param $hash The hash to bind
	 * @param &$obj A reference to the object to bind the hash to
	 * @param $prefix Defaults to null, prefix to use with hash keys
	 * @param $checkSlashes Defaults to true, strip any slashes from the hash values
	 * @param $bindAll Bind all values regardless of their existance as defined instance variables
	 */
	public function bindHashToObject($hash, &$obj, $prefix = null, $checkSlashes = true, $bindAll = false) {
		is_array($hash) or die('bindHashToObject : hash expected');
		is_object($obj) or die('bindHashToObject : object expected');

		if ($bindAll) {
			foreach ($hash as $k => $v) {
				$obj->$k = ($checkSlashes && get_magic_quotes_gpc()) ? stripslashes(w2PHTMLDecode($hash[$k])) : w2PHTMLDecode($hash[$k]);
			}
		} else {
			if ($prefix) {
				foreach (get_object_vars($obj) as $k => $v) {
					if (isset($hash[$prefix . $k])) {
						$obj->$k = ($checkSlashes && get_magic_quotes_gpc()) ? stripslashes(w2PHTMLDecode($hash[$k])) : w2PHTMLDecode($hash[$k]);
					}
				}
			} else {
				foreach (get_object_vars($obj) as $k => $v) {
					if (isset($hash[$k])) {
						$obj->$k = ($checkSlashes && get_magic_quotes_gpc()) ? stripslashes(w2PHTMLDecode($hash[$k])) : w2PHTMLDecode($hash[$k]);
					}
				}
			}
		}
	}

	/** Build or update a table using an XML string
	 *
	 * @param $xml XML string describing table structure
	 * @param $mode Defaults to 'REPLACE'
	 * @return True on success, false if there was an error.
	 */
	public function execXML($xml, $mode = 'REPLACE') {
		global $AppUI;

		include_once W2P_BASE_DIR . '/lib/adodb/adodb-xmlschema.inc.php';
		$schema = new adoSchema($this->_db);
		$schema->setUpgradeMode($mode);
		if (isset($this->_table_prefix) && $this->_table_prefix) {
			$schema->setPrefix($this->_table_prefix, false);
		}
		$schema->ContinueOnError(true);
		if (($sql = $scheme->ParseSchemaString($xml)) == false) {
			$AppUI->setMsg(array('Error in XML Schema', 'Error', $this->_db->ErrorMsg()), UI_MSG_ERR);
			return false;
		}
		if ($schema->ExecuteSchema($sql, true)) {
			return true;
		} else {
			return false;
		}
	}

	/** Load a single column result from a single row
	 * @return Value of the row column
	 */
	public function loadResult() {
		global $AppUI;

		$result = false;

		if (!$this->exec(ADODB_FETCH_NUM)) {
			$AppUI->setMsg($this->_db->ErrorMsg(), UI_MSG_ERROR);
		} elseif ($data = $this->fetchRow()) {
			$result = $data[0];
		}
		$this->clear();
		return $result;
	}

	/** Create a where clause based upon supplied field.
	 *
	 * @param	$where_clause Either string or array of subclauses.
	 * @return SQL WHERE clause as a string.
	 */
	public function make_where_clause($where_clause) {
		$result = '';
		if (!isset($where_clause)) {
			return $result;
		}
		if (is_array($where_clause)) {
			if (count($where_clause)) {
				$started = false;
				$result = ' WHERE ' . implode(' AND ', $where_clause);
			}
		} elseif (strlen($where_clause) > 0) {
			$result = ' WHERE ' . $where_clause;
		}
		return $result;
	}

	/** Create an order by clause based upon supplied field.
	 *
	 * @param	$order_clause	Either string or array of subclauses.
	 * @return SQL ORDER BY clause as a string.
	 */
	public function make_order_clause($order_clause) {
		$result = '';
		if (!isset($order_clause)) {
			return $result;
		}

		if (is_array($order_clause)) {
			$started = false;
			$result = ' ORDER BY ' . implode(',', $order_clause);
		} elseif (strlen($order_clause) > 0) {
			$result = ' ORDER BY ' . $order_clause;
		}
		return $result;
	}

	/** Create a group by clause based upon supplied field.
	 *
	 * @param	$group_clause	Either string or array of subclauses.
	 * @return SQL GROUP BY clause as a string.
	 */
	public function make_group_clause($group_clause) {
		$result = '';
		if (!isset($group_clause)) {
			return $result;
		}

		if (is_array($group_clause)) {
			$started = false;
			$result = ' GROUP BY ' . implode(',', $group_clause);
		} elseif (strlen($group_clause) > 0) {
			$result = ' GROUP BY ' . $group_clause;
		}
		return $result;
	}

	/** Create a join condition based upon supplied fields.
	 *
	 * @param	$join_clause	Either string or array of subclauses.
	 * @return SQL JOIN condition as a string.
	 */
	public function make_join($join_clause) {
		$result = '';
		if (!isset($join_clause)) {
			return $result;
		}
		if (is_array($join_clause)) {
			foreach ($join_clause as $join) {
				$result .= ' ' . strtoupper($join['type']) . ' JOIN ' . $this->quote_db($this->_table_prefix . $join['table']);
				if ($join['alias']) {
					$result .= ' AS ' . $join['alias'];
				} else {
					$result .= ' AS ' . $join['table'];
				}
				if (is_array($join['condition'])) {
					$result .= ' USING (' . implode(',', $join['condition']) . ')';
				} else {
					$result .= ' ON ' . $join['condition'];
				}
			}
		} else {
			$result .= ' LEFT JOIN ' . $this->quote_db($this->_table_prefix . $join_clause);
		}
		return $result;
	}

	/** Create a having clause based upon supplied field.
	 *
	 * @param	$having_clause Either string or array of subclauses.
	 * @return SQL HAVING clause as a string.
	 */
	public function make_having_clause($having_clause) {
		$result = '';
		if (!isset($having_clause)) {
			return $result;
		}
		if (is_array($having_clause)) {
			if (count($having_clause)) {
				$started = false;
				$result = ' HAVING ' . implode(' AND ', $having_clause);
			}
		} elseif (strlen($having_clause) > 0) {
			$result = ' HAVING ' . $having_clause;
		}
		return $result;
	}

	/** Create a limit clause
	 *
	 * @param	$limit	Either integer with nr of records to retrieve or array with offset and nr of records to retrieve .
	 * @param	$offset	integer of offset from where it should start retrieving.
	 * @return	SQL LIMIT clause as a string.
	 */
	public function make_limit_clause($limit, $offset) {
		$result = '';
		if (!isset($limit)) {
			return $result;
		}

		if (is_array($limit) && (count($limit) == 2)) {
			$result = ' LIMIT ' . implode(',', $limit);
		} elseif (isset($limit) && ($offset <= 0)) {
			$result = ' LIMIT ' . intval($limit);
		} elseif (isset($limit) && ($offset > 0)) {
			$result = ' LIMIT ' . intval($offset) . ', ' . intval($limit);
		}
		return $result;
	}

	public function foundRows() {
		global $db;
		$result = false;
		if ($this->include_count) {
			if ($qid = $db->Execute('SELECT FOUND_ROWS() as rc')) {
				$data = $qid->FetchRow();
				$result = isset($data['rc']) ? $data['rc'] : $data[0];
			}
		}
		return $result;
	}

	/** Add quotes to a string
	 *
	 * @param	$string	A string to add quotes to.
	 * @return The quoted string
	 */
	public function quote($string) {
		if (is_int($string)) {
			return $string;
		} else {
			return $this->_db->qstr($string, get_magic_quotes_runtime());
		}
	}

	/** Add quotes to a database identifier
	 * @param $string The identifier to quote
	 * @return The quoted identifier
	 */
	public function quote_db($string) {
		return $this->_db->nameQuote . $string . $this->_db->nameQuote;
	}

	/**
	 * Document::insertArray()
	 *
	 * { Description }
	 *
	 * @param [type] $verbose
	 */
	public function insertArray($table, &$hash, $verbose = false) {
		$this->addTable($table);
		foreach ($hash as $k => $v) {
			if (is_array($v) or is_object($v) or $v == null) {
				continue;
			}
			$fields[] = $k;
			$values[$k] = $v;
		}
		foreach ($fields as $field) {
			if (!in_array($values[$field], $this->_db_funcs)) {
				$this->addInsert($field, $values[$field]);
			} else {
				$this->addInsert($field, $values[$field], false, true);
			}
		}

		if (!$this->exec()) {
			return false;
		}
		$id = db_insert_id();
		return true;
	}

	/**
	 * Document::updateArray()
	 *
	 * { Description }
	 *
	 * @param [type] $verbose
	 */
	public function updateArray($table, &$hash, $keyName, $verbose = false) {
		$this->addTable($table);
		foreach ($hash as $k => $v) {
			if (is_array($v) or is_object($v) or $k[0] == '_') { // internal or NA field
				continue;
			}

			if ($k == $keyName) { // PK not to be updated
				$this->addWhere($keyName . ' = \'' . db_escape($v) . '\'');
				continue;
			}
			$fields[] = $k;
			if ($v == '') {
				$values[$k] = 'NULL';
			} else {
				$values[$k] = $v;
			}
		}
		if (count($values)) {
			foreach ($fields as $field) {
				if (!in_array($values[$field], $this->_db_funcs)) {
					$this->addUpdate($field, $values[$field]);
				} else {
					$this->addUpdate($field, $values[$field], false, true);
				}
			}
			$ret = $this->exec();
		}
		return $ret;
	}

	/**
	 * Document::insertObject()
	 *
	 * { Description }
	 *
	 * @param [type] $keyName
	 * @param [type] $verbose
	 */
	public function insertObject($table, &$object, $keyName = null, $verbose = false) {
		$this->addTable($table);
		foreach (get_object_vars($object) as $k => $v) {
			if (is_array($v) or is_object($v) or $v == null) {
				continue;
			}
			if ($k[0] == '_') { // internal field
				continue;
			}
			$fields[] = $k;
			$values[$k] = $v;
		}
		foreach ($fields as $field) {
			if (!in_array($values[$field], $this->_db_funcs)) {
				$this->addInsert($field, $values[$field]);
			} else {
				$this->addInsert($field, $values[$field], false, true);
			}
		}
		if (!$this->exec()) {
			return false;
		}
		$id = db_insert_id();
		($verbose) && print 'id=[' . $id . '] ';
		if ($keyName && $id) {
			$object->$keyName = $id;
		}
		return true;
	}

	/**
	 * Document::updateObject()
	 *
	 * { Description }
	 *
	 * @param [type] $updateNulls
	 */
	public function updateObject($table, &$object, $keyName, $updateNulls = true) {
		$this->addTable($table);
		foreach (get_object_vars($object) as $k => $v) {
			if (is_array($v) or is_object($v) or $k[0] == '_') { // internal or NA field
				continue;
			}
			if ($k == $keyName) { // PK not to be updated
				$this->addWhere($keyName . ' = \'' . db_escape($v) . '\'');
				continue;
			}
			if ($v === null && !$updateNulls) {
				continue;
			}
			$fields[] = $k;
			$values[$k] = $v;
		}
		if (count($values)) {

			foreach ($fields as $field) {
				if (!in_array($values[$field], $this->_db_funcs)) {
					$this->addUpdate($field, $values[$field]);
				} else {
					$this->addUpdate($field, $values[$field], false, true);
				}
			}
			return $this->exec();
		} else {
			return true;
		}
	}

	/**
	 *	Clone the current query
	 *
	 *	@return	object	The new record object or null if error
	 **/
	public function duplicate() {

        /*
        *  PHP4 is no longer supported or allowed. The
        *    installer/upgrader/converter simply stops executing.
        *  This method also appears in the CDate and W2pObject (modified) class.
        */
		return clone ($this);
	}
}
