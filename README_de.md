# Foldergallery_XH

Foldergallery_XH ermöglicht die Präsentation von Bilder-Galerien auf einer
CMSimple_XH Website. Im Gegensatz zu den meisten anderen verfügbaren
Galerie-Plugins, werden die Galerien vollkommen automatisch aus den
vorhandenen Bildern im einem bestimmten Ordner generiert, so dass die
Galerien nicht im Backend vorbereitet werden müssen. Daher ist
Foldergallery_XH besonders für eine große Azahl von Bildern geeignet, und
ebenso für die Verwendung mit [Extedit_XH](https://github.com/cmb69/extedit_xh).

- [Voraussetzungen](#voraussetzungen)
- [Download](#download)
- [Installation](#installation)
- [Einstellungen](#einstellungen)
- [Verwendung](#verwendung)
- [Einschränkungen](#einschränkungen)
- [Problembehebung](#problembehebung)
- [Lizenz](#lizenz)
- [Danksagung](#danksagung)

## Voraussetzungen

Foldergallery_XH ist ein Plugin für [CMSimple_XH](https://cmsimple-xh.org/de/).
Es benötigt CMSimple_XH ≥ 1.7.0, und PHP ≥ 5.4.0 mit der GD und JSON Extension.

## Download

Das [aktuelle Release](https://github.com/cmb69/foldergallery_xh/releases/latest)
kann von Github herunter geladen werden.

## Installation

Die Installation erfolgt wie bei vielen anderen CMSimple_XH-Plugins auch. Im
[CMSimple_XH Wiki](https://wiki.cmsimple-xh.org/de/?fuer-anwender/arbeiten-mit-dem-cms/plugins)
finden sie ausführliche Hinweise.

1. **Sichern Sie die Daten auf Ihrem Server.**
1. Entpacken Sie die ZIP-Datei auf Ihrem Computer.
1. Laden Sie den gesamten Ordner `foldergallery/` auf Ihren Server in das
   `plugins/` Verzeichnis von CMSimple_XH hoch.
1. Vergeben Sie Schreibrechte für die Unterordner `cache/`, `css/`, `config/`
   und `languages/`.
1. Navigieren Sie zu `Plugins` → `Foldergallery` im Administrationsbereich,
   und prüfen Sie, ob alle Voraussetzungen für den Betrieb erfüllt sind.

## Einstellungen

Die Konfiguration des Plugins erfolgt wie bei vielen anderen
CMSimple_XH Plugins auch im Administrationsbereich der Homepage. Wählen Sie
unter `Plugins` → `Foldergallery` aus.

Sie können die Original-Einstellungen von Foldergallery_XH in der
`Konfiguration` ändern. Beim Überfahren der Hilfe-Icons mit der Maus
werden Hinweise zu den Einstellungen angezeigt.

Die Lokalisierung wird unter `Sprache` vorgenommen. Sie können die
Zeichenketten in Ihre eigene Sprache übersetzen, falls keine entsprechende
Sprachdatei zur Verfügung steht, oder sie entsprechend Ihren Anforderungen
anpassen.

Das Aussehen von Foldergallery_XH kann unter `Stylesheet` angepasst
werden.

## Verwendung

Bereiten Sie die Galerien vor, indem Sie Bilder in den Bilderordner von
CMSimple_XH, oder einen Unterordner davon, hoch laden, entweder per FTP oder
einem Filebrowser. Die Bildunterschriften werden aus dem Dateinamen
gebildet, oder, falls vorhanden, aus dem Exif `ImageDescription` Tag,
der in der Bilddatei gespeichert ist.

Um alle Bilder im Bilderordner auf einer Seite anzuzeigen, fügen Sie
folgenden Pluginaufruf ein:

    {{{foldergallery()}}}

Um nur die Bilder eines Unterordners (und allen Unterordnern dieses Ordners)
anzuzeigen, verwenden Sie in etwa folgendes (ersetzen Sie `%UNTERORDNERNAME%`
durch den tatsächlichen Namen des Unterordners):

    {{{foldergallery('%UNTERORDNERNAME%')}}}

## Einschränkungen

Es kann nur eine einzige Galerie auf jeder Seite angezeigt werden.

Es werden nur JPEG-Bilder unterstützt; andere Bildformate werden ignoriert.

## Problembehebung

Melden Sie Programmfehler und stellen Sie Supportanfragen entweder auf
[Github](https://github.com/cmb69/foldergallery_xh/issues)
oder im [CMSimple_XH Forum](https://cmsimpleforum.com/).

## Lizenz

Foldergallery_XH ist freie Software. Sie können es unter den Bedingungen
der GNU General Public License, wie von der Free Software Foundation
veröffentlicht, weitergeben und/oder modifizieren, entweder gemäß
Version 3 der Lizenz oder (nach Ihrer Option) jeder späteren Version.

Die Veröffentlichung von Foldergallery_XH erfolgt in der Hoffnung, dass es
Ihnen von Nutzen sein wird, aber *ohne irgendeine Garantie*, sogar ohne
die implizite Garantie der *Marktreife* oder der *Verwendbarkeit für einen
bestimmten Zweck*. Details finden Sie in der GNU General Public License.

Sie sollten ein Exemplar der GNU General Public License zusammen mit
Foldergallery_XH erhalten haben. Falls nicht, siehe <https://www.gnu.org/licenses/>.

Copyright © 2017 Christoph M. Becker

Slovakische Übersetzung © 2017 Dr. Martin Sereday

## Danksagung

Dieses Plugin wurde von dem [Fast Gallery](https://www.drupal.org/project/fast_gallery)
Drupal Modul inspiriert.

Foldergallery_XH verwendet [Colorbox](https://www.jacklmoore.com/colorbox/)
von Jack Moore und [PhotoSwipe](https://photoswipe.com/) von Dmitry Semenov.
Danke für die Veröffentlichung dieser großartigen Skripte unter MIT Lizenz.

Das Pluginlogo wurde von [Everaldo Coelho](https://www.everaldo.com/) gestaltet.
Vielen Dank für die Veröffentlichung dieses Icons unter GPL.

Vielen Dank an die Gemeinschaft im [CMSimple_XH Forum](https://www.cmsimpleforum.com/)
für Tipps, Anregungen und das Testen. Besonders möchte ich *frase* und *lck*
für das Testen und Feedback zu einem frühen Entwicklungsstand danken. Ebensfalls
vielen Dank an *bemerkenswelt*, der die Verwendung von [PhotoSwipe](https://photoswipe.com/)
angeregt hat. Und vielen Dank an *frase*, der mich auf das Flex-Layout-Modell
aufmerksam gemacht hat.

Und zu guter letzt vielen Dank an [Peter Harteg](http://www.harteg.dk/),
den „Vater“ von CMSimple, und allen Entwicklern von
[CMSimple_XH](https://www.cmsimple-xh.org/de/) ohne die es dieses
phantastische CMS nicht gäbe.
