<?php

/**
 * Overrides the theme_link function to add the page title and site name to
 * all links, even if not provided.
 * 
 * @param type $variables
 * @return type
 */
function ctheme_link($variables) {

  // Add the site name variable to the end of link titles for SEO
  static $site_name;
  if (empty($site_name)) {
    $site_name = variable_get('site_name');
  }

  $attributes = $variables['options']['attributes'];
  $text = trim(check_plain(strip_tags($variables['text'])));
  if (!empty($attributes['title'])) {
    if (mb_strpos($attributes['title'], $site_name) === FALSE) {
      $attributes['title'] .= " | " . $site_name;
    }
  }
  else {
    $normal_path = drupal_get_normal_path($variables['path']);
    $item = menu_get_item($normal_path);
    if (!empty($item['title'])) {
      $attributes['title'] = $item['title'] . " | " . $site_name;
    }
    elseif (!empty($text)) {
      $attributes['title'] = $text . " | " . $site_name;
    }
    else {
      $attributes['title'] = $site_name;
    }
  }

  return '<a href="' . check_plain(
    url(
      $variables['path'],
      $variables['options']
    )
  ) . '"' . drupal_attributes($attributes) . '>' .
    ($variables['options']['html'] ?
    $variables['text'] : check_plain($variables['text'])
  ) . '</a>';
}

/**
 * Return a themed breadcrumb trail.
 *
 * @param $breadcrumb
 *   An array containing the breadcrumb links.
 * @return
 *   A string containing the breadcrumb output.
 */
function ctheme_breadcrumb($variables){
  $breadcrumb = $variables['breadcrumb'];
  if (!empty($breadcrumb)) {
    $breadcrumb[] = drupal_get_title();
    return '<div class="breadcrumb">' . implode(' <span class="breadcrumb-separator">/</span> ', $breadcrumb) . '</div>';
  }
}

/**
 * Override or insert variables into the html template.
 */
function ctheme_process_html(&$vars) {
  // Make the title short enough for Google search
  $title = $vars['head_title'];
  if (mb_strlen($title) > 64) {
    $parts = explode('|', $title);
    $words = array();
    for ($i = 0; $i < count($parts); $i++) {
      $words = array_merge($words, explode(' ', trim($parts[$i])));
    }

    $head_title = '';
    $i          = 0;
    while (mb_strlen($head_title) + mb_strlen($words[$i]) + 1 <= 64) {
      $head_title = trim($head_title . ' ' . $words[$i]);
      $i++;
    }

    $vars['head_title'] = $head_title;
  }

  // Hook into color.module
  if (module_exists('color')) {
    _color_html_alter($vars);
  }
}

/**
 * Override or insert variables into the page template.
 */
function ctheme_process_page(&$variables) {
  // Hook into color.module.
  if (module_exists('color')) {
    _color_page_alter($variables);
  }

  // call the slideshow function
  $variables['slideshow'] = ctheme_slideshow();
}
/**
 * Change the comment button link text
 *
 * @param $variables
 */
function ctheme_preprocess_node(&$variables) {
  if (isset($variables['content']['links']['comment']['#links']['comment-add']['title'])) {
    $variables['content']['links']['comment']['#links']['comment-add']['title']  = t('Comment');
  }
}

function ctheme_form_alter(&$form, &$form_state, $form_id) {
  if ($form_id == 'search_block_form') {
  
    unset($form['search_block_form']['#title']);
	
    $form['search_block_form']['#title_display'] = 'invisible';
	$form_default = t('Search');
    $form['search_block_form']['#default_value'] = $form_default;
    $form['actions']['submit'] = array('#type' => 'image_button', '#src' => base_path() . path_to_theme() . '/images/search-button.png');

 	$form['search_block_form']['#attributes'] = array('onblur' => "if (this.value == '') {this.value = '{$form_default}';}", 'onfocus' => "if (this.value == '{$form_default}') {this.value = '';}" );
  }
}


function ctheme_slideshow() {
  if (drupal_is_front_page()) {
    // Add javascript files for jquery slideshow.
    drupal_add_js(drupal_get_path('theme', 'ctheme') . '/js/jquery.cycle.all.min.js');

    //Initialize slideshow using theme settings
    $effect=theme_get_setting('slideshow_effect','ctheme');
    $effect_time=theme_get_setting('slideshow_effect_time','ctheme')*1000;

    //Defined the initial height (300) of slideshow and then the slideshow inherits the height of each slider item dynamically
    drupal_add_js('
      jQuery(document).ready(function($) {
        $("#slideshow").cycle({
          fx:    "'.$effect.'",
          speed:  "slow",
          timeout: "'.$effect_time.'",
          pager:  "#slider-navigation",
          pagerAnchorBuilder: function(idx, slide) {
            return "#slider-navigation li:eq(" + (idx) + ") a";
          },
          height: 300,
          after: onAfter
        });

        function onAfter(curr, next, opts, fwd){
          var $ht = $(this).height();
          $(this).parent().animate({height: $ht});
        }
      });', array(
        'type' => 'inline',
        'scope' => 'header',
        'weight' => 5
      )
    );

    // get the published slideshow nodes and make the slideshow html
    $query = db_select('node', 'n');
    $query->addField('n', 'nid');
    $query->condition('n.status', 1, '=');
    $query->condition('n.type', 'slider', '=');
    $query->orderBy('n.changed', 'DESC');
    $result = $query->execute();

    // return the result as an array
    $result = $result->fetchAllAssoc('nid');

    // the container for the slides
    $slideshow_slides = array();

    // the container for the navigations
    $slideshow_navigation = array();

    // load all the nodes
    foreach($result as $nid) {
      $node = node_load($nid->nid);

      // make the title and more button if there is a link
      if (!empty($node->field_link_path[LANGUAGE_NONE][0]['value'])) {
        $title = l($node->title, $node->field_link_path[LANGUAGE_NONE][0]['value']);
        $more = l(
          t('Tell me more'),
          $node->field_link_path[LANGUAGE_NONE][0]['value'],
          array(
            'attributes' => array(
              'class' => array('more')
            )
          )
        );
        $more = '<div class="slider-more">' . $more . '</div>';
      }
      else {
        $title = $node->title;
        $more = '';
      }

      // the type of slide to make
      $slider_type = $node->field_slider_type[LANGUAGE_NONE][0]['value'];

      // Text only slides
      if ($slider_type == 'text') {
        $slideshow_slides[] = '
        <div class="slider-item" style="display:none;">
          <div class="content">
              <h2> ' . $title . ' </h2>
              ' . $node->field_body[LANGUAGE_NONE][0]['value'] . '
              ' . $more . '
          </div>
        </div>
        ';
        $slideshow_navigation[] = '<li><a href="#"></a></li>';
      }

      // Image only slides
      if ($slider_type == 'image' && !empty($node->field_image[LANGUAGE_NONE][0]['uri'])) {
        $img = new TigerfishImage();
        $img->style('slider-full-width') // optional
           ->path($node->field_image[LANGUAGE_NONE][0]['uri'])
           ->alt($node->title)
           ->title($node->title)
           ->imageClasses(array('masked'))
           ->link(isset($node->field_link_path[LANGUAGE_NONE][0]['value']) ?
                        $node->field_link_path[LANGUAGE_NONE][0]['value'] :
                        NULL);
        $img = $img->getImageTag();

        $slideshow_slides[] = '
        <div class="slider-item" style="display:none;">
          <div class="content">
            '.$img.'
          </div>
        </div>
        ';
        $slideshow_navigation[] = '<li><a href="#"></a></li>';
      }

      // Image Left slides
      if ($slider_type == 'image-left' && !empty($node->field_image[LANGUAGE_NONE][0]['uri'])) {
        $img = new TigerfishImage();
        $img->style('slider-half-width') // optional
          ->path($node->field_image[LANGUAGE_NONE][0]['uri'])
          ->alt($node->title)
          ->title($node->title)
          ->imageClasses(array('masked'))
          ->link(isset($node->field_link_path[LANGUAGE_NONE][0]['value']) ?
          $node->field_link_path[LANGUAGE_NONE][0]['value'] :
          NULL);
        $img = $img->getImageTag();

        $slideshow_slides[] = '
        <div class="slider-item" style="display:none;">
          <div class="content">
            <div style="float:left; padding:0 30px 0 0;">
              '.$img.'
            </div>
              <h2> ' . $title . ' </h2>
              ' . $node->field_body[LANGUAGE_NONE][0]['value'] . '
              ' . $more . '
          </div>
        </div>
        ';
        $slideshow_navigation[] = '<li><a href="#"></a></li>';
      }

      // Image Right slides
      if ($slider_type == 'image-right' && !empty($node->field_image[LANGUAGE_NONE][0]['uri'])) {
        $img = new TigerfishImage();
        $img->style('slider-half-width') // optional
          ->path($node->field_image[LANGUAGE_NONE][0]['uri'])
          ->alt($node->title)
          ->title($node->title)
          ->imageClasses(array('masked'))
          ->link(isset($node->field_link_path[LANGUAGE_NONE][0]['value']) ?
          $node->field_link_path[LANGUAGE_NONE][0]['value'] :
          NULL);
        $img = $img->getImageTag();

        $slideshow_slides[] = '
        <div class="slider-item" style="display:none;">
          <div class="content">
            <div style="float:right; padding:0 0 0 30px;">
              '.$img.'
            </div>
              <h2> ' . $title . ' </h2>
              ' . $node->field_body[LANGUAGE_NONE][0]['value'] . '
              ' . $more . '
          </div>
        </div>
        ';
        $slideshow_navigation[] = '<li><a href="#"></a></li>';
      }
    }

    if (!empty($slideshow_slides)) {
      $return = '<div id="slideshow">' . implode('', $slideshow_slides) . '</div>';
      $navigation = '
      <div id="slider-controls-wrapper">
      <div id="slider-controls">
      <ul id="slider-navigation">' . implode('', $slideshow_navigation) .
        '</ul></div></div>';

      return $return . $navigation;

    }
  }
  else {
    return FALSE;
  }
}

/**
 * Take a rendered field and output it regardless of language.
 *
 * @param string $field_name
 * The name of the field to render.
 * @param object $node
 * The full node object this field belongs to.
 * @param int $key
 * The array key of the item in the field to render
 * @param mixed $settings
 * Either the name of a display mode ('default', 'teaser') or an array. If an
 * array, allows finer customisation of the output. Allowed keys include:
 * - image_style: Used for processing images.
 * @return string
 * Returns a rendered field
 */
function ctheme_render_field($field_name, $node, $settings = array(), $key = 0) {
  $field = field_get_items('node', $node, $field_name);

  if (empty($field)) {
    return '';
  }

  if (!empty($settings) && is_array($settings) && !array_keys($settings, 'node_reference_node')) {
    $settings = array('settings' => $settings);
  }

  // If an alt is present, but 0 length, use a default alt.
  if (isset($field[$key]['alt']) && drupal_strlen($field[$key]['alt']) == 0) {
    $field[$key]['alt'] = t('Image');
  }

  $output = field_view_value('node', $node, $field_name, $field[$key], $settings);

  return render($output);
}