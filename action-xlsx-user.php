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

    // echo '<pre>';
    // print_r('rows:');
    // print_r($rows);
    // echo '</pre>';

    $header = $rows[0];
    $dataRows = array_slice($rows, 1);

    // echo '<pre>';
    // print_r('header:');
    // print_r($header);
    // echo '</pre>';

    // echo '<pre>';
    // print_r('dataRows:');
    // print_r($dataRows);
    // echo '</pre>';

    $config = require(__DIR__ . '/config/config.php');
    $db = new Database($config['db']);
    $logger = new Logger();

    foreach ($dataRows as $index => $row) {
        $user = new User($db, $logger);
        $newUserId = $user->create(...$row);
    }

    header('Location: import-xlsx.php?success=1');
    exit;
} else {
    header('Location: import-xlsx.php?error=1');
    exit;
}
