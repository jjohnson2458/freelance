<?php

namespace App\Models;

require_once BASE_PATH . '/core/Model.php';

class User extends \Core\Model
{
    protected static string $table = 'users';

    public static function findByEmail(string $email): ?array
    {
        return self::whereFirst('email', $email);
    }
}
