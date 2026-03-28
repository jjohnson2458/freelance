<?php

namespace App\Controllers;

require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/core/Auth.php';
require_once BASE_PATH . '/core/Csrf.php';
require_once BASE_PATH . '/core/Database.php';
require_once BASE_PATH . '/app/Models/Availability.php';

use App\Models\Availability;
use Core\Auth;
use Core\Csrf;

class CalendarController extends \Core\Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $stmt = Availability::db()->prepare(
            "SELECT * FROM availability WHERE user_id = ? ORDER BY available_from ASC"
        );
        $stmt->execute([Auth::id()]);
        $windows = $stmt->fetchAll();

        $success = $this->getFlash('success');
        $error = $this->getFlash('error');
        $activePage = 'calendar';
        $pageTitle = 'Availability Calendar';

        $this->view('calendar.index', compact('windows', 'success', 'error', 'activePage', 'pageTitle'));
    }

    public function store(): void
    {
        $this->requireAuth();
        Csrf::verifyOrFail();

        $availableFrom = trim($_POST['available_from'] ?? '');
        $availableTo = trim($_POST['available_to'] ?? '') ?: null;
        $hoursPerWeek = (int) ($_POST['hours_per_week'] ?? 0);
        $notes = trim($_POST['notes'] ?? '') ?: null;

        if (empty($availableFrom) || $hoursPerWeek < 1) {
            $this->flash('error', 'Start date and hours per week are required.');
            $this->redirect('/calendar');
        }

        Availability::create([
            'user_id' => Auth::id(),
            'available_from' => $availableFrom,
            'available_to' => $availableTo,
            'hours_per_week' => $hoursPerWeek,
            'notes' => $notes,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $this->flash('success', 'Availability window added.');
        $this->redirect('/calendar');
    }

    public function update(string $id): void
    {
        $this->requireAuth();
        Csrf::verifyOrFail();

        $window = Availability::find((int) $id);
        if (!$window || $window['user_id'] !== Auth::id()) {
            $this->flash('error', 'Availability window not found.');
            $this->redirect('/calendar');
        }

        $availableFrom = trim($_POST['available_from'] ?? '');
        $availableTo = trim($_POST['available_to'] ?? '') ?: null;
        $hoursPerWeek = (int) ($_POST['hours_per_week'] ?? 0);
        $notes = trim($_POST['notes'] ?? '') ?: null;

        if (empty($availableFrom) || $hoursPerWeek < 1) {
            $this->flash('error', 'Start date and hours per week are required.');
            $this->redirect('/calendar');
        }

        Availability::update((int) $id, [
            'available_from' => $availableFrom,
            'available_to' => $availableTo,
            'hours_per_week' => $hoursPerWeek,
            'notes' => $notes,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $this->flash('success', 'Availability window updated.');
        $this->redirect('/calendar');
    }

    public function delete(string $id): void
    {
        $this->requireAuth();
        Csrf::verifyOrFail();

        $window = Availability::find((int) $id);
        if (!$window || $window['user_id'] !== Auth::id()) {
            $this->flash('error', 'Availability window not found.');
            $this->redirect('/calendar');
        }

        Availability::delete((int) $id);

        $this->flash('success', 'Availability window deleted.');
        $this->redirect('/calendar');
    }
}
