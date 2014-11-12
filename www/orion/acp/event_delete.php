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

class __event_delete extends mac {
	public function __construct() {
		parent::__construct();
		
		$this->auth('colab_admin');
	}
	
	public function _home() {
		global $config, $user, $cache;
		
		if (!_button()) {
			return;
		}
		
		$v = _request(array('event' => 0));
		
		$sql = 'SELECT *
			FROM _events
			WHERE id = ?';
		if (!$object = sql_fieldrow(sql_filter($sql, $v->event))) {
			fatal_error();
		}
		
		$sql = 'DELETE FROM _events
			WHERE id = ?';
		sql_query(sql_filter($sql, $v->event));
		
		return redirect(s_link('events'));
	}
}