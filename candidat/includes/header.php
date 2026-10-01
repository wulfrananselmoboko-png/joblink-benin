<?php // Définir $titre avant d'inclure ce fichier ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($titre ?? 'JobLink Bénin') ?> – JobLink Bénin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-md navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="index.php">JobLink Bénin</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="menu">
            <?php if (candidatConnecte()): ?>
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Tableau de bord</a></li>
                    <li class="nav-item"><a class="nav-link" href="offres.php">Offres</a></li>
                    <li class="nav-item"><a class="nav-link" href="mes_candidatures.php">Mes candidatures</a></li>
                    <li class="nav-item"><a class="nav-link" href="profil.php">Mon profil</a></li>
                </ul>
                <span class="text-white me-3"><?= htmlspecialchars($_SESSION['candidat_nom'] ?? '') ?></span>
                <a class="btn btn-outline-light btn-sm" href="logout.php">Déconnexion</a>
            <?php else: ?>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="login.php">Connexion</a></li>
                    <li class="nav-item"><a class="nav-link" href="inscription.php">Inscription</a></li>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</nav>
<main class="container py-4">
<?php afficherFlash(); ?>