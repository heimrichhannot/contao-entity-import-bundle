var Encore = require('@symfony/webpack-encore');

Encore
.setOutputPath('public/assets')
.addEntry('contao-entity-import-bundle-be', './assets/js/contao-entity-import-bundle-be.js')
.setPublicPath('/bundles/heimrichhannotcontaoentityimportbundle/assets')
.setManifestKeyPrefix('bundles/heimrichhannotcontaoentityimportbundle/assets')
.disableSingleRuntimeChunk()
.enableSassLoader()
.enablePostCssLoader()
.cleanupOutputBeforeBuild()
.enableSourceMaps(!Encore.isProduction())
;

module.exports = Encore.getWebpackConfig();