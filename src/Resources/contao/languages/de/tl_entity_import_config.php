<?php

$arrLang = &$GLOBALS['TL_LANG']['tl_entity_import_config'];

/**
 * Backend Modules
 */
$arrLang['import'][0] = 'Import ausführen';
$arrLang['import'][1] = 'Import ID %s ausführen';
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
$arrLang['dryRun'] = ['Testlauf', 'Testlauf ausführen'];
/**
 * Fields
 */
$arrLang['title']                           = ['Titel', 'Geben Sie hier den Titel des Imports ein.'];
$arrLang['targetTable']                     = ['Zieltabelle', 'Wählen Sie hier die Tabelle, in die importiert werden soll.'];
$arrLang['mergeTable']                      = [
    'Beim Importieren zusammenführen (Merge)',
    'Wählen Sie diese Option, wenn beim Importieren bereits bestehende Datensätze mit den zu importierenden Datensätzen zusammengeführt werden sollen.',
];
$arrLang['mergeIdentifierFields']           = [
    'Merge-Identifikationsfelder',
    'Wählen Sie hier die Felder aus, die für das Auffinden bestehender Datensätze genutzt werden sollen (bspw. E-Mail, ID, Vorname + Nachname, ...).',
];
$arrLang['mergeIdentifierFields']['source'] = ['Quellfeld', 'Wählen Sie hier das Quellfeld aus der externen Quelle aus.'];
$arrLang['mergeIdentifierFields']['target'] = ['Zielfeld', 'Wählen Sie hier das Zielfeld in das Importiert wird.'];

$arrLang['importSettings'] = ['Importeinstellungen', ''];
$arrLang['importSettings']['options']['insert'] = 'Beim Importieren neue Datensätze anlegen';
$arrLang['importSettings']['options']['merge']  = 'Beim Importieren Datensätze zusammenführen (Merge)';
$arrLang['importSettings']['options']['purge']  = 'Vor dem Importieren die Datensätze in der Zieltabelle löschen';

/**
 * Messages
 */
$arrLang['importConfirm'] = 'Soll der Import ID %s wirklich durchgeführt werden?';

/**
 * Errors
 */
$arrLang['error']['notInitialized']     = 'Importer ist noch nicht initialisiert.';
$arrLang['error']['error']              = 'Fehler: %s';
$arrLang['error']['tableDoNotExist']    = 'Die Zieltabelle existiert nicht.';
$arrLang['error']['tableFieldsDiffer']  = 'Die Felder vom Ziel und Quelle unterscheiden sich.';
$arrLang['error']['noIdentifierFields'] = 'Identifikatorfelder nicht gesetzt.';
$arrLang['error']['successfulImport']   = 'Erfolgreicher Import von %s Einträgen';
$arrLang['error']['emptyFile']          = 'Daten zum Importieren nicht vorhanden.';
$arrLang['error']['errorImport']        = 'Fehlerhafter Import von %s Einträgen. Fehler: %s';