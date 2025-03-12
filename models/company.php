<?php

class Company
{
    private PDO $db;
    private $logger;

    public function __construct(PDO $db, $logger)
    {
        $this->db = $db;
        $this->logger = $logger;
    }

    // CREATE operation
    public function create($name, $mid, $responsiblePerson, $responsiblePersonBitrixId)
    {
        $sql = "INSERT INTO company (name, mid, responsible_person, responsible_person_bitrix_id) VALUES (:name, :mid, :responsiblePerson, :responsiblePersonBitrixId)";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':name' => $name,
                ':mid' => $mid,
                ':responsiblePerson' => $responsiblePerson,
                ':responsiblePersonBitrixId' => $responsiblePersonBitrixId
            ]);

            $this->logger->logInfo("New company created with name: {$name}, MID: {$mid}");
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            $this->logger->logError("Exception in company creation: " . $e->getMessage());
            return false;
        }
    }

    // READ operation by ID
    public function getById($id)
    {
        $sql = "SELECT * FROM company WHERE id = :id";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            $company = $stmt->fetch(PDO::FETCH_ASSOC);
            return $company ?: null;
        } catch (PDOException $e) {
            $this->logger->logError("Exception in company retrieval: " . $e->getMessage());
            return null;
        }
    }

    // READ operation by MID
    public function getByMid($mid)
    {
        $sql = "SELECT * FROM company WHERE mid = :mid";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':mid' => $mid]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            $this->logger->logError("Exception in company retrieval by MID: " . $e->getMessage());
            return null;
        }
    }

    // UPDATE operation
    public function update($id, $data)
    {
        $updateFields = [];
        $params = [':id' => $id];

        foreach ($data as $key => $value) {
            $updateFields[] = "$key = :$key";
            $params[":$key"] = $value;
        }

        $sql = "UPDATE company SET " . implode(", ", $updateFields) . " WHERE id = :id";
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            $this->logger->logError("Exception in company update: " . $e->getMessage());
            return false;
        }
    }

    // DELETE operation
    public function delete($id)
    {
        $sql = "DELETE FROM company WHERE id = :id";
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            $this->logger->logError("Exception in company deletion: " . $e->getMessage());
            return false;
        }
    }

    // Get all companies
    public function getAll($limit = 1000, $offset = 0)
    {
        $sql = "SELECT * FROM company LIMIT :limit OFFSET :offset";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->logger->logError("Exception in fetching companies: " . $e->getMessage());
            return [];
        }
    }
}
