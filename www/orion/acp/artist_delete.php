<?php

if (!defined('IN_APP')) exit;

class __artist_delete extends mac {
    public function __construct() {
        parent::__construct();

        return $this->auth('founder');
    }

    public function _home() {
        $this->__home();

        $this->warning_show();

        $sql = 'SELECT ub, name
            FROM _artists
            ORDER BY name';
        return _rowset_style($sql, 'artists');
    }

    private function __home() {
        global $config, $cache;

        if (!_button()) {
            return;
        }

        $name = request_var('name', '');

        $sql = 'SELECT *
            FROM _artists
            WHERE name = ?';
        if (!$a_data = sql_fieldrow(sql_filter($sql, $name))) {
            fatal_error();
        }

        $sql = 'SELECT m.user_id, m.user_email
            FROM _artists_auth a, _members m
            WHERE a.ub = ?
                AND a.user_id = m.user_id';
        $result = sql_rowset(sql_filter($sql, $a_data->ub));

        $mods = w();
        foreach ($result as $row) {
            $mods[] = $row->user_id;
        }

        if (count($mods)) {
            foreach ($mods as $i => $each) {
                $sql = 'SELECT COUNT(user_id) AS total
                    FROM _artists_auth
                    WHERE user_id = ?';
                $total = sql_field(sql_filter($sql, $each), 'total', 0);

                if ($total > 1) {
                    unset($mods[$i]);
                }
            }
        }

        if (count($mods)) {
            $sql = 'UPDATE _members SET user_auth_control = 0
                WHERE user_id IN (??)';
            $d_sql[] = sql_filter($sql, _implode(',', $mods));
        }

        $ary_sql = array(
            'DELETE FROM _artists WHERE ub = ?',
            'DELETE FROM _artists_auth WHERE ub = ?',
            'DELETE FROM _artists_fav WHERE ub = ?',
            'DELETE FROM _artists_images WHERE ub = ?',
            'DELETE FROM _artists_log WHERE ub = ?',
            'DELETE FROM _artists_lyrics WHERE ub = ?',
            'DELETE FROM _artists_posts WHERE post_ub = ?',
            'DELETE FROM _artists_stats WHERE ub = ?',
            'DELETE FROM _artists_viewers WHERE ub = ?',
            'DELETE FROM _artists_voters WHERE ub = ?',
            'DELETE FROM _artists_votes WHERE ub = ?',
            'DELETE FROM _forum_topics WHERE topic_ub = ?',
            'DELETE FROM _dl WHERE ub = ?'
        );
        $d_sql = sql_filter($ary_sql, $a_data->ub);

        $sql = 'SELECT topic_id
            FROM _forum_topics
            WHERE topic_ub = ?';
        if ($topics = sql_rowset(sql_filter($sql, $a_data->ub), false, 'topic_id')) {
            $d_sql[] = sql_filter('DELETE FROM _forum_posts
                WHERE topic_id IN (??)', _implode(',', $topics));
        }

        $sql = 'SELECT id
            FROM _dl
            WHERE ub = ?';
        if ($downloads = sql_rowset(sql_filter($sql, $a_data->ub), false, 'id')) {
            $ary_sql = array(
                'DELETE FROM _dl_fav WHERE dl_id IN (??)',
                'DELETE FROM _dl_posts WHERE download_id IN (??)',
                'DELETE FROM _dl_vote WHERE ud IN (??)',
                'DELETE FROM _dl_voters WHERE ud IN (??)'
            );
            $d_sql = array_merge($d_sql, sql_filter($ary_sql, _implode(',', $downloads)));
        }

        if (!_rm($config->artists_path . $a_data->ub)) {
            $this->warning('Error al eliminar directorio de artista.');
        }

        sql_query($d_sql);

        // Cache
        $cache->delete('artist_list a_last_images');

        redirect(s_link('a'));
    }
}
