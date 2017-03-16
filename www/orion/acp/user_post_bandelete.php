<?php

if (!defined('IN_APP')) exit;

class __user_post_bandelete extends mac {
    public function __construct() {
        parent::__construct();

        $this->auth('founder');
    }

    public function _home() {
        global $config, $user, $cache;

        if (!_button()) {
            return false;
        }

        $msg_id = request_var('msg_id', 0);

        $sql = 'SELECT *
            FROM _members_posts
            WHERE post_id = ?';
        if (!$d = sql_fieldrow(sql_filter($sql, $msg_id))) {
            fatal_error();
        }

        $sql = 'DELETE FROM _members_posts
            WHERE post_id = ?';
        sql_query(sql_filter($sql, $msg_id));

        $sql = 'UPDATE _members SET userpage_posts = userpage_posts - 1
            WHERE user_id = ?';
        sql_query(sql_filter($sql, $d->userpage_id));

        if (_button('user')) {
            $sql = 'SELECT ban_id
                FROM _banlist
                WHERE ban_userid = ?';
            if (!$row = sql_fieldrow(sql_filter($sql, $d->poster_id))) {
                sql_insert('banlist', array('ban_userid' => $d->poster_id));
            }
        }

        if (_button('ip')) {
            $sql = 'SELECT ban_id
                FROM _banlist
                WHERE ban_ip = ?';
            if (!$row = sql_fieldrow(sql_filter($sql, $d->post_ip))) {
                $sql_insert = array(
                    'ban_ip' => $d->post_ip
                );
                sql_insert('banlist', $sql_insert);
            }
        }

        return _pre($d, true);
    }
}
