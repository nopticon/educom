<?php

if (!defined('IN_APP')) exit;

class __user_ban extends mac {
    public function __construct() {
        parent::__construct();

        $this->auth('founder');
    }

    public function _home() {
        global $config, $user, $cache;

        if ($this->create()) {
            return;
        }

        return;
    }

    private function create() {
        $v = _request(array('username' => ''));

        if (_empty($v)) return;

        $v->username = get_username_base($v->username);

        $sql = 'SELECT *
            FROM _members
            WHERE username_base = ?';
        if (!$result = sql_fieldrow(sql_filter($sql, $v->username))) {
            return;
        }

        $sql = 'SELECT *
            FROM _banlist
            WHERE ban_userid = ?';
        if (!$ban = sql_fieldrow(sql_filter($sql, $result->user_id))) {
            $insert = array(
                'ban_userid' => $result->user_id
            );
            sql_insert('banlist', $insert);

            $sql = 'DELETE FROM _sessions
                WHERE session_user_id = ?';
            sql_query(sql_filter($sql, $result->user_id));

            echo 'El usuario ' . $result->username . ' fue bloqueado.';
        }

        return true;
    }
}
