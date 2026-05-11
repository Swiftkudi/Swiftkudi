<?php
$pdo = new PDO('sqlite:database/database.sqlite');
$stmt = $pdo->query('SELECT * FROM system_settings WHERE key = "email_verification_required"');
$result = $stmt->fetch(PDO::FETCH_ASSOC);
if ($result) {
    echo 'Database value: ' . $result['value'] . PHP_EOL;
    echo 'Type: ' . $result['type'] . PHP_EOL;
    echo 'Group: ' . $result['group'] . PHP_EOL;
} else {
    echo 'Setting not found in database' . PHP_EOL;
}
?>