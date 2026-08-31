# SC Zauner Group Kronstorf – Songcontest-Webseite

## Umsetzungsstatus

Alle Phasen A0–E4 sind implementiert (Grundgerüst, Admin-Bereich, Voting, Danksagungs-Overlay,
Archiv, Beamer-Auszählung mit schrittweiser Enthüllung, Text-Management, Impressum/Datenschutz).
Ein automatisierter End-to-End-Test (Login, CSV-Import inkl. Fehlerfällen, Punktevergabe,
Geräte-Token-Sperre, Beamer-Enthüllung vor/zurück, CSRF-Schutz, Zugriffsschutz) läuft mit
37 von 37 bestandenen Prüfungen gegen einen lokalen PHP-Server.

**Lokal testen:**
1. `php db/init.php` einmalig ausführen (legt `db/kronstorf.sqlite` an, Admin-Login: `admin` / `Kronstorf#2016`).
2. `php -S localhost:8000` im Projektordner starten.
3. `http://localhost:8000/` sowie `http://localhost:8000/admin-x7k2p/` aufrufen.

**Wichtiger Hinweis für den Echtbetrieb:** Die `.htaccess`-Dateien (schützen `db/`, `config/`,
`includes/`, hochgeladene Sponsorenlogos vor PHP-Ausführung) funktionieren nur auf **Apache**-Hosting.
Nach dem Go-Live unbedingt prüfen, dass `https://DEINE-DOMAIN/db/kronstorf.sqlite` einen 403-Fehler
liefert – falls nicht (z. B. bei Nginx-Hosting), muss der Hosting-Anbieter kontaktiert werden.

**Bekannte bewusste Scope-Entscheidung:** Das Text-Management-System deckt die inhaltlichen Texte
(Start-, Voting-, Danke-, Archiv-Texte sowie Teile von Impressum/Datenschutz) ab. Rein strukturelle
Texte (Impressum-Überschriften, Button-Beschriftungen) sind aktuell fix im Code hinterlegt – falls
gewünscht, kann das auf Wunsch erweitert werden.

**Nachträgliche Verfeinerungen (Stand 30.08.2026):**
- Navigation: „Interpreten" ist kein eigener Menüpunkt mehr, sondern nur noch über die jeweilige Voting-Version auf der Seite „Voting-Versionen" erreichbar.
- Beamer-Enthüllung: Interpreten mit gleicher Punktezahl werden immer gemeinsam enthüllt; Platz 1 wird beim Enthüllen immer gemeinsam mit Platz 2 aufgedeckt.
- Beamer-Darstellung: Balken zeigen keinen Text mehr (Interpreten-Name steht daneben statt darin), anonymisierte Balken sind dunkelgrau, enthüllte rot; der Name von Platz 1 erscheint in Gold und deutlich größer, Platz 2 etwas größer als der Rest; alle Balken-Tracks sind unabhängig vom Namen einheitlich lang.
- Voting-Versionen: neue „Löschen"-Funktion inkl. kaskadierendem Löschen aller zugehörigen Interpreten und Stimmen (für bereits geschlossene bzw. noch nicht geöffnete Versionen, zum Aufräumen alter Testdaten).
- Danksagungs-Overlay: Sponsorenlogos im Sponsorenkranz vergrößert (100px statt ursprünglich 60px).
- Projekt ist an das GitHub-Repository https://github.com/I3uFo/ScKronstorf.at angebunden; die Laufzeit-Datenbank (`db/kronstorf.sqlite`) ist bewusst über `.gitignore` ausgeschlossen, da das Repository öffentlich ist.

**Nachträgliche Verfeinerungen (Stand 31.08.2026):**
- Vereinsname durchgängig auf „SC Zauner Group Kronstorf" aktualisiert (Titel, Header, Footer, Impressum, Datenschutz, Admin-Bereich, Standardtexte).
- Design-System des Vereinsauftritts übernommen: Schriftart Archivo (lokal selbst gehostet, kein CDN), warme Off-White/Rot-Orange-Farbpalette über zentrale CSS-Variablen, scharfkantige Buttons/Karten statt abgerundeter Ecken.
- Dark Mode für die gesamte Seite (öffentlich und Admin-Bereich): folgt standardmäßig der Systemeinstellung, per Umschalt-Button im Header manuell wählbar und dauerhaft gespeichert (`localStorage`); technisch über Bootstraps `data-bs-theme` umgesetzt.
- Startseite: „Save the Date"-Ankündigung (Termin/Ort der nächsten Veranstaltung) ergänzt, über das Text-Management-System bearbeitbar, groß und zentriert direkt über dem „Jetzt abstimmen"-Button.
- Beamer-Ansicht: Interpreten-Name wird bei Bedarf zweizeilig umgebrochen statt mit „…" abgeschnitten.
- Diverse Dark-Mode-Korrekturen: Admin-Menübreite bleibt jetzt auf allen Unterseiten (z. B. Sponsoren) konstant; zuvor im Dunkelmodus unsichtbare Buttons „Beamer-Ansicht öffnen"/„Beamer steuern" sind jetzt theme-fähig; „Abmelden"-Link im Admin-Menü an den unteren Rand verankert.


Projekt-Roadmap für die Vereinswebseite mit Eurovision-Style Voting, versteckter Adminverwaltung und Beamer-Live-Auszählung.

**Stil:** Modern, elegant, userfreundlich, übersichtlich
**Architektur:** Bootstrap (mobile responsive)
**Techstack:** HTML5, PHP, CSS3, SQLite

> Grundsatz für die Umsetzung: Zum Abschluss jedes Punktes wird auf einwandfreie Funktion getestet, bevor der nächste Punkt begonnen wird.

---

## Kernregeln des Votings (nicht verhandelbar)

1. **Ein Gerät, eine Stimme.** Jeder Geräte-Token darf pro Voting-Version genau einmal final absenden. Serverseitig geprüft, nicht nur im Browser.
2. **Kein Interpret doppelt.** Innerhalb einer Stimmabgabe darf derselbe Interpret nicht zwei Plätzen zugewiesen werden.
3. **Punkteschema (Platz 1–11):** 12, 10, 9, 8, 7, 6, 5, 4, 3, 2, 1
4. **Kein Zurück nach dem Absenden.** Nach dem Bestätigen erscheint das Danksagungsoverlay, eine erneute Stimmabgabe vom selben Token ist ausgeschlossen.
5. **Voting ist versioniert.** Jede Songcontest-Ausgabe läuft als eigene Voting-Version mit eigenen Interpreten, Stimmen und Ergebnissen (wiederholt sich jährlich).
6. **Beamer-Enthüllung ist steuerbar.** Von Platz 11 aufwärts bis Platz 1, Schritt für Schritt – und ebenso Schritt für Schritt rückgängig machbar.

---

## Datengrundlage

| Feld | Wert |
|---|---|
| Verein | SC Zauner Group Kronstorf |
| ZVR-Zahl | 168556429 |
| Adresse | Hargelsbergerstr. 4, 4484 Kronstorf |
| E-Mail | office@sckronstorf.at |
| Obmann | Stefan Frühwirth |
| Logo | https://vereine.oefb.at/vereine3/images/834733022602002384_bcc04fb463a96e5ad864-1,0-200x200.png |
| Funktionärsdaten | https://vereine.oefb.at/ScKronstorf/Verein/Funktionaere/ |
| Vorlage Danksagungsoverlay | https://www.songcontest-kronstorf.at/landingpage/ |
| Vorlage Impressum | https://www.wamperl-pass.at/impressum.html |
| Vorlage Farbschema & Sponsoren | https://www.songcontest-kronstorf.at/ |
| Vorlage Voting-Daten (Interpreten) | https://www.songcontest-kronstorf.at/Votingpage |

**Hinweis Voting-Daten:** Die Interpreten-, Songtitel- und Reihenfolge-Daten auf `https://www.songcontest-kronstorf.at/Votingpage` dienen als reale Grundlage für die Testdatei in Phase B1 (Interpreten-Import) und können bei Bedarf 1:1 übernommen werden.

---

## Bauplan – 17 Phasen in 5 Stufen

### Stufe A – Fundament (Setup, Datenbank, Grundlayout)

**A0 – Projekt-Setup & Ordnerstruktur**
- Ziel: Saubere Projektstruktur (`public/`, `admin/`, `includes/`, `assets/`, `db/`, `config/`) mit PHP-PDO-Verbindung zu SQLite.
- Aufgaben:
  - Ordnerstruktur & `config.php` mit DB-Pfad anlegen
  - PDO-Verbindung zu SQLite mit Fehlerbehandlung einrichten
  - Bootstrap 5 lokal einbinden (kein CDN, DSGVO-konform)
  - Basis-Template mit Header/Footer-Include erstellen
- Test: Startseite lädt fehlerfrei, DB-Verbindung steht, Bootstrap-Klassen greifen sichtbar.

**A1 – Datenbank-Design**
- Ziel: Alle Tabellen anlegen: `interpreten`, `device_tokens`, `votes`, `sponsoren`, `voting_versionen`, `seiten_texte`, `admin_users`.
- Aufgaben:
  - Schema inkl. Fremdschlüssel auf `voting_versionen` definieren
  - Migrations-/Init-Skript für frische SQLite-Datei schreiben
  - Testdaten-Fixture für Entwicklung einspielen
- Test: Init-Skript einmal laufen lassen, alle Tabellen und Constraints per Testabfrage verifizieren.

**A2 – Header, Footer & Grundlayout**
- Ziel: Wiederverwendbares Layout mit rundem Logo links im Header und Vereinsnamen, responsivem Footer.
- Aufgaben:
  - Header: Logo kreisrund (border-radius 50%), Name daneben, Navigation
  - Footer mit Platzhaltern für Impressum & Datenschutz verlinken
  - Vereinsfarben & Typografie im Bootstrap-Theme anpassen
- Test: Header/Footer auf Mobile, Tablet und Desktop prüfen, Logo bleibt rund und scharf in allen Breiten.

---

### Stufe B – Admin-Kern (Login, Import, Sponsoren, Versionierung)

**B0 – Admin-Login & versteckter Zugriff**
- Ziel: Nicht verlinkte Admin-URL mit Login-Formular, Passwort-Hash und Session-Schutz.
- Aufgaben:
  - Login-Formular mit `password_hash()` / `password_verify()`
  - Session-Handling inkl. Timeout und Logout
  - Zugriffsschutz für alle `admin/*`-Seiten (Redirect ohne Session)
- Test: Zugriff auf Admin-Seite ohne Login wird geblockt, korrektes Login funktioniert, falsches Passwort wird abgewiesen.

**B1 – Interpreten-Import (CSV/TXT)**
- Ziel: Admin importiert Interpret, Songtitel, Originalinterpret und Auftrittsreihenfolge aus einer Datei.
- Aufgaben:
  - Upload-Formular mit Parser für CSV/TXT (Trennzeichen, UTF-8)
  - Validierung: Pflichtfelder, doppelte Reihenfolge, doppelte Namen
  - Vorschau vor dem endgültigen Import mit Fehleranzeige je Zeile
  - Testdatei (`interpreten_test.csv`) erstellen, Basis: reale Daten von https://www.songcontest-kronstorf.at/Votingpage
- Test: Import mit gültiger Testdatei sowie mit absichtlich fehlerhafter Datei durchspielen, Fehler werden klar angezeigt.

**B2 – Sponsoren-Verwaltung**
- Ziel: Sponsoren mit Logo, Name, Link und Reihenfolge über den Admin-Bereich pflegen.
- Aufgaben:
  - CRUD-Formulare (Anlegen, Bearbeiten, Löschen)
  - Logo-Upload mit Format-/Größenvalidierung
  - Reihenfolge festlegen (für Anzeige im Danksagungsoverlay)
  - Bestehende Sponsoren von https://www.songcontest-kronstorf.at/ als Erstbefüllung übernehmen
- Test: Sponsor anlegen, bearbeiten, löschen und Reihenfolge ändern, Anzeige im Frontend jedes Mal prüfen.

**B3 – Voting-Versionierung**
- Ziel: Jede Songcontest-Ausgabe als eigene Voting-Version mit Status Vorbereitung / Offen / Geschlossen führen.
- Aufgaben:
  - Neue Voting-Version anlegen (Name/Jahr, zugehörige Interpreten)
  - Statuswechsel-Buttons: Voting öffnen, Voting schließen
  - Archivübersicht früherer Voting-Versionen mit Ergebnissen
- Test: Zwei Versionen nacheinander anlegen, Statuswechsel durchspielen, alte Version bleibt unverändert einsehbar.

---

### Stufe C – Voting-Erlebnis (die Publikumsseite)

**C0 – Voting-Seite mit Geräte-Token**
- Ziel: Publikum vergibt Punkte 12–1 an die Interpreten, streng an Geräte-Token und Eindeutigkeit gebunden.
- Aufgaben:
  - Geräte-Token beim ersten Aufruf erzeugen (Cookie + serverseitig gespeichert)
  - Interpreten in Auftrittsreihenfolge mit Platzvergabe (Drag&Drop oder Auswahl) anzeigen
  - Client-seitig: kein Interpret zweimal wählbar, erst ab vollständiger Reihung Absenden aktiv
  - Server-seitig: Token-Prüfung, Vollständigkeits- und Duplikatsprüfung vor dem Insert
- Test: Vollständige Stimmabgabe durchführen, danach zweiten Versuch vom selben Gerät erzwingen (muss blockiert werden), Test auf echtem Smartphone.

**C1 – Danksagungs-Overlay**
- Ziel: Nach dem Absenden erscheint ein nicht schließbarer Danksagungs-Screen mit Sponsorenlogos rund um den Text.
- Aufgaben:
  - Overlay-Layout nach Vorbild songcontest-kronstorf.at Landingpage
  - Sponsorenlogos aus Verwaltung kreisförmig/rahmenartig um den Text anordnen
  - Danksagungstext aus dem Text-Management-System laden
- Test: Overlay nach Absenden prüfen, Seiten-Reload verhindert erneutes Voting, Sponsorenkranz auf Mobile lesbar.

**C2 – Archiv-Seite (vorbereitet, unsichtbar)**
- Ziel: YouTube-Archiv-Seite technisch fertigstellen, aber ohne Navigationslink veröffentlichen.
- Aufgaben:
  - Datenmodell für Video-Einträge (Titel, YouTube-ID, Jahr) anlegen
  - Seite mit YouTube-Embed-Grid bauen, Platzhalterdaten einspielen
  - Seite nicht in Navigation verlinken, per `noindex` absichern
- Test: Seite ist per direkter URL erreichbar, aber in keiner Navigation sichtbar oder von Suchmaschinen indexiert.

---

### Stufe D – Live-Auszählung (die Beamer-Seite)

**D0 – Beamer-Seite & anonyme Auszählung**
- Ziel: Nach Voting-Ende zeigt eine eigene Beamer-Seite die Gesamtpunkte je Interpret als anonymisierte Balken.
- Aufgaben:
  - Eigene, admin-geschützte Route `/beamer` anlegen
  - Punktesumme je Interpret aus `votes` berechnen
  - Balkendiagramm ohne Namen (nur verdeckte Balken/Nummern) darstellen
  - Live-Aktualisierung per Polling, Zustand in der DB je Voting-Version speichern
- Test: Nach Testvoting Auszählung mit erwarteten Summen von Hand gegenrechnen, zweites Browserfenster zeigt denselben Stand.

**D1 – Schrittweise Enthüllung & Undo**
- Ziel: Admin gibt Interpreten von Platz 11 bis Platz 1 einzeln zur Enthüllung frei, mit Möglichkeit zum Zurücknehmen.
- Aufgaben:
  - Admin-Steuerpult: „Nächsten Platz enthüllen" und „Enthüllung zurücknehmen"
  - Freigabestand pro Voting-Version persistieren (überlebt Neuladen)
  - Beamer-Seite reagiert live auf jede Freigabe/Rücknahme
- Test: Kompletten Ablauf von Platz 11 bis Platz 1 enthüllen, anschließend mehrfach schrittweise zurücknehmen und erneut vorwärts gehen.

---

### Stufe E – Politur & Abnahme (Texte, Recht, Sicherheit, Generalprobe)

**E0 – Text-Management-System**
- Ziel: Sämtliche Fließtexte aller Seiten liegen in der Datenbank und sind im Admin-Bereich editierbar.
- Aufgaben:
  - Tabelle `seiten_texte` nach Seite/Textschlüssel strukturieren
  - Admin-Oberfläche zum Bearbeiten je Seite gruppiert
  - Alle Templates auf DB-Texte statt Hardcoding umstellen
- Test: Text auf jeder Seite einmal ändern und live im Frontend kontrollieren, inklusive Danksagungstext.

**E1 – Impressum & Datenschutzerklärung**
- Ziel: Rechtstexte auf Basis der Vereins- und Funktionärsdaten sowie der Vorlage wamperl-pass.at erstellen.
- Aufgaben:
  - Impressum mit ZVR-Zahl, Adresse, Obmann, Kontakt befüllen
  - Datenschutzerklärung inkl. Geräte-Token/Cookie-Hinweis verfassen
  - Footer-Links auf beide Seiten setzen
- Test: Rechtstexte auf Vollständigkeit gegen die Vorlage prüfen, Footer-Links auf allen Seiten kontrollieren.

**E2 – Design-Feinschliff & Responsiveness**
- Ziel: Bootstrap-Theme, Vereinsfarben und Mobile-Verhalten über alle Seiten konsistent verfeinern.
- Aufgaben:
  - Farb- und Typografie-Feinschliff passend zum bestehenden Auftritt von https://www.songcontest-kronstorf.at/
  - Voting- und Beamer-Seite gezielt auf kleinen Bildschirmen testen
  - Cross-Browser-Check (Chrome, Firefox, Safari, Edge)
- Test: Vollständiger Durchklick auf Smartphone, Tablet und Desktop in mindestens zwei Browsern ohne Layoutfehler.

**E3 – Sicherheit & Härtung**
- Ziel: Voting- und Admin-Formulare gegen die üblichen Angriffsvektoren absichern.
- Aufgaben:
  - Durchgängig Prepared Statements gegen SQL-Injection
  - CSRF-Token auf Voting- und Admin-Formularen
  - Ausgaben konsequent escapen (XSS-Schutz), Rate-Limit für Admin-Login
- Test: Manuelle Angriffsversuche (SQLi-Payload, XSS-Payload, Doppelvoting per Entwicklertools) müssen scheitern.

**E4 – Generalprobe / Abnahmetest**
- Ziel: Kompletter Ablauf einmal end-to-end mit Testdaten durchspielen wie an einem echten Songcontest-Abend.
- Aufgaben:
  - Interpreten importieren, Voting öffnen, mehrere Testvotings abgeben
  - Voting schließen, Beamer öffnen, Ergebnis Platz 11 bis 1 enthüllen
  - Neue Voting-Version für den nächsten Jahrgang anlegen und gegen die alte abgrenzen
- Test: Gesamtablauf ohne manuelles Eingreifen in der Datenbank einmal fehlerfrei durchlaufen lassen.

---

## Entscheidungen

1. **Admin-Zugang:** Benutzername `admin`, Erst-Passwort `Kronstorf#2016`. *(Sicherheitshinweis: Wird in Phase B0 ausschließlich als Passwort-Hash in der DB gespeichert, nie im Klartext im Code. Passwortänderung nach Erstinbetriebnahme empfohlen.)*
2. **Geräte-Token:** Genügt als alleinige Sperre pro Stimmabgabe, keine zusätzliche IP-Sperre.
3. **Hosting:** Standard-PHP-Hosting, versteckte Admin-URL ist verfügbar (`mod_rewrite`/eigener Pfad nutzbar).
4. **Farbschema:** Kein Logo-Abgleich nötig – Farbgebung orientiert sich am bestehenden Auftritt von https://www.songcontest-kronstorf.at/.
5. **Sponsoren:** Bereits real vorhanden und einsehbar auf https://www.songcontest-kronstorf.at/, werden von dort übernommen statt mit Platzhaltern zu arbeiten.

---

*Reihenfolge der Phasen entspricht der empfohlenen Bau- und Testreihenfolge, nicht dem zeitlichen Aufwand.*
