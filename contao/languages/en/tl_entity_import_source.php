<?php

use HeimrichHannot\EntityImportBundle\EventListener\DataContainer\EntityImportSourceContainer;

$lang = &$GLOBALS['TL_LANG']['tl_entity_import_source'];

/**
 * Fields
 */
$lang['title'][0]    = 'Title';
$lang['title'][1]    = 'Enter the title of the source here.';
$lang['type'][0]     = 'Type';
$lang['type'][1]     = 'Select the type of import here.';
$lang['fileType'][0] = 'File type';
$lang['fileType'][1] = 'Select the type of file here.';

$lang['csvHeaderRow'][0] = 'Header data record';
$lang['csvHeaderRow'][1] = 'The first record contains the column names.';
$lang['csvSkipEmptyLines'][0] = 'Skip empty lines';
$lang['csvSkipEmptyLines'][1] = 'Should empty lines be skipped?';
$lang['csvDelimiter'][0] = 'Field delimiter';
$lang['csvDelimiter'][1] = 'Enter the field delimiter here.';
$lang['csvEnclosure'][0] = 'Enclosure';
$lang['csvEnclosure'][1] = 'Enter the enclosure here.';
$lang['csvEscape'][0]    = 'Escape';
$lang['csvEscape'][1]    = 'Enter the separator for converting separator-separated field values. If the corresponding check mark is set in the field mapping, values like "1;4;5" are transformed to a serialized array.';

$lang['pathToDataArray'][0]             = 'Path to the data';
$lang['pathToDataArray'][1]             = 'Enter the path of the data in the file here. Is necessary if the data to be imported is not in the first level.';
$lang['fieldMapping'][0]                = 'Field mapping';
$lang['fieldMapping'][1]                = 'Enter the assignment of the fields from the source here.';
$lang['fieldMapping']['name'][0]        = 'Name';
$lang['fieldMapping']['name'][1]        = 'Enter the name of the value for further processing here.';
$lang['fieldMapping']['valueType'][0]   = 'Type of the value';
$lang['fieldMapping']['valueType'][1]   = 'Pick the type of the value here. For dynamic, the value is taken from the record. With static, the content of the field is taken as the value.';
$lang['fieldMapping']['sourceValue'][0] = 'Value from the source';
$lang['fieldMapping']['sourceValue'][1] = 'Enter the location of the value in the source here.';
$lang['fieldMapping']['staticValue'][0] = 'Static value';
$lang['fieldMapping']['staticValue'][1] = 'Enter the value that is to be entered immediately in all data records.';

$lang['fileContent'][0] = 'File preview';
$lang['fileContent'][1] = 'Here you can see the contents of the selected file. The entire file is not displayed.';

$lang['retrievalType'][0] = 'Source';
$lang['retrievalType'][1] = 'Select the type of file source here.';

$lang['retrievalType'][EntityImportSourceContainer::RETRIEVAL_TYPE_HTTP]               = 'HTTP';
$lang['retrievalType'][EntityImportSourceContainer::RETRIEVAL_TYPE_CONTAO_FILE_SYSTEM] = 'Contao file management';
$lang['retrievalType'][EntityImportSourceContainer::RETRIEVAL_TYPE_ABSOLUTE_PATH]      = 'Absolute path';
$lang['type'][EntityImportSourceContainer::TYPE_DATABASE]                              = 'Database';
$lang['type'][EntityImportSourceContainer::TYPE_FILE]                                  = 'File';
$lang['type'][EntityImportSourceContainer::TYPE_YOUTUBE]                               = 'YouTube';
$lang['type'][EntityImportSourceContainer::TYPE_INSTAGRAM]                             = 'Instagram';
$lang['type'][EntityImportSourceContainer::TYPE_FACEBOOK]                              = 'Facebook page';
$lang['type'][EntityImportSourceContainer::TYPE_LINKEDIN]                              = 'LinkedIn company page';

$lang['sourceUrl'][0]            = 'Url';
$lang['sourceUrl'][1]            = 'Enter the URL to the file here.';
$lang['absolutePath'][0]         = 'Absolute file path';
$lang['absolutePath'][1]         = 'Enter an absolute file path on the server here.';
$lang['fileSRC'][0]              = 'Select or upload file';
$lang['fileSRC'][1]              = 'Select an existing file here, or upload a new file.';
$lang['dbDriver'][0]             = 'Driver';
$lang['dbDriver'][1]             = 'Select the database driver here.';
$lang['dbHost'][0]               = 'Host';
$lang['dbHost'][1]               = 'Enter the address of the database host here.';
$lang['dbUser'][0]               = 'User';
$lang['dbUser'][1]               = 'Enter an authorized database user here.';
$lang['dbPass'][0]               = 'Password';
$lang['dbPass'][1]               = 'Enter the password of the authorized database user here.';
$lang['dbDatabase'][0]           = 'Database name';
$lang['dbDatabase'][1]           = 'Enter the name of the database here.';
$lang['dbPconnect'][0]           = 'PConnect';
$lang['dbPconnect'][1]           = 'Select here whether you want to use PConnect.';
$lang['dbCharset'][0]            = 'Character set';
$lang['dbCharset'][1]            = 'Select the desired character set here.';
$lang['dbSocket'][0]             = 'Socket';
$lang['dbSocket'][1]             = 'Enter a socket here.';
$lang['externalUrl'][0]          = 'Url';
$lang['externalUrl'][1]          = 'Enter the Url from which the data is to be imported here.';
$lang['httpMethod'][0]           = 'HTTP-Method';
$lang['httpMethod'][1]           = 'Select the HTTP method with which the file should be accessed.';
$lang['httpAuth'][0]             = 'Authentication';
$lang['httpAuth'][1]             = 'Enter the data for the authentication here.';
$lang['httpAuth']['username'][0] = 'Username';
$lang['httpAuth']['username'][1] = 'Enter your user name here.';
$lang['httpAuth']['password'][0] = 'Password';
$lang['httpAuth']['password'][1] = 'Enter your password here.';

/**
 * Reference
 */
$lang['instagramApi'][0] = 'Instagram API';
$lang['instagramApi'][1] = 'Choose the API of the access token: Instagram API with Facebook Login (app ID, app secret and Instagram user ID) or Instagram API with Instagram Login (access token only; it is refreshed automatically before it expires).';

$lang['apiKey'][0] = 'API key';
$lang['apiKey'][1] = 'Enter the API key you got from the service.';
$lang['youtubeMode'][0] = 'Find the channel by';
$lang['youtubeMode'][1] = 'How to find the YouTube channel.';
$lang['youtubeChannel'][0] = 'YouTube channel ID';
$lang['youtubeChannel'][1] = 'The ID from the channel URL (https://www.youtube.com/channel/<...>), starting with "UC".';
$lang['youtubeHandle'][0] = 'YouTube handle';
$lang['youtubeHandle'][1] = 'The handle of the channel, e.g. "@bahnland" (https://www.youtube.com/@<...>).';
$lang['youtubeUsername'][0] = 'YouTube user name';
$lang['youtubeUsername'][1] = 'The legacy user name from the user URL (https://www.youtube.com/user/<...>). Newer channels have none; use the handle then.';

$lang['instagramAccessToken'][0] = 'Access token';
$lang['instagramAccessToken'][1] = 'The Instagram access token is stored in this field.';
$lang['instagramUserID'][0] = 'Instagram user ID';
$lang['instagramUserID'][1] = 'The ID of the Instagram business account. Leave it empty if only one of your Facebook pages is connected to an Instagram account: requesting the access token fills it in.';
$lang['accessTokenExpiration'][0] = 'Access token expires';
$lang['accessTokenExpiration'][1] = 'When the access token expires.';

$lang['appId'][0] = 'App ID / client ID';
$lang['appId'][1] = 'The ID of the app: Meta app (developers.facebook.com, App settings > Basic) or LinkedIn app (Auth tab, client ID).';
$lang['appSecret'][0] = 'App secret / client secret';
$lang['appSecret'][1] = 'The app secret of the Meta app or the client secret of the LinkedIn app.';
$lang['metaApiVersion'][0] = 'Graph API version';
$lang['metaApiVersion'][1] = 'The version of the Graph API, e.g. "v24.0".';
$lang['metaAccessTokenButton'][0] = 'Request access token';
$lang['metaAccessTokenButton'][1] = 'Save app ID, app secret and Graph API version first. Then log in with a Facebook account that manages the page. In the Meta app, enter this URL as a valid OAuth redirect URI under "Facebook Login > Settings": %s';
$lang['facebookPageId'][0] = 'Facebook page ID';
$lang['facebookPageId'][1] = 'The ID of the page (About > Page transparency). Leave it empty if you manage one page only: requesting the access token fills it in.';
$lang['facebookAccessToken'][0] = 'Page access token';
$lang['facebookAccessToken'][1] = 'Stored when you request it; it does not expire. It stops working if the account that logged in loses its role on the page, changes its password or removes the app.';

$lang['appPageId'][0] = 'LinkedIn organization ID';
$lang['appPageId'][1] = 'The number in the admin URL of the company page (https://www.linkedin.com/company/<ID>/admin/).';
$lang['linkedInVersion'][0] = 'LinkedIn API version';
$lang['linkedInVersion'][1] = 'The version of the LinkedIn API as YYYYMM, e.g. "202610". LinkedIn supports a version for at least a year.';
$lang['numberPosts'][0] = 'Number of posts';
$lang['numberPosts'][1] = 'How many of the latest posts to import (1 to 100).';
$lang['linkedinAccessTokenButton'][0] = 'Request access token';
$lang['linkedinAccessTokenButton'][1] = 'Save client ID and secret first. Then log in with a LinkedIn account that administers the company page. The app needs the Community Management API (scope r_organization_social), and this URL under "Authorized redirect URLs for your app": %s';
$lang['linkedinAccessToken'][0] = 'Access token';
$lang['linkedinAccessToken'][1] = 'Stored when you request it (valid for 60 days). You can also paste a token from LinkedIn\'s token generator.';
$lang['linkedinRefreshTokenExpires'][0] = 'Refresh token expires';
$lang['linkedinRefreshTokenExpires'][1] = 'Until then the access token is refreshed automatically; after that, request a new one.';

$lang['request'] = 'Request';

$lang['refreshAccessToken'][0] = 'Refresh access token now';
$lang['refreshAccessToken'][1] = 'Saves the source and exchanges the access token for a new one with the full lifetime right away (Instagram allows this once the token is at least 24 hours old). A daily cron job refreshes it 30 days before it expires anyway.';

$lang['reference'] = [
    'instagramApi' => [
        'facebook' => 'Instagram API with Facebook Login',
        'instagram' => 'Instagram API with Instagram Login',
    ],
    'posts' => [
        'channel' => 'Channel ID',
        'handle' => 'Handle (@name)',
        'user' => 'User name (legacy)',
    ],
    'valueType'  => [
        'source_value' => 'dynamic',
        'static_value' => 'static',
    ],
    'httpMethod' => [
        'get'  => 'GET',
        'post' => 'POST',
    ],
];

/**
 * Legends
 */
$lang['title_legend']    = 'Title';
$lang['config_legend']   = 'Configuration';
$lang['db_legend']       = 'Database settings';
$lang['external_legend'] = 'External source';
$lang['file_legend']     = 'File';

/**
 * Buttons
 */
$lang['new'][0]        = 'New import source';
$lang['new'][1]        = 'Creating a new import source';
$lang['show'][0]       = 'Import source-Details';
$lang['show'][1]       = 'Show details of import source ID %s';
$lang['editheader'][0] = 'Edit import source';
$lang['editheader'][1] = 'Edit import source ID %s';
$lang['copy'][0]       = 'Copy import source';
$lang['copy'][1]       = 'Duplicate import source ID %s';
$lang['delete'][0]     = 'Delete import source';
$lang['delete'][1]     = 'Delete import source ID %s';
