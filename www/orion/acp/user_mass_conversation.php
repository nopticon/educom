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

class __user_mass_conversation extends mac {
	public function __construct() {
		parent::__construct();
		
		$this->auth('founder');
	}
	
	public function _home() {
		global $config, $user, $cache, $comments;
		
		if (!_button()) {
			return false;
		}
		
		$post_mode = request_var('post_mode', 0);
		$post_subject = request_var('post_subject', '');
		$post_message = request_var('post_message', '', true);
		$post_skip = request_var('post_skip', '', true);
		$post_reply = request_var('post_reply', 0);
		
		$post_message = $comments->prepare($post_message);
		
		$skip_list = '';
		if (!empty($post_skip)) {
			$e_skip = explode(nr(), $post_skip);
			
			foreach ($e_skip as $i => $row) {
				$row = get_username_base($row);
				$e_skip[$i] = "'" . sql_escape($row) . "'";
			}
			
			$sql = 'SELECT user_id
				FROM _members
				WHERE username_base IN (' . _implode(',', $e_skip) . ')';
			$user_skip = sql_rowset($sql, false, 'user_id');
			
			$skip_list = ' AND u.user_id NOT IN (' . _implode(', ', $user_skip) . ') ';
		}
		
		switch ($post_mode) {
			case 1:
				$sql = 'SELECT u.user_id, u.username
					FROM _members u
					WHERE u.user_type <> 2
						AND u.user_id NOT IN (SELECT ban_userid FROM _banlist)
						AND u.user_id <> ?' . $skip_list . '
					ORDER BY u.username';
				break;
			case 2:
				$sql = 'SELECT u.user_id, u.username
					FROM _members u
					WHERE u.user_type = 6
						AND u.user_id NOT IN (SELECT ban_userid FROM _banlist)
						AND u.user_id <> ' . $skip_list . '
					ORDER BY u.username';
				break;
			case 3:
				$sql = 'SELECT u.user_id, u.username
					FROM _members_friends b, _members u
					WHERE b.buddy_id = ' . $skip_list . '
						AND b.user_id = u.user_id
						AND u.user_id NOT IN (SELECT ban_userid FROM _banlist)
					ORDER BY u.username';
				break;
		}
		$result = sql_rowset(sql_filter($sql, $user->d('user_id')));
		
		foreach ($result as $row) {
			$row_message = str_replace('[username]', $row->username, $post_message);
			
			$insert = array(
				'privmsgs_subject' => $post_subject,
				'privmsgs_from_userid' => (int) $user->d('user_id'),
				'privmsgs_to_userid' => (int) $row->user_id,
				'privmsgs_date' => $user->time,
				'msg_ip' => $user->ip,
				'msg_can_reply' => (int) $post_reply,
				'privmsgs_mass' => 1,
				'privmsgs_text' => $row_message
			);
			$dc_id = sql_insert('dc', $insert);
			
			$sql = 'UPDATE _dc SET parent_id = ?, last_msg_id = ?, msg_deleted = ?
				WHERE msg_id = ?';
			sql_query(sql_filter($sql, $dc_id, $dc_id, $user->d('user_id'), $dc_id));
			
			// TODO: Today save
			// $user->save_unread(UH_NOTE, $dc_id, 0, $row->user_id);
			
			echo $row->username . '<br />';
			flush();
		}
		
		return;
	}
}