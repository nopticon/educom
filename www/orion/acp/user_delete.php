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

class __user_delete extends mac {
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
		$username = get_username_base($username);
		
		$sql = 'SELECT *
			FROM _members
			WHERE username_base = ?';
		if (!$userdata = sql_fieldrow(sql_filter($sql, $username))) {
			fatal_error();
		}
		
		$ary_sql = array(
			'DELETE FROM _members WHERE user_id = ?',
			'DELETE FROM _banlist WHERE ban_userid = ?',
			'DELETE FROM _members_group WHERE user_id = ?',
			'DELETE FROM _members_iplog WHERE log_user_id = ?',
			'DELETE FROM _members_ref_invite WHERE invite_uid = ?',
			'DELETE FROM _members_unread WHERE user_id = ?',
			'DELETE FROM _poll_voters WHERE vote_user_id = ?',
			'DELETE FROM _artists_auth WHERE user_id = ?',
			'DELETE FROM _artists_viewers WHERE user_id = ?',
			'DELETE FROM _artists_voters WHERE user_id = ?',
			'DELETE FROM _dl_voters WHERE user_id = ?',
			
			'UPDATE _members_posts SET poster_id = 1 WHERE poster_id = ?',
			'UPDATE _news_posts SET poster_id = 1 WHERE poster_id = ?',
			'UPDATE _artists_posts SET poster_id = 1 WHERE poster_id = ?',
			'UPDATE _dl_posts SET poster_id = 1 WHERE poster_id = ?',
			'UPDATE _events_posts SET poster_id = 1 WHERE poster_id = ?',
			'UPDATE _forum_posts SET poster_id = 1 WHERE poster_id = ?',
			'UPDATE _forum_topics SET topic_poster = 1 WHERE topic_poster = ?'
		);
		
		$sql = w();
		foreach ($ary_sql as $row) {
			$sql[] = sql_filter($row, $userdata->user_id);
		}
		
		$ary_sql = array(
			'DELETE FROM _members_ban WHERE user_id = ? OR banned_user = ?',
			'DELETE FROM _members_friends WHERE user_id = ? OR buddy_id = ?',
			'DELETE FROM _members_ref_assoc WHERE ref_uid = ? OR ref_orig = ?',
			'DELETE FROM _members_viewers WHERE viewer_id = ? OR user_id = ?',
		);
		
		foreach ($ary_sql as $row) {
			$sql[] = sql_filter($row, $userdata->user_id, $userdata->user_id);
		}
		
		sql_query($sql);
		
		return _pre('El registro de <strong>' . $userdata->username . '</strong> fue eliminado.', true);
	}
}