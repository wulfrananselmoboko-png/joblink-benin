<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../classes/Candidat.php';

exigerConnexion();

$recherche = trim($_GET['q'] ?? '');
$statut    = $_GET['statut'] ?? '';

$candidats = Candidat::liste($recherche, $statut);

$titre = 'Candidats';
require __DIR__ . '/includes/header.php';
?>

<h1 class="h3 mb-3">Candidats</h1>

<form method="get" action="candidats.php" class="row g-2 mb-3">
    <div class="col-md-5">
        <input type="text" name="q" class="form-control" placeholder="Rechercher (nom, prénom, e-mail)" value="<?= htmlspecialchars($recherche) ?>">
    </div>
    <div class="col-md-3">
        <select name="statut" class="form-select">
            <option value="">Tous les comptes</option>
            <option value="actif" <?= $statut === 'actif' ? 'selected' : '' ?>>Actifs</option>
            <option value="inactif" <?= $statut === 'inactif' ? 'selected' : '' ?>>Désactivés</option>
        </select>
    </div>
    <div class="col-md-4">
        <button type="submit" class="btn btn-outline-primary">Filtrer</button>
        <a href="candidats.php" class="btn btn-outline-secondary">Réinitialiser</a>
    </div>
</form>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Candidat</th>
                    <th>E-mail</th>
                    <th>Téléphone</th>
                    <th>Ville</th>
                    <th>CV</th>
                    <th>Candidatures</th>
                    <th>Compte</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($candidats)): ?>
                <tr><td colspan="8" class="text-center text-muted py-3">Aucun candidat.</td></tr>
            <?php else: ?>
                <?php foreach ($candidats as $c): ?>
                    <?php $actif = $c['statut'] === 'actif'; ?>
                    <tr>
                        <td><?= htmlspecialchars($c['prenom'] . ' ' . $c['nom']) ?></td>
                        <td><?= htmlspecialchars($c['email']) ?></td>
                        <td><?= htmlspecialchars($c['telephone']) ?></td>
                        <td><?= htmlspecialchars($c['ville'] ?? '—') ?></td>
                        <td><?= $c['a_cv'] ? '<span class="badge text-bg-success">Oui</span>' : '<span class="badge text-bg-secondary">Non</span>' ?></td>
                        <td><?= (int) $c['nb_candidatures'] ?></td>
                        <td><span class="badge text-bg-<?= $actif ? 'success' : 'danger' ?>"><?= $actif ? 'Actif' : 'Désactivé' ?></span></td>
                        <td class="text-end text-nowrap">
                            <a class="btn btn-sm btn-outline-primary" href="candidat_fiche.php?id=<?= (int) $c['id'] ?>">Fiche</a>
                            <form method="post" action="candidat_statut.php" class="d-inline"
                                  onsubmit="return confirm('<?= $actif ? 'Désactiver' : 'Réactiver' ?> ce compte ?');">
                                <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
                                <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
                                <input type="hidden" name="action" value="<?= $actif ? 'desactiver' : 'activer' ?>">
                                <input type="hidden" name="retour" value="liste">
                                <button type="submit" class="btn btn-sm btn-outline-<?= $actif ? 'danger' : 'success' ?>">
                                    <?= $actif ? 'Désactiver' : 'Activer' ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>