<?php
/**
 * @file
 * seo_pages.features.inc
 */

/**
 * Implements hook_node_info().
 */
function seo_pages_node_info() {
  $items = array(
    'seo_page' => array(
      'name' => t('SEO page'),
      'base' => 'node_content',
      'description' => t('SEO page'),
      'has_title' => '1',
      'title_label' => t('Title'),
      'help' => '',
    ),
  );
  return $items;
}
