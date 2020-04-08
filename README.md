# Contao Entity Import Bundle

This bundle offers a generic importer to migrate data from various sources to contao database entities.

**This Bundle is still on development.**

## Features

- import data from either file content or database into arbitrary contao database entities (`tl_*`)
- support for various data types (json, csv, ...)
- support for various source types (contao file system, http, absolute path)
- executable from contao backend, cronjob, symfony command
- possibility to define static values to be filled into the database, with support of insertTags

## Impressions

Importer source backend settings:
![alt import_source_1](./docs/img/importer_source.png)
![alt import_source_2](./docs/img/importer_source_2.png)

Importer config backend settings:
![alt privacy config](./docs/img/importer_config.png)

## Installation

Install via composer: `composer require heimrichhannot/contao-entity-import-bundle` and update your database.

## Configuration

1. Navigate to "Import" in the Contao backend in the section "system".
1. Create an importer source to your needs.
1. Create an importer using the source created in the step before.
1. Run the importer either using dry-run or the normal mode.

## Technical instructions
### Run as symfony command

`huh:entity-import:execute config-id dry-run`

##### Arguments
Argument | Mandatory | Type | Description
--------|--------|-------|---
config-id | true | integer |The ID of the importer configuration
dry-run | false | boolean |Run importer without writing data into database

### Run as contao cron

Import is executable with contao PoorMansCron. The Interval of execution is similar to the contao definition. The import configuration allows to enable cron execution and picking of the cron interval.
Possible to choose between `minutely`, `hourly`, `daily`, `weekly`, `monthly` interval.

## Events
Event name  | Description
------|------------
`huh.entity_import.after_file_source_get_content_event` | Configure the data after receiving from source
`huh.entity_import.after_import_event` | Get imported data after finished import
`huh.entity_import.before_import_event` | Configure the data before importing
`huh.entity_import.before_authentication_event` | Configure authentication data before sending GET request to http source
`huh.entity_import.source_factory_create_source_event` | Implement custom logic for new custom file sources