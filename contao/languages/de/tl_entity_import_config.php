<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

$lang = &$GLOBALS['TL_LANG']['tl_entity_import_config'];

/*
 * Fields
 */
$lang['title'][0] = 'Titel';
$lang['title'][1] = 'Geben Sie hier den Titel des Imports ein.';
$lang['targetTable'][0] = 'Zieltabelle';
$lang['targetTable'][1] = 'Wählen Sie hier die Tabelle, in die importiert werden soll.';
$lang['mergeTable'][0] = 'Beim Importieren zusammenführen (Merge)';
$lang['mergeTable'][1] = 'Wählen Sie diese Option, wenn beim Importieren bereits bestehende Datensätze mit den zu importierenden Datensätzen zusammengeführt werden sollen.';
$lang['deleteBeforeImport'][0] = 'Datensätze vor dem Import löschen';
$lang['deleteBeforeImport'][1] = 'Wählen Sie diese Option wenn vor dem Import vorhandene Datensätze gelöscht werden sollen.';
$lang['deleteBeforeImportWhere'][0] = 'WHERE-Bedingungen für das Löschen';
$lang['deleteBeforeImportWhere'][1] = 'Geben Sie hier SQL-Bedingungen in der Form "pid=27 AND id=1" ein, die für das Löschen von Datensätzen vor jedem Import gelten sollen.';

$lang['mergeIdentifierFields'][0] = 'Identifikationsfelder';
$lang['mergeIdentifierFields'][1] = 'Wählen Sie hier die Felder aus, die für das Auffinden bestehender Datensätze genutzt werden sollen (bspw. E-Mail, ID, Vorname + Nachname, ...).';
$lang['mergeIdentifierFields']['source'][0] = 'Quellfeld';
$lang['mergeIdentifierFields']['source'][1] = 'Wählen Sie hier das Quellfeld aus der externen Quelle aus.';
$lang['mergeIdentifierFields']['target'][0] = 'Feld in Zieltabelle';
$lang['mergeIdentifierFields']['target'][1] = 'Wählen Sie hier ein Feld aus der Zieltabelle aus.';

$lang['mergeIdentifierAdditionalWhere'][0] = 'Zusätzliche WHERE-Bedingung für die Zusammenführung (erhöht Performance)';
$lang['mergeIdentifierAdditionalWhere'][1] = 'Geben Sie hier zusätzlich zu den Identifikationsfelder Bedingungen an, die für die Zusammenführung erfüllt sein müssen.';

$lang['fieldMapping'][0] = 'Feldabbildung';
$lang['fieldMapping'][1] = 'Geben Sie hier die Zuordnung der ausgewählten Felder der Quelle mit den vorhandenen Tabellenspalten.';
$lang['fieldMapping']['columnName'][0] = 'Zielfeld';
$lang['fieldMapping']['columnName'][1] = 'Wählen Sie hier die Spalte der gewählten Tabelle aus.';
$lang['fieldMapping']['valueType'][0] = 'Typ des Wertes';
$lang['fieldMapping']['valueType'][1] = 'Wählen Sie hier den Typ des Wertes aus. Bei dynamisch wird der Wert aus der Feldabbildung der Quelle genommen. Bei statisch wird der Inhalt des Feldes als Wert genommen.';
$lang['fieldMapping']['mappingValue'][0] = 'Quellfeld';
$lang['fieldMapping']['mappingValue'][1] = 'Wählen Sie hier ein Feld aus der Feldabbildung der Quelle aus.';
$lang['fieldMapping']['staticValue'][0] = 'Statischer Wert';
$lang['fieldMapping']['staticValue'][1] = 'Geben Sie hier einen Wert ein. Dieser wird in alle Datensätze eingetragen.';

$lang['fileFieldMapping'][0] = 'Datei-Feldabbildung';
$lang['fileFieldMapping'][1] = 'Geben Sie hier die Zuordnung der ausgewählten Felder der Quelle mit den vorhandenen Tabellenspalten.';
$lang['fileFieldMapping']['mappingField'][0] = 'Quellfeld (siehe Notiz)';
$lang['fileFieldMapping']['mappingField'][1] = 'Wählen Sie hier ein Feld aus der Feldabbildung der Quelle aus. Es kann folgendes enthalten: URL, UUID oder Binärdaten der Datei.';
$lang['fileFieldMapping']['targetField'][0] = 'Zielfeld';
$lang['fileFieldMapping']['targetField'][1] = 'In diesem Feld der Zieltabelle wird eine binäre Referenz (UUID) der Datei gespeichert.';
$lang['fileFieldMapping']['targetFolder'][0] = 'Zielverzeichnis';
$lang['fileFieldMapping']['targetFolder'][1] = 'Wählen Sie hier aus, in welchem Zielverzeichnis die Datei gespeichert werden soll.';
$lang['fileFieldMapping']['namingMode'][0] = 'Benamungsmodus';
$lang['fileFieldMapping']['namingMode'][1] = 'Wählen Sie hier aus, wie der Dateiname generiert werden soll.';
$lang['fileFieldMapping']['filenamePattern'][0] = 'Feldmuster';
$lang['fileFieldMapping']['filenamePattern'][1] = 'Geben Sie hier ein Muster der Form "%title%" ein, welches für die Generierung des Dateinamens genutzt wird (Verkettungen wie "%fieldname1%-%fieldname2%" sind auch möglich).';
$lang['fileFieldMapping']['slugFilename'][0] = 'Dateiname normalisieren (slug)';
$lang['fileFieldMapping']['slugFilename'][1] = 'Wählen Sie diese Option, um den Dateinamen von potentiell problematischen Zeichen zu säubern.';
$lang['fileFieldMapping']['delayAfter'][0] = 'Wartezeit nach HTTP-Request';
$lang['fileFieldMapping']['delayAfter'][1] = 'Geben Sie hier die Zeit in Sekunden ein, die der Importer nach einem HTTP-Request warten soll, um bspw. ein Rate-Limit zu umgehen.';
$lang['fileFieldMapping']['skipIfExisting'][0] = 'Überspringen, wenn vorhanden';
$lang['fileFieldMapping']['skipIfExisting'][1] = 'Wählen Sie diese Option, um einen erneuten Import bei bereits existierender lokalen Datei zu verhindern (Performance).';

$lang['fieldMappingPresets'][0] = 'Felderabbildung aus Vorlage erzeugen';
$lang['fieldMappingPresets'][1] = 'Wählen Sie hier bei Bedarf eine Vorlage aus. ACHTUNG: Dies überschreibt Ihre aktuell gesetzte Felderabbildung!';

$lang['importMode'][0] = 'Importmodus';
$lang['importMode'][1] = 'Wählen Sie hier aus, auf welche Weise importiert werden soll.';

$lang['sortingMode'][0] = 'Sortiermodus';
$lang['sortingMode'][1] = 'Wählen Sie hier aus, auf welche Weise sortiert werden soll.';

$lang['targetSortingField'][0] = 'Sortierfeld';
$lang['targetSortingField'][1] = 'Achtung: Das Feld muss Contaos Sortierlogik entsprechen (Integer als Vielfache von 2).';

$lang['targetSortingOrder'][0] = 'ORDER-Anweisung';
$lang['targetSortingOrder'][1] = 'Geben Sie hier an, wie die Datensätze sortiert werden sollen (Beispiel: title ASC, date DESC).';

$lang['targetSortingContextWhere'][0] = 'WHERE-Kontextbedingungen für die Sortierung';
$lang['targetSortingContextWhere'][1] = 'Sie können hier bei Bedarf eine Bedingung definieren, um den Kontext für die Berechnung der Sortierreihenfolge festzulegen (Beispiel: "pid=3").';

$lang['setDateAdded'][0] = 'Erstelldatum setzen';
$lang['setDateAdded'][1] = 'Wählen Sie diese Option, damit automatisch das Erstelldatum als Unix-Timestamp gesetzt wird.';

$lang['targetDateAddedField'][0] = 'Feld für das Erstelldatum (dateAdded)';
$lang['targetDateAddedField'][1] = 'Wählen Sie hier das Feld aus, in dem der Unix-Timestamp zum Zeitpunkt der Erstellung des Datensatzes gespeichert wird.';

$lang['setTstamp'][0] = 'Datum der letzten Änderung setzen';
$lang['setTstamp'][1] = 'Wählen Sie diese Option, damit automatisch das Datum der letzten Änderung als Unix-Timestamp gesetzt wird.';

$lang['targetTstampField'][0] = 'Zeitstempel-Feld (tstamp)';
$lang['targetTstampField'][1] = 'Wählen Sie hier das Feld aus, in dem der Unix-Timestamp der letzten Änderung gespeichert wird.';

$lang['generateAlias'][0] = 'Alias generieren';
$lang['generateAlias'][1] = 'Wählen Sie diese Option, damit automatisch ein Alias generiert wird.';

$lang['targetAliasField'][0] = 'Alias-Feld (alias)';
$lang['targetAliasField'][1] = 'Wählen Sie hier das Feld aus, in dem der generierte Alias gespeichert wird.';

$lang['aliasFieldPattern'][0] = 'Feldmuster für die Aliasgenerierung (Zielfelder!)';
$lang['aliasFieldPattern'][1] = 'Geben Sie hier ein Zielfeld-Muster der Form "%title%" ein, welches für die Aliasgenerierung genutzt wird (Verkettungen wie "%fieldname1%-%fieldname2%" sind auch möglich).';

$lang['deletionMode'][0] = 'Löschmodus (nach dem Import)';
$lang['deletionMode'][1] = 'Wählen Sie hier aus, auf welche Weise nach dem Import Datensätze gelöscht werden sollen.';

$lang['deletionIdentifierFields'][0] = 'Identifikationsfelder';
$lang['deletionIdentifierFields'][1] = 'Wählen Sie hier die Felder aus, die für das Auffinden bestehender Datensätze genutzt werden sollen (bspw. E-Mail, ID, Vorname + Nachname, ...).';

$lang['targetDeletionAdditionalWhere'][0] = 'Zusätzliche WHERE-Bedingung für das Löschen';
$lang['targetDeletionAdditionalWhere'][1] = 'Geben Sie hier zusätzlich zu den Identifikationsfelder Bedingungen an, die für das Löschen erfüllt sein müssen.';

$lang['targetDeletionWhere'][0] = 'WHERE-Bedingung für das Löschen';
$lang['targetDeletionWhere'][1] = 'Geben Sie hier Bedingungen an, die für das Löschen erfüllt sein müssen.';

$lang['useCron'][0] = 'Cronjob/Command nutzen';
$lang['useCron'][1] = 'Wählen Sie diese Option, um den Importer per Cronjob oder Command auszulösen.';
$lang['cronInterval'][0] = 'Cronjob-Interval';
$lang['cronInterval'][1] = 'Wählen Sie hier das Interval aus, in dem der Import ausgeführt werden soll.';
$lang['cronDomain'][0] = 'Domainname';
$lang['cronDomain'][1] = 'Geben Sie hier die Domain ein, unter der der Cronjob ausgeführt wird.';
$lang['cronLanguage'][0] = 'Sprache';
$lang['cronLanguage'][1] = 'Wählen Sie hier die Sprache aus, mit der der Cronjob ausgeführt wird.';
$lang['usePoorMansCron'][0] = 'Als Poor-Man\'s-Cronjob nutzen';
$lang['usePoorMansCron'][1] = 'Wählen Sie diese Option, um den Importer per Poor-Man\'s-Cronjob auszulösen.';

$lang['addDcMultilingualSupport'][0] = 'Unterstützung für DC_Multilingual hinzufügen';
$lang['addDcMultilingualSupport'][1] = 'Wählen Sie diese Option, wenn es sich bei der Zieltabelle um eine Contao-Tabelle mit Unterstützung für DC_Multilingual handelt.';

$lang['addCategoriesSupport'][0] = 'Unterstützung für heimrichhannot/contao-categories-bundle hinzufügen';
$lang['addCategoriesSupport'][1] = 'Wählen Sie diese Option, um entsprechende Felder zu beachten.';

$lang['addChangeLanguageSupport'][0] = 'Unterstützung für terminal42/contao-changelanguage hinzufügen (ACHTUNG: Erklärung lesen!)';
$lang['addChangeLanguageSupport'][1] = 'ACHTUNG: Der Importer der Entität in der Fallback-Sprache muss vor diesem Importer laufen (Cronjob-Reihenfolge!). Wählen Sie diese Option, um das languageMain-Feld korrekt zu migrieren.';

$lang['changeLanguageTargetExternalIdField'][0] = 'ID-Feld zum Auffinden der importierten Entität';
$lang['changeLanguageTargetExternalIdField'][1] = 'Wählen Sie hier das Feld, welches die ID des importierten Datensatzes enthält.';

$lang['addDraftsSupport'][0] = 'Unterstützung für heimrichhannot/contao-drafts-bundle hinzufügen';
$lang['addDraftsSupport'][1] = 'Wählen Sie diese Option, um entsprechende Felder zu beachten.';

$lang['addSkipFieldsOnMerge'][0] = 'Bei der Zusammenführung zu überspringende Felder hinzufügen';
$lang['addSkipFieldsOnMerge'][1] = 'Wählen Sie diese Option, wenn beim Zusammenführen (Merge) Felder in der Datenbank nicht überschrieben werden sollen.';

$lang['skipFieldsOnMerge'][0] = 'Felder';
$lang['skipFieldsOnMerge'][1] = 'Wählen Sie hier die Felder aus, die beim Zusammenführen nicht überschrieben werden sollen.';

$lang['overrideErrorNotificationEmail'][0] = 'E-Mail-Adresse für Fehlermeldung überschreiben';
$lang['overrideErrorNotificationEmail'][1] = 'Wählen Sie diese Option, wenn Sie die Email, an die die Fehlermeldung geschickt wird anpassen möchten. Standardmäßig wird die Benachrichtigung an den System-Admin verschickt.';

$lang['errorNotificationEmail'][0] = 'E-Mail-Adresse';
$lang['errorNotificationEmail'][1] = 'Tragen Sie hier die E-Mail-Adresse ein, an die die Fehlermeldung verschickt werden soll.';

$lang['useCacheForQuickImporters'][0] = 'Datenbank-Cache für Schnell-Importer nutzen (aktuell nur CSV-Quelle; Datenbank danach aktualisieren!)';
$lang['useCacheForQuickImporters'][1] = 'Aktivieren Sie diese Option, wenn sich in Ihrer Quelle sehr viele Objekte befinden. Sie müssen nach der Aktivierung die Datenbank aktualisieren und vorab ggf. den Symfony-Cache löschen.';

$lang['useCronInWebContext'][0] = 'Import auch im Web-Kontext per Cronjob ausführen (siehe README)';
$lang['useCronInWebContext'][1] = 'Aktivieren Sie diese Option, der Import auch im Web-Kontext per Cronjob. Dies bietet sich vor allem an, wenn der "normale" Import zu viel Speicher verbraucht.';

$lang['state'][0] = 'Importstatus';
$lang['state'][1] = 'In diesem Feld wird der Status des Imports gespeichert.';

$lang['importStarted'][0] = 'Import gestartet am';
$lang['importStarted'][1] = 'In diesem Feld wird Zeitpunkt gespeichert, an dem der Import gestartet wurde.';

$lang['importFinished'][0] = 'Import beendet am';
$lang['importFinished'][1] = 'In diesem Feld wird Zeitpunkt gespeichert, an dem der Import beendet wurde.';

$lang['importProgressTotal'][0] = 'Zu importierende Objekte';
$lang['importProgressTotal'][1] = 'In diesem Feld wird gespeichert, wie viele Objekte insgesamt importiert werden sollen.';

$lang['importProgressCurrent'][0] = 'Bereits importierte Objekte';
$lang['importProgressCurrent'][1] = 'In diesem Feld wird gespeichert, wie viele Objekte bereits importiert wurden.';

$lang['importProgressSkipped'][0] = 'Übersprungene Objekte';
$lang['importProgressSkipped'][1] = 'In diesem Feld wird gespeichert, wie viele Objekte übersprungen wurden.';

$lang['importProgressResult'][0] = 'Ergebnis';
$lang['importProgressResult'][1] = 'In diesem Feld wird das Ergebnis der gespeichert.';

/*
 * Reference
 */
$lang['reference'] = [
    'importMode' => [
        'insert' => 'Beim Importieren neue Datensätze anlegen',
        'merge' => 'Beim Importieren Datensätze zusammenführen (Merge)',
    ],
    'sortingMode' => [
        \HeimrichHannot\EntityImportBundle\DataContainer\EntityImportConfigContainer::SORTING_MODE_TARGET_FIELDS => 'Nach Zielfeld(ern) sortieren',
    ],
    'deletionMode' => [
        \HeimrichHannot\EntityImportBundle\DataContainer\EntityImportConfigContainer::DELETION_MODE_MIRROR => 'In der Quelle nicht mehr vorhandene Datensätze löschen (Spiegelung)',
        \HeimrichHannot\EntityImportBundle\DataContainer\EntityImportConfigContainer::DELETION_MODE_TARGET_FIELDS => 'Nach Zielfeldbedingungen löschen',
    ],
    'cronInterval' => [
        'minutely' => 'Minütlich',
        'hourly' => 'Stündlich',
        'daily' => 'Täglich',
        'weekly' => 'Wöchentlich',
        'monthly' => 'Monatlich',
    ],
    'valueType' => [
        'source_value' => 'dynamisch',
        'static_value' => 'statisch',
    ],
    'namingMode' => [
        'field_pattern' => 'aus Quellfeld-Werten',
        'random_md5' => 'zufällige MD5-Zeichenfolge',
    ],
    \HeimrichHannot\EntityImportBundle\DataContainer\EntityImportConfigContainer::STATE_READY_FOR_IMPORT => 'Bereit für den Import',
    \HeimrichHannot\EntityImportBundle\DataContainer\EntityImportConfigContainer::STATE_SUCCESS => 'Import erfolgreich',
    \HeimrichHannot\EntityImportBundle\DataContainer\EntityImportConfigContainer::STATE_FAILED => 'Import fehlgeschlagen',
    'importProgressDescription' => 'Der Importvorgang wurde gestartet. Je nach zu importierender Datenmenge kann der Import einige Minuten in Anspruch nehmen. Bitte haben Sie einen Moment Geduld...',
];

/*
 * Messages
 */
$lang['importConfirm'] = 'Soll der Import ID %s wirklich durchgeführt werden? Je nach Datenmenge kann der Import einige Minuten in Anspruch nehmen.';

/*
 * Errors
 */
$lang['error']['errorMessage'] = 'Beim Importieren ist ein Fehler aufgetreten: %s.';
$lang['error']['tableDoesNotExist'] = 'Die Zieltabelle existiert nicht.';
$lang['error']['tableFieldsDiffer'] = 'Die Felder vom Quelle und Ziel unterscheiden sich.';
$lang['error']['noIdentifierFields'] = 'Die Identifikatorfelder wurden nicht gesetzt.';
$lang['error']['successfulImport'] = 'Es wurden %s Einträge importiert bzw. aktualisiert (benötigte Zeit: %ss, max. benötigter Speicher: %s).';
$lang['error']['emptyFile'] = 'Es wurden keine Daten zum Importieren gefunden.';
$lang['error']['errorImport'] = 'Beim Importieren ist ein Fehler aufgetreten.';
$lang['error']['error'] = 'Fehler';
$lang['error']['delimiter'] = 'Das Feld-Trennzeichen für die CSV-Quelle wurde nicht gesetzt.';
$lang['error']['enclosure'] = 'Das Text-Trennzeichen für die CSV-Quelle wurde nicht gesetzt.';
$lang['error']['escape'] = 'Escape für csv ist nicht definiert.';
$lang['error']['filePathNotProvided'] = 'Der Pfad zur Datei wurde nicht gefunden.';
$lang['error']['modeNotSet'] = 'Der Import Modus ist nicht gesetzt.';
$lang['error']['configFieldMapping'] = 'Die Feldabbildung des Importes ist nicht gesetzt';

/*
 * Backend Modules
 */
$lang['headline'] = 'Import ID %s';
$lang['label'] = 'Klicken Sie &quot;Import ausführen&quot;, um den Importprozess zu starten.';

/*
 * Legends
 */
$lang['general_legend'] = 'Allgemeines';
$lang['mapping_legend'] = 'Felderabbildung';
$lang['fields_legend'] = 'Felderbearbeitung';
$lang['file_mapping_legend'] = 'Felderabbildung (Dateien)';
$lang['sorting_legend'] = 'Sortierung';
$lang['deletion_legend'] = 'Löschen';
$lang['misc_legend'] = 'Verschiedenes';
$lang['cron_legend'] = 'Cronjob/Command';
$lang['error_legend'] = 'Fehlerbehandlung';

/*
 * Buttons
 */
$lang['new'][0] = 'Neuer Importer';
$lang['new'][1] = 'Einen neuen Importer anlegen';
$lang['show'][0] = 'Importer-Details';
$lang['show'][1] = 'Details von Importer ID %s anzeigen';
$lang['edit'][0] = 'Importer bearbeiten';
$lang['edit'][1] = 'Importer ID %s bearbeiten';
$lang['copy'][0] = 'Importer kopieren';
$lang['copy'][1] = 'Importer ID %s duplizieren';
$lang['delete'][0] = 'Importer löschen';
$lang['delete'][1] = 'Importer ID %s löschen';
$lang['dryRun'][0] = 'Testlauf';
$lang['dryRun'][1] = 'Testlauf ausführen';
$lang['import'][0] = 'Import ausführen';
$lang['import'][1] = 'Import ID %s ausführen';
