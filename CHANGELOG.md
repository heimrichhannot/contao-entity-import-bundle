# Changelog
All notable changes to this project will be documented in this file.

## [0.10.0] - 2020-06-19
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
