<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../classes/Offre.php';
require_once __DIR__ . '/../classes/Candidat.php';
require_once __DIR__ . '/../classes/Candidature.php';

exigerConnexionCandidat();

$idCandidat = (int) $_SESSION['candidat_id'];
$idOffre    = (int) ($_GET['id'] ?? 0);

// Une offre expirée, clôturée ou en brouillon n'est pas visible
$offre = $idOffre > 0 ? Offre::trouverVisible($idOffre) : null;
if (!$offre) {
    flash('danger', 'Cette offre n\'est pas disponible.');
    header('Location: offres.php');
    exit;
}

$candidat = Candidat::trouver($idCandidat);
$aCv      = (bool) $candidat->getCvFichier();
$lettre   = '';
$erreurs  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifierCsrf();
    $lettre = trim($_POST['lettre'] ?? '');

    if (Candidature::statutPour($idCandidat, $idOffre) !== null) {
        $erreurs[] = 'Vous avez déjà postulé à cette offre.';
    }
    if (!$aCv) {
        $erreurs[] = 'Vous devez d\'abord déposer votre CV dans votre profil.';
    }
    if ($lettre === '') {
        $erreurs[] = 'La lettre de motivation est obligatoire.';
    } elseif (mb_strlen($lettre) > 5000) {
        $erreurs[] = 'La lettre de motivation est trop longue (5000 caractères maximum).';
    }

    if (empty($erreurs)) {
        try {
            Candidature::creer($idCandidat, $idOffre, $lettre);
            flash('success', 'Votre candidature a bien été envoyée.');
            header('Location: mes_candidatures.php');
            exit;
        } catch (PDOException $e) {
            // 23000 = le couple (candidat, offre) existe déjà
            $erreurs[] = $e->getCode() === '23000'
                ? 'Vous avez déjà postulé à cette offre.'
                : 'Une erreur est survenue. Veuillez réessayer.';
        }
    }
}

$statutActuel = Candidature::statutPour($idCandidat, $idOffre);
$statuts = [
    'en_attente' => ['En attente', 'warning'],
    'retenue'    => ['Retenue', 'success'],
    'refusee'    => ['Refusée', 'danger'],
];

$titre = $offre['titre'];
require __DIR__ . '/includes/header.php';
?>

<a href="offres.php" class="btn btn-sm btn-outline-secondary mb-3">← Retour aux offres</a>

<div class="card mb-4">
    <div class="card-body">
        <h1 class="h3"><?= htmlspecialchars($offre['titre']) ?></h1>
        <div class="text-muted mb-3"><?= htmlspecialchars($offre['entreprise']) ?> · <?= htmlspecialchars($offre['ville']) ?></div>

        <div class="mb-3">
            <span class="badge text-bg-primary"><?= htmlspecialchars($offre['type_contrat']) ?></span>
            <span class="badge text-bg-secondary"><?= htmlspecialchars($offre['secteur']) ?></span>
        </div>

        <ul class="list-unstyled">
            <li><strong>Salaire :</strong> <?= $offre['salaire'] !== null ? number_format((float) $offre['salaire'], 0, ',', ' ') . ' FCFA' : 'À négocier' ?></li>
            <li><strong>Date limite :</strong> <?= $offre['date_limite'] ? date('d/m/Y', strtotime($offre['date_limite'])) : 'Non précisée' ?></li>
            <li><strong>Publiée le :</strong> <?= date('d/m/Y', strtotime($offre['date_publication'])) ?></li>
        </ul>

        <h2 class="h6">Description</h2>
        <p><?= nl2br(htmlspecialchars($offre['description'])) ?></p>
    </div>
</div>

<div class="card">
    <div class="card-header fw-bold">Postuler</div>
    <div class="card-body">
        <?php if ($statutActuel !== null): ?>
            <?php [$texte, $couleur] = $statuts[$statutActuel] ?? [$statutActuel, 'secondary']; ?>
            <div class="alert alert-info mb-0">
                Vous avez déjà postulé à cette offre. Statut :
                <span class="badge text-bg-<?= $couleur ?>"><?= $texte ?></span>
                — <a href="mes_candidatures.php">voir mes candidatures</a>
            </div>
        <?php else: ?>
            <?php if (!empty($erreurs)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($erreurs as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (!$aCv): ?>
                <div class="alert alert-warning">
                    Vous devez déposer votre CV avant de postuler.
                    <a href="profil.php">Aller à mon profil</a>
                </div>
            <?php else: ?>
                <p class="text-muted small">Votre CV enregistré dans votre profil sera joint automatiquement à votre candidature.</p>
            <?php endif; ?>

            <form method="post" action="offre.php?id=<?= $idOffre ?>">
                <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
                <div class="mb-3">
                    <label for="lettre" class="form-label">Lettre de motivation <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="lettre" name="lettre" rows="7" required <?= $aCv ? '' : 'disabled' ?>><?= htmlspecialchars($lettre) ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary" <?= $aCv ? '' : 'disabled' ?>>Envoyer ma candidature</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>