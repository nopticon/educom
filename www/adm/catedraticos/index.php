<?php

require_once('../conexion.php');

encabezado('Ingreso de Catedr&aacute;ticos');

$sql = 'SELECT *
	FROM catedratico
	ORDER BY id_catedratico DESC';
$catedraticos = $db->sql_rowset($sql);

$form = array(
	array(
		'nombre' => array(
			'type' => 'input',
			'value' => 'Nombre'
		),
		'apellido' => array(
			'type' => 'input',
			'value' => 'Apellido'
		),
		'profesion' => array(
			'type' => 'input',
			'value' => 'Profesi&oacute;n'
		),
		'email' => array(
			'type' => 'input',
			'value' => 'Email'
		),
		'telefonos' => array(
			'type' => 'input',
			'value' => 'Tel&eacute;fonos'
		),
		'direction' => array(
			'type' => 'input',
			'value' => 'Direcci&oacute;n'
		),
		'observacion' => array(
			'type' => 'text',
			'value' => 'Observaci&oacute;n'
		)
	)
);

?>

<form class="form-horizontal" action="cod_cate.php" method="post">
	<?php build($form); submit(); ?>
</form>

<h6>Catedr&aacute;ticos activos</h6>
<div id="list"></div>

<script type="text/javascript">
var target, grid;

<?php echo 'var kdata = ' . json_encode($catedraticos) . ';'; ?>

$(function() {
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
		columns: [
			{ field: "apellido", title: "Apellido", encoded: false },
			{ field: "nombre_catedratico", title: "Nombre", encoded: false },
			{ field: "profesion", title: 'Profesi&oacute;n', encoded: false, width: 150 },
			{ field: "telefono", title: 'Tel&eacute;fono', encoded: false },
			{ field: "email", title: 'Email', encoded: false }
		]
	});
});
</script>

<?php pie(); ?>