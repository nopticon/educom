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
if (!defined('IN_APP')) exit;

class win {
	private $object;
	private $_title;
	private $_template;
	
	public function __construct() {
		return;
	}
	
	public function get_title($default = '') {
		return (!empty($this->_title)) ? $this->_title : $default;
	}
	
	public function get_template($default = '') {
		return (!empty($this->_template)) ? $this->_template : $default;
	}
	
	public function run() {
		$alias = request_var('alias', '');
		
		if (empty($alias)) {
			return $this->elements();
		}
		
		$sql = 'SELECT *
			FROM _win
			WHERE win_alias = ?';
		if (!$this->object = sql_fieldrow(sql_filter($sql, $alias))) {
			fatal_error();
		}
		
		return $this->run_object();
	}
	
	private function elements() {
		$sql = 'SELECT *
			FROM _win
			ORDER BY win_date';
		$win = sql_rowset($sql);
		
		foreach ($win as $i => $row) {
			if (!$ui) _style('win');
			
			_style('win.row', array(
				
			));
		}
		return;
	}
	
	private function run_object() {
		if (_button()) {
			return $this->store();
		}
			
		return;
	}
	
	private function store() {
		return;
	}
}