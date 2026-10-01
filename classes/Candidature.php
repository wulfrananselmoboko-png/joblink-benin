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
        // Statut de la candidature d'un candidat à une offre (null s'il n'a pas postulé)
    public static function statutPour(int $idCandidat, int $idOffre): ?string
    {
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare('SELECT statut FROM candidature WHERE id_candidat = :c AND id_offre = :o');
        $stmt->execute([':c' => $idCandidat, ':o' => $idOffre]);
        $statut = $stmt->fetchColumn();
        return $statut === false ? null : $statut;
    }

    public static function creer(int $idCandidat, int $idOffre, string $lettre): void
    {
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare(
            'INSERT INTO candidature (id_candidat, id_offre, lettre_motivation)
             VALUES (:c, :o, :lettre)'
        );
        $stmt->execute([':c' => $idCandidat, ':o' => $idOffre, ':lettre' => $lettre]);
    }

    // Toutes les candidatures d'un candidat
    public static function duCandidat(int $idCandidat): array
    {
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare(
            'SELECT c.id, c.statut, c.date_candidature,
                    o.id AS id_offre, o.titre,
                    e.nom AS entreprise, v.libelle AS ville
             FROM candidature c
             INNER JOIN offre o      ON o.id = c.id_offre
             INNER JOIN entreprise e ON e.id = o.id_entreprise
             INNER JOIN ville v      ON v.id = o.id_ville
             WHERE c.id_candidat = :id
             ORDER BY c.date_candidature DESC, c.id DESC'
        );
        $stmt->execute([':id' => $idCandidat]);
        return $stmt->fetchAll();
    }

    // Retire une candidature : seulement la sienne, et seulement si elle est en attente
    public static function retirer(int $id, int $idCandidat): bool
    {
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare(
            "DELETE FROM candidature
             WHERE id = :id AND id_candidat = :c AND statut = 'en_attente'"
        );
        $stmt->execute([':id' => $id, ':c' => $idCandidat]);
        return $stmt->rowCount() > 0;
    }
}