<?php

require_once('../conexion.php');

encabezado('Faltas Acad&eacute;micas');

//
// Existing records
//
$list = get_faults_list();

$form1 = [
    'Ingresar faltas' => [
        'carne' => [
            'type'  => 'text',
            'value' => 'Carn&eacute;',
        ]
    ]
];

$form2 = [
    'Ver faltas' => [
        'carne1' => [
            'type'  => 'text',
            'value' => 'Carn&eacute;',
        ]
    ]
];

?>

<?php if (!empty($_SESSION['guardar'])) { unset($_SESSION['guardar']); ?>
    <div class="highlight a_center"><?php echo 'Falta guardada con &eacute;xito.'; ?></div>
<?php } ?>

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

<?php if ($list) { ?>
<br />
<div class="h"><h3>Historial de Faltas Acad&eacute;micas</h3></div>

<table class="table table-striped">
    <thead>
        <tr>
            <td>Carn&eacute;</td>
            <td>Apellido</td>
            <td>Nombre</td>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($list as $row) { ?>
        <tr>
            <td><a href="faltas2.php?carne=<?php echo $row->carne; ?>"><?php echo $row->carne; ?></a></td>
            <td><a href="faltas2.php?carne=<?php echo $row->carne; ?>"><?php echo $row->apellido; ?></a></td>
            <td><a href="faltas2.php?carne=<?php echo $row->carne; ?>"><?php echo $row->nombre_alumno; ?></a></td>
        </tr>
        <?php } ?>
    </tbody>
</table>
<?php } ?>

<?php pie(); ?>
