<?php
require_once __DIR__ . '/Database.php';

class Administrateur
{
    private int $id;
    private string $nom;
    private string $prenom;
    private string $email;
    private string $motDePasse;   // le hash, jamais le mot de passe en clair
    private string $dateCreation;

    public function __construct(int $id, string $nom, string $prenom, string $email, string $motDePasse, string $dateCreation)
    {
        $this->id           = $id;
        $this->nom          = $nom;
        $this->prenom       = $prenom;
        $this->email        = $email;
        $this->motDePasse   = $motDePasse;
        $this->dateCreation = $dateCreation;
    }

    // --- Getters
    public function getId(): int { return $this->id; }
    public function getNom(): string { return $this->nom; }
    public function getPrenom(): string { return $this->prenom; }
    public function getEmail(): string { return $this->email; }
    public function getDateCreation(): string { return $this->dateCreation; }

    // --- Setters
    public function setNom(string $nom): void { $this->nom = $nom; }
    public function setPrenom(string $prenom): void { $this->prenom = $prenom; }
    public function setEmail(string $email): void { $this->email = $email; }

    // --- Vérifie un mot de passe saisi contre le hash stocké
    public function verifierMotDePasse(string $motDePasse): bool
    {
        return password_verify($motDePasse, $this->motDePasse);
    }

    // --- Accès aux données : cherche un administrateur par son e-mail
    public static function trouverParEmail(string $email): ?Administrateur
    {
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare ('SELECT * FROM administrateur WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        $ligne = $stmt->fetch();

        if (!$ligne) {
            return null;
        }
        return new Administrateur(
            (int) $ligne['id'],
            $ligne['nom'],
            $ligne['prenom'],
            $ligne['email'],
            $ligne['mot_de_passe'],
            $ligne['date_creation']
        );
    }
}