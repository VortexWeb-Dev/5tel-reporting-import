<?php
require_once(__DIR__ . '/crest/crest.php');
require_once(__DIR__ . '/crest/settings.php');
require 'vendor/autoload.php';

require_once(__DIR__ . '/db/db.php');
require_once(__DIR__ . '/config/config.php');
require_once(__DIR__ . '/models/company.php');
require_once(__DIR__ . '/models/user.php');
require_once(__DIR__ . '/models/transaction.php');
require_once(__DIR__ . '/utils/logger.php');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

$agent_commission = [
    // 17 => 0, // Anna Lisowska,
    21 => 30, // Peter Walpole
    29 => 70, // Ace Card
    31 => 30, // Sayanthini Vijayrahavan
];

if (isset($_FILES['xlsxFile']) && $_FILES['xlsxFile']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['xlsxFile']['tmp_name'];

    $spreadsheet = IOFactory::load($fileTmpPath);
    $worksheet = $spreadsheet->getActiveSheet();

    $rows = $worksheet->toArray();

    $header = $rows[0];
    $dataRows = array_slice($rows, 1);

    // echo '<pre>';
    // print_r('rows:');
    // print_r($dataRows);
    // echo '</pre>';

    $config = require(__DIR__ . '/config/config.php');
    $db = new Database($config['db']);
    $pdo = $db->getConnection();
    $logger = new Logger();

    foreach ($dataRows as $index => $row) {
        try {
            $transactionDetail = new Transaction($pdo, $logger);

            $args = [
                'statement_month' => $row[0],
                'mid' => $row[1],
                'dba' => $row[2],
                'status' => $row[3],
                'local_currency' => $row[4],
                'entity' => $row[5],
                'sales_rep_code' => $row[6],
                'open_date' => $row[7] === '' ? null : $row[7],
                'tier_code' => $row[8],
                'card_plan' => $row[9],
                'first_batch_date' => $row[10] === '' ? null : $row[10],
                'base_msc_rate' => $row[11] === null ? '0' : $row[11],
                'base_msc_pi' => $row[12] === null ? '0' : $row[12],
                'ex_rate' => $row[13] === null ? '0' : $row[13],
                'ex_pi' => $row[14] === null ? '0' : $row[14],
                'int_lc' => $row[15] === null ? '0' : $row[15],
                'asmt_lc' => $row[16] === null ? '0' : $row[16],
                'base_msc_amt' => $row[17] === null ? '0' : str_replace(',', '', $row[17]),
                'exception_msc_amt' => $row[18] === null ? '0' : str_replace(',', '', $row[18]),
                'msc_amt' => $row[19] === null ? '0' : str_replace(',', '', $row[19]),
                'sales_volume' => $row[20] === null ? '0' : str_replace(',', '', $row[20]),
                'sales_trxn' => $row[21] === null ? '0' : str_replace(',', '', $row[21]),
                'plan' => $row[22],
                'primary_rate' => $row[23] === null ? '0' : $row[23],
                'secondary_rate' => $row[24] === null ? '0' : $row[24],
                'residual_per_item' => $row[25] === null ? '0' : $row[25],
                'revenue_share' => $row[26] === null ? '0' : $row[26],
                'earnings_local_currency' => $row[27] === null ? '0' : str_replace(',', '', $row[27]),
            ];

            // get the responsible person
            $company = new Company($pdo, $logger);
            $responsiblePersonDetails = $company->getResponsiblePerson($args['mid']);

            if ($responsiblePersonDetails) {
                $args['responsible_person'] = $responsiblePersonDetails['responsible_person'];
                $args['responsible_person_bitrix_id'] = $responsiblePersonDetails['responsible_person_bitrix_id'];
            }
            // calculate commission
            $commission_percentage = isset($args['responsible_person_bitrix_id']) ? $agent_commission[$args['responsible_person_bitrix_id']] ?? 0 : 0; // Commission Percentage
            $earnings = (float)$args['earnings_local_currency']; // Earnings- Local Currency

            $args['commission'] = ($commission_percentage / 100) * $earnings; // Commission Amount

            // echo '<pre>';
            // var_dump($args['first_batch_date']);
            // echo '</pre>';

            $transactionId = $transactionDetail->create($args);
        } catch (Exception $e) {
            $logger->logError("Exception processing row {$index}: " . $e->getMessage());
            continue;
        }
    }

    header('Location: import-xlsx.php?success=1');
    exit;
} else {
    header('Location: import-xlsx.php?error=1');
    exit;
}
