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

class __user_ban extends mac {
	public function __construct() {
		parent::__construct();
		
		$this->auth('founder');
	}
	
	public function _home() {
		global $config, $user, $cache;
		
		if ($this->create()) {
			return;
		}
		
		return;
	}

	private function create() {
		$v = _request(array('username' => ''));

		if (_empty($v)) return;
		
		$v->username = get_username_base($v->username);
		
		$sql = 'SELECT *
			FROM _members
			WHERE username_base = ?';
		if (!$result = sql_fieldrow(sql_filter($sql, $v->username))) {
			return;
		}
		
		$sql = 'SELECT *
			FROM _banlist
			WHERE ban_userid = ?';
		if (!$ban = sql_fieldrow(sql_filter($sql, $result->user_id))) {
			$insert = array(
				'ban_userid' => $result->user_id
			);
			sql_insert('banlist', $insert);
			
			$sql = 'DELETE FROM _sessions
				WHERE session_user_id = ?';
			sql_query(sql_filter($sql, $result->user_id));
			
			echo 'El usuario ' . $result->username . ' fue bloqueado.';
		}

		return true;
	}
}