<?php

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\Entity\NodeType;
use Drupal\paragraphs\Entity\ParagraphsType;

$content_type_id = 'edl_home_page';
$paragraph_types = [
  'hero_section' => 'Hero Section',
  'who_we_are_section' => 'Who We Are Section',
  'mission_section' => 'Mission Section',
  'what_we_do_section' => 'What We Do Section',
  'studio_cta_section' => 'Studio CTA Section',
  'explore_edl_section' => 'Explore EDL Section',
];

if (!NodeType::load($content_type_id)) {
  throw new RuntimeException("Content type {$content_type_id} does not exist. Create EDL Home Page first.");
}

foreach ($paragraph_types as $id => $label) {
  if (!ParagraphsType::load($id)) {
    ParagraphsType::create([
      'id' => $id,
      'label' => $label,
      'description' => '',
      'behavior_plugins' => [],
    ])->save();
    echo "Created paragraph type: {$label}\n";
  }
  else {
    echo "Paragraph type already exists: {$label}\n";
  }
}

if (!FieldStorageConfig::loadByName('node', 'field_sections')) {
  FieldStorageConfig::create([
    'field_name' => 'field_sections',
    'entity_type' => 'node',
    'type' => 'entity_reference_revisions',
    'cardinality' => FieldStorageConfig::CARDINALITY_UNLIMITED,
    'settings' => [
      'target_type' => 'paragraph',
    ],
  ])->save();
  echo "Created field storage: field_sections\n";
}
else {
  echo "Field storage already exists: field_sections\n";
}

if (!FieldConfig::loadByName('node', $content_type_id, 'field_sections')) {
  $target_bundles_drag_drop = [];
  foreach (array_keys($paragraph_types) as $weight => $id) {
    $target_bundles_drag_drop[$id] = [
      'enabled' => TRUE,
      'weight' => $weight,
    ];
  }

  FieldConfig::create([
    'field_name' => 'field_sections',
    'entity_type' => 'node',
    'bundle' => $content_type_id,
    'label' => 'Page sections',
    'required' => FALSE,
    'settings' => [
      'handler' => 'default:paragraph',
      'handler_settings' => [
        'target_bundles' => array_combine(array_keys($paragraph_types), array_keys($paragraph_types)),
        'negate' => 0,
        'target_bundles_drag_drop' => $target_bundles_drag_drop,
      ],
    ],
  ])->save();
  echo "Created field config: Page sections on EDL Home Page\n";
}
else {
  echo "Field config already exists: Page sections on EDL Home Page\n";
}

$form_display = \Drupal::service('entity_display.repository')
  ->getFormDisplay('node', $content_type_id);
$form_display->setComponent('field_sections', [
  'type' => 'paragraphs',
  'weight' => 10,
  'settings' => [
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
  ],
])->save();
echo "Updated form display for field_sections\n";

$view_display = \Drupal::service('entity_display.repository')
  ->getViewDisplay('node', $content_type_id);
$view_display->setComponent('field_sections', [
  'type' => 'entity_reference_revisions_entity_view',
  'label' => 'hidden',
  'weight' => 10,
  'settings' => [
    'view_mode' => 'default',
  ],
])->save();
echo "Updated view display for field_sections\n";

