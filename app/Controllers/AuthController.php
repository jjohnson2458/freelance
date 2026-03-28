<?php

namespace App\Controllers;

require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/core/Auth.php';
require_once BASE_PATH . '/core/Csrf.php';
require_once BASE_PATH . '/core/Database.php';
require_once BASE_PATH . '/app/Models/User.php';

use App\Models\User;
use Core\Auth;
use Core\Csrf;

class AuthController extends \Core\Controller
{
    public function showLogin(): void
    {
        if (Auth::check()) {
            $this->redirect('/dashboard');
        }

        $error = $this->getFlash('error');
        $success = $this->getFlash('success');
        $this->view('auth.login', compact('error', 'success'));
    }

    public function login(): void
    {
        Csrf::verifyOrFail();

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $this->flash('error', 'Email and password are required.');
            $this->redirect('/login');
        }

        if (Auth::attempt($email, $password)) {
            $this->redirect('/dashboard');
        }

        $this->flash('error', 'Invalid email or password.');
        $this->redirect('/login');
    }

    public function showRegister(): void
    {
        if (Auth::check()) {
            $this->redirect('/dashboard');
        }

        $error = $this->getFlash('error');
        $this->view('auth.register', compact('error'));
    }

    public function register(): void
    {
        Csrf::verifyOrFail();

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';

        // Validation
        $errors = [];
        if (empty($name)) {
            $errors[] = 'Name is required.';
        }
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'A valid email is required.';
        }
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters.';
        }
        if ($password !== $passwordConfirm) {
            $errors[] = 'Passwords do not match.';
        }

        // Check if email already exists
        if (empty($errors) && User::findByEmail($email)) {
            $errors[] = 'An account with that email already exists.';
        }

        if (!empty($errors)) {
            $this->flash('error', implode(' ', $errors));
            $this->redirect('/register');
        }

        // Create user
        User::create([
            'name' => $name,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'role' => 'user',
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        // Auto-login
        Auth::attempt($email, $password);
        $this->redirect('/dashboard');
    }

    public function logout(): void
    {
        Auth::logout();
        $this->redirect('/login');
    }

    public function showForgotPassword(): void
    {
        $error = $this->getFlash('error');
        $success = $this->getFlash('success');
        $this->view('auth.forgot-password', compact('error', 'success'));
    }

    public function forgotPassword(): void
    {
        Csrf::verifyOrFail();

        $email = trim($_POST['email'] ?? '');

        if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $user = User::findByEmail($email);

            if ($user) {
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

                User::update($user['id'], [
                    'reset_token' => $token,
                    'reset_expires' => $expires,
                ]);

                // Determine base URL
                $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                $resetLink = "{$scheme}://{$host}/reset-password/{$token}";

                $body = "<p>You requested a password reset for your Freelance Proposal Optimizer account.</p>"
                    . "<p><a href=\"{$resetLink}\">Click here to reset your password</a></p>"
                    . "<p>This link expires in 1 hour.</p>"
                    . "<p>If you did not request this, please ignore this email.</p>";

                $subject = 'Password Reset - Freelance Proposal Optimizer';
                $escapedSubject = escapeshellarg($subject);
                $escapedBody = escapeshellarg($body);
                $escapedTo = escapeshellarg($email);

                exec("php C:/xampp/htdocs/claude_messenger/notify.php --subject {$escapedSubject} --body {$escapedBody} --project claude_freelance --to {$escapedTo}");
            }
        }

        // Always show success to avoid revealing whether the email exists
        $this->flash('success', 'If an account with that email exists, a password reset link has been sent.');
        $this->redirect('/forgot-password');
    }

    public function showResetPassword(string $token): void
    {
        $user = User::whereFirst('reset_token', $token);

        if (!$user || strtotime($user['reset_expires']) < time()) {
            $this->flash('error', 'This reset link is invalid or has expired.');
            $this->redirect('/forgot-password');
        }

        $error = $this->getFlash('error');
        $this->view('auth.reset-password', compact('token', 'error'));
    }

    public function resetPassword(): void
    {
        Csrf::verifyOrFail();

        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';

        if (empty($token)) {
            $this->flash('error', 'Invalid reset token.');
            $this->redirect('/forgot-password');
        }

        $user = User::whereFirst('reset_token', $token);

        if (!$user || strtotime($user['reset_expires']) < time()) {
            $this->flash('error', 'This reset link is invalid or has expired.');
            $this->redirect('/forgot-password');
        }

        if (strlen($password) < 8) {
            $this->flash('error', 'Password must be at least 8 characters.');
            $this->redirect('/reset-password/' . $token);
        }

        if ($password !== $passwordConfirm) {
            $this->flash('error', 'Passwords do not match.');
            $this->redirect('/reset-password/' . $token);
        }

        User::update($user['id'], [
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'reset_token' => null,
            'reset_expires' => null,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $this->flash('success', 'Your password has been reset. Please log in.');
        $this->redirect('/login');
    }
}
