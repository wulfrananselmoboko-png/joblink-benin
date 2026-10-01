<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../classes/Offre.php';
require_once __DIR__ . '/../classes/Entreprise.php';
require_once __DIR__ . '/../classes/Candidature.php';

exigerConnexion();

$id    = (int) ($_GET['id'] ?? 0);
$offre = $id > 0 ? Offre::trouver($id) : null;

if (!$offre) {
    flash('danger', 'Offre introuvable.');
    header('Location: candidatures.php');
    exit;
}

$entreprise   = Entreprise::trouver($offre->getIdEntreprise());
$candidatures = Candidature::parOffre($id);

$statuts = [
    'en_attente' => ['En attente', 'warning'],
    'retenue'    => ['Retenue', 'success'],
    'refusee'    => ['Refusée', 'danger'],
];

$titre = 'Candidatures : ' . $offre->getTitre();
require __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-0"><?= htmlspecialchars($offre->getTitre()) ?></h1>
        <div class="text-muted"><?= htmlspecialchars($entreprise ? $entreprise->getNom() : '') ?> · <?= count($candidatures) ?> candidature(s)</div>
    </div>
    <a href="candidatures.php" class="btn btn-outline-secondary">← Retour</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Candidat</th>
                    <th>Contact</th>
                    <th>Date</th>
                    <th>CV</th>
                    <th>Statut</th>
                    <th style="min-width: 230px;">Modifier le statut</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($candidatures)): ?>
                <tr><td colspan="6" class="text-center text-muted py-3">Cette offre n'a pas encore reçu de candidature.</td></tr>
            <?php else: ?>
                <?php foreach ($candidatures as $c): ?>
                    <?php [$texte, $couleur] = $statuts[$c['statut']] ?? [$c['statut'], 'secondary']; ?>
                    <tr>
                        <td>
                            <a href="candidat_fiche.php?id=<?= (int) $c['id_candidat'] ?>">
                                <?= htmlspecialchars($c['prenom'] . ' ' . $c['nom']) ?>
                            </a>
                        </td>
                        <td class="small">
                            <?= htmlspecialchars($c['email']) ?><br>
                            <?= htmlspecialchars($c['telephone']) ?>
                        </td>
                        <td><?= date('d/m/Y', strtotime($c['date_candidature'])) ?></td>
                        <td>
                            <?php if ($c['a_cv']): ?>
                                <a class="btn btn-sm btn-outline-primary" href="candidat_cv.php?id=<?= (int) $c['id_candidat'] ?>">CV</a>
                            <?php else: ?>
                                <span class="text-muted small">—</span>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge text-bg-<?= $couleur ?>"><?= $texte ?></span></td>
                        <td>
                            <form method="post" action="candidature_statut.php" class="d-flex gap-2">
                                <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
                                <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
                                <select name="statut" class="form-select form-select-sm">
                                    <?php foreach ($statuts as $cle => [$libelle, $couleurOption]): ?>
                                        <option value="<?= $cle ?>" <?= $c['statut'] === $cle ? 'selected' : '' ?>><?= $libelle ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="btn btn-sm btn-primary">OK</button>
                            </form>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="6" class="border-top-0 pt-0">
                            <details>
                                <summary class="small text-primary" style="cursor: pointer;">Lire la lettre de motivation</summary>
                                <div class="bg-light rounded p-3 mt-2 small"><?= nl2br(htmlspecialchars($c['lettre_motivation'])) ?></div>
                            </details>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>