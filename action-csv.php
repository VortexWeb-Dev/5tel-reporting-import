<?php
require_once(__DIR__ . '/crest/crest.php');
require_once(__DIR__ . '/crest/settings.php');

if (isset($_FILES['csvFile']) && $_FILES['csvFile']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['csvFile']['tmp_name'];
    $separator = $_POST['separator'];

    $fileContent = file($fileTmpPath);
    if ($fileContent === false) {
        echo "Error reading the file.";
        exit;
    }

    $header = [];
    $dataRows = [];
    $isHeader = true;

    foreach ($fileContent as $line) {
        $data = str_getcsv($line, $separator);
        if ($isHeader) {
            $header = $data;
            $isHeader = false;
        } else {
            $dataRows[] = $data;
        }
    }

    foreach ($dataRows as $row) {
        $earnings = (float)$row[27];
        $revenue_share = (float)$row[26];

        $commission_amount = ($revenue_share / 100) * $earnings;

        $fields = [
            'ufCrm7StatementMonth' => $row[0],
            'ufCrm7Mid' => $row[1],
            'ufCrm7Dba' => $row[2],
            'ufCrm7Status' => $row[3],
            'ufCrm7LocalCurrency' => $row[4],
            'ufCrm7Entity' => $row[5],
            'ufCrm7SalesRepCode' => $row[6],
            'ufCrm7OpenDate' => $row[7],
            'ufCrm7TierCode' => $row[8],
            'ufCrm7CardPlan' => $row[9],
            'ufCrm7FirstBatchDate' => $row[10],
            'ufCrm7BaseMscRate' => $row[11],
            'ufCrm7BaseMscPi' => $row[12],
            'ufCrm7ExRate' => $row[13],
            'ufCrm7ExPi' => $row[14],
            'ufCrm7IntLc' => $row[15],
            'ufCrm7AsmtLc' => $row[16],
            'ufCrm7BaseMscAmt' => $row[17],
            'ufCrm7ExceptionMscAmt' => $row[18],
            'ufCrm7MscAmt' => $row[19],
            'ufCrm7SalesVolume' => $row[20],
            'ufCrm7SalesTrxn' => $row[21],
            'ufCrm7Plan' => $row[22],
            'ufCrm7PrimaryRate' => $row[23],
            'ufCrm7SecondaryRate' => $row[24],
            'ufCrm7ResidualPerItem' => $row[25],
            'ufCrm7RevenueSharePercentage' => $row[26],
            'ufCrm7EarningsLocalCurrency' => $row[27],
            'ufCrm7CommissionAmount' => $commission_amount,
        ];
        $mid = $row[1];

        $company_res = CRest::call('crm.company.list', ['filter' => ['UF_CRM_1719996948788' => $mid]]);
        $company = $company_res['result'][0] ?? null;

        if ($company) {
            $fields['ufCrm7ResponsiblePerson'] = $company['ASSIGNED_BY_ID'];
        }

        CRest::call('crm.item.add', ['entityTypeId' => STATEMENTS_ENTITY_TYPE_ID, 'fields' => $fields]);

        CRest::call('crm.company.add', ['fields' => [
            'TITLE' => $row[2],
            'UF_CRM_1719996912245' => $row[5],
            'UF_CRM_1719996948788' => [
                $row[1]
            ],
            'UF_CRM_1719997644193' => $row[7],
            'UF_CRM_1728567298451' => $commission_amount,
            'UF_CRM_1728567384275' => $row[21]
        ]]);
    }

    header('Location: import-csv.php?success=1');
    exit;
} else {
    header('Location: import-csv.php?error=1');
    exit;
}

/*
Statement Month - ufCrm7StatementMonth
MID - ufCrm7Mid
DBA - ufCrm7Dba
Status - ufCrm7Status
Local Currency - ufCrm7LocalCurrency
Entity - ufCrm7Entity
Sales Rep Code - ufCrm7SalesRepCode
Open Date - ufCrm7OpenDate
Tier Code - ufCrm7TierCode
Card Plan - ufCrm7CardPlan
First Batch Date - ufCrm7FirstBatchDate
Base Msc Rate - ufCrm7BaseMscRate
Base Msc PI - ufCrm7BaseMscPi
Ex Rate - ufCrm7ExRate
Ex PI - ufCrm7ExPi
Int_LC - ufCrm7IntLc
Asmt_LC - ufCrm7AsmtLc
Base MSC Amt - ufCrm7BaseMscAmt
Exception MSC Amt - ufCrm7ExceptionMscAmt
MSC Amt - ufCrm7MscAmt
Sales Volume - ufCrm7SalesVolume
Sales Trxn - ufCrm7SalesTrxn
Plan - ufCrm7Plan
Primary Rate - ufCrm7PrimaryRate
Seconadary Rate - ufCrm7SecondaryRate
Residual Per Item - ufCrm7ResidualPerItem
Revenue Share % - ufCrm7RevenueSharePercentage
Earnings- Local Currency - ufCrm7EarningsLocalCurrency
*/