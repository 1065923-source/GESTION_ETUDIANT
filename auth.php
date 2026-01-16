<?php
session_start();

// Vérifie que l'utilisateur est connecté
function requireLogin() {
    if (!isset($_SESSION['user']) || !is_array($_SESSION['user'])) {
        header("Location: login.php");
        exit;
    }
}

// Vérifie que l'utilisateur a le rôle nécessaire
function requireRoles(array $roles) {
    requireLogin();
    // On vérifie que 'role' existe bien dans le tableau
    if (!isset($_SESSION['user']['role']) || !in_array($_SESSION['user']['role'], $roles)) {
        die("Accès refusé : vous n'avez pas le rôle nécessaire !");
    }
}
?>
