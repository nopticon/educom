<?php

require_once('../../conexion.php');

$id_curso = $_REQUEST['id_curso'];
$id_grado = $_REQUEST['grado'];

encabezado('Modificaci&oacute;n de curso');

$sql = 'SELECT *
	FROM cursos
	WHERE id_curso = ?';
$curso = $db->sql_fieldrow($db->__prepare($sql, $id_curso));

$form = array(
	array(
		'curso' => array(
			'type' => 'text',
			'value' => 'Curso',
			'default' => $curso->nombre_curso
		),
		'capacidad' => array(
			'type' => 'text',
			'value' => 'Capacidad',
			'default' => $curso->capacidad
		),
		'status' => array(
			'type' => 'select',
			'show' => 'Status',
			'value' => array(
				'Alta' => 'Alta',
				'Baja' => 'Baja'
			),
			'default' => $curso->status
		),
	)
);

?>

<form class="form-horizontal" action="../cod_mant/cod_man_cursos.php" method="post">
	<input name="id_curso" type="hidden" value="<?php echo $id_curso; ?>" />
	<input name="id_grado" type="hidden" value="<?php echo $id_grado; ?>" />

	<?php build($form); submit(); ?>
</form>

<?php pie(); ?>