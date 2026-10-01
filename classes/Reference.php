<?php
require_once __DIR__ . '/Database.php';

class Reference
{
    // Liste blanche : un nom de table ne peut pas être passé en paramètre préparé
    private const TABLES = ['secteur', 'ville', 'type_contrat'];

    // Où chaque liste est utilisée (table, colonne)
    private const USAGES = [
        'secteur'      => [['offre', 'id_secteur'], ['entreprise', 'id_secteur']],
        'ville'        => [['offre', 'id_ville'], ['entreprise', 'id_ville'], ['candidat', 'id_ville']],
        'type_contrat' => [['offre', 'id_type_contrat']],
    ];

    private static function verifier(string $table): void
    {
        if (!in_array($table, self::TABLES, true)) {
            throw new InvalidArgumentException('Table non autorisée.');
        }
    }

    public static function tous(string $table): array
    {
        self::verifier($table);
        $pdo = Database::getConnection();
        return $pdo->query("SELECT id, libelle FROM $table ORDER BY libelle")->fetchAll();
    }

    public static function trouver(string $table, int $id): ?array
    {
        self::verifier($table);
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare("SELECT id, libelle FROM $table WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $ligne = $stmt->fetch();
        return $ligne ?: null;
    }

    // Un libellé existe-t-il déjà ? (en ignorant l'élément en cours de modification)
    public static function existe(string $table, string $libelle, int $exclureId = 0): bool
    {
        self::verifier($table);
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM $table WHERE libelle = :libelle AND id <> :id");
        $stmt->execute([':libelle' => $libelle, ':id' => $exclureId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public static function ajouter(string $table, string $libelle): void
    {
        self::verifier($table);
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare("INSERT INTO $table (libelle) VALUES (:libelle)");
        $stmt->execute([':libelle' => $libelle]);
    }

    public static function modifier(string $table, int $id, string $libelle): void
    {
        self::verifier($table);
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE $table SET libelle = :libelle WHERE id = :id");
        $stmt->execute([':libelle' => $libelle, ':id' => $id]);
    }

    public static function supprimer(string $table, int $id): void
    {
        self::verifier($table);
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM $table WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }

    // Combien de fois cet élément est utilisé (offres, entreprises, candidats)
    public static function utilisations(string $table, int $id): int
    {
        self::verifier($table);
        $pdo   = Database::getConnection();
        $total = 0;
        foreach (self::USAGES[$table] as [$tableLiee, $colonne]) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM $tableLiee WHERE $colonne = :id");
            $stmt->execute([':id' => $id]);
            $total += (int) $stmt->fetchColumn();
        }
        return $total;
    }
}