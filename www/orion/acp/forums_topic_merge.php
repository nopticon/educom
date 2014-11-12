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

class __forums_topic_merge extends mac {
	public function __construct() {
		parent::__construct();
		
		$this->auth('mod');
	}
	
	public function _home() {
		global $config, $user, $cache;
		
		if (!_button()) {
			return false;
		}
		
		$from_topic = request_var('from_topic', 0);
		$to_topic = request_var('to_topic', 0);
		
		if (!$from_topic || !$to_topic || $from_topic == $to_topic) {
			fatal_error();
		}
	
		$sql = 'SELECT *
			FROM _forum_topics
			WHERE topic_id = ?';
		if (!$row = sql_fieldrow(sql_filter($sql, $from_topic))) {
			fatal_error();
		}
		
		$sql = 'SELECT *
			FROM _forum_topics
			WHERE topic_id = ?';
		if (!$row = sql_fieldrow(sql_filter($sql, $to_topic))) {
			fatal_error();
		}
		
		$from_forum_id = (int) $row->forum_id;
		$from_poll = (int) $row->topic_vote;
		$to_forum_id = (int) $row->forum_id;
		$to_poll = (int) $row->topic_vote;
		
		if ($from_poll) {
			if ($to_poll) {
				$sql = 'SELECT vote_id
					FROM _poll_options
					WHERE topic_id = ?';
				if ($vote_id = sql_field(sql_filter($sql, $from_topic), 'vote_id', 0)) {
					$sql = array(
						sql_filter('DELETE FROM _poll_voters WHERE vote_id = ?', $vote_id),
						sql_filter('DELETE FROM _poll_results WHERE vote_id = ?', $vote_id),
						sql_filter('DELETE FROM _poll_options WHERE vote_id = ?', $vote_id)
					);
					sql_query($sql);
				}
			} else {
				$sql = 'UPDATE _poll_options SET topic_id = ?
					WHERE topic_id = ?';
				sql_query(sql_filter($sql, $to_topic, $from_topic));
			}
		}
		
		//
		// Update destination topic
		//
		$sql = 'SELECT topic_views
			FROM _forum_topics
			WHERE topic_id = ?';
		if ($topic_views = sql_field(sql_filter($sql, $from_topic), 'topic_views', 0)) {
			$sql = 'UPDATE _forum_topics SET topic_views = topic_views + ??
				WHERE topic_id = ?';
			sql_query(sql_filter($sql, $topic_views, $to_topic));
		}
		
		//
		// Select user_id's favourites on this topic
		//
		$sql = 'SELECT *
			FROM _forum_topics_fav
			WHERE topic_id = ?';
		$user_ids = sql_rowset(sql_filter($sql, $to_topic), false, 'user_id');
		
		$sql_user = (count($user_ids)) ? ' AND user_id NOT IN (' . _implode(', ', $user_ids) . ')' : '';
		
		$sql = array(
			sql_filter('UPDATE _forum_topics_fav SET topic_id = ? WHERE topic_id = ?', $to_topic, $from_topic) . $sql_user,
			sql_filter('DELETE FROM _forum_topics_fav WHERE topic_id = ?', $from_topic),
			sql_filter('UPDATE _forum_posts SET forum_id = ?, topic_id = ? WHERE topic_id = ?', $to_forum_id, $to_topic, $from_topic),
			sql_filter('DELETE FROM _forum_topics WHERE topic_id = ?', $from_topic),
			sql_filter('DELETE FROM _members_unread WHERE element = ? AND item = ?', UH_T, $from_topic),
		);
		
		if ($from_poll && !$to_poll) {
			$sql[] = sql_filter('UPDATE _forum_topics SET topic_vote = 1 WHERE topic_id = ?', $to_topic);
		}
		sql_query($sql);

		// TODO: Today save
		// $user->save_unread(UH_T, $to_topic);
		
		if (in_array($to_forum_id, array(20, 39))) {
			topic_feature($to_topic, 0);
			topic_arkane($to_topic, 0);
		}
		
		sync_topic_merge('topic', $to_topic);
		sync_topic_merge('forum', $to_forum_id);
		
		if ($from_forum_id != $to_forum_id) {
			sync_topic_merge('forum', $from_forum_id);
		}

		return;
	}
}

function sync_topic_merge($type, $id = false) {
	switch($type) {
		case 'all forums':
			$sql = 'SELECT forum_id
				FROM _forums';
			$result = sql_rowset($sql);
			
			foreach ($result as $row) {
				sync('forum', $row->forum_id);
			}
			break;
		case 'all topics':
			$sql = 'SELECT topic_id
				FROM _forum_topics';
			$result = sql_rowset($sql);
			
			foreach ($result as $row) {
				sync('topic', $row->topic_id);
			}
			break;
		case 'forum':
			$sql = 'SELECT COUNT(post_id) AS total
				FROM _forum_posts
				WHERE forum_id = ?';
			$total_posts = sql_field(sql_filter($sql, $id), 'total', 0);
			
			$sql = 'SELECT topic_id
				FROM _forum_posts
				WHERE forum_id = ?
				ORDER BY post_time DESC
				LIMIT 1';
			$last_topic = sql_field(sql_filter($sql, $id), 'topic_id', 0);
			
			$sql = 'SELECT COUNT(topic_id) AS total
				FROM _forum_topics
				WHERE forum_id = ?';
			$total_topics = sql_field(sql_filter($sql, $id), 'total', 0);
			
			$sql = 'UPDATE _forums SET forum_last_topic_id = ?, forum_posts = ?, forum_topics = ?
				WHERE forum_id = ?';
			sql_query(sql_filter($sql, $last_topic, $total_posts, $total_topics, $id));
			break;
		case 'topic':
			$sql = 'SELECT MAX(post_id) AS last_post, MIN(post_id) AS first_post, COUNT(post_id) AS total_posts
				FROM _forum_posts
				WHERE topic_id = ?';
			if ($row = sql_fieldrow(sql_filter($sql, $id))) {
				if ($row->total_posts) {
					$sql = 'UPDATE _forum_topics SET topic_replies = ?, topic_first_post_id = ?, topic_last_post_id = ?
						WHERE topic_id = ?';
					$sql = sql_filter($sql, ($row->total_posts - 1), $row->first_post, $row->last_post, $id);
				} else {
					$sql = sql_filter('DELETE FROM _forum_topics WHERE topic_id = ?', $id);
				}
				sql_query($sql);
			}
			break;
	}
	
	return true;
}