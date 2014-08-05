<?php
/**
 * @file
 * autotag_rules.rules_defaults.inc
 */

/**
 * Implements hook_default_rules_configuration().
 */
function autotag_rules_default_rules_configuration() {
  $items = array();
  $items['rules_tag_about_us_fields'] = entity_import('rules_config', '{ "rules_tag_about_us_fields" : {
      "LABEL" : "tag_about_us_fields",
      "PLUGIN" : "reaction rule",
      "ACTIVE" : false,
      "OWNER" : "rules",
      "REQUIRES" : [ "rules" ],
      "ON" : { "node_presave--about_us" : { "bundle" : "about_us" } },
      "IF" : [
        { "node_is_of_type" : {
            "node" : [ "node" ],
            "type" : { "value" : { "about_us" : "about_us" } }
          }
        }
      ],
      "DO" : [
        { "component_rules_autotag_tag" : {
            "text" : [ "node:field-column-one:value" ],
            "vocabulary" : "tags",
            "target_terms" : [ "node:field-tags" ]
          }
        }
      ]
    }
  }');
  $items['rules_tag_node_blog_body'] = entity_import('rules_config', '{ "rules_tag_node_blog_body" : {
      "LABEL" : "tag_node_body",
      "PLUGIN" : "reaction rule",
      "OWNER" : "rules",
      "REQUIRES" : [ "rules" ],
      "ON" : {
        "node_presave--blog" : { "bundle" : "blog" },
        "node_presave--news" : { "bundle" : "news" },
        "node_presave--page" : { "bundle" : "page" },
        "node_presave--service" : { "bundle" : "service" }
      },
      "IF" : [
        { "node_is_of_type" : {
            "node" : [ "node" ],
            "type" : { "value" : {
                "blog" : "blog",
                "news" : "news",
                "page" : "page",
                "service" : "service"
              }
            }
          }
        }
      ],
      "DO" : [
        { "component_rules_autotag_tag" : {
            "text" : [ "node:body:value" ],
            "vocabulary" : "tags",
            "target_terms" : [ "node:field-tags" ]
          }
        }
      ]
    }
  }');
  return $items;
}
