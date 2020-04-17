<?php

$lang = &$GLOBALS['TL_LANG']['tl_entity_import_config'];

/**
 * Fields
 */
$lang['title'][0]             = 'Titel';
$lang['title'][1]             = 'Geben Sie hier den Titel des Imports ein.';
$lang['targetTable'][0]       = 'Zieltabelle';
$lang['targetTable'][1]       = 'Wählen Sie hier die Tabelle, in die importiert werden soll.';
$lang['mergeTable'][0]        = 'Beim Importieren zusammenführen (Merge)';
$lang['mergeTable'][1]        = 'Wählen Sie diese Option, wenn beim Importieren bereits bestehende Datensätze mit den zu importierenden Datensätzen zusammengeführt werden sollen.';
$lang['purgeBeforeImport'][0] = 'Daten vor dem Import löschen';
$lang['purgeBeforeImport'][1] = 'Wählen Sie diese Option wenn die vorhandenen Daten vor dem Import gelöscht werden sollen.';
$lang['purgeWhereClause'][0]  = 'WHERE-Bedingungen für das Löschen';
$lang['purgeWhereClause'][1]  = 'Geben Sie hier SQL-Bedingungen in der Form "pid=27 AND id=1" ein, die für das Löschen von Datensätzen vor jedem Import gelten sollen.';

$lang['mergeIdentifierFields'][0]           = 'Merge-Identifikationsfelder';
$lang['mergeIdentifierFields'][1]           = 'Wählen Sie hier die Felder aus, die für das Auffinden bestehender Datensätze genutzt werden sollen (bspw. E-Mail, ID, Vorname + Nachname, ...).';
$lang['mergeIdentifierFields']['source'][0] = 'Quellfeld';
$lang['mergeIdentifierFields']['source'][1] = 'Wählen Sie hier das Quellfeld aus der externen Quelle aus.';
$lang['mergeIdentifierFields']['target'][0] = 'Feld in Zieltabelle';
$lang['mergeIdentifierFields']['target'][1] = 'Wählen Sie hier das Zielfeld in das importiert wird.';

$lang['fieldMapping'][0]                 = 'Feldabbildung';
$lang['fieldMapping'][1]                 = 'Geben Sie hier die Zuordnung der ausgewählten Felder der Quelle mit den vorhandenen Tabellenspalten.';
$lang['fieldMapping']['columnName'][0]   = 'Spaltenname';
$lang['fieldMapping']['columnName'][1]   = 'Wählen Sie hier die Spalte der gewählten Tabelle aus.';
$lang['fieldMapping']['valueType'][0]    = 'Typ des Wertes';
$lang['fieldMapping']['valueType'][1]    = 'Wählen Sie hier den Typ des Wertes aus. Bei dynamisch wird der Wert aus der Feldabbildung der Quelle genommen. Bei statisch wird der Inhalt des Feldes als Wert genommen.';
$lang['fieldMapping']['mappingValue'][0] = 'Quellfeld Wert';
$lang['fieldMapping']['mappingValue'][1] = 'Wählen Sie hier ein Feld aus der Feldabbildung der Quelle aus.';
$lang['fieldMapping']['staticValue'][0]  = 'Statischer Wert';
$lang['fieldMapping']['staticValue'][1]  = 'Geben Sie hier einen Wert ein. Dieser wird in alle Datensätze eingetragen.';

$lang['importMode'][0] = 'Importmodus';
$lang['importMode'][1] = 'Wählen Sie hier aus, auf welche Weise importiert werden soll.';

$lang['sortingMode'][0] = 'Sortiermodus';
$lang['sortingMode'][1] = 'Wählen Sie hier aus, auf welche Weise sortiert werden soll.';

$lang['targetSortingField'][0] = 'Sortierfeld';
$lang['targetSortingField'][1] = 'Achtung: Das Feld muss Contaos Sortierlogik entsprechen (Integer als Vielfache von 2).';

$lang['targetSortingPidField'][0] = 'PID-Feld';
$lang['targetSortingPidField'][1] = 'Wählen Sie hier, sofern vorhanden, ein PID-Feld aus, damit die Sortierlogik sich in diesem Kontext bewegt.';

$lang['setDateAdded'][0] = 'Erstelldatum setzen';
$lang['setDateAdded'][1] = 'Wählen Sie diese Option, damit automatisch das Erstelldatum als Unix-Timestamp gesetzt wird.';

$lang['dateAddedField'][0] = 'Feld für das Erstelldatum (dateAdded)';
$lang['dateAddedField'][1] = 'Wählen Sie hier das Feld aus, in dem der Unix-Timestamp zum Zeitpunkt der Erstellung des Datensatzes gespeichert wird.';

$lang['setTstamp'][0] = 'Datum der letzten Änderung setzen';
$lang['setTstamp'][1] = 'Wählen Sie diese Option, damit automatisch das Datum der letzten Änderung als Unix-Timestamp gesetzt wird.';

$lang['tstampField'][0] = 'Zeitstempel-Feld (tstamp)';
$lang['tstampField'][1] = 'Wählen Sie hier das Feld aus, in dem der Unix-Timestamp der letzten Änderung gespeichert wird.';

$lang['generateAlias'][0] = 'Alias generieren';
$lang['generateAlias'][1] = 'Wählen Sie diese Option, damit automatisch ein Alias generiert wird.';

$lang['aliasField'][0] = 'Alias-Feld (alias)';
$lang['aliasField'][1] = 'Wählen Sie hier das Feld aus, in dem der generierte Alias gespeichert wird.';

$lang['aliasFieldPattern'][0] = 'Feldmuster für die Aliasgenerierung (Zielfelder!)';
$lang['aliasFieldPattern'][1] = 'Geben Sie hier ein Zielfeld-Muster der Form "%title%" ein, welches für die Aliasgenerierung genutzt wird (Verkettungen wie "%fieldname1%-%fieldname2%" sind auch möglich).';

$lang['useCron'][0]      = 'Cronjob nutzen';
$lang['useCron'][1]      = 'Wählen Sie diese Option, um den Importer per Cronjob auszulösen.';
$lang['cronInterval'][0] = 'Cron-Interval';
$lang['cronInterval'][1] = 'Wählen Sie hier das Interval aus, in dem der Import ausgeführt werden soll.';


/**
 * Reference
 */
$lang['reference'] = [
    'importMode'   => [
        'insert' => 'Beim Importieren neue Datensätze anlegen',
        'merge'  => 'Beim Importieren Datensätze zusammenführen (Merge)',
    ],
    'sortingMode'  => [
        \HeimrichHannot\EntityImportBundle\DataContainer\EntityImportConfigContainer::SORTING_MODE_SOURCE_ORDER => 'Reihenfolge in der Quelle beibehalten'
    ],
    'cronInterval' => [
        'minutely' => 'Minütlich',
        'hourly'   => 'Stündlich',
        'daily'    => 'Täglich',
        'weekly'   => 'Wöchentlich',
        'monthly'  => 'Monatlich'
    ],
    'valueType'    => [
        'source_value' => 'dynamisch',
        'static_value' => 'statisch',
    ]
];

/**
 * Messages
 */
$lang['importConfirm'] = 'Soll der Import ID %s wirklich durchgeführt werden?';

/**
 * Errors
 */
$lang['error']['errorMessage']        = 'Beim Importieren ist ein Fehler aufgetreten: %s.';
$lang['error']['tableDoesNotExist']   = 'Die Zieltabelle existiert nicht.';
$lang['error']['tableFieldsDiffer']   = 'Die Felder vom Quelle und Ziel unterscheiden sich.';
$lang['error']['noIdentifierFields']  = 'Das Identifikatorfelder wurde nicht gesetzt.';
$lang['error']['successfulImport']    = 'Es wurden %s Einträge erfolgreich importiert bzw. aktualisiert.';
$lang['error']['emptyFile']           = 'Es wurden keine Daten zum Importieren gefunden.';
$lang['error']['errorImport']         = 'Bei %s Einträgen sind beim Import Fehler aufgetreten. Fehler: %s';
$lang['error']['delimiter']           = 'Das Feld-Trennzeichen für die CSV-Quelle wurde nicht gesetzt.';
$lang['error']['enclosure']           = 'Das Text-Trennzeichen für die CSV-Quelle wurde nicht gesetzt.';
$lang['error']['escape']              = 'Escape für csv ist nicht definiert.';
$lang['error']['filePathNotProvided'] = 'Der Pfad zur Datei wurde nicht gefunden.';
$lang['error']['modeNotSet']          = 'Der Import Modus ist nicht gesetzt.';

/**
 * Backend Modules
 */
$lang['import'][0] = 'Import ausführen';
$lang['import'][1] = 'Import ID %s ausführen';
$lang['headline']  = 'Import ID %s';
$lang['label']     = 'Klicken Sie &quot;Import ausführen&quot;, um den Importprozess zu starten.';

/**
 * Legends
 */
$lang['general_legend'] = 'Allgemeines';
$lang['fields_legend']  = 'Felder';
$lang['cron_legend']    = 'Cron';

/**
 * Buttons
 */
$lang['new'][0]    = 'Neuer Importer';
$lang['new'][1]    = 'Einen neuen Importer anlegen';
$lang['show'][0]   = 'Importer-Details';
$lang['show'][1]   = 'Details von Importer ID %s anzeigen';
$lang['edit'][0]   = 'Importer bearbeiten';
$lang['edit'][1]   = 'Importer ID %s bearbeiten';
$lang['copy'][0]   = 'Importer kopieren';
$lang['copy'][1]   = 'Importer ID %s duplizieren';
$lang['delete'][0] = 'Importer löschen';
$lang['delete'][1] = 'Importer ID %s löschen';
$lang['dryRun'][0] = 'Testlauf';
$lang['dryRun'][1] = 'Testlauf ausführen';
