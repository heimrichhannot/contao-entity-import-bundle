<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

$lang = &$GLOBALS['TL_LANG']['MSC']['entityImport'];

$lang['exceptionEmailSubject'] = 'An error has occurred while executing the importer "%s"';
$lang['dbConnectionError'] = 'Unable to connect to database with the given login data.<br><br><strong>Error: "%s"</strong>';
$lang['presetConfirm'] = 'ATTENTION: Setting the preset overwrites your current field mapping. Do you really want to continue?';
$lang['column'] = 'Column';

$lang['accessTokenSavedSuccessfullyWithoutDate'] = 'The access token was requested and saved.';
$lang['accessTokenRequestError'] = 'The access token could not be requested: %s';
$lang['serviceConnectionError'] = 'No posts could be retrieved from the service. Please check the credentials.<br><br>Error: %s';
$lang['serviceNoPostsFound'] = 'The credentials seem to be correct, but the service returned no posts. Has nothing been published yet?';
$lang['youTubeChannelIdCouldNotBeRetrieved'] = 'The YouTube channel could not be found.';
$lang['accessTokenRefreshedUntil'] = 'The access token was refreshed. It is valid until: %s.';
$lang['accessTokenRefreshed'] = 'The access token was refreshed.';
$lang['accessTokenRefreshError'] = 'The access token could not be refreshed: %s';
