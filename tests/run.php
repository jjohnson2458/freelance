<?php

/**
 * Simple test runner for claude_freelance.
 * Usage: php tests/run.php
 */

define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/core/Env.php';
Core\Env::load(BASE_PATH . '/.env');

// Simple assertion framework
$passed = 0;
$failed = 0;
$errors = [];

function assert_true($condition, string $testName): void
{
    global $passed, $failed, $errors;
    if ($condition) {
        $passed++;
        echo "  PASS: {$testName}\n";
    } else {
        $failed++;
        $errors[] = $testName;
        echo "  FAIL: {$testName}\n";
    }
}

function assert_equals($expected, $actual, string $testName): void
{
    global $passed, $failed, $errors;
    if ($expected === $actual) {
        $passed++;
        echo "  PASS: {$testName}\n";
    } else {
        $failed++;
        $errors[] = "{$testName} (expected: " . var_export($expected, true) . ", got: " . var_export($actual, true) . ")";
        echo "  FAIL: {$testName} (expected: " . var_export($expected, true) . ", got: " . var_export($actual, true) . ")\n";
    }
}

function assert_not_null($value, string $testName): void
{
    assert_true($value !== null, $testName);
}

echo "=== claude_freelance Test Suite ===\n\n";

// ---- Core Tests ----
echo "--- Core\\Env ---\n";
assert_equals('freelance', Core\Env::get('DB_NAME', ''), 'Env loads DB_NAME');
assert_equals('root', Core\Env::get('DB_USER', ''), 'Env loads DB_USER');
assert_equals('default_val', Core\Env::get('NONEXISTENT_KEY', 'default_val'), 'Env returns default for missing key');

echo "\n--- Core\\Database ---\n";
require_once BASE_PATH . '/core/Database.php';
require_once BASE_PATH . '/core/ErrorHandler.php';
try {
    $db = Core\Database::getInstance();
    assert_true($db instanceof PDO, 'Database connects successfully');

    $stmt = $db->query("SELECT COUNT(*) as cnt FROM users");
    $count = (int) $stmt->fetch()['cnt'];
    assert_true($count >= 1, 'Users table has at least 1 record (admin seed)');
} catch (Exception $e) {
    assert_true(false, 'Database connection: ' . $e->getMessage());
}

echo "\n--- Core\\Csrf ---\n";
session_start();
require_once BASE_PATH . '/core/Csrf.php';
Core\Csrf::init();
$token = Core\Csrf::token();
assert_true(strlen($token) === 64, 'CSRF token is 64 chars');
$field = Core\Csrf::field();
assert_true(str_contains($field, 'csrf_token'), 'CSRF field contains token input');

echo "\n--- Core\\Auth ---\n";
require_once BASE_PATH . '/core/Auth.php';
assert_true(!Core\Auth::check(), 'Auth::check() is false before login');
assert_true(Core\Auth::user() === null, 'Auth::user() is null before login');

echo "\n--- Models ---\n";
require_once BASE_PATH . '/core/Model.php';
require_once BASE_PATH . '/app/Models/User.php';
require_once BASE_PATH . '/app/Models/Platform.php';
require_once BASE_PATH . '/app/Models/ProposalRule.php';
require_once BASE_PATH . '/app/Models/Resume.php';
require_once BASE_PATH . '/app/Models/Availability.php';

$user = App\Models\User::findByEmail('email4johnson@gmail.com');
assert_not_null($user, 'User::findByEmail finds admin');
assert_equals('J.J. Johnson', $user['name'], 'Admin user has correct name');
assert_equals('admin', $user['role'], 'Admin user has admin role');

$platforms = App\Models\Platform::all('id ASC');
assert_equals(5, count($platforms), 'All 5 platforms seeded');
assert_equals('Upwork', $platforms[0]['name'], 'First platform is Upwork');

$upwork = App\Models\Platform::findBySlug('upwork');
assert_not_null($upwork, 'Platform::findBySlug finds Upwork');
assert_equals('UpworkParser', $upwork['parser_class'], 'Upwork has correct parser class');

$rules = App\Models\ProposalRule::getActiveRules($user['id']);
assert_equals(10, count($rules), '10 proposal rules seeded');
assert_equals('always', $rules[0]['category'], 'First rule is always category');

$availability = App\Models\Availability::where('user_id', $user['id']);
assert_true(count($availability) >= 1, 'Availability window seeded');
assert_equals('2026-04-01', $availability[0]['available_from'], 'Availability starts April 1, 2026');

echo "\n--- Services ---\n";
require_once BASE_PATH . '/app/Services/ClaudeApiService.php';
$api = new App\Services\ClaudeApiService();
assert_true(!$api->isConfigured(), 'API reports not configured (no key in .env)');

require_once BASE_PATH . '/app/Services/ProposalGenerator.php';
$generator = new App\Services\ProposalGenerator();
assert_true(!$generator->isConfigured(), 'Generator reports not configured (no API key)');

echo "\n--- Routes ---\n";
require_once BASE_PATH . '/core/Router.php';
$router = new Core\Router();
require BASE_PATH . '/config/routes.php';
// Just verify routes file loads without errors
assert_true(true, 'Routes file loads without errors');

echo "\n--- Database Schema ---\n";
$tables = ['users', 'resumes', 'platforms', 'jobs', 'proposals', 'proposal_rules', 'availability', 'error_log'];
foreach ($tables as $table) {
    $stmt = $db->query("SHOW TABLES LIKE '{$table}'");
    assert_true($stmt->rowCount() > 0, "Table '{$table}' exists");
}

// ---- Summary ----
echo "\n=== Results ===\n";
echo "Passed: {$passed}\n";
echo "Failed: {$failed}\n";

if (!empty($errors)) {
    echo "\nFailed tests:\n";
    foreach ($errors as $e) {
        echo "  - {$e}\n";
    }
}

echo "\n" . ($failed === 0 ? "ALL TESTS PASSED" : "SOME TESTS FAILED") . "\n";
exit($failed > 0 ? 1 : 0);
