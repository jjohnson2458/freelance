CREATE TABLE IF NOT EXISTS `api_usage_log` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED DEFAULT NULL,
    `feature` VARCHAR(100) NOT NULL COMMENT 'e.g. proposal_generate, proposal_regenerate, email_pipeline',
    `model` VARCHAR(100) NOT NULL COMMENT 'e.g. claude-sonnet-4-20250514',
    `input_tokens` INT UNSIGNED DEFAULT 0,
    `output_tokens` INT UNSIGNED DEFAULT 0,
    `total_tokens` INT UNSIGNED DEFAULT 0,
    `estimated_cost_usd` DECIMAL(10,6) DEFAULT 0 COMMENT 'Estimated cost based on model pricing',
    `response_time_ms` INT UNSIGNED DEFAULT NULL,
    `success` TINYINT(1) DEFAULT 1,
    `error_message` TEXT DEFAULT NULL,
    `metadata` JSON DEFAULT NULL COMMENT 'Optional extra context',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_user` (`user_id`),
    INDEX `idx_feature` (`feature`),
    INDEX `idx_created` (`created_at`),
    INDEX `idx_model` (`model`)
) ENGINE=InnoDB;
