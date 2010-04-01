<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

class smartsearch {

	public $table = null;
	public $table_alias = null;
	public $table_module = null;
	public $table_key = null; // primary key in searched table
	public $table_key2 = null; // primary key in parent table
	public $table_link = null; // first part of link
	public $table_link2 = null; // second part of link
	public $table_title = null;
	public $table_orderby = null;
	public $table_extra = null;
	public $search_fields = array();
	public $display_fields = array();
	public $table_joins = array();
	public $keyword = null;
	public $keywords = null;
	public $tmppattern = '';
	public $display_val = '';
	public $search_options = null;

	public function fetchResults(&$permissions, &$record_count) {
		global $AppUI;
        $outstring = '';

		$q = $this->_buildQuery();
		$results = null;
		if ($q) {
			$results = $q->loadList();
		}
		if ($results) {
			$outstring = '';
			$subrecord_count = 0;
			foreach ($results as $records) {
				if ($permissions->checkModuleItem($this->table_module, 'view', $records[preg_replace('/^.*\.([^\.]+)$/', '$1', $this->table_key)])) {
					//Don't count records for which the user does not have permission
					$record_count += 1;
					$subrecord_count += 1;
					// --MSy-
					$ii = 0;
					$display_val = '';
					foreach ($this->display_fields as $fld) {
						$ii++;
						if (!($this->search_options['display_all_flds'] == 'on') && ($ii > 2)) {
							break;
						}
						$display_val = $display_val . ' ' . $records[preg_replace('/^.*\.([^\.]+)$/', '$1', $fld)];
					}
					//--MSy-
					$tmplink = '';
					if (isset($this->table_link) && isset($this->table_key)) {
						$tmplink = $this->table_link . $records[preg_replace('/^.*\.([^\.]+)$/', '$1', $this->table_key)];
					}
					if (isset($this->table_link2) && isset($this->table_key2)) {
						$tmplink = $this->table_link . $records[preg_replace('/^.*\.([^\.]+)$/', '$1', $this->table_key)] . $this->table_link2 . $records[preg_replace('/^.*\.([^\.]+)$/', '$1', $this->table_key2)];
					}
					//--MSy--
					$outstring .= '<tr><td><a href = "' . $tmplink . '">' . highlight($display_val, $this->keywords) . '</a></td></tr>';
				}
			}
			$outstring = '<tr><th><b>' . $AppUI->_($this->table_title) . ' (' . $subrecord_count . ')' . '</b></th></tr> ' . "\n" . $outstring;
		} else {
			if ($this->search_options['show_empty'] == 'on') {
				$outstring = '<tr><th><b>' . $AppUI->_($this->table_title) . ' (0)' . '</b></th></tr><tr><td>' . $AppUI->_('Empty') . '</td></tr>';
			}
		}
		return $outstring;
	}

	public function setKeyword($keyw) {
		$this->keyword = $keyw;
	}
	public function setAdvanced($search_opts) {
		$this->search_options = $search_opts;
		$this->keywords = $search_opts['keywords'];
	}

	public function _buildQuery() {
		$q = new DBQuery;

		if ($this->table_alias) {
			$q->addTable($this->table, $this->table_alias);
		} else {
			$q->addTable($this->table);
		}
		$q->addQuery($this->table_key);
		if (isset($this->table_key2)) {
			$q->addQuery($this->table_key2);
		}
		//--MSy--
		foreach ($this->table_joins as $join) {
			$q->addJoin($join['table'], $join['alias'], $join['join']);
		}

		foreach ($this->display_fields as $fld) {
			$q->addQuery($fld);
		}

		$q->addOrder($this->table_orderby);

		if ($this->table_extra) {
			$q->addWhere($this->table_extra);
		}

		$sql = '';
		foreach (array_keys($this->keywords) as $keyword) {
			$sql .= '(';

			foreach ($this->search_fields as $field) {
				//OR treatment to each keyword
				// Search for semi-colons, commas or spaces and allow any to be separators
				$or_keywords = preg_split('/[\s,;]+/', $keyword);
				foreach ($or_keywords as $or_keyword) {
					if ($this->search_options['ignore_specchar'] == 'on') {
						$tmppattern = recode2regexp_utf8($or_keyword);
						if ($this->search_options['ignore_case'] == 'on') {
							$sql .= ' ' . $field . ' REGEXP \'' . $tmppattern . '\' or ';
						} else {
							$sql .= ' ' . $field . ' REGEXP BINARY \'' . $tmppattern . '\' or ';
						}
					} else
						if ($this->search_options['ignore_case'] == 'on') {
							$sql .= ' ' . $field . ' LIKE "%' . $or_keyword . '%" or ';
						} else {
							$sql .= ' ' . $field . ' LIKE BINARY "%' . $or_keyword . '%" or ';
						}
				}
			} // foreach $field
			$sql = substr($sql, 0, -4);

			if ($this->search_options['all_words'] == 'on') {
				$sql .= ') and ';
			} else {
				$sql .= ') or ';
			}

		} // foreach $keyword
		//--MSy--
		$sql = substr($sql, 0, -4);
		if ($sql) {
			$q->addWhere($sql);
			return $q;
		} else {
			return null;
		}
	}
}

function highlight($text, $keyval) {
	global $ssearch;

	$txt = $text;
	$hicolor = array('#FFFF66', '#ADD8E6', '#90EE8A', '#FF99FF');
	$keys = array();
	if (!is_array($keyval))
		$keys = array($keyval);
	else
		$keys = $keyval;

	foreach ($keys as $key) {
		if (mb_strlen($key[0]) > 0) {
			$key[0] = stripslashes($key[0]);
			$metacharacters = array('\\', '(', ')', '$', '[', '*', '+', '|', '.', '^', '?');
			$metareplacement = array('\\\\', '\(', '\)', '\$', '\[', '\*', '\+', '\|', '\.', '\^', '\?');
			$key[0] = mb_str_replace($metacharacters, $metareplacement, $key[0]);
			if (isset($ssearch['ignore_specchar']) && ($ssearch['ignore_specchar'] == 'on')) {
				if ($ssearch['ignore_case'] == 'on') {
					$txt = preg_replace('/'.recode2regexp_utf8($key[0]).'/i', '<span style="background:' . $hicolor[$key[1]] . '" >\\0</span>', $txt);
				} else {
					$txt = preg_replace('/'.(recode2regexp_utf8($key[0])).'/', '<span style="background:' . $hicolor[$key[1]] . '" >\\0</span>', $txt);
				}
			} elseif (!isset($ssearch['ignore_specchar']) || ($ssearch['ignore_specchar'] == '')) {
				if ($ssearch['ignore_case'] == 'on') {
					$txt = preg_replace('/'.$key[0].'/i', '<span style="background:' . $hicolor[$key[1]] . '" >\\0</span>', $txt);
				} else {
					$txt = preg_replace('/'.$key[0].'/', '<span style="background:' . $hicolor[$key[1]] . '" >\\0</span>', $txt);
				}
			} else {
				$txt = preg_replace('/'.sql_regcase($key[0]).'/i', '<span style="background:' . $hicolor[$key[1]] . '" >\\0</span>', $txt);
			}
		}
	}
	return $txt;
}

function recode2regexp_utf8($input) {
	$result = '';
	for ($i = 0, $i_cmp = mb_strlen($input); $i < $i_cmp; ++$i)
		switch ($input[$i]) {
			case 'A':
			case 'a':
				$result .= '(a|A!|A�|A?|A�)';
				break;
			case 'C':
			case 'c':
				$result .= '(c|�?|�O)';
				break;
			case 'D':
			case 'd':
				$result .= '(d|�?|Ď)';
				break;
			case 'E':
			case 'e':
				$result .= '(e|A�|ě|A�|Ě)';
				break;
			case 'I':
			case 'i':
				$result .= '(i|A�|A?)';
				break;
			case 'L':
			case 'l':
				$result .= '(l|�o|�3|�1|�1)';
				break;
			case 'N':
			case 'n':
				$result .= '(n|A^|A�)';
				break;
			case 'O':
			case 'o':
				$result .= '(o|A3|A�|A�|A�)';
				break;
			case 'R':
			case 'r':
				$result .= '(r|A�|A�|A�|A~)';
				break;
			case 'S':
			case 's':
				$result .= '(s|A!|A�)';
				break;
			case 'T':
			case 't':
				$result .= '(t|AY|A�)';
				break;
			case 'U':
			case 'u':
				$result .= '(u|Ao|A�|A�|A�)';
				break;
			case 'Y':
			case 'y':
				$result .= '(y|A1|A?)';
				break;
			case 'Z':
			case 'z':
				$result .= '(z|A3|A1)';
				break;
			default:
				$result .= $input[$i];
		}
	return $result;
}