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

class __forums_posts_space extends mac {
	public function __construct() {
		parent::__construct();
		
		$this->auth('founder');
	}
	
	public function _home() {
		global $config, $user, $cache;
		
		$sql = 'SELECT *
			FROM _forum_posts
			WHERE post_id = 125750';
		if ($row = sql_fieldrow($sql)) {
			$a_post = str_replace("\r", '', $row['post_text']);
			
			$sql = 'UPDATE _forum_posts SET post_text = ?
				WHERE post_id = ?';
			sql_query(sql_filter($sql, $a_post, $row['post_id']));
		}
		
		return;
	}
}

?>