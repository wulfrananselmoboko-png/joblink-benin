<?php
require_once __DIR__ . '/Database.php';

class Candidature
{
    public function __construct(
        private int $id,
        private int $idCandidat,
        private int $idOffre,
        private string $lettreMotivation,
        private string $statut,
        private string $dateCandidature
    ) {}

    public static function compter(): int
    {
        $pdo = Database::getConnection();
        return (int) $pdo->query('SELECT COUNT(*) FROM candidature')->fetchColumn();
    }

    // Les dernières candidatures, avec le nom du candidat et le titre de l'offre
    public static function dernieres(int $limite = 5): array
    {
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare(
            'SELECT c.id, c.statut, c.date_candidature,
                    ca.nom, ca.prenom, o.titre
             FROM candidature c
             INNER JOIN candidat ca ON ca.id = c.id_candidat
             INNER JOIN offre o     ON o.id  = c.id_offre
             ORDER BY c.date_candidature DESC, c.id DESC
             LIMIT :limite'
        );
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
        // Nombre de candidatures d'un candidat, par statut
    public static function compterParStatut(int $idCandidat): array
    {
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare(
            'SELECT statut, COUNT(*) AS nb FROM candidature
             WHERE id_candidat = :id GROUP BY statut'
        );
        $stmt->execute([':id' => $idCandidat]);

        $resultat = ['en_attente' => 0, 'retenue' => 0, 'refusee' => 0];
        foreach ($stmt->fetchAll() as $ligne) {
            $resultat[$ligne['statut']] = (int) $ligne['nb'];
        }
        return $resultat;
    }
}