<?php
require_once(__DIR__ . '/crest/crest.php');
require_once(__DIR__ . '/crest/settings.php');
require_once(__DIR__ . '/vendor/autoload.php');
require_once(__DIR__ . '/db/db.php');
require_once(__DIR__ . '/config/config.php');
require_once(__DIR__ . '/models/company.php');
require_once(__DIR__ . '/models/user.php');
require_once(__DIR__ . '/models/transaction.php');
require_once(__DIR__ . '/utils/logger.php');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

function getAgentCommission(int $agentId): float
{
    $agentCommission = [
        3 => 0.0,  // Anna Lisowska
        4 => 30.0, // Peter Walpole
        5 => 70.0, // Ace Card
        6 => 30.0, // Sayanthini Vijayrahavan
    ];
    return $agentCommission[$agentId] ?? 0.0;
}

function processFile(array $file): void
{
    if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
        redirectTo('import-xlsx.php', ['error' => 1]);
    }

    $spreadsheet = IOFactory::load($file['tmp_name']);
    $worksheet = $spreadsheet->getActiveSheet();
    $dataRows = array_slice($worksheet->toArray(), 1); // Remove headers

    $config = require(__DIR__ . '/config/config.php');
    $db = new Database($config['db']);
    $pdo = $db->getConnection();
    $logger = new Logger();
    $company = new Company($pdo, $logger);
    $transactionDetail = new Transaction($pdo, $logger);

    foreach ($dataRows as $index => $row) {
        try {
            processRow($row, $pdo, $logger, $company, $transactionDetail);
        } catch (Exception $e) {
            $logger->logError("Exception processing row {$index}: " . $e->getMessage());
        }
    }

    redirectTo('import-xlsx.php', ['success' => 1]);
}

function processRow(array $row, $pdo, Logger $logger, Company $company, Transaction $transactionDetail): void
{
    [
        $statementMonth,
        $mid,
        $dba,
        $status,
        $localCurrency,
        $entity,
        $salesRepCode,
        $openDate,
        $tierCode,
        $cardPlan,
        $firstBatchDate,
        $baseMscRate,
        $baseMscPi,
        $exRate,
        $exPi,
        $intLc,
        $asmtLc,
        $baseMscAmt,
        $exceptionMscAmt,
        $mscAmt,
        $salesVolume,
        $salesTrxn,
        $plan,
        $primaryRate,
        $secondaryRate,
        $residualPerItem,
        $revenueShare,
        $earningsLocalCurrency
    ] = array_pad($row, 28, null);

    if (empty($statementMonth) && empty($mid) && empty($dba)) {
        return;
    }

    $args = [
        'statement_month'       => $statementMonth,
        'mid'                   => $mid,
        'dba'                   => $dba,
        'status'                => $status,
        'local_currency'        => $localCurrency,
        'entity'                => $entity,
        'sales_rep_code'        => $salesRepCode,
        'open_date'             => empty($openDate) ? null : $openDate,
        'tier_code'             => $tierCode,
        'card_plan'             => $cardPlan,
        'first_batch_date'      => empty($firstBatchDate) ? null : $firstBatchDate,
        'base_msc_rate'         => $baseMscRate ?? '0',
        'base_msc_pi'           => $baseMscPi ?? '0',
        'ex_rate'               => $exRate ?? '0',
        'ex_pi'                 => $exPi ?? '0',
        'int_lc'                => $intLc ?? '0',
        'asmt_lc'               => $asmtLc ?? '0',
        'base_msc_amt'          => formatNumber($baseMscAmt),
        'exception_msc_amt'     => formatNumber($exceptionMscAmt),
        'msc_amt'               => formatNumber($mscAmt),
        'sales_volume'          => formatNumber($salesVolume),
        'sales_trxn'            => formatNumber($salesTrxn),
        'plan'                  => $plan,
        'primary_rate'          => $primaryRate ?? '0',
        'secondary_rate'        => $secondaryRate ?? '0',
        'residual_per_item'     => $residualPerItem ?? '0',
        'revenue_share'         => $revenueShare ?? '0',
        'earnings_local_currency' => formatNumber($earningsLocalCurrency),
    ];

    // Get the responsible person
    $responsiblePersonDetails = $company->getResponsiblePerson($mid);
    if ($responsiblePersonDetails) {
        $args['responsible_person'] = $responsiblePersonDetails['responsible_person'];
        $args['responsible_person_bitrix_id'] = $responsiblePersonDetails['responsible_person_bitrix_id'];
    }

    // Calculate commission
    $commissionPercentage = isset($args['responsible_person_bitrix_id'])
        ? getAgentCommission((int) trim($args['responsible_person_bitrix_id']))
        : 0;

    $earnings = (float) $args['earnings_local_currency'];
    $args['commission'] = ($commissionPercentage / 100) * $earnings;

    $transactionDetail->create($args);
}

function formatNumber($value): string
{
    return isset($value) ? str_replace(',', '', $value) : '0';
}

function redirectTo(string $url, array $params = []): void
{
    $queryString = !empty($params) ? '?' . http_build_query($params) : '';
    header("Location: {$url}{$queryString}");
    exit;
}

processFile($_FILES['xlsxFile']);
exit;
