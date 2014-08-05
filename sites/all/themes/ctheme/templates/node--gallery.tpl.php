<?php
/**
 * @file node--gallery.tpl.php
 * Main node template for gallery nodes.
 */
?>
<?php if (empty($node->view->name) || $node->view->name != 'product'): ?>
<div id="node-<?php print $node->nid; ?>" class="<?php print $classes; ?>"<?php print $attributes; ?>>

  <?php print render($title_prefix); ?>
  <?php if (!$page): ?>
  <h2<?php print $title_attributes; ?>>
    <?php
      print l($title, 'node/' . $node->nid, array('attributes' => array(
        'title' => $title
      )))
    ?>
  </h2>
  <?php endif; ?>
  <?php print render($title_suffix); ?>

  <?php if ($display_submitted): ?>
  <div class="submitted"><?php print $submitted ?></div>
  <?php endif; ?>

  <div class="content clearfix"<?php print $content_attributes; ?>>

    <?php
    // We hide the comments and links now so that we can render them later.
    hide($content['comments']);
    hide($content['links']);
    print render($content);
    ?>
  </div>

  <?php if (!empty($node->field_images[LANGUAGE_NONE])):?>
    <?php foreach($node->field_images[LANGUAGE_NONE] as $key => $value): ?>
      <div class="product<?php print ($key + 1)%3 ? NULL : ' last'; ?>">
        <?php
        if (!empty($value['uri'])) {
          $img = new TigerfishImage();
          $img->style('product-small') // optional
            ->path($value['uri'])
            ->alt($value['alt'])
            ->title($value['alt'])
            ->imageClasses(array('masked'));
          $img->shadowboxGroup('group1');
          $img = $img->getImageTag();
          print $img;
        }
        ?>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>

  <div class="clearfix clear">
    <?php if (!empty($content['links'])): ?>
    <div class="links"><?php print render($content['links']); ?></div>
    <?php endif; ?>

    <?php print render($content['comments']); ?>
  </div>

</div>
<?php endif ?>

