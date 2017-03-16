<?php

if (!defined('IN_APP')) exit;

class __general_word_modify extends mac {
    public function __construct() {
        parent::__construct();

        $this->auth('founder');
    }

    public function _home() {
        global $config, $user, $cache;

        if (!_button()) {
            return false;
        }

        $orig = request_var('orig', '');
        $repl = request_var('repl', '');
        $total_1 = $total_2 = $total_3 = 0;

        $sql = "SELECT *
            FROM _forum_posts
            WHERE post_text LIKE '%??%'
            ORDER BY post_id";
        $result = sql_rowset(sql_filter($sql, $orig));

        foreach ($result as $row) {
            $row->post_text = str_replace($orig, $repl, $row->post_text);

            $sql = 'UPDATE _forum_posts SET post_text = ?
                WHERE post_id = ?';
            sql_query(sql_filter($sql, $row->post_text, $row->post_id));

            $total_1++;
        }

        //

        $sql = "SELECT *
            FROM _artists_posts
            WHERE post_text LIKE '%??%'
            ORDER BY post_id";
        $result = sql_rowset(sql_filter($sql, $orig));

        foreach ($result as $row) {
            $row->post_text = str_replace($orig, $repl, $row->post_text);

            $sql = 'UPDATE _artists_posts SET post_text = ?
                WHERE post_id = ?';
            sql_query(sql_filter($sql, $row->post_text, $row->post_id));

            $total_2++;
        }

        //

        $sql = "SELECT *
            FROM _members_posts
            WHERE post_text LIKE '%??%'
            ORDER BY post_id";
        $result = sql_rowset(sql_filter($sql, $orig));

        foreach ($result as $row) {
            $row->post_text = str_replace($orig, $repl, $row->post_text);

            $sql = 'UPDATE _members_posts SET post_text = ?
                WHERE post_id = ?';
            sql_query(sql_filter($sql,$row->post_text, $row->post_id));

            $total_3++;
        }

        return _pre('La frase <strong>' . $orig . '</strong> fue reemplazada por <strong>' . $repl . '</strong> en ' . $total_1 . ' f, ' . $total_2 . ' a, ' . $total_3 . ' m.', true);
    }
}
