<?php

// Create 'users' table
$sql = '
CREATE TABLE IF NOT EXISTS transaction (
    id SERIAL PRIMARY KEY,
    statement_month VARCHAR(50),
    mid VARCHAR(50),
    dba VARCHAR(255),
    status VARCHAR(50),
    local_currency VARCHAR(20),
    entity VARCHAR(100),
    sales_rep_code VARCHAR(50),
    open_date DATE,
    tier_code VARCHAR(50),
    card_plan VARCHAR(100),
    first_batch_date DATE,
    base_msc_rate DECIMAL(10, 4),
    base_msc_pi DECIMAL(10, 4),
    ex_rate DECIMAL(10, 4),
    ex_pi DECIMAL(10, 4),
    int_lc DECIMAL(10, 4),
    asmt_lc DECIMAL(10, 4),
    base_msc_amt DECIMAL(10, 4),
    exception_msc_amt DECIMAL(10, 4),
    msc_amt DECIMAL(10, 4),
    sales_volume DECIMAL(10, 2),
    sales_trxn DECIMAL(10, 2),
    plan VARCHAR(100),
    primary_rate DECIMAL(10, 4),
    secondary_rate DECIMAL(10, 4),
    residual_per_item DECIMAL(10, 4),
    revenue_share DECIMAL(5, 2),
    earnings_local_currency DECIMAL(10, 4),
    commission DECIMAL(10, 4),
    responsible_person VARCHAR(100),
    responsible_person_bitrix_id INTEGER
);
';

$result = pg_query($this->db->getConnection(), $sql);
if (!$result) {
    die("Error in migration: " . pg_last_error($this->db->getConnection()));
}
