<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../classes/Candidat.php';
require_once __DIR__ . '/../classes/Candidature.php';
require_once __DIR__ . '/../classes/Reference.php';

exigerConnexion();

$id       = (int) ($_GET['id'] ?? 0);
$candidat = $id > 0 ? Candidat::trouver($id) : null;

if (!$candidat) {
    flash('danger', 'Candidat introuvable.');
    header('Location: candidats.php');
    exit;
}

$ville = $candidat->getIdVille() ? Reference::trouver('ville', $candidat->getIdVille()) : null;
$candidatures = Candidature::duCandidat($id);
$actif = $candidat->estActif();

$statuts = [
    'en_attente' => ['En attente', 'warning'],
    'retenue'    => ['Retenue', 'success'],
    'refusee'    => ['Refusée', 'danger'],
];

$titre = 'Fiche de ' . $candidat->getPrenom() . ' ' . $candidat->getNom();
require __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0"><?= htmlspecialchars($candidat->getPrenom() . ' ' . $candidat->getNom()) ?></h1>
    <a href="candidats.php" class="btn btn-outline-secondary">← Retour aux candidats</a>
</div>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header fw-bold">Profil</div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item"><strong>E-mail :</strong> <?= htmlspecialchars($candidat->getEmail()) ?></li>
                <li class="list-group-item"><strong>Téléphone :</strong> <?= htmlspecialchars($candidat->getTelephone()) ?></li>
                <li class="list-group-item"><strong>Ville :</strong> <?= htmlspecialchars($ville['libelle'] ?? 'Non renseignée') ?></li>
                <li class="list-group-item"><strong>Inscrit le :</strong> <?= date('d/m/Y', strtotime($candidat->getDateInscription())) ?></li>
                <li class="list-group-item">
                    <strong>Compte :</strong>
                    <span class="badge text-bg-<?= $actif ? 'success' : 'danger' ?>"><?= $actif ? 'Actif' : 'Désactivé' ?></span>
                </li>
                <li class="list-group-item">
                    <strong>CV :</strong>
                    <?php if ($candidat->getCvFichier()): ?>
                        <a href="candidat_cv.php?id=<?= $id ?>" class="btn btn-sm btn-outline-primary ms-1">Télécharger le CV</a>
                    <?php else: ?>
                        <span class="text-muted">Aucun CV déposé</span>
                    <?php endif; ?>
                </li>
            </ul>
            <div class="card-body">
                <form method="post" action="candidat_statut.php"
                      onsubmit="return confirm('<?= $actif ? 'Désactiver' : 'Réactiver' ?> ce compte ?');">
                    <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
                    <input type="hidden" name="id" value="<?= $id ?>">
                    <input type="hidden" name="action" value="<?= $actif ? 'desactiver' : 'activer' ?>">
                    <input type="hidden" name="retour" value="fiche">
                    <button type="submit" class="btn btn-<?= $actif ? 'danger' : 'success' ?>">
                        <?= $actif ? 'Désactiver le compte' : 'Réactiver le compte' ?>
                    </button>
                </form>
                <?php if ($actif): ?>
                    <p class="small text-muted mt-2 mb-0">Un compte désactivé ne peut plus se connecter.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card">
            <div class="card-header fw-bold">Candidatures (<?= count($candidatures) ?>)</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Offre</th><th>Entreprise</th><th>Date</th><th>Statut</th></tr>
                    </thead>
                    <tbody>
                    <?php if (empty($candidatures)): ?>
                        <tr><td colspan="4" class="text-center text-muted py-3">Aucune candidature.</td></tr>
                    <?php else: ?>
                        <?php foreach ($candidatures as $c): ?>
                            <?php [$texte, $couleur] = $statuts[$c['statut']] ?? [$c['statut'], 'secondary']; ?>
                            <tr>
                                <td><a href="candidatures_offre.php?id=<?= (int) $c['id_offre'] ?>"><?= htmlspecialchars($c['titre']) ?></a></td>
                                <td><?= htmlspecialchars($c['entreprise']) ?></td>
                                <td><?= date('d/m/Y', strtotime($c['date_candidature'])) ?></td>
                                <td><span class="badge text-bg-<?= $couleur ?>"><?= $texte ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>