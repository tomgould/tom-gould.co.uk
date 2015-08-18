<?php
/**
 * @file
 *    Drsh commands for the import jobs
 */

/**
 * Implements hook_drush_command().
 */
function json_import_drush_command() {
  $items['json-import-purge']      = array(
    'description' => dt('Purges all content'),
    'aliases'     => array('ji-purge', 'jip'),
  );
  $items['json-import-types'] = array(
    'description' => dt('Show the local content types'),
    'aliases'     => array('ji-types', 'jit'),
  );
  $items['json-import'] = array(
    'description' => dt('Import all iframe_page nodes'),
    'arguments'   => array(
      'importer_function' => dt('The name of the importer to use with the remote content type (without leading "json_import_import_")'),
      'remote_type' => dt('The remote machine name of the content type to import.'),
    ),
    'aliases'     => array('ji-import', 'jii'),
  );
  $items['json-import-delete'] = array(
    'description' => dt('Deletes all nodes of a given type'),
    'arguments'   => array(
      'type' => dt('The node type to delete.'),
    ),
    'aliases'     => array('ji-delete', 'jid'),
  );

  return $items;
}

/**
 * imports content
 */
function drush_json_import($importer_function = FALSE, $remote_type = FALSE) {
  if (!$importer_function || !$remote_type) {
    echo "Please supply the local and remote node types\n";
    return FALSE;
  }
  else {
    json_import_import_content($importer_function, $remote_type);
  }
}

/**
 * Purges all content
 */
function drush_json_import_purge() {
  json_import_purge_all_content();
}

/**
 * Show the local content types
 */
function drush_json_import_types() {
  $query  = db_select('node_type', 't');
  $query->fields('t', array('type'));
  $result = $query->orderBy('t.type')->execute();

  foreach ($result as $type) {
    echo $type->type . "\n";
  }
}

/**
 * Purges all content
 */
function drush_json_import_delete_birthdays($type = FALSE) {
  if (!$importer_function || !$remote_type) {
    echo "Please supply the local and remote node types\n";
    return FALSE;
  }
  else {
    $nodes = node_load_multiple(array(), array('type' => $type));
    if (!empty($nodes)) {
      foreach ($nodes as $nid => $node) {
        node_delete($nid);
        echo "Deleted: $nid ({$node->title})\n";
      }
    }
    else {
      echo "There were no nodes of type $type to delete.\n";
      return FALSE;
    }
  }
}
