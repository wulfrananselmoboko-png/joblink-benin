<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../classes/Offre.php';
require_once __DIR__ . '/../classes/Reference.php';

exigerConnexion();

$filtreStatut  = $_GET['statut'] ?? '';
$filtreSecteur = (int) ($_GET['secteur'] ?? 0);

$offres   = Offre::toutes($filtreStatut, $filtreSecteur);
$secteurs = Reference::tous('secteur');

$statuts = [
    'brouillon' => ['Brouillon', 'secondary'],
    'publiee'   => ['Publiée', 'success'],
    'cloturee'  => ['Clôturée', 'dark'],
];
$aujourdhui = date('Y-m-d');

$titre = 'Offres';
require __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Offres</h1>
    <a href="offre_form.php" class="btn btn-primary">+ Ajouter une offre</a>
</div>

<form method="get" action="offres.php" class="row g-2 mb-3">
    <div class="col-md-4">
        <select name="statut" class="form-select">
            <option value="">Tous les statuts</option>
            <?php foreach ($statuts as $cle => [$texte, $couleur]): ?>
                <option value="<?= $cle ?>" <?= $filtreStatut === $cle ? 'selected' : '' ?>><?= $texte ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-4">
        <select name="secteur" class="form-select">
            <option value="0">Tous les secteurs</option>
            <?php foreach ($secteurs as $s): ?>
                <option value="<?= (int) $s['id'] ?>" <?= $filtreSecteur === (int) $s['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($s['libelle']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-4">
        <button type="submit" class="btn btn-outline-primary">Filtrer</button>
        <a href="offres.php" class="btn btn-outline-secondary">Réinitialiser</a>
    </div>
</form>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Titre</th>
                    <th>Entreprise</th>
                    <th>Secteur</th>
                    <th>Ville</th>
                    <th>Contrat</th>
                    <th>Date limite</th>
                    <th>Statut</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($offres)): ?>
                <tr><td colspan="8" class="text-center text-muted py-3">Aucune offre.</td></tr>
            <?php else: ?>
                <?php foreach ($offres as $o): ?>
                    <?php
                    [$texte, $couleur] = $statuts[$o['statut']] ?? [$o['statut'], 'secondary'];
                    $expiree = $o['statut'] === 'publiee' && $o['date_limite'] && $o['date_limite'] < $aujourdhui;
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($o['titre']) ?></td>
                        <td><?= htmlspecialchars($o['entreprise']) ?></td>
                        <td><?= htmlspecialchars($o['secteur']) ?></td>
                        <td><?= htmlspecialchars($o['ville']) ?></td>
                        <td><?= htmlspecialchars($o['type_contrat']) ?></td>
                        <td><?= $o['date_limite'] ? date('d/m/Y', strtotime($o['date_limite'])) : '—' ?></td>
                        <td>
                            <span class="badge text-bg-<?= $couleur ?>"><?= $texte ?></span>
                            <?php if ($expiree): ?><span class="badge text-bg-warning">Expirée</span><?php endif; ?>
                        </td>
                        <td class="text-end text-nowrap">
                            <a class="btn btn-sm btn-outline-primary" href="offre_form.php?id=<?= (int) $o['id'] ?>">Modifier</a>
                            <form method="post" action="offre_supprimer.php" class="d-inline"
                                  onsubmit="return confirm('Supprimer cette offre et toutes ses candidatures ?');">
                                <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
                                <input type="hidden" name="id" value="<?= (int) $o['id'] ?>">
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