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

class artists extends downloads {
	public $auth = array();
	public $data = array();
	public $adata = array();
	public $images = array();
	public $layout = array();
	public $voting = array();
	public $msg = array();
	public $ajx = true;

	private $make;
	private $_template;
	private $_title;

	public function __construct() {
		$this->layout = array(
			'_01' => (object) array('code' => 1, 'text' => 'UB_L01', 'tpl' => 'main'),
			'_02' => (object) array('code' => 2, 'text' => 'UB_L02', 'tpl' => 'bio'),
			'_03' => (object) array('code' => 3, 'text' => 'UB_L03', 'tpl' => 'albums'),
			'_04' => (object) array('code' => 4, 'text' => 'UB_L04', 'tpl' => 'gallery'),
			'_05' => (object) array('code' => 5, 'text' => 'UB_L05', 'tpl' => 'tabs'),
			'_06' => (object) array('code' => 6, 'text' => 'UB_L06', 'tpl' => 'lyrics'),
			'_07' => (object) array('code' => 7, 'text' => 'UB_L07', 'tpl' => 'interviews'),
			'_09' => (object) array('code' => 9, 'text' => 'DOWNLOADS', 'tpl' => 'downloads'),
			'_13' => (object) array('code' => 13, 'text' => '', 'tpl' => 'email'),
			'_16' => (object) array('code' => 16, 'text' => '', 'tpl' => 'news'),
			'_18' => (object) array('code' => 18, 'text' => 'UB_L17', 'tpl' => 'video')
		);

		$this->voting = array(
			'ub' => array(1, 2, 3, 5),
			'ud' => array(1, 2, 3, 4, 5)
		);
	}

	/*
	Default layout for artist.
	*/
	public function _main() {
		if ($this->make) {
			return true;
		}

		global $user, $config, $comments;

		// Gallery

		if ($this->data->images) {
			$sql = 'SELECT image
				FROM _artists_images
				WHERE image_default = 1
					AND ub = ?';
			$row = sql_fieldrow(sql_filter($sql, $this->data->ub));

			_style('ub_image', array(
				'IMAGE' => $config->artists_url . $this->data->ub . '/gallery/' . $row->image . '.jpg')
			);

			if ($this->data->images > 1) {
				_style('ub_image.view', array(
					'URL' => s_link('a', $this->data->subdomain, 'gallery', $row->image, 'view'))
				);
			}
		}

		// News

		if ($this->auth['user']) {
			_style('publish', array(
				'TITLE' => ($this->auth['mod']) ? lang('send_news') : lang('send_post'),
				'URL' => s_link('a', $this->data->subdomain))
			);
		}

		if ($this->data->news || $this->data->posts) {
			$sql = "(SELECT ?, p.topic_id, p.post_text, t.topic_time as post_time, m.user_id, m.username, m.username_base, m.user_avatar
				FROM _forum_topics t, _forum_posts p, _members m
				WHERE t.forum_id = ?
					AND t.topic_ub = ?
					AND t.topic_poster = m.user_id
					AND p.post_id = t.topic_first_post_id
					AND t.topic_important = 0
				ORDER BY t.topic_time DESC)
					UNION ALL
				(SELECT ?, p.post_id, p.post_text, p.post_time, m.user_id, m.username, m.username_base, m.user_avatar
				FROM _artists a, _artists_posts p, _members m
				WHERE p.post_ub = ?
					AND p.post_ub = a.ub
					AND p.post_active = 1
					AND p.poster_id = m.user_id
				ORDER BY p.post_time DESC
				LIMIT ??, ??)
				ORDER BY post_time DESC";
			if ($result = sql_rowset(sql_filter($sql, 'news', $config->ub_fans_f, $this->data->ub, 'post', $this->data->ub, 0, 10))) {
				_style('news');

				$user_profile = w();
				foreach ($result as $row) {
					$uid = $row->user_id;

					if (!isset($user_profile[$uid]) || ($uid == GUEST)) {
						$user_profile[$uid] = $comments->user_profile($row);
					}

					_style('news.row', object_merge($user_profile[$uid], array(
						'post_id' => $row->post_id,
						'datetime' => $user->format_date($row->post_time),
						'message' => $comments->parse_message($row->post_text),
						's_delete' => false)
					));
				}
			}

			$total_rows = $this->data->news + $this->data->posts;
		}

		//
		// Auth Members
		//
		if ($this->auth['mod'] || $this->data->mods_legend) {
			$sql = 'SELECT b.ub, u.user_id, u.username, u.username_base, u.user_avatar, u.user_avatar_type
				FROM _artists_auth a, _artists b, _members u
				WHERE a.ub = ?
					AND a.ub = b.ub
					AND a.user_id = u.user_id
				ORDER BY u.username';
			if ($result = sql_rowset(sql_filter($sql, $this->data->ub))) {
				foreach ($result as $i => $row) {
					if (!$i) _style('mods');

					$user_profile = $comments->user_profile($row);

					_style('mods.item', array(
						'PROFILE' => $user_profile->profile,
						'USERNAME' => $user_profile->username)
					);
				}

				$comments->reset();

				if ($this->auth['mod']) {
					_style('mods.manage', array(
						'URL' => s_link('acp', array('artist_auth', 'a' => $this->data->subdomain)))
					);
				}
			}
		}

		return;
	}

	//
	// Layout to show artist's biography
	//
	public function _bio() {
		if ($this->make) {
			return ($this->data->bio);
		}

		global $config, $comments;

		if ($this->data->featured_image) {
			_style('featured_image', array(
				'IMAGE' => $config->artists_url . $this->data->ub . '/gallery/' . $this->data->featured_image . '.jpg',
				'URL' => s_link('a', $this->data->subdomain, 'gallery', $this->data->featured_image, 'view'))
			);
		}

		//
		// Parse Biography
		//
		v_style(array(
			'UB_BIO' => $comments->parse_message($this->data->bio))
		);

		return;
	}

	/*
	Layout to show artist's albums.
	*/
	public function _albums() {
		if ($this->make) {
			return;
		}

		return;
	}

	/*
	Show all pictures associated to this artist.
	*/
	public function _gallery() {
		if ($this->make) {
			return ($this->data->images > 1);
		}

		global $config;

		$mode = request_var('mode', '');
		$download_id = request_var('download_id', 0);

		if ($mode == 'view') {
			if (!$download_id) {
				redirect(s_link('a', $this->data->subdomain, 'gallery'));
			}

			if ($mode == 'view') {
				$sql = 'SELECT g.*, COUNT(g2.image) AS prev_images
					FROM _artists_images g, _artists_images g2
					WHERE g.ub = ?
						AND g2.ub = g.ub
						AND g.image = ?
						AND g2.image <= ?
					GROUP BY g.image
					ORDER BY g.image ASC';
				$sql = sql_filter($sql, $this->data->ub, $download_id, $download_id);
			} else {
				$sql = 'SELECT g.*
					FROM _artists a, _artists_images g
					WHERE a.ub = ?
						AND a.ub = g.ub
						AND g.image = ?';
				$sql = sql_filter($sql, $this->data->ub, $download_id);
			}

			if (!$imagedata = sql_fieldrow($sql)) {
				redirect(s_link('a', $this->data->subdomain, 'gallery'));
			}
		}

		switch ($mode) {
			case 'view':
			default:
				if ($mode == 'view') {
					if (!$this->auth['mod']) {
						$sql = 'UPDATE _artists_images SET views = views + 1
							WHERE ub = ?
								AND image = ?';
						sql_query(sql_filter($sql, $this->data->ub, $imagedata->image));
					}

					_style('selected', array(
						'IMAGE' => $config->artists_url . $this->data->ub . '/gallery/' . $imagedata->image . '.jpg',
						'WIDTH' => $imagedata->width,
						'HEIGHT' => $imagedata->height)
					);

					$this->data->images--;
				}

				//
				// Get thumbnails
				//
				$sql_image = ($download_id) ? sql_filter(' AND g.image <> ? ', $download_id) : '';

				$sql = 'SELECT g.*
					FROM _artists a, _artists_images g
					WHERE a.ub = ' . $this->data->ub . '
						AND a.ub = g.ub
						' . $sql_image . '
					ORDER BY image DESC';
				if (!$result = sql_rowset(sql_filter($sql, $this->data->ub))) {
					redirect(s_link('a', $this->data->subdomain, 'gallery'));
				}

				foreach ($result as $i => $row) {
					if (!$i) _style('thumbnails');

					_style('thumbnails.row', array(
						'URL' => s_link('a', $this->data->subdomain, 'gallery', $row->image, 'view'),
						'IMAGE' => $config->artists_url . $this->data->ub . '/thumbnails/' . $row->image . '.jpg',
						'RIMAGE' => get_a_imagepath($config->artists_path, $config->artists_url, $this->data->ub, $row->image . '.jpg', w('x1 gallery')),
						'WIDTH' => $row->width,
						'HEIGHT' => $row->height,
						'FOOTER' => $row->image_footer)
					);
				}
				break;
		}

		return;
	}

	/*
	Show all tabs for this artist.
	*/
	public function _tabs() {
		if ($this->make) {
			return;
		}

		return;
	}

	/*
	Show all lyrics associated to this artist.
	*/
	public function _lyrics() {
		if ($this->make) {
			return ($this->data->lirics > 0);
		}

		global $config, $lang;

		$mode = request_var('mode', '');
		$download_id = request_var('download_id', 0);

		if ($mode == 'view' || $mode == 'save') {
			if (!$download_id) {
				redirect(s_link('a', $this->data->subdomain, 'lyrics'));
			}

			$sql = 'SELECT l.*
				FROM _artists_lyrics l
				LEFT JOIN _artists a ON a.ub = l.ub
				WHERE l.ub = ?
					AND l.id = ?';
			if (!$lyric_data = sql_fieldrow(sql_filter($sql, $this->data->ub, $download_id))) {
				redirect(s_link('a', $this->data->subdomain, 'lyrics'));
			}
		}

		switch ($mode) {
			case 'view':
			default:
				if ($mode == 'view') {
					if (!$this->auth['mod']) {
						$sql = 'UPDATE _artists_lyrics SET views = views + 1
							WHERE ub = ?
							AND id = ?';
						sql_query(sql_filter($sql, $this->data->ub, $lyric_data->id));

						$lyric_data->views++;
					}

					_style('read', array(
						'TITLE' => $lyric_data->title,
						'AUTHOR' => $lyric_data->author,
						'TEXT' => str_replace(nr(), '<br />', $lyric_data->text),
						'VIEWS' => $lyric_data->views)
					);
				}

				$sql = 'SELECT l.*
					FROM _artists_lyrics l
					LEFT JOIN _artists a ON a.ub = l.ub
					WHERE l.ub = ?
					ORDER BY title';
				$result = sql_rowset(sql_filter($sql, $this->data->ub));

				foreach ($result as $i => $row) {
					if (!$i) _style('select');

					_style('select.item', array(
						'URL' => s_link('a', $this->data->subdomain, 'lyrics', $row->id, 'view') . '#read',
						'TITLE' => $row->title,
						'SELECTED' => ($download_id && $download_id == $row->id) ? true : false)
					);
				}
				break;
		}

		return;
	}

	/*
	Show all interviews made to this artist.
	*/
	public function _interviews() {
		if ($this->make) {
			return;
		}

		return;
	}

	/*
	Show list of all songs available for listening and download.
	*/
	public function _downloads() {
		if ($this->make) {
			return ($this->data->layout == 'downloads');
		}

		if (!$download_id = request_var('download_id', 0)) {
			fatal_error();
		}

		$sql = 'SELECT d.*
			FROM _dl d
			LEFT JOIN _artists a ON d.ub = a.ub
			WHERE d.id = ?
				AND d.ub = ?';
		if (!$this->dl_data = sql_fieldrow(sql_filter($sql, $download_id, $this->data->ub))) {
			fatal_error();
		}

		$this->dl_data = object_merge($this->dl_data, $this->media_type($this->dl_data->ud));

		$mode = request_var('dl_mode', '');

		if (empty($mode)) $mode = 'view';

		if (!in_array($mode, w('view save vote fav'))) {
			redirect(s_link('a', $this->data->subdomain));
		}

		return $this->{'media_' . $mode}();
	}

	/*
	Send private message from any user to this artist.
	*/
	public function _email() {
		if ($this->make) {
			return;
		}

		if (empty($this->data->email)) {
			fatal_error();
		}

		if (!$this->auth['user']) {
			do_login();
		}

		global $user, $config;

		$error_msg = '';
		$subject = '';
		$message = '';
		$current_time = time();

		if (_button()) {
			$subject = request_var('subject', '');
			$message = request_var('message', '', true);

			if (empty($subject) || empty($message)) {
				$error_msg .= (($error_msg != '') ? '<br />' : '') . lang('fields_empty');
			}

			if (empty($error_msg)) {
				$sql = 'UPDATE _artists SET last_email = ?, last_email_user = ?
					WHERE ub = ?';
				sql_query(sql_filter($sql, $current_time, $user->d('user_id'), $this->data->ub));

				$emailer = new emailer($config->smtp_delivery);

				$emailer->from($user->d('user_email'));

				$email_headers = 'X-AntiAbuse: User_id - ' . $user->d('user_id') . nr();
				$email_headers .= 'X-AntiAbuse: Username - ' . $user->d('username') . nr();
				$email_headers .= 'X-AntiAbuse: User IP - ' . $user->ip . nr();

				$emailer->use_template('mmg_send_email', $config->default_lang);
				$emailer->email_address($this->data->email);
				$emailer->set_subject($subject);
				$emailer->extra_headers($email_headers);

				$emailer->assign_vars(array(
					'SITENAME' => $config->sitename,
					'BOARD_EMAIL' => $config->board_email,
					'FROM_USERNAME' => $user->d('username'),
					'UB_NAME' => $this->data->name,
					'MESSAGE' => $message
				));
				$emailer->send();
				$emailer->reset();

				redirect(s_link('a', $this->data->subdomain));
			}
		}

		if ($error_msg != '') {
			_style('error');
		}

		v_style(array(
			'ERROR_MESSAGE' => $error_msg,

			'SUBJECT' => $subject,
			'MESSAGE' => $message)
		);

		return;
	}

	/*
	Register stats when user want to view artist's website.
	*/
	public function _website() {
		if ($this->make) {
			return;
		}

		if ($this->data->www == '') {
			redirect(s_link('a', $this->data->subdomain));
		}

		global $user;

		if (!$this->data->www_awc && !check_www($this->data->www)) {
			trigger_error(sprintf(lang('links_cant_redirect'), $this->data->www));
		}

		$sql = 'UPDATE _artists SET www_views = www_views + 1
			WHERE ub = ?';
		sql_query(sql_filter($sql, $this->data->ub));

		return redirect($this->data->www);
	}

	/*
	Manage artist's favorites from users.
	*/
	public function _favorites() {
		if ($this->make) {
			return;
		}

		global $user;

		if (!$this->auth['user']) {
			do_login(sprintf(lang('login_be_fan'), $this->data->name));
		}

		$url = s_link('a', $this->data->subdomain);

		if ($this->auth['smod']) {
			redirect($url);
		}

		if ($this->auth['fav']) {
			$sql_member = array('user_a_favs' => $user->d('user_a_favs') - 1);

			if ($user->d('user_a_favs') == 1 && $user->d('user_type') == USER_FAN) {
				$sql_member += array('user_type' => USER_NORMAL);
			}

			$sql = 'DELETE FROM _artists_fav
				WHERE ub = ?
					AND user_id = ?';
			sql_query(sql_filter($sql, $this->data->ub, $user->d('user_id')));

			$user->delete_all_unread(UH_AF, $user->d('user_id'));
		} else {
			$sql_member = array('user_a_favs' => $user->d('user_a_favs') + 1);

			if ($user->d('user_type') == USER_NORMAL) {
				$sql_member += array('user_type' => USER_FAN);
			}

			$sql_insert = array(
				'ub' => (int) $this->data->ub,
				'user_id' => (int) $user->d('user_id'),
				'joined' => time()
			);
			$fav_nextid = sql_insert('artists_fav', $sql_insert);

			// TODO: Today save
			// $user->save_unread(UH_AF, $fav_nextid, $this->data->ub);
		}

		$sql = 'UPDATE _members SET ??
			WHERE user_id = ?';
		sql_query(sql_build($sql, sql_build('UPDATE', $sql_member), $user->d('user_id')));

		return redirect($url);
	}

	/*
	Manage news from this artist.
	*/
	public function _news() {
		if ($this->make) {
			return;
		}

		return;
	}

	/*
	Users can vote and rate artist quality.
	*/
	public function _vote() {
		if ($this->make) {
			return;
		}

		if (!$this->auth['user']) {
			do_login();
		}

		$option_id = request_var('vote_id', 0);
		$url = s_link('a', $this->data->subdomain);

		if ($this->auth['mod'] || !$option_id || !in_array($option_id, $this->voting['ub'])) {
			redirect($url);
		}

		global $user;

		$sql = 'SELECT user_id
			FROM _artists_voters
			WHERE ub = ?
				AND user_id = ?';
		if ($row = sql_fieldrow(sql_filter($sql, $this->data->ub, $user->d('user_id')))) {
			redirect($url);
		}

		//
		$sql = 'UPDATE _artists_votes SET vote_result = vote_result + 1
			WHERE ub = ?
				AND option_id = ?';
		sql_query(sql_filter($sql, $this->data->ub, $option_id));

		if (!sql_affectedrows()) {
			$sql_insert = array(
				'ub' => $this->data->ub,
				'option_id' => $option_id,
				'vote_result' => 1
			);
			sql_insert('artists_votes', $sql_insert);
		}

		$sql_insert = array(
			'ub' => $this->data->ub,
			'user_id' => $user->d('user_id'),
			'user_option' => $option_id
		);
		sql_insert('artists_voters', $sql_insert);

		$sql = 'UPDATE _artists SET votes = votes + 1
			WHERE ub = ?';
		sql_query(sql_filter($sql, $this->data->ub));

		redirect($url);
	}

	/*
	Show all artist's videos.
	*/
	public function _video() {
		if ($this->make) {
			return ($this->data->a_video > 0);
		}

		global $user;

		$sql = 'SELECT *
			FROM _artists_video
			WHERE video_a = ?
			ORDER BY video_added DESC';
		$result = sql_rowset(sql_filter($sql, $this->data->ub));

		foreach ($result as $i => $row) {
			if (!$i) _style('video');

			_style('video.row', array(
				'NAME' => $row->video_name,
				'CODE' => $row->video_code,
				'TIME' => $user->format_date($row->video_added))
			);
		}

		return;
	}

	public function get_title($default = '') {
		return (!empty($this->_title)) ? $this->_title : $default;
	}

	public function get_template($default = '') {
		return (!empty($this->_template)) ? $this->_template : $default;
	}

	private function _make($flag = false) {
		$this->make = $flag;
	}

	public function ajax() {
		return $this->ajx;
	}

	public function run() {
		if ($this->_setup()) {
			$this->_panel();

			$this->_title = $this->data->name;
			$this->_template = 'artists.view';
		} else {
			$json = request_var('json', '');

			switch ($json) {
				case 'artists':
					$sql = 'SELECT ub, name, subdomain
						FROM _artists
						ORDER BY name';
					$artists = sql_rowset($sql);

					$response = array();
					foreach ($artists as $row) {
						$response[] = array(
							'key' => $row->subdomain,
							'value' => $row->name
						);
					}

					echo json_encode($response);
					exit;
					break;
			}

			$this->_list();
			$this->latest();
		}

		return;
	}

	public function v($property, $value = -5000) {
		if ($value != -5000) {
			$this->data->$property = $value;
			return $value;
		}

		if (!isset($this->data->$property)) {
			return false;
		}

		return $this->data->$property;
	}

	public function get_data() {
		$sql = 'SELECT *
			FROM _artists
			ORDER BY name ASC';
		$this->adata = sql_rowset($sql, 'ub');

		return;
	}

	public function _setup() {
		global $user;

		$_a = request_var('id', '');
		if (!empty($_a)) {
			if (preg_match('/([0-9a-zA-Z]+)/', $_a)) {
				$sql = 'SELECT *
					FROM _artists
					WHERE subdomain = ?
					LIMIT 1';
				if ($this->data = sql_fieldrow(sql_filter($sql, strtolower($_a)))) {
					return true;
				}
			}

			fatal_error();
		}

		return false;
	}

	public function _auth() {
		global $user;

		$this->auth['user'] = ($user->is('member')) ? true : false;
		$this->auth['adm'] = ($user->is('founder')) ? true : false;
		$this->auth['mod'] = ($this->auth['adm']) ? true : false;
		$this->auth['smod'] = false;
		$this->auth['fav'] = false;
		$this->auth['post'] = true;

		if (!$this->auth['user'] || $this->data->layout == 'website') {
			return;
		}

		if ($user->is('artist')) {
			$sql = 'SELECT u.user_id
				FROM _members u, _artists_auth a, _artists b
				WHERE a.ub = ?
					AND a.user_id = ?
					AND a.user_id = u.user_id
					AND b.ub = a.ub
					AND b.ub = a.ub';
			if (sql_fieldrow(sql_filter($sql, $this->data->ub, $user->d('user_id')))) {
				$this->auth['smod'] = $this->auth['mod'] = true;
				return;
			}
		}

		$sql = 'SELECT aa.*
			FROM _artists_access aa
			LEFT JOIN _artists a ON aa.ub = a.ub
			RIGHT JOIN _members m ON aa.user_id = m.user_id
			WHERE aa.ub = ?
				AND aa.user_id = ?';
		if ($row = sql_fieldrow(sql_filter($sql, $this->data->ub, $user->d('user_id')))) {
			$current_time = time();

			if (!$row->ban_time || $row->ban_time > $current_time) {
				if ($row->ban_access) {
					global $user;

					$message = (!$row->ban_time) ? 'UB_BANNED' : sprintf(lang('ub_banned_until'), $user->format_date($row->ban_time));
					trigger_error($message);
				} else {
					$this->auth['post'] = false;
					$this->auth['post_until'] = ($row->ban_time) ? $rowban_time : 0;
				}
			} else {
				$sql = 'DELETE FROM _artists_access
					WHERE user_id = ?
						AND ub = ?';
				sql_query(sql_filter($sql, $user->d('user_id'), $this->data->ub));
			}
		}

		if ($user->d('user_type') != USER_NORMAL) {
			$sql = 'SELECT f.*
				FROM _artists b, _artists_fav f, _members m
				WHERE b.ub = ?
					AND b.ub = f.ub
					AND f.user_id = ?
					AND f.user_id = m.user_id';
			if (sql_fieldrow(sql_filter($sql, $this->data->ub, $user->d('user_id')))) {
				$this->auth['fav'] = true;
			}
		}

		return;
	}

	public function call_layout() {
		$layout = '_' . $this->data->layout;
		if (!method_exists($this, $layout)) {
			redirect(s_link('a'));
		}

		return $this->$layout();
	}

	public function stats($id) {
		if (is_array($id)) {
			$all_stats = w();
			foreach ($id as $item) {
				$all_stats[$item] = $this->stats($item);
			}
			return $all_stats;
		}

		$t_ub = $s_ub = 0;
		foreach ($this->adata as $data) {
			if ($data[$id] > $s_ub) {
				$s_ub = $data[$id];
				$t_ub = $data->ub;
			}
		}

		return ($t_ub) ? $this->adata[$t_ub] : false;
	}

	public function last_records() {
		global $user, $config, $cache;

		if (!$a_records = $cache->get('artist_records')) {
			$sql = 'SELECT ub, subdomain, name, genre
				FROM _artists
				ORDER BY datetime DESC
				LIMIT 3';
			$a_records = sql_rowset($sql, 'ub');

			$cache->save('artist_records', $a_records);
		}

		if (!$ai_records = $cache->get('ai_records')) {
			$ai_records = w();

			foreach ($a_records as $row) {
				$sql = 'SELECT ub, images
					FROM _artists_images
					WHERE ub = ?
					ORDER BY image';
				$result = sql_rowset(sql_filter($sql, $row->ub));

				foreach ($result as $row) {
					$ai_records[$row->ub][] = $row2->image;
				}
			}

			$cache->save('ai_records', $ai_records);
		}

		_style('a_records');

		foreach ($a_records as $row) {
			_style('a_records.item', array(
				'URL' => s_link('a', $row->subdomain),
				'NAME' => $row->name,
				'GENRE' => $row->genre)
			);

			if (isset($ai_records[$row->ub])) {
				$ai_select = array_rand($ai_records[$row->ub]);

				_style('a_records.item.image', array(
					'IMAGE' => $config->artists_url . $row->ub . '/thumbnails/' . $ai_records[$row->ub][$ai_select] . '.jpg')
				);
			}
		}
	}

	public function latest_music() {
		$sql = 'SELECT d.id, d.title, a.subdomain, a.name
			FROM _dl d, _artists a
			WHERE d.ud = 1
				AND d.ub = a.ub
			ORDER BY d.date DESC
			LIMIT 0, 10';
		$result = sql_rowset($sql);

		foreach ($result as $row) {
			_style('downloads', array(
				'URL' => s_link('a', $row->subdomain, 'downloads', $row->id),
				'A' => $row->name,
				'T' => $row->title)
			);
		}

		return true;
	}

	public function top_stats() {
		global $user, $config;

		_style('a_stats');

		$all_data = $this->stats(w('datetime views votes posts'));

		if ($all_data['datetime']) {
			global $cache;

			$a_random = w();
			if (!$a_random = $cache->get('a_last_images')) {
				$sql = 'SELECT *
					FROM _artists_images
					WHERE ub = ?
					ORDER BY image';
				if ($a_random = sql_rowset(sql_filter($sql, $all_data['datetime']->ub), false, 'image')) {
					$cache->save('a_last_images', $a_random);
				}
			}

			if (count($a_random)) {
				$selected_image = array_rand($a_random);
				if (isset($a_random[$selected_image])) {
					_style('a_stats.gallery', array(
						'IMAGE' => $config->artists_url . $all_data['datetime']->ub . '/thumbnails/' . $a_random[$selected_image] . '.jpg',
						'URL' => s_link('a', $all_data['datetime']->subdomain))
					);
				}
			}
		}

		foreach ($all_data as $id => $data) {
			if ($data->name != '') {
				_style('a_stats.item', array(
					'LANG' => lang('ub_top_' . $id),
					'URL' => s_link('a', $data->subdomain),
					'NAME' => $data->name,
					'LOCATION' => ($data->local) ? 'Guatemala' : $data->location,
					'GENRE' => $data->genre)
				);
			}
		}

		return;
	}

	public function thumbnails() {
		global $cache, $config;

		$sql = 'SELECT a.ub, a.name, a.subdomain, a.location, a.genre, i.image
			FROM _artists a
			INNER JOIN _artists_images i ON a.ub = i.ub
			WHERE a.a_active = 1
				AND i.image_default = 1
			ORDER BY RAND()
			LIMIT 4';
		$artists = sql_rowset($sql);

		foreach ($artists as $i => $row) {
			if (!$i) _style('thumbnails');

			_style('thumbnails.item', array(
				'NAME' => $row->name,
				'IMAGE' => $config->artists_url . $row->ub . '/thumbnails/' . $row->image . '.jpg',
				'URL' => s_link('a', $row->subdomain),
				'LOCATION' => ($row->local) ? 'Guatemala' : $row->location,
				'GENRE' => $row->genre)
			);
		}

		return;
	}

	public function downloads() {
		global $config;

		$sql = 'SELECT *
			FROM _dl';
		$this->ud_song = sql_rowset($sql, 'ud', false, true);

		_style('downloads');

		$ud_in_ary = w();
		foreach ($this->ud_song as $ud => $dl_data) {
			if (!$dl_size = count($dl_data)) continue;

			$ud_size = ($dl_size > $config->main_dl) ? $config->main_dl : $dl_size;
			$download_type = $this->media_type($ud);

			_style('downloads.panel', array(
				'UD' => $download_type->lang,
				'TOTAL_COUNT' => $dl_size)
			);

			for ($i = 0; $i < $ud_size; $i++) {
				$ud_rand = array_rand($dl_data);

				if (isset($ud_in_ary[$ud][$ud_rand])) {
					$i--;
					continue;
				}

				$ud_in_ary[$ud][$ud_rand] = true;

				_style('downloads.panel.item', array(
					'UB' => $this->adata[$dl_data[$ud_rand]->ub]->name,
					'TITLE' => $dl_data[$ud_rand]->title,
					'URL' => s_link('a', $this->adata[$dl_data[$ud_rand]->ub]->subdomain, 'downloads', $dl_data[$ud_rand]->id))
				);
			}
		}

		return;
	}

	public function _list() {
		global $user, $config;

		$genre = request_var('genre', '');
		$search = request_var('search', '');

		//
		// Search for all genres with artists.
		//
		$sql = 'SELECT DISTINCT g.*
			FROM _genres g
			INNER JOIN _artists_genres ag ON ag.ag_genre = g.genre_id
			ORDER BY genre_name';
		/*
			LEFT OUTER JOIN _events e ON t.topic_id = e.event_topic
			WHERE e.event_topic IS NULL
				AND forum_id = ?
				AND t.topic_active = 1
			ORDER BY topic_time DESC*/
		$genres = sql_rowset($sql);

		foreach ($genres as $i => $row) {
			if (!$i) _style('genres');

			_style('genres.row', array(
				'GENRE' => $row->genre_name)
			);
		}

		//
		// Select artists based on genre or search criteria or default list
		//
		$sql = 'SELECT a.ub, a.name, a.subdomain, a.local, a.location, a.genre, i.image
			FROM _artists a
			INNER JOIN _artists_images i ON i.ub = a.ub
			WHERE i.image_default = 1
				AND a.images > 1
			ORDER BY name';
		if (!$artists = sql_rowset($sql)) {
			redirect(s_link('a'));
		}

		foreach ($artists as $i => $row) {
			if (!$i) _style('search_match');

			_style('row', array(
				'NAME' => $row->name,
				'IMAGE' => $config->artists_url . $row->ub . '/thumbnails/' . $row->image . '.jpg',
				'URL' => s_link('a', $row->subdomain),
				'LOCATION' => ($row->local) ? 'Guatemala' : $row->location,
				'GENRE' => $row->genre)
			);
		}

		return;
	}

	public function _panel() {
		global $user, $config, $template;

		$this->data->layout = request_var('layout', '');
		$this->_auth();

		if (!$this->data->layout) {
			$this->data->layout = 'main';
		}

		switch ($this->data->layout) {
			case 'website':
			case 'favorites':
			case 'vote':
				$this->call_layout();
				break;
			default:
				$this->_make(true);

				$available = w();
				foreach ($this->layout as $i => $row) {
					if ($this->data->layout == $row->tpl) {
						$this->data->template = $row->tpl;
					}

					if ($this->{'_' . $row->tpl}()) {
						$available[$row->tpl] = true;

						_style('nav', array(
							'LANG' => lang($row->text))
						);

						if ($this->data->layout == $row->tpl) {
							_style('nav.strong');
						} else {
							$tpl = ($row->tpl == 'main') ? '' : $row->tpl;

							_style('nav.a', array(
								'URL' => s_link('a', $this->data->subdomain, $tpl))
							);
						}
					}
				}

				if (!isset($available[$this->data->layout])) {
					redirect(s_link('a', $this->data->subdomain));
				}

				$this->_make();

				//
				// Call selected layout
				//
				$this->call_layout();

				//
				// Update stats
				//
				if (!$this->auth['mod']) {
					$update_views = false;
					$current_time = time();
					$current_month = date('Ym', $current_time);

					if ($this->auth['user']) {
						$sql_viewers = array(
							'datetime' => (int) $current_time,
							'user_ip' => $user->ip
						);

						$sql_viewers2 = array(
							'ub' => (int) $this->data->ub,
							'user_id' => (int) $user->d('user_id')
						);

						$sql = 'UPDATE _artists_viewers SET ??
							WHERE ??';
						sql_query(sql_filter($sql, sql_build('UPDATE', $sql_viewers), sql_build('SELECT', $sql_viewers2)));

						if (!sql_affectedrows()) {
							$update_views = true;
							$sql_stats = array('ub' => (int) $this->data->ub, 'date' => (int) $current_month);

							sql_insert('artists_viewers', $sql_viewers + $sql_viewers2);

							$sql = 'UPDATE _artists_stats SET members = members + 1
								WHERE ??';
							sql_query(sql_filter($sql, sql_build('SELECT', $sql_stats)));

							if (!sql_affectedrows()) {
								$sql_insert = array(
									'members' => 1,
									'guests' => 0
								);
								sql_insert('artists_stats', $sql_stats + $sql_insert);
							}

							$sql = 'SELECT user_id
								FROM _artists_viewers
								WHERE ub = ?
								ORDER BY datetime DESC
								LIMIT 10, 1';
							if ($row = sql_fieldrow(sql_filter($sql, $this->data->ub))) {
								$sql = 'DELETE FROM _artists_viewers
									WHERE ub = ?
										AND user_id = ?';
								sql_query(sql_filter($sql, $this->data->ub, $row->user_id));
							}
						}
					}

					$_ps = request_var('ps', 0);

					if ((($this->auth['user'] && $update_views) || (!$this->auth['user'] && $this->data->layout == 'main')) && !$_ps) {
						$sql = 'UPDATE _artists SET views = views + 1
							WHERE ub = ?';
						sql_query(sql_filter($sql, $this->data->ub));
						$this->data->views++;

						if ((!$this->auth['user'] && $this->data->layout == 'main') && !$_ps) {
							$sql_stats = array(
								'ub' => (int) $this->data->ub,
								'date' => (int) $current_month
							);
							$sql = 'UPDATE _artists_stats SET guests = guests + 1
								WHERE ??';
							sql_query(sql_filter($sql, sql_build('SELECT', $sql_stats)));

							if (!sql_affectedrows()) {
								$sql_insert = array(
									'members' => 0,
									'guests' => 1
								);
								sql_insert('artists_stats', $sql_stats + $sql_insert);
							}
						}
					}
				}

				//
				// Own events
				//
				$timezone = $config->board_timezone * 3600;

				list($d, $m, $y) = explode(' ', gmdate('j n Y', time() + $user->timezone + $user->dst));
				$midnight = gmmktime(0, 0, 0, $m, $d, $y) - $user->timezone - $user->dst;

				$g = getdate($midnight);
				$week = mktime(0, 0, 0, $m, ($d + (7 - ($g['wday'] - 1)) - (!$g['wday'] ? 7 : 0)), $y) - $timezone;

				$sql = 'SELECT *
					FROM _events e, _artists_events ae
					WHERE ae.a_artist = ?
						AND ae.a_event = e.id
					ORDER BY e.date';
				$result = sql_rowset(sql_filter($sql, $this->data->ub));

				$events = w();
				foreach ($result as $row) {
					if ($row->date >= $midnight) {
						if ($row->date >= $midnight && $row->date < $midnight + 86400) {
							$events['is_today'][] = $row;
						} else if ($row->date >= $midnight + 86400 && $row->date < $midnight + (86400 * 2)) {
							$events['is_tomorrow'][] = $row;
						} else if ($row->date >= $midnight + (86400 * 2) && $row->date < $week) {
							$events['is_week'][] = $row;
						} else {
							$events['is_future'][] = $row;
						}
					} else if ($row->images) {
						$events['is_gallery'][] = $row;
					}
				}

				if (isset($events['is_gallery']) && count($events['is_gallery'])) {
					$gallery = array_shift($events);
					@krsort($gallery);

					_style('events_gallery');
					foreach ($gallery as $row) {
						_style('events_gallery.item', array(
							'URL' => s_link('events', $row->event_alias),
							'TITLE' => $row->title,
							'DATETIME' => $user->format_date($row->date, lang('date_format')))
						);
					}
				}

				if (count($events)) {
					_style('events_future');

					foreach ($events as $is_date => $data) {
						_style('events_future.set', array(
							'L_TITLE' => lang('ue_' . $is_date))
						);

						foreach ($data as $item) {
							_style('events_future.set.row', array(
								'ITEM_ID' => $item->id,
								'TITLE' => $item->title,
								'DATE' => $user->format_date($item->date),
								'THUMBNAIL' => $config->events_url . 'future/thumbnails/' . $item->id . '.jpg',
								'SRC' => $config->events_url . 'future/' . $item->id . '.jpg')
							);
						}
					}
				}

				//
				// Poll
				//
				$user_voted = false;
				if ($this->auth['user'] && !$this->auth['mod']) {
					$sql = 'SELECT *
						FROM _artists_voters
						WHERE ub = ?
							AND user_id = ?';
					if (sql_fieldrow(sql_filter($sql, $this->data->ub, $user->d('user_id')))) {
						$user_voted = true;
					}
				}

				_style('ub_poll');

				if ($this->auth['mod'] || !$this->auth['user'] || $user_voted) {
					$sql = 'SELECT option_id, vote_result
						FROM _artists_votes
						WHERE ub = ?
						ORDER BY option_id';
					$results = sql_rowset(sql_filter($sql, $this->data->ub), 'option_id', 'vote_result');

					_style('ub_poll.results');

					foreach ($this->voting['ub'] as $item) {
						$vote_result = (isset($results->$item)) ? intval($results->$item) : 0;
						$vote_percent = ($this->data->votes > 0) ? $vote_result / $this->data->votes : 0;

						_style('ub_poll.results.item', array(
							'CAPTION' => lang('ub_vc' . $item),
							'RESULT' => $vote_result,
							'PERCENT' => sprintf("%.1d", ($vote_percent * 100)))
						);
					}
				} else {
					_style('ub_poll.options', array(
						'S_VOTE_ACTION' => s_link('a', $this->data->subdomain, 'vote'))
					);

					foreach ($this->voting['ub'] as $item) {
						_style('ub_poll.options.item', array(
							'ID' => $item,
							'CAPTION' => lang('ub_vc' . $item))
						);
					}
				}

				//
				// Downloads
				//
				if ($this->data->um || $this->data->uv) {
					$sql = 'SELECT *
						FROM _dl
						WHERE ub = ?
						ORDER BY ud, title';
					$this->ud_song = sql_rowset(sql_filter($sql, $this->data->ub), 'ud', false, true);

					foreach ($this->ud_song as $key => $data) {
						$download_type = $this->media_type($key);
						_style('ud_block', array('LANG' => $download_type->lang));

						foreach ($data as $song) {
							_style('ud_block.item', array(
								'TITLE' => $song->title)
							);

							if (isset($this->dl_data->id) && ($song->id == $this->dl_data->id)) {
								_style('ud_block.item.strong');
								continue;
							}

							_style('ud_block.item.a', array(
								'URL' => s_link('a', $this->data->subdomain, 'downloads', $song->id))
							);
						}
					}
				}

				//
				// Fan count
				//
				$sql = 'SELECT COUNT(user_id) AS fan_count
					FROM _artists_fav
					WHERE ub = ?
					ORDER BY joined DESC';
				$fan_count = sql_field(sql_filter($sql, $this->data->ub), 'fan_count', 0);

				//
				// Make fans
				//
				if (!$this->auth['mod'] && !$this->auth['smod']) {
					_style('make_fans', array(
						'FAV_URL' => s_link('a', $this->data->subdomain, 'favorites'),
						'FAV_LANG' => ($this->auth['fav']) ? '' : lang('ub_fav_add'))
					);
				}

				//
				// Set template
				//
				v_style(array(
					'INACTIVE' => !$this->data->a_active,
					'UNAME' => $this->data->name,
					'GENRE' => $this->data->genre,
					'POSTS' => number_format($this->data->posts),
					'VOTES' => number_format($this->data->votes),
					'FANS' => $fan_count,
					'L_FANS' => ($fan_count == 1) ? lang('fan') : lang('fans'),
					'LOCATION' => ($this->data->local) ? (($this->data->location != '') ? $this->data->location . ', ' : '') . 'Guatemala' : $this->data->location)
				);

				$template->set_filenames(array(
					'a_body' => 'artists.' . $this->data->template . '.htm')
				);
				$template->assign_var_from_handle('UB_BODY', 'a_body');
				break;
		}

		return;
	}

	public function latest() {
		return;
	}

	public function a_sidebar() {
		global $config;

		$sql = 'SELECT ub, name, subdomain, genre, image
			FROM _artists a
			INNER JOIN _artists_images i ON a.ub = i.image
			WHERE i.image_default = 1
			ORDER BY RAND()
			LIMIT 1';
		if ($row = sql_fieldrow($sql)) {
			_style('random_a', array(
				'NAME' => $row->name,
				'IMAGE' => $config->artists_url . $row->ub . '/thumbnails/' . $row->image . '.jpg',
				'URL' => s_link('a', $row->subdomain),
				'GENRE' => $row->genre)
			);
		}

		return;
	}

}