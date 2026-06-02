<?php

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\Entity\NodeType;
use Drupal\paragraphs\Entity\ParagraphsType;

function ensure_paragraph_type(string $id, string $label): void {
  if (!ParagraphsType::load($id)) {
    ParagraphsType::create([
      'id' => $id,
      'label' => $label,
      'description' => '',
      'behavior_plugins' => [],
    ])->save();
    echo "Created paragraph type: {$label}\n";
    return;
  }

  echo "Paragraph type exists: {$label}\n";
}

function ensure_storage(string $entity_type, string $field_name, string $type, array $settings = [], int $cardinality = 1): void {
  if (FieldStorageConfig::loadByName($entity_type, $field_name)) {
    echo "Field storage exists: {$entity_type}.{$field_name}\n";
    return;
  }

  FieldStorageConfig::create([
    'field_name' => $field_name,
    'entity_type' => $entity_type,
    'type' => $type,
    'cardinality' => $cardinality,
    'settings' => $settings,
  ])->save();
  echo "Created field storage: {$entity_type}.{$field_name}\n";
}

function ensure_field(string $entity_type, string $bundle, string $field_name, string $label, array $settings = []): void {
  if (FieldConfig::loadByName($entity_type, $bundle, $field_name)) {
    echo "Field exists: {$entity_type}.{$bundle}.{$field_name}\n";
    return;
  }

  FieldConfig::create([
    'field_name' => $field_name,
    'entity_type' => $entity_type,
    'bundle' => $bundle,
    'label' => $label,
    'required' => FALSE,
    'settings' => $settings,
  ])->save();
  echo "Created field: {$entity_type}.{$bundle}.{$field_name}\n";
}

function ensure_view_component(string $entity_type, string $bundle, string $field_name, string $type, int $weight, array $settings = []): void {
  $display = \Drupal::service('entity_display.repository')->getViewDisplay($entity_type, $bundle);
  $display->setComponent($field_name, [
    'type' => $type,
    'label' => 'hidden',
    'weight' => $weight,
    'settings' => $settings,
  ])->save();
}

function ensure_form_component(string $entity_type, string $bundle, string $field_name, string $type, int $weight, array $settings = []): void {
  $display = \Drupal::service('entity_display.repository')->getFormDisplay($entity_type, $bundle);
  $display->setComponent($field_name, [
    'type' => $type,
    'weight' => $weight,
    'settings' => $settings,
  ])->save();
}

ensure_paragraph_type('service_card', 'Service Card');
ensure_paragraph_type('explore_link', 'Explore Link');

$storages = [
  ['paragraph', 'field_heading', 'string', ['max_length' => 255], 1],
  ['paragraph', 'field_subheading', 'text_long', [], 1],
  ['paragraph', 'field_background_image', 'image', ['uri_scheme' => 'public', 'default_image' => []], 1],
  ['paragraph', 'field_body', 'text_long', [], 1],
  ['paragraph', 'field_image', 'image', ['uri_scheme' => 'public', 'default_image' => []], 1],
  ['paragraph', 'field_logo', 'image', ['uri_scheme' => 'public', 'default_image' => []], 1],
  ['paragraph', 'field_quote', 'text_long', [], 1],
  ['paragraph', 'field_quote_label', 'string', ['max_length' => 255], 1],
  ['paragraph', 'field_icon', 'image', ['uri_scheme' => 'public', 'default_image' => []], 1],
  ['paragraph', 'field_intro_image', 'image', ['uri_scheme' => 'public', 'default_image' => []], 1],
  ['paragraph', 'field_service_cards', 'entity_reference_revisions', ['target_type' => 'paragraph'], FieldStorageConfig::CARDINALITY_UNLIMITED],
  ['paragraph', 'field_service_title', 'string', ['max_length' => 255], 1],
  ['paragraph', 'field_service_body', 'text_long', [], 1],
  ['paragraph', 'field_service_image', 'image', ['uri_scheme' => 'public', 'default_image' => []], 1],
  ['paragraph', 'field_service_link', 'link', [], 1],
  ['paragraph', 'field_button', 'link', [], 1],
  ['paragraph', 'field_play_icon', 'image', ['uri_scheme' => 'public', 'default_image' => []], 1],
  ['paragraph', 'field_explore_links', 'entity_reference_revisions', ['target_type' => 'paragraph'], FieldStorageConfig::CARDINALITY_UNLIMITED],
  ['paragraph', 'field_link_title', 'string', ['max_length' => 255], 1],
  ['paragraph', 'field_link', 'link', [], 1],
  ['node', 'field_resource_links', 'link', [], FieldStorageConfig::CARDINALITY_UNLIMITED],
];

foreach ($storages as [$entity_type, $field_name, $type, $settings, $cardinality]) {
  ensure_storage($entity_type, $field_name, $type, $settings, $cardinality);
}

$image_settings = [
  'file_directory' => 'edl-homepage/[date:custom:Y]-[date:custom:m]',
  'file_extensions' => 'png gif jpg jpeg webp svg',
  'max_filesize' => '',
  'alt_field' => TRUE,
  'alt_field_required' => TRUE,
  'title_field' => FALSE,
  'title_field_required' => FALSE,
  'default_image' => [],
];
$link_settings = [
  'link_type' => 17,
  'title' => 1,
];
$formatted_text_settings = [];
$service_card_reference_settings = [
  'handler' => 'default:paragraph',
  'handler_settings' => [
    'target_bundles' => ['service_card' => 'service_card'],
    'negate' => 0,
    'target_bundles_drag_drop' => [
      'service_card' => ['enabled' => TRUE, 'weight' => 0],
    ],
  ],
];
$explore_link_reference_settings = [
  'handler' => 'default:paragraph',
  'handler_settings' => [
    'target_bundles' => ['explore_link' => 'explore_link'],
    'negate' => 0,
    'target_bundles_drag_drop' => [
      'explore_link' => ['enabled' => TRUE, 'weight' => 0],
    ],
  ],
];

$fields = [
  ['paragraph', 'hero_section', 'field_heading', 'Heading', []],
  ['paragraph', 'hero_section', 'field_subheading', 'Subheading', $formatted_text_settings],
  ['paragraph', 'hero_section', 'field_background_image', 'Background image', $image_settings],

  ['paragraph', 'who_we_are_section', 'field_heading', 'Heading', []],
  ['paragraph', 'who_we_are_section', 'field_body', 'Body', $formatted_text_settings],
  ['paragraph', 'who_we_are_section', 'field_image', 'Image', $image_settings],
  ['paragraph', 'who_we_are_section', 'field_logo', 'Logo', $image_settings],

  ['paragraph', 'mission_section', 'field_heading', 'Heading', []],
  ['paragraph', 'mission_section', 'field_quote', 'Quote', $formatted_text_settings],
  ['paragraph', 'mission_section', 'field_quote_label', 'Quote label', []],
  ['paragraph', 'mission_section', 'field_background_image', 'Background image', $image_settings],
  ['paragraph', 'mission_section', 'field_icon', 'Icon', $image_settings],

  ['paragraph', 'what_we_do_section', 'field_heading', 'Heading', []],
  ['paragraph', 'what_we_do_section', 'field_intro_image', 'Intro image', $image_settings],
  ['paragraph', 'what_we_do_section', 'field_service_cards', 'Service cards', $service_card_reference_settings],

  ['paragraph', 'service_card', 'field_service_title', 'Service title', []],
  ['paragraph', 'service_card', 'field_service_body', 'Service body', $formatted_text_settings],
  ['paragraph', 'service_card', 'field_service_image', 'Service image', $image_settings],
  ['paragraph', 'service_card', 'field_service_link', 'Service link', $link_settings],

  ['paragraph', 'studio_cta_section', 'field_heading', 'Heading', []],
  ['paragraph', 'studio_cta_section', 'field_body', 'Body', $formatted_text_settings],
  ['paragraph', 'studio_cta_section', 'field_image', 'Image', $image_settings],
  ['paragraph', 'studio_cta_section', 'field_button', 'Button', $link_settings],
  ['paragraph', 'studio_cta_section', 'field_play_icon', 'Play icon', $image_settings],

  ['paragraph', 'explore_edl_section', 'field_heading', 'Heading', []],
  ['paragraph', 'explore_edl_section', 'field_body', 'Body', $formatted_text_settings],
  ['paragraph', 'explore_edl_section', 'field_background_image', 'Background image', $image_settings],
  ['paragraph', 'explore_edl_section', 'field_explore_links', 'Explore links', $explore_link_reference_settings],

  ['paragraph', 'explore_link', 'field_link_title', 'Link title', []],
  ['paragraph', 'explore_link', 'field_link', 'Link', $link_settings],
  ['paragraph', 'explore_link', 'field_icon', 'Icon', $image_settings],
];

foreach ($fields as [$entity_type, $bundle, $field_name, $label, $settings]) {
  ensure_field($entity_type, $bundle, $field_name, $label, $settings);
}

if (NodeType::load('resource_page')) {
  ensure_field('node', 'resource_page', 'field_resource_links', 'Resource links', $link_settings);
}

$widget_map = [
  'string' => 'string_textfield',
  'text_long' => 'text_textarea',
  'image' => 'image_image',
  'link' => 'link_default',
  'entity_reference_revisions' => 'paragraphs',
];
$formatter_map = [
  'string' => 'string',
  'text_long' => 'text_default',
  'image' => 'image',
  'link' => 'link',
  'entity_reference_revisions' => 'entity_reference_revisions_entity_view',
];

$field_types = [];
foreach ($storages as [$entity_type, $field_name, $type]) {
  $field_types[$entity_type . ':' . $field_name] = $type;
}

foreach ($fields as $index => [$entity_type, $bundle, $field_name]) {
  $type = $field_types[$entity_type . ':' . $field_name];
  $widget_settings = [];
  if ($type === 'entity_reference_revisions') {
    $widget_settings = [
      'title' => 'Paragraph',
      'title_plural' => 'Paragraphs',
      'edit_mode' => 'open',
      'closed_mode' => 'summary',
      'autocollapse' => 'none',
      'closed_mode_threshold' => 0,
      'add_mode' => 'dropdown',
      'form_display_mode' => 'default',
      'default_paragraph_type' => '',
      'features' => [
        'add_above' => 'add_above',
        'collapse_edit_all' => 'collapse_edit_all',
        'duplicate' => 'duplicate',
      ],
    ];
  }
  ensure_form_component($entity_type, $bundle, $field_name, $widget_map[$type], $index, $widget_settings);
  ensure_view_component($entity_type, $bundle, $field_name, $formatter_map[$type], $index, $type === 'image' ? ['image_style' => '', 'image_link' => ''] : []);
}

if (NodeType::load('resource_page')) {
  ensure_form_component('node', 'resource_page', 'field_resource_links', 'link_default', 20);
  ensure_view_component('node', 'resource_page', 'field_resource_links', 'link', 20);
}

echo "Finished creating EDL paragraph fields.\n";

