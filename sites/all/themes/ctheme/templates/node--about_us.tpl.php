<?php
/**
 * @file node--about-us.tpl.php
 * Main node template for About us nodes.
 */
?>
<?php print render($content['body']); ?>

<div class="about-features">
  <div class="about-feature">
    <?php print render($content['field_column_one']); ?>
  </div>

  <div class="about-feature">
    <?php print render($content['field_column_two']); ?>
  </div>

  <div class="about-feature last">
    <?php print render($content['field_column_three']); ?>
  </div>

</div>
