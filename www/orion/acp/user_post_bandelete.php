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

class __user_post_bandelete extends mac {
	public function __construct() {
		parent::__construct();
		
		$this->auth('founder');
	}
	
	public function _home() {
		global $config, $user, $cache;
		
		if (!_button()) {
			return false;
		}
		
		$msg_id = request_var('msg_id', 0);
		
		$sql = 'SELECT *
			FROM _members_posts
			WHERE post_id = ?';
		if (!$d = sql_fieldrow(sql_filter($sql, $msg_id))) {
			fatal_error();
		}
		
		$sql = 'DELETE FROM _members_posts
			WHERE post_id = ?';
		sql_query(sql_filter($sql, $msg_id));
		
		$sql = 'UPDATE _members SET userpage_posts = userpage_posts - 1
			WHERE user_id = ?';
		sql_query(sql_filter($sql, $d->userpage_id));
		
		if (_button('user')) {
			$sql = 'SELECT ban_id
				FROM _banlist
				WHERE ban_userid = ?';
			if (!$row = sql_fieldrow(sql_filter($sql, $d->poster_id))) {
				sql_insert('banlist', array('ban_userid' => $d->poster_id));
			}
		}
		
		if (_button('ip')) {
			$sql = 'SELECT ban_id
				FROM _banlist
				WHERE ban_ip = ?';
			if (!$row = sql_fieldrow(sql_filter($sql, $d->post_ip))) {
				$sql_insert = array(
					'ban_ip' => $d->post_ip
				);
				sql_insert('banlist', $sql_insert);
			}
		}
		
		return _pre($d, true);
	}
}