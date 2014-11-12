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

class __forums_topic_delete extends mac {
	public function __construct() {
		parent::__construct();
		
		$this->auth('mod');
	}
	
	public function _home() {
		global $config, $user, $cache;
		
		if (!_button()) {
			return false;
		}
		
		$topics = array_map('intval', explode(',', request_var('topic_id', '')));
		$forums_id_sql = $topics_id = w();
		
		$sql = 'SELECT forum_id, topic_id
			FROM _forum_topics
			WHERE topic_id IN (??)';
		$result = sql_rowset(sql_filter($sql, _implode(', ', $topics)));
		
		foreach ($result as $row) {
			$forums_id_sql[] = (int) $row->forum_id;
			$topics_id[] = (int) $row->topic_id;
		}
		
		$topic_id_sql = _implode(',', $topics_id);
		
		//
		$sql = 'SELECT post_id
			FROM _forum_posts
			WHERE topic_id IN (??)';
		$posts_id = sql_rowset(sql_filter($sql, $topic_id_sql), false, 'post_id');
		$post_id_sql = _implode(',', $posts_id);
		
		//
		$sql = 'SELECT vote_id
			FROM _poll_options
			WHERE topic_id IN (??)';
		$votes_id = sql_rowset(sql_filter($sql, $topic_id_sql), false, 'vote_id');
		$vote_id_sql = _implode(',', $votes_id);
		
		//
		$sql = 'SELECT poster_id, COUNT(post_id) AS posts
			FROM _forum_posts
			WHERE topic_id IN (??)
			GROUP BY poster_id';
		$result = sql_rowset(sql_filter($sql, $topic_id_sql));
		
		$members_sql = w();
		foreach ($result as $row) {
			$sql = 'UPDATE _members SET user_posts = user_posts - ??
				WHERE user_id = ?';
			$members_sql[] = sql_filter($sql, $row->posts, $row->poster_id);
		}
		
		sql_query($members_sql);
		
		//
		// Got all required info so go ahead and start deleting everything
		//
		$sql = 'UPDATE _forum_topics SET topic_active = 0
			WHERE topic_id IN (??)';
		sql_query(sql_filter($sql, $topic_id_sql));
		
		$sql = 'DELETE
			FROM _forum_topics_fav
			WHERE topic_id IN (??)';
		sql_query(sql_filter($sql, $topic_id_sql));
		
		if ($post_id_sql != '') {
			$sql = 'UPDATE _forum_posts SET post_active = 0
				WHERE post_id IN (??)';
			sql_query(sql_filter($sql, $post_id_sql));
		}
	
		//
		/*$sql = 'DELETE FROM _members_unread
			WHERE element = 8
				AND item IN (??)';
		sql_query(sql_filter($sql, $topic_id_sql));*/
		
		//
		foreach ($forums_id_sql as $forum_id) {
			sync_topic_delete($forum_id);
		}
		
		return _pre('El tema fue eliminado.', true);
	}
}

function sync_topic_delete($id) {
	$last_topic = 0;
	$total_posts = 0;
	$total_topics = 0;
	
	//
	$sql = 'SELECT COUNT(post_id) AS total 
		FROM _forum_posts
		WHERE forum_id = ?';
	$total_posts = sql_field(sql_filter($sql, $id), 'total', 0);
	
	$sql = 'SELECT MAX(topic_id) as last_topic, COUNT(topic_id) AS total
		FROM _forum_topics
		WHERE forum_id = ?';
	if ($row = sql_fieldrow(sql_filter($sql, $id))) {
		$last_topic = $row->last_topic;
		$total_topics = $row->total;
	}
	
	//
	$sql = 'UPDATE _forums SET forum_last_topic_id = ?, forum_posts = ?, forum_topics = ?
		WHERE forum_id = ?';
	sql_query(sql_filter($sql, $last_topic, $total_posts, $total_topics, $id));
	
	return;
}