<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../classes/Offre.php';

exigerConnexion();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: offres.php');
    exit;
}
verifierCsrf();

$id = (int) ($_POST['id'] ?? 0);

if ($id > 0 && Offre::trouver($id)) {
    Offre::supprimer($id);
    flash('success', 'Offre supprimée.');
} else {
    flash('danger', 'Offre introuvable.');
}

header('Location: offres.php');
exit;