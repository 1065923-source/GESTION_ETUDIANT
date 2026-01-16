<?php
include 'includes/db.php';

$username = 'finance';
$password = password_hash('finance123', PASSWORD_DEFAULT);
$role = 'finance';

$stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
$stmt->execute([$username, $password, $role]);

echo "Utilisateur finance créé avec succès ✅";
