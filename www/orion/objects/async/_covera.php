<?php

if (!defined('IN_APP')) exit;

require_once(ROOT . 'interfase/artists.php');

$artists = new _artists();
$artists->get_data();

$a_ary = array();
for ($i = 0; $i < 4; $i++) {
    $_a = array_rand($artists->adata);
    if (!$artists->adata[$_a]['images'] || isset($a_ary[$_a])) {
        $i--;
        continue;
    }
    $a_ary[$_a] = $artists->adata[$_a];
}

if (sizeof($a_ary))
{
    $sql = 'SELECT *
        FROM _artists_images
        WHERE ub IN (??)
        ORDER BY RAND()';
    $result = sql_rowset(sql_filter($sql, implode(',', array_keys($a_ary))));

    $random_images = array();
    foreach ($result as $row) {
        if (!isset($random_images[$row['ub']])) {
            $random_images[$row['ub']] = $row['image'];
        }
    }

    $return_string = '<table width="100%" class="t-collapse"><tr>';

    $i = 0;
    foreach ($a_ary as $ub => $data) {
        $url = s_link('a', $data['subdomain']);
        $return_string .= '<td class="' . (($i % 2) ? 'dark-color' : '') . ' pad6"><a href="' . $url . '">' . $data['name'] . '</a><br/ ><small>' . (($data['local']) ? 'Guatemala' : $data['location']) . '</small><br /><div class="sep2-top"><a href="' . $url . '"><img class="box" src="/data/artists/' . $ub . '/thumbnails/' . $random_images[$ub] . '.jpg" title="' . $data['genre'] . '" /></a></div></td>';
        $i++;
    }

    $return_string .= '</tr></table>';
    echo rawurlencode($return_string);
}

?>
