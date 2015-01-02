<?php

require_once('../../conexion.php');

encabezado('Listado de catedraticos con cursos');

$grado = $_REQUEST['grado'];
// $seccion = $_REQUEST['seccion'];

$sql = 'SELECT *
	FROM cursos c, catedratico g
	WHERE c.id_grado = ?
		AND g.id_catedratico = c.id_catedratico';
$list = $db->sql_rowset($db->__prepare($sql, $grado));

?>

<table width="100%">
	<thead>
		<td>Nombre de curso</td>
		<td>Nombre de catedr&aacute;tico</td>
	</thead>

	<?php foreach ($list as $row) { ?>
	<tr>
		<td width="291" class="tex2"><?php echo $row->nombre_curso; ?></td>
		<td width="341" class="tex2"><?php echo $row->nombre_catedratico; ?></td>
	</tr>
	<?php } ?>
</table>

<script type="text/javascript">
$(function() {
	$('#grado').change(function() {
		$.ajax({
			type: "POST",
			url: "../../actseccion.php",
			data: "grado=" + this.value,
			success: function(msg) {
				$('#seccion').html(msg);
			}
		});
	});
});
</script>

<?php pie(); ?>
