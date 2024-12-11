<?php

// Create 'users' table
$sql = '
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100),
    bitrix_id INTEGER UNIQUE
);
';

$result = pg_query($this->db->getConnection(), $sql);
if (!$result) {
    die("Error in migration: " . pg_last_error($this->db->getConnection()));
}
