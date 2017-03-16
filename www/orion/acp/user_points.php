<?php

if (!defined('IN_APP')) exit;

class __user_points extends mac {
    public function __construct() {
        parent::__construct();

        $this->auth('founder');
    }

    public function _home() {
        global $config, $user, $cache;

        $sql = 'SELECT user_id, username, username_base, user_points
            FROM _members
            WHERE user_points <> 0
            ORDER BY user_points DESC, username';
        $result = sql_rowset($sql);

        foreach ($result as $i => $row) {
            if (!$i) _style('members');

            _style('members.row', array(
                'BASE' => s_link('m', $row->username_base),
                'USERNAME' => $row->username,
                'POINTS' => $row->user_points)
            );
        }

        return;
    }
}
