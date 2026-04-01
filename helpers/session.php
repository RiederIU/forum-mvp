<?php

function startSession(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        /**
         * Session-Cookie ist mit HttpOnly für JavaScript nicht lesbar.
         * ini_set('session.cookie_httponly', '1');
         */
        ini_set('session.cookie_httponly', '1');
        session_start();
    }
}

/**
 * Flash-Messages werden nach einmaligem Lesen automatisch gelöscht.
 */

function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array
{
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * CSRF-Token-Management.
 * Erzeugt ein Sicherheits-Token pro Sitzung und validiert es über hash_equals().
 */

function generateCsrfToken(): string
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCsrfToken(string $token): bool
{
    return isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}
