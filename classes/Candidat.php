<?php
require_once __DIR__ . '/Database.php';

class Candidat
{
     public const STATUTS = ['actif', 'inactif'];
    public function __construct(
        private ?int $id,
        private string $nom,
        private string $prenom,
        private string $email,
        private string $motDePasse,   // le hash, jamais le mot de passe en clair
        private string $telephone,
        private ?int $idVille,
        private ?string $cvFichier,
        private string $statut,
        private ?string $dateInscription = null
    ) {}

    // --- Getters
    public function getId(): ?int { return $this->id; }
    public function getNom(): string { return $this->nom; }
    public function getPrenom(): string { return $this->prenom; }
    public function getEmail(): string { return $this->email; }
    public function getTelephone(): string { return $this->telephone; }
    public function getIdVille(): ?int { return $this->idVille; }
    public function getCvFichier(): ?string { return $this->cvFichier; }
    public function getStatut(): string { return $this->statut; }
    public function getDateInscription(): ?string { return $this->dateInscription; }

    // --- Setters
    public function setNom(string $nom): void { $this->nom = $nom; }
    public function setPrenom(string $prenom): void { $this->prenom = $prenom; }
    public function setTelephone(string $telephone): void { $this->telephone = $telephone; }
    public function setIdVille(?int $idVille): void { $this->idVille = $idVille; }
    public function setCvFichier(?string $cvFichier): void { $this->cvFichier = $cvFichier; }

    public function estActif(): bool
    {
        return $this->statut === 'actif';
    }

    public function verifierMotDePasse(string $motDePasse): bool
    {
        return password_verify($motDePasse, $this->motDePasse);
    }

    // --- Accès aux données

    private static function depuisLigne(array $l): Candidat
    {
        return new Candidat(
            (int) $l['id'], $l['nom'], $l['prenom'], $l['email'], $l['mot_de_passe'],
            $l['telephone'], $l['id_ville'] !== null ? (int) $l['id_ville'] : null,
            $l['cv_fichier'], $l['statut'], $l['date_inscription']
        );
    }

    public static function compter(): int
    {
        $pdo = Database::getConnection();
        return (int) $pdo->query('SELECT COUNT(*) FROM candidat')->fetchColumn();
    }

    public static function trouver(int $id): ?Candidat
    {
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM candidat WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $l = $stmt->fetch();
        return $l ? self::depuisLigne($l) : null;
    }

    public static function trouverParEmail(string $email): ?Candidat
    {
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM candidat WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        $l = $stmt->fetch();
        return $l ? self::depuisLigne($l) : null;
    }

    public static function emailExiste(string $email): bool
    {
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM candidat WHERE email = :email');
        $stmt->execute([':email' => $email]);
        return (int) $stmt->fetchColumn() > 0;
    }

    // Crée un compte : le mot de passe est haché ici, avant l'enregistrement
    public static function inscrire(string $nom, string $prenom, string $email, string $telephone, string $motDePasse): int
    {
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare(
            'INSERT INTO candidat (nom, prenom, email, mot_de_passe, telephone)
             VALUES (:nom, :prenom, :email, :mdp, :telephone)'
        );
        $stmt->execute([
            ':nom'       => $nom,
            ':prenom'    => $prenom,
            ':email'     => $email,
            ':mdp'       => password_hash($motDePasse, PASSWORD_DEFAULT),
            ':telephone' => $telephone,
        ]);
        return (int) $pdo->lastInsertId();
    }

    // Met à jour le profil (utilisé à l'étape suivante)
    public function mettreAJour(): void
    {
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare(
            'UPDATE candidat
             SET nom = :nom, prenom = :prenom, telephone = :telephone,
                 id_ville = :ville, cv_fichier = :cv
             WHERE id = :id'
        );
        $stmt->execute([
            ':nom'       => $this->nom,
            ':prenom'    => $this->prenom,
            ':telephone' => $this->telephone,
            ':ville'     => $this->idVille,
            ':cv'        => $this->cvFichier,
            ':id'        => $this->id,
        ]);
    }
        // Liste pour l'administrateur, avec recherche et filtre sur le statut
    public static function liste(string $recherche = '', string $statut = ''): array
    {
        $sql = 'SELECT c.id, c.nom, c.prenom, c.email, c.telephone, c.statut, c.date_inscription,
                       (c.cv_fichier IS NOT NULL) AS a_cv,
                       v.libelle AS ville,
                       (SELECT COUNT(*) FROM candidature ca WHERE ca.id_candidat = c.id) AS nb_candidatures
                FROM candidat c
                LEFT JOIN ville v ON v.id = c.id_ville
                WHERE 1 = 1';
        $params = [];

        if ($recherche !== '') {
            $sql .= ' AND (c.nom LIKE :r1 OR c.prenom LIKE :r2 OR c.email LIKE :r3)';
            $like = '%' . addcslashes($recherche, '%_\\') . '%';
            $params[':r1'] = $like;
            $params[':r2'] = $like;
            $params[':r3'] = $like;
        }
        if ($statut !== '' && in_array($statut, self::STATUTS, true)) {
            $sql .= ' AND c.statut = :statut';
            $params[':statut'] = $statut;
        }
        $sql .= ' ORDER BY c.date_inscription DESC, c.id DESC';

        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // Active ou désactive un compte
    public static function changerStatut(int $id, string $statut): void
    {
        if (!in_array($statut, self::STATUTS, true)) {
            throw new InvalidArgumentException('Statut invalide.');
        }
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare('UPDATE candidat SET statut = :statut WHERE id = :id');
        $stmt->execute([':statut' => $statut, ':id' => $id]);
    }
}