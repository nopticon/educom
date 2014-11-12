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

class __artist extends mac {
	public function __construct() {
		parent::__construct();

		$this->auth('colab');
	}

	public function _home() {
		$this->__home();

		return $this->warning_show();
	}

	private function __home() {
		global $config, $user, $cache;

		if (!_button()) {
			return false;
		}

		$request = _request(array('name' => '', 'local' => 0, 'location' => '', 'genre' => 0, 'email' => '', 'www' => '', 'mods' => ''));
		$request->subdomain = get_subdomain($request->name);

		if (!$request->name) {
			return $this->warning('Ingresa el nombre del artista.');
		}

		$sql = 'SELECT subdomain
			FROM _artists
			WHERE subdomain = ?';
		if (sql_field(sql_filter($sql, $request->subdomain), 'subdomain', '')) {
			return $this->warning('El subdominio ya esta en uso.');
		}

		$sql_insert = array(
			'a_active' => 1,
			'subdomain' => $request->subdomain,
			'name' => $request->name,
			'local' => (int) $request->local,
			'datetime' => time(),
			'location' => $request->location,
			// 'genre' => $requeset->genre,
			'email' => $request->email,
			'www' => str_replace('http://', '', $request->www)
		);
		$artist_id = sql_insert('artists', $sql_insert);

		//
		// Search for selected genre
		//
		$sql = 'SELECT genre_id
			FROM _genres
			WHERE genre_id = ?';
		if (sql_field(sql_filter($sql, $request->genre))) {
			$sql_insert = array(
				'ag_artist' => $artist_id,
				'ag_genre' => $request->genre
			);
			sql_insert('artists_genres', $sql_insert);
		}

		// Cache
		$cache->delete('artist_list artist_records ai_records artist_recent artist_genre');

		// Create directories
		artist_check($artist_id);

		artist_check($artist_id . ' gallery');
		artist_check($artist_id . ' media');
		artist_check($artist_id . ' thumbnails');
		artist_check($artist_id . ' x1');

		// Mods
		if (!empty($request->mods)) {
			$usernames = w();

			$a_mods = explode(nr(), $request->mods);
			foreach ($a_mods as $each) {
				$username_base = get_username_base($each);

				$sql = 'SELECT *
					FROM _members
					WHERE username_base = ?
						AND user_type NOT IN (??, ??)';
				if (!$userdata = sql_fieldrow(sql_filter($sql, $username_base, USER_INACTIVE, USER_FOUNDER))) {
					continue;
				}

				$sql_insert = array(
					'ub' => $artist_id,
					'user_id' => $userdata->user_id
				);
				sql_insert('artists_auth', $sql_insert);

				//
				$update = array('user_type' => USER_ARTIST, 'user_auth_control' => 1);

				if (!$userdata->user_rank) {
					$update['user_rank'] = (int) $config->default_a_rank;
				}

				$sql = 'UPDATE _members SET ??
					WHERE user_id = ?
						AND user_type NOT IN (??, ??)';
				sql_query(sql_filter($sql, sql_build('UPDATE', $update), $userdata->user_id, USER_INACTIVE, USER_FOUNDER));
			}

			redirect(s_link('a', $subdomain));
		}
	}
}