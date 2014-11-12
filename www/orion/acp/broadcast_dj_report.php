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

class __broadcast_dj_report extends mac {
	public function __construct() {
		parent::__construct();
		
		$this->auth('founder');
	}
	
	public function _home() {
		global $config, $user, $cache;
		
		$sql = 'SELECT d.*, m.username, m.username_base
			FROM _radio_dj_log d, _members m
			WHERE d.log_uid = m.user_id
			ORDER BY log_time DESC';
		$result = sql_rowset($sql);
		
		foreach ($result as $i => $row) {
			if (!$i) _style('report');
			
			_style('report.row', array(
				'LINK' => s_link('m', $row->username_base),
				'NAME' => $row->username,
				'TIME' => $user->format_date($row->log_time))
			);
		}
		
		return;
	}
}