<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../classes/Entreprise.php';
require_once __DIR__ . '/../classes/Offre.php';
require_once __DIR__ . '/../classes/Candidat.php';
require_once __DIR__ . '/../classes/Candidature.php';

exigerConnexion();

$nbEntreprises  = Entreprise::compter();
$nbOffres       = Offre::compterPubliees();
$nbCandidats    = Candidat::compter();
$nbCandidatures = Candidature::compter();
$dernieres      = Candidature::dernieres(5);

// Texte et couleur Bootstrap de chaque statut
$statuts = [
    'en_attente' => ['En attente', 'warning'],
    'retenue'    => ['Retenue', 'success'],
    'refusee'    => ['Refusée', 'danger'],
];

$titre = 'Tableau de bord';
require __DIR__ . '/includes/header.php';
?>

<h1 class="h3 mb-4">Tableau de bord</h1>

<div class="row g-3 mb-4">
    <?php
    $cartes = [
        ['Entreprises', $nbEntreprises, 'primary'],
        ['Offres publiées', $nbOffres, 'success'],
        ['Candidats inscrits', $nbCandidats, 'info'],
        ['Candidatures reçues', $nbCandidatures, 'warning'],
    ];
    foreach ($cartes as [$libelle, $valeur, $couleur]): ?>
        <div class="col-6 col-lg-3">
            <div class="card border-<?= $couleur ?> h-100">
                <div class="card-body text-center">
                    <div class="display-6 fw-bold"><?= (int) $valeur ?></div>
                    <div class="text-muted"><?= htmlspecialchars($libelle) ?></div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="card">
    <div class="card-header fw-bold">5 dernières candidatures</div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Candidat</th>
                    <th>Offre</th>
                    <th>Date</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($dernieres)): ?>
                <tr><td colspan="4" class="text-center text-muted py-3">Aucune candidature pour le moment.</td></tr>
            <?php else: ?>
                <?php foreach ($dernieres as $c): ?>
                    <?php [$texte, $couleur] = $statuts[$c['statut']] ?? [$c['statut'], 'secondary']; ?>
                    <tr>
                        <td><?= htmlspecialchars($c['prenom'] . ' ' . $c['nom']) ?></td>
                        <td><?= htmlspecialchars($c['titre']) ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($c['date_candidature'])) ?></td>
                        <td><span class="badge text-bg-<?= $couleur ?>"><?= $texte ?></span></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>