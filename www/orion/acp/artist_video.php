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

class __artist_video extends mac {
	public function __construct() {
		parent::__construct();
		
		$this->auth('artist');
	}
	
	/*
	Show all videos added to the artist.
	*/
	public function _home() {
		global $config, $user, $comments;
		
		$this->_artist();
		
		if ((_button() && $this->create()) || (_button('remove') && $this->remove())) {
			return;
		}
		
		$sql = 'SELECT *
			FROM _artists_video
			WHERE video_a = ?
			ORDER BY video_added DESC';
		$result = sql_rowset(sql_filter($sql, $this->object->ub));

		foreach ($result as $i => $row) {
			if (!$i) _style('video');

			_style('video.row', array(
				'ID' => $row->video_id,
				'CODE' => $row->video_code,
				'NAME' => $row->video_name,
				'TIME' => $user->format_date($row->video_added))
			);
		}
		
		return;
	}
	
	/*
	Create video for this artist.
	*/
	private function create() {
		$code = request_var('code', '');
		$vname = request_var('vname', '');
		
		if (!empty($code)) {
			$sql = 'SELECT *
				FROM _artists_video
				WHERE video_a = ?
					AND video_code = ?';
			if (sql_fieldrow(sql_filter($sql, $this->object->ub, $code))) {
				$code = '';
			}
		}
		
		if (!empty($code)) {
			$code = get_yt_code($code);
		}
		
		if (!empty($code)) {
			$insert = array(
				'video_a' => $this->object->ub,
				'video_name' => $vname,
				'video_code' => $code,
				'video_added' => time()
			);
			sql_insert('artists_video', $insert);

			$sql = 'UPDATE _artists SET a_video = a_video + 1
				WHERE ub = ?';
			sql_query(sql_filter($sql, $this->object->ub));
		}
		
		return redirect(_page());
	}

	/*
	Remove selected videos from the artist.
	*/
	private function remove() {
		$v = _request(array('group' => array(0)));

		if (!$v->group) {
			return;
		}

		$sql = 'SELECT video_id
			FROM _artists_video
			WHERE video_id IN (??)
				AND video_a = ?';
		$result = sql_rowset(sql_filter($sql, implode(',', $v->group), $this->object->ub), false, 'video_id');

		if (!$result) {
			return;
		}

		$sql = 'DELETE FROM _artists_video
			WHERE video_id IN (??)
				AND video_a = ?';
		sql_query(sql_filter($sql, implode(',', $result), $this->object->ub));

		return redirect(s_link('acp', array('artist_video', 'a' => $this->object->subdomain)));
	}
}