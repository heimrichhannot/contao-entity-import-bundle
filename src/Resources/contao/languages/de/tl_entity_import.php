<?php

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_entity_import']['title']        = ['Titel', 'Geben Sie hier den Titel des Imports ein.'];
$GLOBALS['TL_LANG']['tl_entity_import']['type']         = ['Typ', 'Wählen Sie hier den Typ des Imports aus.'];
$GLOBALS['TL_LANG']['tl_entity_import']['type'][\HeimrichHannot\EntityImportBundle\Importer\ImporterSourceInterface::ENTITY_IMPORT_CONFIG_TYPE_DATABASE]
                                                        = 'Datenbank';
$GLOBALS['TL_LANG']['tl_entity_import']['type'][\HeimrichHannot\EntityImportBundle\Importer\ImporterSourceInterface::ENTITY_IMPORT_CONFIG_TYPE_FILE]
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

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_entity_import']['new']    = ['Neuer Import', 'Einen neuen Import anlegen'];
$GLOBALS['TL_LANG']['tl_entity_import']['show']   = ['Import-Details', 'Details von Import ID %s anzeigen'];
$GLOBALS['TL_LANG']['tl_entity_import']['edit']   = ['Import bearbeiten', 'Import ID %s bearbeiten'];
$GLOBALS['TL_LANG']['tl_entity_import']['copy']   = ['Import kopieren', 'Import ID %s duplizieren'];
$GLOBALS['TL_LANG']['tl_entity_import']['delete'] = ['Import löschen', 'Import ID %s löschen'];