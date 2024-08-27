<?php
/**
 * Globaltags Plugin / Functions
 *
 * @package globaltags
 * @author Vladimir Sibirov & Dmitri Beliavski
 * @copyright (c) 2012-2024 seditio.by
 */

defined('COT_CODE') or die('Wrong URL');

 // define globals
define('SEDBY_GLOBALTAGS_REALM', '[SEDBY] Globaltags');

function sedby_globaltags($area = 'all', $items = 0, $order = 'tag', $rs = 'gt_open', $cache_name = '', $cache_ttl = '') {

  $enableCache = false;

  // Condition shortcut
  if (Cot::$cache && !empty($cache_name) && ((int)$cache_ttl > 0)) {
    $enableCache = true;
    $cache_name = str_replace(' ', '_', $cache_name);
  }

  if ($enableCache && Cot::$cache->db->exists($cache_name, SEDBY_GLOBALTAGS_REALM)) {
    $output = Cot::$cache->db->get($cache_name, SEDBY_GLOBALTAGS_REALM);
  } else {

    global $R, $tc_styles;

  	require_once cot_incfile('tags', 'plug');
  	require_once cot_incfile('globaltags', 'plug', 'resources');

    $sql_area = ($area == false) ? "all" : $area;
    $sql_order = ($order == false) ? "" : $order;
    $sql_limit = ($items == false) ? null : (int)$items;

  	$gt_array = cot_tag_cloud($sql_area, $sql_order, $sql_limit);

  	$output = Cot::$R[$rs];

    $jj = 0;

  	foreach ($gt_array as $tag => $cnt) {
  		$jj++;

  		$tag_title = Cot::$cfg['plugin']['tags']['title'] ? cot_tag_title($tag) : $tag;
  		$tag_url = Cot::$cfg['plugin']['tags']['translit'] ? cot_translit_encode($tag) : $tag;
      $tag_url = str_replace(' ', '-', $tag_url);
  		// $tl = ($lang != 'en' && $tag_u != $tag) ? 1 : null;
  		$tl = null;

  		foreach ($tc_styles as $key => $val) {
  			if ($cnt <= $key) {
  				$size = $val;
  				break;
  			}
  		}
  		$output .= cot_rc('gt_tag', array(
  			'url' => cot_url('plug', array('e' => 'tags', 'a' => $area, 't' => $tag_url, 'tl' => $tl)),
  			'title' => htmlspecialchars($tag_title),
  			'size' => $size
  		));
  	}

  	$output .= Cot::$R['gt_close'];
  	$output = ($jj > 0) ? $output : Cot::$L['None'];

    if (($jj > 1) && $enableCache) {
			Cot::$cache->db->store($cache_name, $output, SEDBY_GLOBALTAGS_REALM, $cache_ttl);
		}
  }
  return $output;
}
