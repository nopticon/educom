<?php

if (!defined('IN_APP')) exit;

class __news_modify extends mac {
    public function __construct() {
        parent::__construct();

        $this->auth('founder');
    }

    public function _home() {
        global $config, $user, $cache, $comments;

        $news_id = request_var('news_id', 0);

        if ($news_id) {
            $sql = 'SELECT *
                FROM _news
                WHERE news_id = ?';
            if (!$news_data = sql_fieldrow(sql_filter($sql, $news_id))) {
                fatal_error();
            }

            if (_button('submit2')) {
                $post_subject = request_var('post_subject', '');
                $post_desc = request_var('post_desc', '', true);
                $post_message = request_var('post_text', '', true);

                $post_message = $comments->prepare($post_message);
                $post_desc = $comments->prepare($post_desc);

                //
                $sql = 'UPDATE _news SET post_subject = ?, post_desc = ?, post_text = ?
                    WHERE news_id = ?';
                sql_query(sql_filter($sql, $post_subject, $post_desc, $post_message, $news_id));

                $cache->delete('news');

                redirect(s_link('news', $news_data->news_alias));
            }

            _style('edit', array(
                'ID' => $news_data->news_id,
                'SUBJECT' => $news_data->post_subject,
                'DESC' => $news_data->post_desc,
                'TEXT' => $news_data->post_text)
            );

            return;
        }

        $sql = 'SELECT *
            FROM _news n, _news_cat c
            WHERE c.cat_id = n.cat_id
            ORDER BY c.cat_order, n.news_alias';
        $list = sql_rowset($sql);

        $cat = [];
        foreach ($list as $row) {
            $cat[$row->cat_url][] = $row;
        }

        foreach ($cat as $alias => $list) {
            _style('category', [
                'url' => $list[0]->cat_url,
                'name' => $list[0]->cat_name
            ]);

            foreach ($list as $row) {
                $row->url = s_link('acp', [
                    'news_modify',
                    'news_id' => $row->news_id
                ]);

                _style('category.row', $row);
            }
        }

        return;
    }
}
