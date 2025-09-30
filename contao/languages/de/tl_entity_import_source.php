<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

$lang = &$GLOBALS['TL_LANG']['tl_entity_import_source'];

/*
 * Fields
 */
$lang['title'][0] = 'Titel';
$lang['title'][1] = 'Geben Sie hier den Titel der Quelle ein.';

$lang['type'][0] = 'Typ';
$lang['type'][1] = 'Wählen Sie hier den Typ des Imports aus.';
$lang['type'][\HeimrichHannot\EntityImportBundle\DataContainer\EntityImportSourceContainer::TYPE_DATABASE] = 'Datenbank';
$lang['type'][\HeimrichHannot\EntityImportBundle\DataContainer\EntityImportSourceContainer::TYPE_FILE] = 'Datei';

$lang['fileType'][0] = 'Dateityp';
$lang['fileType'][1] = 'Wählen Sie hier den Typ der Datei aus.';
$lang['fileType'][\HeimrichHannot\EntityImportBundle\DataContainer\EntityImportSourceContainer::FILETYPE_CSV] = 'CSV';
$lang['fileType'][\HeimrichHannot\EntityImportBundle\DataContainer\EntityImportSourceContainer::FILETYPE_JSON] = 'JSON';
$lang['fileType'][\HeimrichHannot\EntityImportBundle\DataContainer\EntityImportSourceContainer::FILETYPE_RSS] = 'RSS';
$lang['fileType'][\HeimrichHannot\EntityImportBundle\DataContainer\EntityImportSourceContainer::FILETYPE_XML] = 'XML';

$lang['csvHeaderRow'][0] = 'Kopfdatensatz';
$lang['csvHeaderRow'][1] = 'Der erste Datensatz enthält die Spaltennamen.';
$lang['csvSkipEmptyLines'][0] = 'Leere Zeilen überspringen';
$lang['csvSkipEmptyLines'][1] = 'Sollen leeren Zeilen übersprungen werden?';
$lang['csvDelimiter'][0] = 'Feld-Trennzeichen';
$lang['csvDelimiter'][1] = 'Geben Sie hier das Feld-Trennzeichen ein.';
$lang['csvEnclosure'][0] = 'Text-Trennzeichen';
$lang['csvEnclosure'][1] = 'Geben Sie hier das Text-Trennzeichen ein.';
$lang['csvEscape'][0] = 'Array-Trennzeichen';
$lang['csvEscape'][1] = 'Geben Sie hier das Trennzeichen für die Umwandlung von trennzeichen-separierten Feldwerten ein. Wenn das entsprechende Häkchen in der Felderabbildung gesetzt ist, werden Werte wie "1;4;5" zu einem serialisierten Array transformiert.';

$lang['pathToDataArray'][0] = 'Pfad zu den Daten';
$lang['pathToDataArray'][1] = 'Geben Sie hier den Pfad der Daten in der Datei ein. Ist notwendig wenn die zu importierenden Daten sich nicht in der ersten Ebene befinden.';
$lang['fieldMapping'][0] = 'Felderabbildung';
$lang['fieldMapping'][1] = 'Geben Sie hier die Zuordnung der Felder aus der Quelle ein.';
$lang['fieldMapping']['name'][0] = 'Name';
$lang['fieldMapping']['name'][1] = 'Geben Sie hier den Namen des Wertes für die weitere Verarbeitung ein.';
$lang['fieldMapping']['valueType'][0] = 'Typ des Wertes';
$lang['fieldMapping']['valueType'][1] = 'Wählen Sie hier den Typ des Wertes aus. Bei dynamisch wird der Wert aus dem Datensatz genommen. Bei statisch wird der Inhalt des Feldes als Wert genommen.';
$lang['fieldMapping']['sourceValue'][0] = 'Wert aus der Quelle';
$lang['fieldMapping']['sourceValue'][1] = 'Geben Sie hier den Ort des Wertes in der Quelle ein.';
$lang['fieldMapping']['staticValue'][0] = 'Statischer Wert';
$lang['fieldMapping']['staticValue'][1] = 'Geben Sie hier den Wert ein, der gleich in allen Datensätzen eingetragen werden soll.';
$lang['fieldMappingPresets'][0] = 'Felderabbildung aus Vorlage erzeugen';
$lang['fieldMappingPresets'][1] = 'Wählen Sie hier bei Bedarf eine Vorlage aus. ACHTUNG: Dies überschreibt Ihre aktuell gesetzte Felderabbildung!';

$lang['fileContent'][0] = 'Dateivorschau';
$lang['fileContent'][1] = 'Hier können Sie den Inhalt der ausgewählten Datei sehen. Es wird nicht die gesammte Datei dargestellt.';

$lang['retrievalType'][0] = 'Dateiquelle';
$lang['retrievalType'][1] = 'Wählen Sie hier die Art der Dateiquelle aus.';

$lang['retrievalType'][\HeimrichHannot\EntityImportBundle\DataContainer\EntityImportSourceContainer::RETRIEVAL_TYPE_HTTP] = 'HTTP';
$lang['retrievalType'][\HeimrichHannot\EntityImportBundle\DataContainer\EntityImportSourceContainer::RETRIEVAL_TYPE_CONTAO_FILE_SYSTEM] = 'Contao Dateiverwaltung';
$lang['retrievalType'][\HeimrichHannot\EntityImportBundle\DataContainer\EntityImportSourceContainer::RETRIEVAL_TYPE_ABSOLUTE_PATH] = 'Absoluter Pfad';

$lang['sourceUrl'][0] = 'URL';
$lang['sourceUrl'][1] = 'Geben Sie hier die URL zur Datei ein.';
$lang['absolutePath'][0] = 'Absoluter Dateipfad';
$lang['absolutePath'][1] = 'Geben Sie hier einen absoluten Dateipfad auf dem Server ein.';
$lang['fileSRC'][0] = 'Datei wählen oder hochladen';
$lang['fileSRC'][1] = 'Wählen Sie hier eine vorhandene Datei, oder laden Sie eine neue Datei hoch.';
$lang['dbDriver'][0] = 'Treiber';
$lang['dbDriver'][1] = 'Wählen Sie hier den Datenbanktreiber aus.';
$lang['dbHost'][0] = 'Host';
$lang['dbHost'][1] = 'Geben Sie hier die Adresse des Datenbankhosts ein.';
$lang['dbUser'][0] = 'Nutzer';
$lang['dbUser'][1] = 'Geben Sie hier einen berechtigten Datenbanknutzer ein.';
$lang['dbPass'][0] = 'Passwort';
$lang['dbPass'][1] = 'Geben Sie hier das Passwort des berechtigten Datenbanknutzers ein.';
$lang['dbDatabase'][0] = 'Datenbankname';
$lang['dbDatabase'][1] = 'Geben Sie hier den Namen der Datenbank ein.';
$lang['dbPort'][0] = 'Port';
$lang['dbPort'][1] = 'Geben Sie hier einen Port ein.';
$lang['dbPconnect'][0] = 'PConnect';
$lang['dbPconnect'][1] = 'Wählen Sie hier, ob Sie PConnect nutzen möchten.';
$lang['dbCharset'][0] = 'Zeichensatz';
$lang['dbCharset'][1] = 'Wählen Sie hier den gewünschten Zeichensatz aus.';
$lang['dbSocket'][0] = 'Socket';
$lang['dbSocket'][1] = 'Geben Sie hier einen Socket ein.';
$lang['dbSourceTable'][0] = 'Quelltabelle';
$lang['dbSourceTable'][1] = 'Wählen Sie hier die Quelltabelle aus.';
$lang['dbSourceTableExplanation'] = 'Wenn Sie von der im CMS genutzten Datenbankverbindung abweichen, müssen Sie den Datensatz erst abspeichern, damit sich die Optionen des Feldes "Quelltabelle" aktualisieren.';
$lang['dbSourceTableWhere'][0] = 'WHERE-Bedingung für den Import';
$lang['dbSourceTableWhere'][1] = 'Geben Sie hier auf Wunsch eine WHERE-Bedingung ein.';
$lang['externalUrl'][0] = 'URL';
$lang['externalUrl'][1] = 'Tragen Sie hier die Url ein, von der die Daten importiert werden sollen.';
$lang['httpMethod'][0] = 'HTTP-Methode';
$lang['httpMethod'][1] = 'Wählen Sie hier die HTTP-Methode mit der auf die Datei zugegriffen werden soll.';
$lang['httpAuth'][0] = 'Authentifizierung';
$lang['httpAuth'][1] = 'Tragen Sie hier die Daten für die Authentifizierung ein.';
$lang['httpAuth']['username'][0] = 'Benutzername';
$lang['httpAuth']['username'][1] = 'Tragen Sie hier Ihren Benutzernamen ein.';
$lang['httpAuth']['password'][0] = 'Passwort';
$lang['httpAuth']['password'][1] = 'Tragen Sie hier Ihr Passwort ein.';
$lang['addDcMultilingualSupport'][0] = 'Unterstützung für DC_Multilingual hinzufügen (ACHTUNG: Erklärung lesen!)';
$lang['addDcMultilingualSupport'][1] = 'Wählen Sie diese Option, wenn es sich bei der Quelltabelle um eine Contao-Tabelle mit Unterstützung für DC_Multilingual handelt. WICHTIG: Fügen Sie die DC_Multilingual-Felder (langPid, language, ...) NICHT der Felderabbildung hinzu.';
$lang['addChangeLanguageSupport'][0] = 'Unterstützung für terminal42/contao-changelanguage hinzufügen (ACHTUNG: Erklärung lesen!)';
$lang['addChangeLanguageSupport'][1] = 'Wählen Sie diese Option, um das languageMain-Feld korrekt zu migrieren. ACHTUNG: Der Importer der Entität in der Fallback-Sprache muss vor diesem Importer laufen (Cron-Reihenfolge!). Fügen Sie "languageMain" NICHT der Felderabbildung hinzu.';

/*
 * Reference
 */
$lang['reference'] = [
    'valueType' => [
        'source_value' => 'dynamisch',
        'static_value' => 'statisch',
    ],
    'httpMethod' => [
        'get' => 'GET',
        'post' => 'POST',
    ],
];

/*
 * Legends
 */
$lang['title_legend'] = 'Titel';
$lang['db_legend'] = 'Datenbankeinstellungen';
$lang['external_legend'] = 'Externe Quelle';
$lang['file_legend'] = 'Datei';
$lang['misc_legend'] = 'Verschiedenes';
$lang['config_legend'] = 'Konfiguration';

/*
 * Buttons
 */
$lang['new'][0] = 'Neue Importquelle';
$lang['new'][1] = 'Eine neue Importquelle anlegen';
$lang['show'][0] = 'Importquelle-Details';
$lang['show'][1] = 'Details von Importquelle ID %s anzeigen';
$lang['editheader'][0] = 'Importquelle bearbeiten';
$lang['editheader'][1] = 'Importquelle ID %s bearbeiten';
$lang['edit'][0] = 'Importer von Importquelle bearbeiten';
$lang['edit'][1] = 'Importer von Importquelle ID %s bearbeiten';
$lang['copy'][0] = 'Importquelle kopieren';
$lang['copy'][1] = 'Importquelle ID %s duplizieren';
$lang['delete'][0] = 'Importquelle löschen';
$lang['delete'][1] = 'Importquelle ID %s löschen';
