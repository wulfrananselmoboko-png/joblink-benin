<?php
require_once __DIR__ . '/Database.php';

class Offre
{
    public const STATUTS = ['brouillon', 'publiee', 'cloturee'];
        // Une offre est visible des candidats si elle est publiée et non expirée
    private const VISIBLE = "o.statut = 'publiee' AND (o.date_limite IS NULL OR o.date_limite >= CURDATE())";

    public function __construct(
        private ?int $id,
        private string $titre,
        private string $description,
        private ?float $salaire,
        private ?string $dateLimite,
        private string $statut,
        private int $idEntreprise,
        private int $idSecteur,
        private int $idVille,
        private int $idTypeContrat,
        private ?string $datePublication = null
    ) {}

    // --- Getters
    public function getId(): ?int { return $this->id; }
    public function getTitre(): string { return $this->titre; }
    public function getDescription(): string { return $this->description; }
    public function getSalaire(): ?float { return $this->salaire; }
    public function getDateLimite(): ?string { return $this->dateLimite; }
    public function getStatut(): string { return $this->statut; }
    public function getIdEntreprise(): int { return $this->idEntreprise; }
    public function getIdSecteur(): int { return $this->idSecteur; }
    public function getIdVille(): int { return $this->idVille; }
    public function getIdTypeContrat(): int { return $this->idTypeContrat; }
    public function getDatePublication(): ?string { return $this->datePublication; }

    // --- Setters
    public function setTitre(string $titre): void { $this->titre = $titre; }
    public function setDescription(string $description): void { $this->description = $description; }
    public function setSalaire(?float $salaire): void { $this->salaire = $salaire; }
    public function setDateLimite(?string $dateLimite): void { $this->dateLimite = $dateLimite; }
    public function setStatut(string $statut): void { $this->statut = $statut; }
    public function setIdEntreprise(int $idEntreprise): void { $this->idEntreprise = $idEntreprise; }
    public function setIdSecteur(int $idSecteur): void { $this->idSecteur = $idSecteur; }
    public function setIdVille(int $idVille): void { $this->idVille = $idVille; }
    public function setIdTypeContrat(int $idTypeContrat): void { $this->idTypeContrat = $idTypeContrat; }

    // --- Accès aux données

    public static function compterPubliees(): int
    {
        $pdo = Database::getConnection();
        return (int) $pdo->query("SELECT COUNT(*) FROM offre WHERE statut = 'publiee'")->fetchColumn();
    }

    // Liste avec filtres facultatifs (statut et secteur)
    public static function toutes(string $statut = '', int $idSecteur = 0): array
    {
        $sql = 'SELECT o.id, o.titre, o.statut, o.salaire, o.date_limite,
                       e.nom AS entreprise, s.libelle AS secteur,
                       v.libelle AS ville, t.libelle AS type_contrat
                FROM offre o
                INNER JOIN entreprise e   ON e.id = o.id_entreprise
                INNER JOIN secteur s      ON s.id = o.id_secteur
                INNER JOIN ville v        ON v.id = o.id_ville
                INNER JOIN type_contrat t ON t.id = o.id_type_contrat
                WHERE 1 = 1';
        $params = [];

        if ($statut !== '' && in_array($statut, self::STATUTS, true)) {
            $sql .= ' AND o.statut = :statut';
            $params[':statut'] = $statut;
        }
        if ($idSecteur > 0) {
            $sql .= ' AND o.id_secteur = :secteur';
            $params[':secteur'] = $idSecteur;
        }
        $sql .= ' ORDER BY o.date_publication DESC, o.id DESC';

        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function trouver(int $id): ?Offre
    {
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM offre WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $l = $stmt->fetch();

        if (!$l) {
            return null;
        }
        return new Offre(
            (int) $l['id'], $l['titre'], $l['description'],
            $l['salaire'] !== null ? (float) $l['salaire'] : null,
            $l['date_limite'], $l['statut'],
            (int) $l['id_entreprise'], (int) $l['id_secteur'],
            (int) $l['id_ville'], (int) $l['id_type_contrat'],
            $l['date_publication']
        );
    }

    // Insère si l'offre est nouvelle (id null), sinon met à jour
    public function enregistrer(): void
    {
        $pdo = Database::getConnection();
        $params = [
            ':titre'       => $this->titre,
            ':description' => $this->description,
            ':salaire'     => $this->salaire,
            ':date_limite' => $this->dateLimite,
            ':statut'      => $this->statut,
            ':entreprise'  => $this->idEntreprise,
            ':secteur'     => $this->idSecteur,
            ':ville'       => $this->idVille,
            ':type'        => $this->idTypeContrat,
        ];

        if ($this->id === null) {
            $stmt = $pdo->prepare(
                'INSERT INTO offre (titre, description, salaire, date_limite, statut,
                                    id_entreprise, id_secteur, id_ville, id_type_contrat)
                 VALUES (:titre, :description, :salaire, :date_limite, :statut,
                         :entreprise, :secteur, :ville, :type)'
            );
            $stmt->execute($params);
            $this->id = (int) $pdo->lastInsertId();
        } else {
            $stmt = $pdo->prepare(
                'UPDATE offre
                 SET titre = :titre, description = :description, salaire = :salaire,
                     date_limite = :date_limite, statut = :statut,
                     id_entreprise = :entreprise, id_secteur = :secteur,
                     id_ville = :ville, id_type_contrat = :type
                 WHERE id = :id'
            );
            $params[':id'] = $this->id;
            $stmt->execute($params);
        }
    }

    // Les candidatures de l'offre s'effacent aussi (ON DELETE CASCADE)
    public static function supprimer(int $id): void
    {
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare('DELETE FROM offre WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }
        // Recherche côté candidat : mot-clé, secteur, ville, type de contrat
    public static function rechercher(string $motCle, int $idSecteur, int $idVille, int $idType): array
    {
        $sql = 'SELECT o.id, o.titre, o.description, o.salaire, o.date_limite,
                       e.nom AS entreprise, s.libelle AS secteur,
                       v.libelle AS ville, t.libelle AS type_contrat
                FROM offre o
                INNER JOIN entreprise e   ON e.id = o.id_entreprise
                INNER JOIN secteur s      ON s.id = o.id_secteur
                INNER JOIN ville v        ON v.id = o.id_ville
                INNER JOIN type_contrat t ON t.id = o.id_type_contrat
                WHERE ' . self::VISIBLE;
        $params = [];

        if ($motCle !== '') {
            // Deux noms de paramètres : un même nom ne peut pas être répété
            $sql .= ' AND (o.titre LIKE :mot1 OR o.description LIKE :mot2)';
            $like = '%' . addcslashes($motCle, '%_\\') . '%';
            $params[':mot1'] = $like;
            $params[':mot2'] = $like;
        }
        if ($idSecteur > 0) {
            $sql .= ' AND o.id_secteur = :secteur';
            $params[':secteur'] = $idSecteur;
        }
        if ($idVille > 0) {
            $sql .= ' AND o.id_ville = :ville';
            $params[':ville'] = $idVille;
        }
        if ($idType > 0) {
            $sql .= ' AND o.id_type_contrat = :type';
            $params[':type'] = $idType;
        }
        $sql .= ' ORDER BY o.date_publication DESC, o.id DESC';

        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // Détail d'une offre, seulement si elle est visible des candidats
    public static function trouverVisible(int $id): ?array
    {
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare(
            'SELECT o.id, o.titre, o.description, o.salaire, o.date_limite, o.date_publication,
                    e.nom AS entreprise, s.libelle AS secteur,
                    v.libelle AS ville, t.libelle AS type_contrat
             FROM offre o
             INNER JOIN entreprise e   ON e.id = o.id_entreprise
             INNER JOIN secteur s      ON s.id = o.id_secteur
             INNER JOIN ville v        ON v.id = o.id_ville
             INNER JOIN type_contrat t ON t.id = o.id_type_contrat
             WHERE o.id = :id AND ' . self::VISIBLE
        );
        $stmt->execute([':id' => $id]);
        $l = $stmt->fetch();
        return $l ?: null;
    }
        // Chaque offre avec son nombre de candidatures par statut (tableau récapitulatif)
    public static function avecNbCandidatures(): array
    {
        $pdo = Database::getConnection();
        return $pdo->query(
            "SELECT o.id, o.titre, o.statut, e.nom AS entreprise,
                    COUNT(c.id) AS nb_total,
                    COALESCE(SUM(c.statut = 'en_attente'), 0) AS nb_attente,
                    COALESCE(SUM(c.statut = 'retenue'), 0)    AS nb_retenues,
                    COALESCE(SUM(c.statut = 'refusee'), 0)    AS nb_refusees
             FROM offre o
             INNER JOIN entreprise e ON e.id = o.id_entreprise
             LEFT JOIN candidature c ON c.id_offre = o.id
             GROUP BY o.id, o.titre, o.statut, e.nom
             ORDER BY nb_total DESC, o.date_publication DESC"
        )->fetchAll();
    }
}