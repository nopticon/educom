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

class __user_points extends mac {
	public function __construct() {
		parent::__construct();
		
		$this->auth('founder');
	}
	
	public function _home() {
		global $config, $user, $cache;
		
		$sql = 'SELECT user_id, username, username_base, user_points
			FROM _members
			WHERE user_points <> 0
			ORDER BY user_points DESC, username';
		$result = sql_rowset($sql);
		
		foreach ($result as $i => $row) {
			if (!$i) _style('members');
			
			_style('members.row', array(
				'BASE' => s_link('m', $row->username_base),
				'USERNAME' => $row->username,
				'POINTS' => $row->user_points)
			);
		}
		
		return;
	}
}