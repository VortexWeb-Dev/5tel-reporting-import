<?php

$sql = '
CREATE TABLE IF NOT EXISTS "company" (
    "id" SERIAL PRIMARY KEY,
    "name" VARCHAR(100),
    "mid" VARCHAR(100) UNIQUE,
    "responsible_person" VARCHAR(100),
    "responsible_person_bitrix_id" INTEGER REFERENCES users(bitrix_id)
);
';

$result = pg_query($this->db->getConnection(), $sql);
if(!$result) {
    die("Error in migration: " . pg_last_error($this->db->getConnection()));
}