<?php
require(__DIR__ . '/config/config.php');
require(__DIR__ . '/db/db.php');
require(__DIR__ . '/db/migrations.php');

$config = require(__DIR__ . '/config/config.php');
$db = new Database($config['db']);
$migrations = new Migrations($db);

$migrations->runMigrations();
$db->closeConnection();
?>