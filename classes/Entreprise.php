<?php
require_once __DIR__ . '/Database.php';

class Entreprise
{
    public function __construct(
        private ?int $id,
        private string $nom,
        private ?string $email,
        private ?string $telephone,
        private ?string $adresse,
        private int $idSecteur,
        private int $idVille,
        private ?string $dateCreation = null
    ) {}

    // --- Getters
    public function getId(): ?int { return $this->id; }
    public function getNom(): string { return $this->nom; }
    public function getEmail(): ?string { return $this->email; }
    public function getTelephone(): ?string { return $this->telephone; }
    public function getAdresse(): ?string { return $this->adresse; }
    public function getIdSecteur(): int { return $this->idSecteur; }
    public function getIdVille(): int { return $this->idVille; }
    public function getDateCreation(): ?string { return $this->dateCreation; }

    // --- Setters
    public function setNom(string $nom): void { $this->nom = $nom; }
    public function setEmail(?string $email): void { $this->email = $email; }
    public function setTelephone(?string $telephone): void { $this->telephone = $telephone; }
    public function setAdresse(?string $adresse): void { $this->adresse = $adresse; }
    public function setIdSecteur(int $idSecteur): void { $this->idSecteur = $idSecteur; }
    public function setIdVille(int $idVille): void { $this->idVille = $idVille; }

    // --- Accès aux données

    public static function compter(): int
    {
        $pdo = Database::getConnection();
        return (int) $pdo->query('SELECT COUNT(*) FROM entreprise')->fetchColumn();
    }

    // Liste pour l'affichage (avec secteur, ville et nombre d'offres)
    public static function toutes(): array
    {
        $pdo = Database::getConnection();
        return $pdo->query(
            'SELECT e.id, e.nom, e.email, e.telephone,
                    s.libelle AS secteur, v.libelle AS ville,
                    (SELECT COUNT(*) FROM offre o WHERE o.id_entreprise = e.id) AS nb_offres
             FROM entreprise e
             INNER JOIN secteur s ON s.id = e.id_secteur
             INNER JOIN ville v   ON v.id = e.id_ville
             ORDER BY e.nom'
        )->fetchAll();
    }

    public static function trouver(int $id): ?Entreprise
    {
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM entreprise WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $l = $stmt->fetch();

        if (!$l) {
            return null;
        }
        return new Entreprise(
            (int) $l['id'], $l['nom'], $l['email'], $l['telephone'], $l['adresse'],
            (int) $l['id_secteur'], (int) $l['id_ville'], $l['date_creation']
        );
    }

    // Insère si l'entreprise est nouvelle (id null), sinon met à jour
    public function enregistrer(): void
    {
        $pdo = Database::getConnection();
        $params = [
            ':nom'       => $this->nom,
            ':email'     => $this->email,
            ':telephone' => $this->telephone,
            ':adresse'   => $this->adresse,
            ':secteur'   => $this->idSecteur,
            ':ville'     => $this->idVille,
        ];

        if ($this->id === null) {
            $stmt = $pdo->prepare(
                'INSERT INTO entreprise (nom, email, telephone, adresse, id_secteur, id_ville)
                 VALUES (:nom, :email, :telephone, :adresse, :secteur, :ville)'
            );
            $stmt->execute($params);
            $this->id = (int) $pdo->lastInsertId();
        } else {
            $stmt = $pdo->prepare(
                'UPDATE entreprise
                 SET nom = :nom, email = :email, telephone = :telephone, adresse = :adresse,
                     id_secteur = :secteur, id_ville = :ville
                 WHERE id = :id'
            );
            $params[':id'] = $this->id;
            $stmt->execute($params);
        }
    }

    // Les offres de l'entreprise s'effacent aussi (ON DELETE CASCADE dans la base)
    public static function supprimer(int $id): void
    {
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare('DELETE FROM entreprise WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }

    // Les offres d'une entreprise
    public static function offres(int $idEntreprise): array
    {
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare(
            'SELECT o.id, o.titre, o.statut, o.salaire, o.date_limite,
                    t.libelle AS type_contrat, v.libelle AS ville
             FROM offre o
             INNER JOIN type_contrat t ON t.id = o.id_type_contrat
             INNER JOIN ville v        ON v.id = o.id_ville
             WHERE o.id_entreprise = :id
             ORDER BY o.date_publication DESC'
        );
        $stmt->execute([':id' => $idEntreprise]);
        return $stmt->fetchAll();
    }
}