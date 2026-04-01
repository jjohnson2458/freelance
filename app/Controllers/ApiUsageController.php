<?php

namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Database;

class ApiUsageController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        // Admin only
        $user = Auth::user();
        if (($user['role'] ?? '') !== 'admin') {
            $this->flash('error', 'Access denied.');
            $this->redirect('/dashboard');
            return;
        }

        $db = Database::getInstance();

        // All-time stats
        $allTime = $db->query("
            SELECT COUNT(*) as calls, COALESCE(SUM(total_tokens),0) as tokens, COALESCE(SUM(estimated_cost_usd),0) as cost
            FROM api_usage_log WHERE success = 1
        ")->fetch();

        // Today
        $today = $db->query("
            SELECT COUNT(*) as calls, COALESCE(SUM(total_tokens),0) as tokens, COALESCE(SUM(estimated_cost_usd),0) as cost
            FROM api_usage_log WHERE success = 1 AND DATE(created_at) = CURDATE()
        ")->fetch();

        // This month
        $month = $db->query("
            SELECT COUNT(*) as calls, COALESCE(SUM(total_tokens),0) as tokens, COALESCE(SUM(estimated_cost_usd),0) as cost
            FROM api_usage_log WHERE success = 1 AND YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())
        ")->fetch();

        // Daily usage last 30 days
        $daily = $db->query("
            SELECT DATE(created_at) as date, COUNT(*) as calls, SUM(total_tokens) as tokens, SUM(estimated_cost_usd) as cost
            FROM api_usage_log WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            GROUP BY DATE(created_at) ORDER BY date DESC
        ")->fetchAll();

        // By feature
        $byFeature = $db->query("
            SELECT feature, COUNT(*) as calls, SUM(total_tokens) as tokens, SUM(estimated_cost_usd) as cost
            FROM api_usage_log GROUP BY feature ORDER BY cost DESC
        ")->fetchAll();

        // By user (top 20)
        $byUser = $db->query("
            SELECT a.user_id, u.name, u.email, COUNT(*) as calls, SUM(a.total_tokens) as tokens, SUM(a.estimated_cost_usd) as cost
            FROM api_usage_log a LEFT JOIN users u ON a.user_id = u.id
            GROUP BY a.user_id ORDER BY cost DESC LIMIT 20
        ")->fetchAll();

        // By model
        $byModel = $db->query("
            SELECT model, COUNT(*) as calls, SUM(total_tokens) as tokens, SUM(estimated_cost_usd) as cost
            FROM api_usage_log GROUP BY model ORDER BY cost DESC
        ")->fetchAll();

        // Recent 100 calls
        $recent = $db->query("
            SELECT a.*, u.name as user_name
            FROM api_usage_log a LEFT JOIN users u ON a.user_id = u.id
            ORDER BY a.created_at DESC LIMIT 100
        ")->fetchAll();

        $this->view('admin.api_usage', [
            'pageTitle' => 'API Token Usage - Admin',
            'activePage' => 'api_usage',
            'allTime' => $allTime,
            'today' => $today,
            'month' => $month,
            'daily' => $daily,
            'byFeature' => $byFeature,
            'byUser' => $byUser,
            'byModel' => $byModel,
            'recent' => $recent,
        ]);
    }
}
