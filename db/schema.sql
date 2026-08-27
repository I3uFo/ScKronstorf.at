PRAGMA foreign_keys = ON;

CREATE TABLE IF NOT EXISTS voting_versionen (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    status TEXT NOT NULL DEFAULT 'vorbereitung' CHECK (status IN ('vorbereitung', 'offen', 'geschlossen')),
    beamer_freigabe_platz INTEGER NOT NULL DEFAULT 0,
    erstellt_am TEXT NOT NULL DEFAULT (datetime('now'))
);

CREATE TABLE IF NOT EXISTS interpreten (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    voting_version_id INTEGER NOT NULL REFERENCES voting_versionen(id) ON DELETE CASCADE,
    reihenfolge INTEGER NOT NULL,
    name TEXT NOT NULL,
    songtitel TEXT NOT NULL,
    originalinterpret TEXT NOT NULL DEFAULT '',
    UNIQUE (voting_version_id, reihenfolge)
);

CREATE TABLE IF NOT EXISTS device_tokens (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    token TEXT NOT NULL UNIQUE,
    erstellt_am TEXT NOT NULL DEFAULT (datetime('now'))
);

CREATE TABLE IF NOT EXISTS stimmabgaben (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    voting_version_id INTEGER NOT NULL REFERENCES voting_versionen(id) ON DELETE CASCADE,
    device_token_id INTEGER NOT NULL REFERENCES device_tokens(id),
    abgegeben_am TEXT NOT NULL DEFAULT (datetime('now')),
    UNIQUE (voting_version_id, device_token_id)
);

CREATE TABLE IF NOT EXISTS votes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    voting_version_id INTEGER NOT NULL REFERENCES voting_versionen(id) ON DELETE CASCADE,
    device_token_id INTEGER NOT NULL REFERENCES device_tokens(id),
    interpret_id INTEGER NOT NULL REFERENCES interpreten(id),
    punkte INTEGER NOT NULL,
    abgegeben_am TEXT NOT NULL DEFAULT (datetime('now')),
    UNIQUE (voting_version_id, device_token_id, interpret_id)
);

CREATE TABLE IF NOT EXISTS sponsoren (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    logo_datei TEXT NOT NULL,
    link TEXT NOT NULL DEFAULT '',
    reihenfolge INTEGER NOT NULL DEFAULT 0
);

CREATE TABLE IF NOT EXISTS seiten_texte (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    seite TEXT NOT NULL,
    schluessel TEXT NOT NULL,
    inhalt TEXT NOT NULL,
    UNIQUE (seite, schluessel)
);

CREATE TABLE IF NOT EXISTS admin_users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS archiv_videos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    titel TEXT NOT NULL,
    youtube_id TEXT NOT NULL,
    jahr INTEGER NOT NULL,
    reihenfolge INTEGER NOT NULL DEFAULT 0
);
