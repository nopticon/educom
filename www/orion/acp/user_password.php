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

class __user_password extends mac {
	public function __construct() {
		parent::__construct();
		
		$this->auth('founder');
	}
	
	public function _home() {
		global $config, $user, $cache;
		
		if (!_button()) {
			return false;
		}
		
		$username = request_var('username', '');
		$password = request_var('password', '');
		
		$username = get_username_base($username);
		
		$sql = 'SELECT user_id, username
			FROM _members
			WHERE username_base = ?';
		if (!$userdata = sql_fieldrow(sql_filter($sql, $username))) {
			fatal_error();
		}
		
		$sql = 'UPDATE _members SET user_password = ?
			WHERE user_id = ?';
		sql_query(sql_filter($sql, HashPassword($password), $userdata->user_id));
		
		return _pre('La contrase&ntilde;a de ' . $userdata->username . ' fue actualizada.', true);
	}
}