<?php

require_once __DIR__ . '/../models/User.php';

class AdminController
{
    // =========================================================================
    //  Nutzerverwaltung (Übersicht)
    // =========================================================================

    public static function users(): void
    {
        requireRole('admin');

        $users     = User::getAll();
        $csrfToken = generateCsrfToken();

        require __DIR__ . '/../views/layout/header.php';
        require __DIR__ . '/../views/admin/users.php';
        require __DIR__ . '/../views/layout/footer.php';
    }

    // =========================================================================
    //  Rolle ändern (mit Selbstschutz)
    // =========================================================================

    public static function updateRole(): void
    {
        requireRole('admin');

        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Ungültiges Formular-Token.');
            logAction('CSRF_FAIL', 'action=admin.updateRole');
            header('Location: index.php?action=admin.users');
            exit;
        }

        $userId  = (int) ($_POST['user_id'] ?? 0);
        $newRole = $_POST['role'] ?? '';

        /**
         * Selbstschutz. Ein Admin darf seine eigene Rolle nicht ändern.
         * Andernfalls könnte sich der einzige Admin versehentlich zum Nutzer degradieren.
         * Die Nutzerverwaltung wäre dann ohne direkten Datenbankeingriff dauerhaft gesperrt.
         */
        if ($userId === currentUser()['id']) {
            setFlash('error', 'Die eigene Rolle kann nicht geändert werden.');
            header('Location: index.php?action=admin.users');
            exit;
        }

        $target = User::getById($userId);
        if (!$target) {
            setFlash('error', 'Nutzer nicht gefunden.');
            header('Location: index.php?action=admin.users');
            exit;
        }

        if (!User::updateRole($userId, $newRole)) {
            setFlash('error', 'Ungültige Rolle.');
            header('Location: index.php?action=admin.users');
            exit;
        }

        logAction('ROLE_CHANGE', "target=$userId ({$target['username']}) new_role=$newRole");
        setFlash('success', 'Rolle von „' . htmlspecialchars($target['username']) . '" auf „' . htmlspecialchars($newRole) . '" geändert.');
        header('Location: index.php?action=admin.users');
        exit;
    }

    // =========================================================================
    //  Nutzer löschen (mit Selbstschutz, CASCADE auf Topics/Posts)
    // =========================================================================

    public static function deleteUser(): void
    {
        requireRole('admin');

        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Ungültiges Formular-Token.');
            logAction('CSRF_FAIL', 'action=admin.deleteUser');
            header('Location: index.php?action=admin.users');
            exit;
        }

        $userId = (int) ($_POST['user_id'] ?? 0);

        if ($userId === currentUser()['id']) {
            setFlash('error', 'Das eigene Konto kann nicht gelöscht werden.');
            header('Location: index.php?action=admin.users');
            exit;
        }

        $target = User::getById($userId);
        if (!$target) {
            setFlash('error', 'Nutzer nicht gefunden.');
            header('Location: index.php?action=admin.users');
            exit;
        }

        User::delete($userId);
        logAction('USER_DELETE', "target=$userId ({$target['username']})");
        setFlash('success', 'Nutzer „' . htmlspecialchars($target['username']) . '" und alle zugehörigen Inhalte gelöscht.');
        header('Location: index.php?action=admin.users');
        exit;
    }
}
