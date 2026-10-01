<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../classes/Offre.php';
require_once __DIR__ . '/../classes/Entreprise.php';
require_once __DIR__ . '/../classes/Reference.php';

exigerConnexion();

$id    = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$offre = null;

if ($id > 0) {
    $offre = Offre::trouver($id);
    if (!$offre) {
        flash('danger', 'Offre introuvable.');
        header('Location: offres.php');
        exit;
    }
}

$entreprises = Entreprise::toutes();
$secteurs    = Reference::tous('secteur');
$villes      = Reference::tous('ville');
$types       = Reference::tous('type_contrat');

$libellesStatut = ['brouillon' => 'Brouillon', 'publiee' => 'Publiée', 'cloturee' => 'Clôturée'];

$val = [
    'titre'         => $offre ? $offre->getTitre() : '',
    'description'   => $offre ? $offre->getDescription() : '',
    'salaire'       => $offre && $offre->getSalaire() !== null ? (string) $offre->getSalaire() : '',
    'date_limite'   => $offre ? ($offre->getDateLimite() ?? '') : '',
    'statut'        => $offre ? $offre->getStatut() : 'brouillon',
    'id_entreprise' => $offre ? $offre->getIdEntreprise() : 0,
    'id_secteur'    => $offre ? $offre->getIdSecteur() : 0,
    'id_ville'      => $offre ? $offre->getIdVille() : 0,
    'id_type'       => $offre ? $offre->getIdTypeContrat() : 0,
];
$erreurs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifierCsrf();

    $val = [
        'titre'         => trim($_POST['titre'] ?? ''),
        'description'   => trim($_POST['description'] ?? ''),
        'salaire'       => trim($_POST['salaire'] ?? ''),
        'date_limite'   => trim($_POST['date_limite'] ?? ''),
        'statut'        => $_POST['statut'] ?? '',
        'id_entreprise' => (int) ($_POST['id_entreprise'] ?? 0),
        'id_secteur'    => (int) ($_POST['id_secteur'] ?? 0),
        'id_ville'      => (int) ($_POST['id_ville'] ?? 0),
        'id_type'       => (int) ($_POST['id_type'] ?? 0),
    ];

    // Contrôles côté serveur
    if ($val['titre'] === '') {
        $erreurs[] = 'Le titre est obligatoire.';
    } elseif (mb_strlen($val['titre']) > 200) {
        $erreurs[] = 'Le titre est trop long (200 caractères maximum).';
    }
    if ($val['description'] === '') {
        $erreurs[] = 'La description est obligatoire.';
    }

    $salaire = null;
    if ($val['salaire'] !== '') {
        if (!is_numeric($val['salaire']) || (float) $val['salaire'] < 0 || (float) $val['salaire'] > 99999999) {
            $erreurs[] = 'Le salaire doit être un nombre positif.';
        } else {
            $salaire = (float) $val['salaire'];
        }
    }

    $dateLimite = null;
    if ($val['date_limite'] !== '') {
        $d = DateTime::createFromFormat('Y-m-d', $val['date_limite']);
        if (!$d || $d->format('Y-m-d') !== $val['date_limite']) {
            $erreurs[] = 'La date limite n\'est pas valide.';
        } else {
            $dateLimite = $val['date_limite'];
        }
    }

    if (!in_array($val['statut'], Offre::STATUTS, true)) {
        $erreurs[] = 'Le statut n\'est pas valide.';
    }
    if (!in_array($val['id_entreprise'], array_map('intval', array_column($entreprises, 'id')), true)) {
        $erreurs[] = 'Veuillez choisir une entreprise.';
    }
    if (!in_array($val['id_secteur'], array_map('intval', array_column($secteurs, 'id')), true)) {
        $erreurs[] = 'Veuillez choisir un secteur.';
    }
    if (!in_array($val['id_ville'], array_map('intval', array_column($villes, 'id')), true)) {
        $erreurs[] = 'Veuillez choisir une ville.';
    }
    if (!in_array($val['id_type'], array_map('intval', array_column($types, 'id')), true)) {
        $erreurs[] = 'Veuillez choisir un type de contrat.';
    }

    if (empty($erreurs)) {
        if ($offre) {
            $offre->setTitre($val['titre']);
            $offre->setDescription($val['description']);
            $offre->setSalaire($salaire);
            $offre->setDateLimite($dateLimite);
            $offre->setStatut($val['statut']);
            $offre->setIdEntreprise($val['id_entreprise']);
            $offre->setIdSecteur($val['id_secteur']);
            $offre->setIdVille($val['id_ville']);
            $offre->setIdTypeContrat($val['id_type']);
        } else {
            $offre = new Offre(
                null, $val['titre'], $val['description'], $salaire, $dateLimite, $val['statut'],
                $val['id_entreprise'], $val['id_secteur'], $val['id_ville'], $val['id_type']
            );
        }
        $offre->enregistrer();

        flash('success', $id > 0 ? 'Offre modifiée.' : 'Offre ajoutée.');
        header('Location: offres.php');
        exit;
    }
}

$titre = $id > 0 ? 'Modifier une offre' : 'Ajouter une offre';
require __DIR__ . '/includes/header.php';
?>

<h1 class="h3 mb-3"><?= htmlspecialchars($titre) ?></h1>

<?php if (!empty($erreurs)): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach ($erreurs as $erreur): ?>
                <li><?= htmlspecialchars($erreur) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="post" action="offre_form.php<?= $id > 0 ? '?id=' . $id : '' ?>">
            <input type="hidden" name="csrf" value="<?= csrfToken() ?>">

            <div class="mb-3">
                <label for="titre" class="form-label">Titre <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="titre" name="titre" value="<?= htmlspecialchars($val['titre']) ?>" required>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                <textarea class="form-control" id="description" name="description" rows="5" required><?= htmlspecialchars($val['description']) ?></textarea>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="salaire" class="form-label">Salaire (FCFA)</label>
                    <input type="number" step="1" min="0" class="form-control" id="salaire" name="salaire"
                           value="<?= htmlspecialchars($val['salaire']) ?>" placeholder="Laisser vide si à négocier">
                </div>
                <div class="col-md-4 mb-3">
                    <label for="date_limite" class="form-label">Date limite</label>
                    <input type="date" class="form-control" id="date_limite" name="date_limite" value="<?= htmlspecialchars($val['date_limite']) ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label for="statut" class="form-label">Statut <span class="text-danger">*</span></label>
                    <select class="form-select" id="statut" name="statut" required>
                        <?php foreach ($libellesStatut as $cle => $texte): ?>
                            <option value="<?= $cle ?>" <?= $val['statut'] === $cle ? 'selected' : '' ?>><?= $texte ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label for="id_entreprise" class="form-label">Entreprise <span class="text-danger">*</span></label>
                <select class="form-select" id="id_entreprise" name="id_entreprise" required>
                    <option value="">-- Choisir --</option>
                    <?php foreach ($entreprises as $e): ?>
                        <option value="<?= (int) $e['id'] ?>" <?= (int) $e['id'] === $val['id_entreprise'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($e['nom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="id_secteur" class="form-label">Secteur <span class="text-danger">*</span></label>
                    <select class="form-select" id="id_secteur" name="id_secteur" required>
                        <option value="">-- Choisir --</option>
                        <?php foreach ($secteurs as $s): ?>
                            <option value="<?= (int) $s['id'] ?>" <?= (int) $s['id'] === $val['id_secteur'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($s['libelle']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="id_ville" class="form-label">Ville <span class="text-danger">*</span></label>
                    <select class="form-select" id="id_ville" name="id_ville" required>
                        <option value="">-- Choisir --</option>
                        <?php foreach ($villes as $v): ?>
                            <option value="<?= (int) $v['id'] ?>" <?= (int) $v['id'] === $val['id_ville'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($v['libelle']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="id_type" class="form-label">Type de contrat <span class="text-danger">*</span></label>
                    <select class="form-select" id="id_type" name="id_type" required>
                        <option value="">-- Choisir --</option>
                        <?php foreach ($types as $t): ?>
                            <option value="<?= (int) $t['id'] ?>" <?= (int) $t['id'] === $val['id_type'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($t['libelle']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <a href="offres.php" class="btn btn-outline-secondary">Annuler</a>
            <button type="submit" class="btn btn-primary">Enregistrer</button>
        </form>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>