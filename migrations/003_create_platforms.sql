CREATE TABLE IF NOT EXISTS platforms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(50) UNIQUE NOT NULL,
    base_url VARCHAR(255),
    is_active TINYINT(1) DEFAULT 1,
    alert_email_from VARCHAR(255),
    parser_class VARCHAR(100),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed 5 target platforms
INSERT IGNORE INTO platforms (name, slug, base_url, is_active, alert_email_from, parser_class, notes) VALUES
('Upwork', 'upwork', 'https://www.upwork.com', 1, 'noreply@upwork.com', 'UpworkParser', 'Primary platform, bidding model'),
('Wellfound', 'wellfound', 'https://wellfound.com', 1, 'notifications@wellfound.com', 'WellfoundParser', 'Startup ecosystem, direct hire'),
('Contra', 'contra', 'https://contra.com', 1, 'hello@contra.com', 'ContraParser', 'Portfolio-driven, 0% commission'),
('Turing.com', 'turing', 'https://www.turing.com', 1, 'notifications@turing.com', 'TuringParser', 'AI-matched, long-term contracts'),
('Freelancer.com', 'freelancer', 'https://www.freelancer.com', 1, 'noreply@freelancer.com', 'FreelancerParser', 'Bidding + contest model');
