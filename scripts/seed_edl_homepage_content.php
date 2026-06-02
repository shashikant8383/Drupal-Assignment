<?php

use Drupal\Core\File\FileExists;
use Drupal\file\Entity\File;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\path_alias\Entity\PathAlias;

$asset_dir = DRUPAL_ROOT . '/../assets/edl-homepage-images';
$public_dir = 'public://edl-homepage';

if (!is_dir($asset_dir)) {
  throw new RuntimeException("Missing asset directory: {$asset_dir}");
}

\Drupal::service('file_system')->prepareDirectory(
  $public_dir,
  \Drupal\Core\File\FileSystemInterface::CREATE_DIRECTORY | \Drupal\Core\File\FileSystemInterface::MODIFY_PERMISSIONS
);

function edl_file(string $filename, string $alt): array {
  static $cache = [];

  if (isset($cache[$filename])) {
    return [
      'target_id' => $cache[$filename]->id(),
      'alt' => $alt,
    ];
  }

  $source = DRUPAL_ROOT . '/../assets/edl-homepage-images/' . $filename;
  if (!file_exists($source)) {
    throw new RuntimeException("Missing image asset: {$source}");
  }

  $destination = 'public://edl-homepage/' . $filename;
  $file_system = \Drupal::service('file_system');
  $uri = $file_system->copy($source, $destination, FileExists::Replace);

  $existing = \Drupal::entityTypeManager()
    ->getStorage('file')
    ->loadByProperties(['uri' => $uri]);
  $file = $existing ? reset($existing) : File::create(['uri' => $uri]);
  $file->setPermanent();
  $file->save();

  $cache[$filename] = $file;

  return [
    'target_id' => $file->id(),
    'alt' => $alt,
  ];
}

function edl_copy_public_asset(string $filename): void {
  $source = DRUPAL_ROOT . '/../assets/edl-homepage-images/' . $filename;
  if (!file_exists($source)) {
    throw new RuntimeException("Missing image asset: {$source}");
  }

  \Drupal::service('file_system')->copy(
    $source,
    'public://edl-homepage/' . $filename,
    FileExists::Replace
  );
}

foreach ([
  'asu-logo-horizontal.jpg',
  'asu-logo-vertical.jpg',
  'asu-footer-logo.png',
  'footer-rank.png',
  'favicon-2x.png',
] as $public_asset) {
  edl_copy_public_asset($public_asset);
}

function edl_text(string $value): array {
  return [
    'value' => $value,
    'format' => 'basic_html',
  ];
}

function edl_link(string $uri, string $title): array {
  return [
    'uri' => $uri,
    'title' => $title,
  ];
}

function edl_paragraph(string $type, array $values): Paragraph {
  $paragraph = Paragraph::create(['type' => $type] + $values);
  $paragraph->save();
  return $paragraph;
}

function edl_paragraph_ref(Paragraph $paragraph): array {
  return [
    'target_id' => $paragraph->id(),
    'target_revision_id' => $paragraph->getRevisionId(),
  ];
}

function edl_delete_existing_sections(Node $node): void {
  if (!$node->hasField('field_sections') || $node->get('field_sections')->isEmpty()) {
    return;
  }

  foreach ($node->get('field_sections')->referencedEntities() as $paragraph) {
    $paragraph->delete();
  }
  $node->set('field_sections', []);
}

function edl_alias(string $source, string $alias): void {
  $storage = \Drupal::entityTypeManager()->getStorage('path_alias');
  foreach ($storage->loadByProperties(['alias' => $alias, 'langcode' => 'en']) as $existing) {
    $existing->delete();
  }
  foreach ($storage->loadByProperties(['path' => $source, 'langcode' => 'en']) as $existing) {
    $existing->delete();
  }

  PathAlias::create([
    'path' => $source,
    'alias' => $alias,
    'langcode' => 'en',
  ])->save();
}

$service_cards = [
  edl_paragraph('service_card', [
    'field_service_title' => 'Instructional Design',
    'field_service_body' => edl_text('Our team works with instructors to build courses that are inclusive and engaging. We collaborate with you through every step of the course design process. With a student-first mindset, we advocate for inclusive outcome-based learning.'),
    'field_service_image' => edl_file('service-instructional-design.png', 'Instructional design course planning visual'),
    'field_service_link' => edl_link('route:<nolink>', 'Learn more'),
  ]),
  edl_paragraph('service_card', [
    'field_service_title' => 'Academic Media',
    'field_service_body' => edl_text('We create professional-level audio, video and graphic presentations for usage in ASU Online fully online degree programs and EdPlus special projects.'),
    'field_service_image' => edl_file('service-academic-media.png', 'Academic media production visual'),
    'field_service_link' => edl_link('route:<nolink>', 'Learn more'),
  ]),
  edl_paragraph('service_card', [
    'field_service_title' => 'Quality Assurance',
    'field_service_body' => edl_text('Our team promotes student success and satisfaction through excellence in course design, development and delivery. We use evidence-based standards and course data to ensure a high-quality online learning experience.'),
    'field_service_image' => edl_file('service-quality-assurance.png', 'Quality assurance visual'),
    'field_service_link' => edl_link('route:<nolink>', 'Learn more'),
  ]),
  edl_paragraph('service_card', [
    'field_service_title' => 'Learning Technologies',
    'field_service_body' => edl_text('We discover, deploy and support technologies that expand access to higher education, extend our competitive advantage and reinforce our brand.'),
    'field_service_image' => edl_file('service-learning-technologies.png', 'Learning technologies visual'),
    'field_service_link' => edl_link('internal:/learning-technologies', 'Learn more'),
  ]),
  edl_paragraph('service_card', [
    'field_service_title' => 'Professional Development and Training',
    'field_service_body' => edl_text('We advance online instruction by providing opportunities for faculty to experience and share strategies while exploring new research-based and technology-supported methodologies.'),
    'field_service_image' => edl_file('service-professional-development-training.png', 'Professional development and training visual'),
    'field_service_link' => edl_link('route:<nolink>', 'Learn more'),
  ]),
];

$explore_links = [
  ['Instructional Design', 'route:<nolink>'],
  ['Academic Media', 'route:<nolink>'],
  ['Quality Assurance', 'route:<nolink>'],
  ['Learning Technologies', 'internal:/learning-technologies'],
  ['Professional development and training', 'route:<nolink>'],
  ['Book a studio appointment', 'route:<nolink>'],
  ['Resource library', 'internal:/resources'],
  ['Contact us', 'route:<nolink>'],
];
$explore_paragraphs = [];
foreach ($explore_links as [$title, $uri]) {
  $explore_paragraphs[] = edl_paragraph('explore_link', [
    'field_link_title' => $title,
    'field_link' => edl_link($uri, $title),
    'field_icon' => edl_file('arrow-right-solid.svg', 'Arrow icon'),
  ]);
}

$sections = [
  edl_paragraph('hero_section', [
    'field_heading' => 'EdPlus Digital Learning (EDL)',
    'field_subheading' => edl_text('The power behind creating and delivering ASU Online programs'),
    'field_background_image' => edl_file('hero-group-23301.png', 'EdPlus Digital Learning hero background'),
  ]),
  edl_paragraph('who_we_are_section', [
    'field_heading' => 'Who we are',
    'field_body' => edl_text('<p>EdPlus Digital Learning (EDL) is a team at EdPlus that supports the success and development of ASU Online instructors and faculty. Composed of instructional designers, media experts and more, the EDL team collaborates to support Arizona State University’s charter to provide an inclusive and world-class education at scale.</p><p>ASU Online faculty are the backbone of student learning outcomes in online courses. Your knowledge and experience in your dedicated field guide students to follow their passions. We are experts in helping you deliver that knowledge broadly using technology.</p>'),
    'field_image' => edl_file('who-we-are-mask-group-13617.jpg', 'ASU Online faculty collaboration'),
    'field_logo' => edl_file('edl-horizontal-primary-logo-gold.png', 'EdPlus Digital Learning logo'),
  ]),
  edl_paragraph('mission_section', [
    'field_heading' => 'Advancing our mission',
    'field_quote' => edl_text('We advance excellence, innovation and access in course design, development and delivery for ASU Online and Open Scale.'),
    'field_quote_label' => 'EDL Mission Statement',
    'field_background_image' => edl_file('mission-topo-pattern-black.png', 'Black topographic pattern'),
    'field_icon' => edl_file('open-quote.svg', 'Quote icon'),
  ]),
  edl_paragraph('what_we_do_section', [
    'field_heading' => 'What we do',
    'field_intro_image' => edl_file('what-we-do-header.jpg', 'Instructional design and media team working'),
    'field_service_cards' => array_map('edl_paragraph_ref', $service_cards),
  ]),
  edl_paragraph('studio_cta_section', [
    'field_heading' => 'Visit our studios',
    'field_body' => edl_text('EdPlus provides online course faculty access to four studios equipped with the latest technology to produce professional videos and media.'),
    'field_image' => edl_file('studio-exported-cropped-image.png', 'EdPlus studio recording space'),
    'field_button' => edl_link('route:<nolink>', 'Book an appointment'),
    'field_play_icon' => edl_file('play-button.svg', 'Play video icon'),
  ]),
  edl_paragraph('explore_edl_section', [
    'field_heading' => 'Explore EDL',
    'field_body' => edl_text('Explore the ways EdPlus supports you in delivering a top-tier education online.'),
    'field_background_image' => edl_file('explore-skysong-cta.png', 'ASU Skysong building'),
    'field_explore_links' => array_map('edl_paragraph_ref', $explore_paragraphs),
  ]),
];

$homepage_nodes = \Drupal::entityTypeManager()
  ->getStorage('node')
  ->loadByProperties([
    'type' => 'edl_home_page',
    'title' => 'EdPlus Digital Learning (EDL)',
  ]);
$homepage = $homepage_nodes ? reset($homepage_nodes) : Node::create([
  'type' => 'edl_home_page',
  'title' => 'EdPlus Digital Learning (EDL)',
]);
$homepage->setPublished(TRUE);
edl_delete_existing_sections($homepage);
$homepage->set('field_sections', array_map('edl_paragraph_ref', $sections));
$homepage->save();
edl_alias('/node/' . $homepage->id(), '/home');

$service_nodes = \Drupal::entityTypeManager()
  ->getStorage('node')
  ->loadByProperties(['type' => 'service_area', 'title' => 'Learning Technologies']);
$service = $service_nodes ? reset($service_nodes) : Node::create([
  'type' => 'service_area',
  'title' => 'Learning Technologies',
]);
$service->setPublished(TRUE);
$service->set('field_summary', 'We discover, deploy and support technologies that expand access to higher education, improve online learning experiences and support ASU Online faculty.');
$service->set('field_feature_image', edl_file('service-learning-technologies.png', 'Learning technologies visual'));
$service->save();
edl_alias('/node/' . $service->id(), '/learning-technologies');

$resource_nodes = \Drupal::entityTypeManager()
  ->getStorage('node')
  ->loadByProperties(['type' => 'resource_page', 'title' => 'Resource Library']);
$resource = $resource_nodes ? reset($resource_nodes) : Node::create([
  'type' => 'resource_page',
  'title' => 'Resource Library',
]);
$resource->setPublished(TRUE);
$resource->set('field_resource_summary', 'Explore helpful resources, guides and support materials for online teaching and digital learning.');
$resource->set('field_resource_links', [
  edl_link('internal:/learning-technologies', 'Learning Technologies'),
  edl_link('route:<nolink>', 'Faculty resources'),
  edl_link('route:<nolink>', 'Course development guides'),
]);
$resource->save();
edl_alias('/node/' . $resource->id(), '/resources');

\Drupal::configFactory()
  ->getEditable('system.site')
  ->set('page.front', '/home')
  ->save();

echo "Seeded EDL homepage node {$homepage->id()} at /home\n";
echo "Seeded Learning Technologies node {$service->id()} at /learning-technologies\n";
echo "Seeded Resource Library node {$resource->id()} at /resources\n";
echo "Set front page to /home\n";
