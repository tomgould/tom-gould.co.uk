<?php
/**
 * @file
 * Classes for processing database data and converting dev paths to live paths
 *
 * 2011-11-29, craig: Class-based refactor
 */

class TigerfishDevToLive {

  /**
   * Flag specifying whether to emit debug messages during execution
   *
   * @var bool
   */
  protected $debug = FALSE;

  /**
   * A set of user-defined filters that the content will be passed through
   *
   * @var array
   */
  protected $filters = array();

  /**
   * The value to convert from when using the default filters.
   *
   * @var string
   */
  protected $toValue = '';

  /**
   * The value to convert to when using the default filters.
   *
   * @var string
   */
  protected $fromValue = '';

  /**
   * Flag to set relative or absolute paths with the default filters.
   *
   * @var bool
   */
  protected $relative = FALSE;

  /**
   * Option for what to do with HTTPS links (TO, FROM, KEEP)
   *
   * @var string
   */
  protected $https = 'KEEP';

  /**
   * Flag to perform object unserialize and convert. Defaults to FALSE.
   *
   * NOTE: Enabling this flag WILL unintentionally break unserialize/reserialize
   * of non stdClass objects (e.g. WysiwygProfileController). In the vast
   * majority of cases, filtering variables inside objects is not required
   * and can safely be brought back in order by running 'drush @site cc all'
   * post-filter.
   *
   * @var bool
   */
  protected $filterObjects = FALSE;

  /**
   * Class constructor
   *
   * We set up our default filters here
   */
  public function __construct() {

    // If we unserialize an object, prevent the magic wakeup from happening.
    ini_set('unserialize_callback_func', '');

  }

  /**
   * Sets the default from value
   *
   * @param string $from
   *    From value to set
   * @return string
   *    Current setting
   */
  public function from($from) {
    if (is_string($from)) {
      $this->fromValue = $from;
    }
    return $this->fromValue;
  }

  /**
   * Sets the default to value
   *
   * @param string $to
   *    To value to set
   * @return string
   *    Current setting
   */
  public function to($to) {
    if (is_string($to)) {
      $this->toValue = $to;
    }
    return $this->toValue;
  }

  /**
   * Sets the relative flag
   *
   * @param bool $relative
   *    Relative value to set
   * @return bool
   *    Current setting
   */
  public function relative($relative) {
    $this->relative = (bool)$relative;
    return $this->relative;
  }

  /**
   * Sets the HTTPS conversion state
   *
   * @param string $https
   *    HTTPS state value to set
   * @return string
   *    Current setting
   */
  public function https($https) {
    if (is_string($https)) {
      $this->https = $https;
    }
    return $this->https;
  }

  /**
   * Sets the skip objects flag
   *
   * @param bool $skip_obj
   *    Skip value to set
   * @return bool
   *    Current setting
   */
  public function filterObjects($filterObjects) {
    $this->filterObjects = (bool)$filterObjects;
    return $this->filterObjects;
  }

  /**
   * Sets the debug mode flag
   *
   * @param bool $debug
   *    Debug flag to set
   * @return bool
   *    Current setting
   */
  public function debugMode($debug) {
    if ((bool)$debug === TRUE) {
      // Debug output via drush_print can only be turned on if we are in
      // a drush call
      if (function_exists('drush_print') && function_exists('drush_get_option')) {
        $this->debug = $debug;
      }
    }
    return $this->debug;
  }

  /**
   * Attaches a filter to the processor
   *
   * @param string $name
   *    Unique name for the filter
   * @param $filter
   *    Name of filter class implementing TigerfishDTLFilter interface
   * @return void
   */
  public function attachFilter($name = '', $filter) {
    $this->filters[$name] = new $filter;
  }

  /**
   * Sets up the default filters
   *
   * @return void
   */
  public function attachDefaultFilters() {

    /**
     * It's possible to extend what filters the content is subjected to.
     * By default, this is a URL filter, and a PATH filter. Other filters
     * can be implemented and attached if other conversions are also required.
     */
    $this->attachFilter('URL', 'TigerfishDTLReplaceHrefAndSrc');
    $this->filters['URL']->from($this->fromValue);
    $this->filters['URL']->to($this->toValue);
    $this->filters['URL']->relative($this->relative);
    $this->filters['URL']->https($this->https);
    $this->filters['URL']->debugMode($this->debug);

    $this->attachFilter('PATH', 'TigerfishDTLReplacePaths');
    $this->filters['PATH']->from($this->fromValue);
    $this->filters['PATH']->to($this->toValue);
    $this->filters['PATH']->relative($this->relative);
    $this->filters['PATH']->https($this->https);
    $this->filters['PATH']->debugMode($this->debug);

  }

  /**
   * Makes replacements recursively to an array
   *
   * This executes all of the filters set in TigerfishDevToLive->filters
   * on the content, so can be extended to perform other replacements if
   * required.
   *
   * @param $var
   *    Array to make replacements within
   * @param int $level
   *    Nesting level
   * @return array
   *    Array with replacements made
   */
  protected function replaceRecursive($var, $level = 0) {

    $level++;

    foreach($var as $key => $value) {

      if (is_array($value)) {

        $this->debug && drush_print(
          t(
            'Key !key is Array',
            array(
              '!key' => $key
            )
          ),
          $level
        );

        $var[$key] = $this->replaceRecursive($value, $level);

      }
      elseif (
        is_object($value)
        && get_class($value) == 'stdClass'
        && $this->filterObjects
      ) {

        $this->debug && drush_print(
          t(
            'Key !key is stdClass Object',
            array(
              '!key' => $key
            )
          ),
          $level
        );

        $obj_as_array = (array)$value;
        $obj_as_array = $this->replaceRecursive($obj_as_array, $level);

        foreach($obj_as_array as $obj_key => $obj_value) {
          $value->{$obj_key} = $obj_value;
        }

        $var[$key] = $value;

      }
      elseif (is_object($value)) {

        // Skip any non-stdClass objects, as we can't process them without
        // up front knowledge of their class structure.
        (!$this->filterObjects || $this->debug) && drush_print(
          t(
            'Key !key is !type Object (skipped)',
            array(
              '!key' => $key,
              '!type' => get_class($value)
            )
          ),
          $level
        );

      }
      else {

        /**
         * Check if the value is literally a serialized FALSE. To prevent
         * confusion when unserialize() returns FALSE on fail, we skip over the
         * unserialize code if the value is already FALSE.
         */
        if ($value == serialize(FALSE)) {
          continue;
        }

        if (preg_match("/^(i|s|a|o|d):(.*);/si", $value)) {

          // The string value looks like its serialized, so let's try
          // unserialization.
          $unserialized = @unserialize($value);

          if ($unserialized === FALSE) {

            /**
             * If a literal FALSE is thrown at this point, it couldn't
             * unserialize. Try to fix up string lengths and try again.
             * If it still doesn't work after that, the data's too dirty
             * to use.
             */
            $error_message = t(
              'Bad serialized data, trying to fix: key = !key, value = !value',
              array(
                '!key' => $key,
                '!value' => $value,
              )
            );

            drush_print($error_message);

            $value = $this->recalcSerializedStringLen($value);
            $unserialized = @unserialize($value);

          }

          if ($unserialized === FALSE) {

            /**
             * I make that a double fault. Thus, it's throw an exception time
             * if debugging, or simply output the error and continue if not.
             */

            $error_message = t(
              'Failed unserialization! key = !key, value = !value',
              array(
                '!key' => $key,
                '!value' => $value,
              )
            );

            if ($this->debug) {
              throw new UnexpectedValueException($error_message);
            }
            else {
              drush_print($error_message);
              continue;
            }

          }
          elseif (is_array($unserialized)) {

            $this->debug && drush_print(
              t(
                'Key !key is serialized Array',
                array(
                  '!key' => $key
                )
              ),
              $level
            );

            $tmp = $this->replaceRecursive($unserialized, $level);
            $var[$key] = serialize($tmp);

          }
          elseif (
            is_object($unserialized)
            && get_class($unserialized) == 'stdClass'
            && $this->filterObjects
          ) {

            $this->debug && drush_print(
              t(
                'Key !key is serialized stdClass Object',
                array(
                  '!key' => $key
                )
              ),
              $level
            );

            $obj_as_array = (array)$unserialized;
            $obj_as_array = $this->replaceRecursive($obj_as_array, $level);

            foreach($obj_as_array as $obj_key => $obj_value) {
              $unserialized->{$obj_key} = $obj_value;
            }

            $var[$key] = serialize($unserialized);

          }
          elseif (is_object($unserialized)) {

            // Skip any non-stdClass objects, as we can't process them without
            // up front knowledge of their class structure.
            (!$this->filterObjects || $this->debug) && drush_print(
              t(
                'Key !key is serialized !type Object (skipped)',
                array(
                  '!key' => $key,
                  '!type' => get_class($unserialized)
                )
              ),
              $level
            );

          }
          else {

            // It's a serialized string. The 'variable' table uses a lot of
            // these as it passes everything through serialize()
            // irrespective of what it actually is.
            $this->debug && drush_print(
              t(
                'Key !key is Value',
                array(
                  '!key' => $key
                )
              ),
              $level
            );

            foreach($this->filters as $filter) {
               $unserialized = $filter->execute($unserialized);
            }

            $var[$key] = serialize($unserialized);

          }

        }
        else {

          // It's not serialized data, it's just a plain string value.
          $this->debug && drush_print(
            t(
              'Key !key is Value',
              array(
                '!key' => $key
              )
            ),
            $level
          );

          foreach($this->filters as $filter) {
             $value = $filter->execute($value);
          }

          $var[$key] = $value;

        }

      }
    }

    return $var;

  }

  /**
   * Makes attempts to fix string lengths in bad serialized data
   *
   * Thanks to: David Coveney http://www.davecoveney.com for the original RegEx,
   * which I refactored to use a callback, as '/e' modifier is discouraged in
   * Drupal code.
   *
   * @param string $value
   *    The broken serialized string
   * @return string
   *    The string with the string lengths fixed up, which will (hopefully)
   *    unserialize() correctly.
   */
  protected function recalcSerializedStringLen($value) {

    return preg_replace_callback(
      '/s:(\d+):"(.*?)";/',
      array( &$this, 'recalcSerializedStringLenCallback'),
      $value
    );

  }

  /**
   * Callback used to fix up serialized strings.
   *
   * @param array $matches
   *    Matches from preg_match()
   *
   * @return string
   *    The value to use as the replacement.
   */
  private function recalcSerializedStringLenCallback($matches) {

    return "s:"  . drupal_strlen($matches[2]) . ":\"" . $matches[2] . "\";";

  }

  /**
   * Executes the processor
   *
   * @throws BadMethodCallException
   * @return void
   */
  public function execute() {

    throw new BadMethodCallException(t('TigerfishDevToLive::execute() is implemented only in descendant classes'));

  }

}

/**
 * Class to apply filters to the current database 'in situ'. Useful for
 * making conversions to a clone database.
 */
class TigerfishDTLInSitu extends TigerfishDevToLive {

  /**
   * Executes the processor
   *
   * @return bool
   *    Throws Exception and returns FALSE on failure
   */
  public function execute() {

    // Add the default filter set if no other filters are defined.
    if (empty($this->filters)) {
      $this->attachDefaultFilters();
    }

    $all_tables_query = db_query('SHOW TABLES');

    $this->debug && drush_print(
      format_plural(
        $all_tables_query->rowCount(),
        '1 table',
        '@count tables'
      )
    );

    /**
     * When performing an In-Situ change, we need to ensure that the registry
     * is in order before we start. This ensures that when magic __wakeup()
     * calls in unserialized class objects occur, they can find all of the
     * includes that they need to be reinstantiated.
     *
     * Examples of class objects that use the __wakeup() system are:
     * RulesEventSet
     * RulesReactionRule
     */
    drush_print(t('Pre flushing caches...'));
    drupal_flush_all_caches();

    /**
     * Skip selected table prefixes altogether, as when they are converted all
     * hell breaks loose.
     */
    $skip_tables = array(
      'cache',
      'wildfire',
      'watchdog',
    );

    /**
     * But we do want to do this one, despite the prefix!
     */
    $no_skip_tables = array(
      'wildfire_templates'
    );

    while($table = $all_tables_query->fetchField(0)) {

      if (!in_array($table, $no_skip_tables)) {
        foreach($skip_tables as $prefix) {
          if (drupal_substr($table, 0, drupal_strlen($prefix)) == $prefix) {
            drush_print(
              t(
                'Skipped: "!table"',
                array(
                  '!table' => $table,
                )
              )
            );
            continue(2);
          }
        }
      }

      drush_print(
        t(
          'Table:   "!table"',
          array(
            '!table' => $table
          )
        )
      );

      $table_data_query = db_select($table, 't');
      $table_data_query->fields('t');
      $table_data = $table_data_query->execute();

      while($table_row = $table_data->fetchAssoc()) {

        // Convert the table row
        $row = $this->replaceRecursive($table_row);

        // Rewrite it if required
        if ($row !== $table_row) {
          /*
           * Note, this uses all of the fields in the source data
           * as keys to determine which row to actually update. This will
           * have the side effect of creating large query strings and
           * may be a very slow process to run. However, it does ensure that
           * exactly the right row is being rewritten.
           *
           * We also don't use an UPSERT (db_merge()) here as we don't want
           * extra rows to be accidentally inserted. So, if the update fails,
           * that's the end of it.
           */
          $query = db_update($table);
          $query->fields($row);
          foreach($table_row as $tr_key => $tr_value) {
            $query->condition($tr_key, $tr_value, '=');
          }
          $query->execute();
        }

      }

    }

    drush_print(t('Post flushing caches...'));
    drupal_flush_all_caches();

  }

}

/**
 * Class that implements the filter interface
 *
 * The class is not useful by itself; it's intended that the class is extended
 * and execute() is fully implemented within any descendant class.
 */
class TigerfishDTLFilter {

  /**
   * Flag specifying whether to emit debug messages during execution
   *
   * @var bool
   */
  protected $debug = FALSE;

  /**
   * Value to change from
   *
   * @var string
   */
  protected $fromValue = '';

  /**
   * Value to change to
   *
   * @var string
   */
  protected $toValue = '';

  /**
   * Flag to set relative or absolute paths
   *
   * @var bool
   */
  protected $relative = FALSE;

  /**
   * Option for what to do with HTTPS links (TO, FROM, KEEP)
   *
   * @var string
   */
  protected $https = 'KEEP';

  /**
   * Sets the debug mode flag
   *
   * @param bool $debug
   *    Debug flag to set
   * @return bool
   *    Current setting
   */
  public function debugMode($debug) {
    if (is_bool(($debug))) {
      if ($debug) {
        // Debug output via drush_print can only be turned on if we are in
        // a drush call
        if (function_exists('drush_print') && function_exists('drush_get_option')) {
          $this->debug = $debug;
        }
      }
    }
    return $this->debug;
  }

  /**
   * Sets the filters from value
   *
   * @param string $from
   *    From value to set
   * @return string
   *    Current setting
   */
  public function from($from) {
    if (is_string($from)) {
      $this->fromValue = $from;
    }
    return $this->fromValue;
  }

  /**
   * Sets the filters to value
   *
   * @param string $to
   *    To value to set
   * @return string
   *    Current setting
   */
  public function to($to) {
    if (is_string($to)) {
      $this->toValue = $to;
    }
    return $this->toValue;
  }

  /**
   * Sets the filters relative flag
   *
   * @param string $relative
   *    Relative flag value to set
   * @return bool
   *    Current setting
   */
  public function relative($relative) {
    if (is_bool($relative)) {
      $this->relative = $relative;
    }
    return $this->relative;
  }

  /**
   * Sets the HTTPS conversion state
   *
   * @param string $https
   *    HTTPS state value to set
   * @return string
   *    Current setting
   */
  public function https($https) {
    if (is_string($https)) {
      $this->https = $https;
    }
    return $this->https;
  }


  /**
   * Executes the filter with the current setup
   *
   * @throws BadMethodCallException
   * @return void
   */
  public function execute() {

    throw new BadMethodCallException(t('TigerfishDTLFilter::execute() is implemented only in descendant classes'));

  }

}

/**
 * Class implementing a filter for href="" and src="" replacements
 */
class TigerfishDTLReplaceHrefAndSrc extends TigerfishDTLFilter {

  /**
   * A set of URLs constructed from TigerfishDTLFilter->fromValue
   * which contain the common constructed URLs based on that value.
   *
   * @var array
   */
  protected $from_urls = array();

  /**
   * Sets the filters from value. Also sets up from_urls by proxy
   *
   * @param string $from
   *    From value to set
   * @return string
   *    Current setting
   */
  public function from($from) {
    if (is_string($from)) {
      $this->fromValue = $from;

      /*
       * This URL set covers most of our usual use cases and URL variants for
       * development sites, etc.
       *
       * We don't filter this list according to the setting of
       * TigerfishDTLFilter->https as we want to catch instances where
       * HTTP and HTTPS versions of URLs have been used incorrectly in the site
       * content.
       */
      $this->from_urls(array(
        'https://' . $this->fromValue,
        'https://local.' . $this->fromValue,
        'https://www.' . $this->fromValue,
        'http://' . $this->fromValue,
        'http://local.' . $this->fromValue,
        'http://www.' . $this->fromValue,
      ));

    }
    return $this->fromValue;
  }

  /**
   * Sets the from URLs array used for matching.
   *
   * @param array $from_urls
   *    Array of URLs to convert from.
   * @return string
   *    Current setting
   */
  private function from_urls($from_urls) {
    if (is_array($from_urls)) {
      $this->from_urls = $from_urls;
    }
    return $this->from_urls;
  }

  /**
   * Executes the filter with the current setup
   *
   * @param string $var
   *    The input string
   * @return string
   *    The string with replacements made.
   */
  public function execute($var) {

    // Replace URLs
    $var = preg_replace_callback(
      '/(src|href)="([^"]+)"/i',
      array( &$this, 'replaceURLsCallback'),
      $var
    );

    return $var;

  }

  /**
   * Callback used to make the string modifications
   *
   * @param array $matches
   *    Matches from preg_match()
   *
   * @return string
   *    The value to use as the replacement.
   */
  private function replaceURLsCallback($matches) {

    preg_match('/(http[s]?)?:\/\/([^\/]+)(.*)?/', $matches[2], $url_matches);

    $replacement = $matches[0];

    if (!empty($url_matches[0])) {

      switch (drupal_strtoupper($this->https)) {
        case 'TO':
          $scheme = 'https';
        break;
        case 'FROM':
          $scheme = 'http';
        break;
        default:
          $scheme = $url_matches[1];
        break;
      }

      foreach($this->from_urls as $from_url) {

        $match_url = $url_matches[1] . '://' . $url_matches[2];

        if ($from_url == $match_url) {
          if (!$this->relative) {
            $replacement = $matches[1] . '="' . $scheme . '://';
            $replacement .= $this->toValue . $url_matches[3] . '"';
          }
          else {
            $replacement = $matches[1] . '="' . $url_matches[3] . '"';
          }
          break;
        }

      }

    }

    if ($replacement !== $matches[0]) {

      $this->debug && drush_print(
        t(
          'URL: !from => !to',
          array(
            '!from' => $matches[0],
            '!to' => $replacement,
          )
        )
      );

    }

    return $replacement;

  }

}

/**
 * Class implementing a filter for Path replacements
 */
class TigerfishDTLReplacePaths extends TigerfishDTLFilter {

  /**
   * Executes the filter with the current setup
   *
   * @param string $var
   *    The input string
   * @return string
   *    The string with replacements made.
   */
  public function execute($var) {

    if (!empty($this->fromValue) && !empty($this->toValue)) {
      // Change /sites/DEVSITE/path into /sites/LIVESITE/path
      $path_var = preg_replace(
        '/(\/?)sites\/' . $this->fromValue . '(.*)?/i',
        '$1sites/' . $this->toValue . '$2',
        $var
      );

      if ($path_var !== $var) {

        $this->debug && drush_print(
          t(
            'PATH: !from => !to',
            array(
              '!from' => $var,
              '!to' => $path_var,
            )
          )
        );

        $var = $path_var;

      }

    }

    return $var;

  }

}
