<?php

$lang = &$GLOBALS['TL_LANG']['tl_entity_import_source'];

/**
 * Fields
 */
$lang['title'][0]    = 'Titel';
$lang['title'][1]    = 'Geben Sie hier den Titel der Quelle ein.';
$lang['type'][0]     = 'Typ';
$lang['type'][1]     = 'Wählen Sie hier den Typ des Imports aus.';
$lang['fileType'][0] = 'Typ';
$lang['fileType'][1] = 'Wählen Sie hier den Typ der Datei aus.';

$lang['csvHeaderRow'][0] = 'Kopfdatensatz';
$lang['csvHeaderRow'][1] = 'Der erste Datensatz enthält die Spaltennamen';
$lang['csvDelimiter'][0] = 'Feld-Trennzeichen';
$lang['csvDelimiter'][1] = 'Geben Sie hier das Feld-Trennzeichen ein.';
$lang['csvEnclosure'][0] = 'Text-Trennzeichen';
$lang['csvEnclosure'][1] = 'Geben Sie hier das Text-Trennzeichen ein.';
$lang['csvEscape'][0]    = 'Array-Trennzeichen';
$lang['csvEscape'][1]    = 'Geben Sie hier das Trennzeichen für die Umwandlung von trennzeichen-separierten Feldwerten ein. Wenn das entsprechende Häkchen in der Felderabbildung gesetzt ist, werden Werte wie "1;4;5" zu einem serialisierten Array transformiert.';

$lang['pathToDataArray'][0]             = 'Pfad zu den Daten';
$lang['pathToDataArray'][1]             = 'Geben Sie hier den Pfad der Daten in der Datei ein. Ist notwendig wenn die zu importierenden Daten sich nicht in der ersten Ebene befinden.';
$lang['fieldMapping'][0]                = 'Felderabblidung';
$lang['fieldMapping'][1]                = 'Geben Sie hier die Zuordnung der Felder aus der Quelle ein.';
$lang['fieldMapping']['name'][0]        = 'Name';
$lang['fieldMapping']['name'][1]        = 'Geben Sie hier den Namen des Wertes für die weitere Verarbeitung ein.';
$lang['fieldMapping']['valueType'][0]   = 'Typ des Wertes';
$lang['fieldMapping']['valueType'][1]   = 'Wählen Sie hier den Typ des Wertes aus. Bei dynamisch wird der Wert aus dem Datensatz genommen. Bei statisch wird der Inhalt des Feldes als Wert genommen.';
$lang['fieldMapping']['sourceValue'][0] = 'Wert aus der Quelle';
$lang['fieldMapping']['sourceValue'][1] = 'Geben Sie hier den Ort des Wertes in der Quelle ein.';
$lang['fieldMapping']['staticValue'][0] = 'Statischer Wert';
$lang['fieldMapping']['staticValue'][1] = 'Geben Sie hier den Wert ein, der gleich in allen Datensätzen eingetragen werden soll.';

$lang['fileContentJson'][0] = 'JSON Ansicht';
$lang['fileContentJson'][1] = 'Hier wird das erste Objekt der ausgewählten JSON Datei dargestellt.';
$lang['fileContentCsv'][0]  = 'CSV Ansicht ';
$lang['fileContentCsv'][1]  = 'Hier wird die erste Spalte des CSV-Dokuments dargestellt.';
$lang['sourceType'][0]      = 'Quelle';
$lang['sourceType'][1]      = 'Wählen Sie hier die Art der Dateiquelle aus.';

$lang['sourceType'][\HeimrichHannot\EntityImportBundle\DataContainer\EntityImportSourceContainer::SOURCE_TYPE_HTTP]               = 'HTTP';
$lang['sourceType'][\HeimrichHannot\EntityImportBundle\DataContainer\EntityImportSourceContainer::SOURCE_TYPE_CONTAO_FILE_SYSTEM] = 'Contao Dateiverwaltung';
$lang['sourceType'][\HeimrichHannot\EntityImportBundle\DataContainer\EntityImportSourceContainer::SOURCE_TYPE_ABSOLUTE_PATH]      = 'Absoluter Pfad';
$lang['type'][\HeimrichHannot\EntityImportBundle\DataContainer\EntityImportSourceContainer::TYPE_DATABASE]                        = 'Datenbank';
$lang['type'][\HeimrichHannot\EntityImportBundle\DataContainer\EntityImportSourceContainer::TYPE_FILE]                            = 'Datei';

$lang['sourceUrl'][0]   = 'Url';
$lang['sourceUrl'][1]   = 'Geben Sie hier die URL zur Datei ein.';
$lang['filePath'][0]    = 'Absoluter Dateipfad';
$lang['filePath'][1]    = 'Geben Sie hier einen absoluten Dateipfad auf dem Server ein.';
$lang['fileSRC'][0]     = 'Datei wählen oder hochladen';
$lang['fileSRC'][1]     = 'Wählen Sie hier eine vorhandene Datei, oder laden Sie eine neue Datei hoch.';
$lang['dbDriver'][0]    = 'Treiber';
$lang['dbDriver'][1]    = 'Wählen Sie hier den Datenbanktreiber aus.';
$lang['dbHost'][0]      = 'Host';
$lang['dbHost'][1]      = 'Geben Sie hier die Adresse des Datenbankhosts ein.';
$lang['dbUser'][0]      = 'Nutzer';
$lang['dbUser'][1]      = 'Geben Sie hier einen berechtigten Datenbanknutzer ein.';
$lang['dbPass'][0]      = 'Passwort';
$lang['dbPass'][1]      = 'Geben Sie hier das Passwort des berechtigten Datenbanknutzers ein.';
$lang['dbDatabase'][0]  = 'Datenbankname';
$lang['dbDatabase'][1]  = 'Geben Sie hier den Namen der Datenbank ein.';
$lang['dbPconnect'][0]  = 'PConnect';
$lang['dbPconnect'][1]  = 'Wählen Sie hier, ob Sie PConnect nutzen möchten.';
$lang['dbCharset'][0]   = 'Zeichensatz';
$lang['dbCharset'][1]   = 'Wählen Sie hier den gewünschten Zeichensatz aus.';
$lang['dbSocket'][0]    = 'Socket';
$lang['dbSocket'][1]    = 'Geben Sie hier einen Socket ein.';
$lang['externalUrl'][0] = 'Url';
$lang['externalUrl'][1] = 'Tragen Sie hier die Url ein, von der die Daten importiert werden sollen.';

/**
 * Reference
 */
$lang['reference'] = [
    'valueType' => [
        'source_value' => 'dynamisch',
        'static_value' => 'statisch'
    ]
];

/**
 * Legends
 */
$lang['title_legend']    = 'Titel';
$lang['db_legend']       = 'Datenbankeinstellungen';
$lang['external_legend'] = 'Externe Quelle';
$lang['file_legend']     = 'Datei';

/**
 * Buttons
 */
$lang['new'][0]    = 'Neuer Import';
$lang['new'][1]    = 'Einen neuen Import anlegen';
$lang['show'][0]   = 'Import-Details';
$lang['show'][1]   = 'Details von Import ID %s anzeigen';
$lang['edit'][0]   = 'Import bearbeiten';
$lang['edit'][1]   = 'Import ID %s bearbeiten';
$lang['copy'][0]   = 'Import kopieren';
$lang['copy'][1]   = 'Import ID %s duplizieren';
$lang['delete'][0] = 'Import löschen';
$lang['delete'][1] = 'Import ID %s löschen';