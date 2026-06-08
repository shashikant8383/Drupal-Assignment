<?php

/**
 * Creates the EDL Contact Form webform.
 *
 * Run with:
 *   ddev drush php:script scripts/create_edl_contact_webform.php
 */

use Drupal\webform\Entity\Webform;

$webform = Webform::load('edl_contact') ?: Webform::create([
  'id' => 'edl_contact',
]);

$webform->set('title', 'EDL Contact Form');
$webform->set('description', 'Contact form for EdPlus Digital Learning inquiries.');
$webform->set('category', 'EDL');
$webform->set('status', 'open');
$webform->set('access', [
  'create' => [
    'roles' => [
      'anonymous',
      'authenticated',
    ],
    'users' => [],
    'permissions' => [],
  ],
]);
$webform->set('settings', array_replace_recursive($webform->getSettings(), [
  'page' => TRUE,
  'page_submit_path' => '/contact',
  'page_confirm_path' => '/contact/confirmation',
  'confirmation_type' => 'inline',
  'confirmation_message' => 'Thank you for contacting EdPlus Digital Learning. We will review your message and follow up soon.',
  'form_submit_label' => 'Submit',
]));

$webform->setElements([
  'first_name' => [
    '#type' => 'textfield',
    '#title' => 'First name',
    '#required' => TRUE,
  ],
  'last_name' => [
    '#type' => 'textfield',
    '#title' => 'Last name',
    '#required' => TRUE,
  ],
  'asu_email' => [
    '#type' => 'email',
    '#title' => 'ASU Email',
    '#required' => TRUE,
  ],
  'phone_number' => [
    '#type' => 'tel',
    '#title' => 'Phone number',
  ],
  'service_area' => [
    '#type' => 'select',
    '#title' => 'Service area',
    '#required' => TRUE,
    '#empty_option' => '- Select -',
    '#options' => [
      'instructional_design' => 'Instructional design support',
      'learning_technologies' => 'Learning technologies support',
      'academic_media' => 'Academic Media support',
      'general' => 'General inquiries',
    ],
  ],
  'message' => [
    '#type' => 'textarea',
    '#title' => 'Please submit your communications in the box below:',
    '#required' => TRUE,
  ],
]);

$webform->save();

print "Created or updated the edl_contact webform at /contact.\n";
