<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../classes/Offre.php';
require_once __DIR__ . '/../classes/Reference.php';

exigerConnexionCandidat();

$motCle  = trim($_GET['q'] ?? '');
$secteur = (int) ($_GET['secteur'] ?? 0);
$ville   = (int) ($_GET['ville'] ?? 0);
$type    = (int) ($_GET['type'] ?? 0);

$offres   = Offre::rechercher($motCle, $secteur, $ville, $type);
$secteurs = Reference::tous('secteur');
$villes   = Reference::tous('ville');
$types    = Reference::tous('type_contrat');

$titre = 'Offres d\'emploi';
require __DIR__ . '/includes/header.php';
?>

<h1 class="h3 mb-3">Offres d'emploi</h1>

<form method="get" action="offres.php" class="card card-body mb-4">
    <div class="row g-2">
        <div class="col-md-12 col-lg-3">
            <input type="text" name="q" class="form-control" placeholder="Mot-clé (poste, compétence...)" value="<?= htmlspecialchars($motCle) ?>">
        </div>
        <div class="col-md-4 col-lg-3">
            <select name="secteur" class="form-select">
                <option value="0">Tous les secteurs</option>
                <?php foreach ($secteurs as $s): ?>
                    <option value="<?= (int) $s['id'] ?>" <?= $secteur === (int) $s['id'] ? 'selected' : '' ?>><?= htmlspecialchars($s['libelle']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4 col-lg-2">
            <select name="ville" class="form-select">
                <option value="0">Toutes les villes</option>
                <?php foreach ($villes as $v): ?>
                    <option value="<?= (int) $v['id'] ?>" <?= $ville === (int) $v['id'] ? 'selected' : '' ?>><?= htmlspecialchars($v['libelle']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4 col-lg-2">
            <select name="type" class="form-select">
                <option value="0">Tous les contrats</option>
                <?php foreach ($types as $t): ?>
                    <option value="<?= (int) $t['id'] ?>" <?= $type === (int) $t['id'] ? 'selected' : '' ?>><?= htmlspecialchars($t['libelle']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-lg-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary flex-fill">Rechercher</button>
            <a href="offres.php" class="btn btn-outline-secondary">✕</a>
        </div>
    </div>
</form>

<p class="text-muted"><?= count($offres) ?> offre(s) trouvée(s)</p>

<?php if (empty($offres)): ?>
    <div class="alert alert-info">Aucune offre ne correspond à votre recherche.</div>
<?php else: ?>
    <div class="row g-3">
        <?php foreach ($offres as $o): ?>
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body d-flex flex-column">
                        <h2 class="h5"><?= htmlspecialchars($o['titre']) ?></h2>
                        <div class="text-muted mb-2"><?= htmlspecialchars($o['entreprise']) ?> · <?= htmlspecialchars($o['ville']) ?></div>
                        <div class="mb-2">
                            <span class="badge text-bg-primary"><?= htmlspecialchars($o['type_contrat']) ?></span>
                            <span class="badge text-bg-secondary"><?= htmlspecialchars($o['secteur']) ?></span>
                        </div>
                        <p class="small flex-grow-1"><?= htmlspecialchars(mb_strimwidth($o['description'], 0, 140, '…')) ?></p>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                <?= $o['salaire'] !== null ? number_format((float) $o['salaire'], 0, ',', ' ') . ' FCFA' : 'Salaire à négocier' ?>
                                <?php if ($o['date_limite']): ?> · Jusqu'au <?= date('d/m/Y', strtotime($o['date_limite'])) ?><?php endif; ?>
                            </small>
                            <a href="offre.php?id=<?= (int) $o['id'] ?>" class="btn btn-sm btn-outline-primary">Voir l'offre</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>