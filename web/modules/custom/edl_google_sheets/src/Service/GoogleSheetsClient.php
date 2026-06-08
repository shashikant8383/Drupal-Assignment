<?php

namespace Drupal\edl_google_sheets\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Psr\Log\LoggerInterface;

class GoogleSheetsClient {

  public function __construct(
    protected ConfigFactoryInterface $configFactory,
    protected LoggerInterface $logger,
  ) {}

  public function appendContactSubmission(array $values): void {
    $config = $this->configFactory->get('edl_google_sheets.settings');

    $spreadsheet_id = $config->get('spreadsheet_id');
    $range = $config->get('sheet_range') ?: 'Contact Submissions!A:H';
    $credentials_path = DRUPAL_ROOT . '/' . $config->get('credentials_path');

    if (!$spreadsheet_id || !file_exists($credentials_path)) {
      $this->logger->error('Google Sheets settings are missing or credentials file was not found.');
      return;
    }

    try {
      $client = new \Google_Client();
      $client->setApplicationName('EDL Contact Form');
      $client->setAuthConfig($credentials_path);
      $client->setScopes([\Google_Service_Sheets::SPREADSHEETS]);

      $service = new \Google_Service_Sheets($client);

      $body = new \Google_Service_Sheets_ValueRange([
        'values' => [$values],
      ]);

      $service->spreadsheets_values->append(
        $spreadsheet_id,
        $range,
        $body,
        [
          'valueInputOption' => 'RAW',
          'insertDataOption' => 'INSERT_ROWS',
        ]
      );
    }
    catch (\Throwable $e) {
      $this->logger->error('Google Sheets append failed: @message', [
        '@message' => $e->getMessage(),
      ]);
    }
  }

}
