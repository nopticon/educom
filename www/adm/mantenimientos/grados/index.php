<?php

require_once('../../conexion.php');

$sql = 'SELECT *
	FROM grado';
$list = $db->sql_rowset($sql);

encabezado('Mantenimiento de Grado');

?>

<div class="ls">
	<ul class="options">
		<?php foreach ($list as $row) { ?>
		<li><a href="mod_grados.php?id_grado=<?php echo $row->id_grado; ?>"><?php echo $row->nombre; ?></a></li>
		<?php } ?>
	</ul>
</div>

<?php pie(); ?>