<?php
session_start();

function adminConnecte(): bool
{
    return isset($_SESSION['admin_id']);
}

// À appeler en haut de chaque page protégée
function exigerConnexion(): void
{
    if (!adminConnecte()) {
        header('Location: login.php');
        exit;
    }
}