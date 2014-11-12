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

class __user_address extends mac {
	public function __construct() {
		parent::__construct();
		
		$this->auth('founder');
	}
	
	public function _home() {
		global $config, $user, $cache;
		
		$limit = 225;
		$steps = 0;
		$items = 0;
		$trash = w();
		
		//
		$sql = "SELECT *
			FROM _members
			WHERE user_type NOT IN (??)
				AND user_email <> ''
				AND user_id NOT IN (
					SELECT ban_userid
					FROM _banlist
					WHERE ban_userid <> 0
				)
			ORDER BY username";
		$result = sql_rowset(sql_filter($sql, USER_INACTIVE));
		
		foreach ($result as $row) {
			if (!preg_match('/^[a-z0-9&\'\.\-_\+]+@[a-z0-9\-]+\.([a-z0-9\-]+\.)*?[a-z]+$/is', $row->user_email)) {
				$trash[] = $row->user_email;
				continue;
			}
			
			if (!$items || $items == $limit) {
				$items = 0;
				$steps++;
				
				_style('step', array(
					'STEPS' => $steps)
				);
			}
			
			_style('step.item', array(
				'USERNAME' => $row->username,
				'USER_EMAIL' => $row->user_email)
			);
		
			$items++;
		}
		
		return;
	}
}