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

// require_once(ROOT . 'interfase/downloads.php');

class events {
	public $data = array();
	public $images = array();
	public $timetoday = 0;

	private $_template;
	private $_title;

	public function __construct() {
		global $user;

		$current_time = time();
		$minutes = date('is', $current_time);
		$this->timetoday = (int) ($current_time - (60 * intval($minutes[0].$minutes[1])) - intval($minutes[2].$minutes[3])) - (3600 * $user->format_date($current_time, 'H'));

		return;
	}

	public function get_title($default = '') {
		return (!empty($this->_title)) ? $this->_title : $default;
	}

	public function get_template($default = '') {
		return (!empty($this->_template)) ? $this->_template : $default;
	}

	public function v($property) {
		if (!isset($this->data->$property)) {
			return false;
		}

		return $this->data->$property;
	}

	public function run() {
		$event_alias = request_var('alias', '');

		if (empty($event_alias)) {
			return $this->all();
		}

		if (!preg_match('#[a-z0-9\_\-]+#i', $event_alias)) {
			fatal_error();
		}

		$event_field = (!is_numb($event_alias)) ? 'event_alias' : 'id';

		$sql = 'SELECT *
			FROM _events
			WHERE ?? = ?';
		if (!$this->data = sql_fieldrow(sql_filter($sql, $event_field, $event_alias))) {
			fatal_error();
		}

		return $this->object();
	}

	public function future() {
		global $config, $user;

		$sql = 'SELECT *
			FROM _events
			WHERE date >= ?
			ORDER BY RAND()';
		$result = sql_rowset(sql_filter($sql, $this->timetoday));

		foreach ($result as $i => $row) {
			if (!$i) _style('events_next');

			$event_mini = $config->events_path . 'mini/' . $row->id . '.jpg';
			$event_image = (@file_exists($event_mini)) ? 'mini/' : 'future/';

			_style('events_next.row', array(
				'URL' => s_link('events', $row->event_alias),
				'TITLE' => $row->title,
				'DATE' => $user->format_date($row->date, lang('date_format')),
				'IMAGE' => $config->events_url . $event_image . $row->id . '.jpg?u=' . $row->event_update)
			);
		}

		return;
	}

	public function past($start = 0) {
		global $config;

		$sql = 'SELECT *
			FROM _events
			WHERE (date < ? OR date > ?)
				AND images > 0
			ORDER BY date DESC
			LIMIT ??, ??';
		$result = sql_fieldrow(sql_filter($sql, $this->timetoday, $this->timetoday, $start, 3));

		foreach ($result as $i => $row) {
			if (!$i) _style('events_past');

			$sql = 'SELECT *
				FROM _events_images
				WHERE event_id = ?
				ORDER BY RAND()';
			$row2 = sql_fieldrow(sql_filter($sql, $row->id));

			_style('events_past.row', array(
				'URL' => s_link('events', $row->event_alias),
				'TITLE' => $row->title,
				'IMAGE' => $config->events_url . 'gallery/' . $row->id . '/thumbnails/' . $row2->image . '.jpg?u=' . $row->event_update)
			);
		}

		return true;
	}

	public function object() {
		global $auth, $user, $config, $comments, $upload;

		$mode = request_var('mode', '');

		switch ($mode) {
			case 'view':
			case 'fav':
			case 'rsvp':
				$download_id = request_var('download_id', 0);
				if (!$download_id) {
					redirect(s_link('events', $this->v('event_alias')));
				}

				switch ($mode) {
					case 'view':
						$sql = 'SELECT e.*, COUNT(e2.image) AS prev_images
							FROM _events_images e, _events_images e2
							WHERE e.event_id = ?
								AND e.event_id = e2.event_id
								AND e.image = ?
								AND e2.image <= ?
							GROUP BY e.image
							ORDER BY e.image ASC';
						$sql = sql_filter($sql, $this->v('id'), $download_id, $download_id);
						break;
					case 'rsvp':
						$sql = '';
						break;
					default:
						$sql = 'SELECT e2.*
							FROM _events_images e2
							LEFT JOIN _events e ON e.id = e2.event_id
							WHERE e2.event_id = ?
								AND e2.image = ?';
						$sql = sql_filter($sql, $this->v('id'), $download_id);
						break;
				}

				if (!empty($sql)) {
					if (!$imagedata = sql_fieldrow($sql)) {
						redirect(s_link('events', $this->v('event_alias')));
					}
				}

				break;
		}

		switch ($mode) {
			case 'fav':
				if (!$user->is('member')) {
					do_login();
				}

				$sql = 'SELECT *
					FROM _events_fav
					WHERE event_id = ?
						AND image_id = ?
						AND member_id = ?';
				if ($row = sql_fieldrow(sql_filter($sql, $this->v('id'), $imagedata->image, $user->d('user_id')))) {
					$sql = 'UPDATE _events_fav SET fav_date = ?
						WHERE event_id = ?
							AND image_id = ?';
					sql_query(sql_filter($sql, time(), $this->v('id'), $imagedata->image));
				} else {
					$sql_insert = array(
						'event_id' => (int) $this->v('id'),
						'image_id' => (int) $imagedata->image,
						'member_id' => (int) $user->d('user_id'),
						'fav_date' => time()
					);
					sql_insert('events_fav', $sql_insert);
				}

				redirect(s_link('events', $this->v('event_alias'), $imagedata->image, 'view'));
				break;
			case 'rsvp':
				$choice = array_key(array_keys(request_var('choice', array(0 => ''))), 0);
				$topic_id = $this->v('event_topic');

				if (!$topic_id) {
					fatal_error();
				}

				if (!$choice) {
					redirect(s_link('events', $this->v('event_alias')));
				}

				if (!$user->is('member')) {
					do_login();
				}

				$sql = 'SELECT vd.vote_id
					FROM _poll_options vd, _poll_results vr
					WHERE vd.topic_id = ?
						AND vr.vote_id = vd.vote_id
						AND vr.vote_option_id = ?
					GROUP BY vd.vote_id';
				if (!$vote_id = sql_field(sql_filter($sql, $topic_id, $choice), 'vote_id', 0)) {
					fatal_error();
				}

				$sql = 'SELECT *
					FROM _poll_voters
					WHERE vote_id = ?
						AND vote_user_id = ?';
				if (!sql_fieldrow(sql_filter($sql, $vote_id, $user->d('user_id')))) {
					$sql = 'UPDATE _poll_results SET vote_result = vote_result + 1
						WHERE vote_id = ?
							AND vote_option_id = ?';
					sql_query(sql_filter($sql, $vote_id, $choice));

					$insert_vote = array(
						'vote_id' => (int) $vote_id,
						'vote_user_id' => (int) $user->d('user_id'),
						'vote_user_ip' => $user->ip,
						'vote_cast' => (int) $choice
					);
					sql_insert('poll_voters', $insert_vote);
				}

				redirect(s_link('events', $this->v('event_alias')));
				break;
			case 'view':
			default:
				/**
				* Generate thumbnail for events list.
				*/
				if ($user->is('colab') && !$this->v('images') && _button('create_thumbnail')) {
					$location_large = $config->events_path . 'future/' . $this->v('id') . '.jpg';
					$location_mini = $config->events_path . 'mini/' . $this->v('id') . '.jpg';

					$x1 = request_var('x1', 0);
					$y1 = request_var('y1', 0);
					$x2 = request_var('x2', 0);
					$y2 = request_var('y2', 0);
					$w = request_var('w', 0);
					$h = request_var('h', 0);

					//Scale the image to the thumb_width set above
					$scale = $config->events_thumb_width / $w;
					$cropped = $upload->resizeThumbnailImage($location_mini, $location_large, $w, $h, $x1, $y1, $scale);

					redirect(s_link('events'));
				}

				/**
				* Normal operations
				*/
				$t_offset = request_var('offset', 0);

				if ($mode == 'view') {
					$sql = 'UPDATE _events_images
						SET views = views + 1
						WHERE event_id = ?
							AND image = ?';
					sql_query(sql_filter($sql, $this->v('id'), $imagedata->image));

					_style('selected', array(
						'IMAGE' => $config->events_url . 'gallery/' . $this->v('id') . '/' . $imagedata->image . '.jpg',
						'WIDTH' => $imagedata->width,
						'HEIGHT' => $imagedata->height,
						'FOOTER' => $imagedata->image_footer)
					);

					if ($user->is('founder')) {
						_style('selected.update', array(
							'URL' => s_link('async eif'),
							'EID' => $this->v('id'),
							'PID' => $imagedata->image)
						);
					}

					$is_fav = false;
					if ($user->is('member')) {
						$sql = 'SELECT member_id
							FROM _events_fav
							WHERE event_id = ?
								AND image_id = ?
								AND member_id = ?';
						if (sql_field(sql_filter($sql, $this->v('id'), $imagedata->image, $user->d('user_id')), 'member_id', 0)) {
							$is_fav = true;
						}
					}

					if (!$is_fav || !$user->is('member')) {
						_style('selected.fav', array(
							'URL' => s_link('events', $this->v('id'), $imagedata->image, 'fav'))
						);
					}
				} else {
					if (!$t_offset && $user->is('founder')) {
						$sql = 'UPDATE _events SET views = views + 1
							WHERE id = ?';
						sql_query(sql_filter($sql, $this->v('id')));
					}
				}

				$error = w();
				$submit_reply = _button('post');
				$reply = request_var('reply', 0);
				$u_event_alias = s_link('events', $this->v('event_alias'));

				// If topic does not exists.
				if (!$this->v('event_topic')) {
					$forum_id = $config->forum_for_events;

					$insert_data['TOPIC'] = array(
						'topic_ub' => 0,
						'topic_active' => 1,
						'topic_title' => $this->v('title'),
						'topic_poster' => (int) $user->d('user_id'),
						'topic_time' => time(),
						'forum_id' => (int) $forum_id,
						'topic_views' => 0,
						'topic_replies' => 0,
						'topic_locked' => 0,
						'topic_announce' => 0,
						'topic_important' => 0,
						'topic_vote' => 0,
						'topic_featured' => 1,
						'topic_points' => 1,
						'topic_color' => '',
						'topic_first_post_id' => 0,
						'topic_last_post_id' => 0
					);
					$this->data->event_topic = sql_insert('forum_topics', $insert_data['TOPIC']);
					
					$sql = 'UPDATE _forums SET forum_topics = forum_topics + 1
						WHERE forum_id = ?';
					sql_query(sql_filter($sql, $forum_id));

					$sql = 'UPDATE _events SET event_topic = ?
						WHERE id = ?';
					sql_query(sql_filter($sql, $this->v('event_topic'), $this->v('id')));
				}

				$sql = 'SELECT t.topic_id, t.topic_title, t.topic_locked, t.topic_replies, t.topic_time, t.topic_important, t.topic_vote, t.topic_featured, t.topic_points, t.topic_last_post_id, f.forum_alias, f.forum_name, f.forum_locked, f.forum_id, f.auth_view, f.auth_read, f.auth_post, f.auth_reply, f.auth_announce, f.auth_pollcreate, f.auth_vote' . $sql_count . '
					FROM _forum_topics t, _forums f
					WHERE t.topic_id = ?
						AND f.forum_id = t.forum_id';
				if ($event_topic = sql_fieldrow(sql_filter($sql, $this->v('event_topic')))) {
					$mod_auth = $user->is('mod');
					$forum_id = $event_topic->forum_id;

					if ($reply) {
						$post_reply = request_var('p', 0);

						$sql = 'SELECT p.*, u.user_id, u.username
							FROM _forum_posts p, _members u
							WHERE p.post_id = ?
								AND p.post_active = 1
								AND u.user_id = p.poster_id';
						if (!$post_data = sql_fieldrow(sql_filter($sql, $post_reply))) {
							redirect(s_link('events', $this->v('event_alias')));
						}
					}

					$is_auth = $user->auth->forum(AUTH_ALL, $forum_id, $event_topic);

					if ($submit_reply) {
						$auth_key = 'auth_reply';

						if (((!$is_auth['auth_view'] || !$is_auth['auth_read'])) || !$is_auth[$auth_key]) {
							if (!$user->is('member')) {
								do_login();
							}

							$can_reply_closed = $user->auth->option(array('forum', 'topics', 'delete'));

							if (!$can_reply_closed && ($event_topic->forum_locked || $event_topic->topic_locked)) {
								$error[] = 'TOPIC_LOCKED';
							}

							if (count($error)) {
								redirect($u_event_alias);
							}
						}

						$post_message = request_var('message', '', true);

						if ($reply) {
							$post_reply_message = request_var('reply_message', '', true);
						}

						// Check message
						if (empty($post_message)) {
							$error[] = 'EMPTY_MESSAGE';
						}

						if (count($error)) {
							redirect($u_event_alias);
						}

						if (!$mod_auth) {
							$sql = 'SELECT MAX(post_time) AS last_post_time
								FROM _forum_posts
								WHERE poster_id = ?';
							if ($last_post_time = sql_field(sql_filter($sql, $user->d('user_id')))) {
								if (intval($last_post_time) > 0 && ($current_time - intval($last_post_time)) < intval($config->flood_interval)) {
									$error[] = 'FLOOD_ERROR';
								}
							}
						}

						if (count($error)) {
							redirect($u_event_alias);
						}

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

						if ($reply && $post_reply_message != '') {
							$post_reply_message = preg_replace('#(^|[\n ]|\()(http|https|ftp)://([a-z0-9\-\.,\?!%\*_:;~\\&$@/=\+]+)(gif|jpg|jpeg|png)#ie', '', $post_reply_message);
						}

						if ($reply && empty($post_reply_message)) {
							$post_reply_message = '...';
						}

						if ($reply && $post_reply_message != '') {
							$post_message = '<blockquote><strong>' . $post_data->username . "</strong>" . nr(false, 2) . $post_reply_message . '</blockquote> ' . $post_message;
						} else {
							$reply = 0;
						}

						$insert_data = array(
							'topic_id' => (int) $this->v('event_topic'),
							'forum_id' => (int) $forum_id,
							'poster_id' => (int) $user->d('user_id'),
							'post_time' => time(),
							'poster_ip' => $user->ip,
							'post_text' => $post_message,
							'post_np' => ''
						);
						if ($reply) {
							$insert_data['post_reply'] = $post_reply;
						}

						$post_id = sql_insert('forum_posts', $insert_data);

						// TODO: Today save
						// $user->delete_unread(UH_T, $this->v('event_topic'));
						// $user->save_unread(UH_T, $this->v('event_topic'));

						//
						$a_list = forum_for_team_list($forum_id);
						if (count($a_list)) {
							$sql_delete_unread = 'DELETE FROM _members_unread
								WHERE element = ?
									AND item = ?
									AND user_id NOT IN (??)';
							sql_query(sql_filter($sql, 8, $this->v('event_topic'), implode(', ', $a_list)));
						}

						$update_topic['topic_last_post_id'] = $post_id;

						if ($topic_locked) {
							topic_feature($topic_id, 0);
						}

						$sql = 'UPDATE _forums SET forum_posts = forum_posts + 1, forum_last_topic_id = ?
							WHERE forum_id = ?';
						sql_query(sql_filter($sql, $this->v('event_topic'), $forum_id));

						$sql = 'UPDATE _forum_topics SET topic_replies = topic_replies + 1, ' . sql_build('UPDATE', $update_topic) . sql_filter('
							WHERE topic_id = ?', $this->v('event_topic'));
						sql_query($sql);

						$sql = 'UPDATE _members SET user_posts = user_posts + 1
							WHERE user_id = ?';
						sql_query(sql_filter($sql, $user->d('user_id')));

						redirect($u_event_alias);
					}
				}

				$u_event_publish = ($reply) ? s_link('events', $this->v('event_alias'), $post_reply, 'reply') : $u_event_alias;

				// Get event thumbnails
				$t_per_page = 15;

				if ($mode == 'view' && $download_id) {
					$val = 1;

					$sql = 'SELECT MAX(image) AS total
						FROM _events_images
						WHERE event_id = ?';
					if ($maximage = sql_field(sql_filter($sql, $this->v('id')), 'total', 0)) {
						$val = ($download_id == $maximage) ? 2 : 1;
					}

					$t_offset = floor(($imagedata->prev_images - $val) / $t_per_page) * $t_per_page;
				}

				if ($this->v('images')) {
					$exception_sql = (isset($download_id) && $download_id) ? sql_filter(' AND g.image <> ? ', $download_id) : '';

					$sql = 'SELECT g.*
						FROM _events e, _events_images g
						WHERE e.id = ?
							AND e.id = g.event_id ' .
							$exception_sql . '
						ORDER BY g.image ASC
						LIMIT ??, ??';
					if (!$result = sql_rowset(sql_filter($sql, $this->v('id'), $t_offset, $t_per_page))) {
						redirect(s_link('events', $this->v('id')));
					}

					build_num_pagination(s_link('events', $this->v('id'), 's%d'), $this->v('images'), $t_per_page, $t_offset, 'IMG_');

					_style('thumbnails');

					foreach ($result as $row) {
						_style('thumbnails.item', array(
							'URL' => s_link('events', $this->v('event_alias'), $row->image, 'view'),
							'IMAGE' => $config->events_url . 'gallery/' . $this->v('id') . '/thumbnails/' . $row->image . '.jpg',
							'RIMAGE' => $config->events_url . 'gallery/' . $this->v('id') . '/' . $row->image . '.jpg',
							'FOOTER' => $row->image_footer,
							'WIDTH' => $row->width,
							'HEIGHT' => $row->height)
						);
					}

					// Credits
					$sql = 'SELECT *
						FROM _events_colab c, _members m
						WHERE c.colab_event = ?
							AND c.colab_uid = m.user_id
						ORDER BY m.username';
					if ($result = sql_rowset(sql_filter($sql, $this->v('id')))) {
						_style('collab');

						foreach ($result as $row) {
							_style('collab.row', array(
								'PROFILE' => s_link('m', $row->username_base),
								'USERNAME' => $row->username)
							);
						}
					}
				} else {
					_style('event_flyer', array(
						'IMAGE_SRC' => $config->events_url . 'future/' . $this->v('id') . '.jpg?u=' . $this->v('event_update'))
					);

					$location_mini = $config->events_path . 'mini/' . $this->v('id') . '.jpg';

					if ($user->is('colab') && !$this->v('images') && !@file_exists($location_mini)) {
						$large_filepath = $config->events_path . 'future/' . $this->v('id') . '.jpg';

						_style('event_flyer.thumbnail', array(
							'ACTION' => $u_event_alias,
							'SCALE' => ($config->events_thumb_height / $config->events_thumb_width),
							'THUMB_WIDTH' => $config->events_thumb_width,
							'THUMB_HEIGHT' => $config->events_thumb_height,
							'LARGE_WIDTH' => $upload->getWidth($large_filepath),
							'LARGE_HEIGHT' => $upload->getHeight($large_filepath)
						));
					}
				}

				list($d, $m, $y) = explode(' ', gmdate('j n Y', time() + $user->timezone + $user->dst));
				$midnight = gmmktime(0, 0, 0, $m, $d, $y) - $user->timezone - $user->dst;

				$event_date = $user->format_date($this->v('date'), 'j F Y \a \l\a\s H:i') . ' horas.';

				if ($this->v('date') >= $midnight) {
					if ($this->v('date') >= $midnight && $this->v('date') < $midnight + 86400) {
						$event_date_format = lang('event_today');
					} else if ($this->v('date') >= $midnight + 86400 && $this->v('date') < $midnight + (86400 * 2)) {
						$event_date_format = lang('event_tomorrow');
					} else {
						$event_date_format = sprintf(lang('event_after'), $event_date);
					}
				} else {
					if ($this->v('date') >= ($midnight - 86400)) {
						$event_date_format = lang('event_yesterday');
					} else {
						$event_date_format = sprintf(lang('event_before'), $event_date);
					}
				}

				v_style(array(
					'EVENT_NAME' => $this->v('title'),
					'EVENT_DATE' => $event_date_format,
					'EVENT_URL' => $u_event_alias,
					'EVENT_PUBLISH' => $u_event_publish)
				);

				$posts_offset = request_var('ps', 0);
				$topic_id = $this->v('event_topic');

				// START RSVP
				if ($topic_id) {
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
						$poll_expired = ($vote_info[0]->vote_length) ? (($vote_info[0]->vote_start + $vote_info[0]->vote_length < time()) ? true : false) : false;

						_style('poll', array(
							'POLL_TITLE' => $vote_info[0]->vote_text)
						);

						if ($user_voted || $poll_expired) {
							_style('poll.results');

							foreach ($vote_info as $row) {
								if ($this->v('date') >= $midnight) {
									$caption = ($row->vote_result == 1) ? lang('rsvp_future_one') : lang('rsvp_future_more');
								} else {
									$caption = ($row->vote_result == 1) ? lang('rsvp_past_one') : lang('rsvp_past_more');
								}

								_style('poll.results.item', array(
									'CAPTION' => $caption,
									'RESULT' => $row->vote_result)
								);
								break;
							}
						} else {
							_style('poll.options', array(
								'S_VOTE_ACTION' => s_link('events', $this->v('event_alias'), 1, 'rsvp'))
							);

							foreach ($vote_info as $row) {
								$caption = ($this->v('date') >= $midnight) ? lang('rsvp_future') : lang('rsvp_past');

								_style('poll.options.item', array(
									'ID' => $row->vote_option_id,
									'CAPTION' => $caption)
								);
								break;
							}
						}
					}
				}
				// END RSVP

				$sql = 'SELECT p.*, u.user_id, u.username, u.username_base, u.user_avatar, u.user_posts, u.user_gender, u.user_rank
					FROM _forum_posts p, _members u
					WHERE p.topic_id = ?
						AND p.post_active = 1
						AND u.user_id = p.poster_id
					ORDER BY p.post_time DESC
					LIMIT ??, ??';
				if (!$messages = sql_rowset(sql_filter($sql, $topic_id, $posts_offset, $config->posts_per_page))) {
					//redirect(s_link('events', $this->v('event_alias')));
				}

				if (!$posts_offset) {
					//unset($messages[0]);
				}

				// _pre($messages, true);

				$i = 0;
				foreach ($messages as $row) {
					if (!$i) {
						$controls = w();
						$unset_user_profile = w('user_id user_posts user_gender');

						_style('messages');
					}

					if ($user->is('member')) {
						$controls[$row->post_id]['reply'] = s_link('events', $this->v('event_alias'), $row->post_id, 'reply');

						if ($mod_auth) {
							$controls[$row->post_id]['edit'] = s_link('acp', array('forums_post_modify', 'msg_id' => $row->post_id));
							$controls[$row->post_id]['delete'] = s_link('acp', array('forums_post_delete', 'msg_id' => $row->post_id));
						}
					}

					_style('messages.row', object_merge($comments->user_profile($row, $unset_user_profile), array(
						'post_id' => $row->post_id,
						'datetime' => $user->format_date($row->post_time),
						'message' => $comments->parse_message($row->post_text),
						'playing' => $row->post_np,
						'deleted' => $row->post_deleted)
					));

					if (isset($controls[$row->post_id])) {
						_style('messages.row.controls');

						foreach ($controls[$row->post_id] as $item => $url) {
							_style('messages.row.controls.' . $item, array(
								'URL' => $url)
							);
						}
					}

					$i++;
				}

				build_num_pagination(s_link('events', $this->v('event_alias'), 'ps%d'), $event_topic->topic_replies, $config->posts_per_page, $posts_offset, 'MSG_');

				$publish_ref = ($posts_offset) ? s_link('events', $this->v('event_alias'), 's' . $t_offset) : s_link('events', $this->v('event_alias'));

				// Posting box
				if ($user->is('member')) {
					_style('publish', array(
						'REF' => $publish_ref)
					);

					if ($reply) {
						if (empty($post_reply_message)) {
							$post_reply_message = $comments->remove_quotes($post_data->post_text);
						}

						if (!empty($post_reply_message)) {
							$rx = array('#(^|[\n ]|\()(http|https|ftp)://([a-z0-9\-\.,\?!%\*_:;~\\&$@/=\+]+)(gif|jpg|jpeg|png)#is', '#\[yt:[0-9a-zA-Z\-\=\_]+\]#is', '#\[sb\]#is', '#\[\/sb\]#is');
							$post_reply_message = preg_replace($rx, '', $post_reply_message);
						}

						if (empty($post_reply_message)) {
							$post_reply_message = '...';
						}

						_style('publish.reply', array(
							'MESSAGE' => $post_reply_message)
						);
					}
				}

				break;
		}

		$this->_title = $this->v('title');
		$this->_template = 'events.view';

		return true;
	}

	public function all() {
		global $config, $user;

		$timezone = $config->board_timezone * 3600;

		list($d, $m, $y) = explode(' ', gmdate('j n Y', time() + $user->timezone + $user->dst));
		$midnight = gmmktime(0, 0, 0, $m, $d, $y) - $user->timezone - $user->dst;

		$g = getdate($midnight);
		$week = mktime(0, 0, 0, $m, ($d + (7 - ($g['wday'] - 1)) - (!$g['wday'] ? 7 : 0)), $y) - $timezone;

		$per_page = 6;

		$sql = 'SELECT *
			FROM _events
			ORDER BY date ASC';
		$result = sql_rowset($sql);

		foreach ($result as $row) {
			if ($row->date >= $midnight && !$row->images) {
				if ($row->date >= $midnight && $row->date < $midnight + 86400) {
					$this->data['is_today'][] = $row;
				} else if ($row->date >= $midnight + 86400 && $row->date < $midnight + (86400 * 2)) {
					$this->data['is_tomorrow'][] = $row;
				} else if ($row->date >= $midnight + (86400 * 2) && $row->date < $week) {
					$this->data['is_week'][] = $row;
				} else {
					$this->data['is_future'][] = $row;
				}
			} else {
				if ($row->images) {
					$this->data['is_gallery'][] = $row;
				}
			}
		}

		if ($total_gallery = count($this->data['is_gallery'])) {
			$gallery_offset = request_var('gallery_offset', 0);

			$gallery = $this->data['is_gallery'];

			// _pre($gallery_offset);
			// _pre($per_page);
			// _pre($gallery, true);

			@krsort($gallery);

			$gallery = array_slice($gallery, $gallery_offset, $per_page);

			// _pre($gallery, true);

			$event_ids = w();
			foreach ($gallery as $item) {
				$event_ids[] = $item->id;
			}

			$sql = 'SELECT *
				FROM _events_images
				WHERE event_id IN (??)
				ORDER BY RAND()';
			$result = sql_rowset(sql_filter($sql, implode(',', $event_ids)));

			$random_images = w();
			foreach ($result as $row) {
				$random_images[$row->event_id] = $row->image;
			}

			_style('gallery', array(
				'EVENTS' => $total_gallery)
			);

			foreach ($gallery as $item) {
				_style('gallery.item', array(
					'URL' => s_link('events', $item->event_alias),
					'TITLE' => $item->title,
					'IMAGE' => $config->events_url . 'gallery/' . $item->id . '/thumbnails/' . $random_images[$item->id] . '.jpg',
					'DATETIME' => $user->format_date($item->date, lang('date_format')))
				);
			}

			build_num_pagination(s_link('events', 'g%d'), $total_gallery, $per_page, $gallery_offset);

			unset($this->data['is_gallery']);
		}

		if (!count($this->data)) {
			return;
		}

		_style('future');

		foreach ($this->data as $is_date => $data) {
			_style('future.set', array(
				'L_TITLE' => lang('ue_' . $is_date))
			);

			foreach ($data as $item) {
				$event_mini = $config->events_path . 'mini/' . $item->id . '.jpg';

				if (@file_exists($event_mini)) {
					$event_image = $config->events_url . 'mini/';
				} else {
					$event_image = $config->events_url . 'future/';
				}

				_style('future.set.item', array(
					'ITEM_ID' => $item->id,
					'TITLE' => $item->title,
					'DATE' => $user->format_date($item->date, lang('date_format')),
					'THUMBNAIL' => $event_image . $item->id . '.jpg',
					'SRC' => $event_image . $item->id . '.jpg?u=' . $item->event_update,
					'U_TOPIC' => s_link('events', $item->event_alias))
				);
			}
		}

		return;
	}
}