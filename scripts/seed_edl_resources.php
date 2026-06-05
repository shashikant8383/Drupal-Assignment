<?php

/**
 * Seeds Resource Item content from the live EDL resources page data.
 *
 * Run with:
 *   ddev drush php:script scripts/seed_edl_resources.php
 *
 * On Pantheon, after this script and assets are pushed:
 *   drush php:script scripts/seed_edl_resources.php
 */

use Drupal\file\Entity\File;
use Drupal\Core\File\FileSystemInterface;
use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\Entity\Vocabulary;

$root = dirname(__DIR__);
$data_file = $root . '/assets/edl-resources/resources.json';
$icon_dir = $root . '/assets/edl-resources/resource-icons';

if (!file_exists($data_file)) {
  throw new RuntimeException("Missing resource data file: {$data_file}");
}

$resources = json_decode(file_get_contents($data_file), TRUE);
if (!is_array($resources)) {
  throw new RuntimeException("Could not parse resource data file: {$data_file}");
}

ensure_vocabulary('resource_category', 'Resource Category');
ensure_vocabulary('resource_type', 'Resource Type');

$file_system = \Drupal::service('file_system');
$file_system->prepareDirectory('public://edl-resources/icons', FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);

$created = 0;
$updated = 0;

foreach ($resources as $resource) {
  $title = trim($resource['title'] ?? '');
  if ($title === '') {
    continue;
  }

  $category_tid = ensure_term('resource_category', $resource['category'] ?? 'General');
  $type_tid = ensure_term('resource_type', $resource['type'] ?? 'External Website');
  $icon_target_id = NULL;

  if (!empty($resource['iconFile'])) {
    $source = $icon_dir . '/' . $resource['iconFile'];
    if (file_exists($source)) {
      $icon_target_id = ensure_file_entity($source, 'public://edl-resources/icons/' . $resource['iconFile']);
    }
  }

  $nodes = \Drupal::entityTypeManager()
    ->getStorage('node')
    ->loadByProperties([
      'type' => 'resource_item',
      'title' => $title,
    ]);

  $node = $nodes ? reset($nodes) : Node::create([
    'type' => 'resource_item',
    'title' => $title,
    'status' => 1,
  ]);

  $node->set('field_resource_category', [['target_id' => $category_tid]]);
  $node->set('field_resource_type', [['target_id' => $type_tid]]);
  $node->set('field_resource_link', [
    [
      'uri' => $resource['href'] ?? 'https://edl.asu.edu/resources/',
      'title' => 'View Resource',
    ],
  ]);
  $node->set('field_resource_summary', '');

  if ($icon_target_id) {
    $node->set('field_resource_icon', [
      [
        'target_id' => $icon_target_id,
        'alt' => $resource['iconAlt'] ?: $title,
      ],
    ]);
  }

  $node->save();
  $nodes ? $updated++ : $created++;
}

\Drupal::service('cache_tags.invalidator')->invalidateTags(['node_list', 'taxonomy_term_list']);

print "Resource seed complete. Created {$created}, updated {$updated}.\n";

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
 * Ensures a taxonomy term exists and returns its term ID.
 */
function ensure_term(string $vid, string $name): int {
  $name = trim($name) ?: 'General';
  $terms = \Drupal::entityTypeManager()
    ->getStorage('taxonomy_term')
    ->loadByProperties([
      'vid' => $vid,
      'name' => $name,
    ]);

  if ($terms) {
    return (int) reset($terms)->id();
  }

  $term = Term::create([
    'vid' => $vid,
    'name' => $name,
  ]);
  $term->save();

  return (int) $term->id();
}

/**
 * Copies an asset into public files and returns its file entity ID.
 */
function ensure_file_entity(string $source, string $destination_uri): int {
  $existing = \Drupal::entityTypeManager()
    ->getStorage('file')
    ->loadByProperties(['uri' => $destination_uri]);

  if ($existing) {
    return (int) reset($existing)->id();
  }

  $file_system = \Drupal::service('file_system');
  $file_system->copy($source, $destination_uri, FileSystemInterface::EXISTS_REPLACE);

  $file = File::create([
    'uri' => $destination_uri,
    'status' => 1,
  ]);
  $file->save();

  return (int) $file->id();
}
