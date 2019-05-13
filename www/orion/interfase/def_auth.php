<?php

if (!defined('IN_APP')) exit;

if (defined('IN_ADMIN')) {
    // all the presets
    //              View      Read      Post      Reply     Edit     Delete   Sticky    Announce    Vote      Poll
    $simple_auth_ary = array(
        0  => array(AUTH_ALL, AUTH_ALL, AUTH_ALL, AUTH_ALL, AUTH_REG, AUTH_REG, AUTH_MOD, AUTH_MOD, AUTH_REG, AUTH_REG),
        1  => array(AUTH_ALL, AUTH_ALL, AUTH_REG, AUTH_REG, AUTH_REG, AUTH_REG, AUTH_MOD, AUTH_MOD, AUTH_REG, AUTH_REG),
        2  => array(AUTH_REG, AUTH_REG, AUTH_REG, AUTH_REG, AUTH_REG, AUTH_REG, AUTH_MOD, AUTH_MOD, AUTH_REG, AUTH_REG),
        3  => array(AUTH_ALL, AUTH_ACL, AUTH_ACL, AUTH_ACL, AUTH_ACL, AUTH_ACL, AUTH_ACL, AUTH_MOD, AUTH_ACL, AUTH_ACL),
        4  => array(AUTH_ACL, AUTH_ACL, AUTH_ACL, AUTH_ACL, AUTH_ACL, AUTH_ACL, AUTH_ACL, AUTH_MOD, AUTH_ACL, AUTH_ACL),
        5  => array(AUTH_ALL, AUTH_MOD, AUTH_MOD, AUTH_MOD, AUTH_MOD, AUTH_MOD, AUTH_MOD, AUTH_MOD, AUTH_MOD, AUTH_MOD),
        6  => array(AUTH_MOD, AUTH_MOD, AUTH_MOD, AUTH_MOD, AUTH_MOD, AUTH_MOD, AUTH_MOD, AUTH_MOD, AUTH_MOD, AUTH_MOD),
    );

    $simple_auth_types = array($lang['Public'], $lang['Registered'], $lang['Registered'] . ' [' . $lang['Hidden'] . ']', $lang['Private'], $lang['Private'] . ' [' . $lang['Hidden'] . ']', $lang['Moderators'], $lang['Moderators'] . ' [' . $lang['Hidden'] . ']');
}

// data description
$field_names = array(
    'auth_view' => $lang['View'],
    'auth_read' => $lang['Read'],
    'auth_post' => $lang['Post'],
    'auth_reply' => $lang['Reply'],
    'auth_edit' => $lang['Edit'],
    'auth_delete' => $lang['Delete'],
    'auth_sticky' => $lang['Sticky'],
    'auth_announce' => $lang['Announce'],
    'auth_vote' => $lang['Vote'],
    'auth_pollcreate' => $lang['Pollcreate'],
);

// value description
$forum_auth_levels = array('ALL', 'REG', 'PRIVATE', 'MOD', 'ADMIN');
$forum_auth_const = array(AUTH_ALL, AUTH_REG, AUTH_ACL, AUTH_MOD, AUTH_ADMIN);
