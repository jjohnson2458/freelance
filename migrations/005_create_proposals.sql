CREATE TABLE IF NOT EXISTS proposals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    job_id INT NOT NULL,
    resume_id INT NOT NULL,
    content TEXT NOT NULL,
    tone VARCHAR(50),
    suggested_rate DECIMAL(10,2),
    rate_type ENUM('hourly','fixed','not_specified'),
    version INT DEFAULT 1,
    is_submitted TINYINT(1) DEFAULT 0,
    submitted_at TIMESTAMP NULL,
    api_model VARCHAR(100),
    api_tokens_used INT,
    generation_time_ms INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
    FOREIGN KEY (resume_id) REFERENCES resumes(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
