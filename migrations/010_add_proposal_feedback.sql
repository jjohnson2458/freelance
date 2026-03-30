ALTER TABLE proposals ADD COLUMN feedback TEXT DEFAULT NULL AFTER submitted_at;
ALTER TABLE proposals ADD COLUMN feedback_at DATETIME DEFAULT NULL AFTER feedback;
ALTER TABLE proposals ADD COLUMN client_response ENUM('won','rejected','no_response','interview') DEFAULT NULL AFTER feedback_at;
