<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../classes/Entreprise.php';

exigerConnexion();

$entreprises = Entreprise::toutes();

$titre = 'Entreprises';
require __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Entreprises</h1>
    <a href="entreprise_form.php" class="btn btn-primary">+ Ajouter une entreprise</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Nom</th>
                    <th>Secteur</th>
                    <th>Ville</th>
                    <th>Téléphone</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($entreprises)): ?>
                <tr><td colspan="5" class="text-center text-muted py-3">Aucune entreprise.</td></tr>
            <?php else: ?>
                <?php foreach ($entreprises as $e): ?>
                    <tr>
                        <td><?= htmlspecialchars($e['nom']) ?></td>
                        <td><?= htmlspecialchars($e['secteur']) ?></td>
                        <td><?= htmlspecialchars($e['ville']) ?></td>
                        <td><?= htmlspecialchars($e['telephone'] ?? '') ?></td>
                        <td class="text-end text-nowrap">
                            <a class="btn btn-sm btn-outline-secondary"
                               href="entreprise_offres.php?id=<?= (int) $e['id'] ?>">Offres (<?= (int) $e['nb_offres'] ?>)</a>
                            <a class="btn btn-sm btn-outline-primary"
                               href="entreprise_form.php?id=<?= (int) $e['id'] ?>">Modifier</a>
                            <form method="post" action="entreprise_supprimer.php" class="d-inline"
                                  onsubmit="return confirm('Supprimer cette entreprise et toutes ses offres ?');">
                                <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
                                <input type="hidden" name="id" value="<?= (int) $e['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger">Supprimer</button>
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