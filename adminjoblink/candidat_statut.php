<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../classes/Candidat.php';

exigerConnexion();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: candidats.php');
    exit;
}
verifierCsrf();

$id     = (int) ($_POST['id'] ?? 0);
$action = $_POST['action'] ?? '';
$retour = $_POST['retour'] ?? '';

if ($id > 0 && Candidat::trouver($id) && in_array($action, ['activer', 'desactiver'], true)) {
    Candidat::changerStatut($id, $action === 'activer' ? 'actif' : 'inactif');
    flash('success', $action === 'activer' ? 'Compte réactivé.' : 'Compte désactivé.');
} else {
    flash('danger', 'Action impossible.');
}

header('Location: ' . ($retour === 'fiche' && $id > 0 ? 'candidat_fiche.php?id=' . $id : 'candidats.php'));
exit;