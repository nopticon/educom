<?php

if (!is_array($lang) || empty($lang)) {
    $lang = array();
}

$lang += array(
    'CHAT_NOW' => 'Chatear Ahora!',
    'CHAT_CREATE' => 'Crear Canal',
    'CHAT_CHANNEL_LISTING' => 'Lista de Canales',
    'CHAT_MANAGE_CHANNEL' => 'Administrar Canal',
    'CHAT_NOCH' => 'No hay canales',
    'RETURN_CHAT' => 'Regresar a la lista',
    'CHAT_CREATE_NAME' => 'Nombre del canal',
    'CHAT_CREATE_DESC' => 'Descripci&oacute;n',
    'CHAT_CREATE_CAT' => 'Categor&iacute;a',
    'CHAT_CREATE_AUTH' => 'Permitir acceso',
    'CHAT_CREATE_TYPE' => 'Tipo de canal',
    'CHAT_CREATE_SUBMIT' => 'Crear!',
    'CHAT_CREATE_EMPTY' => 'Debes completar el nombre del canal.',
    'CHAT_CREATE_INVALID_NAME' => 'El nombre del canal &uacute;nicamente puede contener letras, n&uacute;meros y <strong>-</strong>',
    'CHAT_CREATE_EMPTY_DESC' => 'Debes completar la descripci&oacute;n del canal.',
    'CHAT_ALREADY_CREATED' => 'El nombre del canal ya existe, escoge otro.',

    'CHAT_MEMBER_ENTERED' => '<strong>%s</strong> entr&oacute; al canal.',
    'CHAT_MEMBER_LOGOUT' => '<strong>%s</strong> sali&oacute; del canal.',
    'CHAT_MEMBER_TIMEOUT' => '<strong>%s</strong> sali&oacute; del canal por inactividad.',
    'CHAT_LOGOUT' => 'Salir del chat'
);

?>
