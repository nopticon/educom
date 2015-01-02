<?php

require_once('../conexion.php');

encabezado('Faltas Acad&eacute;micas');

$form1 = array(
	'Ingresar faltas' => array(
		'carne' => array(
			'type' => 'text',
			'value' => 'Carn&eacute;',
		)
	)
);

$form2 = array(
	'Ver faltas' => array(
		'carne1' => array(
			'type' => 'text',
			'value' => 'Carn&eacute;',
		)
	)
);

?>

<?php

if (!empty($_SESSION['guardar'])) {
	unset($_SESSION['guardar']);
?>
	<div class="highlight a_center">
		<img src="../images/falta1.png" />
		<?php echo 'Falta guardada con &Eacute;xito.'; ?>
	</div>
<?php } ?>

<div class="a_center"><img src="/public/images/list.png" /></div>
<div class="a_center"><a href="faltas_alumnos.php">Ver &uacute;ltimas faltas</a></div>

<br />
<table width="100%">
	<tr>
		<td width="40%">
			<form class="form-horizontal" action="faltas.php" method="get">
				<?php build($form1); submit(); ?>
			</form>
		</td>
		<td width="20%">&nbsp;</td>
		<td width="50%">
			<form class="form-horizontal" action="faltas2.php" method="get">
				<?php build($form2); submit(); ?>
			</form>
		</td>
	</tr>
</table>

<?php pie(); ?>