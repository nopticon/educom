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

class board {
	public $cat_data = array();
	public $forum_data = array();
	public $msg;

	public function __construct() {
		return;
	}

	public function run() {
		$cat = $this->categories();
		$forums = $this->forums();

		if (!$cat || !$forums) {
			fatal_error();
		}

		// $this->delete();

		$this->forum_list();
		$this->popular();
		$this->newest();

		return true;
	}

	public function delete() {
		$sql = 'SELECT t.topic_id FROM _forum_topics t WHERE t.topic_id NOT IN (
			SELECT p.topic_id FROM _forum_posts p
		)';
		$list = sql_rowset($sql);

		// _pre($list, true);

		$process = array();
		foreach ($list as $row) {
			$sql = 'DELETE FROM _forum_topics WHERE topic_id = ?';
			sql_query(sql_filter($sql, $row->topic_id));

			$process[] = $row->topic_id;
		}

		_pre($process, true);
	}

	public function categories() {
		global $cache;

		if (!$this->cat_data = $cache->get('forum_categories')) {
			$sql = 'SELECT cat_id, cat_title
				FROM _forum_categories
				ORDER BY cat_order';
			if ($this->cat_data = sql_rowset($sql)) {
				$cache->save('forum_categories', $this->cat_data);
			}
		}

		if (!count($this->cat_data)) {
			return false;
		}

		return true;
	}

	public function forums() {
		$sql = 'SELECT f.*, t.topic_id, t.topic_title, p.post_id, p.post_time, p.post_username, u.user_id, u.username, u.username_base
			FROM (( _forums f
			LEFT JOIN _forum_topics t ON t.topic_id = f.forum_last_topic_id
			LEFT JOIN _forum_posts p ON p.post_id = t.topic_last_post_id)
			LEFT JOIN _members u ON u.user_id = p.poster_id)
			WHERE f.forum_active = 1
			ORDER BY f.cat_id, f.forum_order';
		if (!$this->forum_data = sql_rowset($sql)) {
			return false;
		}

		return true;
	}

	public function forum_list() {
		global $user, $auth;

		$is_auth_ary = w();
		$is_auth_ary = $user->auth->forum(AUTH_VIEW, AUTH_LIST_ALL, $this->forum_data);

		foreach ($this->cat_data as $c_data) {
			$no_catdata = false;

			foreach ($this->forum_data as $f_data) {
				if ($f_data->cat_id == $c_data->cat_id) {
					if (!$is_auth_ary[$f_data->forum_id]['auth_view']) {
						continue;
					}

					if ($f_data->post_id) {
						$f_data->topic_title = (strlen($f_data->topic_title) > 30) ? substr($f_data->topic_title, 0, 30) . '...' : $f_data->topic_title;

						$last_topic = '<a href="' . s_link('topic', $f_data->topic_id) . '">' . $f_data->topic_title . '</a>';
						$last_poster = ($f_data->user_id == GUEST) ? '<span>*' . (($f_data->post_username != '') ? $f_data->post_username : lang('guest')) . '</span>' : '<a href="' . s_link('m', $f_data->username_base) . '">' . $f_data->username . '</a>';
						$last_post_time = '<a href="' . s_link('post', $f_data->post_id) . '#' . $f_data->post_id . '">' . $user->format_date($f_data->post_time) . '</a>';
					} else {
						$last_poster = $last_post_time = $last_topic = '';
					}

					if (!$no_catdata) {
						_style('category', array(
							'DESCRIPTION' => $c_data->cat_title)
						);
						$no_catdata = true;
					}

					_style('category.forums',	array(
						'FORUM_NAME' => $f_data->forum_name,
						'FORUM_DESC' => $f_data->forum_desc,
						'POSTS' => $f_data->forum_posts,
						'TOPICS' => $f_data->forum_topics,
						'LAST_TOPIC' => $last_topic,
						'LAST_POSTER' => $last_poster,
						'LAST_POST_TIME' => $last_post_time,

						'U_FORUM' => s_link('forum', $f_data->forum_alias))
					);
				}
			}
		}
	}

	public function forum_special() {
		global $user, $auth;

		$is_auth_ary = w();
		$is_auth_ary = $user->auth->forum(AUTH_VIEW, AUTH_LIST_ALL, $this->forum_data);

		foreach ($this->cat_data as $c_data) {
			$no_catdata = false;

			foreach ($this->forum_data as $f_data) {
				if ($f_data->cat_id != $c_data->cat_id) continue;
				if ($f_data->cat_id != 9) continue;


				if (!$is_auth_ary[$f_data->forum_id]['auth_view']) {
					continue;
				}

				if ($f_data->post_id) {
					$f_data->topic_title = (strlen($f_data->topic_title) > 30) ? substr($f_data->topic_title, 0, 30) . '...' : $f_data->topic_title;

					$last_topic = '<a href="' . s_link('topic', $f_data->topic_id) . '">' . $f_data->topic_title . '</a>';
					$last_poster = ($f_data->user_id == GUEST) ? '<span>*' . (($f_data->post_username != '') ? $f_data->post_username : lang('guest')) . '</span>' : '<a href="' . s_link('m', $f_data->username_base) . '">' . $f_data->username . '</a>';
					$last_post_time = '<a href="' . s_link('post', $f_data->post_id) . '#' . $f_data->post_id . '">' . $user->format_date($f_data->post_time) . '</a>';
				} else {
					$last_poster = $last_post_time = $last_topic = '';
				}

				if (!$no_catdata) {
					_style('category', array(
						'DESCRIPTION' => $c_data->cat_title)
					);
					$no_catdata = true;
				}

				_style('category.forums',	array(
					'FORUM_NAME' => $f_data->forum_name,
					'FORUM_DESC' => $f_data->forum_desc,
					'POSTS' => $f_data->forum_posts,
					'TOPICS' => $f_data->forum_topics,
					'LAST_TOPIC' => $last_topic,
					'LAST_POSTER' => $last_poster,
					'LAST_POST_TIME' => $last_post_time,

					'U_FORUM' => s_link('forum', $f_data->forum_alias))
				);
			}
		}
	}

	public function popular() {
		global $config, $user;

		$sql = 'SELECT t.topic_id, t.topic_title, t.topic_color, t.topic_replies, p.post_id, p.post_time, u.user_id, u.username, u.username_base
			FROM _forum_posts p, _members u, _forum_topics t
			LEFT OUTER JOIN _events e ON t.topic_id = e.event_topic
			WHERE e.event_topic IS NULL
				AND p.post_id = t.topic_last_post_id
				AND p.poster_id = u.user_id
				AND p.post_deleted = 0
				AND t.topic_active = 1
				AND t.topic_featured = 1
			ORDER BY t.topic_replies DESC, t.topic_views DESC, p.post_time DESC
			LIMIT ??';

		$sql = 'SELECT t.topic_id, t.topic_title
			FROM _forum_topics t
			LEFT OUTER JOIN _events e ON t.topic_id = e.event_topic
			WHERE e.event_topic IS NULL
				AND t.topic_active = 1
				AND t.topic_featured = 1
			ORDER BY t.topic_replies DESC, t.topic_views DESC, t.topic_time DESC
			LIMIT ??';
		if ($result = sql_rowset(sql_filter($sql, 10))) {
			_style('board_popular');

			foreach ($result as $row) {
				_style('board_popular.row', array(
					'U_TOPIC' => s_link('topic', $row->topic_id),
					'TOPIC_TITLE' => $row->topic_title,
					'TOPIC_COLOR' => $row->topic_color)
				);
			}
		}

		return;
	}

	public function newest() {
		global $config, $user;

		$sql = 'SELECT t.topic_id, t.topic_title, t.topic_color
			FROM _forum_topics t
			LEFT OUTER JOIN _events e ON t.topic_id = e.event_topic
			WHERE e.event_topic IS NULL
				AND t.topic_active = 1
				AND t.topic_featured = 1
			ORDER BY t.topic_announce DESC, t.topic_time DESC
			LIMIT ??';
		if ($result = sql_rowset(sql_filter($sql, 10))) {
			_style('board_newest');

			foreach ($result as $row) {
				_style('board_newest.row', array(
					'U_TOPIC' => s_link('topic', $row->topic_id),
					'TOPIC_TITLE' => $row->topic_title,
					'TOPIC_COLOR' => $row->topic_color)
				);
			}
		}

		return;
	}
}