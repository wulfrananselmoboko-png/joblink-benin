<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../classes/Candidat.php';
require_once __DIR__ . '/../classes/GestionCv.php';

exigerConnexionCandidat();

$candidat = Candidat::trouver((int) $_SESSION['candidat_id']);

if (!$candidat || !$candidat->getCvFichier()) {
    http_response_code(404);
    exit('CV introuvable.');
}

GestionCv::envoyer($candidat->getCvFichier());