<?php
require_once(__DIR__ . '/crest/crest.php');
require_once(__DIR__ . '/crest/settings.php');
require 'vendor/autoload.php';

require_once(__DIR__ . '/db/db.php');
require_once(__DIR__ . '/config/config.php');
require_once(__DIR__ . '/models/user.php');
require_once(__DIR__ . '/utils/logger.php');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

if (isset($_FILES['xlsxFile']) && $_FILES['xlsxFile']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['xlsxFile']['tmp_name'];

    $spreadsheet = IOFactory::load($fileTmpPath);
    $worksheet = $spreadsheet->getActiveSheet();
    $rows = $worksheet->toArray();

    $header = $rows[0];
    $dataRows = array_slice($rows, 1);

    $config = require(__DIR__ . '/config/config.php');
    $db = new Database($config['db']);
    $pdo = $db->getConnection();
    $logger = new Logger();

    $user = new User($pdo, $logger);

    // Use the bulkImport method instead of individual transactions
    $usersToImport = [];
    foreach ($dataRows as $index => $row) {
        if (count($row) < 2) {
            $logger->logError("Skipping row " . ($index + 1) . ": Insufficient columns.");
            continue;
        }

        [$name, $bitrixId] = array_map('trim', $row);

        if (empty($name) || empty($bitrixId)) {
            $logger->logError("Skipping row " . ($index + 1) . ": Empty values detected.");
            continue;
        }

        $usersToImport[] = [
            'name' => $name,
            'bitrix_id' => $bitrixId
        ];
    }

    // Use the existing bulkImport method to handle the transaction
    $result = $user->bulkImport($usersToImport);

    if ($result['success']) {
        $logger->logInfo("Successfully imported {$result['total_records']} records.");

        if (!empty($result['errors'])) {
            $logger->logWarning("There were " . count($result['errors']) . " errors during import.");
        }

        header('Location: import-xlsx.php?success=1&imported=' . $result['total_records']);
        exit;
    } else {
        $logger->logError('Bulk import failed: ' . ($result['error'] ?? 'Unknown error'));
        header('Location: import-xlsx.php?error=1&message=' . urlencode($result['error'] ?? 'Unknown error'));
        exit;
    }
} else {
    $errorCode = $_FILES['xlsxFile']['error'] ?? 'No file uploaded';
    header('Location: import-xlsx.php?error=1&message=' . urlencode('File upload error: ' . $errorCode));
    exit;
}
