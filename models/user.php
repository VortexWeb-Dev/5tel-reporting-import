<?php 

class User {
    private $db;
    private $logger;

    public function __construct($db, $logger) {
        $this->db = $db;
        $this->logger = $logger;
    }

    // CREATE operation
    public function create($name, $bitrixId) {
        $sql = "INSERT INTO users (name, bitrix_id) VALUES ($1, $2)";
        
        try {
            $result = pg_query_params($this->db->getConnection(), $sql, [$name, $bitrixId]);

            if ($result) {
                $this->logger->logInfo("New user created with name: {$name} and Bitrix ID: {$bitrixId}");
                return pg_last_oid($result);
            }

            $this->logger->logError("Failed to create user. SQL Error: " . pg_last_error($this->db->getConnection()));
            return false;
        } catch (Exception $e) {
            $this->logger->logError("Exception in user creation: " . $e->getMessage());
            return false;
        }
    }

    public function getUserBitrixId($name) {
        $sql = "SELECT bitrix_id FROM users WHERE name = $1";
        
        try {
            $result = pg_query_params($this->db->getConnection(), $sql, [$name]);

            if ($result) {
                $user = pg_fetch_assoc($result);
                if ($user) {
                    $this->logger->logInfo("User fetched with name: {$name}");
                    return $user['bitrix_id'];
                } else {
                    $this->logger->logWarning("No user found with name: {$name}");
                    return null;
                }
            }

            $this->logger->logError("Failed to fetch user by name. SQL Error: " . pg_last_error($this->db->getConnection()));
            return null;
        } catch (Exception $e) {
            $this->logger->logError("Exception in user retrieval by name: " . $e->getMessage());
            return null;
        }
    }

    // READ operation by ID
    public function getById($id) {
        $sql = "SELECT id, name, bitrix_id FROM users WHERE id = $1";
        
        try {
            $result = pg_query_params($this->db->getConnection(), $sql, [$id]);

            if ($result) {
                $user = pg_fetch_assoc($result);
                if ($user) {
                    $this->logger->logInfo("User fetched with ID: {$id}");
                    return $user;
                } else {
                    $this->logger->logWarning("No user found with ID: {$id}");
                    return null;
                }
            }

            $this->logger->logError("Failed to fetch user. SQL Error: " . pg_last_error($this->db->getConnection()));
            return null;
        } catch (Exception $e) {
            $this->logger->logError("Exception in user retrieval: " . $e->getMessage());
            return null;
        }
    }

    // READ operation by Bitrix ID
    public function getByBitrixId($bitrixId) {
        $sql = "SELECT id, name, bitrix_id FROM users WHERE bitrix_id = $1";
        
        try {
            $result = pg_query_params($this->db->getConnection(), $sql, [$bitrixId]);

            if ($result) {
                $user = pg_fetch_assoc($result);
                if ($user) {
                    $this->logger->logInfo("User fetched with Bitrix ID: {$bitrixId}");
                    return $user;
                } else {
                    $this->logger->logWarning("No user found with Bitrix ID: {$bitrixId}");
                    return null;
                }
            }

            $this->logger->logError("Failed to fetch user by Bitrix ID. SQL Error: " . pg_last_error($this->db->getConnection()));
            return null;
        } catch (Exception $e) {
            $this->logger->logError("Exception in user retrieval by Bitrix ID: " . $e->getMessage());
            return null;
        }
    }

    // UPDATE operation
    public function update($id, $name, $bitrixId = null) {
        // Dynamically build update query
        $updateFields = [];
        $params = [];
        $paramCount = 1;

        if ($name !== null) {
            $updateFields[] = "name = $" . $paramCount;
            $params[] = $name;
            $paramCount++;
        }

        if ($bitrixId !== null) {
            $updateFields[] = "bitrix_id = $" . $paramCount;
            $params[] = $bitrixId;
            $paramCount++;
        }

        // Add ID as last parameter
        $params[] = $id;

        $sql = "UPDATE users SET " . implode(", ", $updateFields) . " WHERE id = $" . $paramCount;
        
        try {
            $result = pg_query_params($this->db->getConnection(), $sql, $params);

            if ($result) {
                $this->logger->logInfo("User updated with ID: {$id}");
                return true;
            }

            $this->logger->logError("Failed to update user with ID: {$id}. SQL Error: " . pg_last_error($this->db->getConnection()));
            return false;
        } catch (Exception $e) {
            $this->logger->logError("Exception in user update: " . $e->getMessage());
            return false;
        }
    }

    // DELETE operation
    public function delete($id) {
        $sql = "DELETE FROM users WHERE id = $1";
        
        try {
            $result = pg_query_params($this->db->getConnection(), $sql, [$id]);

            if ($result) {
                $this->logger->logInfo("User deleted with ID: {$id}");
                return true;
            }

            $this->logger->logError("Failed to delete user with ID: {$id}. SQL Error: " . pg_last_error($this->db->getConnection()));
            return false;
        } catch (Exception $e) {
            $this->logger->logError("Exception in user deletion: " . $e->getMessage());
            return false;
        }
    }

    // Get all users
    public function getAll($limit = 1000, $offset = 0) {
        $sql = "SELECT id, name, bitrix_id FROM users LIMIT $1 OFFSET $2";
        
        try {
            $result = pg_query_params($this->db->getConnection(), $sql, [$limit, $offset]);

            if ($result) {
                $users = [];
                while ($row = pg_fetch_assoc($result)) {
                    $users[] = $row;
                }
                $this->logger->logInfo("Fetched users. Limit: {$limit}, Offset: {$offset}");
                return $users;
            }

            $this->logger->logError("Failed to fetch users. SQL Error: " . pg_last_error($this->db->getConnection()));
            return [];
        } catch (Exception $e) {
            $this->logger->logError("Exception in fetching users: " . $e->getMessage());
            return [];
        }
    }

    // Bulk Import function
    public function bulkImport($users) {
        // Start a transaction to ensure data integrity
        pg_query($this->db->getConnection(), 'BEGIN');

        $successCount = 0;
        $failedCount = 0;
        $errors = [];

        foreach ($users as $index => $user) {
            $sql = "INSERT INTO users (name, bitrix_id) VALUES ($1, $2)";
            $params = [
                $user['name'] ?? null,
                $user['bitrix_id'] ?? null
            ];

            $result = pg_query_params($this->db->getConnection(), $sql, $params);

            if ($result) {
                $successCount++;
            } else {
                $failedCount++;
                $errors[] = [
                    'index' => $index,
                    'error' => pg_last_error($this->db->getConnection()),
                    'user' => $user
                ];
            }
        }

        // Commit or rollback based on success
        if ($failedCount === 0) {
            pg_query($this->db->getConnection(), 'COMMIT');
            $this->logger->logInfo("Bulk import successful. Total records: {$successCount}");
            return [
                'success' => true,
                'total_records' => count($users),
                'imported_records' => $successCount
            ];
        } else {
            pg_query($this->db->getConnection(), 'ROLLBACK');
            $this->logger->logError("Bulk import failed. Successful: {$successCount}, Failed: {$failedCount}");
            return [
                'success' => false,
                'total_records' => count($users),
                'imported_records' => $successCount,
                'failed_records' => $failedCount,
                'errors' => $errors
            ];
        }
    }
}