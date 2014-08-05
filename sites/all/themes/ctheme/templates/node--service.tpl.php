<?php
/**
 * @file node--service.tpl.php
 * Main node template for service nodes.
 */

//krumo($node);
?>
<?php if (empty($node->view->name) || $node->view->name != 'service'): ?>
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
    if (!empty($node->field_image[LANGUAGE_NONE][0]['uri'])) {
      $img = new TigerfishImage();
      $img->style('medium') // optional
        ->path($node->field_image[LANGUAGE_NONE][0]['uri'])
        ->alt($node->title)
        ->title($node->title)
        ->imageClasses(array('masked', 'left'));
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
    print render($content);
    ?>
  </div>

  <div class="clearfix">
    <?php if (!empty($content['links'])): ?>
    <div class="links"><?php print render($content['links']); ?></div>
    <?php endif; ?>

    <?php print render($content['comments']); ?>
  </div>

</div>
<?php endif ?>


<?php if (!empty($node->view) && $node->view->name == 'service'): ?>
  <?php
  if (!empty($node->field_image[LANGUAGE_NONE][0]['uri'])) {
    $img = new TigerfishImage();
    $img->style('service-small') // optional
      ->path($node->field_image[LANGUAGE_NONE][0]['uri'])
      ->alt($node->title)
      ->title($node->title)
      ->imageClasses(array('service-image-small'));
    $img->link('node/' . $node->nid);
    $img = $img->getImageTag();
  }

  // We hide the comments and links now so that we can render them later.
  hide($content['comments']);
  hide($content['links']);
  ?>
  <div class="<?php print ($node->view->row_index + 1)%2 ? 'service-left' : 'service-right'; ?>">
    <h2>
      <?php
        print l($title, 'node/' . $node->nid, array('attributes' => array(
          'title' => $title
        )))
      ?>
    </h2>
    <?php print !empty($img) ? $img : NULL; ?>
    <?php print render($content); ?>
    <div style="clear:both; padding:10px 0 0 0; text-align:right;">
      <?php
        print l(t('More info'), 'node/' . $node->nid, array('attributes' => array(
          'title' => t('More info on ') . $title, 'class' => array('more')
        )))
      ?>
    </div>
  </div>
<?php if (!($node->view->row_index + 1)%2): ?>
 </div>
<?php endif ?>
<?php endif ?>