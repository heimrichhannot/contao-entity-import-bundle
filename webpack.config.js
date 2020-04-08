var Encore = require('@symfony/webpack-encore');

Encore
.setOutputPath('src/Resources/public/assets')
.addEntry('contao-entity-import-bundle-be', './src/Resources/assets/js/contao-entity-import-bundle-be.js')
.setPublicPath('/bundles/heimrichhannotcontaoentityimportbundle/assets')
.setManifestKeyPrefix('bundles/heimrichhannotcontaoentityimportbundle/assets')
.disableSingleRuntimeChunk()
.enableSassLoader()
.enablePostCssLoader()
.cleanupOutputBeforeBuild()
.enableSourceMaps(!Encore.isProduction())
;

module.exports = Encore.getWebpackConfig();