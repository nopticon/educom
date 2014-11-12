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

class today {
	private $type = array();
	private $elements;
	public $downloads;
	
	public function __construct() {
		return;
	}
	
	public function clear_all($user_id = false) {
		global $user;
		
		$sql = 'DELETE FROM _today_objects
			WHERE object_bio = ?';
		sql_query(sql_filter($sql, $user->d('user_id')));
		
		return true;
	}
	
	public function run() {
		global $user;
		
		$sql = 'SELECT *
			FROM _today_objects o
			INNER JOIN _today_type t ON t.type_id = o.object_type
			WHERE object_bio = ?
			GROUP BY o.object_type
			ORDER BY t.type_order, o.object_relation';
		if (!$elements = sql_rowset(sql_filter($sql, $user->d('user_id')))) {
			return false;
		}
		
		$this->downloads = new downloads();
		
		foreach ($elements as $row) {
			if ($response = $this->{$row->type_alias}()) {
				_style($row->type_alias, array(
					'ID' => $row->type_id)
				);
				
				foreach ($response as $_row) {
					_style($row->type_alias . '.row', $_row);
				}
			}
		}
		
		return;
	}
	
	private function _($name) {
		if (!count($this->type)) {
			$sql = 'SELECT type_id, type_alias
				FROM _today_type
				ORDER BY type_order';
			$this->type = sql_rowset($sql, 'type_alias', 'type_id');
		}
		
		return (isset($this->type[$name])) ? $this->type[$name] : 0;
	}
	
	private function conversations() {
		global $user, $comments;
		
		$sql = 'SELECT c.*, c2.privmsgs_date, m.user_id, m.username, m.username_base
			FROM _dc c, _dc c2, _members m
			INNER JOIN _today_objects t ON t.object_bio = m.user_id
			WHERE t.object_bio = ?
				AND t.object_type = ?
				AND t.object_relation = c.msg_id
				AND c.last_msg_id = c2.msg_id
				AND c2.privmsgs_from_userid = m.user_id 
			ORDER BY c2.privmsgs_date DESC';
		$result = sql_rowset(sql_filter($sql, $user->d('user_id'), __FUNCTION__));
		
		$response = w();
		foreach ($result as $i => $row) {
			$user_profile = $comments->user_profile($row);
			
			$response[] = array(
				'S_MARK_ID' => $row->parent_id,
				'U_READ' => s_link('my dc read', $row->last_msg_id),
				'SUBJECT' => $row->privmsgs_subject,
				'DATETIME' => $user->format_date($row->privmsgs_date),
				'USER_ID' => $row->user_id,
				'USERNAME' => $row->username,
				'U_USERNAME' => $user_profile->profile
			);
		}
		
		return $response;
	}
	
	private function board() {
		global $user, $comments;
		
		$sql = 'SELECT t.*, f.forum_alias, f.forum_id, f.forum_name, p.post_id, p.post_username, p.post_time, m.user_id, m.username, m.username_base 
			FROM _members_unread u, _forums f, _forum_topics t, _forum_posts p, _members m 
			WHERE u.user_id = ? 
				AND f.forum_id NOT IN (??)
				AND u.element = ? 
				AND u.item = t.topic_id 
				AND t.topic_id = p.topic_id 
				AND t.topic_last_post_id = p.post_id 
				AND t.forum_id = f.forum_id 
				AND p.poster_id = m.user_id 
			ORDER BY t.topic_announce DESC, p.post_time DESC';
		$result = sql_rowset(sql_filter($sql, $user->d('user_id'), '22' . forum_for_team_not(), UH_T));
		
		$response = w();
		foreach ($result as $i => $row) {
			$user_profile = $comments->user_profile($row);
			
			$response[] = array(
				'S_MARK_ID' => $row->topic_id,
				'FOR_MODS' => in_array($row->forum_id, forum_for_team_array()),
				'TOPIC_URL' => s_link('post', $row->post_id) . '#' . $row->post_id,
				'TOPIC_TITLE' => $row->topic_title,
				'TOPIC_REPLIES' => $row->topic_replies,
				'TOPIC_COLOR' => $row->topic_color,
				'FORUM_URL' => s_link('forum', $row->forum_alias),
				'FORUM_NAME' => $row->forum_name,
				'DATETIME' => $user->format_date($row->post_time),
				'USER_ID' => $row->user_id,
				'USER_PROFILE' => $user_profile->profile,
				'USERNAME' => $user_profile->username
			);
		}
		
		return $response;
	}
}