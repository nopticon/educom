<?php

if (!defined('IN_APP')) exit;

class today {
    private $user_role;

    public function __construct() {
        return;
    }

    public function run() {
        global $user;

        $this->user_role = get_user_role();

        $mode = request_var('mode', '');

        switch ($mode) {
            case 'task':
                $this->show_task();
                break;
            default:
                $this->get_tasks();
                break;
        }

        return;
    }

    public function show_task() {
        global $user, $comments;

        $id = request_var('id', 0);

        $sql = 'SELECT *
            FROM _activities_assigned a
            INNER JOIN _members m ON m.user_id = a.assigned_student
            WHERE a.assigned_id = ?';
        if (!$assigned_task = sql_fieldrow(sql_filter($sql, $id))) {
            fatal_error();
        }

        if (request_method() == 'post') {
            switch ($this->user_role) {
                case 'supervisor':
                    redirect(_page());
                    break;
            }

            $fields = [
                'id' => 0,
                'message' => ''
            ];
            $fields = _request($fields);

            if (empty($fields->id) || empty($fields->message)) {
                redirect(_page());
            }

            //
            // Insert task
            //
            $sql_insert = array(
                'post_activity' => $fields->id,
                'post_active' => 1,
                'post_reply' => 0,
                'post_uid' => $user->d('user_id'),
                'post_time' => time(),
                'poster_ip' => $user->ip,
                'post_text' => $fields->message
            );
            $task_post_id = sql_insert('activities_posts', $sql_insert);

            return redirect(_page());
        }

        $sql = 'SELECT *
            FROM _activities a
            INNER JOIN _members m ON m.user_id = a.activity_teacher
            INNER JOIN catedratico c ON c.id_member = m.user_id
            INNER JOIN cursos u ON u.id_curso = a.activity_schedule
            INNER JOIN secciones s ON s.id_seccion = a.activity_group
            INNER JOIN grado g ON s.id_grado = g.id_grado
            WHERE a.activity_id = ?';
        if (!$task = sql_fieldrow(sql_filter($sql, $assigned_task->assigned_activity))) {
            fatal_error();
        }

        foreach (w('start end') as $field) {
            $task->{'activity_' . $field} = $user->format_date(strtotime($task->{'activity_' . $field}), 'l, ' . lang('date_format'));
        }

        $task->username_base = s_link('m', $task->username_base);
        $task->activity_url = s_link('today', array('task', $task->activity_id));
        $task->activity_description = $comments->parse_message($task->activity_description);
        $task->assigned_username = $assigned_task->username;

        _style('task_details', $task);

        //
        // Show posts
        //
        $sql = 'SELECT p.*, m.user_id, m.username_base, m.username, m.user_avatar
            FROM _activities_posts p
            INNER JOIN _members m ON m.user_id = p.post_uid
            WHERE p.post_activity = ?
                AND p.post_active = 1';
        if ($posts = sql_rowset(sql_filter($sql, $id))) {
            foreach ($posts as $i => $row) {
                if (!$i) _style(['task_details', 'posts']);

                $row->post_time = $user->format_date($row->post_time);
                $row->post_text = $comments->parse_message($row->post_text);

                $profile = $comments->user_profile($row);
                foreach ($profile as $k => $v) {
                    $row->$k = $v;
                }

                _style(['task_details', 'posts', 'row'], $row);
            }
        }

        $allow_comments = true;

        switch ($this->user_role) {
            case 'student':
                if (!$assigned_task->assigned_comments) $allow_comments = false;
                break;
            case 'supervisor':
                $allow_comments = false;
        }

        if ($allow_comments) {
            _style(['task_details', 'post_comment_box'], array(
                'URL' => _page()
            ));
        }

        //
        // If it's a teacher, show students for this tasks
        //
        if ($this->user_role == 'teacher') {
            $sql = 'SELECT a.*, m.username, m.username_base, COUNT(p.post_text) as post_total
                FROM _activities_assigned a
                INNER JOIN _members m ON m.user_id = a.assigned_student
                LEFT JOIN _activities_posts p ON p.post_activity=a.assigned_id
                WHERE a.assigned_activity = ?
                    AND a.assigned_id <> ?
                GROUP BY a.assigned_student
                ORDER BY m.username';
            if ($students = sql_rowset(sql_filter($sql, $assigned_task->assigned_activity, $id))) {
                foreach ($students as $i => $row) {
                    if (!$i) _style(['task_details', 'assignees']);

                    $row->url = s_link('today', ['task', $row->assigned_id]);

                    $row->class = '';
                    if ($row->post_total) {
                        $row->class = 'list-group-item-success';
                    }


                    _style(['task_details', 'assignees', 'row'], $row);
                }
            }
        }

        return;
    }

    public function get_tasks($user_id = false) {
        global $user, $comments;

        if ($user_id === false) {
            $user_id = $user->d('user_id');
        }

        _style($this->user_role, [
            'title' => lang('TASKS')
        ]);

        switch ($this->user_role) {
            case 'student':
                $sql = 'SELECT cu.*, ac.*, aa.assigned_id, c.*, c.apellido as apellido_catedratico, m.username_base
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
                    $row->activity_url = s_link('today', array('task', $row->assigned_id));
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
                        $row2->activity_url = s_link('today', array('task', $row2->activity_id));
                        $row2->activity_description = $comments->parse_message($row2->activity_description);

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
