class ContaoEntityImportBundleBe {
    static init() {
        ContaoEntityImportBundleBe.removeWidthLimitForQuickImporters();
    }

    static removeWidthLimitForQuickImporters() {
        if (document.getElementById('tl_entity_import_quick_config') === null || document.querySelector('.list-widget') === null) {
            return;
        }

        document.querySelector('#header .inner').style['max-width'] = 'none';
        document.getElementById('container').style['max-width'] = 'none';
        document.getElementById('main').style['width'] = 'auto';

        let i = 0;

        document.querySelectorAll('.tl_formbody_edit .widget').forEach((element) => {
            i++;

            if (null === element.querySelector('.list-widget')) {
                element.style['max-width'] = element.classList.contains('long') ? '1000px' : '500px';

                if (i % 2 === 1) {
                    element.classList.add('clr');
                }
            }
        });
    }
}

document.addEventListener('DOMContentLoaded', ContaoEntityImportBundleBe.init);
