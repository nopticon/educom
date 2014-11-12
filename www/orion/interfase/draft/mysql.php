<?php
/*
<Orion, a web development framework for RK.>
Copyright (C) <2011>  <Orion>

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
if (defined('SQL_LAYER')) {
	return;
}
define('SQL_LAYER', 'mysql');

class sql_db {
	private $db_connect_id;
	private $query_result;
	private $row = array();
	private $rowset = array();
	private $num_queries = 0;
	public $return_on_error = false;

	public function __construct($d = false) {
		$pwd = $this->file($d);
		
		if ($this->db_connect_id = @mysql_connect($this->server, $this->user, $pwd)) {
			if (@mysql_select_db($this->dbname)) {
				return $this->db_connect_id;
			}
		}
		return $this->sql_error('');
	}
	
	private function file($d) {
		if (!is_array($d)) {
			$d = decode_ht('.htda');
		}
		
		foreach (array('server' => 0, 'user' => 1, 'dbname' => 3) as $vv => $k) {
			$this->{$vv} = _decode($d[$k]);
		}
		return _decode($d[2]);
	}

	public function sql_close() {
		if ($this->db_connect_id) {
			if ($this->query_result && @is_resource($this->query_result)) {
				@mysql_free_result($this->query_result);
			}
			
			return @mysql_close($this->db_connect_id);
		}
		return false;
	}

	//
	// Base query method
	//
	public function sql_query($query = '', $transaction = false) {
		if (is_array($query)) {
			if (count($query)) {
				foreach ($query as $each) {
					$this->sql_query($each);
				}
			}
			
			return;
		}
		
		// Remove any pre-existing queries
		unset($this->query_result);
		
		if ($query != '') {
			$this->num_queries++;
			
			if (!$this->query_result = @mysql_query($query, $this->db_connect_id)) {
				$this->sql_error($query);
			}
		}
		
		if ($this->query_result) {
			unset($this->row[$this->query_result]);
			unset($this->rowset[$this->query_result]);
			
			return $this->query_result;
		}
		
		return ( $transaction == END_TRANSACTION ) ? true : false;
	}
	
	public function sql_query_limit($query, $total, $offset = 0) {
		if ($query != '') {
			$this->query_result = false;

			// if $total is set to 0 we do not want to limit the number of rows
			if ($total == 0) {
				$total = -1;
			}

			$query .= "\n LIMIT " . ((!empty($offset)) ? $offset . ', ' . $total : $total);

			return $this->sql_query($query);
		} else {
			return false;
		}
	}
	
	// Idea for this from Ikonboard
	public function sql_build_array($query, $assoc_ary = false) {
		if (!is_array($assoc_ary)) {
			return false;
		}

		$fields = array();
		$values = array();
		if ($query == 'INSERT') {
			foreach ($assoc_ary as $key => $var) {
				$fields[] = $key;

				if (is_null($var)) {
					$values[] = 'NULL';
				} elseif (is_string($var)) {
					$values[] = "'" . $this->sql_escape($var) . "'";
				} else {
					$values[] = (is_bool($var)) ? intval($var) : $var;
				}
			}

			$query = ' (' . implode(', ', $fields) . ') VALUES (' . implode(', ', $values) . ')';
		} else if ($query == 'UPDATE' || $query == 'SELECT') {
			$values = array();
			foreach ($assoc_ary as $key => $var) {
				if (is_null($var)) {
					$values[] = "$key = NULL";
				} elseif (is_string($var)) {
					$values[] = "$key = '" . $this->sql_escape($var) . "'";
				} else {
					$values[] = (is_bool($var)) ? "$key = " . intval($var) : "$key = $var";
				}
			}
			$query = implode(($query == 'UPDATE') ? ', ' : ' AND ', $values);
		}

		return $query;
	}
	
	public function sql_num_queries() {
		return $this->num_queries;
	}

	//
	// Other query methods
	//
	public function sql_numrows($query_id = 0) {
		if (!$query_id) {
			$query_id = $this->query_result;
		}
		
		return ($query_id) ? @mysql_num_rows($query_id) : false;
	}
	
	public function sql_affectedrows() {
		return ($this->db_connect_id) ? @mysql_affected_rows($this->db_connect_id) : false;
	}
	
	public function sql_numfields($query_id = 0) {
		if (!$query_id) {
			$query_id = $this->query_result;
		}
		
		return ($query_id) ? @mysql_num_fields($query_id) : false;
	}
	
	public function sql_fieldname($offset, $query_id = 0) {
		if (!$query_id) {
			$query_id = $this->query_result;
		}
		
		return ($query_id) ? @mysql_field_name($query_id, $offset) : false;
	}
	
	public function sql_fieldtype($offset, $query_id = 0) {
		if (!$query_id) {
			$query_id = $this->query_result;
		}
		
		return ($query_id) ? @mysql_field_type($query_id, $offset) : false;
	}
	
	public function sql_fetchrow($query_id = 0) {
		if (!$query_id) {
			$query_id = $this->query_result;
		}
		
		if ($query_id) {
			$this->row[$query_id] = @mysql_fetch_array($query_id);
			return $this->row[$query_id];
		}
		
		return false;
	}
	
	public function sql_fetchrowset($query_id = 0) {
		if (!$query_id) {
			$query_id = $this->query_result;
		}
		
		if ($query_id) {
			unset($this->rowset[$query_id]);
			unset($this->row[$query_id]);
			
			while($this->rowset[$query_id] = @mysql_fetch_array($query_id)) {
				$result[] = $this->rowset[$query_id];
			}
			return $result;
		}
		
		return false;
	}
	
	public function sql_fetchfield($field, $rownum = -1, $query_id = 0) {
		if (!$query_id) {
			$query_id = $this->query_result;
		}
		
		if ($query_id) {
			if ($rownum > -1) {
				$result = @mysql_result($query_id, $rownum, $field);
			} else {
				if (empty($this->row[$query_id]) && empty($this->rowset[$query_id])) {
					if ($this->sql_fetchrow()) {
						$result = $this->row[$query_id][$field];
					}
				} else {
					if ($this->rowset[$query_id]) {
						$result = $this->rowset[$query_id][0][$field];
					} else if($this->row[$query_id]) {
						$result = $this->row[$query_id][$field];
					}
				}
			}
			return $result;
		}
		
		return false;
	}
	
	public function sql_rowseek($rownum, $query_id = 0) {
		if (!$query_id) {
			$query_id = $this->query_result;
		}
		
		return ($query_id) ? @mysql_data_seek($query_id, $rownum) : false;
	}
	
	public function sql_nextid() {
		return ($this->db_connect_id) ? @mysql_insert_id($this->db_connect_id) : false;
	}
	
	public function sql_freeresult($query_id = false) {
		if (!$query_id) {
			$query_id = $this->query_result;
		}

		if ($query_id) {
			unset($this->row[$query_id]);
			unset($this->rowset[$query_id]);
			$this->query_result = false;

			@mysql_free_result($query_id);

			return true;
		}
		
		return false;
	}
	
	public function sql_escape($msg) {
		return mysql_escape_string($msg);
	}
	
	public function sql_error($sql = '') {
		if (!$this->return_on_error) {
			$message = @mysql_error() . (($sql != '') ? "\n\n" . $sql : '');
			trigger_error($message, E_USER_ERROR);
		}
		
		$result = array(
			'message' => @mysql_error($this->db_connect_id),
			'code' => @mysql_errno($this->db_connect_id)
		);

		return $result;
	}
}