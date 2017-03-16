<?php

require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/../www/adm/conexion.php';

$filepath = realpath(__DIR__ . '/../private/plataforma.xlsx');

if (!@file_exists($filepath)) {
    echo 'Import file not found'; exit;
}

$split_words = '/(?#! splitCamelCase Rev:20140412)
    # Split camelCase "words". Two global alternatives. Either g1of2:
      (?<=[a-z])      # Position is after a lowercase,
      (?=[A-Z])       # and before an uppercase letter.
    | (?<=[A-Z])      # Or g2of2; Position is after uppercase,
      (?=[A-Z][a-z])  # and before upper-then-lower case.
    /x';

//
// Init vars
//
$j       = 0;
$country = 90;
$build   = [];
$list    = [];
$grade   = [];
$section = [];
$forums  = [];
$all     = [];

$student_status = 'Inscrito';
$student_year     = date('Y');

$gender_select = [
    'Masculino' => 1,
    'Femenino'  => 2
];

$sheets = array(
    0 => 'personal',
    1 => 'alumnos',
    2 => 'grados-carreras',
    3 => 'pensum',
);

$grade_replace = array(
    '1ro.' => 'Primero',
    '2do.' => 'Segundo',
    '3ro.' => 'Tercero',
    '4to.' => 'Cuarto',
    '5to.' => 'Quinto',
    '6to.' => 'Sexto',
);

//
// Read ODS file
//
$inputFileType = PHPExcel_IOFactory::identify($filepath);
$objReader = PHPExcel_IOFactory::createReader($inputFileType);
$objPHPExcel = $objReader->load($filepath);

foreach ($sheets as $id => $title) {
    $objPHPExcel->setActiveSheetIndex($id);
    $worksheet = $objPHPExcel->getActiveSheet();

    foreach ($worksheet->getRowIterator() as $row) {
        $cellIterator = $row->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(true);

        foreach ($cellIterator as $cell) {
            // $cell_value = $cell->getValue();
            $cell_value = $cell->getCalculatedValue();
            $cell_value = htmlentities($cell_value);

            // $list[$title][$cell->getRow()][$cell->getColumn()] = $cell_value;
            $list[$title][$cell->getRow()][] = $cell_value;
        }
    }
}

foreach ($list as $title => $data) {
    $header = false;
    $j = 0;

    foreach ($data as $i => $row) {
        if ($header === false) {
            $header = $row;
            // $header['A'] = 'No';
            $header = array_map('alias', $header);

            continue;
        }

        foreach ($row as $col => $val) {
            $build[$title][$j][$header[$col]] = trim($val);
            unset($list[$i][$col]);
        }

        $j++;
    }
}

foreach ($build['alumnos'] as $i => $row) {
    foreach ($row as $col => $val) {
        switch ($col) {
            case 'alumno':
            case 'encargado':
                $row[$col] = implode(' ', preg_split($split_words, $val));
                break;
            case 'grado':
                unset($row['grado']);
                $row['grade'] = $val;

                $split_row = explode(' ', $val);
                $section_str = 'A';

                if (count($split_row) > 1) {
                    $last = array_pop($split_row);

                    if (strlen($last) == 1) {
                        $section_str = $last;
                        $row['grade'] = implode(' ', $split_row);
                    }
                }

                $row['grade'] = str_replace(array_keys($grade_replace), array_values($grade_replace), $row['grade']);
                $row['section'] = $section_str;

                break;
        }
    }

    $build['alumnos'][$i] = $row;
}

foreach ($build['pensum'] as $i => $row) {
    foreach ($row as $col => $val) {
        switch ($col) {
            case 'grado':
                unset($row['grado']);
                $row['grade'] = $val;

                $split_row = explode(' ', $val);
                $section_str = 'A';

                if (count($split_row) > 1) {
                    $last = array_pop($split_row);

                    if (strlen($last) == 1) {
                        $section_str = $last;
                        $row['grade'] = implode(' ', $split_row);
                    }
                }

                $row['grade'] = str_replace(array_keys($grade_replace), array_values($grade_replace), $row['grade']);
                $row['section'] = $section_str;

                break;
        }
    }

    $build['pensum'][$i] = $row;
}

// dd($build);

//
// Get current school forum category
//
$sql = 'SELECT cat_id
    FROM _forum_categories
    WHERE cat_title = ?';
$school_category = sql_field(sql_filter($sql, 'Colegio San Gabriel'), 'cat_id', 0);

//
// Truncate tables
//
$truncate_tables = array(
    '_forums',
    '_members',
    'grado',
    'secciones',
    'alumno',
    'reinscripcion',
    'alumnos_encargados',
    'catedratico',
    'areas_cursos',
    'cursos',
);
foreach ($truncate_tables as $row) {
    sql_query(sql_filter('TRUNCATE TABLE ??', $row));
}

//
// Parse and create grades and sections
//
$grade_order = 1;
$forum_order = 1;

$students = $build['alumnos'];
$pensum   = $build['pensum'];

//
// Create required users
//
$required_users = array(
    array(
        'user_type'     => USER_INACTIVE,
        'user_active'   => 0,
        'username'      => 'Anonymous',
        'user_password' => '4otatkan',
    ),
    array(
        'user_type'     => USER_FOUNDER,
        'user_active'   => 1,
        'username'      => 'Guillermo Azurdia',
        'user_password' => '4otatkan',
    )
);
foreach ($required_users as $row) {
    $required_id = create_user_account($row);
}

foreach ($students as $i => $row) {
    if (!isset($grade[$row['grade']])) {
        $sql_insert_grade = [
            'fecha_grado' => '',
            'nombre'      => $row['grade'],
            'seccion'     => '',
            'status'      => 'Alta',
            'grade_order' => $grade_order
        ];
        $grade_id = sql_create('grado', $sql_insert_grade);

        $grade[$row['grade']] = $grade_id;
        $grade_order++;
    }

    $grade_section = $row['grade'] . ' ' . $row['section'];

    if (!isset($section[$grade_section])) {
        $sql_insert_section = [
            'id_grado'       => $grade[$row['grade']],
            'nombre_seccion' => $row['section']
        ];
        $section_id = sql_create('secciones', $sql_insert_section);

        $section[$grade_section] = $section_id;
    }

    if (!isset($forums[$grade_section])) {
        $grade_section_words = ucwords(strtolower($grade_section));

        $insert_forum = array(
            'cat_id'              => $school_category,
            'forum_active'        => 1,
            'forum_alias'         => friendly($grade_section),
            'forum_name'          => $grade_section_words,
            'forum_desc'          => 'Foro para alumnos de ' . $grade_section_words,
            'forum_locked'        => 0,
            'forum_order'         => ($forum_order * 10),
            'forum_posts'         => 0,
            'forum_topics'        => 0,
            'forum_last_topic_id' => 0,
            'auth_view'           => 1,
            'auth_read'           => 1,
            'auth_post'           => 1,
            'auth_reply'          => 0,
            'auth_announce'       => 3,
            'auth_vote'           => 1,
            'auth_pollcreate'     => 1
        );
        $forum_id = sql_create('_forums', $insert_forum);
        $forum_order++;

        $forums[$grade_section] = $forum_id;
    }

    $row['firstname'] = $row['alumno'];
    $row['lastname']  = '';
    $row['code']      = '';

    if (!isset($row['encargado'])) {
        $row['encargado'] = '';
    }

    $user_full = trim($row['firstname'] . ' ' . $row['lastname']);
    $user_base = simple_alias($user_full);

    $user_password            = substr(md5(unique_id()), 0, 8);
    $user_password_supervisor = substr(md5(unique_id()), 0, 8);
    $user_gender              = isset($gender_select[$row['genero']]) ? $gender_select[$row['genero']] : 1;
    $user_gender_supervisor   = isset($gender_select[$row['genero_supervisor']]) ? $gender_select[$row['genero_supervisor']] : 1;
    $user_grade               = $row['grade'] . ' ' . $row['section'];

    $compile = (object) [
        'gender'     => $user_gender,
        'firstname'  => $row['firstname'],
        'lastname'   => $row['lastname'],
        'fullname'   => $user_full,
        'base'       => $user_base,
        'password'   => $user_password,
        'code'       => (int) $row['code'],
        'grade'      => $grade[$row['grade']],
        'section'    => $section[$user_grade],
        'grade_full' => $user_grade
    ];

    $insert_member = array(
        'username'      => $compile->fullname
        'user_password' => $user_password,
        'user_gender'   => $compile->gender
    );
    $member_id = create_user_account($insert_member);

    $insert_student = array(
        'id_member'        => $member_id,
        'carne'            => $compile->code,
        'codigo_alumno'    => '',
        'nombre_alumno'    => $compile->firstname,
        'apellido'         => $compile->lastname,
        'direccion'        => '',
        'orden'            => '',
        'registro'         => '',
        'telefono1'        => '',
        'edad'             => '',
        'sexo'             => $compile->gender,
        'email'            => '',
        'padre'            => '',
        'madre'            => '',
        'encargado'        => $row['encargado'],
        'profesion'        => '',
        'labora'           => '',
        'direccion_labora' => '',
        'email_encargado'  => '',
        'dpi'              => '',
        'extendida'        => '',
        'emergencia'       => '',
        'telefono2'        => '',
        'status'           => $student_status
    );
    $student_id = sql_create('alumno', $insert_student);

    $insert_subscribe = array(
        'id_alumno'               => $student_id,
        'carne'                   => $compile->code,
        'id_grado'                => $compile->grade,
        'id_seccion'              => $compile->section,
        'encargado_reinscripcion' => '',
        'telefonos'               => '',
        'status'                  => $student_status,
        'anio'                    => $student_year
    );
    $reinscription_id = sql_create('reinscripcion', $insert_subscribe);

    if (!empty($row['encargado'])) {
        $insert_supervisor = array(
            'username'      => $row['encargado'],
            'user_password' => $user_password_supervisor,
            'user_gender'   => $user_gender_supervisor
        ));
        $supervisor_id = create_user_account($insert_supervisor);

        $insert_supervisor_student = array(
            'supervisor' => $supervisor_id,
            'student'    => $member_id
        );
        $supervisor_related = sql_create('alumnos_encargados', $insert_supervisor_student);
    }

    $all['students'][] = $compile;
}

$docente      = array();
$cursos       = array();
$general_area = 0;

foreach ($pensum as $i => $row) {
    if (!isset($docente[$row['docente']])) {
        $password_docente = substr(md5(unique_id()), 0, 8);

        $insert_docente = array(
            'username'      => $row['docente'],
            'user_password' => $password_docente
        ));
        $docente_id = create_user_account($insert_docente);

        $docente[$row['docente']] = $docente_id;

        $insert_docente_student = array(
            'id_member'          => $docente_id,
            'registro'           => '',
            'nombre_catedratico' => $row['docente'],
            'apellido'           => '',
            'profesion'          => 'Docente',
            'email'              => '',
            'telefono'           => '',
            'direccion'          => '',
            'observacion'        => '',
            'status'             => 'Alta'
        );
        $docent_related = sql_create('catedratico', $insert_docente_student);
    }

    if (!$general_area) {
        $insert_general = array(
            'nombre_area'      => 'General',
            'observacion_area' => '',
            'rel_order'        => '1'
        );
        $general_area = sql_create('areas_cursos', $insert_general);
    }

    $grade_section = $row['grade'] . ' ' . $row['section'];

    $insert_course = array(
        'id_area'        => $general_area,
        'nombre_curso'   => $row['materia'],
        'capacidad'      => '',
        'fecha'          => '',
        'status'         => 'Alta',
        'id_grado'       => $grade[$row['grade']],
        'id_catedratico' => $docente[$row['docente']],
        'id_section'     => $section[$grade_section]
    );
    $cursos[$row['materia']] = sql_create('cursos', $insert_course);
}

dd($all);
