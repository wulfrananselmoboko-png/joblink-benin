<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../classes/Candidature.php';

exigerConnexion();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: candidatures.php');
    exit;
}
verifierCsrf();

$id     = (int) ($_POST['id'] ?? 0);
$statut = $_POST['statut'] ?? '';

if ($id > 0 && in_array($statut, Candidature::STATUTS, true)) {
    $idOffre = Candidature::changerStatut($id, $statut);

    if ($idOffre !== null) {
        flash('success', 'Le statut de la candidature a été mis à jour.');
        header('Location: candidatures_offre.php?id=' . $idOffre);
        exit;
    }
}

flash('danger', 'Candidature introuvable ou statut invalide.');
header('Location: candidatures.php');
exit;