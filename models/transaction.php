<?php

class Transaction
{
    private PDO $db;
    private $logger;

    public function __construct(PDO $db, $logger)
    {
        $this->db = $db;
        $this->logger = $logger;
    }

    // CREATE operation
    public function create($data)
    {
        $sql = "INSERT INTO transaction (
            statement_month, mid, dba, status, local_currency, entity, sales_rep_code, 
            open_date, tier_code, card_plan, first_batch_date, base_msc_rate, base_msc_pi, 
            ex_rate, ex_pi, int_lc, asmt_lc, base_msc_amt, exception_msc_amt, msc_amt, 
            sales_volume, sales_trxn, plan, primary_rate, secondary_rate, residual_per_item, 
            revenue_share, earnings_local_currency, commission, responsible_person, responsible_person_bitrix_id
        ) VALUES (
            :statement_month, :mid, :dba, :status, :local_currency, :entity, :sales_rep_code, 
            :open_date, :tier_code, :card_plan, :first_batch_date, :base_msc_rate, :base_msc_pi, 
            :ex_rate, :ex_pi, :int_lc, :asmt_lc, :base_msc_amt, :exception_msc_amt, :msc_amt, 
            :sales_volume, :sales_trxn, :plan, :primary_rate, :secondary_rate, :residual_per_item, 
            :revenue_share, :earnings_local_currency, :commission, :responsible_person, :responsible_person_bitrix_id
        )";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($data);
            $this->logger->logInfo("New transaction created for MID: " . $data['mid']);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            $this->logger->logError("Failed to create transaction: " . $e->getMessage());
            return false;
        }
    }

    // READ operation by ID
    public function getById($id)
    {
        $sql = "SELECT * FROM transaction WHERE id = :id";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->logger->logError("Failed to fetch transaction: " . $e->getMessage());
            return null;
        }
    }

    // READ operation by MID
    public function getByMid($mid)
    {
        $sql = "SELECT * FROM transaction WHERE mid = :mid";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['mid' => $mid]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->logger->logError("Failed to fetch transactions by MID: " . $e->getMessage());
            return [];
        }
    }

    // UPDATE operation
    public function update($id, $data)
    {
        $updateFields = [];
        foreach ($data as $key => $value) {
            $updateFields[] = "$key = :$key";
        }
        $sql = "UPDATE transaction SET " . implode(", ", $updateFields) . " WHERE id = :id";

        try {
            $stmt = $this->db->prepare($sql);
            $data['id'] = $id;
            return $stmt->execute($data);
        } catch (PDOException $e) {
            $this->logger->logError("Failed to update transaction: " . $e->getMessage());
            return false;
        }
    }

    // DELETE operation
    public function delete($id)
    {
        $sql = "DELETE FROM transaction WHERE id = :id";
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute(['id' => $id]);
        } catch (PDOException $e) {
            $this->logger->logError("Failed to delete transaction: " . $e->getMessage());
            return false;
        }
    }

    // Get all transactions
    public function getAll($limit = 1000, $offset = 0)
    {
        $sql = "SELECT * FROM transaction LIMIT :limit OFFSET :offset";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->logger->logError("Failed to fetch transactions: " . $e->getMessage());
            return [];
        }
    }
}
