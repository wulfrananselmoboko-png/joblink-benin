<?php
require_once __DIR__ . '/Database.php';

class Candidat
{
    public function __construct(
        private int $id,
        private string $nom,
        private string $prenom,
        private string $email,
        private string $motDePasse,
        private string $telephone,
        private ?int $idVille,
        private ?string $cvFichier,
        private string $statut,
        private string $dateInscription
    ) {}

    public static function compter(): int
    {
        $pdo = Database::getConnection();
        return (int) $pdo->query('SELECT COUNT(*) FROM candidat')->fetchColumn();
    }
}