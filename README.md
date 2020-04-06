# Contao Entity Import Bundle

This Bundle is still on development, install only if you know what you are doing.

## Commandline execution
huh:entity-import:execute config dry-run

## Events
Name  | Description
------|------------
BeforeImportEvent | Configure the data before importing
AfterImportEvent | Get imported data after finished import
BeforeAuthenticationEvent | Configure authentication data before sending GET request to http source
FileSourceGetContentEvent | Implement custom logic for acquiring data from new file source
ImporterFactoryCreateFileSource | Implement custom logic for new custom file sources

