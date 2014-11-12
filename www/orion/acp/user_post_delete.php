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

class __user_post_delete extends mac {
	private $id;
	
	public function __construct() {
		parent::__construct();
		
		$this->auth('user');
	}
	
	public function _home() {
		global $config, $user, $cache;
		
		$this->id = request_var('msg_id', 0);
		
		$sql = 'SELECT *
			FROM _members_posts
			WHERE post_id = ?';
		if (!$this->object = sql_fieldrow(sql_filter($sql, $this->id))) {
			fatal_error();
		}
		
		$this->object = (object) $this->object;
		
		if (!$user->is('founder') && $user->d('user_id') != $this->object->userpage_id) {
			fatal_error();
		}
		
		$sql = 'SELECT username_base
			FROM _members
			WHERE user_id = ?';
		$username_base = sql_field(sql_filter($sql, $this->object->userpage_id), 'username_base', '');
		
		$sql = 'DELETE FROM _members_posts
			WHERE post_id = ?';
		sql_query(sql_filter($sql, $this->id));
		
		$sql = 'UPDATE _members
			SET userpage_posts = userpage_posts - 1
			WHERE user_id = ?';
		sql_query(sql_filter($sql, $this->object->userpage_id));
		
		// TODO: Today save
		// $user->delete_unread(UH_UPM, $this->id);
		
		if ($this->object->post_time > points_start_date() && $this->object->post_time < 1203314400) {
			//$user->points_remove(1, $this->object->poster_id);
		}
		
		return redirect(s_link('m', $username_base));
	}
}