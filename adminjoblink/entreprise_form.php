<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../classes/Entreprise.php';
require_once __DIR__ . '/../classes/Reference.php';

exigerConnexion();

$id         = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$entreprise = null;

if ($id > 0) {
    $entreprise = Entreprise::trouver($id);
    if (!$entreprise) {
        flash('danger', 'Entreprise introuvable.');
        header('Location: entreprises.php');
        exit;
    }
}

$secteurs = Reference::tous('secteur');
$villes   = Reference::tous('ville');

// Valeurs affichées dans le formulaire
$val = [
    'nom'        => $entreprise ? $entreprise->getNom() : '',
    'email'      => $entreprise ? ($entreprise->getEmail() ?? '') : '',
    'telephone'  => $entreprise ? ($entreprise->getTelephone() ?? '') : '',
    'adresse'    => $entreprise ? ($entreprise->getAdresse() ?? '') : '',
    'id_secteur' => $entreprise ? $entreprise->getIdSecteur() : 0,
    'id_ville'   => $entreprise ? $entreprise->getIdVille() : 0,
];
$erreurs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifierCsrf();

    $val = [
        'nom'        => trim($_POST['nom'] ?? ''),
        'email'      => trim($_POST['email'] ?? ''),
        'telephone'  => trim($_POST['telephone'] ?? ''),
        'adresse'    => trim($_POST['adresse'] ?? ''),
        'id_secteur' => (int) ($_POST['id_secteur'] ?? 0),
        'id_ville'   => (int) ($_POST['id_ville'] ?? 0),
    ];

    // Contrôles côté serveur
    if ($val['nom'] === '') {
        $erreurs[] = 'Le nom de l\'entreprise est obligatoire.';
    } elseif (mb_strlen($val['nom']) > 150) {
        $erreurs[] = 'Le nom est trop long (150 caractères maximum).';
    }
    if ($val['email'] !== '' && !filter_var($val['email'], FILTER_VALIDATE_EMAIL)) {
        $erreurs[] = 'L\'adresse e-mail n\'est pas valide.';
    }
    if (mb_strlen($val['telephone']) > 30) {
        $erreurs[] = 'Le téléphone est trop long (30 caractères maximum).';
    }
    if (mb_strlen($val['adresse']) > 255) {
        $erreurs[] = 'L\'adresse est trop longue (255 caractères maximum).';
    }
    if (!in_array($val['id_secteur'], array_map('intval', array_column($secteurs, 'id')), true)) {
        $erreurs[] = 'Veuillez choisir un secteur.';
    }
    if (!in_array($val['id_ville'], array_map('intval', array_column($villes, 'id')), true)) {
        $erreurs[] = 'Veuillez choisir une ville.';
    }

    if (empty($erreurs)) {
        // Un champ facultatif vide devient NULL en base
        $email     = $val['email'] !== '' ? $val['email'] : null;
        $telephone = $val['telephone'] !== '' ? $val['telephone'] : null;
        $adresse   = $val['adresse'] !== '' ? $val['adresse'] : null;

        if ($entreprise) {
            $entreprise->setNom($val['nom']);
            $entreprise->setEmail($email);
            $entreprise->setTelephone($telephone);
            $entreprise->setAdresse($adresse);
            $entreprise->setIdSecteur($val['id_secteur']);
            $entreprise->setIdVille($val['id_ville']);
        } else {
            $entreprise = new Entreprise(null, $val['nom'], $email, $telephone, $adresse, $val['id_secteur'], $val['id_ville']);
        }
        $entreprise->enregistrer();

        flash('success', $id > 0 ? 'Entreprise modifiée.' : 'Entreprise ajoutée.');
        header('Location: entreprises.php');
        exit;
    }
}

$titre = $id > 0 ? 'Modifier une entreprise' : 'Ajouter une entreprise';
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
        <form method="post" action="entreprise_form.php<?= $id > 0 ? '?id=' . $id : '' ?>">
            <input type="hidden" name="csrf" value="<?= csrfToken() ?>">

            <div class="mb-3">
                <label for="nom" class="form-label">Nom <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="nom" name="nom" value="<?= htmlspecialchars($val['nom']) ?>" required>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">E-mail</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($val['email']) ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="telephone" class="form-label">Téléphone</label>
                    <input type="text" class="form-control" id="telephone" name="telephone" value="<?= htmlspecialchars($val['telephone']) ?>">
                </div>
            </div>

            <div class="mb-3">
                <label for="adresse" class="form-label">Adresse</label>
                <input type="text" class="form-control" id="adresse" name="adresse" value="<?= htmlspecialchars($val['adresse']) ?>">
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
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
                <div class="col-md-6 mb-3">
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
            </div>

            <a href="entreprises.php" class="btn btn-outline-secondary">Annuler</a>
            <button type="submit" class="btn btn-primary">Enregistrer</button>
        </form>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>