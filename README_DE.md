# Monorder\_XH

Monorder\_XH ermöglicht die Platzierung von Bestell- oder
Reservierungsformularen für einzelne Posten bzw. Veranstaltungen (daher
der Name, der eine Kontraktion von "mono" und "order", engl. für
Bestellung, ist) auf Ihrer Website. Bei Bestellungen bzw. Reservierungen
werden dabei der verfügbare Bestand bzw. die freien Plätzen
berücksichtigt, so dass Überbuchungen nicht möglich sind.

Die Formulare müssen mit
[Advancedform\_XH](https://github.com/cmb69/advancedform_xh)
erstellt werden, und es ist möglich ein einziges Formular für mehrere
Verkaufsartikel bzw. Veranstaltungen zu verwenden. Allerdings kann
höchstens ein Formular für einen einzigen Verkaufsartikel bzw. eine
Veranstaltung pro Seite genutzt werden, so dass Sie ein Einkaufs- bzw.
Reservierungsystem verwenden sollten, wenn Sie erweiterte Ansprüche
haben.

Abgesehen von seinem eigentlichen Verwendungszweck dient Monorder\_XH
auch zur Demonstration der Möglichkeiten und Beschränkungen der
Verwendung von Advancedform\_XHs Hook-System für maßgeschneiderte
Plugins.

  - [Voraussetzungen](#voraussetzungen)
  - [Download](#download)
  - [Installation](#installation)
  - [Einstellungen](#einstellungen)
  - [Verwendung](#verwendung)
      - [Vorbereitung des Formulars](#vorbereitung-des-formulars)
      - [Vorbereitung der Posten](#vorbereitung-der-posten)
      - [Anzeige des Formulars](#anzeige-des-formulars)
      - [Bestandsanzeige](#bestandsanzeige)
  - [Fehlerbehebung](#fehlerbehebung)
  - [Lizenz](#lizenz)
  - [Danksagung](#danksagung)

## Voraussetzungen

Monorder\_XH ist ein Plugin für CMSimple\_XH ≥ 1.5.4,
PHP ≥ 5.1 und Advancedform\_XH.

## Download

Das [aktuelle Release](https://github.com/cmb69/monorder_xh/releases/latest) kann von Github herunter geladen werden.

## Installation

Die Installation erfolgt wie bei vielen anderen CMSimple\_XH-Plugins
auch. Im [CMSimple\_XH
Wiki](https://wiki.cmsimple-xh.org/doku.php/de:installation) finden
sie ausführliche Hinweise.

1.  Sichern Sie die Daten auf Ihrem Server.
2.  Entpacken Sie die ZIP-Datei auf Ihrem Computer.
3.  Laden Sie das gesamte Verzeichnis monorder/ auf Ihren Server in das
    plugins/ Verzeichnis von CMSimple\_XH hoch.
4.  Vergeben Sie Schreibrechte für die Unterverzeichnisse css/, config/,
    languages/ und den Datenordner des Plugins.
5.  Schützen Sie den Daten-Ordner von Monorder\_XH vor direktem Zugriff
    auf eine Weise, die Ihr Webserver unterstützt. .htaccess-Dateien für
    Apache Server sind bereits im voreingestellten Daten-Ordner
    enthalten.
6.  Navigieren Sie zur Administration von Monorder, und prüfen Sie, ob
    alle Voraussetzungen für den Betrieb erfüllt sind.

## Einstellungen

Die Konfiguration des Plugins erfolgt wie bei vielen anderen
CMSimple\_XH-Plugins auch im Administrationsbereich der Homepage. Wählen
Sie Plugins → Monorder.

Sie können die Original-Einstellungen von Monorder\_XH unter
"Konfiguration" ändern. Beim Überfahren der Hilfe-Icons mit der Maus
werden Hinweise zu den Einstellungen angezeigt.

Die Lokalisierung wird unter "Sprache" vorgenommen. Sie können die
Zeichenketten in Ihre eigene Sprache übersetzen, oder sie entsprechend
Ihren Anforderungen anpassen.

Das Aussehen von Monorder\_XH kann unter "Stylesheet" angepasst werden.

## Verwendung

Wie bereits in der Einleitung erwähnt kann Monorder\_XH sowohl für
Bestell- als auch für Reservierungsformulare verwendet werden – was das
Plugin betrifft, sind beide Konzepte identisch, weshalb im folgenden der
Begriff *Bestellungen* sich auch auf Reservierungen, der Begriff
*Posten* sich auf Bestell- und Verkaufsartikel sowie auf Veranstaltungen
und der Begriff *Menge* sich sowohl auf die Bestellmenge wie auch die
Teilnehmerzahl einer Veranstaltung bezieht. Für Reservierungen sollten
Sie die entsprechenden Begriffe in den Spracheinstellungen des Plugins
ändern.

### Vorbereitung des Formulars

Zunächst müssen Sie das Bestellformular in der
Formular-Verwaltung
von Advancedform\_XH vorbereiten. Die Besonderheit von Formularen, die
mit Monorder\_XH genutzt werden können, ist, dass sie ein Feld vom Typ
"Zahl" oder "versteckt" mit dem Namen "order\_amount" enthalten
*müssen*, und ein Feld vom Typ "Text" oder "versteckt" mit dem Namen
"order\_item" enthalten *können* (beide Namen können in der
Plugin-Konfiguration geändert werden, aber müssen für alle Formulare
gleich sein).

Beachten Sie, dass versteckte Felder nicht in der Bestätigungs-E-Mail
enthalten sein werden, und dass eine Bestätigung nur versandt wird, wenn
für das Formular eine Dank-Seite festgelegt wurde, und jenes ein Feld
vom Typ "Absender (E-Mail)" enthält.

Beachten Sie ebenfalls, dass sie zwar Advancedform\_XHs
Vorlagen-System, nicht aber das Hook-System
verwenden können, da dieses bereits hinter den Kulissen von Monorder\_XH
verwendet wird.

Wenn Sie einen besseren Überblick über alle Bestellungen behalten
wollen, können Sie die "Daten speichern" Option des Formulars
aktivieren. Beachten Sie, dass alle Bestellungen durch dieses Formular
unabhängig vom Posten in der selben CSV-Datei gespeichert werden. Um das
aufzutrennen, können Sie die CSV-Datei zwischenzeitlich in eine
Tabellenkalkulationssoftware importieren und deren Möglichkeiten nutzen.

### Vorbereitung der Posten

Für jeden Posten, den Sie anbieten möchten, müssen Sie einen neuen
Datensatz in der Administration von Monorder\_XH anlegen und die
verfügbare Menge angeben. Natürlich können Sie diese Menge jederzeit
nach Bedarf ändern. Der Name des Postens wird nur intern verwendet,
außer wenn Sie ein order\_item Feld für das Formular definiert haben,
in welchem Fall dessen Wert der Name des Postens sein wird. Daher ist es
sinnvoll einen sprechenden Namen zu verwenden, wie z.B. "Gelbes
CMSimple\_XH T-Shirt XL" oder "CMSimple\_XH Seminar, 2014-03-21".

### Anzeige des Formulars

Um ein Bestellformular auf einer Seite anzuzeigen, nutzen Sie folgenden
Pluginaufruf:

    {{{PLUGIN:Monorder_form('FORMULAR_NAME', 'POSTEN_NAME');}}}

wobei FORMULAR\_NAME der Name des Advancedform\_XH Formulars, und
POSTEN\_NAME der Name des Monorder\_XH Postens ist,
    z.B.:

    {{{PLUGIN:Monorder_form('Bestellung', 'Gelbes CMSimple_XH T-Shirt XL');}}}

Wenn sich Posten im Bestand befinden wird deren Anzahl oberhalb des
Formulars angezeigt; andernfalls erfolgt eine Meldung, dass keine Posten
mehr verfügbar sind, und das Formular wird überhaupt nicht angezeigt.

Wenn ein Kunde das Formular abschickt, wird die Formularprüfung
sicherstellen, dass die Bestellmenge von order\_amount auch verfügbar
ist, und dass der Wert von order\_item nicht geändert wurde; andernfalls
wird die E-Mail nicht verschickt. Wenn alles in Ordnung ist, wird die
E-Mail verschickt und die Bestellmenge wird vom Bestand abgezogen.

### Bestandsanzeige

Wenn Sie möchten, können Sie die Bestandsmenge auf anderen Seiten (z.B.
auf einer Übersichtsseite) anzeigen. Dazu verwenden Sie den folgenden
Pluginaufruf:

    {{{PLUGIN:Monorder_inventory('POSTEN_NAME');}}}

Beispiel:

    {{{PLUGIN:Monorder_inventory('Gelbes CMSimple_XH T-Shirt XL');}}}

## Fehlerbehebung

Melden Sie Programmfehler und stellen Sie Supportanfragen entweder auf [Github](https://github.com/cmb69/monorder_xh/issues)
oder im [CMSimple_XH Forum](https://cmsimpleforum.com/).

## Lizenz

Monorder\_XH ist freie Software. Sie können es unter den Bedingungen
der GNU General Public License, wie von der Free Software Foundation
veröffentlicht, weitergeben und/oder modifizieren, entweder gemäß
Version 3 der Lizenz oder (nach Ihrer Option) jeder späteren Version.

Die Veröffentlichung von Monorder\_XH erfolgt in der Hoffnung, daß es
Ihnen von Nutzen sein wird, aber *ohne irgendeine Garantie*, sogar ohne
die implizite Garantie der *Marktreife* oder der *Verwendbarkeit für einen
bestimmten Zweck*. Details finden Sie in der GNU General Public License.

Sie sollten ein Exemplar der GNU General Public License zusammen mit
Monorder\_XH erhalten haben. Falls nicht, siehe
<http://www.gnu.org/licenses/>.

Copyright 2014-2019 Christoph M. Becker

## Danksagung

Diese Plugin wurde von Simmyne angeregt.

Das Plugin-Icon wurde von [Matt](http://www.freeiconsdownload.com/)
gestaltet. Vielen Dank für die Veröffentlichung unter einer liberalen
Lizenz.

Diese Plugin verwendet "free applications icons" von
[Aha-Soft](http://www.aha-soft.com/). Vielen Dank für die freie
Verwendbarkeit dieser Icons.

Vielen Dank an die Gemeinschaft im
[CMSimple\_XH-Forum](http://www.cmsimpleforum.com/) für Tipps,
Anregungen und das Testen.

Zu guter Letzt vielen Dank an [Peter Harteg](http://harteg.dk/), den
"Vater" von CMSimple, und allen Entwicklern von
[CMSimple\_XH](http://www.cmsimple-xh.org/), ohne die dieses
fantastische CMS nicht existieren würde.
