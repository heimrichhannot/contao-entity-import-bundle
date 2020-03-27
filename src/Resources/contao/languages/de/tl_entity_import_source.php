<?php

$arrLang = &$GLOBALS['TL_LANG']['tl_entity_import_source'];

/**
 * Fields
 */
$arrLang['title']    = ['Titel', 'Geben Sie hier den Titel der Quelle ein.'];
$arrLang['type']     = ['Typ', 'Wählen Sie hier den Typ des Imports aus.'];
$arrLang['fileType'] = ['Typ', 'Wählen Sie hier den Typ der Datei aus.'];

$arrLang['csvHeaderRow']      = ['Kopfdatensatz', 'Der erste Datensatz enthält die Spaltennamen'];
$arrLang['csvDelimiter'] = ['Feld-Trennzeichen', 'Geben Sie hier das Feld-Trennzeichen ein.'];;
$arrLang['csvEnclosure']  = ['Text-Trennzeichen', 'Geben Sie hier das Text-Trennzeichen ein.'];
$arrLang['csvEscape'] = [
    'Array-Trennzeichen',
    'Geben Sie hier das Trennzeichen für die Umwandlung von trennzeichen-separierten Feldwerten ein. Wenn das entsprechende Häkchen in der Felderabbildung gesetzt ist, werden Werte wie "1;4;5" zu einem serialisierten Array transformiert.',
];

$arrLang['pathToDataArray']       = ['Pfad zu den Daten', 'Geben Sie hier den Pfad der Daten in der Datei ein. Ist notwendig wenn die zu importierenden Daten sich nicht in der ersten Ebene befinden.'];
$arrLang['fieldMapping']          = ['Felderabblidung', ''];
$arrLang['fieldMapping']['name']  = ['Name', 'Geben Sie hier den Namen des Wertes für die weitere Verarbeitung ein.'];
$arrLang['fieldMapping']['value'] = ['Wert', 'Geben Sie hier den Ort des Wertes ein.'];

$arrLang['fileContentJson'] = ['JSON Ansicht ', 'Hier wird das erste Objekt der ausgewählten JSON Datei dargestellt.'];
$arrLang['fileContentCsv']  = ['CSV Ansicht ', 'Hier wird die erste Spalte des CSV-Dokuments dargestellt.'];
$arrLang['sourceType']      = ['Quelle', 'Wählen Sie hier die Art der Dateiquelle aus.'];

$arrLang['sourceType'][\HeimrichHannot\EntityImportBundle\DataContainer\EntityImportSourceContainer::SOURCE_TYPE_HTTP]               = 'HTTP';
$arrLang['sourceType'][\HeimrichHannot\EntityImportBundle\DataContainer\EntityImportSourceContainer::SOURCE_TYPE_CONTAO_FILE_SYSTEM] = 'Contao Dateiverwaltung';
$arrLang['sourceType'][\HeimrichHannot\EntityImportBundle\DataContainer\EntityImportSourceContainer::SOURCE_TYPE_ABSOLUTE_PATH]      = 'Absoluter Pfad';
$arrLang['type'][\HeimrichHannot\EntityImportBundle\DataContainer\EntityImportSourceContainer::TYPE_DATABASE]                        = 'Datenbank';
$arrLang['type'][\HeimrichHannot\EntityImportBundle\DataContainer\EntityImportSourceContainer::TYPE_FILE]                            = 'Datei';

$arrLang['sourceUrl']   = ['Url', 'Geben Sie hier die URL zur Datei ein.'];
$arrLang['filePath']    = ['Absoluter Dateipfad', 'Geben Sie hier einen absoluten Dateipfad auf dem Server ein.'];
$arrLang['fileSRC']     = ['Datei wählen oder hochladen', 'Wählen Sie hier eine vorhandene Datei, oder laden Sie eine neue Datei hoch.'];
$arrLang['dbDriver']    = ['Treiber', 'Wählen Sie hier den Datenbanktreiber aus.'];
$arrLang['dbHost']      = ['Host', 'Geben Sie hier die Adresse des Datenbankhosts ein.'];
$arrLang['dbUser']      = ['Nutzer', 'Geben Sie hier einen berechtigten Datenbanknutzer ein.'];
$arrLang['dbPass']      = ['Passwort', 'Geben Sie hier das Passwort des berechtigten Datenbanknutzers ein.'];
$arrLang['dbDatabase']  = ['Datenbankname', 'Geben Sie hier den Namen der Datenbank ein.'];
$arrLang['dbPconnect']  = ['PConnect', 'Wählen Sie hier, ob Sie PConnect nutzen möchten.'];
$arrLang['dbCharset']   = ['Zeichensatz', 'Wählen Sie hier den gewünschten Zeichensatz aus.'];
$arrLang['dbSocket']    = ['Socket', 'Geben Sie hier einen Socket ein.'];
$arrLang['externalUrl'] = ['Url', 'Tragen Sie hier die Url ein, von der die Daten importiert werden sollen.'];


/**
 * Legends
 */
$arrLang['title_legend']    = 'Titel';
$arrLang['db_legend']       = 'Datenbankeinstellungen';
$arrLang['external_legend'] = 'Externe Quelle';
$arrLang['file_legend']     = 'Datei';

/**
 * Buttons
 */
$arrLang['new']    = ['Neuer Import', 'Einen neuen Import anlegen'];
$arrLang['show']   = ['Import-Details', 'Details von Import ID %s anzeigen'];
$arrLang['edit']   = ['Import bearbeiten', 'Import ID %s bearbeiten'];
$arrLang['copy']   = ['Import kopieren', 'Import ID %s duplizieren'];
$arrLang['delete'] = ['Import löschen', 'Import ID %s löschen'];