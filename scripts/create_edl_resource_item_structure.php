<?php

/**
 * Creates the Resource Item content model used by the EDL Resources page.
 *
 * Run with:
 *   ddev drush php:script scripts/create_edl_resource_item_structure.php
 */

use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\Entity\NodeType;
use Drupal\taxonomy\Entity\Vocabulary;

ensure_vocabulary('resource_category', 'Resource Category');
ensure_vocabulary('resource_type', 'Resource Type');
ensure_node_type('resource_item', 'Resource Item');

ensure_field_storage('node', 'field_resource_category', 'entity_reference', [
  'target_type' => 'taxonomy_term',
], FieldStorageConfig::CARDINALITY_UNLIMITED);
ensure_field_storage('node', 'field_resource_type', 'entity_reference', [
  'target_type' => 'taxonomy_term',
]);
ensure_field_storage('node', 'field_resource_icon', 'image');
ensure_field_storage('node', 'field_resource_link', 'link');
ensure_field_storage('node', 'field_resource_summary', 'string_long');

ensure_field('node', 'resource_item', 'field_resource_category', 'Resource Category', [
  'handler' => 'default:taxonomy_term',
  'handler_settings' => [
    'target_bundles' => ['resource_category' => 'resource_category'],
  ],
]);
ensure_field('node', 'resource_item', 'field_resource_type', 'Resource Type', [
  'handler' => 'default:taxonomy_term',
  'handler_settings' => [
    'target_bundles' => ['resource_type' => 'resource_type'],
  ],
]);
ensure_field('node', 'resource_item', 'field_resource_icon', 'Resource Icon', [
  'file_extensions' => 'png jpg jpeg gif svg',
  'file_directory' => 'resource-icons',
  'alt_field' => TRUE,
  'alt_field_required' => FALSE,
]);
ensure_field('node', 'resource_item', 'field_resource_link', 'Resource Link', [
  'link_type' => 17,
  'title' => DRUPAL_OPTIONAL,
]);
ensure_field('node', 'resource_item', 'field_resource_summary', 'Resource Summary');

ensure_form_display();
ensure_view_display();

print "Resource Item structure is ready.\n";

/**
 * Ensures a taxonomy vocabulary exists.
 */
function ensure_vocabulary(string $vid, string $label): void {
  if (!Vocabulary::load($vid)) {
    Vocabulary::create([
      'vid' => $vid,
      'name' => $label,
    ])->save();
  }
}

/**
 * Ensures a node type exists.
 */
function ensure_node_type(string $type, string $label): void {
  if (!NodeType::load($type)) {
    NodeType::create([
      'type' => $type,
      'name' => $label,
    ])->save();
  }
}

/**
 * Ensures field storage exists.
 */
function ensure_field_storage(string $entity_type, string $field_name, string $type, array $settings = [], int $cardinality = 1): void {
  if (!FieldStorageConfig::loadByName($entity_type, $field_name)) {
    FieldStorageConfig::create([
      'field_name' => $field_name,
      'entity_type' => $entity_type,
      'type' => $type,
      'settings' => $settings,
      'cardinality' => $cardinality,
    ])->save();
  }
}

/**
 * Ensures a field instance exists.
 */
function ensure_field(string $entity_type, string $bundle, string $field_name, string $label, array $settings = []): void {
  if (!FieldConfig::loadByName($entity_type, $bundle, $field_name)) {
    FieldConfig::create([
      'field_name' => $field_name,
      'entity_type' => $entity_type,
      'bundle' => $bundle,
      'label' => $label,
      'settings' => $settings,
    ])->save();
  }
}

/**
 * Configures the content edit form.
 */
function ensure_form_display(): void {
  $display = EntityFormDisplay::load('node.resource_item.default') ?: EntityFormDisplay::create([
    'targetEntityType' => 'node',
    'bundle' => 'resource_item',
    'mode' => 'default',
    'status' => TRUE,
  ]);

  $display
    ->setComponent('title', ['type' => 'string_textfield', 'weight' => -5])
    ->setComponent('field_resource_category', ['type' => 'options_select', 'weight' => 1])
    ->setComponent('field_resource_type', ['type' => 'options_select', 'weight' => 2])
    ->setComponent('field_resource_icon', ['type' => 'image_image', 'weight' => 3])
    ->setComponent('field_resource_summary', ['type' => 'string_textarea', 'weight' => 4])
    ->setComponent('field_resource_link', ['type' => 'link_default', 'weight' => 5])
    ->save();
}

/**
 * Configures the default node display.
 */
function ensure_view_display(): void {
  $display = EntityViewDisplay::load('node.resource_item.default') ?: EntityViewDisplay::create([
    'targetEntityType' => 'node',
    'bundle' => 'resource_item',
    'mode' => 'default',
    'status' => TRUE,
  ]);

  $display
    ->setComponent('field_resource_icon', ['type' => 'image', 'weight' => 0])
    ->setComponent('field_resource_category', ['type' => 'entity_reference_label', 'weight' => 1])
    ->setComponent('field_resource_type', ['type' => 'entity_reference_label', 'weight' => 2])
    ->setComponent('field_resource_summary', ['type' => 'basic_string', 'weight' => 3])
    ->setComponent('field_resource_link', ['type' => 'link', 'weight' => 4])
    ->save();
}
