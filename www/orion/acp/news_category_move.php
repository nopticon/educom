<?php

if (!defined('IN_APP')) exit;

class __news_category_move extends mac {
    public function __construct() {
        parent::__construct();

        $this->auth('colab');
    }

    public function _home() {
        global $config, $cache, $user;

        if (!_button()) {
            $sql = 'SELECT cat_id, cat_name
                FROM _news_cat
                ORDER BY cat_id';
            $result = sql_rowset($sql);

            foreach ($result as $i => $row) {
                if (!$i) _style('categories');

                _style('categories.row', array(
                    'CAT_ID' => $row->cat_id,
                    'CAT_NAME' => $row->cat_name)
                );
            }

            return false;
        }

        $t = request_var('news_id', 0);
        $f = request_var('cat_id', 0);

        if (!$f || !$t) {
            fatal_error();
        }

        //
        $sql = 'SELECT *
            FROM _news
            WHERE news_id = ?';
        if (!$tdata = sql_fieldrow(sql_filter($sql, $t))) {
            fatal_error();
        }

        //
        $sql = 'SELECT *
            FROM _news_cat
            WHERE cat_id = ?';
        if (!$fdata = sql_fieldrow(sql_filter($sql, $f))) {
            fatal_error();
        }

        //
        $sql = 'UPDATE _news SET cat_id = ?
            WHERE news_id = ?';
        sql_query(sql_filter($sql, $f, $t));

        return redirect(s_link('news', $t));
    }
}
