<?php

$arrLang = &$GLOBALS['TL_LANG']['tl_entity_import_config'];

/**
 * Backend Modules
 */
$arrLang['import'][0] = 'Import ausführen';
$arrLang['import'][1] = 'Import ID %s ausführen';
$arrLang['dryRun'][0] = 'Testlauf';
$arrLang['dryRun'][1] = 'Testlauf ID %s ausführen';
$arrLang['headline']  = 'Import ID %s';
$arrLang['label']     = 'Klicken Sie &quot;Import ausführen&quot;, um den Importprozess zu starten.';


/**
 * Buttons
 */
$arrLang['new']    = ['Neue Konfiguration', 'Einen neuen Konfiguration anlegen'];
$arrLang['show']   = ['Konfiguration-Details', 'Details von Konfiguration ID %s anzeigen'];
$arrLang['edit']   = ['Konfiguration bearbeiten', 'Konfiguration ID %s bearbeiten'];
$arrLang['copy']   = ['Konfiguration kopieren', 'Konfiguration ID %s duplizieren'];
$arrLang['delete'] = ['Konfiguration löschen', 'Konfiguration ID %s löschen'];

/**
 * Fields
 */
$arrLang['title']                                = ['Titel', 'Geben Sie hier den Titel des Imports ein.'];
$arrLang['description']                          = [
    'Beschreibung',
    'Geben Sie hier eine Beschreibung für diese Konfiguration ein. Sie erscheint in der Listenansicht.'
];
$arrLang['dbSourceTable']                        = [
    'Quelltabelle',
    'Wählen Sie hier die Tabelle aus, die als Quelle des Imports dienen soll.'
];
$arrLang['dbTargetTable']                        = [
    'Zieltabelle',
    'Wählen Sie hier die Tabelle aus, die als Ziel des Imports dienen soll.'
];
$arrLang['importerClass']                        = [
    'Importerklasse',
    'Wählen Sie hier die PHP-Klasse, die als Importer fungieren soll. Die Klasse muss eine Subklasse von "\\HeimrichHannot\\EntityImport\\Importer" sein.'
];
$arrLang['purgeBeforeImport']                    = [
    'Datensätze in der Zieltabelle vor dem Import löschen',
    'Wählen Sie diese Option, wenn in der Zieltabelle vor jedem Import Datensätze gelöscht werden sollen.'
];
$arrLang['whereClausePurge']                     = [
    'WHERE-Bedingungen für das Löschen',
    'Geben Sie hier SQL-Bedingungen in der Form "pid=27 AND id=1" ein, die für das Löschen von Datensätzen vor jedem Import gelten sollen.'
];
$arrLang['dbFieldMapping']                       = [
    'Felderabbildung',
    'Wählen Sie hier aus, welche Felder der Quelltabelle auf welche der Zieltabelle abgebildet werden sollen.'
];
$arrLang['dbFieldMapping']['type']               = [
    'Typ',
    'Wählen Sie \'Quellfeld\', um den Wert eines Feldes in der Quelltabelle in das entsprechende Feld der Zieltabelle zu schreiben. Für einfache Werte nutzen Sie \'Wert\'.',
];
$arrLang['dbFieldMapping']['type']['source']     = 'Quellfeld';
$arrLang['dbFieldMapping']['type']['foreignKey'] = 'Fremdschlüssel (Quelle)';
$arrLang['dbFieldMapping']['type']['value']      = 'Wert';
$arrLang['dbFieldMapping']['type']['sql']        = 'SQL-Abfrage';
$arrLang['dbFieldMapping']['source']             = [
    'Quellfeld',
    'Wählen Sie hier nur dann ein Feld aus, wenn Sie als Typ \'Quellfeld\' oder \'Fremdschlüssel\' gewählt haben.'
];
$arrLang['dbFieldMapping']['value']              = [
    'Wert / Fremdschlüssel',
    'Geben Sie hier nur dann einen Wert oder Fremdschlüssel-Feld ein, wenn Sie als Typ \'Wert\' oder \'Fremdschlüssel\' gewählt haben.'
];
$arrLang['dbFieldMapping']['target']             = [
    'Zielfeld',
    'Wählen Sie hier das Feld in der Zieltabelle aus, in das importiert werden soll.'
];
$arrLang['dbFieldMapping']['transform']          = [
    'Wert Transformieren',
    'Wert vor setzen transformieren (Verwenden Sie ##value## innerhalb von Inserttags {{trimsplit::,::##value##}}'
];
$arrLang['useTimeInterval']                      = ['Zeitraum angeben', 'Geben Sie einen Zeitraum an.'];
$arrLang['start']                                = [
    'Startzeit',
    'Wählen Sie hier die Startzeit eines temporalen Filters aus.'
];
$arrLang['end']                                  = [
    'Endzeit',
    'Wählen Sie hier die Endzeit eines temporalen Filters aus.'
];
$arrLang['whereClause']                          = [
    'WHERE-Bedingungen',
    'Geben Sie hier Bedingungen für die WHERE-Klausel in der Form "pid=27 AND id=1" ein.'
];
$arrLang['sourceDir']                            = [
    'Quellverzeichnis',
    'Wählen Sie hier das Quellverzeichnis für Dateiimporte aus.'
];
$arrLang['targetDir']                            = [
    'Zielverzeichnis',
    'Wählen Sie hier das Zielverzeichnis für Dateiimporte aus.'
];
$arrLang['catContao']                            = [
    'Nachrichten-Kategorien',
    'Wählen Sie hier die Kategorien aus, die den importierten Nachrichten zugewiesen werden sollen.'
];
$arrLang['newsArchive']                          = [
    'Nachrichtenarchiv',
    'Wählen Sie hier das Nachrichtenarchiv aus, in das die Nachrichten importiert werden sollen.'
];
$arrLang['sourceFile']                           = [
    'Quell-Datei',
    'Wählen Sie hier die zu Quell-Datei für den Import aus.'
];
$arrLang['delimiter']                            = ['Feld-Trennzeichen', 'Geben Sie hier das Feld-Trennzeichen ein.'];
$arrLang['arrayDelimiter']                       = [
    'Array-Trennzeichen',
    'Geben Sie hier das Trennzeichen für die Umwandlung von trennzeichen-separierten Feldwerten ein. Wenn das entsprechende Häkchen in der Felderabbildung gesetzt ist, werden Werte wie "1;4;5" zu einem serialisierten Array transformiert.',
];
$arrLang['enclosure']                            = ['Text-Trennzeichen', 'Geben Sie hier das Text-Trennzeichen ein.'];
$arrLang['fileFieldMapping']                     = [
    'Felderabbildung',
    'Wählen Sie hier aus, welche Felder der Quelldatei auf welche der Zieltabelle abgebildet werden sollen.'
];
$arrLang['fileFieldMapping']['type']             = [
    'Typ',
    'Wählen Sie \'Spalte\', um den Wert einer Spalte in der Quell in das entsprechende Feld der Zieltabelle zu schreiben. Für einfache Werte nutzen Sie \'Wert\'.'
];
$arrLang['fileFieldMapping']['type']['source']   = 'Spalte';
$arrLang['fileFieldMapping']['type']['value']    = 'Wert';
$arrLang['fileFieldMapping']['source']           = [
    'Quellspalte',
    'Geben Sie hier nur die Position der Spalte ein, wenn Sie als Typ \'Quellspalte\' gewählt haben. Für die erste Spalte in der Datei geben Sie bspw. 1 ein.'
];
$arrLang['fileFieldMapping']['value']            = [
    'Wert',
    'Geben Sie hier nur dann einen Wert ein, wenn Sie als Typ \'Wert\' gewählt haben.'
];
$arrLang['fileFieldMapping']['target']           = [
    'Zielfeld',
    'Wählen Sie hier das Feld in der Zieltabelle aus, in das importiert werden soll.'
];
$arrLang['fileFieldMapping']['transformToArray'] = [
    'Zu Array<br>transformieren',
    'Wählen Sie diese Option, um Werte wie \'1;4;5\' zu einem serialisierten Array zu transformieren.'
];
$arrLang['purgeAdditionalTables']                = [
    'Datensätze in zusätzlichen Tabellen vor dem Import löschen',
    'Wählen Sie diese Option, wenn in zusätzlichen Tabellen vor jedem Import Datensätze gelöscht werden sollen.'
];
$arrLang['additionalTablesToPurge']              = ['Zusätzliche Tabellen'];
$arrLang['tableToPurge']                         = ['Zusätzliche Tabelle auswählen'];
$arrLang['addMerge']                             = [
    'Beim Importieren mit bereits bestehenden zusammenführen (Merge)',
    'Wählen Sie diese Option, wenn beim Importieren bereits bestehende Datensätze mit den zu importierenden Datensätzen zusammengeführt werden sollen.'
];
$arrLang['mergeIdentifierFields']                = [
    'Merge-Identifikationsfelder',
    'Wählen Sie hier die Felder aus, die für das Auffinden bestehender Datensätze genutzt werden sollen (bspw. E-Mail, ID, Vorname + Nachname, ...).'
];
$arrLang['url']                                  = [
    'Quell-Url',
    'Tragen Sie hier die Url ein, von der die Daten importiert werden sollen.'
];
$arrLang['externalFieldMapping']                 = [
    'Feldabbildung',
    'type' => [
        'Typ',
        'source' => ['Quellfeld', 'Wählen Sie hier das Quellfeld aus der externen Quelle aus.'],
        'value' => ['Wert', 'Tragen Sie hier den Wert ein, der in das Feld gespeichert werden soll.']
    ],
    'source' => 'Quellfeld',
    'value' => 'Wert',
    'target' => 'Zielfeld'
];

$arrLang['externalImportExceptions'] = [
    'Konditionale Regeln beim Import',
    '',
    'externalField' => ['Quell-Feld'],
    'operator' => ['Operator'],
    'externalValue' => ['Quell-Wert'],
    'importField' => ['Ziel-Feld'],
    'importValue' => ['Ziel-Wert']
];

$arrLang['externalImportExclusions'] = [
    'Ausnahmeregeln beim Import',
    'Legen Sie hier Einschränkungen fest, für die Entität nicht importiert werden soll.',
    'externalField' => ['Quell-Feld'],
    'operator' => ['Operator'],
    'externalValue' => ['Quell-Wert'],
];

$arrLang['operators'] = [
    'equal' => '=',
    'notequal' => '!=',
    'lower' => '<',
    'greater' => '>',
    'lowerequal' => '<=',
    'greaterequal' => '>=',
    'like' => 'enthält',
];

$arrLang['publishAfterImport'] = ['Entitäten nach dem Import veröffentlichen', 'Wählen Sie diese Option, wenn die importierte Entität direkt veröffentlicht werden soll.'];
$arrLang['useCron'] = ['Cronjob nutzen', 'Wählen Sie diese Option, um den Importer per Cronjob auszulösen.'];
$arrLang['cronInterval'] = ['Cron-Interval', 'Wählen Sie hier das Interval aus, in dem der Import ausgeführt werden soll.'];

/**
 * Legends
 */
$arrLang['title_legend']    = 'Titel und Beschreibung';
$arrLang['config_legend']   = 'Konfiguration';
$arrLang['category_legend'] = 'Nachrichten-Kategorien';

$arrLang['external_legend_mapping']   = 'Feldabbildung';
$arrLang['external_legend_exception'] = 'Konditionale Regeln';
$arrLang['external_legend_exclusion'] = 'Ausnahmeregeln';

/**
 * Misc
 */
$arrLang['createNewContentElement'] = '&lt;Neues Inhaltselement anlegen&gt;';

/**
 * Messages
 */
$arrLang['confirm']      = 'Der Import wurde erfolgreich abgeschlossen.';
$arrLang['confirmDry']   = 'Der Import wurde erfolgreich geprüft.';
$arrLang['importerInfo'] = 'Für das Importieren wird die Klasse "%s" verwendet.';
$arrLang['newsDry']      = 'Trockenlauf: Nachricht "%s" wird beim Import bearbeitet.';
$arrLang['newsImport']   = 'Nachricht "%s" wurde erfolgreich importiert.';
$arrLang['externalDry']      = 'Trockenlauf: Entität "%s" wird beim Import bearbeitet.';
$arrLang['externalImport']   = 'Entität "%s" wurde erfolgreich importiert.';
$arrLang['externalImportMerged']   = 'Entität "%s" wurde erfolgreich aktualisiert.';
$arrLang['externalImportCronMessage'] = 'EntityImport: Import "%s" wurde durchgeführt.';
$arrLang['externalImportCronMessageNoImporterClass'] = 'EntityImport: Import "%s" konnte nicht durchgeführt werden. Es wurde keine ImporterClass festegelegt.';