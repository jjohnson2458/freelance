<?php

/**
 * Seed default admin user and proposal rules.
 * Usage: php migrations/seed.php
 */

define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/core/Env.php';
Core\Env::load(BASE_PATH . '/.env');

$host = Core\Env::get('DB_HOST', 'localhost');
$port = Core\Env::get('DB_PORT', '3306');
$name = Core\Env::get('DB_NAME', 'freelance');
$user = Core\Env::get('DB_USER', 'root');
$pass = Core\Env::get('DB_PASSWORD', '');

$pdo = new PDO("mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4", $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

// Seed admin user
$existing = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$existing->execute(['email4johnson@gmail.com']);
if (!$existing->fetch()) {
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        'J.J. Johnson',
        'email4johnson@gmail.com',
        password_hash('24AdaPlace', PASSWORD_DEFAULT),
        'admin',
    ]);
    $userId = $pdo->lastInsertId();
    echo "Admin user created (ID: {$userId}).\n";
} else {
    $userId = $existing->fetch()['id'] ?? 1;
    echo "Admin user already exists.\n";
    // Re-fetch to get the ID
    $existing2 = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $existing2->execute(['email4johnson@gmail.com']);
    $userId = $existing2->fetch()['id'];
}

// Seed default proposal rules
$rules = [
    ['always', 'Be honest about skills and experience. Primary database expertise is MySQL, not PostgreSQL. Mention MySQL proficiency directly when relevant.', 1],
    ['always', 'Reference 26 years of professional development experience. Highlight depth and breadth across technologies.', 2],
    ['availability', 'Available starting April 1, 2026. Mention availability proactively when the posting includes a start date or timeline.', 3],
    ['rate', 'Suggest a rate based on the posted budget range. If budget is low, acknowledge it diplomatically and suggest fair market rate. Never undercut drastically.', 4],
    ['never', 'Never exaggerate skills or claim expertise in technologies not on the resume. If a required skill is missing, acknowledge it honestly and highlight transferable experience.', 5],
    ['tone', 'Match the tone to the client. Corporate postings get professional language. Startup/casual postings get a friendlier, more direct tone. Always remain respectful.', 6],
    ['skills', 'Flag weak fits explicitly. If fewer than half the required skills match, recommend skipping the job and explain why in fit_notes.', 7],
    ['always', 'Keep proposals concise — 200-400 words for Upwork/Freelancer, 300-500 for Wellfound/Turing, 150-300 for Contra. Quality over length.', 8],
    ['custom', 'Open with a specific reference to something in the job posting that caught your attention. Show you read the listing carefully.', 9],
    ['custom', 'Close with a clear call to action — offer to discuss the project, share relevant samples, or start with a small trial task.', 10],
];

$ruleCount = 0;
$stmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM proposal_rules WHERE user_id = ?");
$stmt->execute([$userId]);
if ((int)$stmt->fetch()['cnt'] === 0) {
    $insert = $pdo->prepare("INSERT INTO proposal_rules (user_id, category, rule_text, is_active, sort_order) VALUES (?, ?, ?, 1, ?)");
    foreach ($rules as $rule) {
        $insert->execute([$userId, $rule[0], $rule[1], $rule[2]]);
        $ruleCount++;
    }
    echo "Seeded {$ruleCount} proposal rules.\n";
} else {
    echo "Proposal rules already exist.\n";
}

// Seed initial availability
$stmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM availability WHERE user_id = ?");
$stmt->execute([$userId]);
if ((int)$stmt->fetch()['cnt'] === 0) {
    $avail = $pdo->prepare("INSERT INTO availability (user_id, available_from, available_to, hours_per_week, notes) VALUES (?, ?, ?, ?, ?)");
    $avail->execute([$userId, '2026-04-01', null, 40, 'Full-time availability starting April 2026']);
    echo "Seeded availability window.\n";
} else {
    echo "Availability already exists.\n";
}

echo "\nSeed complete.\n";
