<?php
/**
 * @file
 * seo_page_tagger_rule.rules_defaults.inc
 */

/**
 * Implements hook_default_rules_configuration().
 */
function seo_page_tagger_rule_default_rules_configuration() {
  $items = array();
  $items['rules_tag_seo_node_body'] = entity_import('rules_config', '{ "rules_tag_seo_node_body" : {
      "LABEL" : "tag_seo_node_body",
      "PLUGIN" : "reaction rule",
      "OWNER" : "rules",
      "REQUIRES" : [ "rules" ],
      "ON" : { "node_presave--seo_page" : { "bundle" : "seo_page" } },
      "IF" : [
        { "node_is_of_type" : {
            "node" : [ "node" ],
            "type" : { "value" : { "seo_page" : "seo_page" } }
          }
        }
      ],
      "DO" : [
        { "component_rules_autotag_tag" : {
            "text" : [ "node:body:value" ],
            "vocabulary" : "terms",
            "target_terms" : [ "node:field-terms" ]
          }
        }
      ]
    }
  }');
  return $items;
}
