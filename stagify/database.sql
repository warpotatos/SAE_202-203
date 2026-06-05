CREATE DATABASE IF NOT EXISTS stagify CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE stagify;

CREATE TABLE utilisateur (
    num_utilisateur INT AUTO_INCREMENT PRIMARY KEY,
    login           VARCHAR(100) NOT NULL UNIQUE,
    mot_de_passe    VARCHAR(255) NOT NULL,
    email           VARCHAR(150) NOT NULL UNIQUE,
    role            ENUM('etudiant','enseignant','maitre_stage','admin') NOT NULL,
    date_creation   DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE admin (
    num_admin       INT AUTO_INCREMENT PRIMARY KEY,
    nom             VARCHAR(100) NOT NULL,
    prenom          VARCHAR(100) NOT NULL,
    num_utilisateur INT NOT NULL UNIQUE,
    CONSTRAINT fk_admin_util FOREIGN KEY (num_utilisateur) REFERENCES utilisateur(num_utilisateur) ON DELETE CASCADE
);

CREATE TABLE etudiant (
    id_etudiant          INT AUTO_INCREMENT PRIMARY KEY,
    nom                  VARCHAR(100) NOT NULL,
    prenom               VARCHAR(100) NOT NULL,
    email                VARCHAR(150) NOT NULL UNIQUE,
    telephone            VARCHAR(20),
    TD                   VARCHAR(50),
    TP                   VARCHAR(50),
    num_utilisateur      INT NOT NULL UNIQUE,
    num_enseignant_ref   INT DEFAULT NULL,
    CONSTRAINT fk_etudiant_util FOREIGN KEY (num_utilisateur) REFERENCES utilisateur(num_utilisateur) ON DELETE CASCADE
);

CREATE TABLE enseignant (
    num_enseignant  INT AUTO_INCREMENT PRIMARY KEY,
    nom             VARCHAR(100) NOT NULL,
    prenom          VARCHAR(100) NOT NULL,
    matiere         VARCHAR(100),
    email           VARCHAR(150) NOT NULL UNIQUE,
    num_utilisateur INT NOT NULL UNIQUE,
    CONSTRAINT fk_enseignant_util FOREIGN KEY (num_utilisateur) REFERENCES utilisateur(num_utilisateur) ON DELETE CASCADE
);

ALTER TABLE etudiant
    ADD CONSTRAINT fk_etudiant_ens FOREIGN KEY (num_enseignant_ref)
        REFERENCES enseignant(num_enseignant) ON DELETE SET NULL;

CREATE TABLE entreprise (
    id_entreprise INT AUTO_INCREMENT PRIMARY KEY,
    nom           VARCHAR(200) NOT NULL,
    adresse       VARCHAR(255),
    ville         VARCHAR(100),
    telephone     VARCHAR(20),
    email         VARCHAR(150)
);

CREATE TABLE maitre_stage (
    id_maitre       INT AUTO_INCREMENT PRIMARY KEY,
    nom             VARCHAR(100) NOT NULL,
    prenom          VARCHAR(100) NOT NULL,
    email           VARCHAR(150) NOT NULL UNIQUE,
    telephone       VARCHAR(20),
    poste           VARCHAR(150),
    id_entreprise   INT DEFAULT NULL,
    num_utilisateur INT NOT NULL UNIQUE,
    CONSTRAINT fk_maitre_util FOREIGN KEY (num_utilisateur) REFERENCES utilisateur(num_utilisateur) ON DELETE CASCADE,
    CONSTRAINT fk_maitre_ent  FOREIGN KEY (id_entreprise)  REFERENCES entreprise(id_entreprise) ON DELETE SET NULL
);

CREATE TABLE offre_stage (
    num_offre        INT AUTO_INCREMENT PRIMARY KEY,
    titre            VARCHAR(200) NOT NULL,
    description      TEXT,
    duree            VARCHAR(50),
    date_publication DATE,
    statut           ENUM('ouverte','pourvue','fermee') DEFAULT 'ouverte',
    id_entreprise    INT NOT NULL,
    id_maitre        INT DEFAULT NULL,
    CONSTRAINT fk_offre_ent   FOREIGN KEY (id_entreprise) REFERENCES entreprise(id_entreprise) ON DELETE CASCADE,
    CONSTRAINT fk_offre_maitre FOREIGN KEY (id_maitre)    REFERENCES maitre_stage(id_maitre) ON DELETE SET NULL
);

CREATE TABLE postulation (
    id_etudiant      INT NOT NULL,
    num_offre        INT NOT NULL,
    date_postulation DATE DEFAULT (CURRENT_DATE),
    statut           ENUM('en_attente','acceptee','refusee') DEFAULT 'en_attente',
    PRIMARY KEY (id_etudiant, num_offre),
    CONSTRAINT fk_post_etu   FOREIGN KEY (id_etudiant) REFERENCES etudiant(id_etudiant) ON DELETE CASCADE,
    CONSTRAINT fk_post_offre FOREIGN KEY (num_offre)   REFERENCES offre_stage(num_offre) ON DELETE CASCADE
);

CREATE TABLE stage (
    num_stage             INT AUTO_INCREMENT PRIMARY KEY,
    duree                 VARCHAR(50),
    contenu_du_stage      TEXT,
    modalites_encadrement TEXT,
    horaires              VARCHAR(100),
    date_debut            DATE,
    date_fin              DATE,
    date_signature        DATE,
    id_etudiant           INT NOT NULL,
    id_entreprise         INT NOT NULL,
    id_maitre             INT DEFAULT NULL,
    num_offre             INT DEFAULT NULL,
    CONSTRAINT fk_stage_etu  FOREIGN KEY (id_etudiant)   REFERENCES etudiant(id_etudiant) ON DELETE CASCADE,
    CONSTRAINT fk_stage_ent  FOREIGN KEY (id_entreprise) REFERENCES entreprise(id_entreprise) ON DELETE CASCADE,
    CONSTRAINT fk_stage_mai  FOREIGN KEY (id_maitre)     REFERENCES maitre_stage(id_maitre) ON DELETE SET NULL,
    CONSTRAINT fk_stage_off  FOREIGN KEY (num_offre)     REFERENCES offre_stage(num_offre) ON DELETE SET NULL
);

CREATE TABLE jury (
    num_jury     INT AUTO_INCREMENT PRIMARY KEY,
    membres_jury TEXT
);

CREATE TABLE soutenance (
    num_soutenance INT AUTO_INCREMENT PRIMARY KEY,
    date           DATE,
    heures         TIME,
    note_rapport   DECIMAL(5,2) DEFAULT NULL,
    note_oral      DECIMAL(5,2) DEFAULT NULL,
    num_stage      INT NOT NULL UNIQUE,
    num_jury       INT DEFAULT NULL,
    CONSTRAINT fk_souten_stage FOREIGN KEY (num_stage) REFERENCES stage(num_stage) ON DELETE CASCADE,
    CONSTRAINT fk_souten_jury  FOREIGN KEY (num_jury)  REFERENCES jury(num_jury)   ON DELETE SET NULL
);

CREATE TABLE visite_suivi (
    num_visite     INT AUTO_INCREMENT PRIMARY KEY,
    date_visite    DATE,
    lieu           VARCHAR(200),
    heure_visite   TIME,
    commentaires   TEXT,
    num_stage      INT NOT NULL,
    num_enseignant INT DEFAULT NULL,
    CONSTRAINT fk_visite_stage FOREIGN KEY (num_stage)      REFERENCES stage(num_stage)          ON DELETE CASCADE,
    CONSTRAINT fk_visite_ens   FOREIGN KEY (num_enseignant) REFERENCES enseignant(num_enseignant) ON DELETE SET NULL
);

CREATE TABLE probleme (
    num_prob         INT AUTO_INCREMENT PRIMARY KEY,
    date_incident    DATE,
    description      TEXT,
    date_signalement DATE DEFAULT (CURRENT_DATE),
    statut           ENUM('ouvert','en_cours','resolu') DEFAULT 'ouvert',
    num_stage        INT NOT NULL,
    id_etudiant      INT NOT NULL,
    CONSTRAINT fk_prob_stage FOREIGN KEY (num_stage)   REFERENCES stage(num_stage)         ON DELETE CASCADE,
    CONSTRAINT fk_prob_etu   FOREIGN KEY (id_etudiant) REFERENCES etudiant(id_etudiant)    ON DELETE CASCADE
);

CREATE TABLE prise_connaissance (
    num_enseignant INT NOT NULL,
    num_prob       INT NOT NULL,
    date_lecture   DATE DEFAULT (CURRENT_DATE),
    PRIMARY KEY (num_enseignant, num_prob),
    CONSTRAINT fk_pc_ens  FOREIGN KEY (num_enseignant) REFERENCES enseignant(num_enseignant) ON DELETE CASCADE,
    CONSTRAINT fk_pc_prob FOREIGN KEY (num_prob)       REFERENCES probleme(num_prob)         ON DELETE CASCADE
);

CREATE TABLE notation (
    num_enseignant INT NOT NULL,
    num_stage      INT NOT NULL,
    note           DECIMAL(5,2),
    commentaire    TEXT,
    date_notation  DATE DEFAULT (CURRENT_DATE),
    PRIMARY KEY (num_enseignant, num_stage),
    CONSTRAINT fk_note_ens   FOREIGN KEY (num_enseignant) REFERENCES enseignant(num_enseignant) ON DELETE CASCADE,
    CONSTRAINT fk_note_stage FOREIGN KEY (num_stage)      REFERENCES stage(num_stage)           ON DELETE CASCADE
);

CREATE TABLE gestion_utilisateur (
    num_admin       INT NOT NULL,
    num_utilisateur INT NOT NULL,
    date_action     DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (num_admin, num_utilisateur),
    CONSTRAINT fk_gest_admin FOREIGN KEY (num_admin)       REFERENCES admin(num_admin)             ON DELETE CASCADE,
    CONSTRAINT fk_gest_util  FOREIGN KEY (num_utilisateur) REFERENCES utilisateur(num_utilisateur) ON DELETE CASCADE
);


INSERT INTO utilisateur (login, mot_de_passe, email, role) VALUES
('admin',         '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@stagify.fr',         'admin'),
('jean.dupont',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'jean.dupont@etu.fr',       'etudiant'),
('alice.martin',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'alice.martin@etu.fr',      'etudiant'),
('marie.bernard', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'marie.bernard@univ.fr',    'enseignant'),
('marc.leblanc',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'marc.leblanc@techcorp.fr', 'maitre_stage');

INSERT INTO admin (nom, prenom, num_utilisateur) VALUES ('Admin', 'Super', 1);

INSERT INTO enseignant (nom, prenom, matiere, email, num_utilisateur) VALUES
('Bernard', 'Marie', 'Informatique', 'marie.bernard@univ.fr', 3);

INSERT INTO entreprise (nom, adresse, ville, telephone, email) VALUES
('TechCorp SA', '12 rue de la Paix', 'Paris', '01 23 45 67 89', 'contact@techcorp.fr'),
('DataGroup',   '5 avenue Lumière',  'Lyon',  '04 56 78 90 12', 'rh@datagroup.fr');

INSERT INTO maitre_stage (nom, prenom, email, telephone, poste, id_entreprise, num_utilisateur) VALUES
('Leblanc', 'Marc', 'marc.leblanc@techcorp.fr', '06 12 34 56 78', 'Responsable technique', 1, 4);

INSERT INTO etudiant (nom, prenom, email, telephone, TD, TP, num_utilisateur, num_enseignant_ref) VALUES
('Dupont', 'Jean',  'jean.dupont@etu.fr',  '06 11 22 33 44', 'TD1', 'TP2', 2, 1),
('Martin', 'Alice', 'alice.martin@etu.fr', '06 55 66 77 88', 'TD1', 'TP1', 5, 1);

INSERT INTO offre_stage (titre, description, duree, date_publication, statut, id_entreprise, id_maitre) VALUES
('Développeur Web Junior', 'Participation au développement de modules React et Node.js dans notre projet e-commerce.', '6 mois', '2025-05-15', 'ouverte', 1, 1),
('Assistant Data Analyst', 'Analyse de données clients et création de tableaux de bord Power BI.', '4 mois', '2025-05-12', 'ouverte', 2, NULL),
('Stage DevOps / Cloud',   'Déploiement CI/CD et gestion des infrastructures AWS.', '6 mois', '2025-05-20', 'ouverte', 1, 1);
