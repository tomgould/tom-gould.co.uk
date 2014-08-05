<?php
/**
 * @file
 * Drush commands for the Tigerfish module
 */

function tigerfish_drush_command() {
  $items = array();

  $items['updatepaths'] = array(
    'callback' => 'drush_tigerfish_updatepaths',
    'description' => 'Updates paths from A to B in the current database',
    'options' => array(
      'from' => 'Site path to convert from e.g. /sites/FROM/xxx/ and http://FROM/xxx',
      'to' => 'Site path to convert to e.g. /sites/TO/xxx/ and http://TO/xxx',
      'relative' => 'Whether to convert links to relative paths. Defaults to FALSE',
      'https' => 'Whether to convert URL scheme to/from HTTPS. Options are TO, FROM, KEEP. Defaults to KEEP',
      'filter_objects' => 'Whether to filter serialized object data. Use with care. Defaults to FALSE',
    ),
    'examples' => array(
      'drush @sitename updatepaths --from=devsite.tiger-dev.co.uk --to=livesite.com'
        => 'Conversion of dev paths to live paths',
      'drush @sitename updatepaths --from=devsite.tiger-dev.co.uk --https=TO'
        => 'Conversion of dev paths to live paths, and ensure all live site links start with https://. To is automatic based on the sites folder name',
      'drush @sitename updatepaths --from=devsite.tiger-dev.co.uk --to=livesite.com --https=FROM --relative=TRUE'
        => 'The full monty. Converts paths, sets all links to start http://, and sets all found href/src links to be relative',
    ),
  );

  return $items;
}

/*
 * Implements hook_drush_help().
 */
function tigerfish_drush_help($section) {
  switch ($section) {
    case 'drush:updatepaths':
      return t('Updates paths from A to B in the current database');
    break;
  }
}

/**
 * Updates all paths and URLs in the current database to the given 'to' value
 */
function drush_tigerfish_updatepaths() {

  global $base_url;

  module_load_include('php', 'tigerfish', 'TigerfishDevToLive');

  /*
   * In most cases, you want to leave the 'to' parameter blank, and it will
   * derive it from the site that you are running the drush command against.
   * However, for rare instances where it gets it wrong (or you simply want to
   * do something a different way) you can override it.
   */
  $to = drush_get_option('to');

  if (empty($to)) {
    /*
     * TODO: If the Base URL contains a port designation, this should also be
     * handled for completeness. No Tigerfish sites use this presently however.
     * e.g. https://www.example.com:1069/ should be fully catered for.
     */
    $to = parse_url($base_url, PHP_URL_HOST);
    drush_print(
      t(
        'No to value specified, using to value of "!to"',
        array(
          '!to' => $to,
        )
      )
    );
  }

  $from = drush_get_option('from');

  if (empty($from)) {
    drush_print(t('--from is a required parameter'));
    return FALSE;
  }

  $processor = new TigerfishDTLInSitu;
  $processor->from($from);
  $processor->to($to);
  $processor->relative(drush_get_option('relative'));
  $processor->https(drush_get_option('https'));
  $processor->filterObjects(drush_get_option('filter_objects'));
  $processor->debugMode(drush_get_option('debug'));
  $processor->execute();

  drush_print(t('Done'));

}
