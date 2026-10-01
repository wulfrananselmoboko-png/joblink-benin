<?php
require_once __DIR__ . '/Database.php';

class Entreprise
{
    public function __construct(
        private int $id,
        private string $nom,
        private ?string $email,
        private ?string $telephone,
        private ?string $adresse,
        private int $idSecteur,
        private int $idVille,
        private string $dateCreation
    ) {}

    public static function compter(): int
    {
        $pdo = Database::getConnection();
        return (int) $pdo->query('SELECT COUNT(*) FROM entreprise')->fetchColumn();
    }
}