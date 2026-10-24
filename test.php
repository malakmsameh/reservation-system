<?php
$hash = '$2y$10$CwM9UFU79lGuVS2ZQ3pS9uWk9VKm/E3LcRuVz76CnGCZMSRJTgRFW';
$password = 'admin123';

if (password_verify($password, $hash)) {
    echo "The password is VALID! The issue is in your login form code.";
} else {
    echo "The password is INVALID. The hash in the database is wrong or has hidden spaces.";
}
?>