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
assert_true($api->isConfigured(), 'API is configured (key in .env)');

require_once BASE_PATH . '/app/Services/ProposalGenerator.php';
$generator = new App\Services\ProposalGenerator();
assert_true($generator->isConfigured(), 'Generator is configured (API key present)');

echo "\n--- Parsers ---\n";
require_once BASE_PATH . '/app/Services/Parsers/BaseParser.php';
require_once BASE_PATH . '/app/Services/Parsers/UpworkParser.php';
require_once BASE_PATH . '/app/Services/Parsers/WellfoundParser.php';
require_once BASE_PATH . '/app/Services/Parsers/ContraParser.php';
require_once BASE_PATH . '/app/Services/Parsers/TuringParser.php';
require_once BASE_PATH . '/app/Services/Parsers/FreelancerParser.php';

// Test canHandle detection
assert_true(App\Services\Parsers\UpworkParser::canHandle('noreply@upwork.com', 'New job'), 'UpworkParser detects Upwork emails');
assert_true(App\Services\Parsers\WellfoundParser::canHandle('notifications@wellfound.com', 'jobs'), 'WellfoundParser detects Wellfound emails');
assert_true(App\Services\Parsers\ContraParser::canHandle('hello@contra.com', 'opportunity'), 'ContraParser detects Contra emails');
assert_true(App\Services\Parsers\TuringParser::canHandle('notifications@turing.com', 'match'), 'TuringParser detects Turing emails');
assert_true(App\Services\Parsers\FreelancerParser::canHandle('noreply@freelancer.com', 'project'), 'FreelancerParser detects Freelancer emails');
assert_true(!App\Services\Parsers\UpworkParser::canHandle('someone@gmail.com', 'hello'), 'UpworkParser rejects non-Upwork emails');

// Test UpworkParser with sample email
$sampleEmail = "From: noreply@upwork.com\r\nSubject: New job: Build a PHP Dashboard\r\nContent-Type: text/plain\r\n\r\nNew job: Build a PHP Dashboard\n\nDescription:\nWe need a senior PHP developer to build a dashboard.\n\nSkills: PHP, MySQL, JavaScript, Bootstrap\nBudget: \$500 - \$1000 (fixed-price)\n\nClient location: United States\nClient rating: 4.8\n\nhttps://www.upwork.com/jobs/~01abc123\n\nUnsubscribe from job alerts";

$parser = new App\Services\Parsers\UpworkParser($sampleEmail);
$parsed = $parser->parse();
assert_not_null($parsed, 'UpworkParser parses sample email');
assert_true(str_contains($parsed['title'], 'PHP Dashboard'), 'UpworkParser extracts title');
assert_true($parsed['budget_min'] == 500, 'UpworkParser extracts budget min');
assert_true($parsed['budget_max'] == 1000, 'UpworkParser extracts budget max');
assert_equals('fixed', $parsed['budget_type'], 'UpworkParser detects fixed budget');
assert_true(str_contains($parsed['job_url'], 'upwork.com'), 'UpworkParser extracts job URL');

// Test EmailPipeline loads
require_once BASE_PATH . '/app/Services/EmailPipeline.php';
$pipeline = new App\Services\EmailPipeline(1);
assert_true($pipeline instanceof App\Services\EmailPipeline, 'EmailPipeline instantiates');

echo "\n--- Routes ---\n";
require_once BASE_PATH . '/core/Router.php';
$router = new Core\Router();
require BASE_PATH . '/config/routes.php';
// Just verify routes file loads without errors
assert_true(true, 'Routes file loads without errors');

echo "\n--- Talent Model ---\n";
require_once BASE_PATH . '/app/Models/Talent.php';
assert_true(class_exists('App\Models\Talent'), 'Talent class exists');
assert_true(method_exists('App\Models\Talent', 'getByUser'), 'Talent has getByUser method');

$talents = App\Models\Talent::getByUser($user['id']);
assert_true(is_array($talents), 'Talent::getByUser returns an array');

echo "\n--- FileTextExtractor ---\n";
require_once BASE_PATH . '/app/Services/FileTextExtractor.php';
assert_true(class_exists('App\Services\FileTextExtractor'), 'FileTextExtractor class exists');

// Test extractTxt with a temp file
$tmpFile = tempnam(sys_get_temp_dir(), 'test_');
file_put_contents($tmpFile, 'Hello from test file');
$extracted = App\Services\FileTextExtractor::extract($tmpFile, 'txt');
assert_equals('Hello from test file', trim($extracted), 'FileTextExtractor::extract reads .txt content');
unlink($tmpFile);

// Test extract returns null for missing file
$missing = App\Services\FileTextExtractor::extract('/nonexistent/file.txt', 'txt');
assert_true($missing === null, 'FileTextExtractor returns null for missing file');

echo "\n--- Route Validation ---\n";
// Verify new routes are registered by checking the routes file defines them
$routesContent = file_get_contents(BASE_PATH . '/config/routes.php');
assert_true(str_contains($routesContent, "'talents'"), 'Routes file contains talents route');
assert_true(str_contains($routesContent, "'talents/create'"), 'Routes file contains talents/create route');
assert_true(str_contains($routesContent, "'talents/store'"), 'Routes file contains talents/store route');
assert_true(str_contains($routesContent, "'talents/toggle/{id}'"), 'Routes file contains talents/toggle route');
assert_true(str_contains($routesContent, "'guide'"), 'Routes file contains guide route');

echo "\n--- Database Schema ---\n";
$tables = ['users', 'resumes', 'platforms', 'jobs', 'proposals', 'proposal_rules', 'availability', 'error_log', 'talents'];
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
