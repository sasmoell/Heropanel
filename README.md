=== Heropanel 1.4.0 ===
Contributors: Sascha Möller (DE/NRW/PB)
Tags: comics, webcomics, custom post type, media, comic collection
Requires at least: 6.0
Tested up to: 6.6
Requires PHP: 7.2
Stable tag: 1.4.0
License: GPL-3.0
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Ein Plugin, um Comics auf deiner WordPress-Seite zu verwalten und anzuzeigen.

== Beschreibung ==

Das *Heropanel Plugin* ermöglicht es dir, eine Sammlung von Comics auf deiner WordPress-Seite zu erstellen und anzuzeigen.
Es bietet eine benutzerfreundliche Oberfläche im Admin-Bereich sowie verschiedene Möglichkeiten Comics im Frontend darzustellen. Mit diesem Plugin kannst du:

* Comic-Cover-Bilder hochladen und verwalten.
* Comic-Metadaten wie Titel, Autoren, Genres uvm. 
* Limitierte Comic-Ausgaben verwalten.
* Kompakte oder detaillierte Comic-Karten sowie Tabellen im Frontend anzeigen.

Das Plugin ist einfach zu bedienen und bietet alle nötigen Funktionen, um eine Online-Comic-Bibliothek zu erstellen.
Bevor du dieses Plugin bewertest, würde ich mich sehr über eine Rückmeldung freuen. Schreibe doch bitte gerne eine E-Mail an sasmoell@t-online.de - Ich bin auf Rückmeldung angewiesen und sehr dankbar dafür.


== Installation ==

1. Lade das Plugin-Verzeichnis in dein `/wp-content/plugins/`-Verzeichnis hoch.
2. Aktiviere das Plugin über das 'Plugins'-Menü in WordPress.
3. Nach der Aktivierung findest du das "Comics" Menü in deinem WordPress-Dashboard, wo du neue Comics hinzufügen und verwalten kannst.

== Screenshots ==

Eine Dokumentation findest du auf https://heropanel.de

== Changelog ==

= 1.4.0 =

* QR-Code in Comic-Beiträgen. Es wird ein QR-Code zu jedem Comic erzeugt. Er enthält die URL zum jeweiligen Comic.
* Auto-Content: Beim Erstellen eines Comics wird der Shortcode [wp_comics_metadata] automatisch gesetzt. Er zeigt die Metadaten des Comics an und kann bei Bedarf entfernt werden.
* Es kann jetzt auch nach Serien gefiltert werden. Im Shortcode ist der Parameter "series" nun verfügbar. Er wird mit der ID der Serie erweitert. Beispiel: [wp_comics series="4"] zeigt Comics der Serie mit der ID 4 an.
* In den Comics ist nun auch Tagging möglich. Mehr Flexibilität bei der Filterung. Neuer Parameter für Shortcodes (z.B. tag="spinne")
* Tab "Desgin" umbenannten in "Layout & Design"
* Unter "Layout & Design" können jetzt die Spalten ein- oder ausgeblendet werden.


= 1.3.1 =

* Einstellungen wurden in Tabs organisiert
* Verbesserte Tabs: Die Tabs bleiben nach dem Speichern der Änderungen aktiv.
* Erfolgsmeldung nach dem Speichern der Einstellungen hinzugefügt.
* Verschiedene kleinere Bugfixes und Verbesserungen der Benutzeroberfläche.
* Neue Option: Benutzer kann zwischen Pixel und Prozent bei der Angabe der Card-Breite wählen.
* Comic-Card: Hintergrund-, Text- und Titelfarbe kann per Color-Picker eingestellt werden.
* Button für Standardeinstellungen wurde hinzugefügt.
* Verfügbare Shortcodes erweitert. Comics lassen sich jetzt auch als Tabelle ausgeben.
* HeroPanel Docs in Dokumentation verlinkt. Zeigt auf SpiderComics.de.
* Aktuelle News werden als RSS-Feed angezeigt.

= 1.3.0 =
* Erstveröffentlichung des Plugins.

== Frequently Asked Questions ==

= Wie füge ich einen neuen Comic hinzu? =
Nach der Aktivierung des Plugins findest du im WordPress-Dashboard den Menüpunkt "Comics". Dort kannst du neue Comics hinzufügen und ihre Metadaten verwalten.

= Kann ich limitierte Ausgaben meiner Comics anzeigen? =
Ja, das Plugin unterstützt limitierte Ausgaben. Diese können durch Aktivieren der entsprechenden Checkbox und Eingabe der Limitierungsdaten hinzugefügt werden.

= Ist das Plugin kompatibel mit meinem Theme? =
Das Plugin ist mit den meisten modernen WordPress-Themes kompatibel. Kleinere Anpassungen können jedoch erforderlich sein, um das Erscheinungsbild vollständig anzupassen.

== License & Copyright ==

Dieses Plugin ist Open Source und steht unter der GPL-3.0. Weitere Informationen findest du unter: [https://www.gnu.org/licenses/gpl-3.0.html](https://www.gnu.org/licenses/gpl-3.0.html).
