<?php
/**
 * Copyright 2003,2004 Adam Donnison <adam@saki.com.au>
 *
 * This file is part of the collected works of Adam Donnison.
 *
 * This is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * This is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this; if not, write to the Free Software Foundation, Inc.,
 * 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * Container for creating prefix-safe queries.  Allows build up of
 * a select statement by adding components one at a time.
 *
 *  Note: Previously, this class may have been erroneously re-licensed by
 *    Keith Casey (caseydk). Its original copyright statement by Adam Donnison
 *    is being restored until this can be clarified and/or corrected.
 *
 * @package     web2project\deprecated
 *
 */

class w2p_Database_oldQuery {
	/**< Contains the query after it has been built. */
	public $query;
	/**< Array of values used in INSERT or REPLACE statements */
	public $value_list;
	/**< Name of the table to create */
	public $create_table;
	/**< Array containing information about the table definition */
	public $create_definition;
	/**< Use the old style of fetch mode with ADODB */
	public $_old_style = null;

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

	/** Insert a value into the database
	 * @param $field The field to insert the value into
	 * @param $value The specified value
	 * @param $set Defaults to false. If true will check to see if the fields or values supplied are comma delimited strings instead of arrays
	 * @param $func Defaults to false. If true will not use quotation marks around the value - to be used when the value being inserted includes a function
	 */
	public function addInsert($field, $value = null, $set = false, $func = false) {
        error_log(__FUNCTION__ . ' has been deprecated', E_USER_WARNING);
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

	// implemented addReplace() on top of addInsert()
	/** Insert a value into the database, to replace an existing row.
	 * @param $field The field to insert the value into
	 * @param $value The specified value
	 * @param $set Defaults to false. If true will check to see if the fields or values supplied are comma delimited strings instead of arrays
	 * @param $func Defaults to false. If true will not use quotation marks around the value - to be used when the value being inserted includes a function
	 */
	public function addReplace($field, $value, $set = false, $func = false) {
        error_log(__FUNCTION__ . ' has been deprecated', E_USER_WARNING);
        $this->addInsert($field, $value, $set, $func);
		$this->type = 'replace';
	}

// Everything from here to the table structure area is about data retrieval, not query building

	/** Execute the query
	 *
	 * Execute the query and return a handle.  Supplants the db_exec query
	 * @param $style ADODB fetch style. Can be ADODB_FETCH_BOTH, ADODB_FETCH_NUM or ADODB_FETCH_ASSOC
	 * @param $debug Defaults to false. If true, debug output includes explanation of query
	 * @return Handle to the query result
	 */
	public function &exec($style = ADODB_FETCH_BOTH, $debug = false) {
        error_log(__FUNCTION__ . ' has been deprecated', E_USER_WARNING);
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

	/**
	 * Document::insertArray()
	 */
	public function insertArray($table, &$hash) {
        error_log(__FUNCTION__ . ' has been deprecated', E_USER_WARNING);
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
		return $id;
	}

	/**
	 * Document::updateArray()
	 */
	public function updateArray($table, &$hash, $keyName) {
        error_log(__FUNCTION__ . ' has been deprecated', E_USER_WARNING);
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
        error_log(__FUNCTION__ . ' has been deprecated', E_USER_WARNING);
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
        error_log(__FUNCTION__ . ' has been deprecated', E_USER_WARNING);
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
            if (!$this->exec()) {
                return false;
            }
		}

        return true;
	}

// Everything below this line is deprecated and/or related to table structure not content

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

	/** Drop a table from the database
	 *
	 * Use dropTemp() to drop temporary tables
	 * @param $table the name of the table to drop.
	 */
	public function dropTable($table) {
		$this->type = 'drop';
		$this->create_table = $table;
	}

	/** Set a table creation definition from supplied array
	 * @param $def Array containing table definition
	 */
	public function createDefinition($def) {
		$this->create_definition = $def;
	}
    
// Everything below this line is deprecated and no longer used in core

    public function createDatabase($database) {
        $dict = NewDataDictionary($this->_db, w2PgetConfig('dbtype'));
        $dict->CreateDatabase($database);
    }

    /** Alter a database table
     * @param $table the name of the table to alter
     */
    public function alterTable($table) {
        $this->create_table = $table;
        $this->type = 'alter';
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

    /** Drop a temporary table from the database
     * @param $table the name of the temporary table to drop
     */
    public function dropTemp($table) {
        $this->type = 'drop';
        $this->create_table = $table;
    }

    /**
     * Instead of concatenating here, retrieve the relevant fields and do
     *   it in PHP. It won't necessarily be faster but should be more
     *   supportable cross-databasewise.
     *
	 * @deprecated
	 */
	public function concat() {
		trigger_error("concat() has been deprecated in v3.0 and will be removed by v4.0. Please concatenate in PHP instead.", E_USER_NOTICE );
        $arr = func_get_args();
		$conc_str = call_user_func_array(array(&$this->_db, 'Concat'), $arr);
		return $conc_str;
	}

	/**
     * Get database specific SQL used to check for null values.
	 *
     * @deprecated
     *
	 * @return String containing SQL to check for null field value
	 */
	public function ifNull($field, $nullReplacementValue) {
        trigger_error("ifNull() has been deprecated in v3.0 and will be removed by v4.0. There is no replacement.", E_USER_NOTICE );
        return $this->_db->IfNull($field, $nullReplacementValue);
	}

	/**
     * Add a field definition for usage with table creation/alteration
     *
     * @deprecated
     *
	 * @param $name The name of the field
	 * @param $type The type of field to create
	 */
	public function addField($name, $type) {
		if (!is_array($this->create_definition)) {
			$this->create_definition = array();
		}
		$this->create_definition[] = array('action' => 'ADD', 'type' => '', 'spec' => $name . ' ' . $type);
        trigger_error("w2p_Database_Query->addField() has been deprecated in v3.0 and will be removed by v4.0.", E_USER_NOTICE );
	}

	/**
	 * Alter a field definition for usage with table alteration
     *
     * @deprecated
     *
	 * @param $name The name of the field
	 * @param $type The type of the field
	 */
	public function alterField($name, $type) {
		if (!is_array($this->create_definition)) {
			$this->create_definition = array();
		}
		$this->create_definition[] = array('action' => 'CHANGE', 'type' => '', 'spec' => $name . ' ' . $name . ' ' . $type);
        trigger_error("w2p_Database_Query->alterField() has been deprecated in v3.0 and will be removed by v4.0.", E_USER_NOTICE );
	}

	/**
     * Drop a field from table definition or from an existing table
     *
     * @deprecated
     *
	 * @param $name The name of the field to drop
	 */
	public function dropField($name) {
		if (!is_array($this->create_definition)) {
			$this->create_definition = array();
		}
		$this->create_definition[] = array('action' => 'DROP', 'type' => '', 'spec' => $name);
        trigger_error("w2p_Database_Query->dropField() has been deprecated in v3.0 and will be removed by v4.0.", E_USER_NOTICE );
	}

	/**
     * Add an index. Fields should be separated by commas to create a multi-field index
     *
     * @deprecated
	 */
	public function addIndex($name, $type) {
		if (!is_array($this->create_definition)) {
			$this->create_definition = array();
		}
		$this->create_definition[] = array('action' => 'ADD', 'type' => 'INDEX', 'spec' => '(' . $name . ') ' . $type);
        trigger_error("w2p_Database_Query->addIndex() has been deprecated in v3.0 and will be removed by v4.0.", E_USER_NOTICE );
	}

	/**
     * Add a primary key attribute. Fields should be separated by commas to create a multi-field primary key
     *
     * @deprecated
	 */
	public function addPrimary($name) {
		if (!is_array($this->create_definition)) {
			$this->create_definition = array();
		}
		$this->create_definition[] = array('action' => 'ADD', 'type' => 'PRIMARY KEY', 'spec' => '(' . $name . ')');
        trigger_error("w2p_Database_Query->addPrimary() has been deprecated in v3.0 and will be removed by v4.0.", E_USER_NOTICE );
	}

	/**
     * Drop an index
     *
     * @deprecated
	 */
	public function dropIndex($name) {
		if (!is_array($this->create_definition)) {
			$this->create_definition = array();
		}
		$this->create_definition[] = array('action' => 'DROP', 'type' => 'INDEX', 'spec' => $name);
        trigger_error("w2p_Database_Query->dropIndex() has been deprecated in v3.0 and will be removed by v4.0.", E_USER_NOTICE );
	}

	/**
     * Remove a primary key attribute from a field
     *
     * @deprecated
	 */
	public function dropPrimary() {
		if (!is_array($this->create_definition)) {
			$this->create_definition = array();
		}
		$this->create_definition[] = array('action' => 'DROP', 'type' => 'PRIMARY KEY', 'spec' => '');
        trigger_error("w2p_Database_Query->dropPrimary() has been deprecated in v3.0 and will be removed by v4.0.", E_USER_NOTICE );
	}

    /**
     * @deprecated
     */
    public function addClause($clause, $value, $check_array = true) {
        error_log(__FUNCTION__ . ' has been deprecated in v3.0. There is no replacement.', E_USER_WARNING);
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

    public function foundRows() {
        error_log(__FUNCTION__ . ' has been deprecated', E_USER_WARNING);
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

    /** Add item to an internal associative array
     *
     * Used internally with w2p_Database_Query
     *
     * @param	$varname	Name of variable to add/create
     * @param	$name	Data to add
     * @param	$id	Index to use in array.
     */
    public function addMap($varname, $name, $id) {
        error_log(__FUNCTION__ . ' has been deprecated', E_USER_WARNING);
        if (!isset($this->$varname)) {
            $this->$varname = array();
        }
        if (isset($id)) {
            $this->{$varname}[$id] = $name;
        } else {
            $this->{$varname}[] = $name;
        }
    }
}