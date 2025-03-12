<?php

class User
{
    private PDO $db;
    private $logger;

    public function __construct(PDO $db, $logger)
    {
        $this->db = $db;
        $this->logger = $logger;
    }

    // CREATE operation
    public function create($name, $bitrixId)
    {
        $sql = "INSERT INTO users (name, bitrix_id) VALUES (:name, :bitrixId)";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['name' => $name, 'bitrixId' => $bitrixId]);

            $this->logger->logInfo("New user created with name: {$name} and Bitrix ID: {$bitrixId}");
            return $this->db->lastInsertId();
        } catch (Exception $e) {
            $this->logger->logError("Exception in user creation: " . $e->getMessage());
            return false;
        }
    }

    public function getUserBitrixId($name)
    {
        $sql = "SELECT bitrix_id FROM users WHERE name = :name";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['name' => $name]);
            return $stmt->fetchColumn();
        } catch (Exception $e) {
            $this->logger->logError("Exception in user retrieval by name: " . $e->getMessage());
            return null;
        }
    }

    public function getById($id)
    {
        $sql = "SELECT id, name, bitrix_id FROM users WHERE id = :id";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->logger->logError("Exception in user retrieval: " . $e->getMessage());
            return null;
        }
    }

    public function getByBitrixId($bitrixId)
    {
        $sql = "SELECT id, name, bitrix_id FROM users WHERE bitrix_id = :bitrixId";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['bitrixId' => $bitrixId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->logger->logError("Exception in user retrieval by Bitrix ID: " . $e->getMessage());
            return null;
        }
    }

    public function update($id, $name, $bitrixId = null)
    {
        $sql = "UPDATE users SET name = :name, bitrix_id = :bitrixId WHERE id = :id";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $id, 'name' => $name, 'bitrixId' => $bitrixId]);
            return true;
        } catch (Exception $e) {
            $this->logger->logError("Exception in user update: " . $e->getMessage());
            return false;
        }
    }

    public function delete($id)
    {
        $sql = "DELETE FROM users WHERE id = :id";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $id]);
            return true;
        } catch (Exception $e) {
            $this->logger->logError("Exception in user deletion: " . $e->getMessage());
            return false;
        }
    }

    public function getAll($limit = 1000, $offset = 0)
    {
        $sql = "SELECT id, name, bitrix_id FROM users LIMIT :limit OFFSET :offset";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->logger->logError("Exception in fetching users: " . $e->getMessage());
            return [];
        }
    }

    public function bulkImport($users)
    {
        $this->db->beginTransaction();
        $successCount = 0;
        $errors = [];

        try {
            $sql = "INSERT INTO users (name, bitrix_id) VALUES (:name, :bitrixId)";
            $stmt = $this->db->prepare($sql);

            foreach ($users as $user) {
                try {
                    if (empty($user['name']) || empty($user['bitrix_id'])) {
                        throw new Exception("Invalid data: name or bitrix_id is empty.");
                    }
                    $stmt->execute([
                        'name' => $user['name'],
                        'bitrixId' => $user['bitrix_id']
                    ]);
                    $successCount++;
                } catch (Exception $e) {
                    $errors[] = "Error inserting user {$user['name']} ({$user['bitrix_id']}): " . $e->getMessage();
                    $this->logger->logError(end($errors));
                }
            }

            if ($successCount > 0) {
                $this->db->commit();
                return ['success' => true, 'total_records' => $successCount, 'errors' => $errors];
            } else {
                $this->db->rollBack();
                return ['success' => false, 'error' => 'No records inserted.', 'errors' => $errors];
            }
        } catch (Exception $e) {
            $this->db->rollBack();
            $this->logger->logError("Bulk import failed: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
