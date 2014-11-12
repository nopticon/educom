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

class __event extends mac {
	public function __construct() {
		parent::__construct();

		$this->auth('colab');
	}

	public function _home() {
		global $config, $user, $cache, $upload;

		$error = w();

		if (request_method() == 'post') {
			$options = array(
				'param_name' => 'event_image',

				'upload_dir' => $config->events_path . 'prev/',
				'upload_url' => $config->events_url . 'prev/',

				'image_versions' => array(
	                'mini' => array(
	                	'upload_dir' => $config->events_path . 'mini2/',
		                'upload_url' => $config->events_url . 'mini2/',
		                'max_width' => 200,
		                'max_height' => 200,
		                'crop' => true
	                )
	            )
			);

			$upload_handler = new UploadHandler($options, false);

			$post_response = $upload_handler->post();

			_pre($options);
			_pre($post_response);
			_pre($_REQUEST, true);

			$filepath = $config->events_path;
			$filepath_1 = $filepath . 'future/';
			$filepath_2 = $filepath_1 . 'thumbnails/';

			$f = $upload->process($filepath_1, 'event_image', 'jpg');

			if (!count($upload->error) && $f !== false) {
				$img = sql_total('_events');

				// Create vars
				$event_name = request_var('event_name', '');
				$event_artists = request_var('event_artists', array(0 => ''));
				$event_year = request_var('event_year', 0);
				$event_month = request_var('event_month', 0);
				$event_day = request_var('event_day', 0);
				$event_hours = request_var('event_hours', 0);
				$event_minutes = request_var('event_minutes', 0);
				$event_current_topic = request_var('event_current_topic', 0);

				$v_date = gmmktime($event_hours, $event_minutes, 0, $event_month, $event_day, $event_year) - $user->timezone - $user->dst;

				foreach ($f as $row) {
					$xa = $upload->resize($row, $filepath_1, $filepath_1, $img, array(600, 400), false, false, true);
					if ($xa === false) {
						continue;
					}

					$xb = $upload->resize($row, $filepath_1, $filepath_2, $img, array(100, 75), false, false);

					$event_alias = friendly($event_name);

					$insert = array(
						'event_alias' => $event_alias,
						'title' => $event_name,
						'archive' => '',
						'date' => (int) $v_date,
						'event_update' => time()
					);
					$event_id = sql_insert('events', $insert);

					//
					foreach ($event_artists as $subdomain) {
						$sql = 'SELECT *
							FROM _artists
							WHERE subdomain = ?';
						if ($a_row = sql_fieldrow(sql_filter($sql, $subdomain))) {
							$sql = 'SELECT *
								FROM _artists_events
								WHERE a_artist = ?
									AND a_event = ?';
							if (!sql_fieldrow(sql_filter($sql, $a_row->ub, $event_id))) {
								$sql_insert = array(
									'a_artist' => $a_row->ub,
									'a_event' => $event_id
								);
								sql_insert('artists_events', $sql_insert);
							}
						}
					}

					// Alice: Create topic
					$event_url = $config->events_url . 'future/' . $img  . '.jpg';

					$post_message = 'Evento publicado';
					$post_time = time();
					$forum_id = $config->forum_for_events;
					$poster_id = $config->official_user;

					$sql = 'SELECT *
						FROM _forum_topics
						WHERE topic_id = ?';
					if (!$row_current_topic = sql_fieldrow(sql_filter($sql, $event_current_topic))) {
						$insert = array(
							'topic_title' => $event_name,
							'topic_poster' => $poster_id,
							'topic_time' => $post_time,
							'forum_id' => $forum_id,
							'topic_locked' => 0,
							'topic_announce' => 0,
							'topic_important' => 0,
							'topic_vote' => 1,
							'topic_featured' => 1,
							'topic_points' => 1
						);
						$topic_id = sql_insert('forum_topics', $insert);

						$event_current_topic = 0;
					} else {
						$topic_id = $event_current_topic;

						$post_message .= ' en la secci&oacute;n de eventos';

						$sql = 'UPDATE _forum_topics SET topic_title = ?
							WHERE topic_id = ?';
						sql_query(sql_filter($sql, $event_name, $topic_id));
					}

					$post_message .= '.';

					$insert = array(
						'topic_id' => (int) $topic_id,
						'forum_id' => $forum_id,
						'poster_id' => $poster_id,
						'post_time' => $post_time,
						'poster_ip' => $user->ip,
						'post_text' => $post_message,
						'post_np' => ''
					);
					$post_id = sql_insert('forum_posts', $insert);

					$sql = 'UPDATE _events SET event_topic = ?
						WHERE id = ?';
					sql_query(sql_filter($sql, $topic_id, $event_id));

					$insert = array(
						'topic_id' => $topic_id,
						'vote_text' => '&iquest;Asistir&aacute;s a ' . $event_name . '?',
						'vote_start' => time(),
						'vote_length' => ($poll_length * 86400)
					);
					$poll_id = sql_insert('poll_options', $insert);

					$poll_options = array(1 => 'Si asistir&eacute;');

					foreach ($poll_options as $option_id => $option_text) {
						$sql_insert = array(
							'vote_id' => $poll_id,
							'vote_option_id' => $option_id,
							'vote_option_text' => $option_text,
							'vote_result' => 0
						);
						sql_insert('poll_results', $sql_insert);

						$poll_option_id++;
					}

					$sql = 'UPDATE _forums SET forum_posts = forum_posts + 1, forum_last_topic_id = ?' . ((!$event_current_topic) ? ', forum_topics = forum_topics + 1 ' : '') . '
						WHERE forum_id = ?';
					sql_query(sql_filter($sql, $topic_id, $forum_id));

					$sql = 'UPDATE _forum_topics SET topic_first_post_id = ?, topic_last_post_id = ?
						WHERE topic_id = ?';
					sql_query(sql_filter($sql, $post_id, $post_id, $topic_id));

					/*$sql = 'UPDATE _members SET user_posts = user_posts + 1
						WHERE user_id = ?';
					sql_query(sql_filter($sql, $poster_id));*/

					// TODO: Today save
					// $user->save_unread(UH_T, $topic_id);

					redirect(s_link('events', $event_alias));
				}
			}

			_style('error', array(
				'MESSAGE' => parse_error($upload->error))
			);
		}

		$sql = 'SELECT topic_id, topic_title
			FROM _forum_topics t
			LEFT OUTER JOIN _events e ON t.topic_id = e.event_topic
			WHERE e.event_topic IS NULL
				AND forum_id = ?
				AND t.topic_active = 1
			ORDER BY topic_time DESC';
		$topics = sql_rowset(sql_filter($sql, $config->forum_for_events));

		foreach ($topics as $i => $row) {
			if (!$i) _style('topics');

			_style('topics.row', array(
				'TOPIC_ID' => $row->topic_id,
				'TOPIC_TITLE' => $row->topic_title)
			);
		}

		//
		// Get artists where this member is an authorized member
		//
		$sql = 'SELECT a.ub, a.name, a.subdomain
			FROM _artists_auth au
			INNER JOIN _artists a ON au.ub = a.ub
			WHERE au.user_id = ?
			ORDER BY a.name';
		if ($artists = sql_rowset(sql_filter($sql, $user->d('id')))) {
			foreach ($artists as $i => $row) {
				if (!$i) _style('artists');

				_style('artists.row', array(
					'NAME' => $row->name,
					'SUBDOMAIN' => $row->subdomain
				));
			}
		}

		$current_month = date('m');
		$current_day = date('d');
		$current_year = date('Y');

		foreach (range(1, 31) as $row) {
			_style('event_day', array(
				'VALUE' => $row,
				'TEXT' => $row,
				'SELECTED' => ($row == $current_day) ? ' selected="selected"' : '',
			));
		}

		foreach (range(1, 12) as $row) {
			_style('event_month', array(
				'VALUE' => $row,
				'TEXT' => $row,
				'SELECTED' => ($row == $current_month) ? ' selected="selected"' : '',
			));
		}

		foreach (range($current_year + 2, $current_year - 2) as $row) {
			_style('event_year', array(
				'VALUE' => $row,
				'TEXT' => $row,
				'SELECTED' => ($row == $current_year) ? ' selected="selected"' : '',
			));
		}

		foreach (range(0, 23) as $row) {
			_style('event_hours', array(
				'VALUE' => $row,
				'TEXT' => $row,
				'SELECTED' => '',
			));
		}

		foreach (range(0, 55, 5) as $row) {
			_style('event_minutes', array(
				'VALUE' => $row,
				'TEXT' => $row,
				'SELECTED' => '',
			));
		}

		v_style(array(
			'V_DAY' => $current_day,
			'V_MONTH' => $current_month,
			'V_YEAR' => $current_year,
		));

		return;
	}
}