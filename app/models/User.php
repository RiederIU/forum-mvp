<?php

/**
 * User-Model.
 * Kapselt alle Datenbankoperationen auf der users-Tabelle.
 */

class User
{
    public static function findByEmail(string $email): ?array
    {
        $db = getDB();
        $stmt = $db->prepare('SELECT * FROM users WHERE email = :email');
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function existsByUsernameOrEmail(string $username, string $email): bool
    {
        $db = getDB();
        $stmt = $db->prepare(
            'SELECT id FROM users WHERE username = :username OR email = :email'
        );
        $stmt->execute([':username' => $username, ':email' => $email]);
        return (bool) $stmt->fetch();
    }

    public static function create(string $username, string $email, string $passwordHash): int
    {
        $db = getDB();
        $stmt = $db->prepare(
            'INSERT INTO users (username, email, password_hash)
             VALUES (:username, :email, :password_hash)'
        );
        $stmt->execute([
            ':username'      => $username,
            ':email'         => $email,
            ':password_hash' => $passwordHash
        ]);
        return (int) $db->lastInsertId();
    }
}
