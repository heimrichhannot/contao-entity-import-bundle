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
$lang['mergeIdentifierFields'][0]           = 'Wählen Sie hier die Felder aus, die für das Auffinden bestehender Datensätze genutzt werden sollen (bspw. E-Mail, ID, Vorname + Nachname, ...).';
$lang['mergeIdentifierFields']['source'][0] = 'Quellfeld';
$lang['mergeIdentifierFields']['source'][1] = 'Wählen Sie hier das Quellfeld aus der externen Quelle aus.';
$lang['mergeIdentifierFields']['target'][0] = 'Wählen Sie hier das Zielfeld in das Importiert wird.';
$lang['mergeIdentifierFields']['target'][1] = 'Wählen Sie hier das Zielfeld in das Importiert wird.';

$lang['importMode'][0] = 'Importeinstellungen';
$lang['importMode'][1] = 'Wählen Sie hier die Einstellungen für dem Import.';

/**
 * Reference
 */
$lang['reference'] = [
    'importMode' => [
        'insert' => 'Beim Importieren neue Datensätze anlegen',
        'merge'  => 'Beim Importieren Datensätze zusammenführen (Merge)',
        'purge'  => 'Vor dem Importieren die Datensätze in der Zieltabelle löschen',
    ],
];

/**
 * Messages
 */
$lang['importConfirm'] = 'Soll der Import ID %s wirklich durchgeführt werden?';

/**
 * Errors
 */
$lang['error']['errorMessage']        = 'Beim Importieren ist ein Fehler aufgetreten: %s.';
$lang['error']['notInitialized']      = 'Importer ist noch nicht initialisiert.';
$lang['error']['tableDoNotExist']     = 'Die Zieltabelle existiert nicht.';
$lang['error']['tableFieldsDiffer']   = 'Die Felder vom Ziel und Quelle unterscheiden sich.';
$lang['error']['noIdentifierFields']  = 'Identifikatorfelder nicht gesetzt.';
$lang['error']['successfulImport']    = 'Erfolgreicher Import von %s Einträgen';
$lang['error']['emptyFile']           = 'Daten zum Importieren nicht vorhanden.';
$lang['error']['errorImport']         = 'Fehlerhafter Import von %s Einträgen. Fehler: %s';
$lang['error']['delimiter']           = 'Delimiter für csv ist nicht definiert';
$lang['error']['enclosure']           = 'Enclosure für csv ist nicht definiert';
$lang['error']['escape']              = 'Enclosure für csv ist nicht definiert';
$lang['error']['filePathNotProvided'] = 'Der Pfad zur Datei wurde nicht gefunden';
$lang['error']['modeNotSet']          = 'Der Import Modus ist nicht gesetzt';

/**
 * Backend Modules
 */
$lang['import'][0] = 'Import ausführen';
$lang['import'][1] = 'Import ID %s ausführen';
$lang['headline']  = 'Import ID %s';
$lang['label']     = 'Klicken Sie &quot;Import ausführen&quot;, um den Importprozess zu starten.';

/**
 * Buttons
 */
$lang['new'][0]    = 'Neue Konfiguration';
$lang['new'][1]    = 'Einen neuen Konfiguration anlegen';
$lang['show'][0]   = 'Konfiguration-Details';
$lang['show'][1]   = 'Details von Konfiguration ID %s anzeigen';
$lang['edit'][0]   = 'Konfiguration bearbeiten';
$lang['edit'][1]   = 'Konfiguration ID %s bearbeiten';
$lang['copy'][0]   = 'Konfiguration kopieren';
$lang['copy'][1]   = 'Konfiguration ID %s duplizieren';
$lang['delete'][0] = 'Konfiguration löschen';
$lang['delete'][1] = 'Konfiguration ID %s löschen';
$lang['dryRun'][0] = 'Testlauf';
$lang['dryRun'][1] = 'Testlauf ausführen';