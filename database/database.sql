-- =====================================================================
-- JobLink Bénin : script de la base de données (MySQL / MariaDB)
-- Utilisation : sélectionner d'abord la base dans phpMyAdmin, puis
-- onglet « SQL », coller ce script et cliquer sur « Exécuter ».
-- Comptes de test : voir la fin du fichier.
-- =====================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS candidature;
DROP TABLE IF EXISTS offre;
DROP TABLE IF EXISTS entreprise;
DROP TABLE IF EXISTS candidat;
DROP TABLE IF EXISTS administrateur;
DROP TABLE IF EXISTS type_contrat;
DROP TABLE IF EXISTS ville;
DROP TABLE IF EXISTS secteur;

-- ---------------------------------------------------------------------
-- STRUCTURE
-- ---------------------------------------------------------------------

CREATE TABLE administrateur (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nom VARCHAR(100) NOT NULL,
  prenom VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL,
  mot_de_passe VARCHAR(255) NOT NULL,
  date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_administrateur_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE secteur (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  libelle VARCHAR(100) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_secteur_libelle (libelle)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE ville (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  libelle VARCHAR(100) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_ville_libelle (libelle)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE type_contrat (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  libelle VARCHAR(50) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_type_contrat_libelle (libelle)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE candidat (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nom VARCHAR(100) NOT NULL,
  prenom VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL,
  mot_de_passe VARCHAR(255) NOT NULL,
  telephone VARCHAR(30) NOT NULL,
  id_ville INT UNSIGNED DEFAULT NULL,
  cv_fichier VARCHAR(255) DEFAULT NULL,
  statut ENUM('actif','inactif') NOT NULL DEFAULT 'actif',
  date_inscription DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_candidat_email (email),
  KEY idx_candidat_ville (id_ville),
  CONSTRAINT fk_candidat_ville FOREIGN KEY (id_ville) REFERENCES ville (id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE entreprise (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nom VARCHAR(150) NOT NULL,
  email VARCHAR(150) DEFAULT NULL,
  telephone VARCHAR(30) DEFAULT NULL,
  adresse VARCHAR(255) DEFAULT NULL,
  id_secteur INT UNSIGNED NOT NULL,
  id_ville INT UNSIGNED NOT NULL,
  date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_entreprise_secteur (id_secteur),
  KEY idx_entreprise_ville (id_ville),
  CONSTRAINT fk_entreprise_secteur FOREIGN KEY (id_secteur) REFERENCES secteur (id) ON DELETE RESTRICT,
  CONSTRAINT fk_entreprise_ville FOREIGN KEY (id_ville) REFERENCES ville (id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE offre (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  titre VARCHAR(200) NOT NULL,
  description TEXT NOT NULL,
  salaire DECIMAL(10,2) DEFAULT NULL,
  date_limite DATE DEFAULT NULL,
  statut ENUM('brouillon','publiee','cloturee') NOT NULL DEFAULT 'brouillon',
  id_entreprise INT UNSIGNED NOT NULL,
  id_secteur INT UNSIGNED NOT NULL,
  id_ville INT UNSIGNED NOT NULL,
  id_type_contrat INT UNSIGNED NOT NULL,
  date_publication DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_offre_entreprise (id_entreprise),
  KEY idx_offre_secteur (id_secteur),
  KEY idx_offre_ville (id_ville),
  KEY idx_offre_type (id_type_contrat),
  CONSTRAINT fk_offre_entreprise FOREIGN KEY (id_entreprise) REFERENCES entreprise (id) ON DELETE CASCADE,
  CONSTRAINT fk_offre_secteur FOREIGN KEY (id_secteur) REFERENCES secteur (id) ON DELETE RESTRICT,
  CONSTRAINT fk_offre_ville FOREIGN KEY (id_ville) REFERENCES ville (id) ON DELETE RESTRICT,
  CONSTRAINT fk_offre_type FOREIGN KEY (id_type_contrat) REFERENCES type_contrat (id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE candidature (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_candidat INT UNSIGNED NOT NULL,
  id_offre INT UNSIGNED NOT NULL,
  lettre_motivation TEXT NOT NULL,
  statut ENUM('en_attente','retenue','refusee') NOT NULL DEFAULT 'en_attente',
  date_candidature DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_candidat_offre (id_candidat, id_offre),
  KEY idx_candidature_offre (id_offre),
  CONSTRAINT fk_candidature_candidat FOREIGN KEY (id_candidat) REFERENCES candidat (id) ON DELETE CASCADE,
  CONSTRAINT fk_candidature_offre FOREIGN KEY (id_offre) REFERENCES offre (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------
-- DONNÉES DE TEST
-- ---------------------------------------------------------------------

-- Premier administrateur (mot de passe haché avec password_hash)
INSERT INTO administrateur (nom, prenom, email, mot_de_passe) VALUES
('Administrateur', 'JobLink', 'admin@joblink.bj', '$2y$12$ex0gPMeafnkbk/ESnHOW1OTAQq9tItBb7Y.eAv/nnqJTGihdA8rZi');

-- Listes de référence
INSERT INTO secteur (libelle) VALUES
('Informatique et télécoms'),
('Banque et finance'),
('Santé'),
('Commerce et vente'),
('Éducation et formation'),
('Restauration et hôtellerie'),
('BTP et immobilier'),
('Transport et logistique');

INSERT INTO ville (libelle) VALUES
('Cotonou'),
('Porto-Novo'),
('Abomey-Calavi'),
('Parakou'),
('Bohicon'),
('Ouidah');

INSERT INTO type_contrat (libelle) VALUES
('CDI'),
('CDD'),
('Stage'),
('Freelance'),
('Alternance');

-- Entreprises (fictives) : id_secteur, id_ville selon l'ordre des listes ci-dessus
INSERT INTO entreprise (nom, email, telephone, adresse, id_secteur, id_ville) VALUES
('TechBénin Solutions', 'contact@techbenin-solutions.example', '+229 01 00 00 00 01', 'Quartier Haie Vive, Cotonou', 1, 1),
('Finexa Microfinance', 'rh@finexa-microfinance.example', '+229 01 00 00 00 02', 'Avenue Steinmetz, Porto-Novo', 2, 2),
('Clinique Espérance', 'recrutement@clinique-esperance.example', '+229 01 00 00 00 03', 'Route de Godomey, Abomey-Calavi', 3, 3),
('Marché Plus Distribution', 'emploi@marcheplus.example', '+229 01 00 00 00 04', 'Carrefour Zogbo, Cotonou', 4, 1),
('Saveurs du Bénin', 'contact@saveurs-benin.example', '+229 01 00 00 00 05', 'Boulevard de la Marina, Cotonou', 6, 1);

-- Offres : id_entreprise, id_secteur, id_ville, id_type_contrat
INSERT INTO offre (titre, description, salaire, date_limite, statut, id_entreprise, id_secteur, id_ville, id_type_contrat) VALUES
('Développeur web PHP', 'Nous recherchons un développeur PHP orienté objet pour concevoir et maintenir des applications web. Connaissance de MySQL et de Git appréciée.', 350000.00, '2026-11-30', 'publiee', 1, 1, 1, 1),
('Stagiaire en développement mobile', 'Stage de six mois au sein de notre équipe technique : participation au développement d’une application mobile et aux tests.', 50000.00, '2026-11-15', 'publiee', 1, 1, 1, 3),
('Administrateur systèmes et réseaux', 'Gestion du parc informatique, des serveurs et de la sécurité du réseau de l’entreprise. Contrat de douze mois renouvelable.', 400000.00, '2026-12-10', 'publiee', 1, 1, 1, 2),
('Conseiller clientèle microfinance', 'Accueil et accompagnement des clients, présentation des produits d’épargne et de crédit, suivi des dossiers.', 250000.00, '2026-11-20', 'publiee', 2, 2, 2, 1),
('Assistant gestionnaire de crédit', 'Alternance au sein du service crédit : analyse des dossiers et suivi des remboursements. Offre en cours de rédaction.', NULL, '2026-12-05', 'brouillon', 2, 2, 2, 5),
('Infirmier diplômé d’État', 'Prise en charge des patients, soins et suivi au sein du service d’hospitalisation. Diplôme d’État exigé.', 300000.00, '2026-11-25', 'publiee', 3, 3, 3, 1),
('Secrétaire médicale', 'Accueil des patients, gestion des rendez-vous et des dossiers médicaux. Le poste a été pourvu.', 180000.00, '2026-09-15', 'cloturee', 3, 3, 3, 2),
('Commercial terrain', 'Développement du portefeuille clients auprès des commerces de la ville, prise de commandes et suivi des livraisons.', 200000.00, '2026-12-15', 'publiee', 4, 4, 1, 1),
('Community manager', 'Mission freelance : animation des réseaux sociaux de l’enseigne, création de contenus et suivi des statistiques.', NULL, '2026-11-10', 'publiee', 4, 4, 1, 4),
('Cuisinier polyvalent', 'Préparation des plats du jour dans le respect des règles d’hygiène, en équipe avec le chef de cuisine.', 150000.00, '2026-10-31', 'publiee', 5, 6, 1, 1);

-- Candidats (un compte inactif pour tester la désactivation)
INSERT INTO candidat (nom, prenom, email, mot_de_passe, telephone, id_ville, cv_fichier, statut) VALUES
('Adjovi', 'Mireille', 'mireille.adjovi@example.com', '$2y$12$PRT8VgRgTtqyGCTMlpybh.fPb1QHg654txI6K9el7/IQJsh5M.LZa', '+229 02 00 00 00 01', 1, NULL, 'actif'),
('Dossou', 'Fabrice', 'fabrice.dossou@example.com', '$2y$12$fZUvIchtMAetzdZIiHxi3ODeDqqQUaU1t2J//SVNx9rqWIHMZOCWO', '+229 02 00 00 00 02', 3, NULL, 'actif'),
('Gbaguidi', 'Sandrine', 'sandrine.gbaguidi@example.com', '$2y$12$xvFDvwjFaZ9CaxH.IZ.jbOuHUAyIY0qw2uRTuEJGZYz2pyWLYjH0C', '+229 02 00 00 00 03', 2, NULL, 'actif'),
('Houngbo', 'Kevin', 'kevin.houngbo@example.com', '$2y$12$L0fBxphmJ.HQdQs2dxQtOePGqSnrgluwV1NoDQQzYdspHCOrOA73S', '+229 02 00 00 00 04', 4, NULL, 'inactif'),
('Tossou', 'Chimène', 'chimene.tossou@example.com', '$2y$12$OIC54VcmZUGEeypll5cKaupRsa7DZnfk/v6phcLD61Ee1IlNrtyGO', '+229 02 00 00 00 05', 1, NULL, 'actif');

-- Candidatures (id_candidat, id_offre) : un couple ne peut apparaître qu’une fois
INSERT INTO candidature (id_candidat, id_offre, lettre_motivation, statut) VALUES
(1, 1, 'Passionnée par le développement web, je souhaite rejoindre votre équipe technique.', 'en_attente'),
(2, 1, 'Mon expérience en PHP et en MySQL correspond au profil que vous recherchez.', 'retenue'),
(2, 3, 'Je postule également au poste d’administrateur systèmes et réseaux.', 'en_attente'),
(3, 4, 'Le contact avec la clientèle est pour moi une vraie motivation.', 'refusee'),
(3, 8, 'Je souhaite mettre mon sens du contact au service de vos clients.', 'en_attente'),
(5, 2, 'Étudiante en développement mobile, je cherche un stage pour mettre en pratique mes cours.', 'retenue'),
(5, 10, 'La cuisine est ma passion et je souhaite intégrer votre équipe.', 'en_attente');

-- ---------------------------------------------------------------------
-- COMPTES DE TEST
--   Administrateur : admin@joblink.bj              / Admin@2026
--   Candidats      : mireille.adjovi@example.com   / Candidat@2026
--                    (les 5 candidats ont le même mot de passe ;
--                     kevin.houngbo@example.com est désactivé)
-- ---------------------------------------------------------------------
