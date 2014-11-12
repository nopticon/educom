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

class __user_name_change extends mac {
	public function __construct() {
		parent::__construct();
		
		$this->auth('founder');
	}
	
	public function _home() {
		global $config, $user, $cache;
		
		if (!_button()) {
			return false;
		}
		
		$username1 = request_var('username1', '');
		$username2 = request_var('username2', '');
		if (empty($username1) || empty($username2)) {
			fatal_error();
		}
		
		$username_base1 = get_username_base($username1);
		$username_base2 = get_username_base($username2);
		
		$sql = 'SELECT *
			FROM _members
			WHERE username_base = ?';
		if (!$userdata = sql_fieldrow(sql_filter($sql, $username_base1))) {
			_pre('El usuario no existe.', true);
		}
		
		$sql = 'SELECT *
			FROM _members
			WHERE username_base = ?';
		if ($void = sql_fieldrow(sql_filter($sql, $username_base2))) {
			_pre('El usuario ya existe.', true);
		}
		
		//
		$sql = 'UPDATE _members SET username = ?, username_base = ?
			WHERE user_id = ?';
		sql_query(sql_filter($sql, $username2, $username_base2, $userdata->user_id));
		
		$emailer = new emailer();
		
		$emailer->from('info');
		$emailer->use_template('username_change', $config->default_lang);
		$emailer->email_address($userdata->user_email);
		
		$emailer->assign_vars(array(
			'USERNAME' => $userdata->username,
			'NEW_USERNAME' => $username2,
			'U_USERNAME' => s_link('m', $username_base2))
		);
		$emailer->send();
		$emailer->reset();
		
		redirect(s_link('m', $username_base2));
		
		return;
	}
}