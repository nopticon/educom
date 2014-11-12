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

class __artist_biography extends mac {
	public function __construct() {
		parent::__construct();
		
		$this->auth('artist');
	}
	
	public function _home() {
		$this->_artist();
		
		$this->__home();
		
		$sql = 'SELECT bio
			FROM _artists
			WHERE ub = ?';
		$bio = sql_field(sql_filter($sql, $this->object->ub), 'bio');

		v_style(array(
			'MESSAGE' => $bio)
		);
		
		return $this->warning_show();
	}

	private function __home() {
		global $comments;

		if (_button()) {
			$message = request_var('message', '');
			$message = $comments->prepare($message);
			
			$sql = 'UPDATE _artists SET bio = ?
				WHERE ub = ?';
			sql_query(sql_filter($sql, $message, $this->object->ub));

			$this->warning('ARTIST_BIO_UPDATED');
		}
	}
}