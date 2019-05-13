<?php

require_once('../../conexion.php');

encabezado('Listado de catedraticos con cursos');

$grado = request_var('grado', 0);

$sql = 'SELECT *
    FROM cursos c, catedratico g
    WHERE c.id_grado = ?
        AND g.id_member = c.id_catedratico';
$list = $db->sql_rowset(sql_filter($sql, $grado));

?>

<table class="table table-striped">
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

<?php pie();
