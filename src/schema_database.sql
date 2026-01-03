-- ============================================
-- SCHEMA DE BASE DE DONNÉES AMÉLIORÉ
-- Gestionnaire de Travaux Universitaires
-- ============================================

-- Suppression des tables existantes (si elles existent)
DROP TABLE IF EXISTS assignment;
DROP TABLE IF EXISTS course;
DROP TABLE IF EXISTS user;

-- ============================================
-- TABLE USER (Utilisateurs)
-- ============================================
CREATE TABLE user (
                      id INT PRIMARY KEY AUTO_INCREMENT,
                      email VARCHAR(180) UNIQUE NOT NULL,
                      password VARCHAR(255) NOT NULL,
                      first_name VARCHAR(100) NOT NULL,
                      last_name VARCHAR(100) NOT NULL,
                      roles JSON NOT NULL,
                      created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,


                      INDEX idx_email (email),
                      INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE COURSE (Matières/Cours)
-- ============================================
CREATE TABLE course (
                        id INT PRIMARY KEY AUTO_INCREMENT,
                        user_id INT NOT NULL,
                        name VARCHAR(255) NOT NULL,
                        code VARCHAR(50) NOT NULL,
                        color VARCHAR(7) NOT NULL DEFAULT '#007bff',
                        professor VARCHAR(255) NULL,
                        description TEXT NULL,
                        credits INT NULL DEFAULT 0,
                        semester VARCHAR(50) NULL,
                        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                        updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,

                        FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE,
                        INDEX idx_user_id (user_id),
                        INDEX idx_code (code),
                        INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE ASSIGNMENT (Travaux/Devoirs)
-- ============================================
CREATE TABLE assignment (
                            id INT PRIMARY KEY AUTO_INCREMENT,
                            user_id INT NOT NULL,
                            course_id INT NOT NULL,
                            title VARCHAR(255) NOT NULL,
                            description TEXT NULL,
                            due_date DATETIME NOT NULL,
                            priority VARCHAR(20) NOT NULL DEFAULT 'medium',
                            status VARCHAR(20) NOT NULL DEFAULT 'todo',
                            notes TEXT NULL,
                            completion_percentage INT DEFAULT 0,
                            estimated_hours DECIMAL(5,2) NULL,
                            actual_hours DECIMAL(5,2) NULL,
                            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                            updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
                            completed_at DATETIME NULL,

                            FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE,
                            FOREIGN KEY (course_id) REFERENCES course(id) ON DELETE CASCADE,

                            INDEX idx_user_id (user_id),
                            INDEX idx_course_id (course_id),
                            INDEX idx_due_date (due_date),
                            INDEX idx_status (status),
                            INDEX idx_priority (priority),
                            INDEX idx_created_at (created_at),

                            CHECK (completion_percentage >= 0 AND completion_percentage <= 100),
                            CHECK (priority IN ('low', 'medium', 'high', 'urgent')),
                            CHECK (status IN ('todo', 'in_progress', 'completed', 'cancelled'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- DONNÉES DE TEST (Optionnel)
-- ============================================

-- Utilisateur de test (mot de passe : password123)
-- Hash bcrypt de "password123"
INSERT INTO user (email, password, first_name, last_name, roles) VALUES
    ('test@example.com', '$2y$13$KvO8RwXXxXxXxXxXxXxXxXxXxXxXxXxXxXxXxXxXxXxXxXxXx', 'John', 'Doe', '["ROLE_USER"]');

-- Matières de test
INSERT INTO course (user_id, name, code, color, professor, credits, semester) VALUES
                                                                                  (1, 'Mathématiques Avancées', 'MATH301', '#0d6efd', 'Dr. Martin Dupont', 6, 'Automne 2024'),
                                                                                  (1, 'Programmation Web', 'INFO202', '#198754', 'Prof. Sarah Martin', 4, 'Automne 2024'),
                                                                                  (1, 'Base de Données', 'INFO305', '#dc3545', 'Dr. Ahmed Ali', 5, 'Automne 2024');

-- Travaux de test
INSERT INTO assignment (user_id, course_id, title, description, due_date, priority, status, completion_percentage) VALUES
    (1, 1, 'Devoir d\'algèbre linéaire', 'Résoudre les exercices du chapitre 5', '2025-01-15 23:59:00', 'high', 'todo', 0),
(1, 2, 'Projet final - Site e-commerce', 'Créer un site e-commerce complet avec Symfony', '2025-02-28 23:59:00', 'urgent', 'in_progress', 45),
(1, 3, 'TP Base de données relationnelles', 'Normalisation et requêtes SQL avancées', '2025-01-20 18:00:00', 'medium', 'todo', 0);

-- ============================================
-- VUES POUR LES STATISTIQUES
-- ============================================

-- Vue pour les statistiques par utilisateur
CREATE OR REPLACE VIEW user_statistics AS
SELECT
    u.id as user_id,
    u.email,
    COUNT(DISTINCT c.id) as total_courses,
    COUNT(a.id) as total_assignments,
    SUM(CASE WHEN a.status = 'completed' THEN 1 ELSE 0 END) as completed_assignments,
    SUM(CASE WHEN a.status = 'in_progress' THEN 1 ELSE 0 END) as in_progress_assignments,
    SUM(CASE WHEN a.status = 'todo' THEN 1 ELSE 0 END) as todo_assignments,
    SUM(CASE WHEN a.due_date < NOW() AND a.status != 'completed' THEN 1 ELSE 0 END) as overdue_assignments,
    ROUND(AVG(a.completion_percentage), 2) as avg_completion
FROM user u
LEFT JOIN course c ON c.user_id = u.id
LEFT JOIN assignment a ON a.user_id = u.id
GROUP BY u.id, u.email;

-- Vue pour les travaux urgents
CREATE OR REPLACE VIEW urgent_assignments AS
SELECT
    a.*,
    c.name as course_name,
    c.color as course_color,
    u.email as user_email,
    DATEDIFF(a.due_date, NOW()) as days_remaining
FROM assignment a
INNER JOIN course c ON a.course_id = c.id
INNER JOIN user u ON a.user_id = u.id
WHERE a.status != 'completed'
  AND a.status != 'cancelled'
  AND a.due_date > NOW()
ORDER BY a.due_date ASC, a.priority DESC;

-- ============================================
-- INDEXES SUPPLÉMENTAIRES POUR PERFORMANCE
-- ============================================

-- Index composite pour les requêtes fréquentes
CREATE INDEX idx_assignment_user_status ON assignment(user_id, status);
CREATE INDEX idx_assignment_user_duedate ON assignment(user_id, due_date);
CREATE INDEX idx_assignment_status_duedate ON assignment(status, due_date);

-- ============================================
-- TRIGGERS POUR AUTOMATISATION
-- ============================================

-- Trigger : Mettre à jour completed_at quand status = 'completed'
DELIMITER $$
CREATE TRIGGER set_completed_at_on_status_change
BEFORE UPDATE ON assignment
FOR EACH ROW
BEGIN
    IF NEW.status = 'completed' AND OLD.status != 'completed' THEN
        SET NEW.completed_at = NOW();
        SET NEW.completion_percentage = 100;
    END IF;

    IF NEW.status != 'completed' THEN
        SET NEW.completed_at = NULL;
    END IF;
END$$
DELIMITER ;

-- Trigger : Mettre à jour last_login lors de la connexion
DELIMITER $$
CREATE TRIGGER update_last_login
BEFORE UPDATE ON user
FOR EACH ROW
BEGIN
    IF NEW.password != OLD.password OR NEW.email != OLD.email THEN
        SET NEW.updated_at = NOW();
    END IF;
END$$
DELIMITER ;

-- ============================================
-- COMMENTAIRES SUR LES TABLES
-- ============================================

ALTER TABLE user COMMENT = 'Table des utilisateurs de l\'application';
ALTER TABLE course COMMENT = 'Table des matières/cours universitaires';
ALTER TABLE assignment COMMENT = 'Table des travaux/devoirs à réaliser';

-- ============================================
-- FIN DU SCHEMA
-- ============================================
