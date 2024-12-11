<?php
require_once(__DIR__ . '/crest/crest.php');
require_once(__DIR__ . '/crest/settings.php');
require 'vendor/autoload.php';

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

    $fieldMapping = [
        'Statement Month' => 'ufCrm9StatementMonth',
        'MID' => 'ufCrm9Mid',
        'DBA' => 'ufCrm9Dba',
        'Status' => 'ufCrm9Status',
        'Local Currency' => 'ufCrm9LocalCurrency',
        'Entity' => 'ufCrm9Entity',
        'Sales Rep Code' => 'ufCrm9SalesRepCode',
        'Open Date' => 'ufCrm9OpenDate',
        'Tier Code' => 'ufCrm9TierCode',
        'Card Plan' => 'ufCrm9CardPlan',
        'First Batch Date' => 'ufCrm9FirstBatchDate',
        'Base Msc Rate' => 'ufCrm9BaseMscRate',
        'Base Msc PI' => 'ufCrm9BaseMscPi',
        'Ex Rate' => 'ufCrm9ExRate',
        'Ex PI' => 'ufCrm9ExPi',
        'Int_LC' => 'ufCrm9IntLc',
        'Asmt_LC' => 'ufCrm9AsmtLc',
        'Base MSC Amt' => 'ufCrm9BaseMscAmt',
        'Exception MSC Amt' => 'ufCrm9ExceptionMscAmt',
        'MSC Amt' => 'ufCrm9MscAmt',
        'Sales Volume' => 'ufCrm9SalesVolume',
        'Sales Trxn' => 'ufCrm9SalesTrxn',
        'Plan' => 'ufCrm9Plan',
        'Primary Rate' => 'ufCrm9PrimaryRate',
        'Seconadary Rate' => 'ufCrm9SecondaryRate',
        'Residual Per Item' => 'ufCrm9ResidualPerItem',
        'Revenue Share %' => 'ufCrm9RevenueSharePercentage',
        'Earnings- Local Currency' => 'ufCrm9EarningsLocalCurrency',
    ];

    $columnIndexes = [];
    foreach ($header as $index => $columnName) {
        if (isset($fieldMapping[$columnName])) {
            $columnIndexes[$fieldMapping[$columnName]] = $index;
        }
    }

    foreach ($dataRows as $row) {
        $fields = [];

        foreach ($columnIndexes as $crmField => $index) {
            $fields[$crmField] = htmlspecialchars(trim($row[$index]));
        }

        $mid = $fields['ufCrm9Mid'];

        $company_res = CRest::call('crm.company.list', ['filter' => ['UF_CRM_1719996948788' => $mid]]);
        $company = $company_res['result'][0] ?? null;
        $agent_id = $company['ASSIGNED_BY_ID'];

        $commission_percentage = $agent_commission[$agent_id] ?? 0; // Commission Percentage
        $earnings = (float)$fields['ufCrm9EarningsLocalCurrency']; // Earnings- Local Currency

        $fields['ufCrm9CommissionAmount'] = ($commission_percentage / 100) * $earnings; // Commission Amount

        $fields['ufCrm9Month'] = 1385; // For November


        if ($company) {
            $fields['assignedById'] = $company['ASSIGNED_BY_ID'];
        }

        CRest::call('crm.item.add', ['entityTypeId' => STATEMENTS_ENTITY_TYPE_ID, 'fields' => $fields]);

        CRest::call('crm.company.add', ['fields' => [
            'TITLE' => $fields['ufCrm9Dba'],
            'UF_CRM_1719996912245' => $fields['ufCrm9Entity'],
            'UF_CRM_1719996948788' => [$fields['ufCrm9Mid']],
            'UF_CRM_1719997644193' => $fields['ufCrm9OpenDate'],
            'UF_CRM_1728567298451' => $fields['ufCrm9CommissionAmount'],
            'UF_CRM_1728567384275' => $fields['ufCrm9SalesTrxn']
        ]]);
        sleep(1); // sleep for 1 second

    }

    header('Location: import-xlsx.php?success=1');
    exit;
} else {
    header('Location: import-xlsx.php?error=1');
    exit;
}
