<?php

namespace App\Models;

require_once BASE_PATH . '/core/Model.php';

class Talent extends \Core\Model
{
    protected static string $table = 'talents';

    public static function getByUser(int $userId): array
    {
        return self::where('user_id', $userId);
    }
}
