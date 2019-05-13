<?php

if (!defined('IN_APP')) exit;

class __user_password extends mac {
    public function __construct() {
        parent::__construct();

        $this->auth('founder');
    }

    public function _home() {
        global $config, $user, $cache;

        if (!_button()) {
            return false;
        }

        $username = request_var('username', '');
        $password = request_var('password', '');

        $username = get_username_base($username);

        $sql = 'SELECT user_id, username
            FROM _members
            WHERE username_base = ?';
        if (!$userdata = sql_fieldrow(sql_filter($sql, $username))) {
            fatal_error();
        }

        $sql = 'UPDATE _members SET user_password = ?
            WHERE user_id = ?';
        sql_query(sql_filter($sql, HashPassword($password), $userdata->user_id));

        return _pre('La contrase&ntilde;a de ' . $userdata->username . ' fue actualizada.', true);
    }
}
