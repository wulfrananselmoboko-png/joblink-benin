<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../classes/Entreprise.php';

exigerConnexion();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: entreprises.php');
    exit;
}
verifierCsrf();

$id = (int) ($_POST['id'] ?? 0);

if ($id > 0 && Entreprise::trouver($id)) {
    Entreprise::supprimer($id);
    flash('success', 'Entreprise supprimée.');
} else {
    flash('danger', 'Entreprise introuvable.');
}

header('Location: entreprises.php');
exit;