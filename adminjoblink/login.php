<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../classes/Administrateur.php';

// Déjà connecté : direction le tableau de bord
if (adminConnecte()) {
    header('Location: index.php');
    exit;
}

$erreur = '';
$email  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email      = trim($_POST['email'] ?? '');
    $motDePasse = $_POST['mot_de_passe'] ?? '';

    if ($email === '' || $motDePasse === '') {
        $erreur = 'Veuillez remplir tous les champs.';
    } else {
        $admin = Administrateur::trouverParEmail($email);

        if ($admin && $admin->verifierMotDePasse($motDePasse)) {
            session_regenerate_id(true);
            $_SESSION['admin_id']  = $admin->getId();
            $_SESSION['admin_nom'] = $admin->getPrenom() . ' ' . $admin->getNom();
            header('Location: index.php');
            exit;
        }
        // Même message dans les deux cas : on ne dit pas si l'e-mail existe
        $erreur = 'E-mail ou mot de passe incorrect.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connexion administrateur – JobLink Bénin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container" style="max-width: 420px;">
    <div class="card shadow-sm mt-5">
        <div class="card-body p-4">
            <h1 class="h4 text-center mb-4">Espace administrateur</h1>

            <?php if ($erreur !== ''): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($erreur) ?></div>
            <?php endif; ?>

            <form method="post" action="login.php">
                <div class="mb-3">
                    <label for="email" class="form-label">E-mail</label>
                    <input type="email" class="form-control" id="email" name="email"
                           value="<?= htmlspecialchars($email) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="mot_de_passe" class="form-label">Mot de passe</label>
                    <input type="password" class="form-control" id="mot_de_passe" name="mot_de_passe" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Se connecter</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>