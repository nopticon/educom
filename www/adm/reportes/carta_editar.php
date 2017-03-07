<?php

require('../conexion.php');

encabezado('Carta para editar notas');

?>

<table width="100%">
    <tr>
        <td>
            <p class="a_right">Fecha: <?php echo str_repeat('_', 30); ?></p>

            <p>
                Se&ntilde;or Director<br />
                <?php echo lang('SCHOOL_NAME'); ?><br />
                Presente.
            </p>

            <br />
            <p>
                Por este medio me dirijo a usted, en mi calidad de Catedr&aacute;tico de la asignatura de:
                <?php echo str_repeat('_', 41); ?>
                <br /><br />
                de: <?php echo str_repeat('_', 30); ?> Grado, Secci&oacute;n: <?php echo str_repeat('_', 6); ?>, para la correcci&oacute;n
                de la calificaci&oacute;n del alumno(a):
                <br /><br />
                <?php echo str_repeat('_', 90); ?>
                <br /><br />
                en vista que la nota actual es de: _________ puntos, siendo la nota correcta: _________ puntos;
                y la cual justifico por motivo de:
                <br /><br />
                <?php echo str_repeat('_', 135); ?>
                <br /><br />
                <?php echo str_repeat('_', 135); ?>
                <br /><br />
                <?php echo str_repeat('_', 135); ?>
            </p>

            <br />
            <p class="a_center">Atentamente,</p>

            <p class="a_center">(f) <?php echo str_repeat('_', 30); ?></p>

            <p class="a_center">
                <?php echo str_repeat('_', 30); ?><br />
                Nombre Catedr&aacute;tico
            </p>

            <br />
            <p><?php echo str_repeat('_', 30); ?></p>
            <p>Autorizado</p>
        </td>
    </tr>
</table>

<?php pie();
