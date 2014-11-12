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

class __forum_order extends mac {
	public function __construct() {
		parent::__construct();
		
		$this->auth('founder');
	}
	
	public function _home() {
		global $config, $user, $cache;
		
		if (!_button()) {
			$sql = 'SELECT forum_id, forum_name
				FROM _forums
				ORDER BY forum_order ASC';
			$result = sql_rowset($sql);
			
			foreach ($result as $i => $row) {
				if (!$i) _style('forums');
				
				_style('forums.row', array(
					'FORUM_ID' => $row->forum_id,
					'FORUM_NAME' => $row->forum_name)
				);
			}	
			
			return false;
		}
		
		$list = request_var('listContainer', array(0));
		
		$orderid = 10;
		foreach ($list as $catid) {
			$sql = 'UPDATE _forums SET forum_order = ?
				WHERE forum_id = ?';
			sql_query(sql_filter($sql, $orderid, $catid));
			
			$orderid += 10;
		}
		
		_pre('Update.', true);
	}
}