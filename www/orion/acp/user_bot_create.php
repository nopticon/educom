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

class __user_bot_create extends mac {
	public function __construct() {
		parent::__construct();
		
		$this->auth('founder');
	}
	
	public function _home() {
		global $config, $user, $cache;
		
		if ($submit) {
			return false;
		}
		
		$bot_name = request_var('bot_name', '');
		$bot_agent = request_var('bot_agent', '');
		$bot_ip = request_var('bot_ip', '');
		$bot_base = get_username_base($bot_name);
		
		$sql = 'SELECT *
			FROM _bots
			WHERE bot_name = ?';
		
		$insert = true;
		if ($row = sql_fieldrow(sql_filter($sql, $bot_name))) {
			$insert = false;
			
			if ($row->bot_ip != $bot_ip) {
				$sql = 'UPDATE _bots SET bot_ip = ?
					WHERE bot_id = ?';
				sql_query(sql_filter($sql, $row->bot_ip . ',' . $bot_ip, $row->bot_id));
			}
		}
		
		if ($insert) {
			$insert_member = array(
				'user_type' => 2,
				'user_active' => 1,
				'username' => $bot_name,
				'username_base' => $bot_base,
				'user_timezone' => -6.00,
				'user_lang' => 'spanish'
			);
			$bot_id = sql_insert('members', $insert_member);
			
			$insert_bot = array(
				'bot_active' => 1,
				'bot_name' => $bot_name,
				'user_id' => $bot_id,
				'bot_agent' => $bot_agent,
				'bot_ip' => $bot_ip,
			);
			sql_insert('bots', $insert_bot);
		}
		
		$sql = "DELETE FROM _sessions
			WHERE session_browser LIKE '%??%'";
		sql_query(sql_filter($sql, $bot_name));
		
		$cache->delete('bots');
		
		return;
	}
}