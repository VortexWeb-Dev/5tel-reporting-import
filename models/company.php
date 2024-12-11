<?php

class Company
{
    private $db;
    private $logger;

    public function __construct($db, $logger)
    {
        $this->db = $db;
        $this->logger = $logger;
    }

    // CREATE operation
    public function create($name, $mid, $responsiblePerson, $responsiblePersonBitrixId)
    {
        $sql = "INSERT INTO company (name, mid, responsible_person, responsible_person_bitrix_id) VALUES ($1, $2, $3, $4)";

        try {
            $result = pg_query_params($this->db->getConnection(), $sql, [$name, $mid, $responsiblePerson, $responsiblePersonBitrixId]);

            if ($result) {
                $this->logger->logInfo("New company created with name: {$name}, MID: {$mid}");
                return pg_last_oid($result);
            }

            $this->logger->logError("Failed to create company. SQL Error: " . pg_last_error($this->db->getConnection()));
            return false;
        } catch (Exception $e) {
            $this->logger->logError("Exception in company creation: " . $e->getMessage());
            return false;
        }
    }

    function getResponsiblePerson($mid)
    {
        $sql = "SELECT responsible_person_bitrix_id, responsible_person FROM company WHERE mid = $1";
        try {
            $result = pg_query_params($this->db->getConnection(), $sql, [$mid]);
            if ($result) {
                $company = pg_fetch_assoc($result);
                if ($company) {
                    $this->logger->logInfo("Company fetched with MID: {$mid}");
                    return $company;
                } else {
                    $this->logger->logWarning("No company found with MID: {$mid}");
                    return null;
                }
            }
        } catch (Exception $e) {
            $this->logger->logError("Exception in company retrieval: " . $e->getMessage());
            return false;
        } finally {
            pg_free_result($result);
        }
    }

    // READ operation by ID
    public function getById($id)
    {
        $sql = "SELECT id, name, mid, responsible_person, responsible_person_bitrix_id FROM company WHERE id = $1";

        try {
            $result = pg_query_params($this->db->getConnection(), $sql, [$id]);

            if ($result) {
                $company = pg_fetch_assoc($result);
                if ($company) {
                    $this->logger->logInfo("Company fetched with ID: {$id}");
                    return $company;
                } else {
                    $this->logger->logWarning("No company found with ID: {$id}");
                    return null;
                }
            }

            $this->logger->logError("Failed to fetch company. SQL Error: " . pg_last_error($this->db->getConnection()));
            return null;
        } catch (Exception $e) {
            $this->logger->logError("Exception in company retrieval: " . $e->getMessage());
            return null;
        }
    }

    // READ operation by MID
    public function getByMid($mid)
    {
        $sql = "SELECT id, name, mid, responsible_person, responsible_person_bitrix_id FROM company WHERE mid = $1";

        try {
            $result = pg_query_params($this->db->getConnection(), $sql, [$mid]);

            if ($result) {
                $company = pg_fetch_assoc($result);
                if ($company) {
                    $this->logger->logInfo("Company fetched with MID: {$mid}");
                    return $company;
                } else {
                    $this->logger->logWarning("No company found with MID: {$mid}");
                    return null;
                }
            }

            $this->logger->logError("Failed to fetch company by MID. SQL Error: " . pg_last_error($this->db->getConnection()));
            return null;
        } catch (Exception $e) {
            $this->logger->logError("Exception in company retrieval by MID: " . $e->getMessage());
            return null;
        }
    }

    // UPDATE operation
    public function update($id, $name = null, $mid = null, $responsiblePerson = null, $responsiblePersonBitrixId = null)
    {
        // Dynamically build update query
        $updateFields = [];
        $params = [];
        $paramCount = 1;

        if ($name !== null) {
            $updateFields[] = "name = $" . $paramCount;
            $params[] = $name;
            $paramCount++;
        }

        if ($mid !== null) {
            $updateFields[] = "mid = $" . $paramCount;
            $params[] = $mid;
            $paramCount++;
        }

        if ($responsiblePerson !== null) {
            $updateFields[] = "responsible_person = $" . $paramCount;
            $params[] = $responsiblePerson;
            $paramCount++;
        }

        if ($responsiblePersonBitrixId !== null) {
            $updateFields[] = "responsible_person_bitrix_id = $" . $paramCount;
            $params[] = $responsiblePersonBitrixId;
            $paramCount++;
        }

        // Add ID as last parameter
        $params[] = $id;

        $sql = "UPDATE company SET " . implode(", ", $updateFields) . " WHERE id = $" . $paramCount;

        try {
            $result = pg_query_params($this->db->getConnection(), $sql, $params);

            if ($result) {
                $this->logger->logInfo("Company updated with ID: {$id}");
                return true;
            }

            $this->logger->logError("Failed to update company with ID: {$id}. SQL Error: " . pg_last_error($this->db->getConnection()));
            return false;
        } catch (Exception $e) {
            $this->logger->logError("Exception in company update: " . $e->getMessage());
            return false;
        }
    }

    // DELETE operation
    public function delete($id)
    {
        $sql = "DELETE FROM company WHERE id = $1";

        try {
            $result = pg_query_params($this->db->getConnection(), $sql, [$id]);

            if ($result) {
                $this->logger->logInfo("Company deleted with ID: {$id}");
                return true;
            }

            $this->logger->logError("Failed to delete company with ID: {$id}. SQL Error: " . pg_last_error($this->db->getConnection()));
            return false;
        } catch (Exception $e) {
            $this->logger->logError("Exception in company deletion: " . $e->getMessage());
            return false;
        }
    }

    // Get all companies
    public function getAll($limit = 1000, $offset = 0)
    {
        $sql = "SELECT id, name, mid, responsible_person, responsible_person_bitrix_id FROM company LIMIT $1 OFFSET $2";

        try {
            $result = pg_query_params($this->db->getConnection(), $sql, [$limit, $offset]);

            if ($result) {
                $companies = [];
                while ($row = pg_fetch_assoc($result)) {
                    $companies[] = $row;
                }
                $this->logger->logInfo("Fetched companies. Limit: {$limit}, Offset: {$offset}");
                return $companies;
            }

            $this->logger->logError("Failed to fetch companies. SQL Error: " . pg_last_error($this->db->getConnection()));
            return [];
        } catch (Exception $e) {
            $this->logger->logError("Exception in fetching companies: " . $e->getMessage());
            return [];
        }
    }
}
