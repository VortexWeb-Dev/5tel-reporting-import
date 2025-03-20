<?php
require_once(__DIR__ . '/crest/crest.php');
require_once(__DIR__ . '/crest/settings.php');
require_once(__DIR__ . '/vendor/autoload.php');
require_once(__DIR__ . '/db/db.php');
require_once(__DIR__ . '/config/config.php');
require_once(__DIR__ . '/models/company.php');
require_once(__DIR__ . '/models/user.php');
require_once(__DIR__ . '/utils/logger.php');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

function processFile(array $file): void
{
    if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
        redirectTo('import-xlsx.php', ['error' => 1]);
    }

    $spreadsheet = IOFactory::load($file['tmp_name']);
    $worksheet = $spreadsheet->getActiveSheet();
    $dataRows = array_slice($worksheet->toArray(), 1); // Skip header row

    $config = require(__DIR__ . '/config/config.php');
    $db = new Database($config['db']);
    $pdo = $db->getConnection();
    $logger = new Logger();

    foreach ($dataRows as $row) {
        processRow($row, $pdo, $logger);
    }

    redirectTo('import-xlsx.php', ['success' => 1]);
}

function processRow(array $row, $pdo, Logger $logger): void
{
    [$name, $responsiblePerson, $mid] = array_pad($row, 3, null);

    if (empty($name) || empty($mid) || empty($responsiblePerson)) {
        return;
    }

    $user = new User($pdo, $logger);
    $responsiblePersonBitrixId = $user->getUserBitrixId($responsiblePerson);

    $company = new Company($pdo, $logger);
    $company->create($name, $mid, $responsiblePerson, $responsiblePersonBitrixId);
}

function redirectTo(string $url, array $params = []): void
{
    $queryString = !empty($params) ? '?' . http_build_query($params) : '';
    header("Location: {$url}{$queryString}");
    exit;
}

processFile($_FILES['xlsxFile']);
exit;