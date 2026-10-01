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
<nav class="navbar navbar-expand-md navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">JobLink Admin</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="menu">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="index.php">Tableau de bord</a></li>
                <li class="nav-item"><a class="nav-link" href="entreprises.php">Entreprises</a></li>
                <li class="nav-item"><a class="nav-link" href="offres.php">Offres
                </a></li>
                <li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Listes</a>
    <ul class="dropdown-menu">
        <li><a class="dropdown-item" href="reference.php?type=secteur">Secteurs</a></li>
        <li><a class="dropdown-item" href="reference.php?type=ville">Villes</a></li>
        <li><a class="dropdown-item" href="reference.php?type=type_contrat">Types de contrat</a></li>
    </ul>
</li>
            </ul>
            <span class="text-white me-3"><?= htmlspecialchars($_SESSION['admin_nom'] ?? '') ?></span>
            <a class="btn btn-outline-light btn-sm" href="logout.php">Déconnexion</a>
        </div>
    </div>
</nav>
<main class="container py-4">
<?php afficherFlash(); ?>