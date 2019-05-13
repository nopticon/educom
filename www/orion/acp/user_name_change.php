<?php

if (!defined('IN_APP')) exit;

class __user_name_change extends mac {
    public function __construct() {
        parent::__construct();

        $this->auth('founder');
    }

    public function _home() {
        global $config, $user, $cache;

        if (!_button()) {
            return false;
        }

        $username1 = request_var('username1', '');
        $username2 = request_var('username2', '');
        if (empty($username1) || empty($username2)) {
            fatal_error();
        }

        $username_base1 = get_username_base($username1);
        $username_base2 = get_username_base($username2);

        $sql = 'SELECT *
            FROM _members
            WHERE username_base = ?';
        if (!$userdata = sql_fieldrow(sql_filter($sql, $username_base1))) {
            _pre('El usuario no existe.', true);
        }

        $sql = 'SELECT *
            FROM _members
            WHERE username_base = ?';
        if ($void = sql_fieldrow(sql_filter($sql, $username_base2))) {
            _pre('El usuario ya existe.', true);
        }

        //
        $sql = 'UPDATE _members SET username = ?, username_base = ?
            WHERE user_id = ?';
        sql_query(sql_filter($sql, $username2, $username_base2, $userdata->user_id));

        $emailer = new emailer();

        $emailer->from('info');
        $emailer->use_template('username_change', $config->default_lang);
        $emailer->email_address($userdata->user_email);

        $emailer->assign_vars(array(
            'USERNAME' => $userdata->username,
            'NEW_USERNAME' => $username2,
            'U_USERNAME' => s_link('m', $username_base2))
        );
        $emailer->send();
        $emailer->reset();

        redirect(s_link('m', $username_base2));

        return;
    }
}
