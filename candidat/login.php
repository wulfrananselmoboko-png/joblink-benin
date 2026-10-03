<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../classes/Candidat.php';

if (candidatConnecte()) {
    header('Location: index.php');
    exit;
}

$erreur = '';
$email  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifierCsrf();

    $email      = trim($_POST['email'] ?? '');
    $motDePasse = $_POST['mot_de_passe'] ?? '';

    if ($email === '' || $motDePasse === '') {
        $erreur = 'Veuillez remplir tous les champs.';
    } else {
        $candidat = Candidat::trouverParEmail($email);

        if ($candidat && $candidat->verifierMotDePasse($motDePasse)) {
            if (!$candidat->estActif()) {
                $erreur = 'Votre compte est désactivé. Veuillez contacter l\'administrateur.';
            } else {
                session_regenerate_id(true);
                $_SESSION['candidat_id']  = $candidat->getId();
                $_SESSION['candidat_nom'] = $candidat->getPrenom() . ' ' . $candidat->getNom();
                $retour = $_SESSION['retour'] ?? 'index.php';
                unset($_SESSION['retour']);
                header('Location: ' . $retour);
                exit;
                 }
        } else {
            // Même message pour un e-mail inconnu et un mauvais mot de passe
            $erreur = 'E-mail ou mot de passe incorrect.';
        }
    }
}

$titre = 'Connexion';
require __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h1 class="h4 text-center mb-4">Connexion</h1>

                <?php if ($erreur !== ''): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($erreur) ?></div>
                <?php endif; ?>

                <form method="post" action="login.php">
                    <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
                    <div class="mb-3">
                        <label for="email" class="form-label">E-mail</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="mot_de_passe" class="form-label">Mot de passe</label>
                        <input type="password" class="form-control" id="mot_de_passe" name="mot_de_passe" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Se connecter</button>
                </form>

                <p class="text-center mt-3 mb-0">Pas encore de compte ? <a href="inscription.php">S'inscrire</a></p>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>