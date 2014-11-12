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

class __artist_select extends mac {
	public function __construct() {
		parent::__construct();
		
		$this->auth('artist');
	}
	
	public function _home() {
		global $config, $user, $cache;
		
		$artist = request_var('a', '');
		$redirect = request_var('r', '');
		
		if (!empty($artist)) {
			redirect(s_link('acp', array($redirect, 'a' => $artist)));
		}
		
		$artist_select = '';
		if (!$user->is('founder')) {
			$sql = 'SELECT ub
				FROM _artists_auth
				WHERE user_id = ?';
			$artist_select = ' WHERE ub IN (' . _implode(',', sql_rowset(sql_filter($sql, $user->d('user_id')), false, 'ub')) . ') ';
		}
		
		$sql = 'SELECT ub, subdomain, name
			FROM _artists
			??
			ORDER BY name';
		$artists = sql_rowset(sql_filter($sql, $artist_select));
		
		foreach ($artists as $i => $row) {
			if (!$i) _style('artist_list');
			
			_style('artist_list.row', array(
				'URL' => s_link('acp', array($redirect, 'a' => $row->subdomain)),
				'NAME' => $row->name)
			);
		}
		
		return;
	}
}