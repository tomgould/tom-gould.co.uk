<?php

/**
 * Implements hook_drush_command().
 */
function save_nodes_drush_command() {
  $items['save_nodes'] = array(
    'description' => dt('Saves all the nodes'),
  );
  return $items;
}

/**
 * Saves all the nodes
 */
function drush_save_nodes() {
  save_nodes_save_all_the_nodes();
}