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

class __forums_topic_poll extends mac {
	public function __construct() {
		parent::__construct();
		
		$this->auth('founder');
	}
	
	public function _home() {
		global $config, $user, $cache;
		
		if (!_button()) {
			return false;
		}
		
		if (!$topic_id = request_var('topic_id', '')) {
			fatal_error();
		}
		
		$sql = 'SELECT *
			FROM _poll_options
			WHERE topic_id = ?';
		if (!$data_opt = sql_fieldrow(sql_filter($sql, $topic_id))) {
			fatal_error();
		}
		
		$sql = 'SELECT v.*, m.username, r.vote_option_text
			FROM _poll_voters v, _members m, _poll_results r
			WHERE v.vote_id = ?
				AND v.vote_id = r.vote_id
				AND v.vote_user_id = m.user_id
				AND r.vote_option_id = v.vote_cast';
		$result = sql_rowset(sql_filter($sql, $data_opt->vote_id));
		
		echo '<table>';
		
		foreach ($result as $row) {
			echo '<tr>
			<td>' . $row->username . '</td>
			<td>' . $row->vote_option_text . '</td>
			<td>' . $row->vote_user_ip . '</td>
			</tr>';
		}
		
		echo '</table><br /><br /><br />';
		
		return;
	}
}