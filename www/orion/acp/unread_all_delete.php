<?php

if (!defined('IN_APP')) exit;

class __unread_all_delete extends mac {
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
        if (empty($username)) {
            fatal_error();
        }

        $username = get_username_base($username);

        $sql = 'SELECT user_id
            FROM _members
            WHERE username_base = ?';
        if (!$row = sql_fieldrow(sql_filter($sql, $username))) {
            fatal_error();
        }

        $sql = 'DELETE FROM _members_unread
            WHERE user_id = ?
                AND element <> ?';
        sql_query(sql_filter($sql, $row->user_id, 16));

        return _pre('Deleted', true);
    }
}
