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

class topic {
	private $_title;
	private $_template;

	public function __construct() {
		return;
	}

	public function get_title($default = '') {
		return (!empty($this->_title)) ? $this->_title : $default;
	}

	public function get_template($default = '') {
		return (!empty($this->_template)) ? $this->_template : $default;
	}

	public function run() {
		global $config, $auth, $user, $comments;

		$topic_id = request_var('t', 0);
		$post_id = request_var('p', 0);

		if (!$topic_id && !$post_id) {
			fatal_error();
		}

		//
		// Get topic data
		//
		if ($post_id) {
			$sql_from = ', _forum_posts p, _forum_posts p2, _members m ';
			$sql_where = sql_filter('p.post_active = ? AND p.post_id = ? AND p.poster_id = m.user_id AND t.topic_id = p.topic_id AND p2.topic_id = p.topic_id AND p2.post_id <= ?', 1, $post_id, $post_id);
			$sql_count = ', p.post_text, m.username AS reply_username, COUNT(p2.post_id) AS prev_posts, p.post_deleted';
			$sql_order = ' GROUP BY p.post_id, t.topic_id, t.topic_title, t.topic_locked, t.topic_replies, t.topic_time, t.topic_important, t.topic_vote, t.topic_last_post_id, f.forum_name, f.forum_locked, f.forum_id, f.auth_view, f.auth_read, f.auth_post, f.auth_reply, f.auth_announce, f.auth_pollcreate, f.auth_vote ORDER BY p.post_id ASC';
		} else {
			$sql_from = $sql_count = $sql_order = '';
			$sql_where = sql_filter('t.topic_id = ?', $topic_id);
		}

		$sql = 'SELECT t.*, f.*' . $sql_count . '
			FROM _forum_topics t, _forums f' . $sql_from . '
			WHERE ' . $sql_where . '
				AND f.forum_id = t.forum_id
				AND f.forum_active = 1
				AND t.topic_active = 1' .
				$sql_order;
		if (!$topic_data = sql_fieldrow($sql)) {
			fatal_error();
		}

		switch ($topic_data->forum_alias) {
			case 'events':
				$sql = 'SELECT event_alias
					FROM _events
					WHERE event_topic = ?';
				if ($event_alias = sql_field(sql_filter($sql, $topic_data->topic_id), 'event_alias', '')) {
					redirect(s_link('events', $event_alias));
				}
				break;
		}

		//
		// Check mod auth
		$mod_auth = $user->is('mod');

		//
		// Init vars
		$forum_id = $topic_data->forum_id;
		$topic_id = $topic_data->topic_id;
		$topic_url = s_link('topic', $topic_id);

		$reply = request_var('reply', 0);
		$start = request_var('offset', 0);
		$submit_reply = _button('post');
		$submit_vote = _button('vote');

		$error = $is_auth = w();
		$post_message = $post_reply_message = $post_np = '';
		$current_time = time();

		if (!$post_id && $reply) {
			$reply = 0;
		}

		//
		// Start member auth
		//
		$is_auth = $user->auth->forum(AUTH_ALL, $forum_id, $topic_data);

		if ($submit_reply || $submit_vote) {
			$auth_key = ($submit_reply) ? 'auth_reply' : 'auth_vote';

			if (((!$is_auth['auth_view'] || !$is_auth['auth_read'])) || !$is_auth[$auth_key]) {
				if (!$user->is('member')) {
					do_login();
				}

				$can_reply_closed = $user->auth->option(w('forum topics delete'));

				if (!$can_reply_closed && ($topic_data->forum_locked || $topic_data->topic_locked)) {
					$error[] = 'TOPIC_LOCKED';

					if ($submit_vote && !$topic_data->topic_vote) {
						$error[] = 'POST_HAS_NO_POLL';
					}
				}

				if (!count($error)) {
					redirect($topic_url);
				}
			}

			if (!count($error)) {
				if ($submit_vote) {
					$vote_option = request_var('vote_id', 0);

					if ($vote_option) {
						$sql = 'SELECT vd.vote_id
							FROM _poll_options vd, _poll_results vr
							WHERE vd.topic_id = ?
								AND vr.vote_id = vd.vote_id
								AND vr.vote_option_id = ?
							GROUP BY vd.vote_id';
						if ($vote_id = sql_field(sql_filter($sql, $topic_id, $vote_option), 'vote_id', 0)) {
							$sql = 'SELECT *
								FROM _poll_voters
								WHERE vote_id = ?
									AND vote_user_id = ?';
							if (!sql_fieldrow(sql_filter($sql, $vote_id, $user->d('user_id')))) {
								$sql = 'UPDATE _poll_results SET vote_result = vote_result + 1
									WHERE vote_id = ?
										AND vote_option_id = ?';
								sql_query(sql_filter($sql, $vote_id, $vote_option));

								$insert_vote = array(
									'vote_id' => (int) $vote_id,
									'vote_user_id' => (int) $user->d('user_id'),
									'vote_user_ip' => $user->ip,
									'vote_cast' => (int) $vote_option
								);
								sql_insert('poll_voters', $insert_vote);
							}
						}
					}

					redirect(s_link('topic', $topic_id));
				} else {
					$post_message = request_var('message', '', true);
					$post_message = request_utf8('message', '');
					$post_np = request_var('np', '');

					if ($reply) {
						$post_reply_message = request_var('reply_message', '', true);
					}

					// Check message
					if (empty($post_message)) {
						$error[] = 'EMPTY_MESSAGE';
					}

					if (!count($error) && !$mod_auth) {
						$sql = 'SELECT MAX(post_time) AS last_post_time
							FROM _forum_posts
							WHERE poster_id = ?';
						if ($last_post_time = sql_field(sql_filter($sql, $user->d('user_id')))) {
							if (intval($last_post_time) > 0 && ($current_time - intval($last_post_time)) < intval($config->flood_interval)) {
								$error[] = 'FLOOD_ERROR';
							}
						}
					}

					// _pre($post_message);
					// _pre($error, true);

					if (!count($error)) {
						$update_topic = w();

						if (strstr($post_message, '-Anuncio-') && $user->is('mod')) {
							$topic_announce = 1;
							$post_message = str_replace('-Anuncio-', '', $post_message);
							$update_topic['topic_announce'] = $topic_announce;
						}

						if (strstr($post_message, '-Cerrado-') && $user->is('mod')) {
							$topic_locked = 1;
							$post_message = str_replace('-Cerrado-', '', $post_message);
							$update_topic['topic_locked'] = $topic_locked;
						}

						$post_message = $comments->prepare($post_message);

						// _pre($post_message, true);

						if ($reply && $post_reply_message != '') {
							$post_reply_message = preg_replace('#(^|[\n ]|\()(http|https|ftp)://([a-z0-9\-\.,\?!%\*_:;~\\&$@/=\+]+)(gif|jpg|jpeg|png)#ie', '', $post_reply_message);
						}

						if ($reply && empty($post_reply_message)) {
							$post_reply_message = '...';
						}

						if ($reply && $post_reply_message != '') {
							$post_message = '<blockquote><strong>' . $topic_data->reply_username . "</strong>" . nr(false, 2) . $post_reply_message . '</blockquote><br /> ' . $post_message;
						} else {
							$reply = 0;
						}

						$insert_data = array(
							'topic_id' => (int) $topic_id,
							'forum_id' => (int) $forum_id,
							'poster_id' => (int) $user->d('user_id'),
							'post_time' => (int) $current_time,
							'poster_ip' => $user->ip,
							'post_text' => $post_message,
							'post_np' => $post_np
						);
						if ($reply) {
							$insert_data['post_reply'] = $post_id;
						}
						// var_dump($post_id);
						// _pre($insert_data, true);

						$post_id = sql_insert('forum_posts', $insert_data);


						// TODO: Today save
						// $user->delete_unread(UH_T, $topic_id);
						// $user->save_unread(UH_T, $topic_id);

						if (!in_array($forum_id, forum_for_team_array()) && $topic_data->topic_points) {
							//$user->points_add(1);
						}

						//
						$a_list = forum_for_team_list($forum_id);
						if (count($a_list)) {
							$sql_delete_unread = 'DELETE FROM _members_unread
								WHERE element = ?
									AND item = ?
									AND user_id NOT IN (??)';
							sql_query(sql_filter($sql_delete_unread, 8, $topic_id, implode(', ', $a_list)));
						}

						$update_topic['topic_last_post_id'] = $post_id;

						if ($topic_locked) {
							topic_feature($topic_id, 0);
						}

						$sql = 'UPDATE _forums SET forum_posts = forum_posts + 1, forum_last_topic_id = ?
							WHERE forum_id = ?';
						sql_query(sql_filter($sql, $topic_id, $forum_id));

						$sql = 'UPDATE _forum_topics SET topic_replies = topic_replies + 1, ' . sql_build('UPDATE', $update_topic) . sql_filter('
							WHERE topic_id = ?', $topic_id);
						sql_query($sql);

						$sql = 'UPDATE _members SET user_posts = user_posts + 1
							WHERE user_id = ?';
						sql_query(sql_filter($sql, $user->d('user_id')));

						redirect(s_link('post', $post_id) . '#' . $post_id);
					}
				}
			}
		}

		if (!$is_auth['auth_view'] || !$is_auth['auth_read']) {
			if (!$user->is('member')) {
				do_login();
			}

			fatal_error();
		}

		if ($post_id) {
			$start = floor(($topic_data->prev_posts - 1) / (int) $config->posts_per_page) * (int) $config->posts_per_page;
			$user->d('user_topic_order', 0);
		}

		if ($user->is('member')) {
			//
			// Is user watching this topic?
			//
			$sql = 'SELECT notify_status
				FROM _forum_topics_fav
				WHERE topic_id = ?
					AND user_id = ?';
			if (!sql_field(sql_filter($sql, $topic_id, $user->d('user_id')))) {
				if (_button('watch')) {
					$sql_insert = array(
						'user_id' => $user->d('user_id'),
						'topic_id' => $topic_id,
						'notify_status' => 0
					);
					sql_insert('forum_topics_fav', $sql_insert);

					redirect($topic_url . (($start) ? 's' . $start . '/' : ''));
				}

				_style('watch_topic');
			}
		}

		//
		// Get all data for the topic
		//
		$get_post_id = ($reply) ? 'post_id' : 'topic_id';
		$get_post_data['p.' . $get_post_id] = ${$get_post_id};

		// if (!$user->is('founder')) {
			$get_post_data['p.post_active'] = 1;
		// }

		$sql = 'SELECT p.*, u.user_id, u.username, u.username_base, u.user_avatar, u.user_posts, u.user_gender, u.user_rank, u.user_sig
			FROM _forum_posts p, _members u
			WHERE u.user_id = p.poster_id
				-- AND p.post_deleted = 0
				AND ' . sql_build('SELECT', $get_post_data) . '
			ORDER BY p.post_time ' . (($user->d('user_topic_order')) ? 'DESC' : 'ASC') .
			((!$reply) ? ' LIMIT ' . (int) $start . ', ' . (int) $config->posts_per_page : '');
		// _pre($sql, true);
		if (!$messages = sql_rowset($sql)) {
			if ($topic_data->topic_replies + 1) {
				fatal_error();
			}

			redirect(s_link('topic', $topic_id));
		}

		//
		// Re-count topic replies
		//
		if ($user->is('founder')) {
			$sql = 'SELECT COUNT(p.post_id) AS total
				FROM _forum_posts p, _members u
				WHERE p.topic_id = ?
					AND u.user_id = p.poster_id';
			if ($total = sql_field(sql_filter($sql, $topic_id))) {
				$topic_data->topic_replies2 = $total - 1;
			}
		}

		//
		// Update the topic views
		//
		if (!$start && !$user->is('founder')) {
			$sql = 'UPDATE _forum_topics
				SET topic_views = topic_views + 1
				WHERE topic_id = ?';
			sql_query(sql_filter($sql, $topic_id));
		}

		//
		// If the topic contains a poll, then process it
		//
		if ($topic_data->topic_vote) {
			$sql = 'SELECT vd.vote_id, vd.vote_text, vd.vote_start, vd.vote_length, vr.vote_option_id, vr.vote_option_text, vr.vote_result
				FROM _poll_options vd, _poll_results vr
				WHERE vd.topic_id = ?
					AND vr.vote_id = vd.vote_id
				ORDER BY vr.vote_option_order, vr.vote_option_id ASC';
			if ($vote_info = sql_rowset(sql_filter($sql, $topic_id))) {
				$sql = 'SELECT vote_id
					FROM _poll_voters
					WHERE vote_id = ?
						AND vote_user_id = ?';
				$user_voted = sql_field(sql_filter($sql, $vote_info[0]->vote_id, $user->d('user_id')), 'vote_id', 0);

				$poll_expired = ($vote_info[0]->vote_length) ? (($vote_info[0]->vote_start + $vote_info[0]->vote_length < $current_time) ? true : 0) : 0;

				_style('poll', array(
					'POLL_TITLE' => $vote_info[0]->vote_text)
				);

				if ($user_voted || $poll_expired || !$is_auth['auth_vote'] || $topic_data->topic_locked) {
					$vote_results_sum = 0;
					foreach ($vote_info as $row) {
						$vote_results_sum += $row->vote_result;
					}

					_style('poll.results');

					foreach ($vote_info as $row) {
						$vote_percent = ($vote_results_sum > 0) ? $row->vote_result / $vote_results_sum : 0;

						_style('poll.results.item', array(
							'CAPTION' => $row->vote_option_text,
							'RESULT' => $row->vote_result,
							'PERCENT' => sprintf("%.1d", ($vote_percent * 100)))
						);
					}
				} else {
					_style('poll.options', array(
						'S_VOTE_ACTION' => $topic_url)
					);

					foreach ($vote_info as $row) {
						_style('poll.options.item', array(
							'POLL_OPTION_ID' => $row->vote_option_id,
							'POLL_OPTION_CAPTION' => $row->vote_option_text)
						);
					}
				}
			}
		}

		//
		// Advanced auth
		//

		$controls = w();
		$unset_user_profile = w('user_id user_posts user_gender');

		_style('posts');

		foreach ($messages as $row) {
			if ($user->is('member')) {
				$poster = ($row->user_id != GUEST) ? $row->username : (($row->post_username != '') ? $row->post_username : lang('guest'));

				$controls[$row->post_id]['reply'] = s_link('post', $row->post_id, 'reply');

				if ($mod_auth) {
					$controls[$row->post_id]['edit'] = s_link('acp', array('forums_post_modify', 'msg_id' => $row->post_id));
					$controls[$row->post_id]['delete'] = s_link('acp', array('forums_post_delete', 'msg_id' => $row->post_id));
				}
			}

			_style('posts.item', object_merge($comments->user_profile($row, $unset_user_profile), array(
				'post_id' => $row->post_id,
				'post_date' => $user->format_date($row->post_time),
				'message' => $comments->parse_message($row->post_text),
				'playing' => $row->post_np,
				'deleted' => $row->post_deleted,
				'unread' => 0)
			));

			_style('posts.item.' . (($row->user_id != GUEST) ? 'username' : 'guestuser'));

			if (isset($controls[$row->post_id])) {
				_style('posts.item.controls');

				foreach ($controls[$row->post_id] as $item => $url) {
					_style('posts.item.controls.'.$item, array('URL' => $url));
				}
			}
		}

		//
		// Display Member topic auth
		//
		/*
		if ($mod_auth) {
			$mod = array((($topic_data->topic_important) ? 'important' : 'normal'), 'delete', 'move', ((!$topic_data->topic_locked) ? 'lock' : 'unlock'), 'split', 'merge');

			$mod_topic = w();
			foreach ($mod as $item) {
				if ($user->auth->option(array('forum', 'topics', $item))) {
					$mod_topic[$item] = s_link('acp', array('topic', topic' => $topic_id, 'mode' => $item));
				}
			}

			if (count($mod_topic)) {
				_style('auth');

				foreach ($mod_topic as $k => $v) {
					_style('auth.item', array(
						'URL' => $v,
						'LANG' => lang($k . '_topic'))
					);
				}
			}
		}
		*/
		build_num_pagination($topic_url . 's%d/', ($topic_data->topic_replies + 1), $config->posts_per_page, $start, '', 'TOPIC_');

		//
		// Posting box
		if (count($error)) {
			_style('post_error', array(
				'MESSAGE' => parse_error($error))
			);
		}

		$can_reply_closed = $user->auth->option(array('forum', 'topics', 'delete'));

		if ((!$topic_data->forum_locked && !$topic_data->topic_locked) || $can_reply_closed) {
			if ($user->is('member')) {
				if ($is_auth['auth_reply']) {
					$s_post_action = (($reply) ? s_link('post', $post_id, 'reply') : $topic_url) . '#e';

					_style('post_box', array(
						'MESSAGE' => $post_message,
						'NP' => $post_np,
						'S_POST_ACTION' => $s_post_action)
					);

					if ($reply) {
						if (empty($post_reply_message)) {
							$post_reply_message = $comments->remove_quotes($topic_data->post_text);
						}

						if (!empty($post_reply_message)) {
							$rx = array('#(^|[\n ]|\()(http|https|ftp)://([a-z0-9\-\.,\?!%\*_:;~\\&$@/=\+]+)(gif|jpg|jpeg|png)#is', '#\[yt:[0-9a-zA-Z\-\=\_]+\]#is', '#\[sb\]#is', '#\[\/sb\]#is');
							$post_reply_message = preg_replace($rx, '', $post_reply_message);
						}

						if (empty($post_reply_message)) {
							$post_reply_message = '...';
						}

						_style('post_box.reply', array(
							'MESSAGE' => $post_reply_message)
						);
					}
				}
			}
		}

		// MOD: Featured topic
		if ($user->is('mod')) {
			$v_lang = ($topic_data->topic_featured) ? 'REM' : 'ADD';

			_style('feature', array(
				'U_FEAT' => s_link('acp', array('forums_topic_feature', 'msg_id', $topic_data->topic_id)),
				'V_LANG' => lang('topic_featured_' . $v_lang))
			);
		}

		//
		// Send vars to template
		//
		v_style(array(
			'FORUM_NAME' => $topic_data->forum_name,
			'TOPIC_TITLE' => $topic_data->topic_title,
			'TOPIC_REPLIES' => $topic_data->topic_replies,

			'S_TOPIC_ACTION' => $topic_url . (($start) ? 's' . $start . '/' : ''),
			'U_VIEW_FORUM' => s_link('forum', $topic_data->forum_alias))
		);

		$layout_file = 'topic';
		if (@file_exists('./template/custom/topics_' . $forum_id . '.htm')) {
			$layout_file = 'custom/topics_' . $forum_id;
		}

		if (@file_exists('./template/custom/topic_' . $topic_id . '.htm')) {
			$layout_file = 'custom/topic_' . $topic_id;
		}

		$this->_title = $topic_data->topic_title;
		$this->_template = $layout_file;

		return;
	}
}