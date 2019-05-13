<?php

if (!defined('IN_APP')) exit;

class __user_sig_delete extends mac {
    public function __construct() {
        parent::__construct();

        $this->auth('mod');
    }

    public function _home() {
        global $config, $user, $cache;

        if (!_button()) {
            return false;
        }

        $username = request_var('username', '');
        $username = get_username_base($username);

        $sql = 'SELECT user_id, username
            FROM _members
            WHERE username_base = ?';
        if (!$userdata = sql_fieldrow(sql_filter($sql, $username))) {
            fatal_error();
        }

        $sql = 'UPDATE _members SET user_sig = ?
            WHERE user_id = ?';
        sql_query(sql_filter($sql, '', $userdata->user_id));

        return _pre('La firma de ' . $userdata->username . ' ha sido borrada.', true);
    }
}
