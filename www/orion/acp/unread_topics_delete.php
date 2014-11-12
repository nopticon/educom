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

class __unread_topics_delete extends mac {
	public function __construct() {
		parent::__construct();
		
		$this->auth('founder');
	}
	
	public function _home() {
		global $config, $user, $cache, $comments;
		
		$sql = 'SELECT *
			FROM _members_unread
			WHERE element = ?
			GROUP BY item';
		$result = sql_rowset(sql_filter($sql, UH_T));
		
		foreach ($result as $row) {
			$sql2 = 'SELECT topic_id
				FROM _forum_topics
				WHERE topic_id = ?';
			if (!sql_field(sql_filter($sql, $row->item), 'topic_id', 0)) {
				// TODO: Today save
				// $user->delete_all_unread(UH_T, $row->item);
			}
		}
		
		_pre('Deleted', true);
		
		return;
	}
}