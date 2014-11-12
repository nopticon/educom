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

class __news_modify extends mac {
	public function __construct() {
		parent::__construct();
		
		$this->auth('founder');
	}
	
	public function _home() {
		global $config, $user, $cache;
		
		$submit2 = _button('submit2');
		
		if (_button() || $submit2) {
			$news_id = request_var('news_id', 0);
			
			$sql = 'SELECT *
				FROM _news
				WHERE news_id = ?';
			if (!$news_data = sql_fieldrow(sql_filter($sql, $news_id))) {
				fatal_error();
			}
			
			if ($submit2) {
				$post_subject = request_var('post_subject', '');
				$post_desc = request_var('post_desc', '', true);
				$post_message = request_var('post_text', '', true);
				
				if (empty($post_desc) || empty($post_message)) {
					_pre('Campos requeridos.', true);
				}
				
				$comments = new _comments();
				
				$post_message = $comments->prepare($post_message);
				$post_desc = $comments->prepare($post_desc);
				
				//
				$sql = 'UPDATE _news SET post_subject = ?, post_desc = ?, post_text = ?
					WHERE news_id = ?';
				sql_query(sql_filter($sql, $post_subject, $post_desc, $post_message, $news_id));
				
				$cache->delete('news');
				redirect(s_link('news', $news_id));
			}
			
			if (_button()) {
				_style('edit', array(
					'ID' => $news_data->news_id,
					'SUBJECT' => $news_data->post_subject,
					'DESC' => $news_data->post_desc,
					'TEXT' => $news_data->post_text)
				);
			}
		}
		
		if (!_button()) {
			_style('field');
		}
		
		return;
	}
}