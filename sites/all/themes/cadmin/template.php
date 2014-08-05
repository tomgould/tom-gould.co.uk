<?php
/**
 * Preprocessor for theme('node_form').
 */
function cadmin_preprocess_form_node(&$vars) {
  $vars['sidebar'] = isset($vars['sidebar']) ? $vars['sidebar'] : array();

  $vars['sidebar']['taxonomy'] = $vars['form']['taxonomy'];
  unset($vars['form']['taxonomy']);

}

