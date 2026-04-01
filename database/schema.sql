-- =============================================================================
-- Drei Tabellen (users, topics, posts).
-- AUTOINCREMENT verhindert die Wiederverwendung gelöschter IDs.
-- CHECK auf role begrenzt die Werte auf user, moderator, admin.
-- ON DELETE CASCADE entfernt abhängige Datensätze automatisch.
-- =============================================================================

CREATE TABLE IF NOT EXISTS users (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    username        TEXT    UNIQUE NOT NULL,
    email           TEXT    UNIQUE NOT NULL,
    password_hash   TEXT    NOT NULL,
    role            TEXT    DEFAULT 'user'
                    CHECK(role IN ('user', 'moderator', 'admin')),
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS topics (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    title           TEXT    NOT NULL,
    user_id         INTEGER NOT NULL,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS posts (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    content         TEXT    NOT NULL,
    user_id         INTEGER NOT NULL,
    topic_id        INTEGER NOT NULL,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)  REFERENCES users(id)  ON DELETE CASCADE,
    FOREIGN KEY (topic_id) REFERENCES topics(id) ON DELETE CASCADE
);
