<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../classes/Candidature.php';

exigerConnexionCandidat();

$stats = Candidature::compterParStatut((int) $_SESSION['candidat_id']);

$cartes = [
    ['En attente', $stats['en_attente'], 'warning'],
    ['Retenues', $stats['retenue'], 'success'],
    ['Refusées', $stats['refusee'], 'danger'],
];

$titre = 'Tableau de bord';
require __DIR__ . '/includes/header.php';
?>

<h1 class="h3 mb-1">Bonjour, <?= htmlspecialchars($_SESSION['candidat_nom']) ?></h1>
<p class="text-muted mb-4">Voici l'état de vos candidatures.</p>

<div class="row g-3">
    <?php foreach ($cartes as [$libelle, $valeur, $couleur]): ?>
        <div class="col-md-4">
            <div class="card border-<?= $couleur ?> h-100">
                <div class="card-body text-center">
                    <div class="display-5 fw-bold"><?= (int) $valeur ?></div>
                    <div class="text-muted"><?= htmlspecialchars($libelle) ?></div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>