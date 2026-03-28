<?php

namespace App\Controllers;

require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/core/Auth.php';
require_once BASE_PATH . '/core/Csrf.php';
require_once BASE_PATH . '/core/Database.php';
require_once BASE_PATH . '/app/Models/ProposalRule.php';

use App\Models\ProposalRule;
use Core\Auth;
use Core\Csrf;

class RulesController extends \Core\Controller
{
    private const VALID_CATEGORIES = ['always', 'never', 'tone', 'skills', 'availability', 'rate', 'custom'];

    public function index(): void
    {
        $this->requireAuth();

        $stmt = ProposalRule::db()->prepare(
            "SELECT * FROM proposal_rules WHERE user_id = ? ORDER BY sort_order ASC"
        );
        $stmt->execute([Auth::id()]);
        $rules = $stmt->fetchAll();

        $success = $this->getFlash('success');
        $error = $this->getFlash('error');
        $activePage = 'rules';
        $pageTitle = 'Proposal Rules';

        $this->view('rules.index', compact('rules', 'success', 'error', 'activePage', 'pageTitle'));
    }

    public function create(): void
    {
        $this->requireAuth();

        $activePage = 'rules';
        $pageTitle = 'Add Rule';
        $categories = self::VALID_CATEGORIES;

        $this->view('rules.create', compact('activePage', 'pageTitle', 'categories'));
    }

    public function store(): void
    {
        $this->requireAuth();
        Csrf::verifyOrFail();

        $category = trim($_POST['category'] ?? '');
        $ruleText = trim($_POST['rule_text'] ?? '');
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if (!in_array($category, self::VALID_CATEGORIES, true) || empty($ruleText)) {
            $this->flash('error', 'Category and rule text are required.');
            $this->redirect('/rules/create');
        }

        // Get max sort_order
        $stmt = ProposalRule::db()->prepare(
            "SELECT COALESCE(MAX(sort_order), 0) as max_sort FROM proposal_rules WHERE user_id = ?"
        );
        $stmt->execute([Auth::id()]);
        $maxSort = (int) $stmt->fetch()['max_sort'];

        ProposalRule::create([
            'user_id' => Auth::id(),
            'category' => $category,
            'rule_text' => $ruleText,
            'is_active' => $isActive,
            'sort_order' => $maxSort + 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $this->flash('success', 'Rule created successfully.');
        $this->redirect('/rules');
    }

    public function edit(string $id): void
    {
        $this->requireAuth();

        $rule = ProposalRule::find((int) $id);
        if (!$rule || $rule['user_id'] !== Auth::id()) {
            $this->flash('error', 'Rule not found.');
            $this->redirect('/rules');
        }

        $activePage = 'rules';
        $pageTitle = 'Edit Rule';
        $categories = self::VALID_CATEGORIES;

        $this->view('rules.edit', compact('rule', 'activePage', 'pageTitle', 'categories'));
    }

    public function update(string $id): void
    {
        $this->requireAuth();
        Csrf::verifyOrFail();

        $rule = ProposalRule::find((int) $id);
        if (!$rule || $rule['user_id'] !== Auth::id()) {
            $this->flash('error', 'Rule not found.');
            $this->redirect('/rules');
        }

        $category = trim($_POST['category'] ?? '');
        $ruleText = trim($_POST['rule_text'] ?? '');

        if (!in_array($category, self::VALID_CATEGORIES, true) || empty($ruleText)) {
            $this->flash('error', 'Category and rule text are required.');
            $this->redirect('/rules/' . $id . '/edit');
        }

        ProposalRule::update((int) $id, [
            'category' => $category,
            'rule_text' => $ruleText,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $this->flash('success', 'Rule updated successfully.');
        $this->redirect('/rules');
    }

    public function delete(string $id): void
    {
        $this->requireAuth();
        Csrf::verifyOrFail();

        $rule = ProposalRule::find((int) $id);
        if (!$rule || $rule['user_id'] !== Auth::id()) {
            $this->flash('error', 'Rule not found.');
            $this->redirect('/rules');
        }

        ProposalRule::delete((int) $id);

        $this->flash('success', 'Rule deleted.');
        $this->redirect('/rules');
    }

    public function toggle(string $id): void
    {
        $this->requireAuth();
        Csrf::verifyOrFail();

        $rule = ProposalRule::find((int) $id);
        if (!$rule || $rule['user_id'] !== Auth::id()) {
            $this->json(['success' => false, 'error' => 'Rule not found.'], 404);
        }

        $newStatus = $rule['is_active'] ? 0 : 1;
        ProposalRule::update((int) $id, [
            'is_active' => $newStatus,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $this->json(['success' => true, 'is_active' => $newStatus]);
    }

    public function reorder(): void
    {
        $this->requireAuth();
        Csrf::verifyOrFail();

        $input = json_decode(file_get_contents('php://input'), true);
        $items = $input['items'] ?? [];

        if (empty($items)) {
            $this->json(['success' => false, 'error' => 'No items provided.'], 400);
        }

        $stmt = ProposalRule::db()->prepare(
            "UPDATE proposal_rules SET sort_order = ?, updated_at = ? WHERE id = ? AND user_id = ?"
        );

        foreach ($items as $item) {
            $stmt->execute([
                (int) $item['sort_order'],
                date('Y-m-d H:i:s'),
                (int) $item['id'],
                Auth::id(),
            ]);
        }

        $this->json(['success' => true]);
    }
}
