<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../classes/Candidature.php';

exigerConnexionCandidat();

$idCandidat = (int) $_SESSION['candidat_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifierCsrf();

    $id = (int) ($_POST['id'] ?? 0);
    if ($id > 0 && Candidature::retirer($id, $idCandidat)) {
        flash('success', 'Votre candidature a été retirée.');
    } else {
        flash('danger', 'Impossible de retirer cette candidature : elle a déjà été traitée ou n\'existe pas.');
    }
    header('Location: mes_candidatures.php');
    exit;
}

$candidatures = Candidature::duCandidat($idCandidat);
$statuts = [
    'en_attente' => ['En attente', 'warning'],
    'retenue'    => ['Retenue', 'success'],
    'refusee'    => ['Refusée', 'danger'],
];

$titre = 'Mes candidatures';
require __DIR__ . '/includes/header.php';
?>

<h1 class="h3 mb-3">Mes candidatures</h1>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Offre</th>
                    <th>Entreprise</th>
                    <th>Date</th>
                    <th>Statut</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($candidatures)): ?>
                <tr><td colspan="5" class="text-center text-muted py-3">
                    Vous n'avez pas encore postulé. <a href="offres.php">Voir les offres</a>
                </td></tr>
            <?php else: ?>
                <?php foreach ($candidatures as $c): ?>
                    <?php [$texte, $couleur] = $statuts[$c['statut']] ?? [$c['statut'], 'secondary']; ?>
                    <tr>
                        <td><a href="offre.php?id=<?= (int) $c['id_offre'] ?>"><?= htmlspecialchars($c['titre']) ?></a></td>
                        <td><?= htmlspecialchars($c['entreprise']) ?></td>
                        <td><?= date('d/m/Y', strtotime($c['date_candidature'])) ?></td>
                        <td><span class="badge text-bg-<?= $couleur ?>"><?= $texte ?></span></td>
                        <td class="text-end">
                            <?php if ($c['statut'] === 'en_attente'): ?>
                                <form method="post" action="mes_candidatures.php" class="d-inline"
                                      onsubmit="return confirm('Retirer cette candidature ?');">
                                    <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
                                    <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Retirer</button>
                                </form>
                            <?php else: ?>
                                <span class="text-muted small">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>