<?php
$pdo = new PDO('sqlite:database/database.sqlite');
$stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'");
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo "Tables in database:\n";
foreach ($tables as $table) {
    echo "- $table\n";
}
?>