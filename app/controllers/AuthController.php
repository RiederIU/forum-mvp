<?php

require_once __DIR__ . '/../models/User.php';

class AuthController
{
    // =========================================================================
    //  11a  Registrierung
    // =========================================================================

    public static function register(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $csrfToken = generateCsrfToken();
            require __DIR__ . '/../views/layout/header.php';
            require __DIR__ . '/../views/auth/register.php';
            require __DIR__ . '/../views/layout/footer.php';
            return;
        }

        // POST-Verarbeitung

        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Ungültiges Formular-Token.');
            logAction('CSRF_FAIL', 'action=register');
            header('Location: index.php?action=register');
            exit;
        }

        $username        = trim($_POST['username'] ?? '');
        $email           = trim($_POST['email'] ?? '');
        $password        = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';

        /**
         * Sammelt alle Validierungsfehler für eine gemeinsame Ausgabe.
         * Verhindert, dass der Nutzer das Formular pro Fehler neu absenden muss.
         */
        $errors = [];

        if (empty($username)) {
            $errors[] = 'Benutzername ist Pflicht.';
        }
        if (empty($email)) {
            $errors[] = 'E-Mail ist Pflicht.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Ungültiges E-Mail-Format.';
        }
        if (strlen($password) < MIN_PASS) {
            $errors[] = 'Passwort muss mindestens ' . MIN_PASS . ' Zeichen lang sein.';
        }
        if ($password !== $passwordConfirm) {
            $errors[] = 'Passwörter stimmen nicht überein.';
        }

        if (count($errors) > 0) {
            setFlash('error', implode(' ', $errors));
            header('Location: index.php?action=register');
            exit;
        }

        if (User::existsByUsernameOrEmail($username, $email)) {
            setFlash('error', 'Benutzername oder E-Mail bereits vergeben.');
            header('Location: index.php?action=register');
            exit;
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        User::create($username, $email, $hash);
        logAction('REGISTER', 'new_user=' . $username);

        setFlash('success', 'Registrierung erfolgreich. Bitte einloggen.');
        header('Location: index.php?action=login');
        exit;
    }

    // =========================================================================
    //  11b  Login
    // =========================================================================

    public static function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $csrfToken = generateCsrfToken();
            require __DIR__ . '/../views/layout/header.php';
            require __DIR__ . '/../views/auth/login.php';
            require __DIR__ . '/../views/layout/footer.php';
            return;
        }

        // POST-Verarbeitung

        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Ungültiges Formular-Token.');
            logAction('CSRF_FAIL', 'action=login');
            header('Location: index.php?action=login');
            exit;
        }

        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $user = User::findByEmail($email);

        /**
         * Bei fehlgeschlagenem Login wird immer dieselbe Meldung ausgegeben.
         * So lässt sich nicht erkennen, ob E-Mail oder Passwort falsch war.
         */
        if (!$user || !password_verify($password, $user['password_hash'])) {
            logAction('LOGIN_FAIL', 'email=' . $email);
            setFlash('error', 'Ungültige Anmeldedaten.');
            header('Location: index.php?action=login');
            exit;
        }

        /**
         * Neue Session-ID generieren, alte serverseitig löschen (Session-Fixation-Schutz).
         */
        session_regenerate_id(true);

        $_SESSION['user'] = [
            'id'       => $user['id'],
            'username' => $user['username'],
            'email'    => $user['email'],
            'role'     => $user['role']
        ];

        logAction('LOGIN_OK', 'email=' . $email);
        setFlash('success', 'Willkommen, ' . htmlspecialchars($user['username']) . '!');
        header('Location: index.php?action=topics.index');
        exit;
    }

    // =========================================================================
    //  11c  Logout
    // =========================================================================

    public static function logout(): void
    {
        logAction('LOGOUT', '');

        /**
         * Aktuelle Session komplett beenden und neu starten,
         * da Flash-Messages eine aktive Session brauchen.
         */
        $_SESSION = [];
        session_destroy();

        startSession();
        setFlash('success', 'Erfolgreich abgemeldet.');

        header('Location: index.php?action=login');
        exit;
    }
}
