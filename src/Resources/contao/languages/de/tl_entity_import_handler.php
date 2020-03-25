<?php

$arrLang = &$GLOBALS['TL_LANG']['tl_entity_import_handler'];

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

/**
 * Fields
 */
$arrLang['title']       = ['Titel', 'Geben Sie hier den Titel des Imports ein.'];
$arrLang['targetTable'] = ['Zieltabelle', 'Wählen Sie hier die Tabelle, in die importiert werden soll.'];
$arrLang['dryRun'] = ['Testlauf', 'Testlauf ausführen'];
$arrLang['mergeTable'] =  [
    'Beim Importieren zusammenführen (Merge)',
    'Wählen Sie diese Option, wenn beim Importieren bereits bestehende Datensätze mit den zu importierenden Datensätzen zusammengeführt werden sollen.'
];
