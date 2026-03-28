<?php

namespace Core;

use PDO;

abstract class Model
{
    protected static string $table = '';
    protected static string $primaryKey = 'id';

    public static function db(): PDO
    {
        return Database::getInstance();
    }

    public static function find(int $id): ?array
    {
        $stmt = self::db()->prepare(
            "SELECT * FROM " . static::$table . " WHERE " . static::$primaryKey . " = ?"
        );
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public static function all(string $orderBy = 'id DESC'): array
    {
        $stmt = self::db()->query(
            "SELECT * FROM " . static::$table . " ORDER BY " . $orderBy
        );
        return $stmt->fetchAll();
    }

    public static function where(string $column, mixed $value): array
    {
        $stmt = self::db()->prepare(
            "SELECT * FROM " . static::$table . " WHERE {$column} = ?"
        );
        $stmt->execute([$value]);
        return $stmt->fetchAll();
    }

    public static function whereFirst(string $column, mixed $value): ?array
    {
        $stmt = self::db()->prepare(
            "SELECT * FROM " . static::$table . " WHERE {$column} = ? LIMIT 1"
        );
        $stmt->execute([$value]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public static function create(array $data): int
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $stmt = self::db()->prepare(
            "INSERT INTO " . static::$table . " ({$columns}) VALUES ({$placeholders})"
        );
        $stmt->execute(array_values($data));
        return (int) self::db()->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $sets = implode(', ', array_map(fn($col) => "{$col} = ?", array_keys($data)));
        $stmt = self::db()->prepare(
            "UPDATE " . static::$table . " SET {$sets} WHERE " . static::$primaryKey . " = ?"
        );
        return $stmt->execute([...array_values($data), $id]);
    }

    public static function delete(int $id): bool
    {
        $stmt = self::db()->prepare(
            "DELETE FROM " . static::$table . " WHERE " . static::$primaryKey . " = ?"
        );
        return $stmt->execute([$id]);
    }

    public static function count(string $where = '1=1', array $params = []): int
    {
        $stmt = self::db()->prepare(
            "SELECT COUNT(*) as cnt FROM " . static::$table . " WHERE {$where}"
        );
        $stmt->execute($params);
        return (int) $stmt->fetch()['cnt'];
    }

    public static function paginate(int $page = 1, int $perPage = 20, string $where = '1=1', array $params = [], string $orderBy = 'id DESC'): array
    {
        $offset = ($page - 1) * $perPage;
        $total = self::count($where, $params);

        $stmt = self::db()->prepare(
            "SELECT * FROM " . static::$table . " WHERE {$where} ORDER BY {$orderBy} LIMIT {$perPage} OFFSET {$offset}"
        );
        $stmt->execute($params);

        return [
            'data' => $stmt->fetchAll(),
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => (int) ceil($total / $perPage),
        ];
    }
}
