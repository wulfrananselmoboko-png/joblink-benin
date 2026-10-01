<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../classes/Entreprise.php';

exigerConnexion();

$id         = (int) ($_GET['id'] ?? 0);
$entreprise = $id > 0 ? Entreprise::trouver($id) : null;

if (!$entreprise) {
    flash('danger', 'Entreprise introuvable.');
    header('Location: entreprises.php');
    exit;
}

$offres  = Entreprise::offres($id);
$statuts = [
    'brouillon' => ['Brouillon', 'secondary'],
    'publiee'   => ['Publiée', 'success'],
    'cloturee'  => ['Clôturée', 'dark'],
];

$titre = 'Offres de ' . $entreprise->getNom();
require __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Offres de <?= htmlspecialchars($entreprise->getNom()) ?></h1>
    <a href="entreprises.php" class="btn btn-outline-secondary">← Retour aux entreprises</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Titre</th>
                    <th>Contrat</th>
                    <th>Ville</th>
                    <th>Date limite</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($offres)): ?>
                <tr><td colspan="5" class="text-center text-muted py-3">Cette entreprise n'a pas encore d'offre.</td></tr>
            <?php else: ?>
                <?php foreach ($offres as $o): ?>
                    <?php [$texte, $couleur] = $statuts[$o['statut']] ?? [$o['statut'], 'secondary']; ?>
                    <tr>
                        <td><?= htmlspecialchars($o['titre']) ?></td>
                        <td><?= htmlspecialchars($o['type_contrat']) ?></td>
                        <td><?= htmlspecialchars($o['ville']) ?></td>
                        <td><?= $o['date_limite'] ? date('d/m/Y', strtotime($o['date_limite'])) : '—' ?></td>
                        <td><span class="badge text-bg-<?= $couleur ?>"><?= $texte ?></span></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>