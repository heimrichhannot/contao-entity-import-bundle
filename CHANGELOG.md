# Changelog

All notable changes to this project will be documented in this file.

## [0.22.1] - 2022-02-14

- Fixed: cache contract deps

## [0.22.0] - 2022-02-14

- Fixed: config for symfony 5+
- Added: support for contao 4.13

## [0.21.6] - 2021-10-05

- Fixed: http caching issue
- Fixed: database issue

## [0.21.5] - 2021-09-24

- Fixed: database issue

## [0.21.4] - 2021-09-15

- Fixed: redirect issue
- Fixed: type hint issues (php 7.4+)
- Changed: file system cache to new adapter
- Changed: cache directory to var/cache/{env}

## [0.21.3] - 2021-09-15

- Fixed: constructor in `DatabaseSource`

## [0.21.2] - 2021-09-15

- Changed: Enhanced error/message handling in importer (see README)
- Added: importer object to the relevant events
- Fixed: symfony 5 compatbility (event dispatch)
- Changed: symfony version costraint to `^5.2`

## [0.21.1] - 2021-09-14

- Fixed: progress bar issue

## [0.21.0] - 2021-09-14

- Added: support for using command import even in the web context (including a progress bar)
- Removed: contao 4.4 support -> 4.9+ now
- Changed: refactored yml files and service classes

## [0.20.4] - 2021-09-13

- Added: command input/output is now passed to the importer
- Added: progress bar in command mode
- Added: `mergeIdentifierAdditionalWhere` for importer config -> enhance performance for db merge cache creation
- Changed: refactored error/success message handling -> importers now not only return bool but an array containing more context

## [0.20.3] - 2021-09-09

- Fixed: `trim()` is only applied to item data if the data is of type "string"

## [0.20.2] - 2021-09-07

- Added: cache for csv quick importers (activate in importer config and carefully read the field caption)
- Fixed: error message for import command
- Fixed: style fixes for quick importers in the backend

## [0.20.1] - 2021-09-02

- Added: memory usage in success message
- Fixed: sql escape issues for where clauses
- Fixed: performance optimzation

## [0.20.0] - 2021-09-02

- Added: new event `AfterCsvFileSourceGetRowEvent`
- Removed: utf encoding/decoding in csv file source (**BC-Break**)
- Added: `trim()` for all imported data (in extremely rare cases this might be a **BC-Break**)

## [0.19.4] - 2021-09-02

- Fixed: placeholders for sources
- Fixed: maxlength issues in sources

## [0.19.3] - 2021-09-01

- Added: php8 support

## [0.19.2] - 2021-07-30

- Added: `csvHeaderRow` for quick importers
- Fixed: utf-8 encoding of csv files

## [0.19.1] - 2021-06-22

- fixed categories import (now associations are used instead of the record's field)

## [0.19.0] - 2021-06-22

- added optional support for terminal42/contao-changelanguage

## [0.18.13] - 2021-06-21

- fixed missing legend localization
- fixed missing ctable association

## [0.18.12] - 2021-05-17

- fixed utf8 encoding function to mb_convert_encoding in CSVFileSource

## [0.18.11] - 2021-03-17

- readded customizable email address to send errorNotification to

## [0.18.10] - 2021-03-17

- added removal of categories while importing if these are removed from reference entities

## [0.18.9] - 2021-01-21

- added customizable email adress to send errorNotification to
- added event in `Importer::executeImport`

## [0.18.8] - 2020-12-01

- fixed utf-8 issues
- added checks for not null

## [0.18.7] - 2020-11-12

- fixed quick import

## [0.18.6] - 2020-11-12

- fixed check for empty csv file sources

## [0.18.5] - 2020-11-12

- added check for empty csv file sources

## [0.18.4] - 2020-10-20

- fixed js issue

## [0.18.3] - 2020-10-08

- fixed translation issue
- fixed quick importer empty source issue
- fixed quick importer style issue

## [0.18.2] - 2020-09-29

- added readme for quick importers

## [0.18.1] - 2020-09-29

- fixed quick importer bug

## [0.18.0] - 2020-09-29

- added `csvSkipEmptyLines` (thanks to SGehle)

## [0.17.0] - 2020-09-29

- added quick importers for editors

## [0.16.0] - 2020-09-14

- added `usePoorMansCron` in order to allow defining domain and language for non-poor-man's-crons (commands)

## [0.15.0] - 2020-07-17

- added RSS import source
- fixed localizations
- fixed preview issue for HTTP files sources

## [0.14.0] - 2020-07-14

- added fields `cronLanguage` and `cronDomain` in order to pass context to a command call

## [0.13.0] - 2020-07-06

- moved cache to `AbstractSource` so that not only file sources can use it
- added `skipOnExisting` option for `fileFieldMapping`
- fixed stopwatch to contain also source generation

## [0.11.0] - 2020-07-06

- added (optional) support for draft bundle
- added mapping to `AfterItemImportEvent`

## [0.10.2] - 2020-07-06

- fixed delimiter issue (https://github.com/heimrichhannot/contao-entity-import-bundle/pull/1, thanks to SGehle)

## [0.10.1] - 2020-06-22

- removed test field

## [0.10.0] - 2020-06-22

- added file import
- added event `BeforeFileImportEvent` for manipulation file path and/or folder path

## [0.9.0] - 2020-06-19

- fixed localizations
- fixed usability issues
- fixed `SourceFactoryCreateSourceEvent` to contain the source model
- refactoring in `Source`

## [0.8.2] - 2020-06-15

- fixed usage of `deleteBeforeImportWhere` in deleteBeforeImport

## [0.8.1] - 2020-06-12

- fixed input value for `skipFieldsOnMerge`
- added language to new fields

## [0.8.0] - 2020-06-10

- added skip fields for merge

## [0.7.1] - 2020-06-09

- fixed mirror mode deletion syntax bug

## [0.7.0] - 2020-06-08

- importer command now takes a comma separated list of config ids
- fixed database source password encoding issue

## [0.6.1] - 2020-06-08

- fixed db source field retrieval

## [0.6.0] - 2020-06-05

- fixed dry-run for categories and dc_multilingual support
- added option to call the backend import action with a GET parameter `redirect_url`

## [0.5.0] - 2020-06-05

- added support for heimrichhannot/contao-categories-bundle in the database source

## [0.4.0] - 2020-06-04

- added database source in order to import from one database entity into another (cms database or other)
- added support for DC_Multilingual
- fixed bug in merge mode when none of dateAdded, tstamp and alias is generated automatically
- fixed translations
- fixed state return in command

## [0.3.2] - 2020-04-30

- added getter and setter to AfterFileSourceGetContentEvent

## [0.3.1] - 2020-04-23

- fixed jsonFileSource

## [0.3.0] - 2020-04-20

- added config logging
- added logic for logging exceptions from importer
- added FieldValueCopier source and config dca
- added stopwatch

## [0.2.1] - 2020-04-17

- fixed deletion bugs

## [0.2.0] - 2020-04-17

- refactoring
- added logic for sorting, field setting (dateAdded, tstamp, alias)
- added deletion logic
- added new events

## [0.1.3] - 2020-04-09

- added fieldMapping to Importer config

## [0.1.2] - 2020-04-14

- fixed merge mode
- fixed labels
- fixed file_get_contents error handling
- fixed load callback

## [0.1.1] - 2020-04-09

- adapted for contao version 4.9
- modified command implementation
- fixed url entityDecoding for http sources

## [0.1.0] - 2020-04-08

- initial version, posibillity to import from JSON and CSV
