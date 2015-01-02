<?php

require_once('../conexion.php');

encabezado('Ingreso de Tiempo para Examenes');

$sql = 'SELECT *
	FROM examenes
	ORDER BY id_examen';
$examenes = $db->sql_rowset($sql);

$form = array(
	'' => array(
		'examen' => array(
			'type' => 'text',
			'value' => 'Tiempo para Examen'
		),
		'observacion' => array(
			'type' => 'text',
			'value' => 'Observaci&oacute;n'
		),
		'status' => array(
			'type' => 'select',
			'show' => 'Status',
			'value' => array(
				'Alta' => 'Alta',
				'Baja' => 'Baja'
			)
		)
	)
);

?>

<form class="form-horizontal" action="cod_examenes.php" method="post">
	<?php build($form); submit(); ?>
</form>

<br />
<h6>Visualizaci&oacute;n de tiempos</h6>
<div id="list"></div>

<script type="text/javascript">
var target, grid;

<?php echo 'var kdata = ' . json_encode($examenes) . ';'; ?>

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
			{ field: "examen", title: "Tiempo de Examen" },
			{ field: "fecha_ingreso", title: "Fecha de ingreso" },
			{ field: "status", title: 'Status', encoded: false }
		]
	});
});
</script>

<?php pie(); ?>