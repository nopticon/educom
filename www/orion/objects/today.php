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
		// $this->get_faults();
		
		return;
	}

	public function get_tasks($user_id = false) {
		global $user, $comments;

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
				if (!$activity_list = sql_rowset(sql_filter($sql, $user_id))) {
					_style([$this->user_role, 'no_activities']);
				}

				foreach ($activity_list as $i => $row) {
					if (!$i) _style([$this->user_role, 'activities']);

					foreach (w('start end') as $field) {
						$row->{'activity_' . $field} = $user->format_date(strtotime($row->{'activity_' . $field}), 'l, ' . lang('date_format'));
					}

					$row->username_base = s_link('m', $row->username_base);
					$row->activity_description = $comments->parse_message($row->activity_description);

					_style([$this->user_role, 'activities', 'row'], $row);
				}

				$sql = 'SELECT f.*, c.nombre_curso, t.nombre_catedratico
					FROM faltas f
					INNER JOIN cursos c ON c.id_curso = f.course_id
					INNER JOIN catedratico t ON t.id_member = f.teacher_id
					INNER JOIN alumno a ON a.id_alumno = f.id_alumno
					INNER JOIN _members m ON m.user_id = a.id_member
					WHERE m.user_id = ?
					ORDER BY f.id_falta DESC';
				if (!$faults_list = sql_rowset(sql_filter($sql, $user_id))) {
					_style([$this->user_role, 'no_faults']);
				}

				foreach ($faults_list as $j => $row) {
					if (!$j) _style([$this->user_role, 'faults']);

					foreach (w('fecha_falta') as $field) {
						$row->{$field} = $user->format_date(strtotime($row->{$field}), 'l, ' . lang('date_format'));
					}

					// $row->username_base = s_link('m', $row->username_base);

					_style([$this->user_role, 'faults', 'row'], $row);
				}

				$sql = 'SELECT a.attend_date, m.nombre_curso, c.nombre_catedratico
					FROM _student_attends a
					INNER JOIN cursos m ON a.attend_schedule = m.id_curso
					INNER JOIN catedratico c ON a.attend_teacher = c.id_member
					WHERE attend_member = ?
						AND attend_value = 0
					ORDER BY a.attend_date DESC';
				if (!$attends_list = sql_rowset(sql_filter($sql, $user_id))) {
					_style([$this->user_role, 'no_attends']);
				}

				foreach ($attends_list as $j => $row) {
					if (!$j) _style([$this->user_role, 'attends']);

					foreach (w('attend_date') as $field) {
						$row->{$field} = $user->format_date(strtotime($row->{$field}), 'l, ' . lang('date_format'));
					}

					_style([$this->user_role, 'attends', 'row'], $row);
				}
				break;
			case 'supervisor':
				$sql = 'SELECT m.user_id, m.username, m.username_base
					FROM _members m
					INNER JOIN alumnos_encargados e ON e.student = m.user_id
					WHERE e.supervisor = ?
					ORDER BY m.username';
				$list = sql_rowset(sql_filter($sql, $user_id));

				foreach ($list as $i => $row) {
					if (!$i) _style([$this->user_role, 'activities']);

					_style([$this->user_role, 'activities', 'student'], $row);

					// 
					// Get tasks for each student
					// 
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
					if (!$tasks_list = sql_rowset(sql_filter($sql, $row->user_id))) {
						_style([$this->user_role, 'activities', 'student', 'no_tasks']);
					}

					foreach ($tasks_list as $j => $row2) {
						if (!$j) _style([$this->user_role, 'activities', 'student', 'tasks']);

						foreach (w('start end') as $field) {
							$row2->{'activity_' . $field} = $user->format_date(strtotime($row2->{'activity_' . $field}), 'l, ' . lang('date_format'));
						}

						$row2->username_base = s_link('m', $row2->username_base);

						_style([$this->user_role, 'activities', 'student', 'tasks', 'row'], $row2);
					}

					$sql = 'SELECT f.*, c.nombre_curso, t.nombre_catedratico
						FROM faltas f
						INNER JOIN cursos c ON c.id_curso = f.course_id
						INNER JOIN catedratico t ON t.id_member = f.teacher_id
						INNER JOIN alumno a ON a.id_alumno = f.id_alumno
						INNER JOIN _members m ON m.user_id = a.id_member
						WHERE m.user_id = ?
						ORDER BY f.id_falta DESC';
					if (!$faults_list = sql_rowset(sql_filter($sql, $row->user_id))) {
						_style([$this->user_role, 'activities', 'student', 'no_faults']);
					}

					foreach ($faults_list as $j => $row2) {
						if (!$j) _style([$this->user_role, 'activities', 'student', 'faults']);

						foreach (w('fecha_falta') as $field) {
							$row2->{$field} = $user->format_date(strtotime($row2->{$field}), 'l, ' . lang('date_format'));
						}

						// $row2->username_base = s_link('m', $row2->username_base);

						_style([$this->user_role, 'activities', 'student', 'faults', 'row'], $row2);
					}

					$sql = 'SELECT a.attend_date, m.nombre_curso, c.nombre_catedratico
						FROM _student_attends a
						INNER JOIN cursos m ON a.attend_schedule = m.id_curso
						INNER JOIN catedratico c ON a.attend_teacher = c.id_member
						WHERE attend_member = ?
							AND attend_value = 0
						ORDER BY a.attend_date DESC';
					if (!$attends_list = sql_rowset(sql_filter($sql, $row->user_id))) {
						_style([$this->user_role, 'activities', 'student', 'no_attends']);
					}

					foreach ($attends_list as $j => $row) {
						if (!$j) _style([$this->user_role, 'activities', 'student', 'attends']);

						foreach (w('attend_date') as $field) {
							$row->{$field} = $user->format_date(strtotime($row->{$field}), 'l, ' . lang('date_format'));
						}

						_style([$this->user_role, 'activities', 'student', 'attends', 'row'], $row);
					}
				}

				// _pre($list, true);
				break;
			default:
				break;
		}

		return;
	}

	public function get_faults($user_id = false) {
		global $user;

		if ($user_id === false) {
			$user_id = $user->d('user_id');
		}

		switch ($this->user_role) {
			case 'student':
				$sql = 'SELECT f.*, c.nombre_curso, t.nombre_catedratico
					FROM faltas f
					INNER JOIN cursos c ON c.id_curso = f.course_id
					INNER JOIN catedratico t ON t.id_member = f.teacher_id
					INNER JOIN alumno a ON a.id_alumno = f.id_alumno
					INNER JOIN _members m ON m.user_id = a.id_member
					WHERE m.user_id = ?
					ORDER BY f.id_falta DESC';
				if (!$faults_list = sql_rowset(sql_filter($sql, $user_id))) {
					_style([$this->user_role, 'no_faults']);
				}

				foreach ($faults_list as $j => $row) {
					if (!$j) _style([$this->user_role, 'faults']);

					foreach (w('fecha_falta') as $field) {
						$row->{$field} = $user->format_date(strtotime($row->{$field}), 'l, ' . lang('date_format'));
					}

					// $row->username_base = s_link('m', $row->username_base);

					_style([$this->user_role, 'faults', 'row'], $row);
				}
				break;
			case 'supervisor':
				$sql = 'SELECT m.user_id, m.username, m.username_base
					FROM _members m
					INNER JOIN alumnos_encargados e ON e.student = m.user_id
					WHERE e.supervisor = ?
					ORDER BY m.username';
				$list = sql_rowset(sql_filter($sql, $user_id));

				foreach ($list as $i => $row) {
					// if (!$i) _style([$this->user_role, 'activities']);

					// _style([$this->user_role, 'activities', 'student'], $row);

					$sql = 'SELECT f.*, c.nombre_curso, t.nombre_catedratico
						FROM faltas f
						INNER JOIN cursos c ON c.id_curso = f.course_id
						INNER JOIN catedratico t ON t.id_member = f.teacher_id
						INNER JOIN alumno a ON a.id_alumno = f.id_alumno
						INNER JOIN _members m ON m.user_id = a.id_member
						WHERE m.user_id = ?
						ORDER BY f.id_falta DESC';
					if (!$faults_list = sql_rowset(sql_filter($sql, $row->user_id))) {
						_style([$this->user_role, 'activities', 'student', 'no_faults']);
					}

					foreach ($faults_list as $j => $row2) {
						if (!$j) _style([$this->user_role, 'activities', 'student', 'faults']);

						foreach (w('fecha_falta') as $field) {
							$row2->{$field} = $user->format_date(strtotime($row2->{$field}), 'l, ' . lang('date_format'));
						}

						// $row2->username_base = s_link('m', $row2->username_base);

						_style([$this->user_role, 'activities', 'student', 'faults', 'row'], $row2);
					}
				}

				// _pre($list, true);
				break;
			default:
				break;
		}

		return;
	}
}