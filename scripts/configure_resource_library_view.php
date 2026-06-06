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
$options['exposed_form'] = [
  'type' => 'basic',
  'options' => [
    'submit_button' => 'Apply',
    'reset_button' => TRUE,
    'reset_button_label' => 'Reset',
    'exposed_sorts_label' => 'Sort by',
    'expose_sort_order' => TRUE,
    'sort_asc_label' => 'Asc',
    'sort_desc_label' => 'Desc',
  ],
];
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
      'label' => 'Title',
      'field_identifier' => 'title',
    ],
    'exposed' => TRUE,
  ],
  'created' => [
    'id' => 'created',
    'table' => 'node_field_data',
    'field' => 'created',
    'relationship' => 'none',
    'group_type' => 'group',
    'admin_label' => '',
    'entity_type' => 'node',
    'entity_field' => 'created',
    'plugin_id' => 'date',
    'order' => 'DESC',
    'expose' => [
      'label' => 'Newest',
      'field_identifier' => 'created',
    ],
    'exposed' => TRUE,
    'granularity' => 'second',
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
$options['filters'] = resource_library_filters($options['filters'] ?? []);

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

/**
 * Adds the Resource Library exposed filters while keeping required filters.
 */
function resource_library_filters(array $existing_filters): array {
  $filters = [
    'status' => $existing_filters['status'] ?? [
      'id' => 'status',
      'table' => 'node_field_data',
      'field' => 'status',
      'entity_type' => 'node',
      'entity_field' => 'status',
      'plugin_id' => 'boolean',
      'value' => '1',
      'group' => 1,
      'expose' => [
        'operator' => '',
      ],
    ],
    'type' => $existing_filters['type'] ?? [
      'id' => 'type',
      'table' => 'node_field_data',
      'field' => 'type',
      'entity_type' => 'node',
      'entity_field' => 'type',
      'plugin_id' => 'bundle',
      'value' => [
        'resource_item' => 'resource_item',
      ],
    ],
  ];

  $filters['title'] = [
    'id' => 'title',
    'table' => 'node_field_data',
    'field' => 'title',
    'relationship' => 'none',
    'group_type' => 'group',
    'admin_label' => '',
    'entity_type' => 'node',
    'entity_field' => 'title',
    'plugin_id' => 'string',
    'operator' => 'contains',
    'value' => '',
    'group' => 1,
    'exposed' => TRUE,
    'expose' => exposed_filter_options('Search', 'search', 'title_op'),
    'is_grouped' => FALSE,
    'group_info' => grouped_filter_options('Search', 'search'),
  ];

  $filters['field_resource_category_target_id'] = taxonomy_filter_options(
    'field_resource_category_target_id',
    'node__field_resource_category',
    'Category',
    'category',
    'resource_category'
  );

  $filters['field_resource_type_target_id'] = taxonomy_filter_options(
    'field_resource_type_target_id',
    'node__field_resource_type',
    'Resource Type',
    'resource_type',
    'resource_type'
  );

  return $filters;
}

/**
 * Builds taxonomy entity-reference filter options.
 */
function taxonomy_filter_options(string $field, string $table, string $label, string $identifier, string $vocabulary): array {
  return [
    'id' => $field,
    'table' => $table,
    'field' => $field,
    'relationship' => 'none',
    'group_type' => 'group',
    'admin_label' => '',
    'plugin_id' => 'taxonomy_index_tid',
    'operator' => 'or',
    'value' => [],
    'group' => 1,
    'exposed' => TRUE,
    'expose' => exposed_filter_options($label, $identifier, $field . '_op', TRUE),
    'is_grouped' => FALSE,
    'group_info' => grouped_filter_options($label, $identifier),
    'reduce_duplicates' => FALSE,
    'vid' => $vocabulary,
    'type' => 'select',
    'hierarchy' => FALSE,
    'limit' => TRUE,
    'error_message' => TRUE,
  ];
}

/**
 * Returns common exposed filter settings.
 */
function exposed_filter_options(string $label, string $identifier, string $operator_id, bool $multiple = FALSE): array {
  return [
    'operator_id' => $operator_id,
    'label' => $label,
    'description' => '',
    'use_operator' => FALSE,
    'operator' => $operator_id,
    'operator_limit_selection' => FALSE,
    'operator_list' => [],
    'identifier' => $identifier,
    'required' => FALSE,
    'remember' => FALSE,
    'multiple' => $multiple,
    'remember_roles' => [
      'authenticated' => 'authenticated',
      'anonymous' => '0',
      'administrator' => '0',
    ],
    'reduce' => FALSE,
  ];
}

/**
 * Returns empty grouped-filter settings.
 */
function grouped_filter_options(string $label, string $identifier): array {
  return [
    'label' => $label,
    'description' => '',
    'identifier' => $identifier,
    'optional' => TRUE,
    'widget' => 'select',
    'multiple' => FALSE,
    'remember' => FALSE,
    'default_group' => 'All',
    'default_group_multiple' => [],
    'group_items' => [],
  ];
}
