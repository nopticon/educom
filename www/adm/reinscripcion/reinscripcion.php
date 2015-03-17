<?php

require_once('../conexion.php');

$carne = request_var('carne', '');

$sql = 'SELECT *
	FROM alumno a, reinscripcion r, grado g
	WHERE r.id_alumno = a.id_alumno
		AND g.id_grado = r.id_grado
		AND a.carne = ?
	ORDER BY a.id_alumno DESC';
$row = $db->sql_fieldrow(sql_filter($sql, $carne));

if (!$row) {
	redirect('/adm/reinscripcion/');
}

encabezado('Reinscripci&oacute;n del Alumno');

//
// Database
//
$sql = 'SELECT id_grado
	FROM reinscripcion
	WHERE carne = ?
	ORDER BY id_grado DESC
	LIMIT 1';
$last_grade = $db->sql_field(sql_filter($sql, $carne), 'id_grado');

$sql = 'SELECT *
	FROM grado
	WHERE status = ?
		AND id_grado > ?';
if (!$rowset_grado = $db->sql_rowset(sql_filter($sql, 'Alta', $last_grade))) {
	$rowset_grado = array();
}

$primer_seccion = (isset($rowset_grado[0]->id_grado)) ? $rowset_grado[0]->id_grado : 0;

$sql = 'SELECT *
	FROM secciones
	WHERE id_grado = ?';
if (!$rowset_seccion = $db->sql_rowset(sql_filter($sql, $primer_seccion))) {
	$rowset_seccion = array();
}

//
// Historial de grados
//
$sql = 'SELECT *
	FROM reinscripcion r, alumno a, grado g, secciones s
	WHERE r.id_alumno = a.id_alumno
		AND r.id_grado = g.id_grado
		AND s.id_seccion = r.id_seccion
		AND s.id_grado = g.id_grado
		AND r.carne = ?';
$rowset_historia = $db->sql_rowset(sql_filter($sql, $carne));

$form = array(
	'Datos de Alumno' => array(
		'carne' => array(
			'type' => 'text',
			'value' => 'Carn&eacute;',
			'default' => $row->carne
		),
		'codigo_alumno' => array(
			'type' => 'text',
			'value' => 'C&oacute;digo de alumno',
			'default' => $row->codigo_alumno
		),
		'nombre' => array(
			'type' => 'text',
			'value' => 'Nombre',
			'default' => $row->nombre_alumno
		),
		'apellido' => array(
			'type' => 'text',
			'value' => 'Apellido',
			'default' => $row->apellido
		),
	),
	'Datos de Padres' => array(
		'padre' => array(
			'type' => 'text',
			'value' => 'Padre',
			'default' => $row->padre
		),
		'madre' => array(
			'type' => 'text',
			'value' => 'Madre',
			'default' => $row->madre
		),
	),
	'Datos de Encargado ' . date('Y') => array(
		'Encargado' => array(
			'type' => 'text',
			'value' => 'Encargado'
		),
		'telefonos' => array(
			'type' => 'text',
			'value' => 'Tel&eacute;fonos'
		),
		'observacion' => array(
			'type' => 'text',
			'value' => 'Observaciones'
		),
	),
	'Grado a Cursar' => array(
		'grado' => array(
			'type' => 'select',
			'show' => 'Grado',
			'value' => array()
		),
		'seccion' => array(
			'type' => 'select',
			'show' => 'Secci&oacute;n',
			'value' => array()
		),
	),
);

foreach ($rowset_grado as $row) {
	$form['Grado a Cursar']['grado']['value'][$row->id_grado] = $row->nombre;
}

foreach ($rowset_seccion as $row) {
	$form['Grado a Cursar']['seccion']['value'][$row->id_seccion] = $row->nombre_seccion;
}

if (!count($form['Grado a Cursar']['grado']['value'])) {
	$form['Grado a Cursar'] = array();
}

?>

<?php if (count($form['Grado a Cursar'])) { ?>
<form class="form-horizontal" action="cod_reinscripcion.php" method="post">
	<input name="id_alumno" type="hidden" id="id_alumno" value="<?php echo $row->id_alumno; ?>" />
	<input name="carnet" type="hidden" id="carnet" value="<?php echo $row->carne; ?>" />

	<?php build($form); submit(); ?>
</form>
<?php } else { ?>
El alumno ha cursado todos los grados disponibles.<br /><br />
<?php } ?>

<div class="h"><h3>Historial de Grados</h3></div><br />
<div id="list"></div>

<script language="JavaScript" type="text/javascript">
var target, grid;

$(function() {
	<?php echo 'var kdata = ' . json_encode($rowset_historia) . ';'; ?>

	target = $('#list');
	grid = target.kendoGrid({
		dataSource: {
			data: kdata,
			pageSize: 10
		},
		sortable: true,
		pageable: {
			pageSizes: true
		},
		columns: [ {
			field: "anio",
			title: "A&ntilde;o",
			width: 125,
		} , {
			field: "encargado_reinscripcion",
			title: 'Encargado',
			encoded: false
		} , {
			field: "nombre",
			title: "Grado",
			width: 150
		} , {
			field: "nombre_seccion",
			title: 'Seccion',
			width: 100
		} , {
			field: 'fecha_reinscripcion',
			title: 'Fecha',
			width: 125
		} ]
	});

});
</script>

<?php pie(); ?>