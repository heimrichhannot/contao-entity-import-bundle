# Contao Entity Import Bundle

This bundle offers a generic importer to migrate data from various sources to contao database entities.

**This Bundle is still on development.**

## Features

- import data from either file content or database into arbitrary contao database entities (`tl_*`)
- support for various data types (json, csv, ...)
- support for various source types (contao file system, http, absolute path)
- executable from contao backend, as cronjob and symfony command
- automatic field manipulation (sorting, alias, dateAdded, tstamp)
- merge and insert mode
- email and contao_log notifications while executing import with exceptions (will be sent once per importer 
configuration, will be reset after next successful import)

## Impressions

Importer source backend settings:

![alt import_source_1](./docs/img/importer_source.png)

Importer config backend settings:

![alt privacy config](./docs/img/importer_config.png)

## Installation

Install via composer: `composer require heimrichhannot/contao-entity-import-bundle` and update your database.

## Configuration
1. Navigate to "Import" in the Contao backend in the section "system".
1. Create an importer source to your needs.
1. Create an importer using the source created in the step before.
1. Run the importer either using dry-run or the normal mode.

#####config.yml
```yaml
huh_entity_import:
  debug:
    contao_log: true
    email: false
```

## Technical instructions
### Run as symfony command

`huh:entity-import:execute config-id dry-run`

##### Arguments
Argument | Mandatory | Type | Description
--------|--------|-------|---
config-id | true | integer |The ID of the importer configuration
dry-run | false | boolean |Run importer without writing data into database

### Run as contao cron

Import is executable with contao poor man's cron. The interval of execution is similar to the contao definition.
The import configuration allows to enable cron execution and picking of the cron interval.
Possible to choose between `minutely`, `hourly`, `daily`, `weekly`, `monthly` interval. It is recommended to setup
 the debug options in config.yml before importing via cronjob.

## Events

Name | Description
-----|------------
`AfterFileSourceGetContentEvent` | Configure the data after receiving from source
`AfterImportEvent` | Get imported data after finished import
`AfterItemImportEvent` | Get imported item data after finished import
`BeforeAuthenticationEvent` | Configure authentication data before sending GET request to http source
`BeforeImportEvent` | Configure the data before importing
`BeforeItemImportEvent` | Configure the item data before importing; call `setSkipped(true)` in order to skip the import
`SourceFactoryCreateSourceEvent` | Implement custom logic for new custom file sources

## Todo

- database import source
