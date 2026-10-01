<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../classes/Candidat.php';

if (candidatConnecte()) {
    header('Location: index.php');
    exit;
}

$val     = ['nom' => '', 'prenom' => '', 'email' => '', 'telephone' => ''];
$erreurs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifierCsrf();

    $val = [
        'nom'       => trim($_POST['nom'] ?? ''),
        'prenom'    => trim($_POST['prenom'] ?? ''),
        'email'     => trim($_POST['email'] ?? ''),
        'telephone' => trim($_POST['telephone'] ?? ''),
    ];
    $mdp  = $_POST['mot_de_passe'] ?? '';
    $conf = $_POST['confirmation'] ?? '';

    // Contrôles côté serveur
    if ($val['nom'] === '' || $val['prenom'] === '' || $val['email'] === '' || $val['telephone'] === '' || $mdp === '') {
        $erreurs[] = 'Tous les champs sont obligatoires.';
    } else {
        if (mb_strlen($val['nom']) > 100 || mb_strlen($val['prenom']) > 100) {
            $erreurs[] = 'Le nom et le prénom ne doivent pas dépasser 100 caractères.';
        }
        if (!filter_var($val['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($val['email']) > 150) {
            $erreurs[] = 'L\'adresse e-mail n\'est pas valide.';
        } elseif (Candidat::emailExiste($val['email'])) {
            $erreurs[] = 'Cet e-mail est déjà utilisé.';
        }
        if (!preg_match('/^\+?[0-9][0-9 ().-]{6,28}$/', $val['telephone'])) {
            $erreurs[] = 'Le numéro de téléphone n\'est pas valide.';
        }
        if (mb_strlen($mdp) < 8) {
            $erreurs[] = 'Le mot de passe doit contenir au moins 8 caractères.';
        } elseif (strlen($mdp) > 72) {
            $erreurs[] = 'Le mot de passe est trop long (72 caractères maximum).';
        }
        if ($mdp !== $conf) {
            $erreurs[] = 'La confirmation ne correspond pas au mot de passe.';
        }
    }

    if (empty($erreurs)) {
        try {
            Candidat::inscrire($val['nom'], $val['prenom'], $val['email'], $val['telephone'], $mdp);
            flash('success', 'Votre compte a été créé. Vous pouvez vous connecter.');
            header('Location: login.php');
            exit;
        } catch (PDOException $e) {
            // 23000 = contrainte d'unicité violée (e-mail déjà pris entre-temps)
            $erreurs[] = $e->getCode() === '23000'
                ? 'Cet e-mail est déjà utilisé.'
                : 'Une erreur est survenue. Veuillez réessayer.';
        }
    }
}

$titre = 'Inscription';
require __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-7 col-lg-6">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h1 class="h4 text-center mb-4">Créer mon compte</h1>

                <?php if (!empty($erreurs)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($erreurs as $erreur): ?>
                                <li><?= htmlspecialchars($erreur) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="post" action="inscription.php">
                    <input type="hidden" name="csrf" value="<?= csrfToken() ?>">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nom" class="form-label">Nom <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nom" name="nom" value="<?= htmlspecialchars($val['nom']) ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="prenom" class="form-label">Prénom <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="prenom" name="prenom" value="<?= htmlspecialchars($val['prenom']) ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">E-mail <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($val['email']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="telephone" class="form-label">Téléphone <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="telephone" name="telephone" value="<?= htmlspecialchars($val['telephone']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="mot_de_passe" class="form-label">Mot de passe (8 caractères minimum) <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="mot_de_passe" name="mot_de_passe" required>
                    </div>

                    <div class="mb-3">
                        <label for="confirmation" class="form-label">Confirmer le mot de passe <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="confirmation" name="confirmation" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">S'inscrire</button>
                </form>

                <p class="text-center mt-3 mb-0">Déjà inscrit ? <a href="login.php">Se connecter</a></p>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>