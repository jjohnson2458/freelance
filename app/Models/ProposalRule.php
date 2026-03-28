<?php

namespace App\Models;

require_once BASE_PATH . '/core/Model.php';

class ProposalRule extends \Core\Model
{
    protected static string $table = 'proposal_rules';

    public static function getActiveRules(int $userId): array
    {
        $stmt = self::db()->prepare(
            "SELECT * FROM " . static::$table . " WHERE user_id = ? AND is_active = 1 ORDER BY sort_order ASC"
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
}
