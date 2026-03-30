<?php

namespace App\Controllers;

require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/core/Auth.php';
require_once BASE_PATH . '/core/Csrf.php';
require_once BASE_PATH . '/core/Database.php';
require_once BASE_PATH . '/app/Models/Talent.php';

use App\Models\Talent;
use Core\Auth;
use Core\Csrf;

class TalentController extends \Core\Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $talents = Talent::getByUser(Auth::id());
        $success = $this->getFlash('success');
        $error = $this->getFlash('error');
        $activePage = 'talents';
        $pageTitle = 'Talents';

        $this->view('talents.index', compact('talents', 'success', 'error', 'activePage', 'pageTitle'));
    }

    public function create(): void
    {
        $this->requireAuth();

        $activePage = 'talents';
        $pageTitle = 'Add Talent';
        $error = $this->getFlash('error');

        $this->view('talents.create', compact('activePage', 'pageTitle', 'error'));
    }

    public function store(): void
    {
        $this->requireAuth();
        Csrf::verifyOrFail();

        $name = trim($_POST['name'] ?? '');
        $category = trim($_POST['category'] ?? '') ?: null;
        $proficiency = $_POST['proficiency'] ?? 'intermediate';
        $yearsExperience = isset($_POST['years_experience']) && $_POST['years_experience'] !== '' ? (int) $_POST['years_experience'] : null;
        $description = trim($_POST['description'] ?? '') ?: null;

        if (empty($name)) {
            $this->flash('error', 'Talent name is required.');
            $this->redirect('/talents/create');
        }

        $validProficiencies = ['beginner', 'intermediate', 'advanced', 'expert'];
        if (!in_array($proficiency, $validProficiencies)) {
            $proficiency = 'intermediate';
        }

        Talent::create([
            'user_id' => Auth::id(),
            'name' => $name,
            'category' => $category,
            'proficiency' => $proficiency,
            'years_experience' => $yearsExperience,
            'description' => $description,
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $this->flash('success', 'Talent added successfully.');
        $this->redirect('/talents');
    }

    public function edit(string $id): void
    {
        $this->requireAuth();

        $talent = Talent::find((int) $id);

        if (!$talent || (int) $talent['user_id'] !== Auth::id()) {
            $this->flash('error', 'Talent not found.');
            $this->redirect('/talents');
        }

        $activePage = 'talents';
        $pageTitle = 'Edit Talent';
        $error = $this->getFlash('error');

        $this->view('talents.edit', compact('talent', 'activePage', 'pageTitle', 'error'));
    }

    public function update(string $id): void
    {
        $this->requireAuth();
        Csrf::verifyOrFail();

        $talent = Talent::find((int) $id);

        if (!$talent || (int) $talent['user_id'] !== Auth::id()) {
            $this->flash('error', 'Talent not found.');
            $this->redirect('/talents');
        }

        $name = trim($_POST['name'] ?? '');
        $category = trim($_POST['category'] ?? '') ?: null;
        $proficiency = $_POST['proficiency'] ?? 'intermediate';
        $yearsExperience = isset($_POST['years_experience']) && $_POST['years_experience'] !== '' ? (int) $_POST['years_experience'] : null;
        $description = trim($_POST['description'] ?? '') ?: null;

        if (empty($name)) {
            $this->flash('error', 'Talent name is required.');
            $this->redirect('/talents/edit/' . $id);
        }

        $validProficiencies = ['beginner', 'intermediate', 'advanced', 'expert'];
        if (!in_array($proficiency, $validProficiencies)) {
            $proficiency = 'intermediate';
        }

        Talent::update((int) $id, [
            'name' => $name,
            'category' => $category,
            'proficiency' => $proficiency,
            'years_experience' => $yearsExperience,
            'description' => $description,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $this->flash('success', 'Talent updated successfully.');
        $this->redirect('/talents');
    }

    public function delete(string $id): void
    {
        $this->requireAuth();
        Csrf::verifyOrFail();

        $talent = Talent::find((int) $id);

        if (!$talent || (int) $talent['user_id'] !== Auth::id()) {
            $this->flash('error', 'Talent not found.');
            $this->redirect('/talents');
        }

        Talent::delete((int) $id);

        $this->flash('success', 'Talent deleted successfully.');
        $this->redirect('/talents');
    }

    public function toggle(string $id): void
    {
        $this->requireAuth();
        Csrf::verifyOrFail();

        $talent = Talent::find((int) $id);

        if (!$talent || (int) $talent['user_id'] !== Auth::id()) {
            $this->flash('error', 'Talent not found.');
            $this->redirect('/talents');
        }

        $newStatus = $talent['is_active'] ? 0 : 1;
        Talent::update((int) $id, [
            'is_active' => $newStatus,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $label = $newStatus ? 'activated' : 'deactivated';
        $this->flash('success', "Talent {$label} successfully.");
        $this->redirect('/talents');
    }
}
