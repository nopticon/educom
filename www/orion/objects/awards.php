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

/*
 * type_id
 * type_alias
 * type_name
 * type_order
 */

class awards {
	public function __construct() {
		return;
	}
	
	public function run() {
		$sql = 'SELECT *
			FROM _awards_type
			ORDER BY type_order';
		$types = sql_rowset($sql);
		
		foreach ($types as $i => $row) {
			if (!$i) _style('awards');
			
			_style('awards.row', array(
				'NAME' => $row->type_name,
				'DESC' => $row->type_desc)
			);
		}
		
		return;
	}
}