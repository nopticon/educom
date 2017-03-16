<?php

if (!defined('IN_APP')) exit;

// require_once(ROOT . 'interfase/artists.php');

$artists = new artists();

$sql = 'SELECT *
    FROM _artists
    WHERE images > 0
    ORDER BY RAND()
    LIMIT 12';
if (!$selected_artists = sql_rowset($sql, 'ub')) {
    return;
}

$sql = 'SELECT *
    FROM _artists_images
    WHERE ub IN (??)
    ORDER BY RAND()';
$result = sql_rowset(sql_filter($sql, implode(',', array_keys($selected_artists))));

$random_images = w();
foreach ($result as $row) {
    if (!isset($random_images[$row->ub])) {
        $random_images[$row->ub] = $row->image;
    }
}

foreach ($selected_artists as $ub => $data) {
    _style('row', array(
        'NAME' => $data->name,
        'IMAGE' => $config->artists_url . $ub . '/thumbnails/' . $random_images[$ub] . '.jpg',
        'URL' => s_link('a', $data->subdomain),
        'LOCATION' => ($data->local) ? 'Guatemala' : $data->location,
        'GENRE' => $data->genre)
    );
}

$template->set_filenames(array(
    'body' => 'artists.thumbs.htm')
);
$template->pparse('body');

sql_close();
exit;
