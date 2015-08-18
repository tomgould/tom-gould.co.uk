<?php

/**
 * @file
 *     Import functions for the import jobs
 *
 * The tom-gould.co.uk site has the following content types
 *
  about_us
  block_content
  blog
  demo
  gallery
  news
  page
  product
  seo_page
  service
  slider
  video
  webform
 *
 *
 *
 */

/**
 * Creates a advert node
 *
 * @param object $source
 */
function json_import_import_demo($source) {

  global $user, $language;
  $user = user_load(1);

  // Creates an empty node of the given type
  $node = json_import_get_empty_node('demo');

  // imports the title and status
  json_import_defaults($node, $source);

  // Maps Drupal 6 terms to the node if the source site is D6
  json_import_term_mapper($node, $source, 'faq_categories', 'field_faq_categories');

  // Puts the body on the node
  $node->body[$language->language][0] = json_import_body_value($source);

  // maps a field value to the node
  $node->field_advert_pages[LANGUAGE_NONE][0]['value'] = json_import_field_value(
    $source->field_advert_pages
  );

  // Imports a file field
  json_import_files($node, $source, 'field_character_main_image', 'field_characters_main_image');

  // Maps a D6 link field to the node
  $node->field_show_promo_link[LANGUAGE_NONE] = json_import_link_field_value($source->field_show_promo_link);

  // Maps an image to the node fromnm a URL
  json_import_attach_file_from_url($node, 'field_song_thumbnail', $source->field_bc_thumbnail_url[0]->value);

  // Saves the node
  node_save($node);

  // For the logging
  $node->remote_nid = $source->nid;

  return $node;
}
