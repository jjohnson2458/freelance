<?php

namespace App\Models;

require_once BASE_PATH . '/core/Model.php';

class Resume extends \Core\Model
{
    protected static string $table = 'resumes';

    public static function getActive(int $userId): ?array
    {
        $stmt = self::db()->prepare(
            "SELECT * FROM " . static::$table . " WHERE user_id = ? AND is_active = 1 LIMIT 1"
        );
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
}
