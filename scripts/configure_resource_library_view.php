<?php

/**
 * Configures the Resource Library view to display Resource Item fields.
 *
 * Run with:
 *   ddev drush php:script scripts/configure_resource_library_view.php
 */

use Drupal\views\Entity\View;

$view = View::load('resource_library');
if (!$view) {
  throw new RuntimeException('The resource_library view does not exist.');
}

$display = &$view->getDisplay('default');
$options = $display['display_options'];

$options['title'] = 'Resource Library';
$options['style'] = [
  'type' => 'default',
  'options' => [
    'grouping' => [],
    'row_class' => '',
    'default_row_class' => TRUE,
    'uses_fields' => TRUE,
  ],
];
$options['row'] = [
  'type' => 'fields',
  'options' => [
    'default_field_elements' => TRUE,
    'inline' => [],
    'separator' => '',
    'hide_empty' => FALSE,
  ],
];
$options['pager']['options']['items_per_page'] = 50;
$options['sorts'] = [
  'title' => [
    'id' => 'title',
    'table' => 'node_field_data',
    'field' => 'title',
    'relationship' => 'none',
    'group_type' => 'group',
    'admin_label' => '',
    'entity_type' => 'node',
    'entity_field' => 'title',
    'plugin_id' => 'standard',
    'order' => 'ASC',
    'expose' => [
      'label' => '',
      'field_identifier' => '',
    ],
    'exposed' => FALSE,
  ],
];
$options['fields'] = [
  'field_resource_icon' => resource_field('field_resource_icon', 'Resource Icon', 'image', [
    'image_style' => '',
    'image_link' => '',
    'image_loading' => [
      'attribute' => 'lazy',
    ],
  ]),
  'title' => [
    'id' => 'title',
    'table' => 'node_field_data',
    'field' => 'title',
    'relationship' => 'none',
    'group_type' => 'group',
    'admin_label' => '',
    'entity_type' => 'node',
    'entity_field' => 'title',
    'plugin_id' => 'field',
    'label' => 'Title',
    'exclude' => FALSE,
    'alter' => base_alter_options(),
    'element_type' => '',
    'element_class' => '',
    'element_label_type' => '',
    'element_label_class' => '',
    'element_label_colon' => TRUE,
    'element_wrapper_type' => '',
    'element_wrapper_class' => '',
    'element_default_classes' => TRUE,
    'empty' => '',
    'hide_empty' => FALSE,
    'empty_zero' => FALSE,
    'hide_alter_empty' => TRUE,
    'click_sort_column' => 'value',
    'type' => 'string',
    'settings' => [
      'link_to_entity' => TRUE,
    ],
    'group_column' => 'value',
    'group_columns' => [],
    'group_rows' => TRUE,
    'delta_limit' => 0,
    'delta_offset' => 0,
    'delta_reversed' => FALSE,
    'delta_first_last' => FALSE,
    'multi_type' => 'separator',
    'separator' => ', ',
    'field_api_classes' => FALSE,
  ],
  'field_resource_category' => resource_field('field_resource_category', 'Resource Category', 'entity_reference_label', [
    'link' => FALSE,
  ]),
  'field_resource_type' => resource_field('field_resource_type', 'Resource Type', 'entity_reference_label', [
    'link' => FALSE,
  ]),
  'field_resource_link' => resource_field('field_resource_link', 'Resource Link', 'link', [
    'trim_length' => 80,
    'url_only' => FALSE,
    'url_plain' => FALSE,
    'rel' => '',
    'target' => '_blank',
  ]),
  'field_resource_summary' => resource_field('field_resource_summary', 'Resource Summary', 'basic_string', []),
];

$display['display_options'] = $options;
$page = &$view->getDisplay('page_1');
$page['display_options']['path'] = 'resources';
$page['display_options']['display_extenders'] = $page['display_options']['display_extenders'] ?? [];

$view->save();
\Drupal::service('cache_tags.invalidator')->invalidateTags(['config:views.view.resource_library']);

print "Resource Library view fields are configured.\n";

/**
 * Builds common Views field options for Resource Item fields.
 */
function resource_field(string $field_name, string $label, string $formatter, array $settings): array {
  return [
    'id' => $field_name,
    'table' => 'node__' . $field_name,
    'field' => $field_name,
    'relationship' => 'none',
    'group_type' => 'group',
    'admin_label' => '',
    'plugin_id' => 'field',
    'label' => $label,
    'exclude' => FALSE,
    'alter' => base_alter_options(),
    'element_type' => '',
    'element_class' => '',
    'element_label_type' => '',
    'element_label_class' => '',
    'element_label_colon' => TRUE,
    'element_wrapper_type' => '',
    'element_wrapper_class' => '',
    'element_default_classes' => TRUE,
    'empty' => '',
    'hide_empty' => FALSE,
    'empty_zero' => FALSE,
    'hide_alter_empty' => TRUE,
    'click_sort_column' => 'target_id',
    'type' => $formatter,
    'settings' => $settings,
    'group_column' => 'target_id',
    'group_columns' => [],
    'group_rows' => TRUE,
    'delta_limit' => 0,
    'delta_offset' => 0,
    'delta_reversed' => FALSE,
    'delta_first_last' => FALSE,
    'multi_type' => 'separator',
    'separator' => ', ',
    'field_api_classes' => FALSE,
  ];
}

/**
 * Returns the default Views alter options.
 */
function base_alter_options(): array {
  return [
    'alter_text' => FALSE,
    'text' => '',
    'make_link' => FALSE,
    'path' => '',
    'absolute' => FALSE,
    'external' => FALSE,
    'replace_spaces' => FALSE,
    'path_case' => 'none',
    'trim_whitespace' => FALSE,
    'alt' => '',
    'rel' => '',
    'link_class' => '',
    'prefix' => '',
    'suffix' => '',
    'target' => '',
    'nl2br' => FALSE,
    'max_length' => 0,
    'word_boundary' => TRUE,
    'ellipsis' => TRUE,
    'more_link' => FALSE,
    'more_link_text' => '',
    'more_link_path' => '',
    'strip_tags' => FALSE,
    'trim' => FALSE,
    'preserve_tags' => '',
    'html' => FALSE,
  ];
}
