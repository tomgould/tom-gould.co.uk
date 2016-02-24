<div id="<?php print $block_html_id; ?>" class="<?php print $classes; ?>"<?php print $attributes; ?>>

<?php print caching_cache_render($title_prefix); ?>
<?php if ($block->subject && 1 == 2): ?>
<h2<?php print $title_attributes; ?>><?php print $block->subject ?></h2>
<?php endif;?>
<?php print caching_cache_render($title_suffix); ?>

<div class="content"<?php print $content_attributes; ?>>
<?php print $content ?>
</div>
</div>