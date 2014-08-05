<?php
/**
 * @file
 * Encapsulates a Drupal image and provides options for its output.
 *
 * BASIC EXAMPLE
 *
 * Note how chaining is used to save code and improve readability.
 *
 * $img = new TigerfishImage();
 * $img->style('foo') // optional
 *   ->path('files/foo.jpg')
 *   ->alt(t('This is my alt text. Yes I use the t() statement properly.'));
 *
 * FULL EXAMPLE
 *
 * $img = new TigerfishImage();
 * $img->style('foo') // optional
 *   ->path('files/foo.jpg')
 *   ->alt(t('This is my alt text. Yes I use the t() statement properly.'));
 *   ->title(t('This is also my title text.'))
 *   ->imageClasses(array('small', 'round'))
 *   ->link('http://example.com')
 *   ->linkClasses(array('red', 'external'));
 *
 * VIDEO EXAMPLE
 *
 * $img = new TigerfishImage();
 * $img->style('foo') // optional
 *   ->path('files/foo.jpg')
 *   ->alt(t('This is my alt text. Yes I use the t() statement properly.'));
 *   ->link('http://youtube.com/v/1234567') // You need to use this URL format.
 *   ->linkClasses(array('red', 'external'))
 *   ->videoHeight(240)
 *   ->videoWidth(320);
 *
 * RETRIEVING THE IMAGE
 *
 * $url = $img->getImageUrl(); // Get just the path to the image.
 * $tag = $img->getImageTag(); // Get the full image tag (with link if used).
 *
 * GROUPING SHADOWBOX IMAGES
 *
 * Note you only need to change certain elements of the original image tag.
 *
 * $img = new TigerfishImage();
 * $img->style('foo') // optional
 *   ->path('files/foo.jpg')
 *   ->alt(t('Image within group.'))
 *   ->shadowboxGroup('group1');
 *
 * print $img->getImageTag();
 *
 * $img->path('files/foo2.jpg');
 * print $img->getImageTag();
 *
 * $img->path('files/foo3.jpg');
 * print $img->getImageTag();
 */

class TigerfishImage {
  /**
   * The alt text to be placed on the image. This MUST be set.
   *
   * @var string
   */
  protected $alt = NULL;

  /**
   * The name of the image to be used if the image specified is not found or is
   * otherwise unaccessible. Note that this can be overridden by setting a
   * tigerfish_default_image variable to provide a site-specific default image.
   *
   * @var string
   */
  protected $defaultImage = 'default_image.png';

  /**
   * A fragment to be added to the link, if used.
   *
   * @var string
   */
  protected $fragment = NULL;

  /**
   * An array of (X)HTML classes to be applied to the image tag.
   *
   * @var array
   */
  protected $imageClasses = array();

  /**
   * The height of the original image. Automatically calculated.
   *
   * @var int
   */
  protected $imageHeight = 0;

  /**
   * The width of the original image. Automatically calculated.
   *
   * @var int
   */
  protected $imageWidth = 0;

  /**
   * Where the image should link to when clicked. If left as the default NULL,
   * does not link anywhere.
   *
   * @var string
   */
  protected $link = NULL;

  /**
   * An array of (X)HTML classes to be applied to the link tag.
   *
   * Only applies if the link is actually set.
   *
   * @var array
   */
  public $linkClasses = array();

  /**
   * The path to the image file.
   *
   * Set this using the setPath() method rather than manually setting it, as it
   * does some checks and uses the default image where necessary.
   *
   * @var string.
   */
  protected $path = NULL;

  /**
   * Key/value pairs in array format to be put into the querystring of the link.
   *
   * @var array
   */
  protected $queryString = array();

  /**
   * Whether to make this image enlarge via a shadowbox.
   *
   * If this is being set, you should also set an image style to be used for
   * the smaller image, otherwise the image will link to the exact same image
   * but in a shadowbox.
   *
   * @var bool
   */
  protected $shadowbox = FALSE;

  /**
   * The group of images that this image is within if shadowbox is used.
   *
   * Optional.
   *
   * @var string
   */
  protected $shadowboxGroup = NULL;

  /**
   * The image style preset for this image. If NULL, no image style will be
   * used. Set this with the setStyle() method.
   *
   * @var string
   */
  protected $style = NULL;

  /**
   * The URL to the resized/processed file. This is not an internal path.
   *
   * Automatically calculated. This cannot be set manually. Nyaaahhh.
   *
   * @var string
   */
  protected $styleUrl = NULL;

  /**
   * The title attribute for the image, or the link if the image is linked.
   * Defaults to NULL, which will not set a title.
   *
   * @var string
   */
  public $title = NULL;

  /**
   * The URL to the file. This is not an internal path like $path.
   *
   * @var string
   */
  protected $url = NULL;

  /**
   * If shadowbox is being used to display a video, this stores the height.
   *
   * @var int
   */
  protected $videoHeight = NULL;

  /**
   * The player to be used for shadowbox videos.
   *
   * @var string
   */
  protected $videoPlayer = 'swf';

  /**
   * If shadowbox is being used to display a video, this stores the width.
   *
   * @var int
   */
  protected $videoWidth = NULL;

  /**
   * Class constructor.
   */
  function __construct() {
    // Replace the default image with the site-specific default image, if one
    // has been set.
    $this->defaultImage = variable_get('tigerfish_default_image', $this->defaultImage);
  }

  /**
   * Return the path to the image, for use in manually-created image tags.
   *
   * @return string
   * The absolute/relative path to the image, as specified.
   */
  public function getImageUrl() {
    $this->validateParameters();

    if (!empty($this->style)) {
      return $this->styleUrl;
    }
    else {
      return $this->url;
    }
  }

  /**
   * Formulate a rel attribute for use with shadowbox.
   *
   * This means that it will take the group into account and, if a video, will
   * include options like height, player and width.
   *
   * @return string
   * A string that represents the entire rel attribute to be used with
   * shadowbox.
   */
  protected function getShadowboxRel() {
    $pieces = array('shadowbox');

    if (!empty($this->shadowboxGroup)) {
      $pieces[0] .= '[' . $this->shadowboxGroup . ']';
    }

    // Note that both video width and height must be set or this is pointless.
    if (!empty($this->videoHeight) && !empty($this->videoWidth)) {
      $pieces[] = 'height=' . $this->videoHeight;
      $pieces[] = 'width=' . $this->videoWidth;
      $pieces[] = 'player=' . $this->videoPlayer;
    }

    return implode(';', $pieces);
  }

  /**
   * Return the image as a fully formatted (X)HTML image tag.
   *
   * @return string
   * The image tag itself. For example, <img src="http://example.com" />
   */
  public function getImageTag() {
    $variables = array(
      'path'   => $this->path,
      'alt'    => $this->alt,
      'width'  => $this->imageWidth,
      'height' => $this->imageHeight,
    );
    $linkAttributes = array();
    $linkUrl = NULL;

    if (!empty($this->title)) {
      // A title is set. Determine whether to apply it to the link tag or the
      // image tag.
      if (empty($this->link)) {
        $variables['title'] = $this->title;
      }
      else {
        $linkAttributes['title'] = $this->title;
      }
    }

    if (!empty($this->imageClasses)) {
      $variables['attributes'] = array('class' => $this->imageClasses);
    }

    if (!empty($this->linkClasses)) {
        $linkAttributes['class'] = $this->linkClasses;
    }

    if (!empty($this->style)) {
      // This image needs to be processed using theme_image_style.
      $variables['style_name'] = $this->style;
      $theme_function = 'image_style';
    }
    else {
      // This image doesn't need processing so use standard theme_image.
      $theme_function = 'image';
    }

    // If this image is to be displayed in a shadowbox, we need to add a rel=""
    // attribute to the link.
    if ($this->shadowbox === TRUE) {
      $linkAttributes['rel'] = $this->getShadowboxRel();
    }

    // Work out where the link URL should go, if one is set.
    if (!empty($this->shadowbox) && empty($this->link)) {
      // Shadowbox should be set, but no link is specified, so just use the full
      // image URL as the link URL.
      $linkUrl = $this->url;
    }
    elseif (!empty($this->link)) {
      // A link is set. Whether we're using shadowbox or not, we should just
      // link to the desired link URL.
      $linkUrl = $this->link;
    }

    // Render the output of the image itself.
    $output = theme($theme_function, $variables);

    // If we need to add a link to the equation, wrap it around the existing
    // image tag.
    if (!empty($linkUrl)) {
      $params = array(
        'attributes' => $linkAttributes,
        'html'       => TRUE,
      );

      // Add query string and fragment if needed.
      if (!empty($this->queryString)) {
        $params['query'] = $this->queryString;
      }
      if (!empty($this->fragment)) {
        $params['fragment'] = $this->fragment;
      }
      if (!empty($this->title)) {
        $params['attributes']['title'] = $this->title;
      }

      $output = l($output, $linkUrl, $params);
    }

    return $output;
  }

  /**
   * Add a single class to the image tag.
   *
   * Not chainable.
   *
   * @param string $class
   * The class to be added.
   * @return void
   */
  function addImageClass($class) {
    $this->imageClasses[] = $class;
  }

  /**
   * Set the alternative text for the image (the alt="" attribute).
   *
   * This is not optional. It is required to produce standards-compliant code.
   *
   * @param string $alt
   * The text to be used as the alt text.
   * @return TigerfishImage
   * Returns the TigerFish image object for chaining purposes.
   */
  public function alt($alt) {
    $this->alt = $alt;
    return $this;
  }

  /**
   * Adds a fragment to the link.
   *
   * This is such as http://example.com/foo#bar.
   *
   * @param string $fragment
   * The fragment to be added. Will be urlencoded, so just pass regular text.
   * @return TigerfishImage
   * Returns the TigerFish image object for chaining purposes.
   */
  public function fragment($fragment) {
    $this->fragment = urlencode($fragment);
    return $this;
  }

  /**
   * Set image classes for this image tag.
   *
   * @throws InvalidArgumentException
   * @param $array
   * An array of image classes. May be an array of just one element if desired.
   * @return TigerfishImage
   * Returns the TigerFish image object for chaining purposes.
   */
  public function imageClasses($array) {
    if (empty($array) || !is_array($array)) {
      throw new InvalidArgumentException(t('TigerfishImage::imageClasses needs an array as a parameter.'));
    }

    $this->imageClasses = $array;
    return $this;
  }

  /**
   * Sets where the image should link to.
   *
   * This is optional and will cause the image to be surrounded by an <a> tag.
   * If specified, it will cause shadowbox to be turned off for this image.
   *
   * @param string $link
   * The link path as either a Drupal internal path or absolute URL.
   * @return TigerfishImage
   * Returns the TigerFish image object for chaining purposes.
   */
  public function link($link) {
    $this->link = $link;

    return $this;
  }

  /**
   * Adds classes to the link surrounding the image.
   *
   * Note that unless you actually set a link with ->link(), this won't have any
   * effect.
   *
   * @throws InvalidArgumentException
   * @param array $linkClasses
   * An array of link classes. May just be a single-element array if necessary.
   * @return TigerfishImage
   * Returns the TigerFish image object for chaining purposes.
   */
  public function linkClasses($linkClasses) {
    if (empty($linkClasses) || !is_array($linkClasses)) {
      throw new InvalidArgumentException(t('TigerfishImage::linkClasses needs an array as a parameter.'));
    }

    $this->linkClasses = $linkClasses;
    return $this;
  }

  /**
   * Set the path to the image file.
   *
   * This method checks that the file exists, and if not, uses the default
   * image path instead.
   *
   * @param string $path
   * The path to the image. Use a Drupal path, like path_to_theme() . '/foo.jpg'
   * @return TigerfishImage
   * Returns the TigerFish image object for chaining purposes.
   */
  public function path($path) {
    // Get the absolute file system path of our file so we can check if it
    // exists.
    $abspath = drupal_realpath($path);

    if (!file_exists($abspath)) {
      // The file doesn't exist, so use the default instead.
      $path = 'public://' . $this->defaultImage;
      $abspath = drupal_realpath($path);

      if (!file_exists($abspath)) {
        // Ok, so the default file doesn't exist in the Drupal files folder
        // either, so attempt to copy it from the theme. This EXPECTS it to be
        // in the images subfolder of the theme.
        $source = drupal_realpath(drupal_get_path('theme', variable_get('theme_default', 'bartik')) . '/images/default/' . $this->defaultImage);

        if (copy($source, $path) === FALSE) {
          // We didn't find the source image or the copy failed for some reason.
          // We need to bail out here.
          $this->path = NULL;
          throw new Exception(t('The specified image was not found, and the default image was not found at !dpath, and copying it from !orig failed.', array('!dpath' => $path, '!orig' => $source)));
        }
      }
    }

    $size = getimagesize($path);
    $this->imageWidth = $size[0];
    $this->imageHeight = $size[1];

    $this->path = $path;
    $this->url = file_create_url($path);
    return $this;
  }

  /**
   * Set key/value parameters to be placed in the query string.
   *
   * For example for http://example.com/?foo=bar, you would pass into the
   * function array('foo' => 'bar').
   *
   * @param array $qs
   * An array of key/value pairs to be used in the query string.
   * @return TigerfishImage
   * Returns the TigerFish image object for chaining purposes.
   *
   */
  public function queryString($qs) {
    if (empty($qs) || !is_array($qs)) {
      throw new InvalidArgumentException(t('TigerfishImage::queryString requires an array as a parameter'));
    }

    $this->queryString = $qs;
    return $this;
  }

  /**
   * Sets whether to enlarge this image in a shadowbox.
   *
   * If you're doing this, you should set an image style for the non-shadowbox
   * image using ->style(), otherwise the image will be the same size inside and
   * outside of the shadowbox. This will remove any link set on the image, if
   * any.
   *
   * @param bool $shadowbox
   * Whether or not to make this image enlarge in a shadowbox.
   * @return TigerfishImage
   * Returns the TigerFish image object for chaining purposes.
   */
  public function shadowbox($shadowbox) {
    $this->shadowbox = $shadowbox;

    return $this;
  }

  /**
   * Sets the group of images that this image should sit within, for shadowbox.
   *
   * This will cause shadowbox to show 'next' and 'previous' buttons to rotate
   * through images in this group.
   *
   * This is optional.
   *
   * @param string $group
   * The name of the group. Should not contain spaces or special characters.
   * Letters (of upper or lower case) and numbers are fine.
   * @return TigerfishImage
   * Returns the TigerFish image object for chaining purposes.
   */
  public function shadowboxGroup($group) {
    $this->shadowbox = TRUE;
    $this->shadowboxGroup = $group;
    return $this;
  }

  /**
   * Set the image style to be used for processing this image.
   *
   * @param string $style
   * The name of the style.
   * @return TigerfishImage
   * Returns the TigerFish image object for chaining purposes.
   */
  public function style($style) {
    $this->style = $style;
    $this->updateStyleUrl();
    return $this;
  }

  /**
   * Sets the title attribute for the image.
   *
   * Note that if the image is being made into a link, the title will apply to
   * the link tag instead of the image tag.
   *
   * @param string $title
   * The title of the image.
   * @return TigerfishImage
   * Returns the TigerFish image object for chaining purposes.
   */
  public function title($title) {
    $this->title = $title;
    return $this;
  }

  /**
   * Make sure the internal $stylepath variable is present and correct.
   *
   * Used when the path or the style is updated, to make sure that the path
   * to the resized/processed image is still correct.
   *
   * Don't call this until $this->path has been set.
   *
   * @return void
   */
  protected function updateStyleUrl() {
    if ($this->style == NULL) {
      // We can't have a style path if no style is set.
      $this->styleUrl = NULL;
    }
    else {
      $this->styleUrl = image_style_url($this->style, $this->path);
    }
  }

  /**
   * Make sure all the parameters are valid prior to rendering the image.
   *
   * @return void
   */
  protected function validateParameters() {
    // We need to have set alt text here.
    if (empty($this->alt)) {
      throw new InvalidArgumentException(t('Image cannot be rendered: no alt text set.'));
    }
  }

  /**
   * Set the height if shadowbox is being used to show a video.
   *
   * When videos are in use, you must set both videoWidth() and videoHeight() or
   * the image will not be able to render.
   *
   * @param int $height
   * The height of the video, in pixels.
   * @return TigerfishImage
   * Returns the TigerFish image object for chaining purposes.
   */
  public function videoHeight($height) {
    $this->shadowbox = TRUE;
    $this->videoHeight = $height;
    return $this;
  }

  /**
   * Sets the player for shadowbox videos.
   *
   * Defaults to swf.
   *
   * @param string $player
   * The type of player to be used.
   * @return TigerfishImage
   * Returns the TigerFish image object for chaining purposes.
   */
  public function videoPlayer($player) {
    $this->shadowbox = TRUE;
    $this->videoPlayer = $player;
    return $this;
  }

  /**
   * Set the width if shadowbox is being used to show a video.
   *
   * When videos are in use, you must set both videoWidth() and videoHeight() or
   * the image will not be able to render.
   *
   * @param int $width
   * The width of the video, in pixels.
   * @return TigerfishImage
   * Returns the TigerFish image object for chaining purposes.
   */
  public function videoWidth($width) {
    $this->shadowbox = TRUE;
    $this->videoWidth = $width;
    return $this;
  }
}
