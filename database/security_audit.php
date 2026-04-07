<?php
/**
 * Automatisiertes Sicherheits-Audit.
 *
 * Prüft die korrekte Implementierung aller Sicherheitsmaßnahmen
 * auf Code-Ebene, ohne den laufenden Webserver zu benötigen.
 * Ergänzend dazu sind manuelle Browser-Tests erforderlich
 * (siehe Checkliste in Schritt 2).
 *
 * Ausführung: php database/security_audit.php
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../helpers/session.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/logging.php';

startSession();

$passed = 0;
$failed = 0;

function audit(string $label, bool $condition, string $detail = ''): void
{
    global $passed, $failed;
    if ($condition) {
        echo "  [PASS] $label\n";
        $passed++;
    } else {
        echo "  [FAIL] $label" . ($detail ? " — $detail" : '') . "\n";
        $failed++;
    }
}

// =========================================================================
//  1. Prepared Statements (SQL-Injection-Schutz)
// =========================================================================
echo "\n=== 1. SQL-Injection-Schutz ===\n";

$db = getDB();

$maliciousInputs = [
    "' OR 1=1 --",
    "'; DROP TABLE users; --",
    "\" OR \"\"=\"",
    "1; DELETE FROM posts WHERE 1=1"
];

foreach ($maliciousInputs as $input) {
    try {
        $stmt = $db->prepare('SELECT * FROM users WHERE username = :u');
        $stmt->execute([':u' => $input]);
        $result = $stmt->fetch();
        audit(
            "Prepared Statement blockt: " . substr($input, 0, 25),
            $result === false,
            'Unerwarteter Treffer — Injection möglich!'
        );
    } catch (Exception $e) {
        audit("Prepared Statement bei: " . substr($input, 0, 25), false, $e->getMessage());
    }
}

$stmt = $db->prepare(
    "SELECT COUNT(*) FROM users WHERE username LIKE :s"
);
$stmt->execute([':s' => "%' OR '1'='1%"]);
audit(
    'LIKE-Suche mit Injection-Payload liefert 0 Treffer',
    (int) $stmt->fetchColumn() === 0
);

// =========================================================================
//  2. XSS-Schutz (Output-Encoding)
// =========================================================================
echo "\n=== 2. XSS-Schutz (hsc-Funktion) ===\n";

// header.php definiert hsc(), gibt aber auch HTML aus — Ausgabe unterdrücken
ob_start();
require_once __DIR__ . '/../app/views/layout/header.php';
ob_end_clean();

$xssPayloads = [
    '<script>alert(1)</script>'          => '&lt;script&gt;alert(1)&lt;/script&gt;',
    '<img src=x onerror=alert(1)>'       => '&lt;img src=x onerror=alert(1)&gt;',
    '"><svg onload=alert(1)>'            => '&quot;&gt;&lt;svg onload=alert(1)&gt;',
    "'; alert('xss'); //"                => "&#039;; alert(&#039;xss&#039;); //"
];

foreach ($xssPayloads as $input => $expected) {
    $encoded = hsc($input);
    audit(
        'hsc() escaped: ' . substr($input, 0, 30),
        $encoded === $expected,
        "Erwartet: $expected, Erhalten: $encoded"
    );
}

audit(
    'hsc() verwendet ENT_QUOTES + UTF-8',
    hsc("Test'\"<>") === "Test&#039;&quot;&lt;&gt;"
);

// =========================================================================
//  3. Passwort-Hashing
// =========================================================================
echo "\n=== 3. Passwort-Hashing ===\n";

$stmt = $db->query("SELECT password_hash FROM users WHERE username = 'admin'");
$hash = $stmt->fetchColumn();

audit(
    'Admin-Passwort ist bcrypt-Hash',
    str_starts_with($hash, '$2y$')
);
audit(
    'Klartext-Passwort nicht gespeichert',
    $hash !== 'admin123'
);
audit(
    'password_verify() funktioniert korrekt',
    password_verify('admin123', $hash)
);
audit(
    'Falsches Passwort wird abgelehnt',
    !password_verify('wrongpassword', $hash)
);

// =========================================================================
//  4. CSRF-Token
// =========================================================================
echo "\n=== 4. CSRF-Token ===\n";

$token = generateCsrfToken();
audit('Token hat 64 Hex-Zeichen (256 Bit)', strlen($token) === 64);
audit('Token besteht nur aus Hex-Zeichen', ctype_xdigit($token));
audit('Gültiges Token wird akzeptiert', validateCsrfToken($token));
audit('Leeres Token wird abgelehnt', !validateCsrfToken(''));
audit('Falsches Token wird abgelehnt', !validateCsrfToken('abc123'));

/**
 * Timing-Angriff-Resistenz: hash_equals() vergleicht in konstanter
 * Zeit — validateCsrfToken() nutzt diese Funktion intern.
 * Ein automatischer Test hierfür ist nicht aussagekräftig (Jitter
 * überlagert den Effekt), daher wird die Implementierung per
 * Code-Review verifiziert (siehe Checkliste Punkt 4c).
 */
audit(
    'Token ist session-gebunden',
    isset($_SESSION['csrf_token']) && $_SESSION['csrf_token'] === $token
);

// =========================================================================
//  5. Session-Sicherheit
// =========================================================================
echo "\n=== 5. Session-Konfiguration ===\n";

audit(
    'Session-Cookie ist HttpOnly',
    (bool) ini_get('session.cookie_httponly')
);
audit(
    'Session ist aktiv',
    session_status() === PHP_SESSION_ACTIVE
);

// =========================================================================
//  6. Foreign-Key-Enforcement
// =========================================================================
echo "\n=== 6. SQLite Foreign Keys ===\n";

$fkStatus = $db->query('PRAGMA foreign_keys')->fetchColumn();
audit('PRAGMA foreign_keys = ON', (int) $fkStatus === 1);

// =========================================================================
//  7. Rollenprüfung (RBAC-Hierarchie)
// =========================================================================
echo "\n=== 7. RBAC-Hierarchie ===\n";

$_SESSION['user'] = ['id' => 999, 'username' => 'test', 'email' => 'test@test.de', 'role' => 'user'];
audit('User hat Rolle user', hasRole('user'));
audit('User hat NICHT Rolle moderator', !hasRole('moderator'));
audit('User hat NICHT Rolle admin', !hasRole('admin'));

$_SESSION['user']['role'] = 'moderator';
audit('Moderator hat Rolle user (Hierarchie)', hasRole('user'));
audit('Moderator hat Rolle moderator', hasRole('moderator'));
audit('Moderator hat NICHT Rolle admin', !hasRole('admin'));

$_SESSION['user']['role'] = 'admin';
audit('Admin hat Rolle user (Hierarchie)', hasRole('user'));
audit('Admin hat Rolle moderator (Hierarchie)', hasRole('moderator'));
audit('Admin hat Rolle admin', hasRole('admin'));

unset($_SESSION['user']);
audit('Kein User → hasRole() gibt false', !hasRole('user'));

// =========================================================================
//  8. Logging
// =========================================================================
echo "\n=== 8. Logging-System ===\n";

$testMarker = 'AUDIT_TEST_' . time();
logAction($testMarker, 'security_audit');

$logContent = file_get_contents(LOG_FILE);
audit('Logdatei existiert und ist lesbar', $logContent !== false);
audit('Audit-Eintrag wurde geschrieben', str_contains($logContent, $testMarker));
audit('Logeintrag enthält Timestamp-Format', (bool) preg_match('/\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\]/', $logContent));
audit('Logeintrag enthält IP-Feld', str_contains($logContent, 'ip='));

// =========================================================================
//  9. Datenbank-Constraint-Integrität
// =========================================================================
echo "\n=== 9. Schema-Constraints ===\n";

try {
    $db->exec("INSERT INTO users (username, email, password_hash, role) VALUES ('_audit_test', '_audit@test.de', 'hash', 'invalidrole')");
    audit('CHECK-Constraint auf role blockiert ungültige Werte', false, 'Insert hätte fehlschlagen müssen');
    $db->exec("DELETE FROM users WHERE username = '_audit_test'");
} catch (Exception $e) {
    audit('CHECK-Constraint auf role blockiert ungültige Werte', true);
}

try {
    $db->exec("INSERT INTO users (username, email, password_hash) VALUES ('_audit_unique1', 'duplicate@test.de', 'hash')");
    $db->exec("INSERT INTO users (username, email, password_hash) VALUES ('_audit_unique2', 'duplicate@test.de', 'hash')");
    audit('UNIQUE-Constraint auf email', false, 'Duplikat hätte fehlschlagen müssen');
    $db->exec("DELETE FROM users WHERE username LIKE '_audit_%'");
} catch (Exception $e) {
    audit('UNIQUE-Constraint auf email blockiert Duplikate', true);
    $db->exec("DELETE FROM users WHERE username LIKE '_audit_%'");
}

// =========================================================================
//  Zusammenfassung
// =========================================================================
echo "\n" . str_repeat('=', 50) . "\n";
echo "Ergebnis: $passed PASSED, $failed FAILED\n";
echo str_repeat('=', 50) . "\n\n";

if ($failed > 0) {
    echo "⚠️  $failed Test(s) fehlgeschlagen — bitte prüfen!\n";
} else {
    echo "✅ Alle Sicherheitsprüfungen bestanden.\n";
}
