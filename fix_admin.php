<?php
require_once 'config.php';

$email = 'admin@example.com';
$new_password = 'admin123';
$new_hash = password_hash($new_password, PASSWORD_DEFAULT);

$stmt = database_data::$pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
if ($stmt->execute([$new_hash, $email])) {
    echo "Admin password successfully updated in the database! You can now log in with admin123. Please delete this file.";
} else {
    echo "Failed to update database.";
}
?>