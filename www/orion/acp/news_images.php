<?php

if (!defined('IN_APP')) exit;

class __news_images extends mac {
    public function __construct() {
        parent::__construct();

        $this->auth('all');
    }

    public function _home() {
        global $config, $user, $cache, $upload;

        if (_button()) {
            $news_id = request_var('news_id', 0);

            $sql = 'SELECT news_id
                FROM _news
                WHERE news_id = ?';
            if (!sql_field(sql_filter($sql, $news_id), 'news_id', 0)) {
                fatal_error();
            }

            $filepath_1 = $config->news_path;

            $f = $upload->process($filepath_1, 'add_image', 'jpg');

            if (!count($upload->error) && $f !== false) {
                foreach ($f as $row) {
                    $xa = $upload->resize($row, $filepath_1, $filepath_1, $news_id, array(100, 75), false, false, true);
                }

                redirect(s_link());
            }

            _style('error', array(
                'MESSAGE' => parse_error($upload->error))
            );
        }

        $sql = 'SELECT *
            FROM _news
            ORDER BY post_time DESC';
        $result = sql_rowset($sql);

        foreach ($result as $row) {
            _style('news_list', array(
                'NEWS_ID' => $row->news_id,
                'NEWS_TITLE' => $row->post_subject)
            );
        }

        return;
    }
}
