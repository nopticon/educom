<?php

if (!defined('IN_APP')) exit;

class __unread_topics_delete extends mac {
    public function __construct() {
        parent::__construct();

        $this->auth('founder');
    }

    public function _home() {
        global $config, $user, $cache, $comments;

        $sql = 'SELECT *
            FROM _members_unread
            WHERE element = ?
            GROUP BY item';
        $result = sql_rowset(sql_filter($sql, UH_T));

        foreach ($result as $row) {
            $sql2 = 'SELECT topic_id
                FROM _forum_topics
                WHERE topic_id = ?';
            if (!sql_field(sql_filter($sql, $row->item), 'topic_id', 0)) {
                // TODO: Today save
                // $user->delete_all_unread(UH_T, $row->item);
            }
        }

        _pre('Deleted', true);

        return;
    }
}
