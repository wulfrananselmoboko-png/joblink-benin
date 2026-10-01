<?php
require_once __DIR__ . '/Database.php';

class Reference
{
    // Liste blanche : un nom de table ne peut pas être passé en paramètre préparé
    private const TABLES = ['secteur', 'ville', 'type_contrat'];

    public static function tous(string $table): array
    {
        if (!in_array($table, self::TABLES, true)) {
            throw new InvalidArgumentException('Table non autorisée.');
        }
        $pdo = Database::getConnection();
        return $pdo->query("SELECT id, libelle FROM $table ORDER BY libelle")->fetchAll();
    }
}