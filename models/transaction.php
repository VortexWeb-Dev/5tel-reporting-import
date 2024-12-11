<?php

class Transaction {
    private $db;
    private $logger;

    public function __construct($db, $logger) {
        $this->db = $db;
        $this->logger = $logger;
    }

    // CREATE operation
    public function create($data) {
        $sql = "
            INSERT INTO transaction (
                statement_month, mid, dba, status, local_currency, entity, sales_rep_code, 
                open_date, tier_code, card_plan, first_batch_date, base_msc_rate, base_msc_pi, 
                ex_rate, ex_pi, int_lc, asmt_lc, base_msc_amt, exception_msc_amt, msc_amt, 
                sales_volume, sales_trxn, plan, primary_rate, secondary_rate, residual_per_item, 
                revenue_share, earnings_local_currency, commission, responsible_person, responsible_person_bitrix_id
            ) VALUES (
                $1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, $14, $15, $16, $17, $18, 
                $19, $20, $21, $22, $23, $24, $25, $26, $27, $28, $29, $30, $31
            )
        ";

        $params = [
            $data['statement_month'] ?? null,
            $data['mid'] ?? null,
            $data['dba'] ?? null,
            $data['status'] ?? null,
            $data['local_currency'] ?? null,
            $data['entity'] ?? null,
            $data['sales_rep_code'] ?? null,
            $data['open_date'] ?? null,
            $data['tier_code'] ?? null,
            $data['card_plan'] ?? null,
            $data['first_batch_date'] ?? null,
            $data['base_msc_rate'] ?? null,
            $data['base_msc_pi'] ?? null,
            $data['ex_rate'] ?? null,
            $data['ex_pi'] ?? null,
            $data['int_lc'] ?? null,
            $data['asmt_lc'] ?? null,
            $data['base_msc_amt'] ?? null,
            $data['exception_msc_amt'] ?? null,
            $data['msc_amt'] ?? null,
            $data['sales_volume'] ?? null,
            $data['sales_trxn'] ?? null,
            $data['plan'] ?? null,
            $data['primary_rate'] ?? null,
            $data['secondary_rate'] ?? null,
            $data['residual_per_item'] ?? null,
            $data['revenue_share'] ?? null,
            $data['earnings_local_currency'] ?? null,
            $data['commission'] ?? null,
            $data['responsible_person'] ?? null,
            $data['responsible_person_bitrix_id'] ?? null
        ];

        try {
            $result = pg_query_params($this->db->getConnection(), $sql, $params);

            if ($result) {
                $this->logger->logInfo("New transaction created for MID: {$data['mid']}");
                return pg_last_oid($result);
            }

            $this->logger->logError("Failed to create transaction. SQL Error: " . pg_last_error($this->db->getConnection()));
            return false;
        } catch (Exception $e) {
            $this->logger->logError("Exception in transaction creation: " . $e->getMessage());
            return false;
        }
    }

    // READ operation by ID
    public function getById($id) {
        $sql = "SELECT * FROM transaction WHERE id = $1";

        try {
            $result = pg_query_params($this->db->getConnection(), $sql, [$id]);

            if ($result) {
                $transaction = pg_fetch_assoc($result);
                if ($transaction) {
                    $this->logger->logInfo("Transaction fetched with ID: {$id}");
                    return $transaction;
                } else {
                    $this->logger->logWarning("No transaction found with ID: {$id}");
                    return null;
                }
            }

            $this->logger->logError("Failed to fetch transaction. SQL Error: " . pg_last_error($this->db->getConnection()));
            return null;
        } catch (Exception $e) {
            $this->logger->logError("Exception in transaction retrieval: " . $e->getMessage());
            return null;
        }
    }

    // READ operation by MID
    public function getByMid($mid) {
        $sql = "SELECT * FROM transaction WHERE mid = $1";

        try {
            $result = pg_query_params($this->db->getConnection(), $sql, [$mid]);

            if ($result) {
                $transactions = [];
                while ($row = pg_fetch_assoc($result)) {
                    $transactions[] = $row;
                }
                $this->logger->logInfo("Transactions fetched for MID: {$mid}");
                return $transactions;
            }

            $this->logger->logError("Failed to fetch transactions for MID: {$mid}. SQL Error: " . pg_last_error($this->db->getConnection()));
            return [];
        } catch (Exception $e) {
            $this->logger->logError("Exception in fetching transactions by MID: " . $e->getMessage());
            return [];
        }
    }

    // UPDATE operation
    public function update($id, $data) {
        $updateFields = [];
        $params = [];
        $paramCount = 1;

        foreach ($data as $key => $value) {
            $updateFields[] = "$key = $" . $paramCount;
            $params[] = $value;
            $paramCount++;
        }

        $params[] = $id;

        $sql = "UPDATE transaction SET " . implode(", ", $updateFields) . " WHERE id = $" . $paramCount;

        try {
            $result = pg_query_params($this->db->getConnection(), $sql, $params);

            if ($result) {
                $this->logger->logInfo("Transaction updated with ID: {$id}");
                return true;
            }

            $this->logger->logError("Failed to update transaction with ID: {$id}. SQL Error: " . pg_last_error($this->db->getConnection()));
            return false;
        } catch (Exception $e) {
            $this->logger->logError("Exception in transaction update: " . $e->getMessage());
            return false;
        }
    }

    // DELETE operation
    public function delete($id) {
        $sql = "DELETE FROM transaction WHERE id = $1";

        try {
            $result = pg_query_params($this->db->getConnection(), $sql, [$id]);

            if ($result) {
                $this->logger->logInfo("Transaction deleted with ID: {$id}");
                return true;
            }

            $this->logger->logError("Failed to delete transaction with ID: {$id}. SQL Error: " . pg_last_error($this->db->getConnection()));
            return false;
        } catch (Exception $e) {
            $this->logger->logError("Exception in transaction deletion: " . $e->getMessage());
            return false;
        }
    }

    // Get all transactions
    public function getAll($limit = 1000, $offset = 0) {
        $sql = "SELECT * FROM transaction LIMIT $1 OFFSET $2";

        try {
            $result = pg_query_params($this->db->getConnection(), $sql, [$limit, $offset]);

            if ($result) {
                $transactions = [];
                while ($row = pg_fetch_assoc($result)) {
                    $transactions[] = $row;
                }
                $this->logger->logInfo("Fetched transactions. Limit: {$limit}, Offset: {$offset}");
                return $transactions;
            }

            $this->logger->logError("Failed to fetch transactions. SQL Error: " . pg_last_error($this->db->getConnection()));
            return [];
        } catch (Exception $e) {
            $this->logger->logError("Exception in fetching transactions: " . $e->getMessage());
            return [];
        }
    }
}