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

class __artist_lyric_create extends mac {
	public function __construct() {
		parent::__construct();
		
		$this->auth('artist');
	}
	
	public function _home() {
		global $config, $user, $cache;

		$this->_artist();
		
		if ($this->create()) {
			return;
		}
		
		return;
	}

	private function create() {
		$v = _request(array('title' => '', 'author' => '', 'text' => ''));

		if (_empty($v)) {
			return;
		}

		$v->ub = $this->object->ub;
		sql_insert('artists_lyrics', $v);

		$sql = 'UPDATE _artists SET lirics = lirics + 1
			WHERE ub = ?';
		sql_query(sql_filter($sql, $this->object->ub));

		return redirect(s_link('a', $this->object->subdomain));
	}
}