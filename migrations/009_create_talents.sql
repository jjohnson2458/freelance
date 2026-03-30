CREATE TABLE IF NOT EXISTS talents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    category VARCHAR(100) DEFAULT NULL,
    proficiency ENUM('beginner','intermediate','advanced','expert') NOT NULL DEFAULT 'intermediate',
    years_experience INT DEFAULT NULL,
    description TEXT DEFAULT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
