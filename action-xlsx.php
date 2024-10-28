<?php
require_once(__DIR__ . '/crest/crest.php');
require_once(__DIR__ . '/crest/settings.php');
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

if (isset($_FILES['xlsxFile']) && $_FILES['xlsxFile']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['xlsxFile']['tmp_name'];

    $spreadsheet = IOFactory::load($fileTmpPath);
    $worksheet = $spreadsheet->getActiveSheet();
    $rows = $worksheet->toArray();

    $header = $rows[0];
    $dataRows = array_slice($rows, 1);

    $fieldMapping = [
        'Statement Month' => 'ufCrm7StatementMonth',
        'MID' => 'ufCrm7Mid',
        'DBA' => 'ufCrm7Dba',
        'Status' => 'ufCrm7Status',
        'Local Currency' => 'ufCrm7LocalCurrency',
        'Entity' => 'ufCrm7Entity',
        'Sales Rep Code' => 'ufCrm7SalesRepCode',
        'Open Date' => 'ufCrm7OpenDate',
        'Tier Code' => 'ufCrm7TierCode',
        'Card Plan' => 'ufCrm7CardPlan',
        'First Batch Date' => 'ufCrm7FirstBatchDate',
        'Base Msc Rate' => 'ufCrm7BaseMscRate',
        'Base Msc PI' => 'ufCrm7BaseMscPi',
        'Ex Rate' => 'ufCrm7ExRate',
        'Ex PI' => 'ufCrm7ExPi',
        'Int_LC' => 'ufCrm7IntLc',
        'Asmt_LC' => 'ufCrm7AsmtLc',
        'Base MSC Amt' => 'ufCrm7BaseMscAmt',
        'Exception MSC Amt' => 'ufCrm7ExceptionMscAmt',
        'MSC Amt' => 'ufCrm7MscAmt',
        'Sales Volume' => 'ufCrm7SalesVolume',
        'Sales Trxn' => 'ufCrm7SalesTrxn',
        'Plan' => 'ufCrm7Plan',
        'Primary Rate' => 'ufCrm7PrimaryRate',
        'Seconadary Rate' => 'ufCrm7SecondaryRate',
        'Residual Per Item' => 'ufCrm7ResidualPerItem',
        'Revenue Share %' => 'ufCrm7RevenueSharePercentage',
        'Earnings- Local Currency' => 'ufCrm7EarningsLocalCurrency',
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
            $fields[$crmField] = $row[$index];
        }

        $earnings = (float)$fields['ufCrm7EarningsLocalCurrency'];
        $revenue_share = (float)$fields['ufCrm7RevenueSharePercentage'];
        $fields['ufCrm7CommissionAmount'] = ($revenue_share / 100) * $earnings;

        $mid = $fields['ufCrm7Mid'];

        $company_res = CRest::call('crm.company.list', ['filter' => ['UF_CRM_1719996948788' => $mid]]);
        $company = $company_res['result'][0] ?? null;

        if ($company) {
            $fields['ufCrm7ResponsiblePerson'] = $company['ASSIGNED_BY_ID'];
        }

        CRest::call('crm.item.add', ['entityTypeId' => STATEMENTS_ENTITY_TYPE_ID, 'fields' => $fields]);

        CRest::call('crm.company.add', ['fields' => [
            'TITLE' => $fields['ufCrm7Dba'],
            'UF_CRM_1719996912245' => $fields['ufCrm7Entity'],
            'UF_CRM_1719996948788' => [$fields['ufCrm7Mid']],
            'UF_CRM_1719997644193' => $fields['ufCrm7OpenDate'],
            'UF_CRM_1728567298451' => $fields['ufCrm7CommissionAmount'],
            'UF_CRM_1728567384275' => $fields['ufCrm7SalesTrxn']
        ]]);
    }

    header('Location: import-xlsx.php?success=1');
    exit;
} else {
    header('Location: import-xlsx.php?error=1');
    exit;
}
