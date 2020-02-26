<?php

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_entity_import']['title']        = ['Titel', 'Geben Sie hier den Titel des Imports ein.'];
$GLOBALS['TL_LANG']['tl_entity_import']['type']         = ['Typ', 'Wählen Sie hier den Typ des Imports aus.'];
$GLOBALS['TL_LANG']['tl_entity_import']['fileType']     = ['Typ', 'Wählen Sie hier den Typ der Datei aus.'];
$GLOBALS['TL_LANG']['tl_entity_import']['JSONFileContent']     = ['JSON Ansicht ', 'Hier wird das erste Objekt der ausgewählten JSON Datei dargestellt.'];
$GLOBALS['TL_LANG']['tl_entity_import']['sourceType']   = ['Quelle', 'Wählen Sie hier die Art der Dateiquelle aus.'];
$GLOBALS['TL_LANG']['tl_entity_import']['sourceType'][\HeimrichHannot\EntityImportBundle\DataContainer\EntityImportContainer::SOURCE_TYPE_HTTP]
                                                        = 'HTTP';
$GLOBALS['TL_LANG']['tl_entity_import']['sourceType'][\HeimrichHannot\EntityImportBundle\DataContainer\EntityImportContainer::SOURCE_TYPE_CONTAO_FILE_SYSTEM]
                                                        = 'Contao Dateiverwaltung';
$GLOBALS['TL_LANG']['tl_entity_import']['sourceType'][\HeimrichHannot\EntityImportBundle\DataContainer\EntityImportContainer::SOURCE_TYPE_ABSOLUTE_PATH]
                                                        = 'Absoluter Pfad';
$GLOBALS['TL_LANG']['tl_entity_import']['sourceUrl']    = ['Url', 'Geben Sie hier die URL zur Datei ein.'];
$GLOBALS['TL_LANG']['tl_entity_import']['filePath']     = ['Absoluter Dateipfad', 'Geben Sie hier einen absoluten Dateipfad auf dem Server ein.'];
$GLOBALS['TL_LANG']['tl_entity_import']['fileSRC']      = ['Datei wählen oder hochladen', 'Wählen Sie hier eine vorhandene Datei, oder laden Sie eine neue Datei hoch.'];
$GLOBALS['TL_LANG']['tl_entity_import']['type'][\HeimrichHannot\EntityImportBundle\DataContainer\EntityImportContainer::TYPE_DATABASE]
                                                        = 'Datenbank';
$GLOBALS['TL_LANG']['tl_entity_import']['type'][\HeimrichHannot\EntityImportBundle\DataContainer\EntityImportContainer::TYPE_FILE]
                                                        = 'Datei';
$GLOBALS['TL_LANG']['tl_entity_import']['dbDriver']     = ['Treiber', 'Wählen Sie hier den Datenbanktreiber aus.'];
$GLOBALS['TL_LANG']['tl_entity_import']['dbHost']       = ['Host', 'Geben Sie hier die Adresse des Datenbankhosts ein.'];
$GLOBALS['TL_LANG']['tl_entity_import']['dbUser']       = ['Nutzer', 'Geben Sie hier einen berechtigten Datenbanknutzer ein.'];
$GLOBALS['TL_LANG']['tl_entity_import']['dbPass']       = ['Passwort', 'Geben Sie hier das Passwort des berechtigten Datenbanknutzers ein.'];
$GLOBALS['TL_LANG']['tl_entity_import']['dbDatabase']   = ['Datenbankname', 'Geben Sie hier den Namen der Datenbank ein.'];
$GLOBALS['TL_LANG']['tl_entity_import']['dbPconnect']   = ['PConnect', 'Wählen Sie hier, ob Sie PConnect nutzen möchten.'];
$GLOBALS['TL_LANG']['tl_entity_import']['dbCharset']    = ['Zeichensatz', 'Wählen Sie hier den gewünschten Zeichensatz aus.'];
$GLOBALS['TL_LANG']['tl_entity_import']['dbSocket']     = ['Socket', 'Geben Sie hier einen Socket ein.'];
$GLOBALS['TL_LANG']['tl_entity_import']['externalUrl']  = ['Url', 'Tragen Sie hier die Url ein, von der die Daten importiert werden sollen.'];


/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_entity_import']['title_legend']     = 'Titel';
$GLOBALS['TL_LANG']['tl_entity_import']['db_legend']        = 'Datenbankeinstellungen';
$GLOBALS['TL_LANG']['tl_entity_import']['external_legend']  = 'Externe Quelle';
$GLOBALS['TL_LANG']['tl_entity_import']['file_legend']      = 'Datei';

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_entity_import']['new']    = ['Neuer Import', 'Einen neuen Import anlegen'];
$GLOBALS['TL_LANG']['tl_entity_import']['show']   = ['Import-Details', 'Details von Import ID %s anzeigen'];
$GLOBALS['TL_LANG']['tl_entity_import']['edit']   = ['Import bearbeiten', 'Import ID %s bearbeiten'];
$GLOBALS['TL_LANG']['tl_entity_import']['copy']   = ['Import kopieren', 'Import ID %s duplizieren'];
$GLOBALS['TL_LANG']['tl_entity_import']['delete'] = ['Import löschen', 'Import ID %s löschen'];