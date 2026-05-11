<?php
$pdo = new PDO('sqlite:database/database.sqlite');
$stmt = $pdo->query("SELECT * FROM migrations ORDER BY id DESC LIMIT 10");
$migrations = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Recent migrations:\n";
foreach ($migrations as $migration) {
    echo "- " . $migration['migration'] . " (batch: " . $migration['batch'] . ")\n";
}
?>