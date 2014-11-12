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

class __broadcast_program_create extends mac {
	public function __construct() {
		parent::__construct();
		
		$this->auth('founder');
	}
	
	public function _home() {
		global $config, $user, $cache;
		
		if (!_button()) {
			return;
		}
		
		$v = _request(array('name' => '', 'base' => '', 'genre' => '', 'start' => 0, 'end' => 0, 'day' => 0, 'dj' => ''));
		
		$sql = 'SELECT show_id
			FROM _radio
			WHERE show_base = ?';
		if ($row = sql_fieldrow(sql_filter($sql, $v->base))) {
			//_pre('El programa ya existe', true);
		}
		
		$time_start = mktime($v->start - $user->d('user_timezone'), 0, 0, 0, 0, 0);
		$time_end = mktime($v->end - $user->d('user_timezone'), 0, 0, 0, 0, 0);
		
		$v->start = date('H', $time_start);
		$v->end = date('H', $time_end);
		
		$dj_list = $v->dj;
		unset($v->dj);
		
		foreach ($v as $vv => $d) {
			$v->{'show_' . $vv} = $d;
			unset($v->$vv);
		}

		$show_id = sql_insert('radio', $v);
		
		$e_dj = explode(nr(), $dj_list);
		foreach ($e_dj as $rowu) {
			$rowu = get_username_base($rowu);
			
			$sql = 'SELECT *
				FROM _members
				WHERE username = ?';
			if ($row = sql_fieldrow(sql_filter($sql, $rowu))) {
				$sql_insert = array(
					'dj_show' => $show_id,
					'dj_uid' => $row->user_id
				);
				sql_insert('radio_dj', $sql_insert);
				
				$sql = 'SELECT *
					FROM _team_members
					WHERE team_id = 4
						AND member_id = ?';
				if (!$row2 = sql_fieldrow(sql_filter($sql, $row->user_id))) {
					$sql_insert = array(
						'team_id' => 4,
						'member_id' =>  $row->user_id,
						'real_name' => '',
						'member_mod' => 0
					);
					sql_insert('team_members', $sql_insert);
				}
			}
		}
		
		$cache->delete('team_members');
		
		return;
	}
}