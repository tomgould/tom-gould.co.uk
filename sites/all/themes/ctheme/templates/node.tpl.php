<div id="node-<?php print $node->nid; ?>" class="<?php print $classes; ?>"<?php print $attributes; ?>>

  <?php print $user_picture; ?>

  <?php print caching_cache_render($title_prefix); ?>
  <?php if (!$page): ?>
    <h2<?php print $title_attributes; ?>>
      <?php
        l($title, 'node/' . $node->nid, array('attributes' => array(
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