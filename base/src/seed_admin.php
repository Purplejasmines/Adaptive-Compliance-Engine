<?php
// seed_admin.php - quick script to insert a test admin with a hashed password.
// WARNING: Keep this file outside webroot or remove after use in production.
require_once 'db_connect.php';

$name = 'Super Admin';
$email = 'admin@nra.gov';
$plain = 'AdminPass123!'; // change before running in any shared environment
$hash = password_hash($plain, PASSWORD_DEFAULT);

// Insert (use prepared statements)
$stmt = $pdo->prepare("INSERT INTO tax_admins (full_name, email, password_hash) VALUES (?, ?, ?)");
try {
    $stmt->execute([$name, $email, $hash]);
    echo "Inserted admin: $email with password: $plain\n";
} catch (PDOException $e) {
    echo 'Error inserting admin: ' . $e->getMessage();
}

?>
