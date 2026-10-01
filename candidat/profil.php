<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../classes/Candidat.php';
require_once __DIR__ . '/../classes/Reference.php';
require_once __DIR__ . '/../classes/GestionCv.php';

exigerConnexionCandidat();

$idCandidat = (int) $_SESSION['candidat_id'];
$candidat   = Candidat::trouver($idCandidat);
$villes     = Reference::tous('ville');

$val = [
    'nom'       => $candidat->getNom(),
    'prenom'    => $candidat->getPrenom(),
    'telephone' => $candidat->getTelephone(),
    'id_ville'  => $candidat->getIdVille() ?? 0,
];
$erreursInfos = [];
$erreursCv    = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifierCsrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'infos') {
        $val = [
            'nom'       => trim($_POST['nom'] ?? ''),
            'prenom'    => trim($_POST['prenom'] ?? ''),
            'telephone' => trim($_POST['telephone'] ?? ''),
            'id_ville'  => (int) ($_POST['id_ville'] ?? 0),
        ];

        if ($val['nom'] === '' || $val['prenom'] === '' || $val['telephone'] === '') {
            $erreursInfos[] = 'Le nom, le prénom et le téléphone sont obligatoires.';
        }
        if (mb_strlen($val['nom']) > 100 || mb_strlen($val['prenom']) > 100) {
            $erreursInfos[] = 'Le nom et le prénom ne doivent pas dépasser 100 caractères.';
        }
        if ($val['telephone'] !== '' && !preg_match('/^\+?[0-9][0-9 ().-]{6,28}$/', $val['telephone'])) {
            $erreursInfos[] = 'Le numéro de téléphone n\'est pas valide.';
        }
        if ($val['id_ville'] !== 0 && !in_array($val['id_ville'], array_map('intval', array_column($villes, 'id')), true)) {
            $erreursInfos[] = 'La ville choisie n\'est pas valide.';
        }

        if (empty($erreursInfos)) {
            $candidat->setNom($val['nom']);
            $candidat->setPrenom($val['prenom']);
            $candidat->setTelephone($val['telephone']);
            $candidat->setIdVille($val['id_ville'] > 0 ? $val['id_ville'] : null);
            $candidat->mettreAJour();

            $_SESSION['candidat_nom'] = $val['prenom'] . ' ' . $val['nom'];
            flash('success', 'Votre profil a été mis à jour.');
            header('Location: profil.php');
            exit;
        }
    } elseif ($action === 'cv') {
        try {
            $nouveau = GestionCv::enregistrer($_FILES['cv'] ?? [], $idCandidat);
            $ancien  = $candidat->getCvFichier();

            $candidat->setCvFichier($nouveau);
            $candidat->mettreAJour();
            GestionCv::supprimer($ancien);   // l'ancien CV est effacé après le remplacement

            flash('success', 'Votre CV a été enregistré.');
            header('Location: profil.php');
            exit;
        } catch (RuntimeException $e) {
            $erreursCv[] = $e->getMessage();
        }
    }
}

$titre = 'Mon profil';
require __DIR__ . '/includes/header.php';
?>

<h1 class="h3 mb-3">Mon profil</h1>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header fw-bold">Mes informations</div>
            <div class="card-body">
                <?php if (!empty($erreursInfos)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($erreursInfos as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="post" action="profil.php">
                    <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
                    <input type="hidden" name="action" value="infos">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nom" class="form-label">Nom</label>
                            <input type="text" class="form-control" id="nom" name="nom" value="<?= htmlspecialchars($val['nom']) ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="prenom" class="form-label">Prénom</label>
                            <input type="text" class="form-control" id="prenom" name="prenom" value="<?= htmlspecialchars($val['prenom']) ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">E-mail</label>
                        <input type="email" class="form-control" value="<?= htmlspecialchars($candidat->getEmail()) ?>" disabled>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="telephone" class="form-label">Téléphone</label>
                            <input type="text" class="form-control" id="telephone" name="telephone" value="<?= htmlspecialchars($val['telephone']) ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="id_ville" class="form-label">Ville</label>
                            <select class="form-select" id="id_ville" name="id_ville">
                                <option value="0">-- Non renseignée --</option>
                                <?php foreach ($villes as $v): ?>
                                    <option value="<?= (int) $v['id'] ?>" <?= (int) $v['id'] === $val['id_ville'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($v['libelle']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card">
            <div class="card-header fw-bold">Mon CV</div>
            <div class="card-body">
                <?php if ($candidat->getCvFichier()): ?>
                    <p class="mb-2">Un CV est enregistré. <a href="cv.php" target="_blank">Consulter mon CV</a></p>
                <?php else: ?>
                    <p class="text-muted mb-2">Aucun CV déposé. Il est nécessaire pour postuler.</p>
                <?php endif; ?>

                <?php if (!empty($erreursCv)): ?>
                    <div class="alert alert-danger">
                        <?php foreach ($erreursCv as $e): ?><div><?= htmlspecialchars($e) ?></div><?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form method="post" action="profil.php" enctype="multipart/form-data">
                    <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
                    <input type="hidden" name="action" value="cv">
                    <div class="mb-3">
                        <label for="cv" class="form-label">
                            <?= $candidat->getCvFichier() ? 'Remplacer mon CV' : 'Déposer mon CV' ?> (PDF, 2 Mo maximum)
                        </label>
                        <input type="file" class="form-control" id="cv" name="cv" accept="application/pdf,.pdf" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Envoyer</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>