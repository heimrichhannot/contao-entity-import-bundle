# Contao Entity Import Bundle

This bundle offers a generic importer to migrate data from various sources to contao database entities.

**This Bundle is still on development.**

## Features

- import data from either file content or database into arbitrary contao database entities (`tl_*`)
- support for various data types (json, csv, ...)
- support for various source types (contao file system, http, ...)
- cron, command, TODO

## Impressions

source image
...

importer config image
....

## Installation

Install via composer: `composer require heimrichhannot/contao-entity-import-bundle` and update your database.

## Configuration

1. Navigate to "Import" in the Contao backend in the section "system".
1. Create an importer source to your needs.
1. Create an importer using the source created in the step before.
1. Run the importer either using dry-run or the normal mode.

## Technical instructions
### Run as symfony command

`huh:entity-import:execute config dry-run`

### Run as contao cron

...

## Events
Name  | Description
------|------------
`BeforeImportEvent` | Configure the data before importing
AfterImportEvent | Get imported data after finished import
BeforeAuthenticationEvent | Configure authentication data before sending GET request to http source
FileSourceGetContentEvent | Implement custom logic for acquiring data from new file source
ImporterFactoryCreateFileSource | Implement custom logic for new custom file sources