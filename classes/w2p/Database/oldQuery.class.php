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

    /**
     * @deprecated
     * @codeCoverageIgnore
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
		} else {
			if (!$func) {
				$this->addMap('value_list', $this->quote($value), $field);
			} else {
				$this->addMap('value_list', $value, $field);
			}
        }
        $this->type = 'insert';
	}

    /**
     * @deprecated
     * @codeCoverageIgnore
     */
	public function addReplace($field, $value, $set = false, $func = false) {
        error_log(__FUNCTION__ . ' has been deprecated', E_USER_WARNING);
        $this->addInsert($field, $value, $set, $func);
		$this->type = 'replace';
	}

    /**
     * @deprecated
     * @codeCoverageIgnore
     */
	public function createTable($table, $def = null) {
        $this->type = 'createPermanent';
		$this->create_table = $table;
		if ($def) {
			$this->create_definition = $def;
		}
	}

    /**
     * @deprecated
     * @codeCoverageIgnore
     */
	public function dropTable($table) {
		$this->type = 'drop';
		$this->create_table = $table;
	}

    /**
     * @deprecated
     * @codeCoverageIgnore
     */
	public function createDefinition($def) {
		$this->create_definition = $def;
	}

    /**
     * @deprecated
     * @codeCoverageIgnore
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
     * @deprecated
     * @codeCoverageIgnore
     */
    public function updateArray($table, &$hash, $keyName) {
        error_log(__FUNCTION__ . ' has been deprecated', E_USER_WARNING);
        $this->addTable($table);
        foreach ($hash as $k => $v) {
            if (is_array($v) or is_object($v) or $k[0] == '_') { // internal or NA field
                continue;
            }

            if ($k == $keyName) { // PK not to be updated
                $this->addWhere($keyName . ' = \'' . (int) $v . '\'');
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
     * @deprecated
     * @codeCoverageIgnore
     */
    public function createDatabase($database) {
        $dict = NewDataDictionary($this->_db, w2PgetConfig('dbtype'));
        $dict->CreateDatabase($database);
    }

    /**
     * @deprecated
     * @codeCoverageIgnore
     */
    public function alterTable($table) {
        $this->create_table = $table;
        $this->type = 'alter';
    }

    /**
     * @deprecated
     * @codeCoverageIgnore
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

    /**
     * @deprecated
     * @codeCoverageIgnore
     */
    public function DDcreateTable($table, $def, $opts) {
        $dict = NewDataDictionary($this->_db, w2PgetConfig('dbtype'));
        $query_array = $dict->ChangeTableSQL(w2PgetConfig('dbprefix') . $table, $def, $opts);
        //returns 0 - failed, 1 - executed with errors, 2 - success
        return $dict->ExecuteSQLArray($query_array);
    }

    /**
     * @deprecated
     * @codeCoverageIgnore
     */
    public function DDcreateIndex($name, $table, $cols, $opts) {
        $dict = NewDataDictionary($this->_db, w2PgetConfig('dbtype'));
        $query_array = $dict->CreateIndexSQL($name, $table, $cols, $opts);
        //returns 0 - failed, 1 - executed with errors, 2 - success
        return $dict->ExecuteSQLArray($query_array);
    }

    /**
     * @deprecated
     * @codeCoverageIgnore
     */
    public function createTemp($table) {
        $this->type = 'createTemporary';
        $this->create_table = $table;
    }

    /**
     * @deprecated
     * @codeCoverageIgnore
     */
    public function dropTemp($table) {
        $this->type = 'drop';
        $this->create_table = $table;
    }

    /**
     * @deprecated
     * @codeCoverageIgnore
     */
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

    /**
     * @deprecated
     * @codeCoverageIgnore
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

    /**
     * @deprecated
     * @codeCoverageIgnore
     */
    public function make_order_clause($order_clause) {
        trigger_error("make_order_clause has been deprecated in v3.0.", E_USER_NOTICE );
        $this->_convertFromOldStructure();

        return $this->_buildOrder();
    }

    /**
     * @deprecated
     * @codeCoverageIgnore
     */
    public function make_group_clause($group_clause) {
        trigger_error("make_group_clause has been deprecated in v3.0.", E_USER_NOTICE );
        $this->_convertFromOldStructure();

        return $this->_buildGroup();
    }

    /**
     * @deprecated
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
     */
    public function make_limit_clause($limit, $offset) {
        trigger_error(__FUNCTION__ . " has been deprecated in v3.0.", E_USER_NOTICE );

        $this->setLimit($limit, $offset);

        return $this->_buildLimit();
    }

    /**
     * @deprecated
     * @codeCoverageIgnore
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

    /**
     * @deprecated
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
     */
    public function quote_db($string) {
        trigger_error(__FUNCTION__ . " has been deprecated in v3.0.", E_USER_NOTICE );

        return $this->quote($string);
    }

    public function __call($name, $params)
    {
        switch($name) {
            case 'execXML':
            case 'includeCount':
            case 'loadArrayList':
                error_log("$name has been deprecated in v3.0. There is no replacement.", E_USER_WARNING);
                break;
            case 'duplicate':
                return clone ($this);
            default:
                throw new w2p_Database_Exception("The $name method has not been implemented.");
        }
    }
}