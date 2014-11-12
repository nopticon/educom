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

class __forums_post_delete extends mac {
	public function __construct() {
		parent::__construct();

		$this->auth('mod');
	}

	public function _home() {
		global $config, $user, $cache;

		$post_id = request_var('msg_id', 0);

		if (!$post_id) {
			fatal_error();
		}

		$sql = 'SELECT f.*, t.topic_id, t.topic_first_post_id, t.topic_last_post_id, t.topic_vote, t.topic_replies, p.post_id, p.poster_id, m.user_id
			FROM _forum_posts p, _forum_topics t, _forums f, _members m
			WHERE p.post_id = ?
				AND p.post_active = 1
				AND t.topic_id = p.topic_id
				AND f.forum_id = p.forum_id
				AND m.user_id = p.poster_id';
		if (!$post_info = sql_fieldrow(sql_filter($sql, $post_id))) {
			fatal_error();
		}

		$topic_id = $post_info->topic_id;

		$post_data = (object) array(
			'poster_post' => ($post_info->poster_id == $user->d('user_id')) ? true : false,
			'first_post' => ($post_info->topic_first_post_id == $post_id) ? true : false,
			'last_post' => ($post_info->topic_last_post_id == $post_id) ? true : false,
			'last_topic' => ($post_info->forum_last_topic_id == $topic_id) ? true : false,
			'has_poll' => ($post_info->topic_vote) ? true : false
		);

		//
		// Update stats
		//
		$forum_update_sql = array(
			'forum_posts = forum_posts - 1'
		);
		$topic_update_sql = array();

		if ($post_data->last_post) {
			if ($post_data->first_post) {
				$forum_update_sql[] = 'forum_topics = forum_topics - 1';
			} else {
				if ($post_info->topic_replies) {
					$topic_update_sql[] = 'topic_replies = topic_replies - 1';
				}

				$sql = 'SELECT MAX(post_id) AS last_post_id
					FROM _forum_posts
					WHERE topic_id = ?
						AND topic_active = 1';
				if ($last_post_id = sql_field(sql_filter($sql, $topic_id), 'last_post_id', 0)) {
					$topic_update_sql[] = 'topic_last_post_id = ' . $last_post_id;
				}
			}

			if ($post_data->last_topic) {
				$sql = 'SELECT MAX(topic_id) AS last_topic_id
					FROM _forum_topics
					WHERE forum_id = ?
						AND topic_id <> ?
						AND topic_active = 1';
				if ($last_topic_id = sql_field(sql_filter($sql, $post_info->forum_id, $topic_id), 'last_topic_id', 0)) {
					$forum_update_sql[] = 'forum_last_topic_id = ' . $last_topic_id;
				}
			}
		} else if ($post_data->first_post) {
			$sql = 'SELECT MIN(post_id) AS first_post_id
				FROM _forum_posts
				WHERE topic_id = ?
					AND post_active = 1';
			if ($first_post_id = sql_field(sql_filter($sql, $topic_id), 'first_post_id', 0)) {
				if ($post_info->topic_replies) {
					$topic_update_sql[] = 'topic_replies = topic_replies - 1';
				}

				$topic_update_sql[] = 'topic_first_post_id = ' . $first_post_id;
			}
		} else {
			if ($post_info->topic_replies) {
				$topic_update_sql[] = 'topic_replies = topic_replies - 1';
			}
		}

		// _pre($post_info);
		// _pre($post_data);
		// _pre($topic_update_sql);
		// _pre($forum_update_sql, true);

		//
		// Process
		//
		$sql = 'UPDATE _forum_posts SET post_active = 0
			WHERE post_id = ?';
		sql_query(sql_filter($sql, $post_id));

		if ($post_data->first_post) {
			$sql = 'UPDATE _forum_topics SET topic_active = 0
				WHERE topic_id = ?';
			sql_query(sql_filter($sql, $topic_id));
		}

		$sql = 'UPDATE _forums SET ' . implode(',', $forum_update_sql) . '
			WHERE forum_id = ?';
		sql_query(sql_filter($sql, $post_info->forum_id));

		if (count($topic_update_sql)) {
			$sql = 'UPDATE _forum_topics SET ' . implode(',', $topic_update_sql) . '
				WHERE topic_id = ?';
			sql_query(sql_filter($sql, $topic_id));
		}

		if ($post_data->first_post) {
			redirect(s_link('forum', $post_info->forum_alias));
		}

		redirect(s_link('topic', $topic_id));
	}
}

function sync_post_delete($id) {
	$last_topic = $total_posts = $total_topics = 0;

	//
	$sql = 'SELECT COUNT(post_id) AS total
		FROM _forum_posts
		WHERE forum_id = ?
			AND post_active = 1';
	$total_posts = sql_field(sql_filter($sql, $id), 'total', 0);

	$sql = 'SELECT MAX(topic_id) as last_topic, COUNT(topic_id) AS total
		FROM _forum_topics
		WHERE forum_id = ?
			AND topic_active = 1';
	if ($row = sql_fieldrow(sql_filter($sql, $id))) {
		$last_topic = $row->last_topic;
		$total_topics = $row->total;
	}

	//
	$sql = 'UPDATE _forums SET forum_last_topic_id = ?, forum_posts = ?, forum_topics = ?
		WHERE forum_id = ?';
	sql_query(sql_filter($sql, $last_topic, $total_posts, $total_topics, $id));

	return sql_affected();
}