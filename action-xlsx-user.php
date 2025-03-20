<?php
require_once(__DIR__ . '/crest/crest.php');
require_once(__DIR__ . '/crest/settings.php');
require 'vendor/autoload.php';

require_once(__DIR__ . '/db/db.php');
require_once(__DIR__ . '/config/config.php');
require_once(__DIR__ . '/models/user.php');
require_once(__DIR__ . '/utils/logger.php');

use PhpOffice\PhpSpreadsheet\IOFactory;

function processFile($file)
{
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        redirectTo('import-xlsx.php', 'File upload error: ' . ($file['error'] ?? 'No file uploaded'));
    }

    return IOFactory::load($file['tmp_name'])->getActiveSheet()->toArray();
}

function processRows(array $rows, $pdo, $logger)
{
    $user = new User($pdo, $logger);
    $dataRows = array_slice($rows, 1); // Exclude header

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

        $usersToImport[] = ['name' => $name, 'bitrix_id' => $bitrixId];
    }

    return $user->bulkImport($usersToImport);
}

function redirectTo($location, $message)
{
    header("Location: $location?error=1&message=" . urlencode($message));
    exit;
}

// Main execution
$rows = processFile($_FILES['xlsxFile']);

$config = require(__DIR__ . '/config/config.php');
$db = new Database($config['db']);
$pdo = $db->getConnection();
$logger = new Logger();

$result = processRows($rows, $pdo, $logger);

if ($result['success']) {
    $logger->logInfo("Successfully imported {$result['total_records']} records.");
    header('Location: import-xlsx.php?success=1&imported=' . $result['total_records']);
    exit;
} else {
    redirectTo('import-xlsx.php', $result['error'] ?? 'Unknown error');
}
exit;
