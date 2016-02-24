<?php
// $Id: comment-wrapper.tpl.php,v 1.10 2010/05/05 06:41:22 webchick Exp $

/**
 * @file
 * Default theme implementation to provide an HTML container for comments.
 *
 * Available variables:
 * - $content: The array of content-related elements for the node. Use
 *   caching_cache_render($content) to print them all, or
 *   print a subset such as caching_cache_render($content['comment_form']).
 * - $classes: String of classes that can be used to style contextually through
 *   CSS. It can be manipulated through the variable $classes_array from
 *   preprocess functions. The default value has the following:
 *   - comment-wrapper: The current template type, i.e., "theming hook".
 * - $title_prefix (array): An array containing additional output populated by
 *   modules, intended to be displayed in front of the main title tag that
 *   appears in the template.
 * - $title_suffix (array): An array containing additional output populated by
 *   modules, intended to be displayed after the main title tag that appears in
 *   the template.
 *
 * The following variables are provided for contextual information.
 * - $node: Node object the comments are attached to.
 * The constants below the variables show the possible values and should be
 * used for comparison.
 * - $display_mode
 *   - COMMENT_MODE_FLAT
 *   - COMMENT_MODE_THREADED
 *
 * Other variables:
 * - $classes_array: Array of html class attribute values. It is flattened
 *   into a string within the variable $classes.
 *
 * @see template_preprocess_comment_wrapper()
 * @see theme_comment_wrapper()
 */
?>
<div id="comments" class="<?php print $classes; ?>"<?php print $attributes; ?>>
  <?php if ($content['comments'] && $node->type != 'forum'): ?>

    <div id="comments-title">
    
        <div id="comments-title-left">
        <?php print caching_cache_render($title_prefix); ?>
        <h2 class="title"><?php print t('Comments'); ?></h2>
        <?php print caching_cache_render($title_suffix); ?>
        </div>
        
        <div id="comments-title-right">
        <span class="counter"><?php print $node->comment_count; ?></span>
        </div>

    </div>
    
  <?php endif; ?>

  <?php print caching_cache_render($content['comments']); ?>

  <?php if ($content['comment_form']): ?>
    <h2 class="title comment-form"><?php print t('Comment'); ?></h2>
    <?php print caching_cache_render($content['comment_form']); ?>
  <?php endif; ?>
</div>