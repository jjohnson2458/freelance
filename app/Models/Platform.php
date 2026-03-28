<?php

namespace App\Models;

require_once BASE_PATH . '/core/Model.php';

class Platform extends \Core\Model
{
    protected static string $table = 'platforms';

    public static function findBySlug(string $slug): ?array
    {
        return self::whereFirst('slug', $slug);
    }
}
