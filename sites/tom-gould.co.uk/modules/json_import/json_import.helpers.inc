<?php
/**
 * @file
 *     Helper functions for the import jobs
 */

/**
 * Debug function
 *
 * @param array $var
 * @param bool $die
 */
function json_import_debug($var, $die = FALSE) {

  print '<pre style="border:1px solid black; padding: 10px; margin:25px">' . var_export($var, TRUE) . '</pre>';

  if ($die) {
    die;
  }
}

/**
 * Prepares and empty node object
 *
 * @param string $type
 *    The machine name of the node to prepare
 *
 * @return \stdClass
 */
function json_import_get_empty_node($type) {
  global $language;

  $node           = new stdClass();
  $node->uid      = 1;
  $node->name     = 'admin';
  $node->status   = 0;
  $node->promote  = 1;
  $node->language = $language->language;

  $node->type = $type;

  node_object_prepare($node);

  return $node;
}

/**
 * Truncates a table
 *
 * @param string $table
 */
function json_import_truncate($table) {
  echo "Truncating $table\n";

  db_query('TRUNCATE TABLE ' . $table);
}


/**
 * Returns a field value or an empty string if it's not there or empty
 * 
 * @param array $field
 *     The field array
 * @param string $element
 *     The element name in the field
 * @param any $return
 *     The default return value
 *
 * @return type
 */
function json_import_field_value($field, $element = 'value', $return = '#return#') {
  if (!empty($field[0]->$element)) {
    return $field[0]->$element;
  }
  else {
    return $return === '#return#' ? '' : $return;
  }
}

/**
 * Returns a link field array to match the D7 field form the old D6 field
 *
 * @param array $remote_links
 *   The D6 link field
 */
function json_import_link_field_value($remote_links) {
  $return = array();
  $i      = 0;
  if (!empty($remote_links['url'])) {
    $return[$i] = array(
      'url'        => empty($remote_links['url']) ? NULL : $remote_links['url'],
      'title'      => empty($remote_links['title']) ? NULL : $remote_links['title'],
      'attributes' => empty($remote_links['attributes']) ? array() : $remote_links['attributes'],
    );
    $i++;
  }
  elseif (count($remote_links) > 0 && is_object($remote_links[0])) {

    if (is_array($remote_links)) {
      foreach ($remote_links as $link) {
        $return[$i] = array(
          'url'        => empty($link->url) ? NULL : $link->url,
          'title'      => empty($link->title) ? NULL : $link->title,
          'attributes' => empty($link->attributes) ? array() : $link->attributes,
        );
        $i++;
      }
    }
  }

  return $return;
}

/**
 * Returns body value or NULL if it's not there or empty
 *
 * @param object $node
 * @param string $format
 */
function json_import_body_value($node, $format = 'full_html') {
  if (!empty($node->body)) {
    try {
      $body = str_replace('&agrave;', 'à', $node->body);
      $body = str_replace('&Agrave;', 'À', $body);
      $body = str_replace('&nbsp;', ' ', $body);
      $body = @html_entity_decode($body);
      $body = @htmlspecialchars_decode($body);
    } catch (Exception $e) {
      $body = $node->body;
    }

    $return = array(
      'value'  => $body,
      'format' => $format,
    );

    return $return;
  }
  else {
    return array('value' => '');
  }
}

/**
 * Returns body value or NULL if it's not there or empty
 * 
 * @param type $node
 * @param type $source
 */
function json_import_defaults(&$node, $source) {
  // Node title
  if (!empty($source->title)) {
    $node->title = $source->title;
  }
  else {
    $node->title = '';
  }

  // Node status
  if (!empty($source->status)) {
    $node->status = $source->status;
  }
  else {
    $node->status = 0;
  }
}

/**
 * Populates or unsets the taxonomy field on the node based on the lookup
 * by name of the tid from the source node.
 *
 * @param object $node
 * @param object $source
 * @param string $vocab (local vocabulary name)
 * @param string $field (local field name)
 */
function json_import_term_mapper(&$node, $source, $vocab, $field) {
  global $drupal_core_version;
  if ($drupal_core_version == 6) {
    $taxonomy = (array) $source->taxonomy;
    $term     = (array) array_shift($taxonomy);
    $name     = empty($term['name']) ? NULL : $term['name'];
    if (!empty($name)) {
      $new_term                                = taxonomy_get_term_by_name($name, $vocab);
      $tid                                     = empty($new_term) ? NULL : key($new_term);
      $node->{$field}[LANGUAGE_NONE][0]['tid'] = empty($tid) ? NULL : $tid;
    }
    if (empty($node->{$field}[LANGUAGE_NONE][0]['tid'])) {
      unset($node->{$field});
    }
  }
  elseif ($drupal_core_version == 7) {

  }
}

/**
 * Downloads and attaches the remote files to the local node
 *
 * @param object $node
 * @param object $source
 * @param string $local_name
 * @param string $source_name
 */
function json_import_files(&$node, $source, $local_name, $source_name) {
  $info = field_info_instance('node', $local_name, $node->type);
  $i    = 0;
  if (is_array($source->{$source_name})) {
    foreach ($source->{$source_name} as $id => $item) {
      if (!empty($item)) {
        if (strlen(trim($info['settings']['file_directory'])) > 0) {
          $final_path = 'public://' . $info['settings']['file_directory'] . '/' . $item->filename;
        }
        else {
          $final_path = 'public://' . $item->filename;
        }
        $node->{$local_name}[LANGUAGE_NONE][$i] = json_import_download_external_file(
          json_import_remote_url() . $item->filepath, $final_path);
      }
      else {
        $node->{$local_name}[LANGUAGE_NONE] = array();
      }
      $i++;
    }
  }
}

/**
 * Downloads and attaches the remote files to the local node
 *
 * @param object $node
 * @param object $source
 * @param string $local_name
 * @param string $source_name
 */
function json_import_attach_file_from_url(&$node, $local_name, $url) {
  $info = field_info_instance('node', $local_name, $node->type);
  if (!empty($url)) {

    $filename = end(explode('/', $url));
    if (mb_strpos($filename, '?') !== FALSE) {
      $filename = mb_substr($filename, 0, mb_strpos($filename, '?'));
    }

    if (strlen(trim($info['settings']['file_directory'])) > 0) {
      $final_path = 'public://' . $info['settings']['file_directory'] . '/' . $filename;
    }
    else {
      $final_path = 'public://' . $filename;
    }

    $node->{$local_name}[LANGUAGE_NONE][0] = json_import_download_external_file(
      $url, $final_path);
  }
  else {
    $node->{$local_name}[LANGUAGE_NONE] = array();
  }
}

/**
 * Returns the nids of a given type from the old CI site.
 *
 * @global object $language
 *
 * @param string $type
 *
 * @return boolean|object
 */
function json_import_get_nids($type) {
  return json_decode(
    file_get_contents(
      json_import_remote_url() . 'node-json/nodes/' . $type
    )
  );
}

/**
 * Returns a node by ID from the old CI site.
 *
 * @global object $language
 *
 * @param int $nid
 *
 * @return boolean|object
 */
function json_import_get_node($nid) {
  $node = json_decode(
    file_get_contents(
      json_import_remote_url() . 'node/' . $nid . '/json'
    )
  );

  global $drupal_core_version;
  $drupal_core_version = $node->drupal_core_version;

  return $node;
}

function json_import_get_drupal_core_version() {
  global $drupal_core_version;

  if (!empty($drupal_core_version)) {
    return $drupal_core_version;
  }
  else {
    return NULL;
  }
}

/**
 * Returns the URL of the old site or shows an error
 *
 * @return string|NULL
 */
function json_import_remote_url() {
  $url = variable_get('json_import_remote_url', NULL);

  if (empty($url)) {
    echo "You need to set the URL of the remote site eg: 'drush vset json_import_remote_url http://example.com' \n";
    die;
  }

  if (mb_substr($url, -1) !== '/') {
    $url .= '/';
  }

  return $url;
}

/**
 * Returns the URL of the old site or shows an error
 *
 * @return string|NULL
 */
function json_import_get_log_location() {
  $directory = variable_get('json_import_log_location', NULL);

  if (empty($directory)) {
    echo "You need to set location to write the logs to eg: 'drush vset json_import_log_location /home/user/logs' \n";
    die;
  }

  if (!file_prepare_directory($directory, FILE_CREATE_DIRECTORY)) {
    echo "Please create the $directory directory and make sure it is writable \n";
    die;
  }

  if (mb_substr($directory, -1) !== '/') {
    $directory .= '/';
  }

  return $directory;
}

/**
 * Saves a remote file to the DB and file system
 * 
 * param string $url
 *    Full url to file to download
 * param string $uri
 *    Drupal uri of where to save file public://archive/test.pdf
 * param int $save_mode
 *    File save mode from drupal core, ex FILE_EXISTS_REPLACE
 */
function json_import_download_external_file($url, $uri, $save_mode = FILE_EXISTS_RENAME, $manage_file = TRUE) {

  $url_info      = parse_url($url);
  $url_path_info = pathinfo($url_info['path']);

  //This helps with filenames with spaces
  $url = $url_info['scheme'] . '://' . $url_info['host'] . $url_path_info['dirname'] . '/' . rawurlencode($url_path_info['basename']);

  //Need to remove the filename from the uri
  $uri_target    = file_uri_target($uri);
  $uri_scheme    = file_uri_scheme($uri);
  $uri_path_info = pathinfo($uri_target);
  $directory     = file_stream_wrapper_uri_normalize($uri_scheme . "://" . $uri_path_info['dirname']);

  if (file_prepare_directory($directory, FILE_CREATE_DIRECTORY)) {
    $drupal_result = drupal_http_request($url);
    if (!empty($drupal_result->data)) {
      $path = file_stream_wrapper_uri_normalize($uri);
      if ($manage_file) {
        $new_file = file_save_data($drupal_result->data, $path, $save_mode);
      }
      else {
        return file_unmanaged_save_data($drupal_result->data, $path, $save_mode);
      }
    }
    else {
      drupal_set_message("Error downloading file, no data recieved for " . $url);
      return FALSE;
    }

    $new_file->display = 1;
    return (array) $new_file;
  }
  else {
    drupal_set_message("Could not create directory");
  }
}
