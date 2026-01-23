<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

$lang = &$GLOBALS['TL_LANG']['MSC']['entityImport'];

$lang['exceptionEmailSubject'] = 'Beim Ausführen des Importers "%s" ist ein Fehler aufgetreten';
$lang['dbConnectionError'] = 'Mit den eingegebenen Zugangsdaten konnte keine Verbindung zu einer Datenbank hergestellt werden.<br><br><strong>Fehler: "%s"</strong>';
$lang['presetConfirm'] = 'ACHTUNG: Wenn Sie die Vorlage setzen, wird Ihre bisherige Felderabbilung überschrieben. Möchten Sie wirklich fortfahren?';
$lang['column'] = 'Spalte';

$lang['uploadedAt'] = 'Hochgeladen';
$lang['postedAt'] = 'Gepostet';
$lang['accessTokenSavedSuccessfully'] = 'Das Access-Token wurde erfolgreich abgerufen und gespeichert. Es ist gültig bis: %s.';
$lang['accessTokenSavedSuccessfullyWithoutDate'] = 'Das Access-Token wurde erfolgreich abgerufen und gespeichert.';
$lang['accessTokenExpiredEmailSubject'] = 'Das Access-Token Ihrer Facebook-Newsroom-Quelle ist abgelaufen';
$lang['accessTokenExpirationEmailBody'] = "Klicken Sie auf den folgenden Link, um ein neues Access-Token anzufordern:\n\n%s";
$lang['serviceConnectionError'] = 'Es konnten keine Beiträge vom Dienst bezogen werden. Bitte prüfen Sie die Zugangsdaten.<br><br>Fehler: %s';
$lang['serviceNoPostsFound'] = 'Obwohl die Zugangsdaten korrekt zu sein scheinen, konnten keine Beiträge vom Dienst bezogen werden. Wurden noch keine Beiträge veröffentlicht?';
$lang['youTubeChannelIdCouldNotBeRetrieved'] = 'Die Channel-ID konnte nicht aus dem Nutzer bezogen werden.';
$lang['entityImportSourceNotFound'] = 'Die Importquelle mit der ID %s konnte nicht gefunden werden.';
$lang['goToVideo'] = 'Zum Video';
$lang['goToTweet'] = 'Zum Tweet';
$lang['goToPost'] = 'Zum Post';
$lang['emailLinkedInSubject'] = 'LinkedIn Access Token Erinnerung';
$lang['emailLinkedInHtml'] = 'Hallo Admin, <br><br>Der LinkedIn Access Token auf der Webseite %s für den Account %s muss neu generiert werden. Melde dich dafür im Contao Backend an, rufe den Import des LinkedIn Kontos auf, betätige den Button "Access Token anfornden" aus und speichere. Anschließend musst du nur noch der App den Zugriff erlauben und der Access Token wird neu generiert. Dieser Vorgang muss jedes Jahr wiederholt werden.';
$lang['emailLinkedInSubjectError'] = 'Refresh LinkedIn Access Token fehlgeschlagen';
$lang['emailLinkedInHtmlError'] = 'Hallo Admin, <br><br>Der LinkedIn Access Token auf der Webseite %s für den Account %s muss neu generiert werden. Ein Refresh-Versuch wurde fehlgeschlagen, bitte überprüfen.<br>Fehler:<br>%s';
