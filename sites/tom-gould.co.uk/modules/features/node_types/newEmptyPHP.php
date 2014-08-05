<?php

// Exported field: 'node-service-field_tags'
$fields['node-service-field_tags'] = array(
  'field_config'   => array(
    'active'       => '1',
    'cardinality'  => '-1',
    'deleted'      => '0',
    'entity_types' => array(),
    'field_name'   => 'field_tags',
    'foreign keys' => array(
      'tid' => array(
        'columns' => array(
          'tid' => 'tid',
        ),
        'table'   => 'taxonomy_term_data',
      ),
    ),
    'indexes'      => array(
      'tid' => array(
        0 => 'tid',
      ),
    ),
    'module'       => 'taxonomy',
    'settings'     => array(
      'allowed_values' => array(
        0 => array(
          'vocabulary' => 'tags',
          'parent'     => '0',
        ),
      ),
    ),
    'translatable' => '0',
    'type'         => 'taxonomy_term_reference',
  ),
  'field_instance' => array(
    'bundle'        => 'service',
    'default_value' => NULL,
    'deleted'       => '0',
    'description'   => '',
    'display'       => array(
      'default' => array(
        'label'    => 'above',
        'module'   => 'taxonomy',
        'settings' => array(),
        'type'     => 'taxonomy_term_reference_link',
        'weight'   => 2,
      ),
      'teaser'  => array(
        'label'    => 'above',
        'settings' => array(),
        'type'     => 'hidden',
        'weight'   => 0,
      ),
    ),
    'entity_type'   => 'node',
    'field_name'    => 'field_tags',
    'label'         => 'Tags',
    'required'      => 0,
    'settings'      => array(
      'user_register_form' => FALSE,
    ),
    'widget'        => array(
      'active'   => 0,
      'module'   => 'taxonomy',
      'settings' => array(
        'autocomplete_path' => 'taxonomy/autocomplete',
        'size'              => 60,
      ),
      'type'     => 'taxonomy_autocomplete',
      'weight'   => '4',
    ),
  ),
);
