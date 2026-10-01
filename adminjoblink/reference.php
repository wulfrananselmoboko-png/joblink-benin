<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../classes/Reference.php';

exigerConnexion();

$listes = [
    'secteur'      => ['Secteurs', 'secteur'],
    'ville'        => ['Villes', 'ville'],
    'type_contrat' => ['Types de contrat', 'type de contrat'],
];

$type = $_GET['type'] ?? 'secteur';
if (!isset($listes[$type])) {
    $type = 'secteur';
}
[$nomListe, $nomSingulier] = $listes[$type];
$max = $type === 'type_contrat' ? 50 : 100;   // tailles des colonnes dans la base

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifierCsrf();

    $action  = $_POST['action'] ?? '';
    $id      = (int) ($_POST['id'] ?? 0);
    $libelle = trim($_POST['libelle'] ?? '');

    try {
        if ($action === 'supprimer') {
            if ($id > 0 && Reference::trouver($type, $id)) {
                if (Reference::utilisations($type, $id) > 0) {
                    flash('danger', 'Suppression impossible : cet élément est utilisé.');
                } else {
                    Reference::supprimer($type, $id);
                    flash('success', 'Élément supprimé.');
                }
            } else {
                flash('danger', 'Élément introuvable.');
            }
        } elseif ($action === 'ajouter' || $action === 'modifier') {
            if ($libelle === '') {
                flash('danger', 'Le libellé est obligatoire.');
            } elseif (mb_strlen($libelle) > $max) {
                flash('danger', "Le libellé est trop long ($max caractères maximum).");
            } elseif (Reference::existe($type, $libelle, $action === 'modifier' ? $id : 0)) {
                flash('danger', 'Ce libellé existe déjà.');
            } elseif ($action === 'ajouter') {
                Reference::ajouter($type, $libelle);
                flash('success', 'Élément ajouté.');
            } elseif ($id > 0 && Reference::trouver($type, $id)) {
                Reference::modifier($type, $id, $libelle);
                flash('success', 'Élément modifié.');
            } else {
                flash('danger', 'Élément introuvable.');
            }
        }
    } catch (PDOException $e) {
        flash('danger', 'Une erreur est survenue lors de l\'enregistrement.');
    }

    header('Location: reference.php?type=' . $type);
    exit;
}

$elements = Reference::tous($type);
$edit     = isset($_GET['edit']) ? Reference::trouver($type, (int) $_GET['edit']) : null;

$titre = $nomListe;
require __DIR__ . '/includes/header.php';
?>

<h1 class="h3 mb-3">Listes de référence</h1>

<ul class="nav nav-pills mb-3">
    <?php foreach ($listes as $cle => [$nom, $singulier]): ?>
        <li class="nav-item">
            <a class="nav-link <?= $cle === $type ? 'active' : '' ?>" href="reference.php?type=<?= $cle ?>">
                <?= htmlspecialchars($nom) ?>
            </a>
        </li>
    <?php endforeach; ?>
</ul>

<div class="card mb-4">
    <div class="card-body">
        <h2 class="h6">
            <?= $edit ? 'Modifier ' : 'Ajouter ' ?>un <?= htmlspecialchars($nomSingulier) ?>
        </h2>
        <form method="post" action="reference.php?type=<?= $type ?>" class="row g-2">
            <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
            <input type="hidden" name="action" value="<?= $edit ? 'modifier' : 'ajouter' ?>">
            <?php if ($edit): ?>
                <input type="hidden" name="id" value="<?= (int) $edit['id'] ?>">
            <?php endif; ?>
            <div class="col-md-6">
                <input type="text" class="form-control" name="libelle" maxlength="<?= $max ?>"
                       value="<?= htmlspecialchars($edit['libelle'] ?? '') ?>" placeholder="Libellé" required>
            </div>
            <div class="col-md-6">
                <button type="submit" class="btn btn-primary"><?= $edit ? 'Enregistrer' : 'Ajouter' ?></button>
                <?php if ($edit): ?>
                    <a href="reference.php?type=<?= $type ?>" class="btn btn-outline-secondary">Annuler</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Libellé</th>
                    <th>Utilisations</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($elements)): ?>
                <tr><td colspan="3" class="text-center text-muted py-3">Aucun élément.</td></tr>
            <?php else: ?>
                <?php foreach ($elements as $el): ?>
                    <?php $nb = Reference::utilisations($type, (int) $el['id']); ?>
                    <tr>
                        <td><?= htmlspecialchars($el['libelle']) ?></td>
                        <td><?= $nb ?></td>
                        <td class="text-end text-nowrap">
                            <a class="btn btn-sm btn-outline-primary"
                               href="reference.php?type=<?= $type ?>&edit=<?= (int) $el['id'] ?>">Modifier</a>
                            <form method="post" action="reference.php?type=<?= $type ?>" class="d-inline"
                                  onsubmit="return confirm('Supprimer cet élément ?');">
                                <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
                                <input type="hidden" name="action" value="supprimer">
                                <input type="hidden" name="id" value="<?= (int) $el['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger" <?= $nb > 0 ? 'disabled title="Utilisé : suppression impossible"' : '' ?>>
                                    Supprimer
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