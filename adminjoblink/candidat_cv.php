<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../classes/Candidat.php';
require_once __DIR__ . '/../classes/GestionCv.php';

exigerConnexion();

$candidat = Candidat::trouver((int) ($_GET['id'] ?? 0));

if (!$candidat || !$candidat->getCvFichier()) {
    http_response_code(404);
    exit('CV introuvable.');
}

GestionCv::envoyer(
    $candidat->getCvFichier(),
    true,
    'CV_' . $candidat->getNom() . '_' . $candidat->getPrenom() . '.pdf'
);