<?php // Définir $titre avant d'inclure ce fichier ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($titre ?? 'Administration') ?> – JobLink Bénin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">JobLink Admin</a>
        <div>
            <span class="text-white me-3"><?= htmlspecialchars($_SESSION['admin_nom'] ?? '') ?></span>
            <a class="btn btn-outline-light btn-sm" href="logout.php">Déconnexion</a>
        </div>
    </div>
</nav>
<main class="container py-4">