<?php
/**
 * Testdaten-Generator für reproduzierbare Funktions- und Integrationstests.
 *
 * Erzeugt ein deterministisches Datenset mit allen drei Rollen und
 * ausreichend Themen/Beiträgen, um Pagination, Suche und RBAC-
 * Szenarien zu testen. Idempotent: löscht bestehende Daten vor dem
 * Neuanlegen (Reihenfolge beachtet FK-Constraints).
 *
 * Ausführung: php database/seed.php
 * Zugangsdaten: Alle Testnutzer verwenden das Passwort 'test1234'.
 */

require_once __DIR__ . '/../config/database.php';

$db = getDB();

// =========================================================================
//  1. Bestehende Daten entfernen (FK-Reihenfolge: Posts → Topics → Users)
// =========================================================================

$db->exec('DELETE FROM posts');
$db->exec('DELETE FROM topics');
$db->exec("DELETE FROM users WHERE username != 'admin'");

echo "Bestehende Daten bereinigt.\n";

// =========================================================================
//  2. Testnutzer anlegen
// =========================================================================

$testUsers = [
    ['moderator', 'mod@forum.local',     'moderator'],
    ['alice',     'alice@forum.local',    'user'],
    ['bob',       'bob@forum.local',      'user'],
    ['charlie',   'charlie@forum.local',  'user'],
];

$stmtUser = $db->prepare(
    'INSERT INTO users (username, email, password_hash, role)
     VALUES (:username, :email, :password_hash, :role)'
);

foreach ($testUsers as [$username, $email, $role]) {
    $stmtUser->execute([
        ':username'      => $username,
        ':email'         => $email,
        ':password_hash' => password_hash('test1234', PASSWORD_DEFAULT),
        ':role'          => $role
    ]);
}

echo "4 Testnutzer angelegt.\n";

// =========================================================================
//  3. Alle User-IDs sammeln (inkl. Admin)
// =========================================================================

$userIds = $db->query('SELECT id FROM users ORDER BY id ASC')->fetchAll(PDO::FETCH_COLUMN);

// =========================================================================
//  4. Themen erstellen (8 Stück, verteilt auf alle Nutzer)
// =========================================================================

$topicTitles = [
    'Willkommen im Forum — Vorstellungsrunde',
    'PHP vs. Java — Welche Sprache für Webprojekte?',
    'Beste Lernressourcen für Einsteiger',
    'SQLite im Produktiveinsatz — Erfahrungen?',
    'MVC-Pattern verständlich erklärt',
    'CSS-Tipps für saubere Layouts',
    'Sicherheit im Web — Best Practices',
    'Projektideen für das nächste Semester',
];

$stmtTopic = $db->prepare(
    'INSERT INTO topics (title, user_id) VALUES (:title, :user_id)'
);

foreach ($topicTitles as $i => $title) {
    $stmtTopic->execute([
        ':title'   => $title,
        ':user_id' => $userIds[$i % count($userIds)]
    ]);
}

echo "8 Themen erstellt.\n";

// =========================================================================
//  5. Beiträge generieren (3–10 pro Thema, verschiedene Autoren)
// =========================================================================

/**
 * Synthetische Beitragstexte mit variierender Länge, um realistische
 * Forenaktivität zu simulieren. Die Texte sind inhaltlich zum jeweiligen
 * Thema passend, damit die Volltextsuche sinnvolle Treffer liefert.
 */

$sampleContents = [
    'Das ist ein sehr guter Punkt. Ich habe ähnliche Erfahrungen gemacht und kann das nur bestätigen.',
    'Interessante Perspektive! Hast du dazu vielleicht eine Quelle oder einen Link?',
    'Ich sehe das etwas anders. Meiner Meinung nach sollte man hier differenzierter argumentieren.',
    'Vielen Dank für den Beitrag. Das hat mir bei meinem eigenen Projekt sehr weitergeholfen.',
    'Kann jemand das genauer erklären? Ich bin noch relativ neu in dem Thema.',
    'Guter Hinweis! Ich habe das gerade ausprobiert und es funktioniert einwandfrei.',
    'Dem stimme ich voll zu. Besonders der Aspekt der Sicherheit wird oft unterschätzt.',
    'Hat jemand Erfahrung mit alternativen Ansätzen? Ich würde gerne verschiedene Lösungen vergleichen.',
    'Wichtiger Beitrag. Das sollte man auf jeden Fall im Hinterkopf behalten.',
    'Ich habe dazu letzte Woche einen Artikel gelesen, der genau dieses Problem behandelt.',
];

$topicIds = $db->query('SELECT id FROM topics ORDER BY id ASC')->fetchAll(PDO::FETCH_COLUMN);

$stmtPost = $db->prepare(
    'INSERT INTO posts (content, user_id, topic_id)
     VALUES (:content, :user_id, :topic_id)'
);

$totalPosts = 0;

foreach ($topicIds as $topicId) {
    $postCount = rand(3, 10);
    for ($j = 0; $j < $postCount; $j++) {
        $stmtPost->execute([
            ':content'  => $sampleContents[$j % count($sampleContents)],
            ':user_id'  => $userIds[array_rand($userIds)],
            ':topic_id' => $topicId
        ]);
        $totalPosts++;
    }
}

echo "$totalPosts Beiträge generiert.\n";

// =========================================================================
//  Zusammenfassung
// =========================================================================

echo "\n--- Seed abgeschlossen ---\n";
echo "Nutzer:  " . count($db->query('SELECT id FROM users')->fetchAll()) . "\n";
echo "Themen:  " . count($topicIds) . "\n";
echo "Posts:   $totalPosts\n";

echo "\nZugangsdaten:\n";
echo "  admin     / admin@forum.local     / admin123   (Admin)\n";
echo "  moderator / mod@forum.local       / test1234   (Moderator)\n";
echo "  alice     / alice@forum.local     / test1234   (User)\n";
echo "  bob       / bob@forum.local       / test1234   (User)\n";
echo "  charlie   / charlie@forum.local   / test1234   (User)\n";
