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

header('Content-type: text/html; charset=utf-8');

class broadcast { 
	private $_template;
	private $_title;
	
	public function __construct() {
		return;
	}
	
	public function get_title($default = '') {
		return (!empty($this->_title)) ? $this->_title : $default;
	}
	
	public function get_template($default = '') {
		return (!empty($this->_template)) ? $this->_template : $default;
	}

	private function v($property) {
		if (!isset($this->data->$property)) {
			return false;
		}
		
		return $this->data->$property;
	}

	public function run() {
		return $this->all();

		// return $this->object();
	}

	private function all() {
		global $user, $config, $comments;

		$sql = 'SELECT *
			FROM _partners
			ORDER BY partner_order';
		$partners = sql_rowset($sql);

		foreach ($partners as $i => $row) {
			if (!$i) _style('partners');
			
			_style('partners.row', array(
				'NAME' => $row->partner_name,
				'IMAGE' => $row->partner_image,
				'URL' => $config->assets_url . '/style/sites/' . $row->partner_url)
			);
		}

		return;
	}

	private function object() {
		global $user, $config, $comments;

		$this->_title = '';
		$this->_template = '';

		return;
	}
}