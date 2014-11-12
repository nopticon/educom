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

class __news_images extends mac {
	public function __construct() {
		parent::__construct();
		
		$this->auth('all');
	}
	
	public function _home() {
		global $config, $user, $cache, $upload;
		
		if (_button()) {
			$news_id = request_var('news_id', 0);
			
			$sql = 'SELECT news_id
				FROM _news
				WHERE news_id = ?';
			if (!sql_field(sql_filter($sql, $news_id), 'news_id', 0)) {
				fatal_error();
			}
			
			$filepath_1 = $config->news_path;
			
			$f = $upload->process($filepath_1, 'add_image', 'jpg');
			
			if (!count($upload->error) && $f !== false) {
				foreach ($f as $row) {
					$xa = $upload->resize($row, $filepath_1, $filepath_1, $news_id, array(100, 75), false, false, true);
				}
				
				redirect(s_link());
			}
			
			_style('error', array(
				'MESSAGE' => parse_error($upload->error))
			);
		}
		
		$sql = 'SELECT *
			FROM _news
			ORDER BY post_time DESC';
		$result = sql_rowset($sql);
		
		foreach ($result as $row) {
			_style('news_list', array(
				'NEWS_ID' => $row->news_id,
				'NEWS_TITLE' => $row->post_subject)
			);
		}
		
		return;
	}
}