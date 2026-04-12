# Webforum MVP

Ein webbasiertes Diskussionsforum als Minimum Viable Product (MVP), entwickelt
im Rahmen des Moduls **DLBITPEWP01-01 — Einstieg in die Web-Programmierung**
an der IU Internationalen Hochschule.

---

## Voraussetzungen

| Komponente            | Version / Hinweis                                          |
|-----------------------|------------------------------------------------------------|
| XAMPP                 | >= 8.x (inkl. Apache + PHP)                                |
| PHP                   | >= 8.0 mit aktivierten Extensions `pdo_sqlite`, `sqlite3`  |
| Git                   | beliebige Version                                          |
| Browser               | aktueller Chrome, Firefox, Edge oder Safari                |
| DB Browser for SQLite | optional, empfohlen zur Dateninspektion                    |

---

## Installation

```bash
# 1. Repository klonen
git clone <REPOSITORY-URL> C:/xampp/htdocs/forum

# 2. In das Projektverzeichnis wechseln
cd C:/xampp/htdocs/forum

# 3. Datenbank initialisieren (erstellt forum.sqlite + Admin-Account)
php database/init.php

# 4. Testdaten laden (5 Nutzer, 8 Themen, diverse Beiträge)
php database/seed.php

# 5. Apache in XAMPP starten und Forum im Browser öffnen
#    http://localhost/forum/public/
```

---

## Test-Zugangsdaten

| Benutzername | E-Mail               | Passwort   | Rolle      |
|--------------|----------------------|------------|------------|
| admin        | admin@forum.local    | admin123   | Admin      |
| moderator    | mod@forum.local      | test1234   | Moderator  |
| alice        | alice@forum.local    | test1234   | User       |
| bob          | bob@forum.local      | test1234   | User       |
| charlie      | charlie@forum.local  | test1234   | User       |

> **Hinweis:** Diese Zugangsdaten gelten ausschließlich für die lokale
> Entwicklungsumgebung. In einer Produktivumgebung müssen individuelle,
> sichere Passwörter verwendet werden.

---

## Projektstruktur

```
forum/
├── public/                     DocumentRoot (einziger Einstiegspunkt)
│   ├── index.php               Front-Controller / Router
│   └── css/style.css           Stylesheet
├── app/
│   ├── controllers/            AuthController, TopicController,
│   │                           PostController, AdminController
│   ├── models/                 User, Topic, Post
│   └── views/
│       ├── layout/             header.php, footer.php, pagination.php
│       ├── auth/               login.php, register.php
│       ├── topics/             index.php, create.php, show.php, edit.php
│       ├── posts/              edit.php
│       └── admin/              users.php
├── config/
│   ├── database.php            PDO-Verbindung (Singleton)
│   └── app.php                 Globale Konstanten
├── database/
│   ├── schema.sql              CREATE TABLE Statements
│   ├── init.php                DB-Initialisierung
│   ├── seed.php                Testdaten-Generator
│   ├── perftest.php            Performancetest
│   └── security_audit.php      Automatisiertes Sicherheits-Audit
├── helpers/
│   ├── session.php             Session, Flash-Messages, CSRF
│   ├── auth.php                Login-Prüfung, Rollenprüfung (RBAC)
│   └── logging.php             Zentrales Logging
├── .gitignore
└── README.md
```

---

## Technologie-Stack

| Schicht       | Technologie        | Begründung                                      |
|---------------|--------------------|-------------------------------------------------|
| Backend       | PHP 8.x (vanilla)  | Serverseitig, ohne Framework-Overhead           |
| Frontend      | HTML5, CSS3, JS    | Semantisches Markup, responsives Layout         |
| Datenbank     | SQLite             | Dateibasiert, kein Server nötig, ideal für MVP  |
| Architektur   | MVC                | Trennung von Logik, Daten und Darstellung       |
| Webserver     | Apache (XAMPP)     | Standard-Entwicklungsumgebung für PHP           |
| Versionierung | Git                | Nachvollziehbare Entwicklungshistorie           |

---

## Funktionsumfang

- **Authentifizierung:** Registrierung, Login, Logout mit bcrypt-Hashing und Session-Fixation-Schutz
- **Themen:** Erstellen, Anzeigen, Bearbeiten, Löschen (CRUD)
- **Beiträge:** Antworten, Bearbeiten, Löschen mit Bump-Semantik
- **Rollenbasierte Zugriffskontrolle:** User, Moderator, Admin mit hierarchischem Berechtigungsmodell
- **Admin-Panel:** Nutzerverwaltung mit Rollenzuweisung und Löschfunktion inkl. Selbstschutz-Mechanismus
- **Sicherheit:** CSRF-Token, Prepared Statements, XSS-Schutz (Output-Encoding), HttpOnly-Session-Cookies
- **Suche:** Volltextsuche über Thementitel und Beitragsinhalte
- **Pagination:** Wiederverwendbares Partial mit ARIA-Attributen
- **Logging:** Strukturiertes Logging aller sicherheitsrelevanten Aktionen
- **Barrierefreiheit:** Fokus-Indikatoren, Kontrastverhältnisse (WCAG AA), semantisches HTML, ARIA-Landmarks

---

## Qualitätssicherung

```bash
# Sicherheits-Audit ausführen
php database/security_audit.php

# Performancetest ausführen (500 Themen, 2.500 Beiträge)
php database/perftest.php
```

---

## Betriebskonzept

### IST-Zustand (Entwicklungsumgebung)

| Aspekt     | Aktuell                   |
|------------|---------------------------|
| Server     | Lokaler Apache via XAMPP  |
| Datenbank  | SQLite (Einzeldatei)      |
| HTTPS      | Nein (localhost)          |
| Backup     | Manuell (Datei kopieren)  |
| Deployment | Kein (lokaler Betrieb)    |
| Monitoring | app.log (manuell)         |
| Nutzerzahl | < 10 (Entwicklung/Test)   |

### Skalierungspfad

| Stufe           | Nutzerzahl | Maßnahmen                                                           |
|-----------------|------------|---------------------------------------------------------------------|
| 1 — MVP         | < 10       | Keine, aktueller Stand                                              |
| 2 — Pilot       | < 50       | VPS mit HTTPS, Let's-Encrypt-Zertifikat, `session.cookie_secure=1`  |
| 3 — Wachstum    | > 100      | Migration auf MySQL/MariaDB, Reverse-Proxy, Backup-Automatisierung  |

---

## Lizenz

Dieses Projekt wurde im Rahmen einer akademischen Prüfungsleistung erstellt.
