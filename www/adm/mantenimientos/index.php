<?php

require_once('../conexion.php');

encabezado('Mantenimiento del Sistema');

?>

<ul class="options">
	<li><a href="alumnos/index.php"><img src="/public/images/iconos/59.ico" width="20" valign="middle" /> Alumnos</a></li>
	<li><a href="grados/index.php"><img src="/public/images/iconos/106.ico" width="20" valign="middle" /> Grados</a></li>
	<li><a href="cursos/index.php"><img src="/public/images/iconos/166.ico" width="20" valign="middle" /> Cursos</a></li>
	<li><a href="catedraticos/index.php"><img src="/public/images/iconos/209.ico" width="20" valign="middle" /> Catedr&aacute;ticos</a></li>
	<li><a href="examen/index.php"><img src="/public/images/iconos/292.ico" width="20" valign="middle" /> Unidades</a></li>
</ul>

<?php pie(); ?>