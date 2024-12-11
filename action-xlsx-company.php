<?php
require_once(__DIR__ . '/crest/crest.php');
require_once(__DIR__ . '/crest/settings.php');
require 'vendor/autoload.php';

require_once(__DIR__ . '/db/db.php');
require_once(__DIR__ . '/config/config.php');
require_once(__DIR__ . '/models/company.php');
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
    $logger = new Logger();

    foreach ($dataRows as $index => $row) {
        $company = new Company($db, $logger);
        $name = $row[1];
        $mid = $row[7];
        $responsiblePerson = $row[4];

        $user = new User($db, $logger);
        $responsiblePersonBitrixId = $user->getUserBitrixId($responsiblePerson);

        $args = [$name, $mid, $responsiblePerson, $responsiblePersonBitrixId];
        $newUserId = $company->create(...$args);
    }

    header('Location: import-xlsx.php?success=1');
    exit;
} else {
    header('Location: import-xlsx.php?error=1');
    exit;
}
