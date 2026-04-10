# Artefakte-Checkliste — Phase 19

Alle Artefakte für den Projektbericht DLBITPEWP01-01.

---

## S — Screenshots (`docs/screenshots/`)

| # | Datei | Inhalt | Bericht-Ziel |
|---|-------|--------|--------------|
| S1 | `01_login.png` | Login-Formular (leer, ausgeloggt) | B.4.4 |
| S2 | `02_register_validierung.png` | Registrierung mit PHP-Validierungsfehlern (Passwort zu kurz + Passwörter stimmen nicht überein) | B.4.4 |
| S3 | `03_forumuebersicht.png` | Themenübersicht mit 8 Themen, Beitragszähler, Suche (eingeloggt als alice) | B.4.4 |
| S4 | `04_thread_ansicht.png` | Thread mit mehreren Beiträgen + Antwortformular (eingeloggt als alice) | B.4.4 |
| S5 | `05_admin_panel.png` | Nutzerverwaltung mit Rollen-Dropdowns, Selbstschutz bei eigenem Konto sichtbar (eingeloggt als admin) | B.4.4 |
| S6 | `06_flash_messages.png` | Collage: error-Flash (Ungültige Anmeldedaten) + success-Flash (Willkommen, alice!) | B.4.4 |

---

## C — Code-Snippets (Anhang)

| # | Datei | Inhalt |
|---|-------|--------|
| C1 | `database/schema.sql` | Datenbankschema: 3 Tabellen, FK, CHECK, CASCADE |
| C2 | `public/index.php` | Front-Controller / Router, zentraler Einstiegspunkt |
| C3 | `app/models/Topic.php` → `getAll()` | Pagination + Suche in einem Query, Prepared Statements |
| C4 | `app/controllers/AuthController.php` → `login()` | `password_verify`, `session_regenerate_id`, CSRF-Prüfung |
| C5 | `helpers/session.php` → `generateCsrfToken()` + `validateCsrfToken()` | Token-Generierung (`random_bytes`), Timing-Schutz (`hash_equals`) |
| C6 | `app/views/layout/pagination.php` | DRY-Prinzip, ARIA-Attribute, Wiederverwendbarkeit |

---

## D — Diagramme

| # | Diagramm | Speicherort | Bericht-Ziel |
|---|----------|-------------|--------------|
| D1 | ER-Diagramm | `Technische_Umsetzung/ER-Diagramm/` | B.2.2 |
| D2 | Architekturdiagramm | `Technische_Umsetzung/D2_Architekturdiagramm.png` | B.3.4 |
| D3 | 4 UI-Mockups | `Technische_Umsetzung/Mock-ups/` | B.1.2 |

---

## T — Testdokumente

| # | Artefakt | Quelle | Bericht-Ziel |
|---|----------|--------|--------------|
| T1 | Funktionstesttabelle (39 automatisierte Tests + manuelle Browser-Tests) | `database/security_audit.php` + manuelle Browser-Tests | B.5 |
| T2 | Sicherheits-Audit-Ergebnis (39 PASSED, 0 FAILED) | `php database/security_audit.php` | B.4.2 |
| T3 | Performancetest-Ergebnis (alle < 200 ms) | `php database/perftest.php` | B.5 |

---

## U — Usability

| # | Artefakt | Inhalt | Bericht-Ziel |
|---|----------|--------|--------------|
| U1 | Usability-Einschätzung | Minimal-Überraschungs-Prinzip, Validierungsfeedback, RBAC-Navigation, Responsivität, WCAG AA | B.4.4 |

---

## R — Referenz-Screenshots (aus früheren Phasen)

| # | Datei | Inhalt | Bericht-Ziel |
|---|-------|--------|--------------|
| R1 | `Technische_Umsetzung/Screenshots/Bericht B.3.3_Blueprint_Phase_6_Schritt7.png` | XAMPP-Konfiguration (Apache-Start) | B.3.3 |

---

## Usability-Einschätzung (Vorlage für B.4.4)

Die Benutzeroberfläche folgt dem Prinzip der minimalen Überraschung: Navigation,
Formulare und Feedback-Mechanismen (Flash-Messages) verhalten sich konsistent über
alle Ansichten hinweg. Validierungsfehler werden gesammelt und gemeinsam angezeigt,
sodass der Nutzer nicht pro Fehler einzeln korrigieren muss. Die rollenbasierte
Navigation passt die sichtbaren Elemente dynamisch an (z. B. Admin-Link nur für
Administratoren, Bearbeiten/Löschen nur für berechtigte Nutzer), wodurch
nicht-autorisierte Aktionen gar nicht erst angeboten werden. Das responsive Layout
gewährleistet die Nutzbarkeit auf Bildschirmbreiten ab 320 px; die Barrierefreiheit
wird durch sichtbare Fokus-Indikatoren, WCAG-AA-konforme Kontrastverhältnisse und
semantisches HTML5-Markup sichergestellt.
