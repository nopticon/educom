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

class __forims_points_delete extends mac {
	public function __construct() {
		parent::__construct();
		
		$this->auth('founder');
	}
	
	public function _home() {
		global $config, $user, $cache;
		
		$sql = 'SELECT *
			FROM _forum_topics_nopoints
			ORDER BY exclude_topic';
		$result = sql_rowset($sql);
		
		foreach ($result as $i => $row) {
			$sql = 'UPDATE _forum_topics
				SET topic_points = 0
				WHERE topic_id = ?';
			sql_query(sql_filter($sql, $row->exclude_topic));
			
			if (!$i) _style('topics');
			
			_style('topics.rows', array(
				'NAME' => $row->exclude_topic)
			);
		}
		
		return;
	}
}