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

class today {
	private $user_role;

	public function __construct() {
		return;
	}
	
	public function run() {
		global $user;

		$this->user_role = get_user_role();

		_style($this->user_role, [
			'title' => lang('TASKS')
		]);

		$this->get_tasks();
		
		return;
	}

	public function get_tasks($user_id = false) {
		global $user;

		if ($user_id === false) {
			$user_id = $user->d('user_id');
		}

		switch ($this->user_role) {
			case 'student':
				$sql = 'SELECT cu.*, ac.*, c.*, c.apellido as apellido_catedratico, m.username_base
					FROM alumno a, reinscripcion r, _activities ac, _activities_assigned aa, 
						catedratico c, grado g, secciones s, areas_cursos acu, cursos cu, _members m
					WHERE a.id_member = ?
						AND aa.assigned_student = a.id_member
						AND a.id_alumno = r.id_alumno
						AND aa.assigned_activity = ac.activity_id
						AND c.id_member = ac.activity_teacher
						AND s.id_grado = g.id_grado
						AND s.id_seccion = ac.activity_group
						AND acu.id_area = cu.id_area
						AND ac.activity_schedule = cu.id_curso
						AND m.user_id = c.id_member';
				$activity_list = sql_rowset(sql_filter($sql, $user_id));

				foreach ($activity_list as $i => $row) {
					if (!$i) _style([$this->user_role, 'activities']);

					foreach (w('start end') as $field) {
						$row->{'activity_' . $field} = $user->format_date(strtotime($row->{'activity_' . $field}), 'l, ' . lang('date_format'));
					}

					$row->username_base = s_link('m', $row->username_base);

					_style([$this->user_role, 'activities', 'row'], $row);
				}
				break;
			default:
				break;
		}

		return;
	}
}