<?php
/**
 * @file node--product.tpl.php
 * Main node template for product nodes.
 */

//krumo($node);
?>
<?php if (empty($node->view->name) || $node->view->name != 'product'): ?>
<div id="node-<?php print $node->nid; ?>" class="<?php print $classes; ?>"<?php print $attributes; ?>>

  <?php print caching_cache_render($title_prefix); ?>
  <?php if (!$page): ?>
  <h2<?php print $title_attributes; ?>>
    <?php
      print l($title, 'node/' . $node->nid, array('attributes' => array(
        'title' => $title
      )))
    ?>
  </h2>
  <?php endif; ?>
  <?php print caching_cache_render($title_suffix); ?>

  <?php if ($display_submitted): ?>
  <div class="submitted"><?php print $submitted ?></div>
  <?php endif; ?>

  <div class="content clearfix"<?php print $content_attributes; ?>>

  <?php
    if (!empty($node->field_image[LANGUAGE_NONE][0]['uri'])) {
      $img = new TigerfishImage();
      $img->style('medium') // optional
        ->path($node->field_image[LANGUAGE_NONE][0]['uri'])
        ->alt($node->title)
        ->title($node->title)
        ->imageClasses(array('masked', 'right'));
      if ($page === TRUE) {
        $img->shadowboxGroup('group1');
      }
      else {
        $img->link('node/' . $node->nid);
      }
      $img = $img->getImageTag();
      print $img;
    }

    // We hide the comments and links now so that we can render them later.
    hide($content['comments']);
    hide($content['links']);
    print caching_cache_render($content);
    ?>
  </div>

  <div class="clearfix">
    <?php if (!empty($content['links'])): ?>
    <div class="links"><?php print caching_cache_render($content['links']); ?></div>
    <?php endif; ?>

    <?php print caching_cache_render($content['comments']); ?>
  </div>

</div>
<?php endif ?>


<?php if (!empty($node->view) && $node->view->name == 'product'): ?>
<div class="product<?php print ($node->view->row_index + 1)%3 ? NULL : ' last'; ?>">
  <?php
  if (!empty($node->field_image[LANGUAGE_NONE][0]['uri'])) {
    $img = new TigerfishImage();
    $img->style('product-small') // optional
      ->path($node->field_image[LANGUAGE_NONE][0]['uri'])
      ->alt($node->title)
      ->title($node->title)
      ->imageClasses(array('masked'));
    $img->link('node/' . $node->nid);
    $img = $img->getImageTag();
    print $img;
  }

  // We hide the comments and links now so that we can render them later.
  hide($content['comments']);
  hide($content['links']);
  ?>
  <h2>
    <?php
      print l($title, 'node/' . $node->nid, array('attributes' => array(
        'title' => $title
      )))
    ?>
  </h2>
  <?php print caching_cache_render($content); ?>
  <div style="clear:both; padding:20px 0 0 0;">
    <?php print l(t('More info'), 'node/' . $node->nid, array('attributes' => array('class' => array('more')))); ?>
  </div>
</div>
<?php endif ?>