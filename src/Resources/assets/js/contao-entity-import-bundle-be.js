import '../scss/contao-entity-import-bundle-be.scss';

class ContaoEntityImportBundleBe {
    static init() {
        ContaoEntityImportBundleBe.removeWidthLimitForQuickImporters();
    }

    static removeWidthLimitForQuickImporters() {
        if (document.getElementById('tl_entity_import_quick_config') === null) {
            return;
        }

        document.querySelector('#header .inner').style['max-width'] = 'none';
        document.getElementById('container').style['max-width'] = 'none';
    }
}

document.addEventListener('DOMContentLoaded', ContaoEntityImportBundleBe.init);
