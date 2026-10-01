<?php
require_once __DIR__ . '/Database.php';

class Offre
{
    public function __construct(
        private int $id,
        private string $titre,
        private string $description,
        private ?float $salaire,
        private ?string $dateLimite,
        private string $statut,
        private int $idEntreprise,
        private int $idSecteur,
        private int $idVille,
        private int $idTypeContrat,
        private string $datePublication
    ) {}

    public static function compterPubliees(): int
    {
        $pdo = Database::getConnection();
        return (int) $pdo->query("SELECT COUNT(*) FROM offre WHERE statut = 'publiee'")->fetchColumn();
    }
}