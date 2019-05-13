<?php

if (!defined('IN_APP')) exit;

header('Content-type: text/html; charset=utf-8');

require_once(ROOT . 'interfase/facebook.php');

//------------------------------------------------------------

$d = decode_ht('.htf_');

$fbd = new stdClass();
foreach (w('page appid secret token') as $i => $k) {
    $fbd->$k = _decode($d[$i]);
}
unset($d);

$facebook_page = '48722647107';

$facebook = new Facebook(array(
    'appId'  => $fbd->appid,
    'secret' => $fbd->secret)
);

foreach (w('at') as $i => $k) {
    $htk[$k] = _decode($d[$i]);
}

$attr = array(
    'access_token' => $fbd->token
);

$likes = (object) $facebook->api($facebook_page);

if (isset($likes->likes)) {
    $cache->save('fb_likes', $likes->likes);
}

$wall = $facebook->api($fbd->page . '/feed/', $attr);

$wall_feed = array_reverse($wall['data']);
$from_time = 1321336800;

$official_posts = w();
foreach ($wall_feed as $row) {
    if ($row['from']['id'] != $facebook_page) {
        continue;
    }

    $created_time = strtotime($row['created_time']);

    if ($created_time < $from_time) {
        continue;
    }

    $sql = 'SELECT *
        FROM _news
        WHERE news_fbid = ?';
    if (sql_fieldrow(sql_filter($sql, $row['id']))) {
        continue;
    }

    if (isset($row['picture'])) {
        if (strpos($row['picture'], 'safe_image') !== false) {
            $row['picture'] = explode('&', $row['picture']);

            foreach ($row['picture'] as $picture_row) {
                if (($url_pos = strpos($picture_row, 'url=')) !== false) {
                    $row['picture'] = urldecode(substr($picture_row, 4));
                    break;
                }
            }
        }

        $f = $upload->remote($config['news_path'], array($row['picture']), 'jpg png');

        foreach ($f as $row) {
            $mini = $upload->resize($row, $config['news_path'], $config['news_path'], 1, array(100, 75), false, false, true);
        }

        echo '<pre>';
        print_r($upload);
        print_r($f);
        echo '</pre>';
        exit;

    }

    /*$sql_insert = array(
        '' => '',
    );
    sql_insert('news', $sql_insert);
     *
     * rk user (1433)
    */



    //echo $row['created_time'] . ' ---- ' . $created_time . '<br>';

    $official_posts[] = $row;
}

//_pre($likes);
_pre($official_posts, true);
