# Webforum MVP

Webbasiertes Diskussionsforum als Minimum Viable Product (MVP), entwickelt im Rahmen des Moduls
DLBITPEWP01-01 (Einstieg in die Web-Programmierung) an der IU Internationalen Hochschule.

## Voraussetzungen

- XAMPP >= 8.x (Apache + PHP)
- PHP >= 8.0 mit `pdo_sqlite` und `sqlite3`
- Git
- Aktueller Browser (Chrome, Firefox, Edge oder Safari)

## Installation

```bash
git clone https://github.com/RiederIU/forum-mvp.git C:/xampp/htdocs/forum
cd C:/xampp/htdocs/forum
php database/init.php       # Datenbank und Admin-Account anlegen
php database/seed.php       # Testdaten laden (5 Nutzende, 8 Themen)
```

Anschließend Apache in XAMPP starten und `http://localhost/forum/public/` aufrufen.

## Test-Zugangsdaten

| Benutzername | E-Mail              | Passwort  | Rolle     |
|--------------|---------------------|-----------|-----------|
| admin        | admin@forum.local   | admin123  | Admin     |
| moderator    | mod@forum.local     | test1234  | Moderator |
| alice        | alice@forum.local   | test1234  | User      |
| bob          | bob@forum.local     | test1234  | User      |
| charlie      | charlie@forum.local | test1234  | User      |

Die Zugangsdaten gelten ausschließlich für die lokale Entwicklungsumgebung.

## Projektstruktur

```
forum/
├── public/
│   ├── index.php                Front-Controller
│   └── css/style.css
├── app/
│   ├── controllers/             Auth, Topic, Post, Admin
│   ├── models/                  User, Topic, Post
│   └── views/                   Layout, Auth, Topics, Posts, Admin
├── config/
│   ├── database.php             PHP Data Objects (PDO), Singleton
│   └── app.php                  Anwendungskonstanten
├── database/
│   ├── schema.sql               Tabellendefinitionen
│   ├── init.php                 DB-Initialisierung
│   ├── seed.php                 Testdaten
│   ├── perftest.php             Performancetest
│   └── security_audit.php       Sicherheitstest (39 Pruefungen)
├── helpers/
│   ├── session.php              Session, Flash-Messages, CSRF-Schutz
│   ├── auth.php                 Login-Pruefung, Rollenbasierte Zugriffskontrolle
│   └── logging.php              Audit-Logging
├── .gitignore
└── README.md
```

## Technologie-Stack

| Schicht     | Technologie       |
|-------------|-------------------|
| Backend     | PHP 8 (ohne Framework) |
| Frontend    | HTML5, CSS3, JavaScript |
| Datenbank   | SQLite            |
| Architektur | MVC (Model-View-Controller) |
| Webserver   | Apache via XAMPP   |

## Funktionsumfang

- Registrierung, Login und Logout mit bcrypt-Hashing
- Erstellen, Lesen, Bearbeiten und Loeschen von Themen und Beitraegen
- Rollenbasierte Zugriffskontrolle (User, Moderator, Admin)
- Admin-Bereich mit Nutzerverwaltung
- Cross-Site-Request-Forgery-Schutz (CSRF), Prepared Statements, Output-Encoding gegen Cross-Site-Scripting (XSS)
- Volltextsuche ueber Titel und Beitragsinhalte
- Seitennavigation mit Accessible Rich Internet Applications (ARIA)-Attributen
- Logging sicherheitsrelevanter Aktionen

## Tests ausfuehren

```bash
php database/security_audit.php    # 39 automatisierte Sicherheitspruefungen
php database/perftest.php          # Lasttest mit 500 Themen und 2500 Beitraegen
```
