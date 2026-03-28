<?php

namespace App\Controllers;

require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/core/Auth.php';
require_once BASE_PATH . '/core/Csrf.php';
require_once BASE_PATH . '/core/Database.php';
require_once BASE_PATH . '/app/Models/Platform.php';

use App\Models\Platform;
use Core\Auth;
use Core\Csrf;

class PlatformController extends \Core\Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $platforms = Platform::all('name ASC');
        $success = $this->getFlash('success');
        $error = $this->getFlash('error');
        $activePage = 'platforms';
        $pageTitle = 'Platforms';

        $this->view('platforms.index', compact('platforms', 'success', 'error', 'activePage', 'pageTitle'));
    }

    public function toggle(string $id): void
    {
        $this->requireAuth();
        Csrf::verifyOrFail();

        $platform = Platform::find((int) $id);

        if (!$platform) {
            $this->flash('error', 'Platform not found.');
            $this->redirect('/platforms');
        }

        $newStatus = (int) $platform['is_active'] === 1 ? 0 : 1;
        Platform::update((int) $id, ['is_active' => $newStatus]);

        $statusText = $newStatus ? 'enabled' : 'disabled';
        $this->flash('success', htmlspecialchars($platform['name']) . ' has been ' . $statusText . '.');
        $this->redirect('/platforms');
    }

    public function edit(string $id): void
    {
        $this->requireAuth();

        $platform = Platform::find((int) $id);

        if (!$platform) {
            $this->flash('error', 'Platform not found.');
            $this->redirect('/platforms');
        }

        $activePage = 'platforms';
        $pageTitle = 'Edit Platform';
        $error = $this->getFlash('error');

        $this->view('platforms.edit', compact('platform', 'activePage', 'pageTitle', 'error'));
    }

    public function update(string $id): void
    {
        $this->requireAuth();
        Csrf::verifyOrFail();

        $platform = Platform::find((int) $id);

        if (!$platform) {
            $this->flash('error', 'Platform not found.');
            $this->redirect('/platforms');
        }

        $data = [
            'base_url' => trim($_POST['base_url'] ?? ''),
            'alert_email_from' => trim($_POST['alert_email_from'] ?? ''),
            'parser_class' => trim($_POST['parser_class'] ?? ''),
            'notes' => trim($_POST['notes'] ?? ''),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // Allow name update only if provided (but field is readonly in form)
        if (!empty(trim($_POST['name'] ?? ''))) {
            $data['name'] = trim($_POST['name']);
        }

        Platform::update((int) $id, $data);

        $this->flash('success', 'Platform updated successfully.');
        $this->redirect('/platforms');
    }
}
