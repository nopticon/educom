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

class downloads {
	public $ud;
	public $ud_song;
	public $dl_data;
	public $filename;
	public $filepath;

	public function __construct() {
		$this->ud = $this->ud_song = $this->dl_data = w();
		$this->filename = $this->filepath = '';

		return;
	}

	public function media_type($ud) {
		global $user;

		$type = 0;
		switch ($ud) {
			case E_UD_AUDIO:
				$type = array('lang' => lang('audio'), 'extension' => 'mp3', 'av' => 'Audio');
				break;
			case E_UD_VIDEO:
				$type = array('lang' => lang('video'), 'extension' => 'wmv', 'av' => 'Video');
				break;
		}
		return (object) $type;
	}

	public function media_view() {
		global $user, $config, $comments;

		if (!$this->auth['adm'] && !$this->auth['mod']) {
			$sql = 'UPDATE _dl SET views = views + 1
				WHERE id = ?';
			sql_query(sql_filter($sql, $this->dl_data->id));
		}

		$stats_text = '';
		foreach (array('views' => 'VIEW', 'downloads' => 'DL') as $item => $stats_lang) {
			$stats_text .= (($stats_text != '') ? ', ' : '') . '<strong>' . $this->dl_data->$item . '</strong> ' . lang($stats_lang) . (($this->dl_data->$item > 1) ? 's' : '');
		}

		v_style(array(
			'S_DOWNLOAD_ACTION' => s_link('a', $this->data->subdomain, 'downloads', $this->dl_data->id, 'save'),

			'DL_ID' => $this->dl_data->id,
			'DL_A' => $this->data->ub,
			'DL_TITLE' => $this->dl_data->title,
			'DL_FORMAT' => $this->dl_data->av,
			'DL_DURATION' => $this->dl_data->duration,
			'DL_ALBUM' => $this->dl_data->album,
			'DL_YEAR' => $this->dl_data->year,
			'DL_POSTS' => $this->dl_data->posts,
			'DL_VOTES' => $this->dl_data->votes,
			'DL_FILESIZE' => $this->format_filesize($this->dl_data->filesize),
			'DL_STATS' => $stats_text)
		);

		//
		// FAV
		//
		$is_fav = false;

		$sql = 'SELECT dl_id
			FROM _dl_fav
			WHERE dl_id = ?
				AND user_id = ?';
		if (sql_field(sql_filter($sql, $this->dl_data->id, $user->d('user_id')), 'dl_id', 0)) {
			$is_fav = true;
		}

		if (!$is_fav) {
			_style('dl_fav', array(
				'URL' => s_link('a', $this->data->subdomain, 'downloads', $this->dl_data->id, 'fav'))
			);
		}

		//
		// UD POLL
		//
		$user_voted = false;
		if ($this->dl_data->votes && $this->auth['user'] && !$this->auth['adm'] && !$this->auth['mod']) {
			$sql = 'SELECT user_id
				FROM _dl_voters
				WHERE ud = ?
					AND user_id = ?';
			if (sql_field(sql_filter($sql, $this->dl_data->id, $user->d('user_id')), 'user_id', 0)) {
				$user_voted = true;
			}
		}

		_style('ud_poll');

		if ($this->auth['adm'] || $this->auth['mod'] || !$this->auth['user'] || $user_voted) {
			$sql = 'SELECT option_id, vote_result
				FROM _dl_vote
				WHERE ud = ?
				ORDER BY option_id';
			$results = sql_rowset(sql_filter($sql, $this->dl_data->id), 'option_id', 'vote_result');

			_style('ud_poll.results');

			foreach ($this->voting['ud'] as $row) {
				$vote_result = (isset($results[$row])) ? (int) $results[$row] : 0;
				$vote_percent = ($this->dl_data->votes > 0) ? $vote_result / $this->dl_data->votes : 0;

				_style('ud_poll.results.item', array(
					'CAPTION' => lang('ub_udv' . $row),
					'RESULT' => $vote_result,
					'PERCENT' => sprintf("%.1d", ($vote_percent * 100)))
				);
			}
		} else {
			_style('ud_poll.options', array(
				'S_VOTE_ACTION' => s_link('a', $this->data->subdomain, 'downloads', $this->dl_data->id, 'vote'))
			);

			foreach ($this->voting['ud'] as $row) {
				_style('ud_poll.options.item', array(
					'ID' => $row,
					'CAPTION' => lang('ub_udv' . $row))
				);
			}
		}

		//
		// UD MESSAGES
		//
		$comments_ref = s_link('a', $this->data->subdomain, 'downloads', $this->dl_data->id);

		if ($this->dl_data->posts) {
			$start = request_var('dps', 0);
			$comments->ref = $comments_ref;
			$comments->auth = $this->auth;

			$sql = 'SELECT p.*, u.user_id, u.username, u.username_base, u.user_avatar
				FROM _dl d, _dl_posts p, _artists a, _members u
				WHERE d.id = ?
					AND d.ub = ?
					AND d.id = p.download_id
					AND d.ub = a.ub
					AND p.post_active = 1
					AND p.poster_id = u.user_id
				ORDER BY p.post_time DESC
				LIMIT ??, ??';

			$comments->data = array(
				'SQL' => sql_filter($sql, $this->dl_data->id, $this->data->ub, $start, $config->s_posts)
			);

			if ($this->auth['user']) {
				$comments->data['CONTROL']['reply'] = array(
					'REPLY' => array(
						'URL' => s_link('a', $this->data->subdomain, 'comments', '%d', 'reply'),
						'ID' => 'post_id'
					)
				);
			}

			if ($this->auth['user'] && !$this->auth['adm'] && !$this->auth['mod']) {
				$comments->data['CONTROL']['report'] = array(
					'REPORT' => array(
						'URL' => s_link('a', $this->data->subdomain, 'comments', '%d', 'report'),
						'ID' => 'post_id'
					)
				);
			}

			if ($this->auth['adm'] || $this->auth['mod']) {
				$comments->data['CONTROL']['auth'] = w();

				if ($this->auth['adm'] && $user->is('founder')) {
					$comments->data['CONTROL']['auth']['EDIT'] = array(
						'URL' => s_link('acp', array('artist_message', 'a' => $this->data->subdomain, 'id' => '%d', 'action' => 'modify')),
						'ID' => 'post_id'
					);
				}

				$comments->data['CONTROL']['auth']['DELETE'] = array(
					'URL' => s_link('acp', array('artist_message', 'a' => $this->data->subdomain, 'id' => '%d', 'action' => 'remove')),
					'ID' => 'post_id'
				);
			}

			//
			$comments->view($start, 'dps', $this->dl_data->posts, $config->s_posts, 'ud_posts', 'DMSG_', 'TOPIC_', false);
		}

		if ($this->auth['post']) {
			if ($this->auth['user']) {
				_style('dl_post_box', array(
					'REF' => $comments_ref,
					'NL' => (int) !$this->auth['user'])
				);
			} else {
				_style('dl_no_guest_posting', array(
					'LEGEND' => sprintf(lang('ub_no_guest_posting'), $this->data->name, s_link('my register')))
				);
			}
		} else {
			_style('dl_no_post_auth');

			if ($this->auth['post_until']) {
				_style('dl_no_post_auth.until', array(
					'UNTIL_DATETIME' => $user->format_date($this->auth['post_until']))
				);
			}
		}

		return;
	}

	public function media_save() {
		$sql = 'UPDATE _dl SET downloads = downloads + 1
			WHERE id = ?';
		sql_query(sql_filter($sql, $this->dl_data->id));

		$orig = array('&ntilde;', '&Ntilde;', '.');
		$repl = array('n', 'N', '');

		$this->filename = str_replace($orig, $repl, $this->data->name) . '_' . str_replace($orig, $repl, $this->dl_data->title) . '.' . $this->dl_data->extension;
		$this->filepath = 'data/artists/' . $this->data->ub . '/media/' . $this->dl_data->id . '.' . $this->dl_data->extension;
		$this->media_file();

		return;
	}

	public function media_vote() {
		if (!$this->auth['user']) {
			do_login();
		}

		global $user;

		$option_id = request_var('vote_id', 0);
		$url = s_link('a', $this->data['subdomain'], 'downloads', $this->dl_data->id);

		if ($this->auth['adm'] || $this->auth['mod'] || !in_array($option_id, $this->voting['ud'])) {
			redirect($url);
		}

		$sql = 'SELECT user_id
			FROM _dl_voters
			WHERE ud = ?
				AND user_id = ?';
		if (sql_field(sql_filter($sql, $this->dl_data->id, $user->d('user_id')), 'user_id', 0)) {
			redirect($url);
		}

		$sql = 'UPDATE _dl_vote SET vote_result = vote_result + 1
			WHERE ud = ?
				AND option_id = ?';
		sql_query(sql_filter($sql, $this->dl_data->id, $option_id));

		if (!sql_affectedrows()) {
			$sql_insert = array(
				'ud' => $this->dl_data->id,
				'option_id' => $option_id,
				'vote_result' => 1
			);
			sql_insert('dl_vote', $sql_insert);
		}

		$sql_insert = array(
			'ud' => $this->dl_data->id,
			'user_id' => $user->d('user_id'),
			'user_option' => $option_id
		);
		sql_insert('dl_voters', $sql_insert);

		$sql = 'UPDATE _dl SET votes = votes + 1
			WHERE id = ?';
		sql_query(sql_filter($sql, $this->dl_data->id));

		redirect($url);
	}

	public function media_fav() {
		if (!$this->auth['user']) {
			do_login();
		}

		global $user;

		$url = s_link('a', $this->data->subdomain, 'downloads', $this->dl_data->id);

		$sql = 'SELECT dl_id
			FROM _dl_fav
			WHERE dl_id = ?
				AND user_id = ?';
		if (sql_field(sql_filter($sql, $this->dl_data->id, $user->d('user_id')), 'dl_id', 0)) {
			redirect($url);
		}

		$sql_insert = array(
			'dl_id' => $this->dl_data->id,
			'user_id' => $user->d('user_id'),
			'favtime' => time()
		);
		sql_insert('dl_fav', $sql_insert);

		$sql = 'UPDATE _members SET user_dl_favs = user_dl_favs + 1
			WHERE user_id = ?';
		sql_query(sql_filter($sql, $user->d('user_id')));

		return redirect($url);
	}

	public function media_file($name = '', $path = '', $data = '', $content_type = 'application/octet-stream', $disposition = 'attachment') {
		sql_close();

		$bad_chars = array("'", "\\", ' ', '/', ':', '*', '?', '"', '<', '>', '|');

		$this->filename = ($name != '') ? $name : $this->filename;
		$this->filepath = ($path != '') ? $path : $this->filepath;

		$this->filename = rawurlencode(str_replace($bad_chars, '_', $this->filename));
		$this->filename = 'RockRepublik__' . preg_replace("/%(\w{2})/", '_', $this->filename);

		// Headers
		header('Content-Type: ' . $content_type . '; name="' . $this->filename . '"');
		header('Content-Disposition: ' . $disposition . '; filename="' . $this->filename . '"');
		header('Accept-Ranges: bytes');
		header('Pragma: no-cache');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Content-transfer-encoding: binary');

		if ($data == '') {
			$this->filepath = '../' . $this->filepath;

			header('Content-length: ' . @filesize($this->filepath));
			@readfile($this->filepath);
		} else {
			print($data);
		}

		flush();
		exit;
	}

	public function format_filesize($filesize) {
		$mb = ($filesize >= 1048576) ? true : false;
		$div = ($mb) ? 1048576 : 1024;
		return bcdiv($filesize, $div, 2) . (($mb) ? ' MB' : ' KB');
	}
}